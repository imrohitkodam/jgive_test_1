<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

// Load helper File
require_once dirname(__FILE__) . '/helper.php';

$componentParams = ComponentHelper::getParams('com_jgive');
$loadbootstrap = ($componentParams->get('load_bootstrap') == 1)? true : false;

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

if (!ComponentHelper::isEnabled('com_jgive', true))
{
    return;
}

if (!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath);
	JLoader::load('campaignHelper');
}

// Get Params
$orderby        = $params->get('campaigns_sort_by');
$orderby_dir    = $params->get('order_dir');
$count          = $params->get('no_of_camp_show');
$image          = $params->get('image');
$featured_camp  = $params->get('featured_camp');
$show_related_campaigns = $params->get('show_related_campaigns');

// For group campaign module
$module_for = $params->get('module_for');
$groupid    = '';
$group_page = 0;

if ($module_for == 'js_group_camp')
{
	// Get js group id
	$input = Factory::getApplication()->input;

	if ($input->get('task') == 'viewgroup')
	{
		$group_page = 1;
		$groupid    = $input->get('groupid', '', 'INT');
	}
	else
	{
		return;
	}
}

// Show related campaigns
if ($show_related_campaigns)
{
    // Get the application instance
    $app = Factory::getApplication();
    $input = $app->input;

    // Get the current campaign ID
    $campaignId = $input->getInt('id');
    if (!$campaignId)
	{
        // If 'id' isn't found, fallback to 'cid' for the donations view
        $campaignId = $input->getInt('cid');
	}

    // Proceed only if campaign ID is found
    if (!empty($campaignId))
	{
		// Load the Campaign model to fetch campaign data
        JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/models');
        $JgiveModelCampaign = new JgiveModelCampaign;

        // Get the current campaign data using the campaign ID
        $campaignData = $JgiveModelCampaign->getItem($campaignId);

        // Extract the category ID of the current campaign
        $categoryId = $campaignData['campaign']->category_id;
	}
    else
	{
        // If no campaign ID is found, do not display the module
        return;
    }
}

// Model helper object
$modJGiveHelper = new modJGiveHelper;
$campaignsData  = $modJGiveHelper->getData($groupid, $group_page, $orderby, $orderby_dir, $count, $image, $featured_camp, $categoryId, $campaignId);

// Do not display the module if no campaigns data is available
if(empty($campaignsData))
{
	return;
}

// Call default.php
require ModuleHelper::getLayoutPath('mod_jgive_campaigns');
