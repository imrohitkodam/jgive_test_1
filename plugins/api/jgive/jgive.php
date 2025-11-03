<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die( 'Restricted access');

use Joomla\CMS\Factory;

JLoader::import('components.com_jgive.includes.jgive', JPATH_SITE);

/**
 * Base Class for api plugin
 *
 * @package     JGive
 * @subpackage  component
 * @since       2.5.0
 */
class PlgAPIJgive extends ApiPlugin
{
	/**
	 * JGive api plugin to load com_api classes
	 *
	 * @param   string  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *
	 * @since   2.5.0
	 */
	public function __construct($subject, $config = array())
	{
		parent::__construct($subject, $config = array());
		ApiResource::addIncludePath(dirname(__FILE__) . '/jgive');

		// Load language files
		$lang = Factory::getLanguage();
		$lang->load('plg_api_jgive', JPATH_ADMINISTRATOR, '', true);
		$lang->load('com_jgive', JPATH_SITE, '', true);
	}
}
