<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined("_JEXEC") or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * Individuals Model
 *
 * @since  _DEPLOY_VERSION__
 */
class JGiveModelIndividuals extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.3.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id','a.id',
				'first_name','a.first_name',
				'last_name','a.last_name',
				'email','a.email',
				'vendor_id','a.vendor_id',
				'published','a.published',
				'search'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return  void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = 'id', $direction = 'desc')
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'id';
		}

		$this->setState('list.ordering', $orderCol);

		// Load the filter search
		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jgive');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		$app   = Factory::getApplication();
		$user  = Factory::getUser();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*');
		$query->select('b.country');
		$query->select('c.region');
		$query->select('d.city');
		$query->from($db->quoteName('#__jg_individuals', 'a'));
		$query->join('LEFT', $db->quoteName('#__tj_country', 'b') . ' ON (' . $db->quoteName('a.country') . ' = ' . $db->quoteName('b.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_region', 'c') . ' ON (' . $db->quoteName('a.region') . ' = ' . $db->quoteName('c.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_city', 'd') . ' ON (' . $db->quoteName('a.city') . ' = ' . $db->quoteName('d.id') . ')');

		// Frontend List view show only vendor associated records
		if ($app->isClient('site'))
		{
			$userVendorId = JGive::utilities()->getVendorId($user->id, 'com_jgive');
			$query->where($db->quoteName('a.vendor_id') . ' = ' . (int) $userVendorId);
			$query->where($db->quoteName('a.vendor_id') . ' <> ' . (int) 0);
		}

		// Filter by word provided in search box
		$search     = $this->getState('filter.search');
		$ajaxSearch = $this->getState('search');
		$published  = $this->getState('published');

		if (!empty($ajaxSearch))
		{
			if (stripos($ajaxSearch, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($ajaxSearch, 3));
			}
			else
			{
				$ajaxSearch = $db->Quote('%' . $db->escape($ajaxSearch, true) . '%');
				$query->where('( a.first_name LIKE ' . $ajaxSearch .
						' OR a.last_name LIKE ' . $ajaxSearch . ' )');
			}
		}
		elseif (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape(trim($search), true) . '%');
				$query->where('( a.first_name LIKE ' . $search .
						' OR a.last_name LIKE ' . $search .
						' OR (CONCAT(a.first_name, " ", a.last_name) LIKE ' . $search . ')' . 
						' OR a.email LIKE ' . $search .
						' )');
			}
		}

		// To get vendor specific contacts
		$vendorId = $this->getState('filter.vendor_id');

		if (!empty($vendorId))
		{
			$query->where($db->quoteName('a.vendor_id') . ' = ' . (int) $vendorId);
		}

		$email = $this->getState('filter.email');

		if (!empty($email))
		{
			$query->where($db->quoteName('a.email') . ' = ' . $db->quote($email));
		}

		// For auto suggested name in donation and organization form
		if (!empty($published) && ($published == '1'))
		{
			$query->where('(a.published = 1)');
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   2.3.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a list of individuals.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   2.3.0
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$app   = Factory::getApplication();

		// Get Joomla Custom field data
		if (!empty($items))
		{
			JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

			foreach ($items as $i => $item)
			{
				$item->type = "ind";

				// Custom requirement for app
				$item->search_name = $item->first_name . ' ' . $item->last_name;
				$individualFields = FieldsHelper::getFields('com_jgive.individual', $item, true);

				$individualCustomFields = array();

				if (!empty($individualFields))
				{
					foreach ($individualFields as $field)
					{
						$individualCustomFields[$field->name] = $field->rawvalue;
					}

					$item->customField = $individualCustomFields;
				}

				$item->vendorTitle = "";

				if (!empty($item->vendor_id))
				{
					$vendorClassObj = TJVendors::vendor($item->vendor_id, 'com_jgive');
					$item->vendorTitle = $vendorClassObj->getTitle();
				}

				if ($app->isClient('site'))
				{
					$individualObj   = JGive::Individual($item->id);
					$item->amount    = $individualObj->getTotalDonatedAmount($formattedAmount = true);
					$item->donations = $individualObj->getAllDonationsCount();
				}
			}
		}

		return $items;
	}

	/**
	 * Method to get a list of individuals and distinct joomla users
	 *
	 * @param   string   $search    Search value.
	 * @param   integer  $vendorId  vendorId
	 *
	 * @return  mixed  An array of individuals and distinct joomla users.
	 *
	 * @since   2.3.0
	 */
	public function getIndividuals($search, $vendorId)
	{
		$app   = Factory::getApplication();

		$this->setState("search", $search);
		$this->setState("published", '1');
		$individuals   = $this->getItems();

		// At backend Donation form show individual + joomla users list
		if ($app->isClient('administrator') && empty($vendorId))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			// Get distinct joomla users.
			$query->select("'user' as type");
			$query->select("a.name as first_name");
			$query->select("a.*");
			$query->from($db->qn('#__users', 'a'));

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('a.id = ' . (int) substr($search, 3));
				}
				elseif (stripos($search, 'username:') === 0)
				{
					$search = $db->quote('%' . $db->escape(substr($search, 9), true) . '%');
					$query->where('a.username LIKE ' . $search);
				}
				else
				{
					// Escape the search token.
					$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
					$query->where(
					'( a.name LIKE ' . $search .
						' OR a.email LIKE ' . $search .
						' OR a.username LIKE ' . $search .
					' )');
				}
			}

			$query->where($db->quoteName('a.id') . ' NOT IN (SELECT user_id FROM #__jg_individuals)');
			$query->order($db->quoteName('id') . ' ASC LIMIT 5');

			$db->setQuery($query);
			$users       = $db->loadObjectlist();
			$individuals = array_merge($individuals, $users);
		}

		return $individuals;
	}

	/**
	 * Delete individuals and organization_contacts.
	 *
	 * @param   array  $indID  individuals id array.
	 *
	 * @return boolean
	 *
	 * @since   2.3
	 */
	public function delete($indID)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__jg_organization_contacts'));
		$query->where($db->quoteName('#__jg_organization_contacts.individual_id') . ' IN (' . implode(',', $indID) . ')');
		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		foreach ($indID as $id)
		{
			$individualModel = BaseDatabaseModel::getInstance('Individual', 'JGiveModel', array('ignore_request' => true));
			$individualModel->delete($id);
		}

		return true;
	}

	/**
	 * Method to get emailid from individuals table by using id.
	 *
	 * @param   Array  $contactsId  Pass the Donor Id for getting email
	 *
	 * @return Array
	 *
	 * @since    2.5.0
	 */
	public function getContactsEmail($contactsId)
	{
		$db         = Factory::getDbo();
		$query      = $db->getQuery(true);
		$query->select('DISTINCT d.email');
		$query->select($db->qn('d.first_name'));
		$query->select($db->qn('d.last_name'));
		$query->from($db->qn('#__jg_individuals', 'd'));
		$query->where($db->qn('d.id') . ' IN(' . implode(',', ArrayHelper::toInteger($contactsId)) . ')');
		$query->where($db->qn('d.published') . ' = 1');
		$db->setQuery($query);
		$emailArray = $db->loadAssocList();

		return $emailArray;
	}

	/**
	 * Sending mail to individuals in bulk
	 *
	 * @param   string  $recipient  Email
	 * @param   string  $subject    Email sub
	 * @param   string  $body       Email body
	 * @param   string  $action     Mail to send for action e.g donation.made, campaign.create etc
	 *
	 * @return  object | boolean
	 */
	public function sendMailToContacts($recipient, $subject, $body, $action = "donation.made")
	{
		$app   = Factory::getApplication();
		$from        = $app->get('mailfrom');
		$fromname    = $app->get('fromname');
		$recipient   = trim($recipient);
		$mailer = Factory::getMailer();
		$mailer->setSender(array($from, $fromname));
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->addRecipient($recipient);
		$mailer->isHtml(true);
		$mailer->AddCustomHeader("X-Custom-Header:" . $action);

		return $mailer->Send();
	}
}
