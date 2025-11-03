<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once JPATH_COMPONENT . '/controller.php';

jimport('techjoomla.common');

/**
 * Controller for single form view
 *
 * @since  1.5.19
 */

class JgiveControllerDashboard extends jgiveController
{
	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$this->techjoomlacommon = new TechjoomlaCommon;

		parent::__construct();
	}

	/**
	 * Method for getting Campaigns donation and average donation data for showing graph
	 *
	 * @return   json
	 *
	 * since 2.0
	 */
	public function getDashboardGraphData()
	{
		$input    = Factory::getApplication()->input;

		// Creating Object of FrontendHelper class
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		$this->techjoomlacommon = new TechjoomlaCommon;
		$lastTwelveMonth = $this->techjoomlacommon->getLastTwelveMonths();

		require_once JPATH_SITE . "/components/com_jgive/helpers/campaign.php";
		$campaignHelper = new campaignHelper;

		$duration = $input->get('filtervalue');
		$userid = $input->get('userId');

		$model = $this->getModel('dashboard');

		$results = $model->getDashboardGraphData($duration, $userid);

		if ($duration == 0)
		{
			$graphDuration = 7;
		}
		elseif ($duration == 1)
		{
			$graphDuration = 30;
			$arraychunkvar = 7;
		}
		elseif ($duration == 2)
		{
			$arraychunkvar = 30;

			$todate = Factory::getDate(date('Y-m-d'))->Format(Text::_('Y-m-d'));
			$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 1 year'));
			$graphDuration  = $campaignHelper->getDateDiffInDays($backdate, $todate);
		}

		$totalDonationAmt = 0;

		foreach ($results as $result)
		{
			$totalDonationAmt += $result->donation_amount;
		}

		if ($duration == 0 || $duration == 1)
		{
			for ($i = 0; $i < $graphDuration; $i++)
			{
				$graphDataArr['donationDate'][$i] = date("Y-m-d", strtotime($i . " days ago"));
				$graphDataArr['donationAvg'][$i] = $totalDonationAmt / $graphDuration;

				if (!empty($results))
				{
					for ($j = 0; $j < count($results); $j++)
					{
						if ($graphDataArr['donationDate'][$i] == $results[$j]->cdate)
						{
							$graphDataArr['donationAmount'][$i] = $results[$j]->donation_amount;

							break;
						}
						else
						{
							$graphDataArr['donationAmount'][$i] = "0";
						}
					}
				}
				else
				{
					$graphDataArr['donationAmount'][$i] = "0";
				}
			}
		}
		elseif ($duration == 2)
		{
			for ($i = 0; $i < count($lastTwelveMonth); $i++)
			{
				$graphDataArr['donationDate'][$i] = $lastTwelveMonth[$i]['month'];
				$graphDataArr['donationAvg'][$i] = $totalDonationAmt / $graphDuration;

				if (!empty($results))
				{
					for ($j = 0; $j < count($results); $j++)
					{
						$monthNum  = $results[$j]->MONTHSNAME;
						$dateObj   = DateTime::createFromFormat('!m', $monthNum);
						$monthName = $dateObj->format('F');

						if ($lastTwelveMonth[$i]['month'] == $monthName)
						{
							$graphDataArr['donationAmount'][$i] = $results[$j]->donation_amount;
							break;
						}
						else
						{
							$graphDataArr['donationAmount'][$i] = "0";
						}
					}
				}
				else
				{
					$graphDataArr['donationAmount'][$i] = "0";
				}
			}
		}

		$avgDonation = $totalDonationAmt / $graphDuration;
		$graphDataArr['totalDonation'] = Text::_('COM_JGIVE_DASHBOARD_GRAPH_TOTAL_AMOUNT') . ": " .
		$jgiveFrontendHelper->getFormattedPrice($totalDonationAmt);
		$graphDataArr['avgDonation'] = Text::_('COM_JGIVE_DASHBOARD_GRAPH_AVERAGE_AMOUNT') . ": " . $jgiveFrontendHelper->getFormattedPrice($avgDonation);

		if ($duration == 1)
		{
			$graphDonationAmount = array_chunk($graphDataArr['donationAmount'], $arraychunkvar);
			$graphDonationAmountNewArr = [];

			$graphDonationAvgAmount = array_chunk($graphDataArr['donationAvg'], $arraychunkvar);
			$graphDonationAvgAmountNewArr = [];

			for ($i = 0; $i < count($graphDonationAmount); $i++)
			{
				// Donation divide in chunk
				$graphDonationAmountNewArr[] = array_sum($graphDonationAmount[$i]);
				$graphDataArr['donationAmount'] = $graphDonationAmountNewArr;

				// Avg Donation divide in chunk
				$graphDonationAvgAmountNewArr[] = array_sum($graphDonationAvgAmount[$i]);
				$graphDataArr['donationAvg'] = $graphDonationAvgAmountNewArr;
			}

			$graphDonationDate = array_chunk($graphDataArr['donationDate'], $arraychunkvar);
			$graphDonationDateNewArr = [];

			for ($i = 0; $i < count($graphDonationDate); $i++)
			{
				$graphDonationDateNewArr[] = reset($graphDonationDate[$i]);
				$graphDataArr['donationDate'] = $graphDonationDateNewArr;
			}
		}

		if ($duration == 0)
		{
			for ($k = 0; $k < count($graphDataArr['donationDate']); $k++)
			{
				$graphDataArr['donationDate'][$k] = date("D", strtotime($graphDataArr['donationDate'][$k]));
			}
		}
		elseif ($duration == 1)
		{
			for ($k = 0; $k < count($graphDataArr['donationDate']); $k++)
			{
				$graphDataArr['donationDate'][$k] = date("d/m", strtotime($graphDataArr['donationDate'][$k]));
			}
		}

		if ($duration == 0 || $duration == 1)
		{
			$graphDataArr['donationAvg'] = array_reverse($graphDataArr['donationAvg']);
			$graphDataArr['donationAmount'] = array_reverse($graphDataArr['donationAmount']);
			$graphDataArr['donationDate'] = array_reverse($graphDataArr['donationDate']);
		}

		echo json_encode($graphDataArr);
		jexit();
	}
}
