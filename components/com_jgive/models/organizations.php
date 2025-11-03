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
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * Organizations Model
 *
 * @since  _DEPLOY_VERSION__
 */
class JGiveModelOrganizations extends ListModel
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
				'name','a.name',
				'email','a.email',
				'phone','a.phone',
				'taxnumber','a.taxnumber',
				'website','a.website',
				'published','a.published',
				'vendor_id','a.vendor_id',
				'search',
				'contact_name'
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
		$app  = Factory::getApplication();

		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', null, 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

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
		$query->select(
						array('c.id as contact_id', 'c.first_name as contact_firstname', 'c.last_name as contact_lastname',
						'c.email as contact_email', 'c.phone as contact_phone'
						)
					);
		$query->select('d.country');
		$query->select('e.region');
		$query->select('f.city');
		$query->from($db->quoteName('#__jg_organizations', 'a'));
		$query->join(
						'LEFT', $db->quoteName('#__jg_organization_contacts', 'b') .
						' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.organization_id') . ')'
					);
		$query->join('LEFT', $db->quoteName('#__jg_individuals', 'c') . ' ON (' . $db->quoteName('b.individual_id') . ' = ' . $db->quoteName('c.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_country', 'd') . ' ON (' . $db->quoteName('a.country') . ' = ' . $db->quoteName('d.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_region', 'e') . ' ON (' . $db->quoteName('a.region') . ' = ' . $db->quoteName('e.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_city', 'f') . ' ON (' . $db->quoteName('a.city') . ' = ' . $db->quoteName('f.id') . ')');

		// Frontend List view show only vendor associated records
		if ($app->isClient('site'))
		{
			$userVendorId = JGive::utilities()->getVendorId($user->id, 'com_jgive');
			$query->where($db->quoteName('a.vendor_id') . ' = ' . (int) $userVendorId);
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
				$query->where('( a.name LIKE ' . $ajaxSearch . ' )');
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
				$query->where(
					'( a.name LIKE ' . $search .
						' OR a.email LIKE ' . $search .
						' OR a.website LIKE ' . $search .
					' )');
			}
		}

		// To get vendor specific contacts
		$vendorId = $this->getState('filter.vendor_id');

		if (!empty($vendorId))
		{
			$query->where($db->quoteName('a.vendor_id') . ' = ' . (int) $vendorId);
		}

		// For auto suggested name in donation and organization form
		if (!empty($published) && ($published == 1))
		{
			$query->where('(a.published = 1)');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.id');
		$orderDirn 	= $this->state->get('list.direction', 'asc');

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
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a list of organizations.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   2.3.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get Joomla Custom field data
		if (!empty($items))
		{
			JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

			foreach ($items as $i => $item)
			{
				$item->type = "org";

				// Custom requirement for app
				$item->search_name = $item->name;
				$organizationFields = FieldsHelper::getFields('com_jgive.organization', $item, true);

				$organizationCustomFields = array();

				if (!empty($organizationFields))
				{
					foreach ($organizationFields as $field)
					{
						$organizationCustomFields[$field->name] = $field->rawvalue;
					}

					$item->customField = $organizationCustomFields;
				}

				$item->vendorTitle = "";

				if (!empty($item->vendor_id))
				{
					$vendorClassObj = TJVendors::vendor($item->vendor_id, 'com_jgive');
					$item->vendorTitle = $vendorClassObj->getTitle();
				}

				$donor         = JGive::donor();
				$donorId       = $donor->getDonorIDByContactDetails($item->id, $item->type);
				$donorData     = JGive::donor($donorId);
				$contributorId = $donorData->getContributorId();
				$item->totalDonationAmount = 0;
				$item->totalDonations = 0;

				if ($contributorId)
				{
					$item->totalDonationAmount = $donorData->getTotalDonatedAmount();
					$item->totalDonations = $donorData->getAllDonationsCount();
				}
			}
		}

		return $items;
	}

	/**
	 * Delete organization and organization contact.
	 *
	 * @param   array  $orgId  organization id array.
	 *
	 * @return boolean
	 *
	 * @since   2.5.0
	 */
	public function delete($orgId)
	{
		$app   = Factory::getApplication();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__jg_organization_contacts'));
		$query->where($db->quoteName('#__jg_organization_contacts.organization_id') . ' IN (' . implode(',', $orgId) . ')');
		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		$Organizationform = ($app->isClient('site'))? 'Organizationform' : 'Organization';
		$organizationModel = BaseDatabaseModel::getInstance($Organizationform, 'JGiveModel', array('ignore_request' => true));

		foreach ($orgId as $id)
		{
			$organizationModel->delete($id);
		}

		return true;
	}

	/**
	 * Method to get emailid from organization table by using id.
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
		$query->select('DISTINCT org.email');
		$query->select($db->qn('org.name'));
		$query->from($db->qn('#__jg_organizations', 'org'));
		$query->where($db->qn('org.id') . ' IN(' . implode(',', ArrayHelper::toInteger($contactsId)) . ')');
		$query->where($db->qn('org.published') . ' = 1');
		$db->setQuery($query);
		$emailArray = $db->loadAssocList();

		return $emailArray;
	}

	/**
	 * Sending mail to Organizations in bulk
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
