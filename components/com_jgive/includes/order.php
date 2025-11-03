<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * JGive Order class
 *
 * @since  2.5.0
 */
class JGiveOrder extends CMSObject
{
	/**
	 * The auto incremental primary key of the order
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $id = 0;

	/**
	 * Order id with prefix
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $order_id = '';

	/**
	 * Campaign id - Foreign key of jg_campaign table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $campaign_id = 0;

	/**
	 * Donor id - Foreign key of jg_donor table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $donor_id = 0;

	/**
	 * Donation id - Foreign key of jg_donation table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $donation_id = 0;

	/**
	 * Fund_holder - Order amount holder
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $fund_holder = '';

	/**
	 * Order created date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $cdate = '';

	/**
	 * Order modified date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $mdate = '';

	/**
	 * Order amount recevied date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $payment_received_date = '';

	/**
	 * Order transaction id
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $transaction_id = '';

	/**
	 * Order original amount without fee
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $original_amount = 0;

	/**
	 * Order amount
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $amount = 0;

	/**
	 * Order fee
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $fee = 0;

	/**
	 * Vat number
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $vat_number = '';

	/**
	 * Order status
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $status = '';

	/**
	 * Order processor
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $processor = '';

	/**
	 * IP address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $ip_address = '';

	/**
	 * Order related extra information
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $extra = '';

	/**
	 * Holds the already loaded instances of the order
	 *
	 * @var    array
	 * @since   2.5.0
	 */
	private static $orderObj = array();

	/**
	 * Constructor activating the default information of the order
	 *
	 * @param   int  $id  The unique order key to load.
	 *
	 * @since   2.5.0
	 */
	public function __construct($id = 0)
	{
		if (!empty($id))
		{
			$this->load($id);
		}
	}

	/**
	 * Returns the order object
	 *
	 * @param   integer  $id  The primary key of the order to load (optional).
	 *
	 * @return  JGiveOrder  The order object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JGiveOrder;
		}

		// Check if the order id is already cached.
		if (empty(self::$orderObj[$id]))
		{
			self::$orderObj[$id] = new JGiveOrder($id);
		}

		return self::$orderObj[$id];
	}

	/**
	 * Method to load a order properties
	 *
	 * @param   int  $id  The order id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JGive::table("orders");

		if ($table->load($id))
		{
			$this->id                    = (int) $table->get('id');
			$this->order_id              = $table->get('order_id');
			$this->campaign_id           = (int) $table->get('campaign_id');
			$this->donor_id              = (int) $table->get('donor_id');
			$this->donation_id           = (int) $table->get('donation_id');
			$this->fund_holder           = $table->get('fund_holder');
			$this->cdate                 = $table->get('cdate');
			$this->mdate                 = $table->get('mdate');
			$this->payment_received_date = $table->get('payment_received_date');
			$this->transaction_id        = $table->get('transaction_id');
			$this->original_amount       = (float) $table->get('original_amount');
			$this->amount                = (float) $table->get('amount');
			$this->fee                   = (float) $table->get('fee');
			$this->vat_number            = $table->get('vat_number');
			$this->status                = $table->get('status');
			$this->processor             = $table->get('processor');
			$this->ip_address            = $table->get('ip_address');
			$this->extra                 = $table->get('extra');

			return true;
		}

		return false;
	}

	/**
	 *Overridden: Returns an associative array of object properties.
	 *
	 * @param   boolean  $public  If true, returns only the public properties.
	 *
	 * @return  array
	 *
	 * @since   2.5.0
	 *
	 * @see     CMSObject::get()
	 */
	public function getProperties($public = true)
	{
		$vars = get_object_vars($this);

		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if ('_' == substr($key, 0, 1))
				{
					unset($vars[$key]);
				}
			}
		}

		return $vars;
	}

	/**
	 * This method will return Order id
	 *
	 * @return  integer  return the id.
	 *
	 * @since  2.5.0
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * This method will return Order Id with Prefix
	 *
	 * @return  string  return Order Id with Prefix
	 *
	 * @since  2.5.0
	 */
	public function getOrderId()
	{
		return $this->order_id;
	}

	/**
	 * This method will return Campaign id
	 *
	 * @return  integer  return Campaign id.
	 *
	 * @since  2.5.0
	 */
	public function getCampaignId()
	{
		return $this->campaign_id;
	}

	/**
	 * This method will return Donor id
	 *
	 * @return  integer  return Donor id.
	 *
	 * @since  2.5.0
	 */
	public function getDonorId()
	{
		return $this->donor_id;
	}

	/**
	 * This method will return Donation Id
	 *
	 * @return  integer  return Donation Id
	 *
	 * @since  2.5.0
	 */
	public function getDonationId()
	{
		return $this->donation_id;
	}

	/**
	 * This method will return Fund holder
	 *
	 * @return  string  return Fund holder
	 *
	 * @since  2.5.0
	 */
	public function getFundHolder()
	{
		return $this->fund_holder;
	}

	/**
	 * This method will return Formatted or Without Formatted Order Created Date
	 *
	 * @param   boolean  $formattedDate  Default true
	 *
	 * @return  string  return Formatted or Without Formatted Order Created Date
	 *
	 * @since  2.5.0
	 */
	public function getCreatedDate($formattedDate = false)
	{
		return $formattedDate ? JGive::Utilities()->getFormattedDate($this->cdate): $this->cdate;
	}

	/**
	 * This method will return Modified Date
	 *
	 * @return  string  return Modified Date
	 *
	 * @since  2.5.0
	 */
	public function getModifiedDate()
	{
		return $this->mdate;
	}

	/**
	 * This method will return Formatted Payment Recevied Date or Without Formatted Payment Recevied Date
	 *
	 * @param   boolean  $formattedDate  Default false
	 *
	 * @return  string  return Formatted Payment Recevied Date or Without Formatted Payment Recevied Date
	 *
	 * @since  2.5.0
	 */
	public function getPaymentReceivedDate($formattedDate = false)
	{
		return $formattedDate ? JGive::Utilities()->getFormattedDate($this->payment_received_date): $this->payment_received_date;
	}

	/**
	 * This method will return Transaction Id
	 *
	 * @return  string  return Transaction Id
	 *
	 * @since  2.5.0
	 */
	public function getTransactionId()
	{
		return $this->transaction_id;
	}

	/**
	 * This method will return Formatted Original Amount or Without Formatted Original Amount
	 *
	 * @param   boolean  $formattedAmount  Default false
	 *
	 * @return  float  return Formatted Original Amount or Without Formatted Original Amount
	 *
	 * @since  2.5.0
	 */
	public function getOriginalAmount($formattedAmount = false)
	{
		return $formattedAmount? JGive::Utilities()->getFormattedPrice($this->original_amount):$this->original_amount;
	}

	/**
	 * This method will return Formatted Amount or Without Formatted  Amount
	 *
	 * @param   boolean  $formattedAmount  Default false
	 *
	 * @return  float  return Formatted Amount or Without Formatted  Amount
	 *
	 * @since  2.5.0
	 */
	public function getAmount($formattedAmount = false)
	{
		return $formattedAmount ? JGive::Utilities()->getFormattedPrice($this->amount) :$this->amount;
	}

	/**
	 * This method will return Formatted Fee or Without Formatted Fee
	 *
	 * @param   boolean  $formattedAmount  Default false
	 *
	 * @return  float  return Formatted Fee or Without Formatted Fee.
	 *
	 * @since  2.5.0
	 */
	public function getFee($formattedAmount = false)
	{
		return $formattedAmount ? JGive::Utilities()->getFormattedPrice($this->fee) :$this->fee;
	}

	/**
	 * This method will return Vat Number
	 *
	 * @return  string  return Vat Number
	 *
	 * @since  2.5.0
	 */
	public function getVatNumber()
	{
		return $this->vat_number;
	}

	/**
	 * This method will return Order Status
	 *
	 * @return  string  return Order Status
	 *
	 * @since  2.5.0
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * This method will return Payment Gateway Name
	 *
	 * @param   boolean  $formattedText  Default false
	 *
	 * @return  string  return the Payment Gateway Name.
	 *
	 * @since  2.5.0
	 */
	public function getProcessor($formattedText = false)
	{
		return $formattedText ? JGive::Utilities()->getPaymentGatewayName($this->processor) :$this->processor;
	}

	/**
	 * Method to add order
	 *
	 * @param   array  $data  data
	 *
	 * @return  integer|boolean  order id on success
	 *
	 * @since   2.5.0
	 */
	public function addOrder($data)
	{
		if (!$this->validateOrderData($data))
		{
			$this->setError($this->getError());

			return false;
		}

		$data['fee']             = $this->calculateFee($data);
		$data['donation_amount'] = JGive::utilities()->getRoundedAmount($data['donation_amount']);

		$params = JGive::config();

		if ($params->get('send_payments_to_owner'))
		{
			$data['fund_holder'] = 1;
		}

		if (!$this->bind($data))
		{
			$this->setError($this->getError());

			return false;
		}

		$result = $this->save();

		if (!$result)
		{
			// If order is not saved then delete the entries from donors and donations table
			$donorClass = JGive::donor($data['donor_id']);
			$donorClass->delete();

			$donationClass = JGive::donation($data['donation_id']);
			$donationClass->delete();

			$this->setError($this->getError());

			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Method to bind an associative array of data
	 *
	 * @param   array  $array  The associative array to bind to the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function bind($array)
	{
		if (isset($array['campaign_id']))
		{
			$this->campaign_id = $array['campaign_id'];
		}

		if (isset($array['donor_id']))
		{
			$this->donor_id = $array['donor_id'];
		}

		if (isset($array['donation_id']))
		{
			$this->donation_id = $array['donation_id'];
		}

		if (isset($array['fund_holder']))
		{
			$this->fund_holder = $array['fund_holder'];
		}

		if ($this->isNew())
		{
			$this->cdate = Factory::getDate()->toSql();
		}

		$this->mdate = Factory::getDate()->toSql();

		if (isset($array['payment_received_date']))
		{
			$this->payment_received_date = $array['payment_received_date'];
		}

		if (isset($array['transaction_id']))
		{
			$this->transaction_id = $array['transaction_id'];
		}

		if (isset($array['donation_amount']))
		{
			$this->original_amount = $array['donation_amount'];
		}

		if (isset($array['donation_amount']))
		{
			$this->amount = $array['donation_amount'];
		}

		if (isset($array['fee']))
		{
			$this->fee = $array['fee'];
		}

		if (isset($array['vat_number']))
		{
			$this->vat_number = $array['vat_number'];
		}

		if (isset($array['donation_status']))
		{
			$this->status = $array['donation_status'];
		}

		if (isset($array['gateways']))
		{
			$this->processor = $array['gateways'];
		}

		if (isset($array['ip_address']))
		{
			$this->ip_address = $array['ip_address'];
		}

		if (isset($array['extra']))
		{
			$this->extra = $array['extra'];
		}

		return true;
	}

	/**
	 * Method to save
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function save()
	{
		$isNew = $this->isNew();

		$table = JGive::table('orders');

		try
		{
			$table->bind($this->getProperties());

			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the order  in the database
			$result = $table->store();

			if ($result && $isNew)
			{
				$this->load($table->get('id'));

				$this->order_id = $this->generateOrderID($this->getId());

				if (!$this->save())
				{
					$this->setError($table->getError());

					return false;
				}

				return $this->id;
			}
			elseif ($result && !$isNew)
			{
				return $this->load($this->getId());
			}
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Method to delete data
	 *
	 * @return  boolean  True on success
	 *
	 * @since  2.5.0
	 */
	public function delete()
	{
		$table = JGive::table('orders');

		if (!$table->delete($this->getId()))
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to check is order new or not
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	private function isNew()
	{
		return $this->id < 1;
	}

	/**
	 * To generate order id with prefix
	 *
	 * @param   integer  $orderID  Order id
	 *
	 * @return  string|boolean  on success return order id with prefix
	 *
	 * @since 2.5.0
	 */
	public function generateOrderID($orderID)
	{
		if (empty($orderID))
		{
			$this->setError("COM_TJCART_EMPTY_ORDER_ID_NOT_ALLOWED");

			return false;
		}

		$params = JGive::config();

		// String length should not be more than 5
		$separator     = (string) $params->get('separator');
		$orderidPrefix = substr((string) $params->get('order_prefix'), 0, 5) . $separator;
		$maxLen        = 23;

		// Use padding length set by admin only if it is les than allowed(calculate) length
		$paddingCount  = (int) $params->get('padding_count') > $maxLen ? $maxLen : (int) $params->get('padding_count');

		if ($params->get('random_orderid'))
		{
			$utilitiesClass = JGive::utilities();
			$orderidPrefix .= $utilitiesClass->_random(5) . $separator;

			// Order_id column field ength - prefix_length - no_of_underscores - length_of_random number
			$len = (23 - 5 - 2 - 5);
		}
		else
		{
			/* This length shud be such that it matches the column lenth of primary key
			 It is used to add pading
			 order_id_column_field_length - prefix_length - no_of_underscores*/
			$len = (23 - 5 - 2);
		}

		if (strlen((string) $orderID) <= $len)
		{
			$append = '';

			for ($z = 0; $z < $paddingCount; $z++)
			{
				$append .= '0';
			}

			$append = $append . $orderID;
		}

		return $orderidPrefix . $append;
	}

	/**
	 * Method to validate order data
	 *
	 * @param   array  $data  Order data
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5.0
	 */
	public function validateOrderData($data)
	{
		$params = JGive::config();

		if (!$data['campaign_id'])
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_VALIDATION_CAMPAIGN_NOT_PRESENT'));

			return false;
		}

		$campaign = JGive::campaign($data['campaign_id']);
		$campaignData = $campaign->getProperties();

		if (!$data['donor_id'])
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_FORM_VALIDATION_INVALID_DONOR'));

			return false;
		}

		// Donation amount should not be Zero or negative
		if ($data['donation_amount'] <= 0)
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_NEGATIVE_ZERO_NUMBER_ERROR_MSG'));

			return false;
		}

		// Server side validation Invalid Payment Gateways
		if (!in_array($data['gateways'], $params->get('gateways')))
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_VALIDATION_WRONG_PAYMENT_METHOD'));

			return false;
		}

		if (($campaignData['minimum_amount'] > 0) && ( $data['donation_amount'] < $campaignData['minimum_amount']))
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_VALIDATION_DONATION_AMOUNT_GREATER_THAN_CAMPAIGN_AMOUNT'));

			return false;
		}

		// Payment Recevied Date Validation
		if (!empty($data['payment_received_date']) && ($data['payment_received_date'] > Factory::getDate()->Format("Y-m-d")))
		{
			$this->setError(Text::_('COM_JGIVE_PAYMENT_RECEIVED_DATE_ERROR_MESSAGE'));

			return false;
		}

		// Max allowed donor/investers validation
		if (isset($campaignData['max_donors']) && $campaignData['max_donors'] > 0)
		{
			$jgiveModelOrders = JGive::model('orders', array('ignore_request' => true));
			$jgiveModelOrders->setState("filter.campaign_id", $data['campaign_id']);
			$jgiveModelOrders->setState("filter.status", COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED);
			$donations = (array) $jgiveModelOrders->getItems();

			if (count($donations) >= (int) $campaignData['max_donors'])
			{
				$this->setError(Text::_('COM_JGIVE_CAMPAIGN_MAX_ALLOWED_DONOR_REACHED'));

				return false;
			}
		}

		// Donor not allow to donate more that goal amount if campaign not allowing exceed donation
		if ($campaignData['allow_exceed'] != 1)
		{
			// Donation amount grater than campaign goal amount
			if ($campaignData['goal_amount'] < $data['donation_amount'])
			{
				$this->setError(Text::sprintf('COM_JGIVE_GOAL_AMOUNT_EXCEED_NOT_ALLOW_MSG', $campaignData['goal_amount']));

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to calculate fee
	 *
	 * @param   array  $data  Order data
	 *
	 * @return  float  fee.
	 *
	 * @since   2.5.0
	 */
	public function calculateFee($data)
	{
		if ($data['donor_type'] === 'ind')
		{
			$donor = JGive::individual($data['individual_id']);
		}
		else
		{
			$donor = JGive::organization($data['individual_id']);
		}

		$donorData = $donor->getProperties();

		$params = JGive::config();

		$commissionFee       = $params->get('commission_fee');
		$fixedCommissionFee = $params->get('fixed_commissionfee');

		$sendPaymentsToOwner = $params->get('send_payments_to_owner');

		// Get the user groupwise commision form params
		$paramsUsergroup = $params->get('usergroup');

		$campaignCreator        = Factory::getUser($donorData['user_id']);
		$campCreatorGroupsIds = $campaignCreator->groups;

		$campDetails = JGive::campaign($data['campaign_id']);
		$campaignType = $campDetails->getType();

		$vendorId               = $campDetails->getVendorId();

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');

		$vendorCommissionTable   = Table::getInstance('vendorfee', 'TjvendorsTable', array());
		$vendorCommissionTable->load(array('vendor_id' => $vendorId, 'client' => 'com_jgive'));
		$vendorCommissionFlat    = $vendorCommissionTable->flat_commission;
		$vendorCommissionPercent = $vendorCommissionTable->percent_commission;

		if ($vendorCommissionPercent != 0 || $vendorCommissionFlat != 0)
		{
			$commissionFee = $vendorCommissionPercent;
			$fixedCommissionFee = $vendorCommissionFlat;
		}

		if (!$sendPaymentsToOwner)
		{
			if (!empty($paramsUsergroup))
			{
				$count = count($paramsUsergroup);

				for ($l = 0; $l < $count; $l = $l + 3)
				{
					if (in_array($paramsUsergroup[$l], $campCreatorGroupsIds))
					{
						if ($campaignType == 'donation')
						{
							$commissionFee = (int) ($paramsUsergroup[$l + 1]);
						}
						elseif ($campaignType == 'investment')
						{
							$commissionFee = (int) ($paramsUsergroup[$l + 2]);
						}

						break;
					}
				}
			}

			$fee = 0;

			if ($commissionFee > 0)
			{
				$fee = JGive::utilities()->getRoundedAmount((($data['donation_amount'] * $commissionFee) / 100) + $fixedCommissionFee);
			}

			return $fee;
		}
	}
}
