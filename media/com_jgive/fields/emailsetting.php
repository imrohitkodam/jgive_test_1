<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('JPATH_BASE') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;


/**
 * Custom Cron field for component params.
 *
 * @package  JGive
 *
 * @since    2.2
 */
class JFormFieldEmailsetting extends JFormField
{
	public $type = 'Emailsetting';

	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return   string  The field input markup.
	 *
	 * @since  1.6
	 */
	protected function getInput()
	{
		$notificationLinkHtml = $notificationLink = '';
		$notificationLink = Uri::base() . substr(Route::_('index.php?option=com_tjnotifications&extension=com_jgive'), strlen(Uri::base(true)) + 1);
		$notificationLinkHtml = '<strong><a name="notification_page_link" href="' . $notificationLink . '">'
		. Text::_("COM_JGIVE_EMAIL_NOTIFICATION") . '</a></strong>';

		return '<div class="alert alert-info">' . Text::sprintf("COM_JGIVE_CONFIG_MIGRATION_NOTE", $notificationLinkHtml) . '</div>';
	}
}
