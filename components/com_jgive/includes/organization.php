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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;

/**
 * JGive Donor class
 *
 * @since  2.5.0
 */
class JGiveOrganization extends CMSObject
{
	/**
	 * The auto incremental primary key of the organization donor
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $id = 0;

	/**
	 * Donor name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $name = '';

	/**
	 * Donor email id
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $email = '';

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
	 * Donor user id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $user_id = 0;

	/**
	 * Donor address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $addr_line_1 = '';

	/**
	 * Donor address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $addr_line_2 = '';

	/**
	 * Donor city (Tjfield city table id)
	 *
	 * @var    object
	 * @since  2.5.0
	 */
	private $city;

	/**
	 * Donor region (Tjfield region table id)
	 *
	 * @var    object
	 * @since  2.5.0
	 */
	private $region;

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
	 * Donor other_city_check
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $other_city_check = 0;

	/**
	 * Donor other_city_value
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $other_city_value = '';

	/**
	 * Donor website
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $website = '';

	/**
	 * Donor published
	 *
	 * @var    boolean
	 * @since  2.5.0
	 */
	private $published = '';

	/**
	 * Donor created_date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $created_date = '';

	/**
	 * Donor created_by
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $created_by = 0;

	/**
	 * Donor modified_date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $modified_date = '';

	/**
	 * Donor modified_by
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $modified_by = 0;

	/**
	 * Donor vendor_id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $vendor_id = 0;

	/**
	 * holds the already loaded instances of the donor
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	private static $organizationObj = array();

	/**
	 * Holds the custom fields
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	private $com_fields = array();

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
	 * Returns the global organization donor object
	 *
	 * @param   integer  $id  The primary key of the organization donor to load
	 *
	 * @return  JGiveOrganization The donor object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JGiveOrganization;
		}

		// Check if the donor id is already cached.
		if (empty(self::$organizationObj[$id]))
		{
			self::$organizationObj[$id] = new JGiveOrganization($id);
		}

		return self::$organizationObj[$id];
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
		$table = JGive::table("organization");

		if ($table->load($id))
		{
			$this->id               = (int) $table->get('id');
			$this->user_id          = (int) $table->get('user_id');
			$this->email            = $table->get('email');
			$this->name             = $table->get('name');
			$this->addr_line_1      = $table->get('addr_line_1');
			$this->addr_line_2      = $table->get('addr_line_2');
			$this->city             = JGive::Utilities()->getCity((int) $table->get('city'));
			$this->region           = JGive::Utilities()->getRegion((int) $table->get('region'));
			$this->country          = JGive::Utilities()->getCountry((int) $table->get('country'));
			$this->zip              = $table->get('zip');
			$this->phone            = $table->get('phone');
			$this->taxnumber        = $table->get('taxnumber');
			$this->other_city_check = (int) $table->get('other_city_check');
			$this->other_city_value = (int) $table->get('other_city_value');
			$this->website          = $table->get('website');
			$this->published        = $table->get('published');
			$this->created_date     = $table->get('created_date');
			$this->modified_date    = $table->get('modified_date');
			$this->created_by       = $table->get('created_by');
			$this->modified_by      = $table->get('modified_by');
			$this->vendor_id        = $table->get('vendor_id');

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
		return $this->name;
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
		return $this->addr_line_2 ? $this->addr_line_1 . ", " . $this->addr_line_2 :  $this->addr_line_1;
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
	public function getRegion()
	{
		return $this->region;
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
	 * Method to get the other_city_check
	 *
	 * @return  integer return the other_city_check.
	 *
	 * @since   2.5.0
	 */
	public function getOtherCityCheck()
	{
		return $this->other_city_check;
	}

	/**
	 * Method to get the other_city_value
	 *
	 * @return  string return the other_city_value.
	 *
	 * @since   2.5.0
	 */
	public function getOtherCityValue()
	{
		return $this->other_city_value;
	}

	/**
	 * Method to get the website
	 *
	 * @return  string return the website.
	 *
	 * @since   2.5.0
	 */
	public function getWebsite()
	{
		return $this->website;
	}

	/**
	 * Method to get the contact created date
	 *
	 * @param   boolean  $formattedDate  If true, returns formatted Date.
	 *
	 * @return  string  return formatted created date
	 *
	 * @since   2.5.0
	 */
	public function getCreatedDate($formattedDate = false)
	{
		return $formattedDate ? JGive::utilities()->getFormattedDate($this->created_date) : $this->created_date;
	}

	/** Method to add individual donor
	 *
	 * @param   array  $data  data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function addOrganization($data)
	{
		if (empty($data['vendor_id']) || (!array_key_exists('vendor_id', $data)))
		{
			$utilityClass = JGive::utilities();
			$vendorId     = $utilityClass->getVendorId(Factory::getUser()->id, "com_jgive");

			if (empty($vendorId))
			{
				$vendorId = $utilityClass->createVendor();

				if ($vendorId == false)
				{
					$this->setError(Text::_('COM_JGIVE_ORGANIZATION_FORM_INVALID_VENDOR'));

					return false;
				}
			}

			$data['vendor_id'] = $vendorId;
		}

		$this->load($data['id']);
		$isRecordExist = $this->isExist($data['email'], (int) $data['vendor_id']);

		if ($isRecordExist == 0)
		{
			$this->setError(Text::_('JLIB_DATABASE_ERROR_EMAIL_INUSE'));

			return false;
		}

		$data['published'] = 1;

		if ($this->bind($data))
		{
			$result = $this->save();

			if (!$result)
			{
				$this->setError($this->getError());

				return false;
			}

			if (!empty($data['contact_id']))
			{
				$data['id'] = $result;
				$orgContactId = $this->addContact($data);

				if (!$orgContactId)
				{
					$this->setError($this->getError());

					return false;
				}
			}

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
		if (isset($array['id']))
		{
			$this->id = $array['id'];
		}

		if (isset($array['name']))
		{
			$this->name = $array['name'];
		}

		if (isset($array['email']))
		{
			$this->email = $array['email'];
		}

		if (isset($array['phone']))
		{
			$this->phone = $array['phone'];
		}

		if (isset($array['taxnumber']))
		{
			$this->taxnumber = $array['taxnumber'];
		}

		if (isset($array['website']))
		{
			$this->website = $array['website'];
		}

		if (isset($array['user_id']))
		{
			$this->user_id = $array['user_id'];
		}

		if (isset($array['addr_line_1']))
		{
			$this->addr_line_1 = $array['addr_line_1'];
		}

		if (isset($array['addr_line_2']))
		{
			$this->addr_line_2 = $array['addr_line_2'];
		}

		if (isset($array['country']))
		{
			$this->country = $array['country'];
		}

		if (isset($array['region']))
		{
			$this->region = $array['region'];
		}

		if (isset($array['city']))
		{
			$this->city = $array['city'];
		}

		if (isset($array['other_city_check']))
		{
			$this->other_city_check = $array['other_city_check'];
		}

		if (isset($array['other_city_value']))
		{
			$this->other_city_value = $array['other_city_value'];
		}

		if (isset($array['zip']))
		{
			$this->zip = $array['zip'];
		}

		if (isset($array['published']))
		{
			$this->published = $array['published'];
		}

		if (isset($array['vendor_id']))
		{
			$this->vendor_id = $array['vendor_id'];
		}

		// Set created date if item is new
		if ($this->isNew())
		{
			$this->created_by   = Factory::getUser()->id;
			$this->created_date = Factory::getDate()->toSql();
		}

		// Set modified date
		$this->modified_by   = Factory::getUser()->id;
		$this->modified_date = Factory::getDate()->toSql();

		if (isset($array['com_fields']))
		{
			$this->com_fields = $array['com_fields'];
		}

		return true;
	}

	/**
	 * Method to save
	 *
	 * @return  integer|boolean id on success
	 *
	 * @since   2.5.0
	 */
	public function save()
	{
		$table = JGive::table('organization');

		try
		{
			$isNew = (empty($this->id)) ? true: false;
			$result = $table->save(array_filter($this->getProperties()));

			if (!$result)
			{
				$this->setError($table->getError());

				return false;
			}

			$this->load($table->get('id'));

			Factory::getApplication()->triggerEvent('onContentAfterSave', array('com_jgive.organization', $table, $isNew, $this->getProperties()));

			return $this->id;
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Method to validate form data
	 *
	 * @param   array  $data  the array of form data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function validateFormData($data)
	{
		$organizationFormModel = JGive::model('organizationform', array('ignore_request' => true));

		Form::addFormPath(JPATH_SITE . '/components/com_jgive/models/forms');
		$form = $organizationFormModel->getForm();

		$formData = $organizationFormModel->validate($form, $data);

		if ($formData === false)
		{
			// Handle how to handle the multiple errors in api
			$errors = $organizationFormModel->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i ++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$this->setError($errors[$i]->getMessage());
				}
				else
				{
					$this->setError($errors[$i]);
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Method to check the entry is new or not
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
	 * Checking count of existing email in organization table
	 *
	 * @param   string   $email     email
	 * @param   integer  $vendorId  vendorId
	 *
	 * @return  integer
	 *
	 * @since   2.5.0
	 */
	public function isExist($email, $vendorId)
	{
		if (empty($email))
		{
			return 1;
		}

		$organizationTable = JGive::table('Organization');
		$organizationTable->load(array('email' => $email, 'vendor_id' => (int) $vendorId));

		if ($this->id == 0 || $this->id == "")
		{
			if (empty($organizationTable->id))
			{
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->select($db->quoteName('id'))
					->from($db->quoteName('#__users'))
					->where($db->quoteName('email') . ' = ' . $db->quote($email));
				$db->setQuery($query, 0, 1);
				$isUserExist = $db->loadResult();

				if ($isUserExist)
				{
					// When email not found in organization table, but found in users table.
					return 2;
				}

				// When email not found in organization table as well as users table.
				return 1;
			}
		}
		else
		{
			if ($organizationTable->id == $this->id || $organizationTable->id == 0)
			{
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Saving Organization contact person against organization
	 *
	 * @param   array  $data  Organization Data
	 *
	 * @return  integer
	 *
	 * @since   2.5.0
	 */
	public function addContact($data)
	{
		$contactObj     = JGive::contact();
		$orgContactData = $contactObj->loadContactByOrgId($data['id']);
		$contactdata    = (explode('.', $data['contact_id']));
		$obj            = array();
		$obj['id']      = (!empty($orgContactData['id'])) ? $orgContactData['id'] : '';
		$obj['organization_id'] = $data['id'];

		$individualObj = JGive::individual($contactdata['1']);
		$indVendorId   = $individualObj->getVendorID();

		if ($indVendorId != $data['vendor_id'])
		{
			$this->setError(Text::_('COM_JGIVE_ORGANIZATION_FORM_INVALID_VENDOR'));

			return false;
		}

		$obj['individual_id'] = $contactdata['1'];

		return ($contactObj->save($obj));
	}
}
