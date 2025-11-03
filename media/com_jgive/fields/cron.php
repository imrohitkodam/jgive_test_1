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
 * Custom Cron field for component params.
 *
 * @package  JGive
 *
 * @since    2.2
 */
class JFormFieldCron extends JFormField
{
	public $type = 'Cron';

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
		$params = ComponentHelper::getParams('com_jgive');
		$inputClass = (JVERSION > '4.0.0') ? 'form-control' : '';
		$return = '';
	
		switch ($this->fieldname) {
			case 'cronjoburl_newcamp':
				$privateKey = $params->get('private_key_newcamp', 'jg25');
	
				$url = Uri::root() . 'index.php?option=com_jgive&task=campaignform.sendCampaignEmails&pkey=' . $privateKey;
	
				$return = '<input type="text" class="' . $inputClass . '" name="' . $this->name . '" disabled="disabled" value="' . htmlspecialchars($url, ENT_QUOTES) . '" size="100">';
				break;
	
			case 'cronjoburl':
				$privateKey = $params->get('private_key_cronjob', 'az197');
	
				$url = Uri::root() . 'index.php?option=com_jgive&controller=masspayment&task=performmasspay&pkey=' . $privateKey;
	
				$return = '<input type="text" class="' . $inputClass . '" name="' . $this->name . '" disabled="disabled" value="' . htmlspecialchars($url, ENT_QUOTES) . '" size="100">';
				break;
				
			case 'cronjoburl_vendor_report_monthly':
				$privateKey = $params->get('private_key_vendor_report_monthly', 'jg25');
	
				$url = Uri::root() . 'index.php?option=com_jgive&task=vendorreport.sendAllVendorReports&period=monthly&pkey=' . $privateKey;
	
				$return = '<input type="text" class="' . $inputClass . '" name="' . $this->name . '" disabled="disabled" value="' . htmlspecialchars($url, ENT_QUOTES) . '" size="100">';
				break;

			case 'cronjoburl_vendor_report_quarterly':
				$privateKey = $params->get('private_key_vendor_report_quarterly', 'jg25');
	
				$url = Uri::root() . 'index.php?option=com_jgive&task=vendorreport.sendAllVendorReports&period=quarterly&pkey=' . $privateKey;
	
				$return = '<input type="text" class="' . $inputClass . '" name="' . $this->name . '" disabled="disabled" value="' . htmlspecialchars($url, ENT_QUOTES) . '" size="100">';
				break;


			case 'cronjoburl_vendor_report_yearly':
				$privateKey = $params->get('private_key_vendor_report_yearly', 'jg25');
	
				$url = Uri::root() . 'index.php?option=com_jgive&task=vendorreport.sendAllVendorReports&period=yearly&pkey=' . $privateKey;
	
				$return = '<input type="text" class="' . $inputClass . '" name="' . $this->name . '" disabled="disabled" value="' . htmlspecialchars($url, ENT_QUOTES) . '" size="100">';
				break;
		}
	
		return $return;
	}
}
