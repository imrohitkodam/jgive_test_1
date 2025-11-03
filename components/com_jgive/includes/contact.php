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
 * JGive Contact class
 *
 * @since  2.5.0
 */
class JGiveContact extends CMSObject
{
	/**
	 * The auto incremental primary key of the order
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $id = 0;

	/**
	 *Organization Id - Foreign key of _jg_organizations table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $organization_id = 0;

	/**
	 * Individual id - Foreign key of _jg_individuals table
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $individual_id = 0;

	/**
	 * Holds the already loaded instances
	 *
	 * @var    array
	 * @since   2.5.0
	 */
	private static $contactObj = array();

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
	 * @return  JGiveContact  The order object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JGiveContact;
		}

		// Check if the order id is already cached.
		if (empty(self::$contactObj[$id]))
		{
			self::$contactObj[$id] = new JGiveContact($id);
		}

		return self::$contactObj[$id];
	}

	/**
	 * Method to load a Organization contact properties
	 *
	 * @param   int  $id  The order id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JGive::table("organizationcontacts");

		if ($table->load($id))
		{
			$this->id                    = (int) $table->get('id');
			$this->organization_id       = (int) $table->get('organization_id');
			$this->individual_id         = (int) $table->get('campaign_id');

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
	 * This method will return organization Id
	 *
	 * @return  integer  return organization id
	 *
	 * @since  2.5.0
	 */
	public function getOrganizationId()
	{
		return $this->organization_id;
	}

	/**
	 * This method will return individual id
	 *
	 * @return  integer  return individual id.
	 *
	 * @since  2.5.0
	 */
	public function getIndividualId()
	{
		return $this->individual_id;
	}

	/**
	 * This method will return organization contact
	 *
	 * @param   integer  $organizationId  organization Id
	 *
	 * @return  array  contact info
	 *
	 * @since  2.5.0
	 */
	public function loadContactById($organizationId)
	{
		$contactTable = JGive::table('organizationcontacts');
		$contactTable->load(array('organization_id' => $organizationId));
		$individualClass = JGive::individual($contactTable->individual_id);

		return $individualClass->getProperties();
	}

	/**
	 * Method to save
	 *
	 * @param   array  $data  data
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function save($data)
	{
		$table = JGive::table('organizationcontacts');

		try
		{
			// Bind data
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			$result = $table->save($data);

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
	 * This method will return organization contact.
	 *
	 * @param   integer  $organizationId  organization Id
	 *
	 * @return  array  organization contact info
	 *
	 * @since  2.5.0
	 */
	public function loadContactByOrgId($organizationId)
	{
		$contactTable = JGive::table('organizationcontacts');
		$contactTable->load(array('organization_id' => $organizationId));

		return $contactTable->getProperties();
	}
}
