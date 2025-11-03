<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die();
use Joomla\CMS\Table\Table;

/**
 * JTable class for Campaign.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       2.3
 */
class JgiveTableOrganizationContacts extends Table
{
	public $alias;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$_db  Database connector object
	 *
	 * @since 1.7
	 */
	public function __construct (&$_db)
	{
		parent::__construct('#__jg_organization_contacts', 'id', $_db);
	}
}
