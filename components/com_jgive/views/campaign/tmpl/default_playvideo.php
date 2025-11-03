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

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;

$this->video_params['divId'] = "jgive_video";

JPluginHelper::importPlugin('tjvideo', $this->video_params['plugin']);
$result = Factory::getApplication()->triggerEvent('onRenderPluginHTML', array($this->video_params));

echo $result[0];
