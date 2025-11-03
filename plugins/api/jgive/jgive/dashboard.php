<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('techjoomla.tjmoney.tjmoney');

/**
 * Dashboard API class
 *
 * @since  2.5.0
 */
class JgiveApiResourceDashboard extends ApiResource
{
	/**
	 * Get User Dashboard Information of JGive
	 *
	 * @return  json dashboard information
	 *
	 * @since   2.5.0
	 */
	public function get()
	{
		$resultArr = new stdclass;
		$input = Factory::getApplication()->input;
		$user  = Factory::getUser();

		if (empty($user->id))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_INVALID_USER"));
		}

		$params         = Jgive::config();
		$tjCurrency     = new TjMoney($params->get('currency'));
		$currencySymbol = $tjCurrency->getSymbol();
		$resultArr->results['currencySymbol'] = $currencySymbol;

		// Get array of donation amount basis on date and duration for graph
		$fromDate     = $input->get('from_date', '', 'STRING');
		$endDate      = $input->get('end_date', '', 'STRING');
		$groupBy      = $input->get('group_by', '', 'STRING');
		$previousDatesArray = JGive::utilities()->getPreviousDates(array("from_date"=>$fromDate, "end_date"=>$endDate, "group_by"=>$groupBy));
		$dashboardModelObj = JGive::model("Dashboard", array('ignore_request' => true));
		$dashboardModelObj->setState('filter.from_date', $fromDate, 'STRING');
		$dashboardModelObj->setState('filter.end_date', $endDate, 'STRING');
		$dashboardModelObj->setState('filter.group_by', $groupBy, 'STRING');
		$dashboardModelObj->setState('filter.campaign_creator', $user->id, 'INT');
		$resultArr->results['filtered_amount'] = $dashboardModelObj->getFilterWiseDonationAmount();

		// Get total donation amount basis on only date
		$dashboardModelObj = JGive::model("Dashboard", array('ignore_request' => true));
		$dashboardModelObj->setState('filter.from_date', $fromDate, 'STRING');
		$dashboardModelObj->setState('filter.end_date', $endDate, 'STRING');
		$dashboardModelObj->setState('filter.campaign_creator', $user->id, 'INT');
		$resultArr->results['total_donation'] = $dashboardModelObj->getTotalDonationAmount();
		$resultArr->results['filtered_total_donation'] = JGive::utilities()->getFormattedPrice($dashboardModelObj->getTotalDonationAmount());

		/*Previous Donations Amount*/
		$dashboardModelObj = JGive::model("Dashboard", array('ignore_request' => true));
		$dashboardModelObj->setState('filter.from_date', $previousDatesArray['from_date'], 'STRING');
		$dashboardModelObj->setState('filter.end_date', $previousDatesArray['end_date'], 'STRING');
		$dashboardModelObj->setState('filter.campaign_creator', $user->id, 'INT');
		$resultArr->results['previous_total_donation'] = $dashboardModelObj->getTotalDonationAmount();

		// Get array of donors basis on date and duration for graph
		$dashboardModelObj = JGive::model("Dashboard", array('ignore_request' => true));
		$dashboardModelObj->setState('filter.from_date', $fromDate, 'STRING');
		$dashboardModelObj->setState('filter.end_date', $endDate, 'STRING');
		$dashboardModelObj->setState('filter.group_by', $groupBy, 'STRING');
		$dashboardModelObj->setState('filter.campaign_creator', $user->id, 'INT');

		$getTotalDonors = $dashboardModelObj->getTotalDonorsCount();
		$resultArr->results['filtered_donors'] = $getTotalDonors;

		// Get total donors count basis on date only
		$totalDonors = 0;

		foreach ($getTotalDonors as $key => $donor)
		{
			$totalDonors += $donor['donor_count'];
		}

		$resultArr->results['total_donors'] = $totalDonors;

		/*Previous Donor count*/
		$dashboardModelObj = JGive::model("Dashboard", array('ignore_request' => true));
		$dashboardModelObj->setState('filter.from_date', $previousDatesArray['from_date'], 'STRING');
		$dashboardModelObj->setState('filter.end_date', $previousDatesArray['end_date'], 'STRING');
		$dashboardModelObj->setState('filter.group_by', $groupBy, 'STRING');
		$dashboardModelObj->setState('filter.campaign_creator', $user->id, 'INT');

		$getTotalDonors = $dashboardModelObj->getTotalDonorsCount();
		$previousTotalDonors = 0;

		foreach ($getTotalDonors as $key => $donor)
		{
			$previousTotalDonors += $donor['donor_count'];
		}

		$resultArr->results['previous_total_donors'] = $previousTotalDonors;

		// Get Active campaingns data
		$campaignsModelObj = JGive::model("Campaigns", array('ignore_request' => true));
		$campaignsModelObj->setState('filter.creator_id', $user->id, 'INT');
		$campaignsModelObj->setState('filter.success_status', '0', 'STRING');
		$campaignsModelObj->setState('displayPins', 1);
		$campaignsModelObj->setState('list.limit', 5, 'INT');
		$this->items = $campaignsModelObj->getItems();
		$this->getApiItems();

		$resultArr->results['campaigns'] = $this->items;
		$resultArr->results['totalCampaigns'] = $campaignsModelObj->getTotal();

		$this->plugin->setResponse($resultArr);
	}

	/**
	 * Method to process donations data for API.
	 *
	 * @return  null
	 *
	 * @since   2.5.0
	 */
	private function getApiItems()
	{
		if (!empty($this->items))
		{
			foreach ($this->items as $key => $item)
			{
				$campaign = JGive::campaign($item['id']);
				$this->items[$key]['start_date'] = JGive::utilities()->getFormattedDate($item['start_date']);
				$this->items[$key]['end_date']   = JGive::utilities()->getFormattedDate($item['end_date']);
				$this->items[$key]['filtered_goal_amount'] = JGive::utilities()->getFormattedPrice($item['goal_amount']);
				$this->items[$key]['filtered_amount_received'] = JGive::utilities()->getFormattedPrice($item['amount_received']);
				$this->items[$key]['remaining_days']            = $campaign->getRemainingDays();
				$this->items[$key]['days_to_start']             = $campaign->getDaysToStart();
			}
		}
	}
}
