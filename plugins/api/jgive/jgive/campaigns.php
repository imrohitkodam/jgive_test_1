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
 * Class for getting Campaigns
 *
 * @package  JGive
 *
 * @since    _DEPLOY_VERSION_
 */
class JgiveApiResourceCampaigns extends ApiResource
{
	/**
	 *  API Plugin for get method
	 *
	 * @return  void
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function get()
	{
		$user = Factory::getUser();
		$input    = Factory::getApplication()->input;
		$result = new stdClass;
		$result->results = array();
		$result->empty_message = '';

		$limitstart = $input->get('limitstart', 0, 'INT');
		$limit = $input->get('limit', 10, 'INT');

		$campaignsModel = JGive::model('Campaigns', array('ignore_request' => true));
		$campaignsModel->setState('filter.creator_id', $user->id);
		$campaignsModel->setState('filter.success_status', $input->get('success_status', '0', 'STRING'));
		$campaignsModel->setState('filter_search', $input->get('search', '', 'STRING'));
		$campaignsModel->setState('filter_campaign_type', $input->get('type', '', 'STRING'));
		$campaignsModel->setState('filter_campaign_cat', $input->get('category', '', 'INT'));
		$campaignsModel->setState('list.ordering',  $input->get('ordering', 'camp.id', 'STRING'));
		$campaignsModel->setState('list.direction', $input->get('direction', 'desc', 'STRING'));
		$campaignsModel->setState('list.start', $limitstart);
		$campaignsModel->setState('list.limit', $limit);

		$this->items = $campaignsModel->getItems();

		$promoter = JGive::promoter();

		$result->total_amount           = $promoter->getReceivedAmount();
		$result->formatted_total_amount = $promoter->getReceivedAmount(true);
		$result->active_project_count   = $promoter->getCampaignCount('0');
		$result->total                  = $campaignsModel->getTotal();

		if (empty($this->items))
		{
			$result->empty_message	= Text::_('PLG_API_JGIVE_NO_DATA_FOUND');
			$this->plugin->setResponse($result);

			return;
		}

		$this->getApiItems();

		$result->results = $this->items;

		unset($this->items);
		$this->plugin->setResponse($result);
	}

	/**
	 * Method to process campaign data for API.
	 *
	 * @return  null
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	private function getApiItems()
	{
		if (!empty($this->items))
		{
			foreach ($this->items as $objCopy)
			{
				$campaign                           = JGive::campaign($objCopy->id);
				$promoter                           = JGive::promoter($objCopy->id);
				$objCopy->start_date                = $campaign->getStartDate(true);
				$objCopy->end_date	                = $campaign->getEndDate(true);
				$objCopy->donors_count              = $campaign->getDonorsCount();
				$objCopy->received_amount           = $campaign->getTotalAmount();
				$objCopy->formatted_received_amount = $campaign->getTotalAmount(true);
				$objCopy->formatted_goal_amount     = $campaign->getGoalAmount(true);
				$objCopy->donation_count            = $campaign->getDonationsCount();
				$objCopy->promoter                  = $promoter->getProperties();
				$objCopy->category                  = $campaign->getCategory();
				$objCopy->cover_image               = $campaign->getCoverImage();
				$objCopy->remaining_days            = $campaign->getRemainingDays();
				$objCopy->days_to_start             = $campaign->getDaysToStart();
			}
		}
	}
}
