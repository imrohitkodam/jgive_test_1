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
use Joomla\CMS\Table\Table;

/**
 * Orders Table class
 *
 * @since  0.0.1
 */
class JGiveTableOrders extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$_db  A database connector object
	 */
	public function __construct(&$_db)
	{
		parent::__construct('#__jg_orders', 'id', $_db);
	}
}
