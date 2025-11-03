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

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Factory;

include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * JGive Promoter class
 *
 * @since  2.5.0
 */
class JGivePromoter extends CMSObject
{
	/**
	 * The auto incremental primary key of the campaign
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $id = 0;

	/**
	 * Vendor id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $vendor_id = 0;

	/**
	 * Campaign creator user id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $user_id = 0;

	/**
	 * Campaign promoter first name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $first_name = '';

	/**
	 * Campaign promoter last name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $last_name = '';

	/**
	 * Campaign promoter name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $full_name = '';

	/**
	 * Campaign promoter user name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $user_name = '';

	/**
	 * Campaign promoter address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $address = '';

	/**
	 * Campaign promoter address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $address2 = '';

	/**
	 * Campaign promoter full address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $full_address = '';

	/**
	 * Campaign promoter city
	 *
	 * @var    object
	 * @since  2.5.0
	 */
	private $city = 0;

	/**
	 * Campaign promoter other city(if not found in city dropdown)
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $other_city = '';

	/**
	 * Campaign promoter state
	 *
	 * @var    object
	 * @since  2.5.0
	 */
	private $state = 0;

	/**
	 * Campaign promoter country
	 *
	 * @var    object
	 * @since  2.5.0
	 */
	private $country = 0;

	/**
	 * Campaign promoter zip
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $zip = '';

	/**
	 * Campaign promoter phone number
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $phone = '';

	/**
	 * Website address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $website_address = '';

	/**
	 * holds the already loaded instances of the campaign-promoter
	 *
	 * @var    array
	 * @since   2.5.0
	 */
	private static $promoterObj = array();

	/**
	 * Constructor activating the default information of the campaign
	 *
	 * @param   int  $id  The unique campaign key to load.
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
	 * Returns the global promoter object
	 *
	 * @param   integer  $id  The primary key of the campaign to load (optional).
	 *
	 * @return  JGiveCampaign  The campaign object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JGivePromoter;
		}

		// Check if the campaign id is already cached.
		if (empty(self::$promoterObj[$id]))
		{
			self::$promoterObj[$id] = new JGivePromoter($id);
		}

		return self::$promoterObj[$id];
	}

	/**
	 * Method to load a campaign properties
	 *
	 * @param   int  $id  The campaign id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		// @Todo: Need to use promoter entity in future
		$table = JGive::table("campaign");

		if ($table->load($id))
		{
			$this->id              = (int) $table->get('id');
			$this->vendor_id       = (int) $table->get('vendor_id');
			$vendorObj             = TJVendors::vendor($this->vendor_id, 'com_jgive');
			$this->first_name      = $table->get('first_name') ? $table->get('first_name') : $vendorObj->getTitle();
			$this->last_name       = $table->get('last_name');
			$this->user_id         = (int) $table->get('creator_id');
			$this->user_name       = Factory::getUser($this->user_id)->username;
			$this->full_name       = $this->getName();
			$this->address 	       = $table->get('address')? $table->get('address'): $vendorObj->getAddress();
			$this->address2	       = $table->get('address2');
			$this->full_address    = $this->getAddress();
			$this->city            = $vendorObj->getCity();
			$this->state           = $vendorObj->getRegion();
			$this->country         = $vendorObj->getCountry();
			$this->other_city      = $vendorObj->getOtherCity();
			$this->zip             = $vendorObj->getZip();
			$this->phone           = $vendorObj->getPhoneNumber();
			$this->website_address = $vendorObj->getWebsiteAddress();
			$this->vat_number      = $vendorObj->getVatNumber();

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
	 * Method to get promoter name
	 *
	 * @return  string  return promoter name
	 *
	 * @since  2.5.0
	 */
	public function getName()
	{
		if ($this->first_name && $this->last_name)
		{
			return ucwords($this->first_name) . " " . ucwords($this->last_name);
		}
	}

	/**
	 * Method to get promoter address
	 * 
	 * @return  string  return promoter address
	 *
	 * @since  2.5.0
	 */
	public function getAddress()
	{
		return $this->address2 ? $this->address . ", " . $this->address2 :  $this->address;
	}

	/**
	 * Method to get zip code
	 *
	 * @return  string  return zip code
	 *
	 * @since  2.5.0
	 */
	public function getZipCode()
	{
		return $this->zip;
	}

	/**
	 * Method to get phone number
	 *
	 * @return  integer  return phone number
	 *
	 * @since  2.5.0
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * Method to get website
	 *
	 * @return  string  return website
	 *
	 * @since  2.5.0
	 */
	public function getWebsite()
	{
		return $this->website_address;
	}

	/**
	 * Method to get total donation amount received by promoter
	 *
	 * @param   boolean  $formattedAmount  get the amount with or without currency symbol as per configuration
	 * 
	 * @return  integer  return total amount
	 *
	 * @since  2.5.0
	 */
	public function getReceivedAmount($formattedAmount = false)
	{
		$orderModel = JGive::model('orders', array('ignore_request' => true));
		$orderModel->setState('filter.campaign_creator_id', Factory::getUser()->id, 'INT');
		$orderModel->setState('filter.status', COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED, 'STRING');
		$orderModel->setState('filter.campaign_success_status', '0', 'STRING');
		$orderModel->setState('filter.campaign_published', '1', 'STRING');
		$totalAmount = $orderModel->getDonationAmountSum();

		return $formattedAmount ? JGive::utilities()->getFormattedPrice(($totalAmount)) :$totalAmount;
	}

	/**
	 * Method to get total count of campaign
	 *
	 * @param   int  $status  campaign status 0 - Ongoing, 1 - Successful, -1 - Failed
	 * 
	 * @return  integer  return total count
	 *
	 * @since  2.5.0
	 */
	public function getCampaignCount($status = COM_JGIVE_CONSTANT_CAMPAIGN_STATUS_ONGOING)
	{
		$campaignsModel = JGive::model('Campaigns', array('ignore_request' => true));
		$campaignsModel->setState('filter.creator_id', Factory::getUser()->id);
		$campaignsModel->setState('filter.success_status', $status);

		return $campaignsModel->getTotal();
	}
}
