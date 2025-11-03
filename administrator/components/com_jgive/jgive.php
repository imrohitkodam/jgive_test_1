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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;

// Include dependancies

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

require_once JPATH_SITE . '/media/com_jgive/js/load_js.php';

require_once JPATH_SITE . '/components/com_jgive/includes/jgive.php';

$versionClass  = JGive::jgversion();
$version = $versionClass->getMediaVersion();
$options = array("version" => $version);

if (File::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jgive');
}

$document = Factory::getDocument();

// Frontend css
HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive.css', $options);
HTMLHelper::_('stylesheet', 'media/com_jgive/css/artificiers.min.css', $options);

// Backend css
HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive_admin.css', $options);
HTMLHelper::_('script', 'media/com_jgive/javascript/jgive.js');

// Select Tags input
HTMLHelper::_('stylesheet', 'media/com_jgive/css/bootstrap-tagsinput.css');
HTMLHelper::_('script', 'media/com_jgive/javascript/bootstrap-tagsinput.min.js');


// Load jgive helper
$jgivehelperPath = JPATH_ADMINISTRATOR . '/components/com_jgive/helpers/jgive.php';

if (!class_exists('JgiveHelper'))
{
	// Require_once $path;
	JLoader::register('JgiveHelper', $jgivehelperPath);
	JLoader::load('JgiveHelper');
}

// Load campaigns helper
$campaignshelperPath = JPATH_ADMINISTRATOR . '/components/com_jgive/helpers/campaigns.php';

if (!class_exists('CampaignsHelper'))
{
	// Require_once $path;
	JLoader::register('CampaignsHelper', $campaignshelperPath);
	JLoader::load('CampaignsHelper');
}

// Load jgiveFontendHelper
$helperPath = JPATH_SITE . '/components/com_jgive/helper.php';

if (!class_exists('jgiveFrontendHelper'))
{
	// Require_once $path;
	JLoader::register('jgiveFrontendHelper', $helperPath);
	JLoader::load('jgiveFrontendHelper');
}

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

if (!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath);
	JLoader::load('campaignHelper');
}

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/donations.php';

if (!class_exists('donationsHelper'))
{
	JLoader::register('donationsHelper', $helperPath);
	JLoader::load('donationsHelper');
}

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/reports.php';

if (!class_exists('reportsHelper'))
{
	JLoader::register('reportsHelper', $helperPath);
	JLoader::load('reportsHelper');
}

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/media.php';

if (!class_exists('jgivemediaHelper'))
{
	JLoader::register('jgivemediaHelper', $helperPath);
	JLoader::load('jgivemediaHelper');
}

include_once  JPATH_SITE . '/components/com_jgive/includes/jgive.php';
JGive::init();

// Load Global language constants to in .js file
jgiveFrontendHelper::getLanguageConstant();

// Require the base controller
require_once JPATH_COMPONENT . '/controller.php';

// Execute the task.
$controller = BaseController::getInstance('jgive');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
