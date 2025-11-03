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

/**
 * JGive Donation class
 *
 * @since  2.5.0
 */
class JGiveDonation extends CMSObject
{
	/**
	 * The auto incremental primary key of the donation
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $id = 0;

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
	 * Order id - Foreign key of jg_order table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $order_id = 0;

	/**
	 * Giveback id - Foreign key of jg_givebacks table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $giveback_id = 0;

	/**
	 * Annonymous donation - Donation is annonymous donation or not flag
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $annonymous_donation = 0;

	/**
	 * Is recurring - Donation is recurring or not flag
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $is_recurring = 0;

	/**
	 * Recurring frequency - Donation recurring frequency
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $recurring_frequency = '';

	/**
	 * Recurring count - Donation recurring count
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $recurring_count = 0;

	/**
	 * Subcribe id
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $subscr_id = '';

	/**
	 * Comment - Commend added by donor while making donation
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $comment = '';

	/**
	 * Holds the already loaded instances of the donation
	 *
	 * @var    array
	 * @since   2.5.0
	 */
	private static $donationObj = array();

	/**
	 * Constructor activating the default information of the donation
	 *
	 * @param   int  $id  The unique donation key to load.
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
	 * Returns the global donation object
	 *
	 * @param   integer  $id  The primary key of the donation to load (optional).
	 *
	 * @return  JGiveDonation  The donation object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JGiveDonation;
		}

		// Check if the donation id is already cached.
		if (empty(self::$donationObj[$id]))
		{
			self::$donationObj[$id] = new JGiveDonation($id);
		}

		return self::$donationObj[$id];
	}

	/**
	 * Method to load a donation properties
	 *
	 * @param   int  $id  The donation id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JGive::table("donations");

		if ($table->load($id))
		{
			$this->id                  = (int) $table->get('id');
			$this->campaign_id         = (int) $table->get('campaign_id');
			$this->donor_id            = (int) $table->get('donor_id');
			$this->order_id            = (int) $table->get('order_id');
			$this->giveback_id         = (int) $table->get('giveback_id');
			$this->annonymous_donation = (int) $table->get('annonymous_donation');
			$this->is_recurring        = (int) $table->get('is_recurring');
			$this->recurring_frequency = $table->get('recurring_frequency');
			$this->recurring_count     = (int) $table->get('recurring_count');
			$this->subscr_id           = $table->get('subscr_id');
			$this->comment             = $table->get('comment');

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
	 * This method will return Id
	 *
	 * @return  integer  return the Id.
	 *
	 * @since  2.5.0
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * This method will return Campaign id
	 *
	 * @return  integer  return the Campaign id.
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
	 * @return  integer  return the Donor id.
	 *
	 * @since  2.5.0
	 */
	public function getDonorId()
	{
		return $this->donor_id;
	}

	/**
	 * This method will return Order id
	 *
	 * @return  integer  return the Order id.
	 *
	 * @since  2.5.0
	 */
	public function getOrderId()
	{
		return $this->order_id;
	}

	/**
	 * This method will return Giveback id
	 *
	 * @return  integer  return the Giveback id.
	 *
	 * @since  2.5.0
	 */
	public function getGivebackId()
	{
		return $this->giveback_id;
	}

	/**
	 * Method to detect donation is Annonymous or not
	 *
	 * @return  boolean  true - is annonymous donation ; false - is not annonymous Donation
	 *
	 * @since  2.5.0
	 */
	public function isAnnonymousDonation()
	{
		if ($this->annonymous_donation)
		{
			return true;
		}

		return false;
	}

	/**
	 * This method will return is Recurring donation or not
	 *
	 * @return  boolean  true - is recurring ; false - is not recurring
	 *
	 * @since  2.5.0
	 */
	public function isRecurring()
	{
		if ($this->is_recurring)
		{
			return true;
		}

		return false;
	}

	/**
	 * This method will return donation recurring frequency
	 *
	 * @return  string  return the recurring donation frequency in duration day, month, year
	 *
	 * @since  2.5.0
	 */
	public function getRecurringFrequency()
	{
		return $this->recurring_frequency;
	}

	/**
	 * This method will return donation recurring count
	 *
	 * @return  integer  return the Recurring count
	 *
	 * @since  2.5.0
	 */
	public function getRecurringCount()
	{
		return $this->recurring_count;
	}

	/**
	 * This method will return Comment added by donor while doing donation
	 *
	 * @return  string  return the Comment
	 *
	 * @since  2.5.0
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * Method to add donation
	 *
	 * @param   array  $data  data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function addDonation($data)
	{
		$donor = JGive::donor();
		$donerId = $donor->addDonor($data);

		if (!$donerId)
		{
			$this->setError($donor->getError());

			return false;
		}

		$data['donor_id'] = $donerId;

		if (!$this->save())
		{
			$donorClass = JGive::donor($data['donor_id']);
			$donorClass->delete();

			$this->setError($this->getError());

			return false;
		}

		$data['donation_id'] = $this->getId();

		$order = JGive::order();

		$result = $order->addOrder($data);

		if (!$result)
		{
			$this->setError($order->getError());

			return false;
		}

		return $result;
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

		if (isset($array['annonymous_donation']))
		{
			$this->annonymous_donation = $array['annonymous_donation'];
		}

		if (isset($array['is_recurring']))
		{
			$this->is_recurring = $array['is_recurring'];
		}

		if (isset($array['recurring_frequency']))
		{
			$this->recurring_frequency = $array['recurring_frequency'];
		}

		if (isset($array['recurring_count']))
		{
			$this->recurring_count = $array['recurring_count'];
		}

		if (isset($array['comment']))
		{
			$this->comment = $array['comment'];
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
		$table = JGive::table('donations');

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
		$table = JGive::table('donations');

		if (!$table->delete($this->getId()))
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}
}
