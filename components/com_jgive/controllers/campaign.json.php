<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;

jimport('techjoomla.common');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');

/**
 * Campaign controller class
 *
 * @since  2.1.0
 */
class JGiveControllerCampaign extends JGiveController
{
	/**
	 * Method for getting Campaign specific donation and average donation data for showing graph
	 *
	 * @return   json
	 *
	 * since 2.1
	 */
	public function getCampaignGraphData()
	{
		$input            = Factory::getApplication()->input;
		$techjoomlacommon = new TechjoomlaCommon;
		$lastTwelveMonth  = $techjoomlacommon->getLastTwelveMonths();
		$campaignHelper   = new CampaignHelper;

		// Creating Object of FrontendHelper class
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		$duration = $input->get('filtervalue');
		$id       = $input->get('cid');

		$campDetailData = $campaignHelper->getCampaignDetails($id);

		$model = $this->getModel('campaign');

		$results = $model->getGraphData($duration, $id);

		$graphDuration = 0;
		$arraychunkvar = 0;
		$graphDataArr  = array();

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

			$todate        = Factory::getDate(date(Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3')))->Format(Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
			$backdate      = date(Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'), strtotime(date(Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3')) . ' - 1 year'));
			$graphDuration = $campaignHelper->getDateDiffInDays($backdate, $todate);
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
				$graphDataArr['donationDate'][$i] = date(Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'), strtotime($i . " days ago"));
				$graphDataArr['donationAvg'][$i]  = $totalDonationAmt / $graphDuration;

				if (!empty($results))
				{
					$resultCount = count($results);

					for ($j = 0; $j < $resultCount; $j++)
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
			$lastTwelveMonthCount = count($lastTwelveMonth);

			for ($i = 0; $i < $lastTwelveMonthCount; $i++)
			{
				$graphDataArr['donationDate'][$i] = $lastTwelveMonth[$i]['month'];
				$graphDataArr['donationAvg'][$i]  = $totalDonationAmt / $graphDuration;

				if (!empty($results))
				{
					$resultCount = count($results);

					for ($j = 0; $j < $resultCount; $j++)
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

		if ($campDetailData->type == 'donation')
		{
			$graphDataArr['totalDonation'] = Text::_("COM_JGIVE_CAMPAIGN_SINGLE_TOTAL_DONATION") .
			$jgiveFrontendHelper->getFormattedPrice($totalDonationAmt);
			$graphDataArr['avgDonation']   = Text::_("COM_JGIVE_CAMPAIGN_SINGLE_AVG_DONATION") .
			$jgiveFrontendHelper->getFormattedPrice($avgDonation);
		}
		else
		{
			$graphDataArr['totalDonation'] = Text::_("COM_JGIVE_CAMPAIGN_SINGLE_TOTAL_INVESTMENT") .
			$jgiveFrontendHelper->getFormattedPrice($totalDonationAmt);
			$graphDataArr['avgDonation']   = Text::_("COM_JGIVE_CAMPAIGN_SINGLE_AVG_INVESTMENT") .
			$jgiveFrontendHelper->getFormattedPrice($avgDonation);
		}

		if ($duration == 1)
		{
			$graphDonationAmount = array_chunk($graphDataArr['donationAmount'], $arraychunkvar);
			$graphDonationAmountNewArr = [];

			$graphDonationAvgAmount       = array_chunk($graphDataArr['donationAvg'], $arraychunkvar);
			$graphDonationAvgAmountCount  = count($graphDonationAmount);
			$graphDonationAvgAmountNewArr = [];

			for ($i = 0; $i < $graphDonationAvgAmountCount; $i++)
			{
				$graphDonationAmountNewArr[]    = array_sum($graphDonationAmount[$i]);
				$graphDataArr['donationAmount'] = $graphDonationAmountNewArr;

				// Avg Donation divide in chunk
				$graphDonationAvgAmountNewArr[] = array_sum($graphDonationAvgAmount[$i]);
				$graphDataArr['donationAvg']    = $graphDonationAvgAmountNewArr;
			}

			$graphDonationDate       = array_chunk($graphDataArr['donationDate'], $arraychunkvar);
			$graphDonationDateCount  = count($graphDonationDate);
			$graphDonationDateNewArr = [];

			for ($i = 0; $i < $graphDonationDateCount; $i++)
			{
				$graphDonationDateNewArr[]    = reset($graphDonationDate[$i]);
				$graphDataArr['donationDate'] = $graphDonationDateNewArr;
			}
		}

		$graphDataCount = count($graphDataArr['donationDate']);

		if ($duration == 0)
		{
			for ($k = 0; $k < $graphDataCount; $k++)
			{
				$graphDataArr['donationDate'][$k] = date("D", strtotime($graphDataArr['donationDate'][$k]));
			}
		}
		elseif ($duration == 1)
		{
			for ($k = 0; $k < $graphDataCount; $k++)
			{
				$graphDataArr['donationDate'][$k] = date("d/m", strtotime($graphDataArr['donationDate'][$k]));
			}
		}

		if ($duration == 0 || $duration == 1)
		{
			$graphDataArr['donationAvg']    = array_reverse($graphDataArr['donationAvg']);
			$graphDataArr['donationAmount'] = array_reverse($graphDataArr['donationAmount']);
			$graphDataArr['donationDate']   = array_reverse($graphDataArr['donationDate']);
		}

		echo new JResponseJson($graphDataArr, Text::_('COM_JGIVE_GRAPH_DATA'));
	}

	/**
	 * loads region/states according to selected country
	 * called via jquery ajax
	 *
	 * @return  void
	 */
	public function getState()
	{
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$country             = $this->input->get('country', '', 'INT');
		$defaultState        = array("id" => 0, "region" => Text::_('COM_JGIVE_STATE'),"region_jtext" => Text::_('COM_JGIVE_STATE'));

		// Use helper file function
		$state = $jgiveFrontendHelper->getState($country);

		if (!empty($state))
		{
			array_unshift($state, $defaultState);
		}

		echo new JResponseJson($state, Text::_('COM_JGIVE_STATE'));
	}

	/**
	 * loads city according to selected country
	 * called via jquery ajax
	 *
	 * @return  void
	 */
	public function getCity()
	{
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$country             = $this->input->get('country', '', 'INT');
		$defaultCity         = array("id" => 0, "city" => Text::_('COM_JGIVE_CITY'),"city_jtext" => Text::_('COM_JGIVE_CITY'));

		// Use helper file function
		$city = $jgiveFrontendHelper->getCity($country);

		if (!empty($city))
		{
			array_unshift($city, $defaultCity);
		}

		echo new JResponseJson($city, Text::_('COM_JGIVE_CITY'));
	}

	/**
	 * Show More donor of the campaign
	 *
	 * @return  json
	 *
	 * since 1.7
	 */
	public function showMoreDonors()
	{
		$input = Factory::getApplication()->input;
		$post  = $input->post;
		$cid         = $post->get('cid', '', 'INT');
		$jgive_index = $post->get('jgive_index', '', 'INT');

		$model  = $this->getModel('campaign');
		$result = $model->showMoreDonors($cid, $jgive_index);
		echo json_encode($result);
		jexit();
	}
}
