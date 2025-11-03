<?php
/**
 * @package     JGive
 * @subpackage  JGive API plugin
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Class for getting donors week, month, year and all count 
 * getting donations week, month, year and all amount 
 *
 * @package  JGive
 *
 * @since    __DELPOY_VERSION__
 */
class JgiveApiResourceDatefilter extends ApiResource
{
	/**
	 *  API Plugin for get method
	 *
	 * @return  donors, donations count
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function get()
	{
		$userId          = Factory::getUser()->id;
		$input           = Factory::getApplication()->input;
		$result          = new stdClass;
		$result->results = array();

		if (empty($userId))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_USER_ID_NOT_SET"));
		}

		$params         = Jgive::config();
		$tjCurrency     = new TjMoney($params->get('currency'));
		$currencySymbol = $tjCurrency->getSymbol();
		$type           = $input->get('type', '', 'STRING');
		$result->results['currencySymbol'] = $currencySymbol;

		$date = new DateTime();
		$week = $date->format("W");
		$year = $date->format("Y");

		$date->setISODate($year, $week);
		$datesArray['week']['from_date'] = $date->format('Y-m-d 00:00:00');
		$date->modify('+6 days');
		$datesArray['week']['end_date'] = $date->format('Y-m-d 23:59:59');
		$datesArray['previous_week']    = JGive::utilities()->getPreviousDates(array("from_date"=>$weekFromDate, "end_date"=>$weekEndDate, "group_by"=>"day"));

		$datesArray['month']['from_date'] = date("Y-m-01 00:00:00");
		$datesArray['month']['end_date']  = date("Y-m-t 23:59:59");
		$datesArray['previous_month']     = JGive::utilities()->getPreviousDates(array("from_date"=>date("Y-m-01 00:00:00"), "end_date"=>date("Y-m-t 23:59:59"), "group_by"=>"week"));

		$datesArray['year']['from_date'] = date("Y") . '-01-01 00:00:00';
		$datesArray['year']['end_date']  = date("Y") . '-12-31 23:59:59';
		$datesArray['previous_year']     = JGive::utilities()->getPreviousDates(array("from_date"=>date("Y") . '-01-01 00:00:00', "end_date"=>date("Y") . '-12-31 23:59:59', "group_by"=>"month"));;

		if (strtolower($type) == 'donors')
		{
			// Donors
			foreach ($datesArray as $key => $values)
			{
				if ($key == 'week' || $key == 'previous_week')
				{
					$group_by = 'week';
				}
				elseif ($key == 'month' || $key == 'previous_month')
				{
					$group_by = 'month';
				}
				else
				{
					$group_by = 'year';
				}

				$donorsModel = JGive::model('donors', array('ignore_request' => true));
				$donorsModel->setState("filter.creator_id", $userId);
				$donorsModel->setState('filter.from_date', $values['from_date']);
				$donorsModel->setState('filter.end_date', $values['end_date']);
				$donors->$key = $donorsModel->getTotal();
			}

			$result->results['donors']    = $donors;
			$donorsModel = JGive::model('donors', array('ignore_request' => true));
			$donorsModel->setState("filter.creator_id", $userId);
			$totalDonors = $donorsModel->getTotal();
			$result->results['donors']->total_donors = $totalDonors;
		}

		if (strtolower($type) == 'donations')
		{
			// Donations
			foreach ($datesArray as $key => $values)
			{
				$dashboardModelObj = JGive::model("Dashboard", array('ignore_request' => true));
				$dashboardModelObj->setState('filter.from_date', $values['from_date'], 'STRING');
				$dashboardModelObj->setState('filter.end_date', $values['end_date'], 'STRING');
				$dashboardModelObj->setState('filter.campaign_creator', $userId, 'INT');
				$donations->$key = (float) $dashboardModelObj->getTotalDonationAmount();
			}

			$result->results['donations'] = $donations;
			$dashboardModelObj = JGive::model("Dashboard", array('ignore_request' => true));
			$dashboardModelObj->setState('filter.campaign_creator', $userId, 'INT');
			$totalDonation = $dashboardModelObj->getTotalDonationAmount();
			$result->results['donations']->total_donation = (float) $totalDonation;
		}

		$this->plugin->setResponse($result);
	}
}
