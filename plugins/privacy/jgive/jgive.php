<?php
/**
 * @package     JGIVE
 * @subpackage  Plg_Privacy_JGIVE
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\CMS\Table\User as UserTable;
use Joomla\CMS\Factory;

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models/');
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_jgive/tables');

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');
JLoader::register('PrivacyRemovalStatus', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php');


/**
 * JGive Privacy Plugin.
 *
 * @since  2.2.1
 */
class PlgPrivacyJgive extends PrivacyPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  2.2.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  2.2.1
	 */
	protected $db;

	/**
	 * Reports the privacy related capabilities for this plugin to site administrators.
	 *
	 * @return  array
	 *
	 * @since   2.2.1
	 */
	public function onPrivacyCollectAdminCapabilities()
	{
		$this->loadLanguage();

		return array(
			Text::_('PLG_PRIVACY_JGIVE') => array(
				Text::_('PLG_PRIVACY_JGIVE_PRIVACY_CAPABILITY_CAMPAIGN_PROMOTER_INFORMATION'),
				Text::_('PLG_PRIVACY_JGIVE_PRIVACY_CAPABILITY_DONATION_ORDER_INFORMATION'),
				Text::_('PLG_PRIVACY_JGIVE_PRIVACY_VENDOR_CAPABILITY_USER_DETAIL'),
				Text::_('PLG_PRIVACY_JGIVE_PRIVACY_REPORTS_CAPABILITY_USER_DETAIL'),
				Text::_('PLG_PRIVACY_JGIVE_PRIVACY_JLIKE_CAPABILITY_USER_DETAIL'),
				Text::_('PLG_PRIVACY_JGIVE_PRIVACY_ACTIVITY_STREAM_CAPABILITY_USER_DETAIL'),
				Text::_('PLG_PRIVACY_JGIVE_PRIVACY_TJNOTIFICATION_CAPABILITY_USER_DETAIL')
			)
		);
	}

	/**
	 * Processes an export request for JGive user data
	 *
	 * This event will collect data for the following tables:
	 *
	 * - #__jg_campaigns
	 * - #__jg_donors
	 * - #__jg_orders
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   PrivacyTableRequest  $user     User object
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   2.2.1
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request, User $user = null)
	{
		if (!$user)
		{
			return array();
		}

		/** @var JTableUser $user */
		$userTable = UserTable::getTable();
		$userTable->load($user->id);

		// Create domains array for JGive
		$domains = array();
		$domains[] = $this->createJgiveCampaignsDomain($userTable);
		$domains[] = $this->createJgiveDonorsDomain($userTable);
		$domains[] = $this->createJgiveOrdersDomain($userTable);

		return $domains;
	}

	/**
	 * Create the domain for Jgive Campaigns data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   2.2.1
	 */
	private function createJgiveCampaignsDomain(User $user)
	{
		$domain = $this->createDomain('user_campaigns', 'jgive_user_campaigns_data');
		$selectArray = array('id', 'title', 'creator_id', 'type', 'goal_amount', 'start_date', 'end_date',
			'paypal_email', 'first_name', 'last_name', 'address', 'address2', 'city', 'other_city', 'state',
			'country', 'zip', 'phone');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName($selectArray))
			->from($this->db->quoteName('#__jg_campaigns'))
			->where($this->db->quoteName('creator_id') . '=' . (int) $user->id);

		$campaignsData = $this->db->setQuery($query)->loadAssocList();

		foreach ($campaignsData as $campaignData)
		{
			$domain->addItem($this->createItemFromArray($campaignData, $campaignData['id']));
		}

		return $domain;
	}

	/**
	 * Create the domain for Jgive Orders data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   2.2.1
	 */
	private function createJgiveDonorsDomain(User $user)
	{
		$domain = $this->createDomain('user_donors_information', 'jgive_user_donors_information');
		$selectArray = array('d.id', 'd.user_id','c.title', 'd.email', 'd.first_name', 'd.last_name',
		'd.address','d.address2', 'd.city', 'd.state', 'd.country', 'd.zip', 'd.phone');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName($selectArray))
			->from($this->db->quoteName('#__jg_donors', 'd'))
			->join('LEFT', $this->db->quoteName('#__jg_campaigns', 'c')
			. ' ON (' . $this->db->quoteName('d.campaign_id') . ' = ' . $this->db->quoteName('c.id') . ')')
			->where($this->db->quoteName('d.user_id') . '=' . (int) $user->id);

		$donorsData = $this->db->setQuery($query)->loadAssocList();

		foreach ($donorsData as $donorData)
		{
			$domain->addItem($this->createItemFromArray($donorData, $donorData['id']));
		}

		return $domain;
	}

	/**
	 * Create the domain for Jgive Orders data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   2.2.1
	 */
	private function createJgiveOrdersDomain(User $user)
	{
		$domain = $this->createDomain('user_orders', 'jgive_user_orders_data');

		$query = $this->db->getQuery(true)
			->select(array('o.id', 'o.order_id', 'c.title', 'o.original_amount', 'o.amount', 'o.vat_number','o.ip_address','o.status', 'o.processor'))
			->from($this->db->quoteName('#__jg_orders', 'o'))
			->join('LEFT', $this->db->quoteName('#__jg_donors', 'd')
			. ' ON (' . $this->db->quoteName('o.donor_id') . ' = ' . $this->db->quoteName('d.id') . ')')
			->join('LEFT', $this->db->quoteName('#__jg_campaigns', 'c')
			. ' ON (' . $this->db->quoteName('o.campaign_id') . ' = ' . $this->db->quoteName('c.id') . ')')
			->where($this->db->quoteName('d.user_id') . '=' . (int) $user->id);

		$ordersData = $this->db->setQuery($query)->loadAssocList();

		foreach ($ordersData as $orderData)
		{
			$domain->addItem($this->createItemFromArray($orderData, $orderData['id']));
		}

		return $domain;
	}

	/**
	 * Performs validation to determine if the data associated with a remove information request can be processed
	 *
	 * This event will not allow a super user account to be removed
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyRemovalStatus
	 *
	 * @since   2.2.1
	 */
	public function onPrivacyCanRemoveData(PrivacyTableRequest $request, User $user = null)
	{
		$status = new PrivacyRemovalStatus;

		if (!$user->id)
		{
			return $status;
		}

		// Check if user is campaign creator
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__jg_campaigns'));
		$query->where($db->quoteName('creator_id') . '=' . (int) $user->id);
		$db->setQuery($query);
		$campaigns = $db->loadColumn();

		if (!empty($campaigns))
		{
			$allOrders = array();

			foreach ($campaigns as $campaignId)
			{
				// Get campaign orders
				$query = $db->getQuery(true);
				$query->select($db->quoteName('order_id'));
				$query->from($db->quoteName('#__jg_orders'));
				$query->where($db->quoteName('campaign_id') . '=' . (int) $campaignId);
				$db->setQuery($query);
				$orders = $db->loadColumn();

				if (!empty($orders))
				{
					$allOrders = array_merge($allOrders, $orders);
				}
			}
		}

		$query = $this->db->getQuery(true)
			->select(array('o.order_id'))
			->from($this->db->quoteName('#__jg_orders', 'o'))
			->join('LEFT', $this->db->quoteName('#__jg_donors', 'd')
			. ' ON (' . $this->db->quoteName('o.donor_id') . ' = ' . $this->db->quoteName('d.id') . ')')
			->where($this->db->quoteName('d.user_id') . '=' . (int) $user->id);

		$allDonations = $this->db->setQuery($query)->loadColumn();

		$message = array();

		if (!empty($allDonations))
		{
			$status->canRemove = false;
			$donationsList = 'ID: ' . implode(', ', $allDonations);
			$message[] = Text::sprintf('PLG_PRIVACY_JGIVE_PRIVACY_ERROR_USER_DONATIONS', $donationsList);
		}

		// Restrict user deletion if there are orders associated with the stores owned by the user
		if (!empty($allOrders))
		{
			$status->canRemove = false;
			$ordersList = 'ID: ' . implode(', ', $allOrders);
			$message[] = Text::sprintf('PLG_PRIVACY_JGIVE_PRIVACY_ERROR_CAMPAIGNS_WITH_ORDERS', $ordersList);
		}

		$status->reason = implode(' ', $message);

		return $status;
	}

	/**
	 * Removes the data associated with a remove information request
	 *
	 * This event will pseudoanonymise the user account
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  void
	 *
	 * @since   2.2.1
	 */
	public function onPrivacyRemoveData(PrivacyTableRequest $request, User $user = null)
	{
		// This plugin only processes data for registered user accounts
		if (!$user)
		{
			return;
		}

		// If there was an error loading the user do nothing here
		if ($user->guest)
		{
			return;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__jg_campaigns'))
			->where($this->db->quoteName('creator_id') . ' = ' . (int) $user->id);
		$this->db->setQuery($query);
		$campaignIds = $this->db->loadColumn();

		if (!empty($campaignIds))
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
			$jgiveCampaignTable = Table::getInstance('campaign', 'JgiveTable', array('dbo', $this->db));

			foreach ($campaignIds as $campaignId)
			{
				$jgiveCampaignTable->delete($campaignId);
			}
		}
	}
}
