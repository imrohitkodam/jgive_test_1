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

/**
 * Donations API class
 *
 * @since  2.5.0
 */
class JgiveApiResourceDonations extends ApiResource
{
	protected $items = array();

	/**
	 * Get Donations list
	 *
	 * @return  json donations list
	 *
	 * @since   2.5.0
	 */
	public function get()
	{
		$resultArr      = new stdclass;
		$user           = Factory::getUser();
		$input          = Factory::getApplication()->input;
		$donationStatus = COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED;
		$fromDate       = $input->get('from_date', '', 'STRING');
		$endDate        = $input->get('end_date', '', 'STRING');

		// Get total donations sum of my all campaigns of specified date range
		$ordersModelObj = JGive::model('orders', array('ignore_request' => true));
		$ordersModelObj->setState("filter.campaign_creator_id", $user->id, 'INT');
		$ordersModelObj->setState("filter.status", $donationStatus, 'STRING');
		$ordersModelObj->setState("filter.from_date", $fromDate, 'STRING');
		$ordersModelObj->setState("filter.end_date", $endDate, 'STRING');
		$resultArr->results['filtered_amount'] = $ordersModelObj->getDonationAmountSum();
		$resultArr->results['formatted_filtered_amount'] = JGive::utilities()->getFormattedPrice($ordersModelObj->getDonationAmountSum());

		// Get total donations sum of my all campaigns
		$ordersModelObj = JGive::model('orders', array('ignore_request' => true));
		$ordersModelObj->setState("filter.campaign_creator_id", $user->id, 'INT');
		$ordersModelObj->setState("filter.status", $donationStatus, 'STRING');
		$resultArr->results['total_amount'] = $ordersModelObj->getDonationAmountSum();
		$resultArr->results['formatted_total_amount'] = JGive::utilities()->getFormattedPrice($ordersModelObj->getDonationAmountSum());

		// Get donations record on basis of filters
		$limit            = $input->get('limit', 20, 'INT');
		$limitStart       = $input->get('start', 0, 'INT');
		$orderBy          = $input->get('ordering', 'o.id', 'STRING');
		$orderByDirection = $input->get('direction', 'desc', 'STRING');
		$search           = $input->get('search', '', 'STRING');

		$ordersModelObj   = JGive::model('orders', array('ignore_request' => true));
		$ordersModelObj->setState("filter.campaign_id", $input->get('campaign_id', '', 'INT'));
		$ordersModelObj->setState("filter.campaign_creator_id", $user->id, 'INT');
		$ordersModelObj->setState("filter.donor_type", $input->get('donor_type', '', 'STRING'));
		$ordersModelObj->setState("filter.contributor_id", $input->get('contributor_id', '', 'INT'));
		$ordersModelObj->setState("filter.status", $donationStatus, 'STRING');
		$ordersModelObj->setState("filter.from_date", $fromDate, 'STRING');
		$ordersModelObj->setState("filter.end_date", $endDate, 'STRING');
		$ordersModelObj->setState("filter.search", $search, 'STRING');
		$ordersModelObj->setState("list.ordering",  $orderBy, 'STRING');
		$ordersModelObj->setState("list.direction", $orderByDirection, 'STRING');
		$ordersModelObj->setState("list.start", $limitStart, 'INT');
		$ordersModelObj->setState("list.limit", $limit, 'INT');

		$this->items = $ordersModelObj->getItems();

		// Process data for APIs
		$this->getApiItems();
		$resultArr->results['donations'] = $this->items ? $this->items : array();

		// Get total donations count
		$resultArr->results['total'] = $ordersModelObj->getTotal();

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
				$donationObj                              = JGive::donation($item->donation_id);
				$this->items[$key]->cdate                 = JGive::utilities()->getFormattedDate($item->cdate);
				$this->items[$key]->mdate                 = JGive::utilities()->getFormattedDate($item->mdate);
				$this->items[$key]->payment_received_date = JGive::utilities()->getFormattedDate($item->payment_received_date);
				$this->items[$key]->original_amount       = JGive::utilities()->getFormattedPrice($item->original_amount);
				$this->items[$key]->amount                = JGive::utilities()->getFormattedPrice($item->amount);
				$this->items[$key]->fee                   = JGive::utilities()->getFormattedPrice($item->fee);
				$this->items[$key]->processor             = JGive::utilities()->getPaymentGatewayName($item->processor);
				$this->items[$key]->isRecurring           = $donationObj->isRecurring();
			}
		}
	}
}
