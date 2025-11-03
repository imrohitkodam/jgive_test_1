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

/**
 * JGive Donor class
 *
 * @since  2.5.0
 */
class JGiveDonor extends CMSObject
{
	/**
	 * The auto incremental primary key of the donor
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $id = 0;

	/**
	 * Donor user id (foreign key of user table)
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $user_id = 0;

	/**
	 * Donor contributor id (foreign key of individual or organization table)
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $contributor_id = 0;

	/**
	 * Donor type (ind/org)
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $donor_type = '';

	/**
	 * Campaign id (foreign key of campaign table)
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $campaign_id = 0;

	/**
	 * Donor email id
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $email = '';

	/**
	 * Donor first name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $first_name = '';

	/**
	 * Donor last name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $last_name = '';

	/**
	 * Organization name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $org_name = '';

	/**
	 * Donor address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $address = '';

	/**
	 * Donor address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $address2 = '';

	/**
	 * Donor city (Tjfield city table id)
	 *
	 * @var    object
	 * @since  2.5.0
	 */
	private $city;

	/**
	 * Donor state (Tjfield region table id)
	 *
	 * @var    object
	 * @since  2.5.0
	 */
	private $state;

	/**
	 * Donor country (Tjfield country table id)
	 *
	 * @var    object
	 * @since  2.5.0
	 */
	private $country;

	/**
	 * Donor zip
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $zip = '';

	/**
	 * Donor phone number
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $phone = '';

	/**
	 * Donor taxnumber
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $taxnumber = '';

	/**
	 * holds the already loaded instances of the donor
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	private static $donorObj = array();

	/**
	 * Constructor activating the default information of the donor
	 *
	 * @param   int  $id  The unique donor key to load.
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
	 * Returns the global donor object
	 *
	 * @param   integer  $id  The primary key of the donor to load
	 *
	 * @return  JGiveDonor  The donor object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JGiveDonor;
		}

		// Check if the donor id is already cached.
		if (empty(self::$donorObj[$id]))
		{
			self::$donorObj[$id] = new JGiveDonor($id);
		}

		return self::$donorObj[$id];
	}

	/**
	 * Method to load a donor properties
	 *
	 * @param   int  $id  The donor id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JGive::table("donor");

		if ($table->load($id))
		{
			$this->id             = (int) $table->get('id');
			$this->user_id        = (int) $table->get('user_id');
			$this->contributor_id = (int) $table->get('contributor_id');
			$this->donor_type     = $table->get('donor_type');
			$this->campaign_id    = (int) $table->get('campaign_id');
			$this->email          = $table->get('email');
			$this->first_name     = $table->get('first_name');
			$this->last_name      = $table->get('last_name');
			$this->org_name       = $table->get('org_name');
			$this->address        = $table->get('address');
			$this->address2       = $table->get('address2');
			$this->city           = JGive::Utilities()->getCity((int) $table->get('city'));
			$this->state         = JGive::Utilities()->getRegion((int) $table->get('state'));
			$this->country        = JGive::Utilities()->getCountry((int) $table->get('country'));
			$this->zip            = $table->get('zip');
			$this->phone          = $table->get('phone');
			$this->taxnumber      = $table->get('taxnumber');

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
	 * Method to load a individual or organization properties
	 *
	 * @return  Mixed  Object on success, false on failure
	 *
	 * @since   2.5.0
	 */
	public function loadByType()
	{
		if ($this->contributor_id != 0 && !empty($this->donor_type))
		{
			if ($this->donor_type == 'org')
			{
				self::$donorObj[$this->id] = JGive::Organization($this->contributor_id);
			}

			self::$donorObj[$this->id] = JGive::Individual($this->contributor_id);
		}

		return self::$donorObj[$this->id];
	}

	/**
	 * Method to get the id
	 *
	 * @return  integer return the id.
	 *
	 * @since  2.5.0
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Method to get the name
	 *
	 * @return  string return the name
	 *
	 * @since   2.5.0
	 */
	public function getName()
	{
		return $this->first_name . " " . $this->last_name;
	}

	/**
	 * Method to get the type
	 *
	 * @return  string return the type
	 *
	 * @since   2.5.0
	 */
	public function getType()
	{
		return $this->donor_type;
	}

	/**
	 * Method to get the mobile no
	 *
	 * @return  string return the mobile no
	 *
	 * @since   2.5.0
	 */
	public function getContact()
	{
		return $this->phone;
	}

	/**
	 * Method to get the email
	 *
	 * @return  string return the email.
	 *
	 * @since   2.5.0
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Method to get the address
	 *
	 * @return  string return the address.
	 *
	 * @since   2.5.0
	 */
	public function getAddress()
	{
		return $this->address2 ? $this->address . ", " . $this->address2 :  $this->address;
	}

	/**
	 * Method to get the City
	 *
	 * @return  object return the City object.
	 *
	 * @since   2.5.0
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * Method to get the State
	 *
	 * @return  object return the State object.
	 *
	 * @since   2.5.0
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Method to get the Country
	 *
	 * @return  object return the Country object.
	 *
	 * @since   2.5.0
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * Method to get the zip
	 *
	 * @return  string return the zip.
	 *
	 * @since   2.5.0
	 */
	public function getZip()
	{
		return $this->zip;
	}

	/**
	 * Method to get the taxnumber
	 *
	 * @return  string return the taxnumber.
	 *
	 * @since   2.5.0
	 */
	public function getTaxNumber()
	{
		return $this->taxnumber;
	}

	/**
	 * Method to get the foreign key of organization / individual table based on the donor type.
	 *
	 * @return  integer return the contributor_id.
	 *
	 * @since   2.5.0
	 */
	public function getContributorId()
	{
		return $this->contributor_id;
	}

	/**
	 * Method to get the campaign_id
	 *
	 * @return  integer return the campaign_id.
	 *
	 * @since   2.5.0
	 */
	public function getCampaignId()
	{
		return $this->campaign_id;
	}

	/**
	 * Method to get the user_id
	 *
	 * @return  integer return the user_id.
	 *
	 * @since   2.5.0
	 */
	public function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Method to get the org_name
	 *
	 * @return  string return the org_name.
	 *
	 * @since   2.5.0
	 */
	public function getOrganizationName()
	{
		return $this->org_name;
	}

	/**
	 * Method to get the donor full name
	 *
	 * @param   string  $type  type of donor
	 *
	 * @return  string return the donor name.
	 *
	 * @since   2.5.0
	 */
	public function getDonorName($type = '')
	{
		if ($type == 'org')
		{
			return $this->getOrganizationName();
		}
		else
		{
			return $this->getName();
		}
	}

	/**
	 * Function for adding donor in donors table
	 *
	 * @param   array  $data  data array
	 *
	 * @return  integer|boolean
	 */
	public function addDonor($data)
	{
		if ($data['donor_type'] === 'ind')
		{
			$donor = JGive::individual($data['contributor_id']);
		}
		else
		{
			$donor = JGive::organization($data['contributor_id']);
		}

		$donorData = $donor->getProperties();
		$donorData['campaign_id'] = $data['campaign_id'];
		$donorData['donor_type'] = $data['donor_type'];

		if ($this->bind($donorData))
		{
			if (!$this->save())
			{
				$this->setError($this->getError());

				return false;
			}

			return $this->getId();
		}

		return false;
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
		if (isset($array['user_id']))
		{
			$this->user_id = $array['user_id'];
		}

		if (isset($array['id']))
		{
			$this->contributor_id = $array['id'];
		}

		if (isset($array['donor_type']))
		{
			$this->donor_type = $array['donor_type'];
		}

		if (isset($array['campaign_id']))
		{
			$this->campaign_id = $array['campaign_id'];
		}

		if (isset($array['email']))
		{
			$this->email = $array['email'];
		}

		if (isset($array['first_name']))
		{
			$this->first_name = $array['first_name'];
		}

		if (isset($array['last_name']))
		{
			$this->last_name = $array['last_name'];
		}

		if (isset($array['name']))
		{
			$this->org_name = $array['name'];
		}

		if (isset($array['addr_line_1']))
		{
			$this->address = $array['addr_line_1'];
		}

		if (isset($array['addr_line_2']))
		{
			$this->address2 = $array['addr_line_2'];
		}

		if (isset($array['city']))
		{
			$this->city = $array['city']->id;
		}

		if (isset($array['region']))
		{
			$this->state = $array['region']->id;
		}

		if (isset($array['country']))
		{
			$this->country = $array['country']->id;
		}

		if (isset($array['zip']))
		{
			$this->zip = $array['zip'];
		}

		if (isset($array['phone']))
		{
			$this->phone = $array['phone'];
		}

		if (isset($array['taxnumber']))
		{
			$this->taxnumber = $array['taxnumber'];
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
		$table = JGive::table('donor');

		try
		{
			$result = $table->save($this->getProperties());

			if (!$result)
			{
				$this->setError($table->getError());

				return false;
			}

			return $this->load($table->get('id'));
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
		$table = JGive::table('donor');

		if (!$table->delete($this->getId()))
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to get total donated amount
	 *
	 * @param   boolean  $formattedAmount  get the amount with or without currency symbol as per configuration
	 *
	 * @return  integer  return total amount
	 *
	 * @since  2.5.0
	 */
	public function getTotalDonatedAmount($formattedAmount = false)
	{
		$orderModel = JGive::model('orders', array('ignore_request' => true));
		$orderModel->setState('filter.contributor_id', $this->contributor_id);
		$orderModel->setState('filter.status', COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED);
		$orderModel->setState('filter.donor_type', $this->donor_type);
		$orderModel->setState('filter.campaign_creator_id', Factory::getUser()->id);

		$totalAmount = $orderModel->getDonationAmountSum();

		return $formattedAmount ? JGive::utilities()->getFormattedPrice(($totalAmount)) :$totalAmount;
	}

	/**
	 * Method to get recent donation
	 *
	 * @return  object  return donation object
	 *
	 * @since  2.5.0
	 */
	public function recentDonatedAmount()
	{
		$orderModel = JGive::model('orders', array('ignore_request' => true));
		$orderModel->setState('filter.contributor_id', $this->contributor_id);
		$orderModel->setState('filter.status', COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED);
		$orderModel->setState('filter.donor_type', $this->donor_type);
		$orderModel->setState('list.direction', 'desc');
		$orderModel->setState('filter.campaign_creator_id', Factory::getUser()->id);
		$orderModel->setState('list.limit', 1);

		$orderData  = $orderModel->getItems();
		$orderData[0]->formatted_original_amount = JGive::utilities()->getFormattedPrice(($orderData[0]->original_amount));

		return $orderData;
	}

	/**
	 * Method to get donated campaigns
	 *
	 * @return  array  return array of campaigns
	 *
	 * @since  2.5.0
	 */
	public function getDonatedCampaigns()
	{
		$orderModel = JGive::model('orders', array('ignore_request' => true));
		$orderModel->setState('filter.contributor_id', $this->contributor_id);
		$orderModel->setState('filter.status', COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED);
		$orderModel->setState('filter.donor_type', $this->donor_type);
		$orderModel->setState('filter.campaign_creator_id', Factory::getUser()->id);
		$orderModel->setState('filter.group', 'campaign_id');

		$orders  = $orderModel->getItems();

		$campaings = array();

		foreach ($orders as $order)
		{
			$campaignClass = JGive::campaign($order->campaign_id);

			$campaings[] = $campaignClass->getProperties();
		}

		return $campaings;
	}

	/**
	 * Method to get donor table id
	 *
	 * @param   integer  $contributorId  contributor id of org/ind
	 *
	 * @param   string   $type           contributor
	 *
	 * @return  integer  donor id
	 *
	 * @since  2.5.0
	 */
	public function getDonorIDByContactDetails($contributorId, $type)
	{
		$donorTable = JGive::table("donor");
		$donorTable->load(array('contributor_id' => $contributorId, 'donor_type' => $type));

		return $donorTable->id;
	}

	/**
	 * Method to get last donation date
	 *
	 * @return  string  donation date
	 *
	 * @since  2.5.0
	 */
	public function getLastDonationDate()
	{
		$orderdata = $this->recentDonatedAmount();

		$db       = Factory::getDbo();
		$nullDate = $db->getNullDate();

		// Check date is null 00-00-00 00:00
		if ($orderdata[0]->payment_received_date == $nullDate)
		{
			$donationDate = $orderdata[0]->cdate;
		}
		else
		{
			$donationDate = $orderdata[0]->payment_received_date;
		}

		return JGive::utilities()->getFormattedDate($donationDate);
	}

	/**
	 * Method to get total number donations of contacts
	 *
	 * @return  integer  total number of donations
	 *
	 * @since  2.5.0
	 */
	public function getAllDonationsCount()
	{
		$orderModel = JGive::model('orders', array('ignore_request' => true));
		$orderModel->setState('filter.contributor_id', $this->contributor_id);
		$orderModel->setState('filter.status', COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED);
		$orderModel->setState('filter.donor_type', $this->donor_type);
		$orderModel->setState('filter.campaign_creator_id', Factory::getUser()->id);

		return $orderModel->getTotal();
	}
}
