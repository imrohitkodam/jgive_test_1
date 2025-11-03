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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


/**
 * JFormFieldCron class.
 *
 * @package  JGive
 * @since    1.8
 */
class JFormFieldCron extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Cron';

	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since 1.5
	 */

	protected function getInput()
	{
		$params = ComponentHelper::getParams('com_jgive');
		$this->private_key_cronjob = $params->get('private_key_cronjob');
		$this->private_key_cronjob = $params->get('private_key_cronjob_thumbnail');

		$private_key_cronjob = $params->get('private_key_cronjob');
		$private_key_cronjob_thumbnail = $params->get('private_key_cronjob_thumbnail');

		if (isset($private_key_cronjob))
		{
			$params = ComponentHelper::getParams('com_jgive');
			$cron_masspayment = '';
			$cron_masspayment = Route::_(
			Uri::root() . 'index.php?option=com_jgive&controller=masspayment&task=performmasspay&pkey=' .
			$this->private_key_cronjob
			);
			$return	=	'<input type="text" name="cronjoburl" disabled="disabled" value="' . $cron_masspayment .
			'" size="100">';

			return $return;
		}

		if (isset($private_key_cronjob_thumbnail))
		{
			$params = ComponentHelper::getParams('com_jgive');
			$cron_thumbnail = '';
			$cron_thumbnail = Route::_(
			Uri::root() . 'index.php?option=com_jgive&task=videothumb&pkey=' . $this->private_key_cronjob
			);
			$return	=	'<input type="text" name="cronjoburlthumbnail" disabled="disabled" value="' . $cron_thumbnail . '" size="100">';

			return $return;
		}
	}
}
