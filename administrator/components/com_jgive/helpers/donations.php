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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

jimport('techjoomla.common');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_tjprivacy/models');
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjprivacy/tables');
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');

/**
 * Donations_Backend Helper
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class Donations_BackendHelper
{
	/**
	 * Function for sets campaign id in session
	 *
	 * @param   Array  $post  POST
	 *
	 * @return  boolean
	 */
	public function setSessionCampaignId($post)
	{
		$session = Factory::getSession();
		$this->clearSessionCampaignId();
		$session->set('JGIVE_cid', $post['cid']);

		return true;
	}

	/**
	 * Function for clears campaign id from session
	 *
	 * @return  boolean
	 */
	public function clearSessionCampaignId()
	{
		$session = Factory::getSession();
		$session->set('JGIVE_cid', '');

		return true;
	}

	/**
	 * Function for adding  order in order table
	 *
	 * @param   Object  $post  Post
	 *
	 * @return  boolean
	 */
	public function addOrder($post)
	{
		$app = Factory::getApplication();
		$this->techjoomlacommon = new TechjoomlaCommon;
		$params                 = ComponentHelper::getParams('com_jgive');

		// Validate all order data
		$validateDataStatus = $this->validateOrderData($post);

		if (!$validateDataStatus)
		{
			return false;
		}

		$path = JPATH_ADMINISTRATOR . '/components/com_jgive/helpers/campaign.php';

		if (!class_exists('CampaignHelper'))
		{
			JLoader::register('CampaignHelper', $path);
			JLoader::load('CampaignHelper');
		}

		$campaignHelper       = new campaignHelper;

		$path = JPATH_ROOT . '/components/com_jgive/helper.php';

		if (!class_exists('jgiveFrontendHelper'))
		{
			JLoader::register('jgiveFrontendHelper', $path);
			JLoader::load('jgiveFrontendHelper');
		}

		$jgiveFrontendHelper = new JgiveFrontendHelper;

		$cid = $post->get('cid', 0, 'INT');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaignform');
		$jgiveModelCampaignFrom = BaseDatabaseModel::getInstance('campaignform', 'JGiveModel');
		$item                   = $jgiveModelCampaignFrom->getItem($cid);

		// Get params
		$session              = Factory::getSession();
		$commission_fee       = $params->get('commission_fee', 0, 'INT');
		$fixed_commission_fee = $params->get('fixed_commissionfee', 0, 'INT');

		$vendor_id               = $item->vendor_id;
		$vendorCommissionTable   = Table::getInstance('vendorfee', 'TjvendorsTable', array());
		$vendorCommissionTable->load(array('vendor_id' => $vendor_id, 'client' => 'com_jgive'));
		$vendorCommissionFlat    = $vendorCommissionTable->flat_commission;
		$vendorCommissionPercent = $vendorCommissionTable->percent_commission;

		if ($vendorCommissionPercent > 0 || $vendorCommissionFlat > 0)
		{
			$commission_fee = $vendorCommissionPercent;
			$fixed_commission_fee = $vendorCommissionFlat;
		}

		$send_payments_to_owner = $params->get('send_payments_to_owner');
		$db                     = Factory::getDbo();
		$contactId              = $post->get('donorid', '', 'STRING');
		$contactId              = explode(".", $contactId);
		$userId                 = '';
		$contactType            = '';
		$orgName                = '';

		if ($contactId[0] === 'ind')
		{
			$individualClassObj = JGive::individual($contactId[1]);
			$contact = (object) $individualClassObj->getProperties();

			if ($contact->vendor_id != $vendor_id)
			{
				if (empty($contact->first_name))
				{
					$userdata            = Factory::getUser((int) $contactId[1]);
					$contact->first_name = $userdata->name;
					$contact->email      = $userdata->email;
				}

				$indData              = (array) $contact;
				$indData['id']        = 0;
				$indData['vendor_id'] = $vendor_id;
				$individualObj        = JGive::individual();
				$indId                = $individualObj->addIndividualDonor($indData);

				$individualClassObj = JGive::individual($indId);
				$vendorSpecificInd  = (object) $individualClassObj->getProperties();
				$contact            = $vendorSpecificInd;
				$contactId[1]       = $indId;
			}

			$userId       = $contact->user_id;
			$contactType  = 'ind';
		}
		elseif ($contactId[0] === 'org')
		{
			$organizationClassObj = JGive::organization($contactId[1]);
			$contact = (object) $organizationClassObj->getProperties();

			if ($contact->vendor_id != $vendor_id)
			{
				$orgData = (array) $contact;
				$orgData['id'] = 0;
				$orgData['vendor_id'] = $vendor_id;
				unset($orgData['contact_id']);
				unset($orgData['contact_name']);

				$organizationObjClass = JGive::organization();
				$return               = $organizationObjClass->addOrganization($orgData);

				$organizationClassObj = JGive::organization($return);
				$vendorSpecificOrg    = (object) $organizationClassObj->getProperties();
				$contact              = $vendorSpecificOrg;
				$contactId[1]         = $return;
			}

			$userId      = $contact->user_id;
			$orgName     = $contact->name;
			$contactType = 'org';
		}
		elseif ($contactId[0] === 'user')
		{
			$userdata = Factory::getUser((int) $contactId[1]);
			$indidata = array();
			$indidata['first_name'] = $userdata->name;
			$indidata['email']      = $userdata->email;
			$indidata['user_id']    = $contactId[1];
			$indidata['vendor_id']  = $vendor_id;

			$individualFormModel = JGive::model('Individualform', array('ignore_request' => true));
			$individualID        = $individualFormModel->addDonorContact($indidata);

			$individualClassObj = JGive::individual($individualID);
			$contact            = (object) $individualClassObj->getProperties();
			$userId             = $contactId[1];
		}

		// Get Checkout Type Registerd/Guest 0=Registerd 1=Guest
		$checkout_type = $post->get('checkout_type', '', 'STRING');
		$user          = Factory::getUser($userId);

		// Get the user groupwise commision form params
		$params_usergroup = $params->get('usergroup');

		// Get campaign type donation/Investment & its creator
		$camp_details    = $campaignHelper->getCampaignType($cid);

		// Get Campaign creator groups ids
		$campaign_creator        = Factory::getUser($camp_details->creator_id);
		$camp_creator_groups_ids = $campaign_creator->groups;

		// If checkout type=Registered
		if ($checkout_type == 0)
		{
			$userid = $user->id;
		}
		else
		{
			$userid = 0;
		}

		$this->guest_donation = $params->get('guest_donation');

		if ($this->guest_donation)
		{
			if (!$userid)
			{
				$session->set('quick_reg_no_login', '1');
			}
		}
		elseif (!$userid && !$contactId[1])
		{
			return false;
		}

		// Save donor details
		$obj = new stdClass;

		$obj->id             = '';
		$obj->user_id        = $userid;
		$obj->campaign_id    = $cid;
		$obj->donor_type     = $contactType;
		$obj->contributor_id = $contactId[1];
		$obj->org_name       = $orgName;

		$obj->email      = $contact->email;
		$obj->first_name = $contact->first_name ? $contact->first_name : '';
		$obj->last_name  = $contact->last_name ? $contact->last_name : '';
		$obj->address    = $contact->addr_line_1;
		$obj->address2   = $contact->addr_line_2;

		$obj->country   = $contact->country;
		$obj->state     = $contact->region;
		$obj->zip       = $contact->zip;
		$obj->city      = $contact->city;
		$obj->phone     = $contact->phone;
		$obj->taxnumber = $contact->taxnumber;

		if ($obj->city == 0)
		{
			$obj->city = $contact->other_city_value;
		}

		$jgiveTableDonor = Table::getInstance('Donor', 'JGiveTable', array());
		$jgiveTableDonor->bind((array) $obj);

		if ($obj->id)
		{
			if (!$db->updateObject('#__jg_donors', $obj, 'id'))
			{
				echo $db->stderr();

				return false;
			}
		}
		elseif (!$db->insertObject('#__jg_donors', $obj, 'id'))
		{
			echo $db->stderr();

			return false;
		}

		// Get last insert id
		if ($obj->id)
		{
			$donors_key = $obj->id;
		}
		else
		{
			$donors_key = $db->insertid();
		}

		$obj              = new stdClass;
		$obj->id          = '';
		$obj->campaign_id = $post->get('cid');
		$obj->donor_id    = $donors_key;

		if ($post->get('donation_type', '', 'INT'))
		{
			$obj->is_recurring        = 1;
			$obj->recurring_frequency = $post->get('recurring_freq', '', 'STRING');
			$obj->recurring_count     = $post->get('recurring_count', '', 'INT');
		}
		else
		{
			$obj->is_recurring        = 0;
			$obj->recurring_frequency = '';
			$obj->recurring_count     = '';
		}

		$obj->annonymous_donation = $post->get('annonymousDonation', '', 'INT');
		$no_giveback              = $post->get('no_giveback', '', 'INT');

		// Check donor not checked no giveback option
		if (!$no_giveback)
		{
			$obj->giveback_id = $post->get('givebacks', '', 'INT');
		}
		else
		{
			$obj->giveback_id = 0;
		}

		if (!isset($obj->comment) || empty($obj->comment))
		{
			$obj->comment = '';
		}

		if (!$db->insertObject('#__jg_donations', $obj, 'id'))
		{
			echo $db->stderr();

			return false;
		}

		$donation_id = $db->insertid();

		// Save order details
		$obj     = new stdClass;
		$obj->id = '';

		/*Lets make a random char for this order
		take order prefix set by admin*/
		$order_prefix       = (string) $params->get('order_prefix');

		// String length should not be more than 5
		$order_prefix       = substr($order_prefix, 0, 5);

		// Take separator set by admin
		$separator          = (string) $params->get('separator');
		$obj->order_id      = $order_prefix . $separator;

		// Check if we have to add random number to order id
		$use_random_orderid = (int) $params->get('random_orderid');

		if ($use_random_orderid)
		{
			$random_numer = $this->_random(5);
			$obj->order_id .= $random_numer . $separator;
			/* This length shud be such that it matches the column lenth of primary key
			it is used to add pading order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number */
			$len = (23 - 5 - 2 - 5);
		}
		else
		{
			/*this length shud be such that it matches the column lenth of primary key
			it is used to add pading
			order_id_column_field_length - prefix_length - no_of_underscores*/
			$len = (23 - 5 - 2);
		}

		$obj->campaign_id           = $cid;
		$obj->donor_id              = $donors_key;
		$obj->donation_id           = $donation_id;
		$obj->cdate                 = $this->techjoomlacommon->getDateInUtc(date("Y-m-d H:i:s"));
		$obj->mdate                 = $this->techjoomlacommon->getDateInUtc(date("Y-m-d H:i:s"));
		$obj->payment_received_date = $this->techjoomlacommon->getDateInUtc($post->get('payment_received_date'));
		$obj->transaction_id        = $post->get('transaction_id', '', 'STRING');

		$amount_separator     = $params->get('amount_separator');
		$feeMode              = $params->get('fee_mode', 'inclusive', 'string');
		$exclusiveFeeOptional = $params->get('exclusive_fee_optional', '0', 'string');
		$platformFee          = $post->get('platform_fee', '', 'FLOAT');
		$donationAmount       = $post->get('donation_amount', '', 'FLOAT');

		// Inclusive Commission
		$originalDonationAmount = $donationAmount;
		$amount                 = $donationAmount;

		// Exclusive Commission
		if ($feeMode == 'exclusive')
		{
			// Exclusive Commission compulsory
			$originalDonationAmount = $donationAmount + $platformFee;
			$amount                 = $donationAmount;

			// Exclusive Commission optional
			if ((int) $exclusiveFeeOptional === 1)
			{
				$exclusivePlatformFeeCheck = $post->get('exclusive_platform_fee');

				if (isset($exclusivePlatformFeeCheck) && $exclusivePlatformFeeCheck == 'on')
				{
					// Donor has wish to pay platform fee
					$originalDonationAmount = $donationAmount + $platformFee;
					$amount                 = $donationAmount;
				}
				else
				{
					// Donor does not wish to pay platform fee
					$originalDonationAmount = $donationAmount;
					$amount                 = $donationAmount;
				}
			}
		}

		$originalDonationAmount  = $jgiveFrontendHelper->getRoundedAmount($originalDonationAmount);
		$amount                  = $jgiveFrontendHelper->getRoundedAmount($amount);

		if (!empty($amount_separator))
		{
			$originalDonationAmount = str_replace($amount_separator, '.', $originalDonationAmount);
			$amount                 = str_replace($amount_separator, '.', $amount);
		}

		$obj->original_amount       = $originalDonationAmount;
		$obj->amount                = $amount;
		$obj->fee                   = 0;

		if (!$send_payments_to_owner)
		{
			if (!empty($params_usergroup))
			{
				$count = count($params_usergroup);

				for ($l = 0; $l < $count; $l = $l + 3)
				{
					if (in_array($params_usergroup[$l], $camp_creator_groups_ids))
					{
						if ($camp_details->type == 'donation')
						{
							$commission_fee = (int) ($params_usergroup[$l + 1]);
						}
						elseif ($camp_details->type == 'investment')
						{
							$commission_fee = (int) ($params_usergroup[$l + 2]);
						}

						break;
					}
				}
			}

			$obj->fee = $jgiveFrontendHelper->getRoundedAmount((($obj->amount * $commission_fee) / 100) + $fixed_commission_fee);

			if ($feeMode == 'exclusive')
			{
				if ($exclusiveFeeOptional == '1')
				{
					if ($post->get('exclusive_platform_fee') && $post->get('exclusive_platform_fee') == 'on')
					{
						$paidPlatformFee = true;
					}
					else
					{
						$obj->fee = 0;
						$paidPlatformFee = false;
					}
				}
				else
				{
					$paidPlatformFee = true;
				}
			}
			else
			{
				$paidPlatformFee = true;
			}

			if ($feeMode == 'exclusive' && $obj->fee != $platformFee)
			{
				$app->enqueueMessage(Text::_('COM_JGIVE_DASHBOARD_CREATE_ACTIVITIES_ERROR'), 'error');

				return false;
			}

			$commissionConfig = array();
			$commissionConfig['fee_mode']               = $feeMode;
			$commissionConfig['exclusive_fee_optional'] = $exclusiveFeeOptional;
			$commissionConfig['paid_platform_fee']      = $paidPlatformFee;
			$commissionConfig['currency']               = $params->get('currency');
			$commissionConfig['commission_fee']         = $commission_fee;
			$commissionConfig['fixed_commissionfee']    = $fixed_commission_fee;

			$obj->params = json_encode($commissionConfig);
		}

		$obj->fund_holder = 0;
		$obj->vat_number  = $post->get('vat_number', '', 'STRING');

		if ($send_payments_to_owner)
		{
			// Money for this order will goto campaign promoters account
			$obj->fund_holder = 1;
		}

		// By default pending status
		$obj->status    = $post->get('pstatus', '', 'STRING');
		$obj->processor = $post->get('gateways', '', 'STRING');

		// Get the IP Address
		if (!empty($_SERVER['REMOTE_ADDR']))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$ip = 'unknown';
		}

		$obj->ip_address = $ip;

		if (!$db->insertObject('#__jg_orders', $obj, 'id'))
		{
			echo $db->stderr();
		}

		// Get last insert id
		$ordersKey = $db->insertid();

		// Set order id in session
		$session = Factory::getSession();
		$session->set('JGIVE_order_id', $ordersKey);

		$db->setQuery('SELECT order_id FROM #__jg_orders WHERE id=' . $ordersKey);
		$order_id      = (string) $db->loadResult();
		$maxlen        = 23 - strlen($order_id) - strlen($ordersKey);
		$padding_count = (int) $params->get('padding_count');

		// Use padding length set by admin only if it is les than allowed(calculate) length
		if ($padding_count > $maxlen)
		{
			$padding_count = $maxlen;
		}

		if (strlen((string) $ordersKey) <= $len)
		{
			$append = '';

			for ($z = 0; $z < $padding_count; $z++)
			{
				$append .= '0';
			}

			$append = $append . $ordersKey;
		}

		$res      = new stdClass;
		$res->id  = $ordersKey;
		$res->order_id = $order_id . $append;

		if (!$db->updateObject('#__jg_orders', $res, 'id'))
		{
			// They return false;
		}

		if (!empty($params->get('terms_condition', 0)) && !empty($params->get('payment_terms_article', 0)))
		{
			// Save User Privacy Terms and conditions Data
			$userPrivacyData = array();
			$privacyTermsCondition = $post->get('terms_condition', '', 'STRING');
			$userPrivacyData['client'] = 'com_jgive.donation';
			$userPrivacyData['client_id'] = $res->id;
			$userPrivacyData['user_id'] = $user->id?$user->id:0;
			$userPrivacyData['purpose'] = Text::_('COM_JGIVE_USER_PRIVACY_TERMS_PURPOSE_FOR_DONATION');
			$userPrivacyData['accepted'] = isset($privacyTermsCondition)?1:0;
			$userPrivacyData['date'] = Factory::getDate('now')->toSQL();

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjprivacy/models', 'tjprivacy');
			$tjprivacyModelObj = BaseDatabaseModel::getInstance('tjprivacy', 'TjprivacyModel');
			$tjprivacyModelObj->save($userPrivacyData);
		}

		// Trigger for campaign donation status

		/* @ToDo - Check this trigger is used on donation add or update
		 * and change trigger name accordingly or place at correct location
		 */
		PluginHelper::importPlugin('system');
		Factory::getApplication()->triggerEvent('onAfterJGPaymentStatusChange', array($ordersKey, $obj->status , '', 0));

		JLoader::import('models.donation', JPATH_SITE . '/components/com_jgive');
		$donationModel = new JgiveModelDonation;
		$donationDetails = $donationModel->getItem($ordersKey);

		JLoader::import('components.com_jgive.events.donation', JPATH_SITE);
		$jGiveTriggerDonation = new JGiveTriggerDonation;
		$jGiveTriggerDonation->onDonationAfterSave($donationDetails, true);

		$donationDetails['isadmin'] = 1;
		PluginHelper::importPlugin('jgive');
		PluginHelper::importPlugin('actionlog');
		$result = Factory::getApplication()->triggerEvent('onAfterJGPaymentStatusProcess', array($donationDetails));

		return true;
	}

	/**
	 * Function getPaymentVars
	 *
	 * @param   String  $pg_plugin  PG Plugin
	 * @param   String  $tid        Order id
	 *
	 * @return  vars
	 */
	public function getPaymentVars($pg_plugin, $tid)
	{
		$campaignHelper         = new campaignHelper;
		$vars                   = new stdclass;
		$params                 = ComponentHelper::getParams('com_jgive');

		require_once JPATH_SITE . "/components/com_jgive/helpers/campaign.php";

		$pass_data                = $this->getdetails($tid);
		$vars->order_id           = $pass_data->order_id;

		$session = Factory::getSession();
		$session->set('order_id', $tid);

		$vars->client = 'com_jgive';

		$vars->user_firstname = $pass_data->first_name;
		$vars->user_id        = Factory::getUser()->id;
		$vars->user_email     = $pass_data->email;

		$this->guest_donation = $params->get('guest_donation');

		if ($this->guest_donation)
		{
			if (!$vars->user_id)
			{
				$vars->user_id = 0;
				$session       = Factory::getSession();
				$session->set('quick_reg_no_login', '1');
				$guest_email = md5($vars->user_email);
				$session     = Factory::getSession();
				$session->set('guest_email', $guest_email);
			}
		}

		return $vars;
	}

	/**
	 * Function For loads payment plugin gateway html
	 *
	 * @param   String  $pg_plugin  PG Plugin
	 * @param   String  $tid        Order id
	 *
	 * @return  string html
	 */
	public function getHTML($pg_plugin, $tid)
	{
		$vars       = $this->getPaymentVars($pg_plugin, $tid);
		PluginHelper::importPlugin('payment', $pg_plugin);
		$html = Factory::getApplication()->triggerEvent('onTP_GetHTML', array($vars));

		return $html;
	}

	/**
	 * Function For get details of order and donor
	 *
	 * @param   String  $tid  Order id
	 *
	 * @return  Array
	 */
	public function getdetails($tid)
	{
		$user_id = Factory::getUser()->id;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
						$db->qn(
								array(
										'o.id', 'o.order_id', 'd.first_name',' d.email', 'd.phone','ds.is_recurring','ds.recurring_frequency','ds.recurring_count','ds.comment'
									)
							)
						);
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_donors', 'd') . ' ON (' . $db->qn('d.id') . ' = ' . $db->qn('o.donor_id') . ')');
		$query->join('LEFT', $db->qn('#__jg_donations', 'ds') . ' ON (' . $db->qn('ds.id') . ' = ' . $db->qn('o.donation_id') . ')');
		$query->where($db->qn('o.id') . ' = ' . (int) $tid . ' AND ' . $db->qn('d.user_id') . ' = ' . (int) $user_id);
		$db->setQuery($query);
		$orderdetails = $db->loadObjectlist();

		return $orderdetails['0'];
	}

	/**
	 * Function Payment Process
	 *
	 * @param   String  $post       POST
	 * @param   Array   $pg_plugin  PG Plugin
	 * @param   INT     $order_id   Order id
	 *
	 * @return  Array|boolean
	 */
	public function processPayment($post, $pg_plugin, $order_id)
	{
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$session             = Factory::getSession();
		$guest_email         = '';
		$guest_email         = $session->get('guest_email');
		$session->clear('guest_email');
		$session->set('order_link_guestemail', $guest_email);

		// Load donations helper
		require_once JPATH_SITE . '/components/com_jgive/helpers/donations.php';
		$donationsHelper = new donationsHelper;

		$app   = Factory::getApplication();
		$input = $app->input;
		$input->set('remote', 1);
		$donationItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations');

		$return_resp = array();

		// Authorise Post Data
		if ($post['plugin_payment_method'] == 'onsite')
		{
			$plugin_payment_method = $post['plugin_payment_method'];
		}

		// Trigger payment plugins- onTP_Processpayment
		PluginHelper::importPlugin('payment', $pg_plugin);
		$data = Factory::getApplication()->triggerEvent('onTP_Processpayment', array($post));
		$data = $data[0];

		// Store log
		$res = @$this->storelog($pg_plugin, $data);

		// Get order id
		if (empty($order_id))
		{
			$order_id = $data['order_id'];
		}

		// Get id for orders table using order_id
		$order_id_key      = $donationsHelper->getOrderIdKeyFromOrderId($order_id);

		// Gateway used
		$data['processor'] = $pg_plugin;

		// Payment status
		$data['status']    = trim($data['status']);

		// Get order amount
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('original_amount');
		$query->from($db->qn('#__jg_orders'));
		$query->where($db->qn('id') . ' = ' . (int) $order_id_key);
		$db->setQuery($query);
		$order_amount = $db->loadResult();

		// Return url
		$return_resp['return'] = $data['return'];

		// If payment status is confirmed
		if ($data['status'] == 'C' && $order_amount == $data['total_paid_amt'])
		{
			// Update order details
			$this->updateOrder($data);

			// Update order status, send email,
			$donationsHelper->updatestatus($order_id_key, $data['status'], $comment = '', $notify_chk = 1);

			// Trigger plugins
			// onAfterJGPaymentSuccess
			PluginHelper::importPlugin('system');
			PluginHelper::importPlugin('actionlog');
			$result = Factory::getApplication()->triggerEvent('onAfterJGivePaymentSuccess', array($order_id_key));

			PluginHelper::importPlugin('jgive');
			Factory::getApplication()->triggerEvent('onAfterJGPaymentSuccess', array($data));

			if ($result === false)
			{
				return false;
			}

			// Added guest email in url for payment processs on site
			$return_resp['return'] = Uri::root() . substr(
				Route::_("index.php?option=com_jgive&view=donation&donationid=" .
					$order_id_key . "&processor={$pg_plugin}&email=" . $guest_email . "&Itemid=" . $donationItemid, false
				), strlen(Uri::base(true)) + 1
			);

			$return_resp['status'] = '1';
		}
		elseif ($order_amount != $data['total_paid_amt'])
		{
			$data['status']        = 'E';
			$return_resp['status'] = '0';
		}
		elseif (!empty($data['status']))
		{
			// Added guest email in url for payment processs on site
			if ($plugin_payment_method && $data['status'] == 'P')
			{
				$return_resp['return'] = Uri::root() . substr(
				Route::_("index.php?option=com_jgive&view=donation&donationid=" .
				$order_id_key . "&processor={$pg_plugin}&email=" . $guest_email . "&Itemid=" . $donationItemid, false
				), strlen(Uri::base(true)) + 1
				);
			}

			if ($data['status'] != 'C')
			{
				$this->updateOrder($data);
			}
			elseif ($data['status'] == 'C')
			{
				// Added guest email in url for payment processs on site
				$return_resp['return'] = Uri::root() . substr(
				Route::_(
				"index.php?option=com_jgive&view=donations&layout=cancel&processor={$pg_plugin}&email=" .
				$guest_email . "&Itemid=" . $chkoutItemid
				), strlen(Uri::base(true)) + 1
				);
			}

			$return_resp['status'] = '0';

			// TO DO where is this  used ???
			$res->processor        = $data['processor'];
			$return_resp['msg']    = $data['error']['code'] . " " . $data['error']['desc'];
		}

		return $return_resp;
	}

	/**
	 * Function For store log
	 *
	 * @param   String  $name  Name
	 * @param   Array   $data  Data
	 *
	 * @return  void
	 */
	public function storelog($name, $data)
	{
		$data1              = array();
		$data1['raw_data']  = $data['raw_data'];
		$data1['JT_CLIENT'] = "com_jgive";
		PluginHelper::importPlugin('payment', $name);
		Factory::getApplication()->triggerEvent('onTP_Storelog', array($data1));
	}

	/**
	 * Function For Update order
	 *
	 * @param   Array  $data  Data
	 *
	 * @return  void
	 */
	public function updateOrder($data)
	{
		$db    = Factory::getDbo();

		// Load donations helper
		require_once JPATH_SITE . '/components/com_jgive/helpers/donations.php';
		$donationsHelper = new donationsHelper;

		// Get id for orders table using order_id
		$order_id_key = $donationsHelper->getOrderIdKeyFromOrderId($data['order_id']);

		// Get donation id
		$query = $db->getQuery(true);
		$query->select('donation_id');
		$query->from($db->qn('#__jg_orders'));
		$query->where($db->qn('id') . ' = ' . (int) $order_id_key);
		$db->setQuery($query);
		$donation_id = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('subscr_id');
		$query->select('is_recurring');
		$query->from($db->qn('#__jg_donations'));
		$query->where($db->qn('id') . ' = ' . (int) $donation_id);
		$db->setQuery($query);
		$donation_details = $db->loadObject();

		/* If subscriber id is not exist then it is first response from paypal
		hence update donation table insert subsriber id & also update transaction id*/
		if ($donation_details->is_recurring)
		{
			if ($data['txn_type'] == 'subscr_payment') // For recurring payment
			{
				// Insert subscriber id in donation table if not exits (Recurring donations)
				if (empty($donation_details->subscr_id))
				{
					$res            = new stdClass;
					$res->id        = $donation_id;
					$res->subscr_id = $data['subscriber_id'];

					if (!$db->updateObject('#__jg_donations', $res, 'id'))
					{
						// They return false;
					}

					// Update First order status & transaction id

					$res                 = new stdClass;
					$res->id             = $order_id_key;
					$res->mdate          = date("Y-m-d H:i:s");
					$res->transaction_id = $data['transaction_id'];
					$res->status         = $data['status'];
					$res->processor      = $data['processor'];
					$res->extra          = json_encode($data['raw_data']);

					if (!$db->updateObject('#__jg_orders', $res, 'id'))
					{
						// They return false;
					}
				}
				// For recurring payment for more responses other than first
				else
				{
					/*check the transaction id is present in the order table
					--------- get transaction id*/
					$query = $db->getQuery(true);
					$query->select('transaction_id');
					$query->from($db->qn('#__jg_orders'));
					$query->where($db->qn('donation_id') . ' = ' . (int) $donation_id);
					$db->setQuery($query);
					$transaction_ids = $db->loadColumn();

					// Check is transaction id exist in array
					if ($transaction_ids[0])
					{
						$flag = 0;

						for ($i = 0; $i < count($transaction_ids); $i++)
						{
							// If same transaction id then update the order
							if ($transaction_ids[$i] == $data['transaction_id'])
							{
								// Transaction id already in table
								$flag = 1;
								break;
							}
						}

						// New transaction
						if ($flag == 0)
						{
							/*order_id campaign_id  donor_id donation_id fund_holder cdate mdate
							transaction_id transaction_id original_amount amount  fee  status processor ip_address extra*/
							$this->_addNewRecurringOrder($data, $order_id_key);
						}
						// Update existing transaction
						else
						{
							$res                 = new stdClass;
							$res->mdate          = date("Y-m-d H:i:s");
							$res->transaction_id = $data['transaction_id'];
							$res->status         = $data['status'];
							$res->processor      = $data['processor'];
							$res->extra          = json_encode($data['raw_data']);

							if (!$db->updateObject('#__jg_orders', $res, 'transaction_id'))
							{
								// They return false;
							}
						}
					}
				}
			}
		}
		else
		{
			$res                 = new stdClass;
			$res->id             = $order_id_key;
			$res->mdate          = date("Y-m-d H:i:s");
			$res->transaction_id = $data['transaction_id'];
			$res->status         = $data['status'];
			$res->processor      = $data['processor'];
			$res->extra          = json_encode($data['raw_data']);

			if (!$db->updateObject('#__jg_orders', $res, 'id'))
			{
				// The return false;
			}

			// For adaptive payment add entry in payout report
			if ($data['txn_type'] == 'Adaptive Payment PAY')
			{
				$this->addPayout($data['raw_data']['paymentInfoList']['paymentInfo'][1]);
			}
		}
	}

	/**
	 * Function For add payout entry after adaptive payment from paypal
	 *
	 * @param   Array  $data  Data
	 *
	 * @return  boolean
	 */
	public function addPayout($data)
	{
		$db    = Factory::getDbo();
		$camp_promoter_email = $data['receiver']['email'];

		if (!$camp_promoter_email)
		{
			return;
		}

		$query = $db->getQuery(true);
		$query->select('creator_id');
		$query->from($db->qn('#__jg_campaigns'));
		$query->where($db->qn('paypal_email') . ' = ' . $db->quote($camp_promoter_email));
		$db->setQuery($query);
		$camp_promoter_id = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->qn('#__jg_payouts'));
		$query->where($db->qn('transaction_id') . ' = ' . $db->quote($data['transactionId']));
		$db->setQuery($query);
		$payout_id = $db->loadResult();

		$res = new stdClass;

		if ($payout_id)
		{
			$res->id = $payout_id;
		}
		else
		{
			$res->id = '';
		}

		$res->user_id        = $camp_promoter_id;
		$res->payee_name     = Factory::getUser($camp_promoter_id)->name;
		$res->date           = date("Y-m-d H:i:s");
		$res->transaction_id = $data['transactionId'];
		$res->email_id       = $camp_promoter_email;
		$res->amount         = $data['receiver']['amount'];

		if ($data['transactionStatus'] == 'COMPLETED')
		{
			$res->status = 1;
		}
		else
		{
			$res->status = 0;
		}

		$res->ip_address = '';
		$res->type       = 'adaptive_paypal';

		if ($res->id)
		{
			if (!$db->updateObject('#__jg_payouts', $res, 'id'))
			{
			}
		}
		else
		{
			if (!$db->insertObject('#__jg_payouts', $res, 'id'))
			{
			}
		}

		return true;
	}

	/**
	 * Function For _addNewRecurringOrder
	 *
	 * @param   Array  $data     Data
	 * @param   INT    $orderId  Order_id_key
	 *
	 * @return  void
	 */
	public function _addNewRecurringOrder($data, $orderId)
	{
		JLoader::import('models.donation', JPATH_SITE . '/components/com_jgive');
		$donationModel = new JgiveModelDonation;
		$donationInfo  = $donationModel->getItem($orderId);

		/*order_id campaign_id  donor_id donation_id fund_holder cdate mdate
		transaction_id transaction_id original_amount amount  fee  status processor ip_address extra
		save order details*/
		$db              = Factory::getDbo();
		$obj             = new stdClass;
		$obj->id         = '';

		// Lets make a random char for this order take order prefix set by admin
		$obj->id            = '';
		$params             = ComponentHelper::getParams('com_jgive');
		$order_prefix       = (string) $params->get('order_prefix');

		// String length should not be more than 5
		$order_prefix       = substr($order_prefix, 0, 5);

		// Take separator set by admin
		$separator          = (string) $params->get('separator');
		$obj->order_id      = $order_prefix . $separator;

		// Check if we have to add random number to order id
		$use_random_orderid = (int) $params->get('random_orderid');

		if ($use_random_orderid)
		{
			$random_numer = $this->_random(5);
			$obj->order_id .= $random_numer . $separator;
			/* This length shud be such that it matches the column lenth of primary key
			it is used to add pading order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number*/
			$len = (23 - 5 - 2 - 5);
		}
		else
		{
			/*This length shud be such that it matches the column lenth of primary key
			 * it is used to add pading order_id_column_field_length - prefix_length - no_of_underscores*/
			$len = (23 - 5 - 2);
		}

		$obj->campaign_id     = $donationInfo['campaign']->id;
		$obj->donor_id        = $donationInfo['donor']->id;
		$obj->donation_id     = $donationInfo['payment']->donation_id;
		$obj->cdate           = date("Y-m-d H:i:s");
		$obj->mdate           = date("Y-m-d H:i:s");
		$obj->original_amount = $donationInfo['payment']->original_amount;
		$obj->amount          = $donationInfo['payment']->amount;

		// Need To Modify
		$obj->fee = $donationInfo['payment']->fee;

		$obj->fund_holder = $donationInfo['payment']->fund_holder;

		$obj->processor = $donationInfo['payment']->processor;

		// Get the IP Address
		$obj->ip_address = $donationInfo['payment']->ip_address;

		$obj->transaction_id = $data['transaction_id'];
		$obj->status == $data['status'];
		$obj->extra = json_encode($data['raw_data']);

		try
		{
			$db->insertObject('#__jg_orders', $obj, 'id');
		}
		catch (RuntimeException $e)
		{
			echo $e->getMessage();
		}

		$orders_key = $db->insertid();

		if (!$orders_key)
		{
			return 'Error in saving order details';
		}

		$db->setQuery('SELECT order_id FROM #__jg_orders WHERE id=' . $orders_key);
		$order_id      = (string) $db->loadResult();
		$maxlen        = 23 - strlen($order_id) - strlen($orders_key);
		$padding_count = (int) $params->get('padding_count');

		// Use padding length set by admin only if it is les than allowed(calculate) length
		if ($padding_count > $maxlen)
		{
			$padding_count = $maxlen;
		}

		$append = '';

		if (strlen((string) $orders_key) <= $len)
		{
			for ($z = 0; $z < $padding_count; $z++)
			{
				$append .= '0';
			}

			$append = $append . $orders_key;
		}

		$res           = new stdClass;
		$res->id       = $orders_key;
		$res->order_id = $order_id . $append;

		if (!$db->updateObject('#__jg_orders', $res, 'id'))
		{
		}
	}

	/**
	 * Function _random
	 *
	 * @param   INT  $length  Length
	 *
	 * @return  INT
	 */
	public function _random($length = 17)
	{
		$salt   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len    = strlen($salt);
		$random = '';

		$stat = @stat(__FILE__);

		if (empty($stat) || !is_array($stat))
		{
			$stat = array(php_uname());
		}

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i++)
		{
			$random .= $salt[mt_rand(0, $len - 1)];
		}

		return $random;
	}

	/**
	 * Method to validate donation data
	 *
	 * @param   Object  $post  Order data
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.3.0
	 */
	public function validateOrderData($post)
	{
		$app            = Factory::getApplication();
		$return         = true;
		$params         = ComponentHelper::getParams('com_jgive');

		JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');
		$campaignHelper = new campaignHelper;

		// Donor name Validation
		if (empty($post->get('unique-name', '', 'STRING')))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_DONATION_FORM_VALIDATION_DONOR_NAME'), 'warning');
			$return = false;
		}

		if (empty($post->get('donorid')))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_DONATION_FORM_VALIDATION_INVALID_DONOR'), 'warning');
			$return = false;
		}

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaignform');
		$jgiveModelCampaignFrom = BaseDatabaseModel::getInstance('campaignform', 'JGiveModel', array('ignore_request' => true));
		$item                   = $jgiveModelCampaignFrom->getItem($post->get('cid', '', 'INT'));
		$jgiveModelDonorsObj       = BaseDatabaseModel::getInstance('Donors', 'JGiveModel', array('ignore_request' => true));
		$campaignAmounts           = $campaignHelper->getCampaignAmounts($item->id);
		$item->amount_received     = $campaignAmounts['amount_received'];
		$item->campaignDonorsCount = $jgiveModelDonorsObj->getDonorsPerCamp($item->id);

		// If campaign does not exist
		if (!$item->id)
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_DONATION_VALIDATION_CAMPAIGN_NOT_PRESENT'), 'warning');
			$return = false;
		}

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaigns');
		$jgiveModelCampaigns = BaseDatabaseModel::getInstance('campaigns', 'JGiveModel');
		$donateButtonStatus  = $jgiveModelCampaigns->getDonateButtonStatusFlag((array) $item);

		// Donation button status should not be "Close" or "Not yet started"
		if ($donateButtonStatus != 1)
		{
			$donationbuttonStatusmsg = ($donateButtonStatus == -1) ? Text::_(
			'COM_JGIVE_DONATION_NOT_STARTED_BUTTON_FLAG_MSG') : Text::_('COM_JGIVE_DONATION_CLOSE_BUTTON_FLAG_MSG');

			$app->enqueueMessage($donationbuttonStatusmsg, 'warning');

			return false;
		}

		// Donation amount should not be Zero or negative
		if ($post->get('donation_amount', '', 'FLOAT') <= 0)
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_DONATION_NEGATIVE_ZERO_NUMBER_ERROR_MSG'), 'warning');
			$return = false;
		}

		$gatewayOptions = array();

		if (is_string($params->get('gateways')))
		{
			$gatewayOptions[] = $params->get('gateways');
		}
		else
		{
			$gatewayOptions = $params->get('gateways');
		}

		// Server side validation Invalid Payment Gateways
		if (!in_array($post->get('gateways', '', 'STRING'), $gatewayOptions))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_DONATION_VALIDATION_WRONG_PAYMENT_METHOD'), 'warning');
			$return = false;
		}

		// User Privacy validation
		if (!empty($params->get('terms_condition', 0)) && !empty($params->get('payment_terms_article', 0)))
		{
			if (empty($post->get('terms_condition', '', 'STRING')) || $post->get('terms_condition', '', 'STRING') != 'on')
			{
				$app->enqueueMessage(Text::_('COM_JGIVE_CHECK_TERMS'), 'warning');
				$return = false;
			}
		}

		// Campaign minimum donation validation
		if (($item->minimum_amount > 0) && ($post->get('donation_amount', '', 'FLOAT') < $item->minimum_amount))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_DONATION_VALIDATION_DONATION_AMOUNT_GREATER_THAN_CAMPAIGN_AMOUNT'), 'warning');
			$return = false;
		}

		if (!empty($post->get('payment_received_date')) && ($post->get('payment_received_date') > date("Y-m-d")))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_PAYMENT_RECEIVED_DATE_ERROR_MESSAGE'), 'warning');
			$return = false;
		}

		// Max allowed donor/investers validation
		if (isset($item->max_donors) && $item->max_donors > 0)
		{
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'donations');
			$jgiveModelDonations = BaseDatabaseModel::getInstance('donations', 'JGiveModel');
			$jgiveModelDonations->setState("filter.campaign_id", $post->get('cid', '', 'INT'));
			$jgiveModelDonations->setState("filter.payment_status", "C");
			$donations = (array) $jgiveModelDonations->getItems();

			if (count($donations) >= (int) $item->max_donors)
			{
				$app->enqueueMessage(Text::_('COM_JGIVE_CAMPAIGN_MAX_ALLOWED_DONOR_REACHED'), 'warning');
				$return = false;
			}
		}

		$givebacks = $post->get('givebacks', '', 'INT');

		if ($givebacks)
		{
			$giveBacksTable   = Table::getInstance('GiveBacks', 'JGiveTable', array());
			$giveBacksTable->load(array('id' => $givebacks));

			if (isset($giveBacksTable->amount) && ($giveBacksTable->amount > $post->get('donation_amount', '', 'FLOAT')))
			{
				$app->enqueueMessage(Text::_('COM_JGIVE_AMOUNT_SHOULD_BE') . ' ' . $giveBacksTable->amount, 'warning');
				$return = false;
			}
		}

		// Donor not allow to donate more that goal amount if campaign not allowing exceed donation
		if ($item->allow_exceed != 1)
		{
			// Donation amount grater than campaign goal amount
			if ($item->goal_amount < $post->get('donation_amount', '', 'FLOAT'))
			{
				$app->enqueueMessage(Text::sprintf('COM_JGIVE_GOAL_AMOUNT_EXCEED_NOT_ALLOW_MSG', $item->goal_amount), 'warning');
				$return = false;
			}
		}

		return $return;
	}
}
