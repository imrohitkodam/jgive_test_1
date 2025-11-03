<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;

/**
 * Donor Table class.
 *
 * @since  2.2.0
 */
class JGiveTableDonor extends Table
{
	/**
	 * Donor type (ind/org)
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $donor_type = '';

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   2.2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jg_donors', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean
	 *
	 * @see     JTable::check
	 *
	 * @since   2.2.0
	 */
	public function check()
	{
		$params               = ComponentHelper::getParams('com_jgive');
		$showSelectedFields   = $params->get('show_selected_fields_on_donation', '', 'STRING');
		$donationFieldsToShow = $params->get('donationfield', array(), 'ARRAY');
		$quick_donation       = $params->get('quick_donation', 0, 'int');
		$requiredFieldsArray  = array();

		if ($this->donor_type === 'org' && $this->donor_type != '')
		{
			$requiredFieldsArray  = array('org_name');
		}

		// Combine the array to create key and value pairs
		$requiredFieldsArray  = array_combine($requiredFieldsArray, $requiredFieldsArray);

		// If there are no mandatory fields skip validations
		if (empty($requiredFieldsArray))
		{
			return true;
		}

		// Remove the selected hidden fields, to skip validations
		if (!empty($donationFieldsToShow))
		{
			foreach ($donationFieldsToShow as $df)
			{
				unset($requiredFieldsArray[$df]);
			}
		}

		// Check here is hide donation field configuration is enabled
		if (!$quick_donation)
		{
			foreach ($requiredFieldsArray as $rf)
			{
				if ($showSelectedFields && !empty($donationFieldsToShow))
				{
					if (!in_array($this->{$rf}, $donationFieldsToShow) && trim($this->{$rf}) == '')
					{
						return false;
					}
				}
				else
				{
					if (trim($this->{$rf}) == '')
					{
						return false;
					}
				}
			}
		}

		return true;
	}
}
