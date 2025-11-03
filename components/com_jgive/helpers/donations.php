<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

// Component Helper
jimport('techjoomla.tjmail.mail');

Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
$helperPath = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

if (!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath);
	JLoader::load('campaignHelper');
}

/**
 * Donations Helper class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class DonationsHelper
{
	/**
	 * Process Refund
	 *
	 * @param   Array  $orderData  Description
	 *
	 * @return  0/1
	 */
	public function processRefund($orderData)
	{
		$data                        = array();
		$data['order_id']            = $orderData->id;
		$data['user_id']             = $orderData->donor_id;
		$data['total']               = $orderData->amount;
		$data['client']              = 'com_jgive';
		$data['payment_description'] = Text::_('COM_JGIVE_PROCESS_REFUND_DEFAULT_MSG') . ' ' . $orderData->title;
		$data['return']              = '';

		if ($orderData->processor == 'ewallet')
		{
			PluginHelper::importPlugin('payment', $orderData->processor);
			$result     = Factory::getApplication()->triggerEvent('onTP_ProcessRefund', array($data));

			if ($result[0]['status'] == 'C')
			{
				$comment = Text::_('COM_JGIVE_PROCESS_REFUND_DEFAULT_MSG') . ' ' . $orderData->title;
				$this->updatestatus($result[0]['order_id'], 'RF', $comment, 1);

				// Start - Plugin trigger OnAfterJGivePaymentProcess.
				PluginHelper::importPlugin('system');

				// Params - orderId, newStatus, comment, sendEmail
				$result = Factory::getApplication()->triggerEvent('onAfterJGPaymentStatusChange', array($result[0]['order_id'],'RF',$comment,1));

				return 1;
			}
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Get Donation Status Array for all, details, my donations view
	 *
	 * @return   Array  options
	 */
	public function getSStatusArray()
	{
		$sstatus = array();
		$app     = Factory::getApplication();

		if ($app->isClient("site"))
		{
			$sstatus[] = HTMLHelper::_('select.option', '-1', Text::_('COM_JGIVE_APPROVAL_STATUS'));
		}

		$sstatus[] = HTMLHelper::_('select.option', 'P', Text::_('COM_JGIVE_PENDING'));
		$sstatus[] = HTMLHelper::_('select.option', 'C', Text::_('COM_JGIVE_CONFIRMED'));
		$sstatus[] = HTMLHelper::_('select.option', 'RF', Text::_('COM_JGIVE_REFUND'));
		$sstatus[] = HTMLHelper::_('select.option', 'E', Text::_('COM_JGIVE_CANCELED'));
		$sstatus[] = HTMLHelper::_('select.option', 'D', Text::_('COM_JGIVE_DENIED'));

		return $sstatus;
	}

	/**
	 * Used in donation info view
	 *
	 * @param   INT  $order_id_key  Order id key
	 *
	 * @return  Donors
	 */
	public function getPaymentDetails($order_id_key)
	{
		$db    = Factory::getDbo();
		$query = "SELECT o.*, d.giveback_id, d.is_recurring, d.recurring_frequency,d.recurring_count, g.description as giveback_desc

		FROM `#__jg_orders` as o
		LEFT JOIN `#__jg_donations` as d ON o.donation_id = d.id
		LEFT JOIN `#__jg_campaigns_givebacks` as g ON d.giveback_id = g.id
		WHERE o.`id`=" . $order_id_key;
		$db->setQuery($query);
		$donor = $db->loadObject();

		return $donor;
	}

	/**
	 * Function to update status of order
	 *
	 * @param   INT      $order_id_key        Order id
	 * @param   String   $status              Order status
	 * @param   string   $comment             Comment
	 * @param   integer  $send_mail           Send mail
	 * @param   integer  $duplicate_response  Duplicate response flag
	 *
	 * @return  void
	 */
	public function updatestatus($order_id_key, $status, $comment = '', $send_mail = 1, $duplicate_response = 0)
	{
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$session             = Factory::getSession();
		$guest_email         = '';
		$guest_email         = $session->get('order_link_guestemail');
		$db                  = Factory::getDbo();

		if (empty($guest_email))
		{
			$query = $db->getQuery(true);
			$query->select('email')
				->from($db->qn('#__jg_donors', 'd'))
				->join('LEFT', $db->qn('#__jg_orders', 'o') . 'ON (' . $db->qn('o.donor_id') . ' = ' . $db->qn('d.id') . ')')
				->where($db->qn('o.id') . ' = ' . (int) $order_id_key);

			$db->setQuery($query);
			$guest_email = md5($db->loadResult());
		}

		$session->clear('order_link_guestemail');

		$query = $db->getQuery(true);
		$mainframe   = Factory::getApplication();
		$res         = new stdClass;
		$res->id     = $order_id_key;
		$res->status = $status;

		if ($res->status == 'C')
		{
			$res->payment_received_date = date("Y-m-d H:i:s");
		}

		if (!$db->updateObject('#__jg_orders', $res, 'id'))
		{
			return 2;
		}

		PluginHelper::importPlugin('system');
		$result = Factory::getApplication()->triggerEvent('onAfterJGPaymentStatusChange', array($order_id_key, $status ,$comment, 0));
	}

	/**
	 * Get OrderIdKey From OrderId
	 *
	 * @param   INT  $order_id  Order id
	 *
	 * @return integer
	 */
	public function getOrderIdKeyFromOrderId($order_id)
	{
		$orderData = Table::getInstance('Orders', 'JGiveTable');
		$orderData->load(array('order_id' => $order_id));

		return $orderData->id ? $orderData->id : 0;
	}

	/**
	 * Get Sold Givebacks
	 *
	 * @param   INT     $order_id_key  Order id
	 * @param   string  $status        Order status
	 *
	 * @return  boolean
	 */
	public function getSoldGivebacks($order_id_key, $status = '')
	{
		$orderData = Table::getInstance('Orders', 'JGiveTable');
		$orderData->load(array('id' => $order_id_key));
		$donationid = $orderData->donation_id;

		if ($donationid)
		{
			$donationsData = Table::getInstance('donations', 'JGiveTable');
			$donationsData->load(array('id' => $donationid));
			$givebackId = $donationsData->giveback_id;

			if ($givebackId)
			{
				$givebackesTable = Table::getInstance('GiveBacks', 'JGiveTable');
				$givebackesTable->load(array('id' => $givebackId));
				$quantity      = $givebackesTable->quantity;
				$totalQuantity = $givebackesTable->total_quantity;

				if ($quantity < $totalQuantity)
				{
					$quantity++;

					$db            = Factory::getDbo();
					$res           = new stdClass;
					$res->id       = $givebackId;
					$res->quantity = $quantity;
					$db->updateObject('#__jg_campaigns_givebacks', $res, 'id');

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Function to return status text as per the status code
	 *
	 * @param   STRING  $statusCode  Order status code
	 *
	 * @return  ARRAY  status text and class
	 *
	 * @since   2.2.0
	 * Deprecated in 2.5.4 use com_jgive/includes/utilities.php getOrderStatusText() instead.
	 */
	public function getOrderStatusText($statusCode)
	{
		$statusArray = array();

		switch ($statusCode)
		{
			case 'C' :
				$statusArray['statusText'] = Text::_('COM_JGIVE_CONFIRMED');
				$statusArray['statusClass'] = 'success';
			break;

			case 'RF' :
				$statusArray['statusText'] = Text::_('COM_JGIVE_REFUND');
				$statusArray['statusClass'] = 'error';
			break;

			case 'P' :
				$statusArray['statusText'] = Text::_('COM_JGIVE_PENDING');
				$statusArray['statusClass'] = 'warning';
			break;

			case 'E' :
				$statusArray['statusText'] = Text::_('COM_JGIVE_CANCELED');
				$statusArray['statusClass'] = 'error';
			break;

			case 'D' :
				$statusArray['statusText'] = Text::_('COM_JGIVE_DENIED');
				$statusArray['statusClass'] = 'error';
			break;
		}

		return $statusArray;
	}

	/**
	 * Method to Check if order is allowed to Complete Payment
	 *
	 * @param   INT  $order_id_key  Order id key
	 *
	 * @return  StdClass|boolean
	 *
	 * @since    1.8.1
	 */
	public function getAllowedRetryPayment($order_id_key)
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
		$orderData = Table::getInstance('Orders', 'JGiveTable');
		$orderData->load(array('id' => $order_id_key));

		if (empty($orderData->id))
		{
			return false;
		}

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'donation');
		$jgiveModelDonation = BaseDatabaseModel::getInstance('Donation', 'JGiveModel');
		$donation_details   = $jgiveModelDonation->getItem($order_id_key);

		$giveback_id = $donation_details['payment']->giveback_id;

		$giveback_data = Table::getInstance('GiveBacks', 'JGiveTable');
		$giveback_data->load(array('id' => $giveback_id));
		$donation_details['giveback'] = $giveback_data;

		$remaining_amount = $donation_details['campaign']->remaining_amount;
		$allow_exceed     = $donation_details['campaign']->allow_exceed;
		$minimum_amount   = $donation_details['campaign']->minimum_amount;
		$donation_amount  = $donation_details['payment']->amount;
		$giveback_amount  = $donation_details['giveback']->amount;
		$camp_end_date    = strtotime($donation_details['campaign']->end_date);
		$current_date     = strtotime(date('Y-m-d'));

		$retryPayment = new StdClass;

		if ($remaining_amount <= 0 && $allow_exceed == 0)
		{
			$remaining_amount_flag = 1;
			$retryPayment->status  = $remaining_amount_flag;
			$retryPayment->msg     = Text::_("COM_JGIVE_GOAL_ACHIEVED_MSG");
		}
		elseif ($donation_amount < $giveback_amount)
		{
			$donation_amt_flag    = 1;
			$retryPayment->status = $donation_amt_flag;
			$retryPayment->msg    = Text::_("COM_JGIVE_DONATION_AMT_GREATER_MSG");
		}
		elseif ($minimum_amount > $donation_amount)
		{
			$minimum_amt_flag     = 1;
			$retryPayment->status = $minimum_amt_flag;
			$retryPayment->msg    = Text::_("COM_JGIVE_MIN_AMT_GREATER_MSG");
		}
		elseif ($camp_end_date < $current_date)
		{
			$camp_end_date_flag   = 1;
			$retryPayment->status = $camp_end_date_flag;
			$retryPayment->msg    = Text::_("COM_JGIVE_CAMPAIGN_CLOSED_MSG");
		}
		else
		{
			$allowedRetryPayment_option = 0;
			$retryPayment->status       = $allowedRetryPayment_option;
			$retryPayment->msg          = Text::_("COM_JGIVE_ALLOWED_COMPLETE_DONATION");
		}

		return $retryPayment;
	}
	/**
	 * Function to encrypt PAN number
	 *
	 * @param string plain text
	 * @param string key to encrypt
	 * 
	 * 
	 * @return encrypted PAN number
	 * 
	 * @since 4.1.0
	 */
	public function encryptData($plaintext, $key) {
		$iv = openssl_random_pseudo_bytes(16);
		$encrypted = openssl_encrypt($plaintext, 'AES-256-CBC', $key, 0, $iv);
		return base64_encode($iv . $encrypted); // Store IV with encrypted text
	}
	
	/**
	 * Function to decrypt PAN number
	 * 
	 * @param string encrypted text
	 * @param string key to decrypt
	 *
	 * @return decrypted PAN number
	 * 
	 * @since 4.1.0
	 */
	public function decryptData($encryptedData, $key) {
		$data = base64_decode($encryptedData);
		$iv = substr($data, 0, 16);
		$encryptedText = substr($data, 16);
		return openssl_decrypt($encryptedText, 'AES-256-CBC', $key, 0, $iv);
	}
}
