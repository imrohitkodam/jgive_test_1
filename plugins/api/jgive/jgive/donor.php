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
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Json;

/**
 * Single Order API class
 *
 * @since  2.5.0
 */
class JgiveApiResourceDonor extends ApiResource
{
	/**
	 * Get Single Donor Information
	 *
	 * @return  json order information
	 *
	 * @since   2.5.0
	 */
	public function get()
	{
		$input = Factory::getApplication()->input;
		$id    = $input->getInt('id', 0);
		$type  = $input->get('type', '', 'STRING');

		$data            = array();
		$result          = new stdClass;
		$result->results = array();

		if (empty($id))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_INVALID_DONOR_ID"));
		}

		if (empty($type) || $type != 'ind' && $type != 'org')
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_INVALID_TYPE"));
		}

		if ($type === 'ind')
		{
			$contributorClass = JGive::individual($id);
		}
		else
		{
			$contributorClass = JGive::organization($id);
		}

		$data = $contributorClass->getProperties();
		$data['member_since'] = $contributorClass->getCreatedDate(true);

		if (!$data['id'])
		{
			$result->empty_message	= Text::_('PLG_API_JGIVE_NO_DATA_FOUND');
			$this->plugin->setResponse($result);

			return;
		}

		// Get donor id by passing contributor id
		$donor = JGive::donor();
		$donorId = $donor->getDonorIDByContactDetails($id, $type);

		// Pass the donor id donor class to get the other data from donor class
		$donorData = JGive::donor($donorId);
		$contributorId = $donorData->getContributorId();

		$data['campaigns']                      = array();
		$data['total_donated_amount']           = 0;
		$data['total_donations_count']          = 0;
		$data['recent_donated_amount']          = 0;
		$data['recent_donated_campaign']        = '';
		$data['formatted_total_donated_amount'] = 0;
		$data['last_donated_date']              = '';

		if ($contributorId)
		{
			$data['total_donated_amount']           = $donorData->getTotalDonatedAmount();
			$data['total_donations_count']          = $donorData->getAllDonationsCount();
			$data['recent_donated_amount']          = JGive::utilities()->getFormattedPrice($donorData->recentDonatedAmount()[0]->amount);
			$data['recent_donated_campaign']        = $donorData->recentDonatedAmount()[0]->title;
			$data['formatted_total_donated_amount'] = $donorData->getTotalDonatedAmount(true);
			$data['last_donated_date']              = $donorData->getLastDonationDate();
			$data['campaigns']                      = $donorData->getDonatedCampaigns();
		}

		if ($type === 'org')
		{
			$contactClass = JGive::contact();
			$contact      = $contactClass->loadContactById($id);

			$data['contact'] = array();

			if ($contact['id'])
			{
				$data['contact'] = $contact;
			}
		}

		$params                 = Jgive::config();
		$tjCurrency             = new TjMoney($params->get('currency'));
		$data['currencySymbol'] = $tjCurrency->getSymbol();

		$result->results = $data;
		$this->plugin->setResponse($result);
	}

	/**
	 * Add donor
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	public function post()
	{
		$user     = Factory::getUser();
		$lang     = Factory::getLanguage();
		$lang->load('com_jgive', JPATH_SITE);

		$json = Factory::getApplication()->input->json;
		$data = $json->get('request', array(), 'ARRAY');
		$data = array_filter($data);

		$utilityClass      = JGive::utilities();
		$vendorId          = $utilityClass->getVendorId($user->id, "com_jgive");
		$data['vendor_id'] = $vendorId;

		if (empty($data))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_NO_DATA_FOUND"));
		}

		$resultArr = new stdClass;
		$resultArr->result = array();

		if (!$data['donor_type'])
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_DONER_TYPE_REQUIRED"));
		}

		if ($data['donor_type'] === "ind")
		{
			$donorClass = JGive::individual();

			if ($donorClass->validateFormData($data))
			{
				$result = $donorClass->addIndividualDonor($data);
			}
		}
		else
		{
			$donorClass = JGive::organization();

			if ($donorClass->validateFormData($data))
			{
				$result = $donorClass->addOrganization($data);

				$data['organization_id'] = $result;

				if ($data['individual_id'])
				{
					$contactClass   = JGive::contact();
					$orgContactData = $contactClass->loadContactByOrgId($result);
					$data["id"]     = $orgContactData['id'];
					$contactClass->save($data);
				}
			}
		}

		if ($result)
		{
			$resultArr->result['status']  = 1;
			$resultArr->result['id']      = $result;
			$resultArr->result['message'] = Text::_("PLG_API_JGIVE_DONOR_ADDED_SUCCESSFULLY");
		}
		else
		{
			ApiError::raiseError(200, $donorClass->getError());
		}

		$this->plugin->setResponse($resultArr);
	}
}
