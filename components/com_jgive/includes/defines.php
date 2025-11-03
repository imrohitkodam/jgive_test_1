<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

if (defined('COM_JGIVE_SITE_DEFINE_FILE'))
{
	return;
}

define('COM_JGIVE_CONSTANT_ORDER_STATUS_PENDING', 'P');
define('COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED', 'C');
define('COM_JGIVE_CONSTANT_ORDER_STATUS_REFUND', 'RF');
define('COM_JGIVE_CONSTANT_ORDER_STATUS_CANCELED', 'E');
define('COM_JGIVE_CONSTANT_ORDER_STATUS_DECLINE', 'D');
define('COM_JGIVE_CONSTANT_INTEGRATION_JOOMLA', 'joomla');
define('COM_JGIVE_CONSTANT_INTEGRATION_COMMUNITY_BUILDER', 'cb');
define('COM_JGIVE_CONSTANT_INTEGRATION_JOMSOCIAL', 'jomsocial');
define('COM_JGIVE_CONSTANT_INTEGRATION_JOMWALL', 'jomwall');
define('COM_JGIVE_CONSTANT_INTEGRATION_EASYSOCIAL', 'easySocial');
define('COM_JGIVE_CONSTANT_INTEGRATION_EASYPROFILE', 'easyprofile');
define('COM_JGIVE_CONSTANT_CAMPAIGN_STATUS_ONGOING', '0');
define('COM_JGIVE_CONSTANT_CAMPAIGN_STATUS_SUCCESSFULL', '1');
define('COM_JGIVE_CONSTANT_CAMPAIGN_STATUS_FAILED', '-1');

// Need this constant for performance purpose. Always define this at the end of file
define('COM_JGIVE_SITE_DEFINE_FILE', true);
