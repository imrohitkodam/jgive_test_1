<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * JgiveModelDonors model class.
 *
 * @package  JGive
 * @since    1.8.1
 */
class JgiveModelDonor extends ItemModel
{
	/**
	 * Method &populateState.
	 *
	 * @return void.
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_jgive');

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_jgive.edit.donor.id');
		}
		else
		{
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_jgive.edit.donor.id', $id);
		}

		$this->setState('donor.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('donor.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method &getData.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return item.
	 */
	public function &getData($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id))
			{
				$id = $this->getState('donor.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id))
			{
				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->_item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, 'JObject');
			}
		}

		if (isset($this->_item->user_id))
		{
			$this->_item->user_id_name = Factory::getUser($this->_item->user_id)->name;
		}

		if (isset($this->_item->campaign_id) && $this->_item->campaign_id != '')
		{
			if (is_object($this->_item->campaign_id))
			{
				$this->_item->campaign_id = ArrayHelper::fromObject($this->_item->campaign_id);
			}

			$values = (is_array($this->_item->campaign_id)) ? $this->_item->campaign_id : explode(',', $this->_item->campaign_id);

			$textValue = array();

			foreach ($values as $value)
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select('title')->from('`#__jg_campaigns`')->where('id = ' . $db->quote($db->escape($value)));
				$db->setQuery($query);
				$results = $db->loadObject();

				if ($results)
				{
					$textValue[] = $results->title;
				}
			}

			$this->_item->campaign_id = !empty($textValue) ? implode(', ', $textValue) : $this->_item->campaign_id;
		}

		// Adding country, region, city node for API
		if ($this->_item->country)
		{
			$this->_item->countryDetails = JGive::Utilities()->getCountry((int) $this->_item->country);
		}

		if ($this->_item->state)
		{
			$this->_item->regionDetails = JGive::Utilities()->getRegion((int) $this->_item->state);
		}

		if (is_numeric($this->_item->city))
		{
			$this->_item->cityDetails = JGive::Utilities()->getCity((int) $this->_item->city);
		}

		return $this->_item;
	}

	/**
	 * Method getTable.
	 *
	 * @param   String  $type    Type
	 * @param   String  $prefix  Prefix
	 * @param   Array   $config  Config
	 *
	 * @return Id
	 *
	 * @since    1.8.1
	 */
	public function getTable($type = 'Donor', $prefix = 'JgiveTable', $config = array())
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
	 * Method checkin.
	 *
	 * @param   String  $alias  Alias
	 *
	 * @return Id
	 *
	 * @since    1.8.1
	 */
	public function getItemIdByAlias($alias)
	{
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Method checkin.
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return Boolean
	 *
	 * @since    1.8.1
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('donor.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method checkout.
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return Boolean
	 *
	 * @since    1.8.1
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('donor.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method getCategoryName.
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return Object
	 *
	 * @since    1.8.1
	 */
	public function getCategoryName($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title')->from('#__categories')->where('id = ' . $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method publish.
	 *
	 * @param   Integer  $id     Id
	 * @param   String   $state  State
	 *
	 * @return void
	 *
	 * @since    1.8.1
	 */
	public function publish($id, $state)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->state = $state;

		return $table->store();
	}

	/**
	 * Method delete.
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return void
	 *
	 * @since    1.8.1
	 */
	public function delete($id)
	{
		$table = $this->getTable();

		return $table->delete($id);
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

		return parent::getItem($pk);
	}
}
