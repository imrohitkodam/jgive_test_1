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
defined('_JEXEC') or die();

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Component\ComponentHelper;

$componentParams = ComponentHelper::getParams('com_jgive');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jgive/models/');

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

$mode = $params->get('mode', 'category', 'STRING');
$data = array();

$loadbootstrap = ($componentParams->get('load_bootstrap') == 1)? true : false;

// To check if single campaign to show is selected
if ($mode === 'campaign')
{
	$selectSingleCampaignToShow = $params->get('select_single_campaign_to_show', 0, 'INT');

	$campaignsModel = BaseDatabaseModel::getInstance('campaigns', 'JgiveModel');
	$campaignsModel->setState('select_single_campaign_to_show', $selectSingleCampaignToShow);
	$campaignsModel->setState('list.limit', 1);
	$campaignsModel->setState('displayPins', 1);

	$data = $campaignsModel->getItems();
}
// To check if category wise campaign selection is selected
elseif ($mode === 'category')
{
	$categories      = $params->get('campaign_cat', array(), 'ARRAY');
	$campaignsLimit  = $params->get('no_of_camp_show', 0, 'INT');
	$campaignsSortBy = $params->get('campaigns_sort_by', 'start_date');
	$orderDirection  = $params->get('order_dir', 'ASC');
	$campaignsToShow = $params->get('campaigns_to_show', '', 'STRING');

	// A check to check if All category is selected in category selection
	if (in_array(0, $categories))
	{
		$categories = array(0);
	}

	foreach ($categories as $category)
	{
		$campaignsModel = BaseDatabaseModel::getInstance('campaigns', 'JgiveModel');
		$campaignsModel->setState('mod_limit', $campaignsLimit);

		if ($category != 0)
		{
			$campaignsModel->setState('filter.category_id', $category);
		}

		if ($campaignsToShow == 'featured')
		{
			$campaignsModel->setState('filter.featured', 1);
		}
		else
		{
			$campaignsModel->setState('filter.success_status', $campaignsToShow);
		}

		$campaignsModel->setState('list.ordering', $campaignsSortBy);
		$campaignsModel->setState('list.direction', $orderDirection);
		$campaignsModel->setState('displayPins', 1);

		$data = array_merge($data, $campaignsModel->getItems());
	}
}

require ModuleHelper::getLayoutPath('mod_jgive_campaigns_pin', 'default');
