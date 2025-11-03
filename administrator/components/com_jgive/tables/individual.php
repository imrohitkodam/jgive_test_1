<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_movie
 *
 * @copyright   Copyright (C) 2009 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;

/**
 * IndividualsTable class
 *
 * @since  2.3.0
 */
class JGiveTableIndividual extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct (&$db)
	{
		parent::__construct('#__jg_individuals', 'id', $db);
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
		if (empty($array['country']))
		{
			$array['country'] = 0;
		}

		if (empty($array['region']))
		{
			$array['region'] = 0;
		}

		if (empty($array['city']))
		{
			$array['city'] = 0;
		}

		return parent::bind($array, $ignore);
	}
}
