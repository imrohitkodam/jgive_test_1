<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('FFF');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;


/**
 * Dashboard form model class.
 *
 * @package  JGive
 * @since    1.8
 */

class JgiveModelDashboard extends BaseDatabaseModel
{
	/**
	 * The method used to find out the count of the campaign which has created by the logged user.
	 *
	 * @return  list of campaign id
	 *
	 * @since   1.8
	 */
	public function getCampaignIds()
	{
		$db = Factory::getDbo();

		// Get Login user id
		$user    = Factory::getUser();
		$user_id = $user->id;

		$query = $db->getQuery(true);
		$query->select($db->qn(array('id', 'creator_id', 'title')))
			->from($db->qn('#__jg_campaigns'))
			->where($db->qn('#__jg_campaigns.creator_id') . ' = ' . (int) $user_id);

		$db->setQuery($query);
		$campaignIds = $db->loadAssocList();

		return $campaignIds;
	}

	/**
	 * Function getDashboardData
	 *
	 * @return  array of campaign id
	 *
	 * @since   1.8
	 */
	public function getDashboardData()
	{
		$db      = Factory::getDbo();

		// Get Login user id
		$user    = Factory::getUser();
		$user_id = $user->id;

		$query = $db->getQuery(true);
		$query->select('SUM(o.amount) as alltimedonationamount');
		$query->select('COUNT(o.id) as countofdonar');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . 'ON (' . $db->qn('c.id') . ' = ' . $db->qn('o.campaign_id') . ')');
		$query->where($db->qn('c.creator_id') . ' = ' . (int) $user_id);
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));

		$db->setQuery($query);

		$dashboardData = $db->loadAssoc();

		return $dashboardData;
	}

	/**
	 * Function getDonarCount
	 *
	 * @return  donar count
	 *
	 * @since   1.8
	 */
	public function getDonarCount()
	{
		$db      = Factory::getDbo();

		// Get Login user id
		$user    = Factory::getUser();
		$user_id = $user->id;
		$query   = $db->getQuery(true);

		// Donor Count who has registered
		$query->select('COUNT(DISTINCT d.user_id)');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_donors', 'd') . ' ON (' . $db->qn('d.id') . ' = ' . $db->qn('o.donor_id') . ')');
		$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('o.campaign_id') . ' = ' . $db->qn('c.id') . ')');
		$query->where($db->qn('c.creator_id') . ' = ' . (int) $user_id);
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->where($db->qn('d.user_id') . '<>' . $db->quote('0'));

		$db->setQuery($query);
		$donarCountreg = $db->loadResult();

		$query   = $db->getQuery(true);

		// Donor Count whi has guest
		$query->select('COUNT(DISTINCT d.email)');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_donors', 'd') . ' ON (' . $db->qn('d.id') . ' = ' . $db->qn('o.donor_id') . ')');
		$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('o.campaign_id') . ' = ' . $db->qn('c.id') . ')');
		$query->where($db->qn('c.creator_id') . ' = ' . (int) $user_id);
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->where($db->qn('d.user_id') . '=' . $db->quote('0'));

		$db->setQuery($query);
		$donarCountguest = $db->loadResult();

		return $donarCountreg + $donarCountguest;
	}

	/**
	 * Function for Recent 5 donation Details
	 *
	 * @return  Object List of Recent periodic donation
	 *
	 * @since   1.8
	 */
	public function getRecent5DonationDetails()
	{
		$db      = Factory::getDbo();
		$user    = Factory::getUser();

		// Get Login user id
		$user_id = $user->id;
		$query   = $db->getQuery(true);

		$query->select($db->qn(array('o.id', 'o.order_id', 'o.cdate', 'o.amount', 'o.status', 'd.first_name', 'd.last_name')))
			->from($db->qn('#__jg_orders', 'o'))
			->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('o.campaign_id') . ' = ' . $db->qn('c.id') . ')')
			->join('INNER', $db->qn('#__jg_donors', 'd') . ' ON (' . $db->qn('d.id') . ' = ' . $db->qn('o.donor_id') . ')')
			->where($db->qn('c.creator_id') . ' = ' . (int) $user_id)
			->order($db->qn('o.cdate') . ' DESC')
			->setLimit(5);

		$db->setQuery($query);
		$recentDonations = $db->loadAssocList();

		return $recentDonations;
	}

	/**
	 * Function for Top 5 donors
	 *
	 * @return  Object List of Recent periodic donation
	 *
	 * @since   1.8
	 */
	public function getTop5Donors()
	{
		$db = Factory::getDbo();

		// Get Login user id
		$user    = Factory::getUser();
		$user_id = $user->id;

		$query   = $db->getQuery(true);

		$query->select('o.campaign_id');
		$query->select('d.first_name');
		$query->select('d.last_name');
		$query->select('SUM(o.amount) as donorwise_total_donation_amount');
		$query->from($db->qn('#__jg_donors', 'd'));
		$query->join('INNER', $db->qn('#__jg_orders', 'o') . ' ON (' . $db->qn('o.donor_id') . ' = ' . $db->qn('d.id') . ')');
		$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('o.campaign_id') . ')');
		$query->where($db->qn('c.creator_id') . ' = ' . (int) $user_id . ' AND ' . $db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->group($db->qn('d.user_id'));
		$query->order($db->qn('donorwise_total_donation_amount') . ' DESC');

		$db->setQuery($query);
		$top5Donors = $db->loadAssocList();

		return $top5Donors;
	}

	/**
	 * Function for get user created campaign Details
	 *
	 * @return  array of campaign Details
	 *
	 * @since   1.8
	 */
	public function getMyCampaign()
	{
		$user = Factory::getUser();
		$user_id = $user->id;

		$mainframe = Factory::getApplication();
		$option = $mainframe->input->get('option', '', 'STRING');

		$filter_searchCamp = $mainframe->getUserStateFromRequest("$option.filter_search", 'filter_search', '', 'string');
		$filter_dashboard_filters = $mainframe->getUserStateFromRequest("$option.dashboard_filters", 'dashboard_filters');

		$filter_dashboard_categories = $mainframe->input->get('cat', '', 'INT');

		$filter_categories = $filter_dashboard_categories;
		$filter_dashboard_campStatus = $mainframe->input->get('campStatus');
		$filter_dashboard_campType = $mainframe->input->get('campType');
		$filter_dashboard_orgType = $mainframe->input->get('orgType');

		if ($filter_dashboard_filters)
		{
			$todate = Factory::getDate(date('Y-m-d'))->Format(Text::_('Y-m-d'));

			if ($filter_dashboard_filters == 1)
			{
				$previousDuration = 7;
				$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $previousDuration . ' days'));
			}
			elseif ($filter_dashboard_filters == 2)
			{
				$previousDuration = 30;
				$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $previousDuration . ' days'));
			}
			elseif ($filter_dashboard_filters == 3)
			{
				$previousDuration = 365;
				$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $previousDuration . ' days'));
			}
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.*');
		$query->from($db->quoteName('#__jg_campaigns', 'c'));
		$query->where($db->quoteName('c.creator_id') . ' = ' . (int) $user_id);

		if ($filter_searchCamp)
		{
			$search = $db->Quote('%' . $db->escape($filter_searchCamp, true) . '%');
			$query->where($db->quoteName('c.title') . ' LIKE ' . $search);
		}

		if ($filter_dashboard_filters != 0)
		{
			$query->where('DATE(' . $db->quoteName('c.start_date') . ')' . ' >= ' . $db->quote($backdate));
		}

		if ($filter_categories != null)
		{
			$query->where($db->quoteName('c.category_id') . ' = ' . (int) $filter_categories);
		}

		if ($filter_dashboard_campStatus != null)
		{
			$query->where($db->quoteName('c.published') . ' = ' . $db->quote($filter_dashboard_campStatus));
		}

		if ($filter_dashboard_campType != null)
		{
			$query->where($db->quoteName('c.type') . ' = ' . $db->quote($filter_dashboard_campType));
		}

		if ($filter_dashboard_orgType != null)
		{
			$query->where($db->quoteName('c.org_ind_type') . ' = ' . $db->quote($filter_dashboard_orgType));
		}

		$db->setQuery($query);
		$my_camp_data = $db->loadAssocList();

		require_once JPATH_SITE . "/components/com_jgive/helpers/campaign.php";
		$campaignHelper = new campaignHelper;

		foreach ($my_camp_data as $key => $camp_data)
		{
			// Get campaign amounts
			$amounts             = $campaignHelper->getCampaignAmounts($camp_data['id']);

			$my_camp_data[$key]['amount_received'] = $amounts['amount_received'];
			$my_camp_data[$key]['remaining_amount'] = $amounts['remaining_amount'];
		}

		return $my_camp_data;
	}

	/**
	 * Function for get donors Details Details
	 *
	 * @return  array of list
	 *
	 * @since   1.8
	 */

	public function getDonorsDetails()
	{
		$db = Factory::getDbo();

		$user = Factory::getUser();
		$user_id = $user->id;

		$query = $db->getQuery(true);

		$query->select('SUM(o.amount) as donation_amount');
		$query->select('d.*');
		$query->select('ds.annonymous_donation');
		$query->select('ds.giveback_id');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('o.campaign_id') . ')');
		$query->join('INNER', $db->qn('#__users', 'u') . ' ON (' . $db->qn('u.id') . ' = ' . $db->qn('c.creator_id') . ')');
		$query->join('INNER', $db->qn('#__jg_donors', 'd') . ' ON (' . $db->qn('d.id') . ' = ' . $db->qn('o.donor_id') . ')');
		$query->join('INNER', $db->qn('#__jg_donations', 'ds') . ' ON (' . $db->qn('ds.donor_id') . ' = ' . $db->qn('d.id') . ')');
		$query->where($db->qn('c.creator_id') . ' = ' . (int) $user_id . ' AND ' . $db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->group($db->qn('d.email'));
		$query->order($db->qn('donation_amount') . ' DESC');

		$db->setQuery($query);
		$donorsDetails = $db->loadObjectList();

		// Getting Donor Avatar profile
		if (isset($donorsDetails))
		{
			foreach ($donorsDetails as $donorsDetail)
			{
				$helperPath = JPATH_SITE . '/components/com_jgive/helpers/integrations.php';

				if (!class_exists('JgiveIntegrationsHelper'))
				{
					JLoader::register('JgiveIntegrationsHelper', $helperPath);
					JLoader::load('JgiveIntegrationsHelper');
				}

				$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
				$donorsDetail->avatar      = $JgiveIntegrationsHelper->getUserAvatar($donorsDetail->user_id);
				$donorsDetail->profile_url = $JgiveIntegrationsHelper->getUserProfileUrl($donorsDetail->user_id);
			}
		}

		return $donorsDetails;
	}

	/**
	 * Function for get periodic donation graph data
	 *
	 * @return  array of list
	 *
	 * @since   1.8
	 */
	public function getPeriodicDonationGraphData()
	{
		$db       = Factory::getDbo();

		// Get Login user id
		$user     = Factory::getUser();
		$user_id  = $user->id;

		$app      = Factory::getApplication();
		$backdate = $app->getUserStateFromRequest('from', 'from', '', 'string');
		$todate = $app->getUserStateFromRequest('to', 'to', '', 'string');

		// Get date for 30 days before, in Y-m-d H:i:s format
		$thirtyDaysBefore = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		$backdate         = !empty($backdate) ? $backdate : Factory::getDate($thirtyDaysBefore)->Format(Text::_('Y-m-d'));

		// Get current date, in Y-m-d H:i:s format
		$todate         = !empty($todate) ? $todate : Factory::getDate(date('Y-m-d'))->Format(Text::_('Y-m-d'));

		$query = $db->getQuery(true);

		$query->select('SUM(o.amount) AS donation_amount');
		$query->select('DATE(o.cdate) AS cdate');
		$query->select('COUNT(o.id) AS orders_count');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('o.campaign_id') . ')'
		);
		$query->where(
		$db->qn('c.creator_id') . ' = ' . (int) $user_id . ' AND ' . $db->qn('o.status') . ' = ' .
		$db->quote('C')
		);
		$query->where(
		'DATE(' . $db->qn('o.cdate') . ')' . ' >= ' . $db->quote($backdate) . ' AND ' . 'DATE(' . $db->qn('o.cdate') . ')' . ' <= ' . $db->quote($todate)
		);
		$query->group('DATE(' . $db->qn('o.cdate') . ')');

		$db->setQuery($query);

		return $db->loadObjectList("cdate");
	}

	/**
	 * Method to get Dashboard Graph Data
	 *
	 * @param   Integer  $duration  This show the duration for graph data
	 * @param   Integer  $userid    This show the camp promoter id
	 *
	 * @return  Json data
	 *
	 * @since  2.0
	 */
	public function getDashboardGraphData($duration, $userid)
	{
		if ($duration == 0)
		{
			$graphDuration = 7;
		}
		elseif ($duration == 1)
		{
			$graphDuration = 30;
		}

		$todate = Factory::getDate(date('Y-m-d'))->Format(Text::_('Y-m-d'));

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($duration == 0 || $duration == 1)
		{
			$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $graphDuration . ' days'));
			$query->select('SUM(o.amount) AS donation_amount');
			$query->select('DATE(o.cdate) AS cdate');
			$query->select('COUNT(o.id) AS orders_count');
			$query->from($db->qn('#__jg_orders', 'o'));
			$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('o.campaign_id') . ')');
			$query->where($db->qn('c.creator_id') . ' = ' . (int) $userid . ' AND ' . $db->qn('o.status') . ' = ' . $db->quote('C'));
			$query->where('DATE(' . $db->qn('o.cdate') . ')' . ' >= ' . $db->quote($backdate) . ' AND ' . 'DATE(' . $db->qn('o.cdate') . ')' . ' <= ' .
			$db->quote($todate)
			);
			$query->group('DATE(' . $db->qn('o.cdate') . ')');
			$query->order($db->qn('o.cdate') . 'DESC');

			$db->setQuery($query);

			$results = $db->loadObjectList();
		}
		elseif ($duration == 2)
		{
			$curdate    = date('Y-m-d');
			$back_year  = date('Y') - 1;
			$back_month = date('m') + 1;
			$backdate   = $back_year . '-' . $back_month . '-' . '01';

			$query->select('SUM(o.amount) AS donation_amount');
			$query->select('MONTH(o.cdate) AS MONTHSNAME');
			$query->select('YEAR(o.cdate) AS YEARNAME');
			$query->select('COUNT(o.id) AS orders_count');
			$query->from($db->qn('#__jg_orders', 'o'));
			$query->join('INNER', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('o.campaign_id') . ')');
			$query->where($db->qn('c.creator_id') . ' = ' . (int) $userid . ' AND ' . $db->qn('o.status') . ' = ' . $db->quote('C'));
			$query->where('DATE(' . $db->qn('o.cdate') . ')' . ' >= ' . $db->quote($backdate) . ' AND ' . 'DATE(' . $db->qn('o.cdate') . ')' . ' <= ' .
			$db->quote($todate)
			);
			$query->group($db->quote('YEARNAME'));
			$query->group('MONTHSNAME');
			$query->order($db->quote('YEAR( o.cdate )') . 'DESC');
			$query->order($db->quote('MONTH( o.cdate )') . 'DESC');

			$db->setQuery($query);

			$results = $db->loadObjectList();
		}

		return $results;
	}

	/**
	 * Function DashboardDropDownFilter
	 *
	 * @return Options
	 */
	public function dashboardDropDownOption()
	{
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '0', Text::_('COM_JGIVE_FILTER_SELECT_OPTION'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('COM_JGIVE_FILTER_LATEST'));
		$options[] = HTMLHelper::_('select.option', '2', Text::_('COM_JGIVE_FILTER_LAST_MONTH'));
		$options[] = HTMLHelper::_('select.option', '3', Text::_('COM_JGIVE_FILTER_LAST_YEAR'));

		return $options;
	}

	/**
	 * This function will return array of donations amount on basis of day,week,month
	 *
	 * @return Array donations amount array or total donation sum
	 *
	 * @since  2.5.0
	 */
	public function getFilterWiseDonationAmount()
	{
		$totalDonationAmount = array();
		$groupBy             = strtoupper($this->getState('filter.group_by'));

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(o.amount) as amount');

		$query->select('' . $groupBy . '(o.cdate) AS ' . strtolower($this->getState('filter.group_by')) . '');

		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_campaigns', 'c') . 'ON (' . $db->qn('c.id') . ' = ' . $db->qn('o.campaign_id') . ')');

		if (!empty($this->getState('filter.campaign_creator')))
		{
			$query->where($db->qn('c.creator_id') . ' = ' . (int) $this->getState('filter.campaign_creator'));
		}

		if (!empty($this->getState('filter.from_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' > ' . $db->quote($this->getState('filter.from_date')));
		}

		if (!empty($this->getState('filter.end_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' <= ' . $db->quote($this->getState('filter.end_date')));
		}

		$query->group('' . $groupBy . '(' . $db->quoteName('o.cdate') . ')');
		$query->order($db->quote('o.id') . 'DESC');
		$db->setQuery($query);

		$totalDonationAmount = $db->loadAssocList();

		return $totalDonationAmount;
	}

	/**
	 * This function will return sum of donations amount on basis of from date and end date
	 *
	 * @return float donations amount array or total donation sum
	 *
	 * @since  2.5.0
	 */
	public function getTotalDonationAmount()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(o.amount) as amount');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_campaigns', 'c') . 'ON (' . $db->qn('c.id') . ' = ' . $db->qn('o.campaign_id') . ')');

		if (!empty($this->getState('filter.campaign_creator')))
		{
			$query->where($db->qn('c.creator_id') . ' = ' . (int) $this->getState('filter.campaign_creator'));
		}

		if (!empty($this->getState('filter.from_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' >= ' . $db->quote($this->getState('filter.from_date')));
		}

		if (!empty($this->getState('filter.end_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' <= ' . $db->quote($this->getState('filter.end_date')));
		}

		$query->order($db->quote('o.id') . 'DESC');
		$db->setQuery($query);
		$totalDonationAmount = $db->loadResult();

		return ($totalDonationAmount == null) ? 0 : $totalDonationAmount;
	}

	/**
	 * This function will return array of donors count on basis of day,week,month
	 *
	 * @return Array
	 *
	 * @since  2.5.0
	 */
	public function getTotalDonorsCount()
	{
		$groupBy = strtolower($this->getState('filter.group_by'));
		$donors  = $this->getTotalDonorsByDonorType();

		$i = 0;
		$tempArr = array();
		$keyArr  = array();

		if (!empty($donors))
		{
			// Finding duplicate record in array, getting sum of duplicate array element and stored in temp Array
			foreach ($donors as $key => $donor)
			{
				if (!in_array($donor[$groupBy], $keyArr))
				{
					$keyArr[$i]  = $donor[$groupBy];
					$tempArr[$i] = $donor;
				}
				else
				{
					$pkey = array_search($donor[$groupBy], $keyArr);

					if (isset($pkey))
					{
						$tempArr[$pkey]['donor_count'] += $donor['donor_count'];
					}
					else
					{
						$keyArr[$i]  = $donor[$groupBy];
						$tempArr[$i] = $donor;
					}
				}

				$i++;
			}
		}

		return $tempArr;
	}

	/**
	 * This function will return array of Individual/Organization donors count on basis of day,week,month
	 *
	 * @return Array of donors counts
	 *
	 * @since  2.5.0
	 */
	public function getTotalDonorsByDonorType()
	{
		$groupBy        = strtoupper($this->getState('filter.group_by'));
		$filteredDonors = array();
		$db             = Factory::getDbo();
		$query          = $db->getQuery(true);
		$query->select('COUNT(d.contributor_id) as donor_count');

		if ($groupBy == 'DAY')
		{
			$query->select('DAY(o.cdate) AS day');
		}
		elseif ($groupBy == 'WEEK' || $groupBy == 'MONTH')
		{
			$query->select('' . $groupBy . '(o.cdate) AS ' . strtolower($this->getState('filter.group_by')) . '');
		}

		$query->select('GROUP_CONCAT(d.donor_type)');
		$query->from($db->qn('#__jg_donors', 'd'));
		$query->join('LEFT', '#__jg_orders AS o ON o.donor_id = d.id');
		$query->join('LEFT', '#__jg_campaigns AS c ON c.id = o.campaign_id');
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));

		if (!empty($this->getState('filter.campaign_creator')))
		{
			$query->where($db->quoteName('c.creator_id') . ' = ' . (int) $this->getState('filter.campaign_creator'));
		}

		if (!empty($this->getState('filter.from_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' >= ' . $db->quote($this->getState('filter.from_date')));
		}

		if (!empty($this->getState('filter.end_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' <= ' . $db->quote($this->getState('filter.end_date')));
		}

		if ($groupBy)
		{
			$query->group('' . $groupBy . '(' . $db->quoteName('o.cdate') . ')');
		}

		$query->group('d.contributor_id');
		$query->order($db->quote('o.id') . 'DESC');

		$db->setQuery($query);
		$filteredDonors = $db->loadAssocList();

		return $filteredDonors;
	}
}
