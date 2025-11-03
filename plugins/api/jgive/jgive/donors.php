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
 * Class for getting donors list
 *
 * @package  JGive
 *
 * @since    2.5.0
 */
class JgiveApiResourceDonors extends ApiResource
{
	/* @TODO: projects donated to (will do after merging campigns api)
	   @TODO: get all donations   (will do after merging donations api)*/

	protected $items = array();
	/**
	 *  API Plugin for get method
	 *
	 * @return  donors list
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function get()
	{
		$userId                  = Factory::getUser()->id;
		$input                   = Factory::getApplication()->input;
		$result                  = new stdClass;
		$result->results         = array();
		$result->new_donor_count = '';

		if (empty($userId))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_USER_ID_NOT_SET"));
		}

		$limitstart = $input->get('limitstart', 0, 'INT');
		$limit = $input->get('limit', 10, 'INT');

		$donorsModel = JGive::model('donors', array('ignore_request' => true));
		$donorsModel->setState("filter.creator_id", $userId);
		$donorsModel->setState('list.ordering',  $input->get('ordering', 'a.id', 'STRING'));
		$donorsModel->setState('list.direction', $input->get('direction', 'desc', 'STRING'));
		$donorsModel->setState('filter.from_date', $input->get('from_date', '', 'STRING'));
		$donorsModel->setState('filter.end_date', $input->get('end_date', '', 'STRING'));
		$donorsModel->setState('list.start', $limitstart);
		$donorsModel->setState('list.limit', $limit);
		$donorsModel->setState('filter.search', $input->get('search', '', 'STRING'));
		$donorsModel->setState('filter.donor_type', $input->get('donor_type', '', 'STRING'));
		$donorsModel->setState('filter.group_by', $input->get('group_by', '', 'STRING'));

		$result->new_donor_count = $donorsModel->getNewDonorCount();

		$this->items   = $donorsModel->getItems();
		$result->total = $donorsModel->getTotal();

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
	 * Method to process donor data for API.
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
				$donorClass = JGive::donor($objCopy->id);
				$objCopy->total_donated_amount = $donorClass->getTotalDonatedAmount();
				$objCopy->formatted_total_donated_amount = $donorClass->getTotalDonatedAmount(true);
				$objCopy->recent_donated_amount = $donorClass->recentDonatedAmount();
				$objCopy->campaigns = $donorClass->getDonatedCampaigns();
				$objCopy->total_donations_count = $donorClass->getAllDonationsCount();
			}
		}
	}
}
