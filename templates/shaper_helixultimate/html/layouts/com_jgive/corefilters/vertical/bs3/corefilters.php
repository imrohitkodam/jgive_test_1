<?php
/**
 * @package    Jgive
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * Template Override for Helix Ultimate - Fixed filter reset issue (Vertical Layout)
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Cms\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

require_once JPATH_SITE . '/components/com_jgive/models/campaigns.php';
require_once JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

$app            = Factory::getApplication();
$jinput         = $app->input;
$campaignHelper = new campaignHelper;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jgive/models');
$campaignsModel               = BaseDatabaseModel::getInstance('Campaigns', 'JgiveModel');
$jgiveParams                  = $campaignsModel->getState();
$campaigns_to_show            = $campaignHelper->campaignsToShowOptions();
$campaign_type_filter_options = $campaignHelper->getCampaignTypeFilterOptions();
$filter_org_ind_type          = $campaignHelper->organization_individual_type();

// Get itemids
$menu       = $app->getMenu();
$activeMenu = $menu->getActive();

if (!empty($activeMenu))
{
	$menuItemId = $activeMenu->id;
}

$singleCampaignItemid = !empty($menuItemId)?$menuItemId:'';

// Take option value
$lists['filter_campaigns_to_show']  = $jgiveParams->get('filter_campaigns_to_show');
$lists['filter_campaign_countries'] = $jgiveParams->get('filter_campaign_countries');
$lists['filter_campaign_states']    = $jgiveParams->get('filter_campaign_states');
$lists['filter_campaign_city']      = $jgiveParams->get('filter_campaign_city');
$lists['filter_org_ind_type']       = $jgiveParams->get('filter_org_ind_type');
$lists['filter_org_ind_type_my']    = $jgiveParams->get('filter_org_ind_type_my');
$lists['filter_campaign_type']      = $jgiveParams->get('filter_campaign_type');

$campaignsModel->ordering_options           = $campaignsModel->getCampignsOrderingOptions();
$campaignsModel->ordering_direction_options = $campaignsModel->getCampignsOrderingDirection();

// For countries
$countryOptions    = array();
$countryOptions[]  = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_COUNTRY_TOOLTIP'));
$campaignCountries = $campaignsModel->getFilterCountries();

if (!empty ($campaignCountries))
{
	foreach ($campaignCountries  as $campaignCountry)
	{
		$value            = $campaignCountry->country_id;
		$option           = $campaignCountry->country;
		$countryOptions[] = HTMLHelper::_('select.option', $value, $option);
	}
}

// For states
$stateArray     = array();
$stateArray[]   = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELECT_STATE'));
$campaignStates = $campaignsModel->getCampaignsFilterStates();

if (isset($campaignStates))
{
	foreach ($campaignStates  as $campaignState)
	{
		$value        = $campaignState->id;
		$option       = $campaignState->region;
		$stateArray[] = HTMLHelper::_('select.option', $value, $option);
	}
}

// For city
$cityArray      = array();
$cityArray[]    = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELECT_CITY'));
$campaignCities = $campaignsModel->getCampaignsFilterCities();

if (isset($campaignCities))
{
	foreach ($campaignCities  as $campaignCity)
	{
		if ($campaignCity->id )
		{
			$value       = $campaignCity->id;
			$option      = $campaignCity->city;
			$cityArray[] = HTMLHelper::_('select.option', $value, $option);
		}
	}
}

$countryoption    = $countryOptions;
$campaign_states  = $stateArray;
$campaign_city    = $cityArray;

$showCampaignsToShow = $campaignHelper->filedToShowOrHide('campaigns_to_show');
$showCampaignType    = $campaignHelper->filedToShowOrHide('campaign_type');
$showCountryFilter   = $campaignHelper->filedToShowOrHide('country_filter');
$showOrgIndType      = $campaignHelper->filedToShowOrHide('org_ind_type');
?>

<div class="col-xs-12 col-sm-3">
	<form action="" name="jgVerticalCoreFilters" method="post" id="jgVerticalCoreFilters">
		<?php if ($showCampaignsToShow == 1) { ?>
			<div class="campaignsToShowFilterwrapper">
				<h5><strong><?php echo Text::_('COM_JGIVE_CAMPAIGNS_TO_SHOW');?></strong></h5>
				<div>
					<label>
						<input type="radio" class="tjfieldCheck" name="filter_campaigns_to_show"
						id="filter_campaigns_to_show" value="" <?php
						if (empty($lists['filter_campaigns_to_show']))
						{
							echo 'checked';
						}
						else
						{
							echo '';
						}?>
						onclick="jgiveCommon.filters.submitFilters('jgVerticalCoreFilters');"/>
						<?php echo Text::_('COM_JGIVE_RESET_FILTER_TO_ALL'); ?>
					</label>
				</div>
				<?php
				for ($i = 0, $n = count($campaigns_to_show); $i < $n; $i++)
				{
					$check = "";
					$selected = $campaigns_to_show[$i]->value;

					if ($lists['filter_campaigns_to_show'] == $selected)
					{
						$class = "active";
						$check = "checked";
					}
					else
					{
						$class = "";
					}
				?>
					<div class="<?php echo $class; ?>">
						<label>
							<input type="radio" class="tjfieldCheck"
							name="filter_campaigns_to_show"
							id="filter_campaigns_to_show" <?php echo $check;?>
							value="<?php echo $campaigns_to_show[$i]->value; ?>"
							onclick="jgiveCommon.filters.submitFilters('jgVerticalCoreFilters');"/>
							<?php echo $campaigns_to_show[$i]->text; ?>
						</label>
					</div>
				<?php
				}
				?>
			</div>
		<?php } ?>

		<?php if ($showOrgIndType == 1) { ?>
			<div class="orgIndTypeFilterwrapper">
				<h5><strong><?php echo Text::_('COM_JGIVE_ORG_IND_TYPE');?></strong></h5>
				<?php
				echo HTMLHelper::_(
				'select.genericlist', $filter_org_ind_type, "filter_org_ind_type",
				'class="form-control" size="1" onchange="jgiveCommon.filters.submitFilters(\'jgVerticalCoreFilters\');" name="filter_org_ind_type"',
				"value", "text", $lists['filter_org_ind_type']
				);
				?>
			</div>
		<?php } ?>

		<?php if ($showCountryFilter == 1) { ?>
			<div class="countryFilterwrapper">
				<h5><strong><?php echo Text::_('COM_JGIVE_COUNTRY');?></strong></h5>
				<?php
				echo HTMLHelper::_(
				'select.genericlist', $countryoption, "filter_campaign_countries",
				'size="1" onchange="jgiveCommon.filters.submitFilters(\'jgVerticalCoreFilters\');" class="form-control" name="filter_campaign_countries"',
				"value", "text", $lists['filter_campaign_countries']
				); ?>
				</br>
				<?php echo HTMLHelper::_(
				'select.genericlist', $campaign_states, "filter_campaign_states",
				'size="1" onchange="jgiveCommon.filters.submitFilters(\'jgVerticalCoreFilters\');" class="form-control" name="filter_campaign_states"',
				"value", "text", $lists['filter_campaign_states']
				);?>
				</br>
				<?php echo HTMLHelper::_(
				'select.genericlist', $campaign_city, "filter_campaign_city",
				'size="1" onchange="jgiveCommon.filters.submitFilters(\'jgVerticalCoreFilters\');" class="form-control" name="filter_campaign_city"',
				"value", "text", $lists['filter_campaign_city']
				);
			?>
			</div>
		<?php } ?>

		<?php if ($showCampaignType == 1) { ?>
			<div class="campaignTypeFilterwrapper">
				<h5><strong><?php echo Text::_('COM_JGIVE_CAMPAIGN_TYPE');?></strong></h5>
				<?php
				$selectedType = $lists['filter_campaign_type'];
				?>
				<div>
					<label>
						<input type="radio" class="tjfieldCheck" name="filter_campaign_type"
						id="filter_campaign_type" value="" <?php echo empty($selectedType) ? 'checked': '';?>
						onclick="jgiveCommon.filters.submitFilters('jgVerticalCoreFilters');"/>
						<?php echo Text::_('COM_JGIVE_RESET_FILTER_TO_ALL'); ?>
					</label>
				</div>
				<?php
				for ($i = 0, $n = count($campaign_type_filter_options); $i < $n; $i++)
				{
					$chec = "";
					$selected = $campaign_type_filter_options[$i]->value;

					if ($lists['filter_campaign_type'] == $selected)
					{
						$class = "active";
						$chec = "checked";
					}
					else
					{
						$class = "";
					}
				?>
					<div class="<?php echo $class; ?>">
						<label>
							<input type="radio" class="tjfieldCheck" name="filter_campaign_type"
							id="filter_campaign_type" value="<?php echo $campaign_type_filter_options[$i]->value; ?>" 
							<?php echo $chec;?>
							onclick="jgiveCommon.filters.submitFilters('jgVerticalCoreFilters');"/>
							<?php echo $campaign_type_filter_options[$i]->text; ?>
						</label>
					</div>
				<?php
				}
				?>
			</div>
		<?php } ?>
	</form>
</div>

<script type="text/javascript">
	// Fix for Helix Ultimate template - Ensure tjListFilters is properly initialized
	if (typeof tjListFilters === 'undefined') {
		var tjListFilters = [];
	}
	
	// Initialize filter array for Helix Ultimate compatibility
	jQuery(document).ready(function() {
		// Ensure tjListFilters is available globally
		if (typeof window.tjListFilters === 'undefined') {
			window.tjListFilters = [
				'filter_campaigns_to_show',
				'filter_campaign_countries', 
				'filter_campaign_states',
				'filter_campaign_city',
				'filter_org_ind_type',
				'filter_campaign_type',
				'filter_search',
				'filter_order',
				'filter_order_Dir',
				'limit',
				'limitstart'
			];
		}
		
		// Make sure the global tjListFilters variable is set
		if (typeof tjListFilters === 'undefined') {
			tjListFilters = window.tjListFilters;
		}
	});
</script>
