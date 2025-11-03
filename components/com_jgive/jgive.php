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
defined('_JEXEC') or die(';)');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

require_once JPATH_COMPONENT . '/controller.php';
require_once JPATH_SITE . '/media/com_jgive/js/load_js.php';

$params = ComponentHelper::getParams('com_jgive');

// Icon constants.
define('JGIVE_ICON_CHECKMARK', " icon-checkmark");
define('JGIVE_ICON_MINUS', " icon-minus-2");
define('JGIVE_ICON_PLUS', " icon-plus-2");
define('JGIVE_ICON_EDIT', " icon-pencil-2");
define('JGIVE_ICON_REMOVE', " icon-cancel-2");
define('JGIVE_TOOLBAR_ICON_SETTINGS', "icon-cog");

$bsVersion = $params->get('bootstrap_version', '', 'STRING');

if (empty($bsVersion))
{
	$bsVersion = (JVERSION >= '4.0.0') ? 'bs5' : 'bs3';
}

define('JGIVE_LOAD_BOOTSTRAP_VERSION', $bsVersion);

// Load techjoomla bootstrapper

$helperPath = dirname(__FILE__) . '/helper.php';

if (!class_exists('jgiveFrontendHelper'))
{
	// Require_once $path;
	JLoader::register('jgiveFrontendHelper', $helperPath);
	JLoader::load('jgiveFrontendHelper');
}

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/donations.php';

if (!class_exists('donationsHelper'))
{
	// Require_once $path;
	JLoader::register('donationsHelper', $helperPath);
	JLoader::load('donationsHelper');
}

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/integrations.php';

// Load integrations helper file
if (!class_exists('JgiveIntegrationsHelper'))
{
	// Require_once $path;
	JLoader::register('JgiveIntegrationsHelper', $helperPath);
	JLoader::load('JgiveIntegrationsHelper');
}

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/reports.php';

if (!class_exists('reportsHelper'))
{
	JLoader::register('reportsHelper', $helperPath);
	JLoader::load('reportsHelper');
}

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

if (!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath);
	JLoader::load('campaignHelper');
}

// Load media helper
$helperPath = JPATH_SITE . '/components/com_jgive/helpers/media.php';

if (!class_exists('jgivemediaHelper'))
{
	JLoader::register('jgivemediaHelper', $helperPath);
	JLoader::load('jgivemediaHelper');
}

// Load media helper
$helperPath = JPATH_SITE . '/components/com_jgive/helpers/video/vimeo.php';

if (!class_exists('helperVideoVimeo'))
{
	JLoader::register('helperVideoVimeo', $helperPath);
	JLoader::load('helperVideoVimeo');
}

// Select Tags input
HTMLHelper::_('stylesheet', 'media/com_jgive/css/bootstrap-tagsinput.css');
HTMLHelper::_('script', 'media/com_jgive/javascript/bootstrap-tagsinput.min.js');

// Load Global language constants to in .js file
jgiveFrontendHelper::getLanguageConstant();
JLoader::register('JgiveFrontendHelper', JPATH_COMPONENT . '/helpers/jgive.php');

// Load Helix Ultimate compatibility fix if using that template
$app = Factory::getApplication();
$template = $app->getTemplate();
if ($template === 'shaper_helixultimate') {
    HTMLHelper::_('script', 'media/com_jgive/javascript/helix-ultimate-compatibility.js');
}

include_once  JPATH_SITE . '/components/com_jgive/includes/jgive.php';
JGive::init();

// Execute the task.
$controller = BaseController::getInstance('Jgive');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
