<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;

JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);

/**
 * Item Model for donation.
 *
 * @since  1.6
 */
class JgiveModelDonation extends ItemModel
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 *
	 * @return void
	 */
	protected function populateState()
	{
		// Load the parameters.
		$params = ComponentHelper::getParams('com_jgive');

		$input = Factory::getApplication()->input;

		// Get donation id from URL and set into model state
		$donationId = $input->get('donationid', '', 'INT');

		if (!empty($donationId))
		{
			$this->setState('donationid', $donationId);
		}

		// Get guest email id from the URL and set into model state
		$guestEmail = $input->get('email', '', 'STRING');

		if (!empty($guestEmail))
		{
			$this->setState('guestemail', $guestEmail);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get donation details
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	ARRAY|BOOLEAN	donation details on success, false on failure.
	 *
	 * @since	2.2.0
	 */
	public function getItem($pk = null)
	{
		// If id is not passed from function parameter then check in the model state
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('donationid');

		// Include JGive tables
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');

		// Get donation related data from orders table
		$orderData = Table::getInstance('Orders', 'JGiveTable');
		$orderData->load(array('id' => $pk));

		if (!$orderData->id)
		{
			return false;
		}

		// Get donation related data from donations table
		$donation = Table::getInstance('Donations', 'JGiveTable');
		$donation->load(array('id' => $orderData->donation_id));

		// Get donor's details
		$donor = Table::getInstance('Donor', 'JGiveTable');
		$donor->load(array('id' => $donation->donor_id));

		// Get country, state, city name of donor
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$donor->country_name = $jgiveFrontendHelper->getCountryNameFromId($donor->country);
		$donor->state_name   = $jgiveFrontendHelper->getRegionNameFromId($donor->state, $donor->country);
		$donor->city_name    = $jgiveFrontendHelper->getCityNameFromId($donor->city, $donor->country);

		// If city name is missing maybe it's some other city (city name added in other city inputbox)
		if (!$donor->city_name)
		{
			$donor->city_name = $donor->city;
		}

		// Variable to store data of campaign for which donation is made
		$cdata = array();

		// Get campaign data for which donation is made
		$campaignData = Table::getInstance('Campaign', 'JGiveTable');
		$campaignData->load(array('id' => $orderData->campaign_id));
		$cdata['campaign'] = $campaignData;

		// Get total amount of donation received for the campaign
		JLoader::import('models.campaign', JPATH_SITE . '/components/com_jgive');
		$jgiveCampaignModel = new JgiveModelCampaign;

		$cdata['campaign']->amount_received = (int) $jgiveCampaignModel->getRaisedFunds($orderData->campaign_id);

		// Get total donation amount
		$donationAmount = Table::getInstance('Orders', 'JGiveTable');
		$donationAmount->load(array('campaign_id' => (int) $orderData->campaign_id, 'status' => 'C', 'id' => (int) $pk));
		$cdata['campaign']->donation_amount = $donationAmount->original_amount;

		// Get remaining amount
		$cdata['campaign']->remaining_amount = ($cdata['campaign']->goal_amount) - ($cdata['campaign']->amount_received);

		// Get campaign main image
		$com_params  = ComponentHelper::getParams('com_jgive');
		$storagePath = $com_params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

		JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
		$filetable = Table::getInstance('Files', 'TJMediaTable');

		JLoader::import('mediaxref', JPATH_SITE . '/components/com_jgive/models');
		$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');

		// Getting campaign main image data
		$campaignMainImage = $modelMediaXref->getCampaignMedia($cdata['campaign']->id, 'com_jgive.campaign', 0);

		if (!empty($campaignMainImage))
		{
			$filetable->load($campaignMainImage[0]->media_id);
			$mediaType   = explode(".", $filetable->type);
			$imgPath     = $storagePath . '/' . $mediaType[0] . 's';
			$mediaConfig = array('id' => $campaignMainImage[0]->media_id, 'uploadPath' => $imgPath);
			$cdata['campaign']->image = TJMediaStorageLocal::getInstance($mediaConfig);
		}

		// Authorization checks
		$user   = Factory::getUser();
		$params = $this->getState('params');

		$authorized = false;

		// Check if user is authorized to view the donation details
		if (($donor->user_id == $user->id)
			|| ($donor->user_id == $campaignData->creator_id)
			|| ($user->authorise('core.admin'))
			|| ($user->id == $campaignData->creator_id)
			|| ($this->getState('dataForactivity', '')))
		{
			$authorized = true;
		}
		else
		{
			if ($params->get('guest_donation'))
			{
				$guestEmail = $this->getState('guestemail');

				if ($guestEmail == md5($donor->email))
				{
					$authorized = true;
				}
			}
		}

		if (!$authorized)
		{
			throw new Exception(Text::_('COM_JGIVE_AUTH_ERROR'), 403);
		}

		// Variable in which donation data will be returned
		$item             = array();
		$item['donor']    = $donor;
		$item['campaign'] = $cdata['campaign'];
		$item['payment']  = $this->getPaymentDetails($pk);

		return $item;
	}

	/**
	 * Function to get payment details for the donation
	 *
	 * @param   INT  $orderId  Order id key
	 *
	 * @return  Donors
	 *
	 * @since   2.2.0
	 */
	public function getPaymentDetails($orderId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('o.*');
		$query->select($db->qn(array('d.giveback_id', 'd.annonymous_donation', 'd.is_recurring', 'd.recurring_frequency', 'd.recurring_count')));
		$query->select($db->qn('g.description', 'giveback_desc'));
		$query->select($db->qn('g.title', 'giveback_title'));
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_donations', 'd') . 'ON (' . $db->qn('o.donation_id') . ' = ' . $db->qn('d.id') . ')');
		$query->join('LEFT', $db->qn('#__jg_campaigns_givebacks', 'g') . 'ON (' . $db->qn('d.giveback_id') . ' = ' . $db->qn('g.id') . ')');
		$query->where($db->qn('o.id') . ' = ' . (int) $orderId);
		$db->setQuery($query);

		return $db->loadObject();
	}
}
