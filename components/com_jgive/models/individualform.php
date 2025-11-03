<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * JGive Model
 *
 * @since  2.5.0
 */
class JGiveModelIndividualForm extends AdminModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.5.0
	 */
	public function __construct($config = array())
	{
		$config['event_after_delete'] = 'jgOnAfterIndividualDelete';
		$config['event_change_state'] = 'jgOnAfterIndividualChangeState';
		$config['event_after_save']   = 'jgOnAfterIndividualSave';

		parent::__construct($config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   2.5.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_jgive.individual', 'individual', array('control' => 'jform','load_data' => $loadData));

		return (empty($form)) ? false : $form;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  boolean|JTable   A database object
	 *
	 * @since  2.5.0
	 */
	public function getTable($type = 'Individual', $prefix = 'JGiveTable', $config = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient("administrator"))
		{
			return Table::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');

			return Table::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   2.5.0
	 */
	protected function loadFormData()
	{
		$app  = Factory::getApplication();

		// Check admin and load admin form in case of admin individual form
		if ($app->isClient('administrator'))
		{
			// Check the session for previously entered form data.
			$data = $app->getUserState('com_jgive.edit.individual.data', array());
		}
		else
		{
			$data = $app->getUserState('com_jgive.edit.individualform.data', array());
		}

		$data = (!empty($data)) ? $data : $this->getItem();

		return $data;
	}

	/**
	 * Method to get the data that returns data for form load.
	 *
	 * @param   int|null  $pk  data id.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   2.5.0
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (!empty($item->user_id))
		{
			$user = Factory::getUser((int) $item->user_id);

			if (!empty($user))
			{
				$item->user_id = $user->id;
			}
		}

		if (!empty($item))
		{
			JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
			$customFields = FieldsHelper::getFields('com_jgive.individual', (object) (array) $item, true);

			if (!empty($customFields))
			{
				$item->fields = $customFields;
			}
		}

		return $item;
	}

	/**
	 * Function is written here for adding donor details into "#_jg_individual" table to maintain contac
	 *
	 * @param   Object  $data  donor object
	 *
	 * @return  boolean|void|integer
	 *
	 * @since   2.5.0
	 */
	public function addDonorContact($data)
	{
		$user   = Factory::getUser();

		// Geting Donor information here from frontend
		$data       = (array) $data;

		$jgiveTableIndividual = JGive::table('Individual');
		$jgiveTableIndividual->load(array('email' => $data['email'], 'vendor_id' => (int) $data['vendor_id']));

		$data['id']               = $jgiveTableIndividual->id ? $jgiveTableIndividual->id: 0;
		$data['addr_line_1']      = $data['address'];
		$data['addr_line_2']      = $data['address2'];
		$data['region']           = $data['state'];
		$data['other_city_check'] = ($data['other_city_check'] == 'on') ? 1 : 0;
		$data['other_city_value'] = '';

		if ($jgiveTableIndividual->id == 0)
		{
			$data['created_date'] = Factory::getDate('now')->toSQL();
			$data['created_by']   = $data['user_id'];
			$data['modified_date'] = '';
			$data['modified_by']   = 0;
		}
		else
		{
			$data['created_date']  = $jgiveTableIndividual->created_date;
			$data['created_by']    = $jgiveTableIndividual->created_by;
			$data['modified_date'] = Factory::getDate('now')->toSQL();
			$data['modified_by']   = $user->id;
		}

		unset($data['address']);
		unset($data['address2']);
		unset($data['campaign_id']);

		// If donor has added other city from input box
		if ($data['other_city_check'])
		{
			$data['other_city_value'] = $data['city'];
			$data['city']             = 0;
		}

		$data['published'] = 1;

		/*As Guest or Register user or individual contact which is associated with other vendor but not associate with this campaign vendor
		Add  2 new individual contacts of that donor
		- one which will associative with campaign vendor
		- another for self(for donor which will not be related with the vendor)*/
		$individualObj = JGive::individual();
		$individualObj->bind($data);
		$id = $individualObj->save();

		$jgiveTableIndividual = JGive::table('Individual');
		$jgiveTableIndividual->load(array('email' => $data['email'], 'vendor_id' => 0));

		$data['id']        = $jgiveTableIndividual->id ? $jgiveTableIndividual->id: 0;
		$data['vendor_id'] = 0;
		$noVendorIndividualObj = JGive::individual();
		$noVendorIndividualObj->bind($data);
		$noVendorIndividualObj->save();

		return ($id);
	}

	/**
	 * Method to validate the Campaign form data from server side.
	 *
	 * @param   \JForm  $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   2.5.0
	 */
	public function validate($form, $data, $group = null)
	{
		$return = true;
		$individualObj = JGive::individual($data['id']);
		$cansaveEmail  = $individualObj->isExist($data['email'], $data['vendor_id']);

		if ($cansaveEmail == 0)
		{
			$this->setError(Text::_('COM_JGIVE_INDIVIDUAL_UNIQUE_EMAIL'));
			$return = false;
		}

		if (!empty($data['phone']))
		{
			$individualTable = JGive::table('Individual');
			$individualTable->load(array('phone' => $data['phone'], 'vendor_id' => (int) $data['vendor_id']));

			if ($data['id'] == 0 || $data['id'] == "")
			{
				if (!empty($individualTable->id))
				{
					$this->setError(Text::_('COM_JGIVE_INDIVIDUAL_UNIQUE_CONTACT'));
					$return = false;
				}
			}
			else
			{
				if ($individualTable->id != $data['id'] && (!empty($individualTable->id)))
				{
					$this->setError(Text::_('COM_JGIVE_INDIVIDUAL_UNIQUE_CONTACT'));
					$return = false;
				}
			}
		}

		$data = parent::validate($form, $data, $group);

		return ($return === true) ? $data: false;
	}
}
