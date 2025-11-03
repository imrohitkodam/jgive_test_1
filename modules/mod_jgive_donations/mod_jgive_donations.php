<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Component\ComponentHelper;

if (!ComponentHelper::isEnabled('com_jgive', true))
{
    return;
}

// Load helper File
require_once dirname(__FILE__) . '/helper.php';
$modJGiveDonationHelper = new modJGiveDonationHelper;
$module_for             = $params->get('module_for');
$no_of_record_show      = $params->get('no_of_record_show');
$userid                 = Factory::getUser()->id;

$result = $modJGiveDonationHelper->getData($module_for, $no_of_record_show);

if ($params->get('module_for') == 'my_donations')
{
	$totalMyDonations = $modJGiveDonationHelper->getData($module_for, $no_of_record_show = null);
	$totalMyDonationsCount = count($totalMyDonations);
}

require ModuleHelper::getLayoutPath('mod_jgive_donations');
