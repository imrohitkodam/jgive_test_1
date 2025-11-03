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

/**
 * Featured Table class.
 *
 * @since  2.1
 */
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Featured Table class.
 *
 * @since  1.6
 */
class JgiveTableDonations extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   1.6
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jg_donations', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 *
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable:bind
	 * @since   1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (!isset($array['comment']) || empty($array['comment']))
		{
			$array['comment'] = 0;
		}
		
		if (!isset($array['recurring_count']) || empty($array['recurring_count']))
		{
			$array['recurring_count'] = 0;
		}

		return parent::bind($array, $ignore);
	}
}
