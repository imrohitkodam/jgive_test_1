<?php
/**
 * @package    Jgive
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\FileLayout;

$displayData = array();

if (!empty($this->data))
{
	$displayData['campData'] = $this->data[0];
}
else
{
	$campData = array();
	$campData['params'] = $this->params;
	$campData['otherData'] = $this->otherData;
	$displayData['campData'] = $campData;
}

$displayData['pagination'] = $this->pagination;
$displayData['campTypeOption'] = $this->campaign_type_filter_options;
$displayData['categories'] = $this->cat_options;
$displayData['org_ind_type'] = $this->filter_org_ind_type;
$displayData['sort_by'] = $this->ordering_options;
$displayData['orderDir'] = $this->ordering_direction_options;
$displayData['list'] = $this->lists;
?>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div class="row mt-2">
		<div class="col-xs-12 col-md-5">
			<ul class="list-inline">
				<li><h1 class="fs-title af-mt-10"><strong><?php echo strtoupper(Text::_('COM_JGIVE_ALL_CAMPAIGNS'));?></strong></h1></li>
			</ul>
		</div>
		<div class="col-xs-12 col-md-7 mt-3">
			<form action="" method="post" name="adminForm4" id="adminForm4">
				<input type="hidden" name="option" value="com_jgive" />
				<input type="hidden" name="view" value="campaigns" />
				<input type="hidden" name="layout" value="all" />
				<?php echo HTMLHelper::_('form.token'); ?>
				<ul class="af-d-flex pull-right px-3">
					<?php echo $this->pagination->getLimitBox(); ?>
				</ul>
				<ul class="pull-right list-inline campaignsform">
					<?php $launch_camp_url = Uri::root() . substr(Route::_('index.php?option=com_jgive&view=campaignform&Itemid=' . $this->otherData['createCampItemid']), strlen(Uri::base(true)) + 1);?>

					<?php
					if ($this->canCreate)
					{?>
						<li class="campaign__launch list__separation px-3">
							<a href="<?php echo $launch_camp_url;?>" title="<?php echo Text::_('COM_JGIVE_CREATE_NEW_CAMPAIGN')?>">
								<i class="fa fa-paper-plane-o text-dark" aria-hidden="true"></i>
								<span class="hidden"><?php echo Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_LAUNCH_CAMPAIGN');?></span>
							</a>
						</li>
					<?php
					}

					if ($this->params['show_filters'])
					{
						if ($this->params['show_sorting_options'])
						{
						?>
						<li class="list__separation px-2">
							<div class="dropdown af-d-flex">
								<a onclick="jgive.jgShowSortFilter();" title="<?php echo Text::_('COM_JGIVE_ORDERING_OPTIONS');?>"><i class="fa fa-sort af-mr-5 text-dark" aria-hidden="true"></i></a>
								<?php
									echo HTMLHelper::_('select.genericlist', $this->ordering_options, "filter_order", ' size="1"
									onchange="jgiveCommon.filters.submitFilters(\'adminForm4\');"
									class="form-control collapse" name="filter_order"',"value", "text", $this->lists['filter_order']);
								?>
							</div>
						</li>
					<?php
						}?>
						<li class="list__separation px-2">
							<a id="displayFilter" href="javascript:void(0)" onclick="jgive.toggleDiv('displayFilterText');" title="<?php echo Text::_('COM_JGIVE_FILTER_CAMPAIGN');?>">
								<i class="fa fa-filter text-dark"></i>
							</a>
						</li>
					<?php
					}
					?>
					<?php
					if ($this->params['show_search_filter'])
					{?>
						<li class="campaign__search">
							<a id="searchCampBtn" href="javascript:void(0)" class="btn btn-sm" onclick="jgive.toggleSearch('SearchFilterInputBox');" title="<?php echo Text::_('COM_JGIVE_ENTER_CAMPAIGN_NAME')?>">
								<i class="fa fa-search text-dark" ></i>
							</a>
							<span class="pull-left search__campaign af-d-none search__campaign--wrapper" id="SearchFilterInputBox">
								<input type="text"
									placeholder="<?php echo Text::_('COM_JGIVE_ENTER_CAMPAIGN_NAME'); ?>"
									name="filter_search"
									id="filter_search"
									value="<?php echo $this->lists['filter_search'];?>"
									class="form-control col-xs-5"
									onchange="jgiveCommon.filters.submitFilters('adminForm4');"
									onkeypress="return jgive.searchCampaigns(event);"
									/>
								<button
									onclick="jgive.campaigns.campaignSearchClear()"
									type="button" class="btn btn-sm"
									id="searchCampClear"
									data-original-title="Clear"
									title="<?php echo Text::_('COM_JGIVE_CLEAR_TOOLTIP');?>">
									<i class="fa fa-close"></i>
								</button>
							</span>
						</li>
					<?php
					}?>
				</ul>
			</form>
		</div>
		<!-- Filters Div-->
		<div class="col-xs-12 campaign__filter af-pt-10 af-d-none" id="displayFilterText">
			<a id="displayToggleFilter" href="javascript:void(0)" onclick="jgive.toggleDiv('displayFilterText');" title="<?php echo Text::_('COM_JGIVE_FILTER_CAMPAIGN');?>" class="visible-xs pull-right">
				<i class="fa fa-remove" aria-hidden="true"></i>
			</a>
			<div class="row">
				<?php $jgiveLayout = new FileLayout('campaigns.filters');
					echo $jgiveLayout->render($displayData);
				?>
			</div>
		</div>
	</div>
<?php
	if (empty($this->data))
	{
	?>
		<div class="alert alert-warning">
			<?php echo Text::_('COM_JGIVE_NO_CAMPAIGN_FOUND');?>
		</div>
<?php
	}
	else
	{
	?>
	<div class="row mt-2">
		<div class="col-xs-12">
			<div class="row">
			<?php
				$pin_width = $this->params['pin_width'] ? $this->params['pin_width'] : 275;
				$pin_padding = $this->params['pin_padding'] ? $this->params['pin_padding'] : 5;
			?>
			<style type="text/css">
				@media only screen and (min-width: 481px){
				.jgive_pin_item { width: <?php echo $pin_width . 'px'; ?> !important;}
				}
			</style>
			<?php
				foreach ($this->data as $displayData)
				{
				?>
					<div class="col-sm-3 col-xs-12 jgive_pin_item af-mb-20">
						<?php
							$jgiveLayout = new FileLayout('campaigns.pin');
							echo $jgiveLayout->render($displayData);
						?>
					</div>
				<?php
				}
			?>
			</div>
		</div>
	</div>
<?php
	}
?>
	<form action="" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<?php $class_pagination = ''; ?>
				<div class="<?php echo $class_pagination; ?> com_jgive_align_center pull-right">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
			</div>
		</div>
		<input type="hidden" name="option" value="com_jgive" />
		<input type="hidden" name="view" value="campaigns" />
		<input type="hidden" name="layout" value="all" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
<script>
	var tjListFilters = [];

	jQuery(document).ready(function(){
		if(localStorage.getItem("flag") == 1 || localStorage.getItem("flag") != null)
		{
			jQuery(".campaign__filter").toggleClass( "af-d-none active" );
		}

		if(localStorage.getItem("filter") == 0)
		{
			jQuery(".search__campaign").toggleClass( "af-d-none active" );
		}

		if(localStorage.getItem("dropdown")==1)
		{
			jQuery("#filter_order").toggle();
		}

		jQuery("#limit").attr('onchange', 'jgiveCommon.filters.submitFilters(\'adminForm4\')');

		<?php
		foreach ($this->availableFilters as $availableFilter)
		{
			?>
			tjListFilters.push('<?php echo $availableFilter; ?>');
			<?php
		}
		?>
	});

	var jgive_baseurl = "<?php echo Uri::root(); ?>";
	var menuItemId = "<?php echo $this->otherData['allCampaignsItemid']; ?>";
</script>
