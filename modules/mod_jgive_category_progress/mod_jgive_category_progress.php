<?php
/**
 * @package     JGive
 * @subpackage  mod_jgive_category_progres
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models/');

if (!ComponentHelper::isEnabled('com_jgive', true))
{
    return;
}

// Load helper File
$helperPath = JPATH_SITE . '/components/com_jgive/helper.php';

if (!class_exists('jgiveFrontendHelper'))
{
	JLoader::register('jgiveFrontendHelper', $helperPath);
	JLoader::load('jgiveFrontendHelper');
}

include_once  JPATH_SITE . '/components/com_jgive/includes/jgive.php';
JGive::init();

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

if (!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath);
	JLoader::load('campaignHelper');
}

// Create instance of Model class and Helper
$jgiveFrontendHelper = new jgiveFrontendHelper;
$categories          = $params->get('campaign_cat', array(), 'ARRAY');
$campaignsToShow     = $params->get('campaigns_to_show');

if (in_array(0, $categories))
{
	$categories = array(0);
}

foreach ($categories as $category)
{
	$campaigns = BaseDatabaseModel::getInstance('campaigns', 'JgiveModel');
	$campaigns->setState('filter.category_id', $category);

	if ($campaignsToShow != 'all')
	{
		if ($campaignsToShow == 'featured')
		{
			$campaigns->setState('filter.featured', 1);
		}
		else
		{
			$campaigns->setState('filter.success_status', $campaignsToShow);
		}
	}

	$campaigns->setState('displayPins', 1);
	$categoryCampaignsData[$category] = $campaigns->getItems();
}

// To  total amount and raised amount and calculate percent of progress bar
$recPer = $totalAmount = $raisedAmount = 0;

if (!empty($categoryCampaignsData))
{
	foreach ($categoryCampaignsData as $campaignsData)
	{
		foreach ($campaignsData as $campaignData)
		{
			// Getting sum of goal amount of all camapaigns of selected category
			if (isset($campaignData['goal_amount']))
			{
				$totalAmount = $totalAmount + $campaignData['goal_amount'];
			}

			// Getting sum of donation of all camapaigns of selected category
			if (isset($campaignData['amount_received']))
			{
				$raisedAmount = $raisedAmount + $campaignData['amount_received'];
			}
		}
	}
}

if ($raisedAmount > 0 && $totalAmount > 0)
{
	$recPer = intval((100 * $raisedAmount) / $totalAmount);
}

// Getting formatted price
$formattedAmountRaised = $jgiveFrontendHelper->getFormattedPrice($raisedAmount);
$formattedGoalAmount   = $jgiveFrontendHelper->getFormattedPrice($totalAmount);

// Call default.php
require ModuleHelper::getLayoutPath('mod_jgive_category_progress', 'default');
