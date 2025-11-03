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
class JGiveModelOrganizationform extends AdminModel
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
		$config['event_after_delete'] = 'jgOnAfterOrganizationDelete';
		$config['event_change_state'] = 'jgOnAfterOrganizationChangeState';
		$config['event_after_save']   = 'jgOnAfterOrganizationSave';

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
		$form = $this->loadForm('com_jgive.organization', 'organization', array('control' => 'jform', 'load_data' => $loadData));

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
	public function getTable($type = 'Organization', $prefix = 'JGiveTable', $config = array())
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

		// Check admin and load admin form in case of admin organization form
		if ($app->isClient('administrator'))
		{
			// Check the session for previously entered form data.
			$data = $app->getUserState('com_jgive.edit.organization.data', array());
		}
		else
		{
			$data = $app->getUserState('com_jgive.edit.organizationform.data', array());
		}

		$data = (!empty($data)) ? $data : $this->getItem();

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    2.3.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->id))
			{
				$contactObjClass     = JGive::contact();
				$organizationContact = $contactObjClass->loadContactByOrgId($item->id);
				$individualObjClass  = JGive::individual($organizationContact['individual_id']);

				if (!empty($individualObjClass->getId()))
				{
					$item->contact_id   = $individualObjClass->getId();
					$item->contact_name = $individualObjClass->getName();
				}

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
					// Get Organization's custom fields
					JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
					$customFields = FieldsHelper::getFields('com_jgive.organization', (object) (array) $item, true);

					if (!empty($customFields))
					{
						$item->fields = $customFields;
					}
				}
			}
		}

		return $item;
	}

	/**
	 * Method to validate the Organization contact form data from server side.
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
		$organizationObj = JGive::organization($data['id']);
		$cansaveEmail  = $organizationObj->isExist($data['email'], $data['vendor_id']);

		if ($cansaveEmail == 0)
		{
			$this->setError(Text::_('COM_JGIVE_INDIVIDUAL_UNIQUE_EMAIL'));
			$return = false;
		}

		$data   = parent::validate($form, $data, $group);

		return ($return === true) ? $data: false;
	}
}
