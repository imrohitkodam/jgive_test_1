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
 * Class for getting Campaign details
 *
 * @package  JGive
 *
 * @since    _DEPLOY_VERSION_
 */
class JgiveApiResourceCampaign extends ApiResource
{
	/**
	 * Get Campaign details
	 *
	 * @return  void
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function get()
	{
		$input = Factory::getApplication()->input;
		$id = $input->getInt('id', 0);

		$data = array();
		$result = new stdClass;
		$result->results = array();
		$result->empty_message = '';

		if (empty($id))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_INVALID_CAMPAIGN_ID"));
		}

		$campaign = JGive::campaign($id);
		$data = $campaign->getProperties();

		if (!$data['id'])
		{
			$result->empty_message	= Text::_('PLG_API_JGIVE_NO_DATA_FOUND');
			$this->plugin->setResponse($result);

			return;
		}

		$promoter                                = JGive::promoter($id);
		$data['cover_image']                     = $campaign->getCoverImage();
		$data['category']                        = $campaign->getCategory();
		$data['promoter']                        = $promoter->getProperties();
		$data['success_status']                  = $campaign->getSuccessStatus();
		$data['goal_amount']                     = $data['goal_amount'];
		$data['formatted_goal_amount']           = $campaign->getGoalAmount(true);
		$data['minimum_amount']                  = $data['minimum_amount'];
		$data['formatted_minimum_amount']        = $campaign->getMinimumAmount(true);
		$data['total_donors_count']              = $campaign->getDonorsCount();
		$data['total_amount_received']           = $campaign->getTotalAmount();
		$data['formatted_total_amount_received'] = $campaign->getTotalAmount(true);
		$data['formatted_start_date']            = $campaign->getStartDate(true);
		$data['formatted_end_date']	             = $campaign->getEndDate(true);
		$data['formatted_created_date']	         = $campaign->getCreatedDate(true);

		$result->results = $data;
		$this->plugin->setResponse($result);
	}
}
