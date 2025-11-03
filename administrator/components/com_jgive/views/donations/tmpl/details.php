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

// Load frontend order details view
ob_start();
include JPATH_SITE . '/components/com_jgive/views/donations/tmpl/details.php';
$html = ob_get_contents();
ob_end_clean();
echo $html;
