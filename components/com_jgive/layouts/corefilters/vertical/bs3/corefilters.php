<?php
/**
 * @package    Jgive
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
$countryarray       = array();
$countryarray[]     = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_COUNTRY_TOOLTIP'));
$campaign_countries = $campaignsModel->getFilterCountries();

if (!empty ($campaign_countries))
{
	foreach ($campaign_countries  as $tmp)
	{
		$value          = $tmp->country_id;
		$option         = $tmp->country;
		$countryarray[] = HTMLHelper::_('select.option', $value, $option);
	}
}

$countryoption = $countryarray;

// For state
$statearray   = array();
$statearray[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELECT_STATE'));

// Get states
$campaign_states = $campaignsModel->getCampaignsFilterStates();

if (isset($campaign_states))
{
	foreach ($campaign_states  as $tmp)
	{
		$value        = $tmp->id;
		$option       = $tmp->region;
		$statearray[] = HTMLHelper::_('select.option', $value, $option);
	}
}

$campaign_states = $statearray;

// For city
$cityarray   = array();
$cityarray[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELECT_CITY'));

// Get city
$campaign_city = $campaignsModel->getCampaignsFilterCities();

if (isset($campaign_city))
{
	foreach ($campaign_city  as $tmp)
	{
		if ($tmp->id )
		{
			$value       = $tmp->id;
			$option      = $tmp->city;
			$cityarray[] = HTMLHelper::_('select.option', $value, $option);
		}
		elseif (empty($tmp->id) && $tmp->othercity)
		{
			$value       = $tmp->othercity;
			$option      = $tmp->othercity;
			$cityarray[] = HTMLHelper::_('select.option', $value, $option);
		}
	}
}

$campaign_city = $cityarray;
?>
<div class="jgive_filters">
	<form action="" method="GET" name="jgVerticalCoreFilters" id="jgVerticalCoreFilters">
		<input type="hidden" name="option" value="com_jgive" />
		<input type="hidden" name="view" value="campaigns" />
		<input type="hidden" name="layout" value="all" />
		<div class="panel-group" id="accordion">
			<!-- Quick Search -->
			<div>
				<div>
					<b><?php echo Text::_('COM_JGIVE_CAMPAIGNS_TO_SHOW'); ?></b>
				</div>
				<?php
					$selected    = $jinput->get('filter_campaigns_to_show', '', 'STRING');
					$camps_quick = 'index.php?option=com_jgive&view=campaigns&layout=all&filter_campaigns_to_show=&Itemid=' . $singleCampaignItemid;
					$camps_quick = Uri::root() . substr(Route::_($camps_quick), strlen(Uri::base(true)) + 1);
				?>
				<div class="<?php echo empty($selected) ? 'active': ''; ?>">
					<label>
						<input type="radio" class="" name="filter_campaigns_to_show"
						id="filter_campaigns_to_show" value="<?php echo Text::_('COM_JGIVE_RESET_FILTER_TO_ALL'); ?>"
						<?php 
						if (empty($selected) || $selected == 'All')
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
					for ($i = 1; $i < count($campaigns_to_show); $i ++)
					{
						$check       = "";
						$selected    = htmlspecialchars($campaigns_to_show[$i]->value, ENT_COMPAT, 'UTF-8');
						$camps_quick = 'index.php?option=com_jgive&view=campaigns&layout=all&filter_campaigns_to_show=' . $selected . '&Itemid=' . $singleCampaignItemid;
						$camps_quick = Uri::root() . substr(Route::_($camps_quick), strlen(Uri::base(true)) + 1);
						$class       = "";

						if ($lists['filter_campaigns_to_show'] == $selected)
						{
							$class = "active";
							$check = "checked";
						}
						?>
						<div class="<?php echo $class; ?>">
							<label>
								<input type="radio" class=""
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
			<!-- Quick Search E-->

			<!--  Filters s-->
			<?php
			$show_place_filter        = $jgiveParams->params->get('show_place_filter');
			$show_org_ind_type_filter = $jgiveParams->params->get('show_org_ind_type_filter');
			$filter_user              = $jinput->get('filter_user', '', 'INT');

			if ($show_place_filter || $show_org_ind_type_filter || $filter_user)
			{
			?>
				<div><b><?php echo Text::_('COM_JGIVE_FILTER_CAMPAIGNS');?></b></div>
				<div class="form-group">
					<?php
					if ($show_org_ind_type_filter)
					{
						echo HTMLHelper::_(
						'select.genericlist', $filter_org_ind_type, "filter_org_ind_type",
						'class="form-control" size="1" onchange="jgiveCommon.filters.submitFilters(\'jgVerticalCoreFilters\');" name="filter_org_ind_type"',
						"value", "text", $lists['filter_org_ind_type']
						);
					} ?>
					</br>
					<?php
					if ($show_place_filter)
					{
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
					}?>
				</div>
			<?php
			}?>
			<!-- Filters More options e--->

			<!-- Campaign Type s-->
			<?php
			$campaignHelper = new campaignHelper;
			$campaign_type  = $campaignHelper->filedToShowOrHide('campaign_type');

			if (count((array)$jgiveParams->params->get('camp_type')) > 1)
			{
				if ($jgiveParams->params->get('show_type_filter') AND $campaign_type)
				{
					?>
					<div><b><?php echo Text::_('COM_JGIVE_CAMP_TYPE'); ?></b></div>
					<div>
						<?php
						$camp_type    = 'index.php?option=com_jgive&view=campaigns&layout=all&filter_campaign_type=&Itemid=' . $singleCampaignItemid;
						$camp_type    = Uri::root() . substr(Route::_($camp_type), strlen(Uri::base(true)) + 1);
						$selectedType = $jinput->get('filter_campaign_type', '', 'string');
						?>

						<div class="<?php echo empty($selectedType) ? 'active': ''; ?>">
							<label>
								<input type="radio" class="tjfieldCheck" name="filter_campaign_type"
								id="filter_campaign_type" value="" <?php echo empty($selectedType) ? 'checked': '';?>
								onclick="jgiveCommon.filters.submitFilters('jgVerticalCoreFilters');"/>
								<?php echo Text::_('COM_JGIVE_RESET_FILTER_TO_ALL'); ?>
							</label>
						</div>

						<?php
						for ($i = 1; $i < count($campaign_type_filter_options); $i ++)
						{
							$chec = "";
							$selected = $campaign_type_filter_options[$i]->value;
							$camp_type = 'index.php?option=com_jgive&view=campaigns&layout=all&filter_campaign_type=' . $selected . '&Itemid=' . $singleCampaignItemid;
							$camp_type = Uri::root() . substr(Route::_($camp_type), strlen(Uri::base(true)) + 1);
							$class = "";

							if ($selectedType == $campaign_type_filter_options[$i]->value)
							{
								$chec = 'checked';
							}

							if ($lists['filter_campaign_type'] == $selected)
							{
								$class = "active";
								$chec = "checked";
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
				<?php
				}
			}?>
			<!-- Campaign Type e-->
		</div>
	</form>
</div>
