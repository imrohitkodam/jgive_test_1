<?php
/**
 * @package     JGive
 * @subpackage  API Plugin
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Json;


/**
 * Single Order API class
 *
 * @since  2.5.0
 */
class JgiveApiResourceOrder extends ApiResource
{
	/**
	 * Get Single Order Information
	 *
	 * @return  json order information
	 *
	 * @since   2.5.0
	 */
	public function get()
	{
		$resultArr = new stdclass;

		$input   = Factory::getApplication()->input;
		$orderId = $input->get('id', 0, 'INT');

		if (empty($orderId))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_ORDER_ID_NOT_SET"));
		}

		// Get Order Details
		$orderObj  = JGive::model('order');
		$order     = $orderObj->getItem($orderId);

		if ($order['id'] == 0)
		{
			ApiError::raiseError(200, Text::_("PLG_API_JGIVE_NO_DATA_FOUND"));
		}

		$resultArr->results = $order;

		if (!empty($order))
		{
			// Get Campaign Details
			$campaignClassObj                      = JGive::campaign($order['campaign_id']);
			$campaign                              = $campaignClassObj->getProperties();
			$campaign['goal_amount_formatted']     = $campaignClassObj->getGoalAmount(true);
			$campaign['recevied_amount_formatted'] = $campaignClassObj->getTotalAmount(true);
			$campaign['start_date_formatted']      = $campaignClassObj->getStartDate(true);
			$campaign['end_date_formatted']        = $campaignClassObj->getEndDate(true);
			$campaign['percent_amount_formatted']  = $campaignClassObj->getTotalAmountInPercentage();
			$campaign['total_donors_formatted']    = $campaignClassObj->getDonorsCount();

			// Get Campaign cover image
			$campaignCoverImg               = $campaignClassObj->getCoverImage('media_m');
			$bin                            = file_get_contents($campaignCoverImg);
			$campaign['cover_image']        = base64_encode($bin);
			$resultArr->results['campaign'] = $campaign;

			// Get Donation Details
			$donationClassObj               = JGive::donation($order['donation_id']);
			$donation                       = $donationClassObj->getProperties();
			$resultArr->results['donation'] = $donation;

			// Get Donor Details
			$donorObj = JGive::model('donor');
			$donor = $donorObj->getData($order['donor_id']);
			$resultArr->results['donor'] = $donor;
		}

		$this->plugin->setResponse($resultArr);
	}

	/**
	 * Add order
	 *
	 * @return  boolean
	 *
	 * @since   2.5.0
	 */
	public function post()
	{
		$lang     = Factory::getLanguage();
		$lang->load('com_jgive', JPATH_SITE);

		$json = Factory::getApplication()->input->json;
		$data = $json->get('request', array(), 'ARRAY');

		$resultArr = new stdClass;
		$resultArr->result = array();

		if (empty($data))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_NO_DATA_FOUND"));
		}

		// Check campaign id is valid or not

		if (empty($data['campaign_id']))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_INVALID_CAMPAIGN_ID"));
		}

		$campaign = JGive::campaign($data['campaign_id']);
		$campaignData = $campaign->getProperties();

		if (!$campaignData['id'])
		{
			$resultArr->empty_message	= Text::_('PLG_API_JGIVE_NO_DATA_FOUND');
			$this->plugin->setResponse($resultArr);

			return;
		}

		$donorInfo                     = explode('.', $data['donorid']);
		$data['donor_type']            = $donorInfo[0];
		$data['contributor_id']        = $donorInfo[1];

		if ($data['donation_type'])
		{
			$data['is_recurring'] = 1;
		}

		$donationFormModel = JGive::model('donationform', array('ignore_request' => true));

		Form::addFormPath(JPATH_SITE . '/components/com_jgive/models/forms');
		$form = $donationFormModel->getForm();
		$formData = $donationFormModel->validate($form, $data);

		if ($formData === false)
		{
			// Handle how to handle the multiple errors in api
			$errors = $donationFormModel->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i ++)
			{
				if ($errors[$i] instanceof Exception)
				{
					ApiError::raiseError(200, $errors[$i]->getMessage());
				}
				else
				{
					ApiError::raiseError(200, $errors[$i]);
				}
			}

			return false;
		}

		$donationClass = JGive::donation();
		$result        = $donationClass->addDonation($data);

		if ($result)
		{
			$resultArr->result['status']  = 1;
			$resultArr->result['id']      = $result;
			$resultArr->result['message'] = Text::_("PLG_API_JGIVE_ORDER_ADDED_SUCCESSFULLY");
		}
		else
		{
			ApiError::raiseError(200, $donationClass->getError());
		}

		$this->plugin->setResponse($resultArr);
	}
}
