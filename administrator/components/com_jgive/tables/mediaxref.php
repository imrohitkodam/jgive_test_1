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
 * Media xref Table class.
 *
 * @since  2.1
 */
class JGiveTableMediaXref extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__tj_media_files_xref', 'id', $db);
	}
}
