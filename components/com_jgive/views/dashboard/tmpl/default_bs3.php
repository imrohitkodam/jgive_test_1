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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

// Creating Object of FrontendHelper class
$jgiveFrontendHelper = new jgiveFrontendHelper;
$campaignHelper = new campaignHelper;

// Set Title by campaign name
$document = Factory::getDocument();

// Load Chart Javascript Files.
HTMLHelper::_('script', 'media/com_jgive/vendors/js/Chart.min.js');
?>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div class="row af-pt-20 campaignMain border-b af-bg-faded">
		<div class="col-md-12">
			<h1 class="af-mt-0 af-font-bold fs-title"><?php echo Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_OVERVIEW');?></h1>
			<div class="row">
			<?php
				echo $this->loadTemplate("graph");
				echo $this->loadTemplate("donors");

				if ($this->promoterDashboardData['params']['campaign_activity'] != '0')
				{
					echo $this->loadTemplate("activity");
				}
			?>
			</div>
		</div>
	</div>
	<form action="" name="dashboardFilterform" method="post" id="dashboardFilterform">
		<div class="row af-mt-25">
			<div class="col-xs-12 col-sm-4">
				<h4><strong><?php echo strtoupper(Text::_('COM_JGIVE_MY_CAMPAIGNS'));?></strong></h4>
			</div>
			<div class="col-xs-12 col-sm-8">
				<ul class="pull-right list-inline campaignsform mt-2">
					<?php
					$launch_camp_url = Uri::root() . substr(Route::_('index.php?option=com_jgive&view=campaignform&Itemid=' . $this->promoterDashboardData['otherData']->createCampaignItemid), strlen(Uri::base(true)) + 1);
					?>
					<li class="campaign__launch list__separation">
						<a href="<?php echo $launch_camp_url;?>" title="<?php echo Text::_('COM_JGIVE_CREATE_NEW_CAMPAIGN')?>">
						<i class="fa fa-paper-plane-o text-dark" aria-hidden="true"></i>
						<span class="hidden"><?php echo Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_LAUNCH_CAMPAIGN');?></span>
						</a>
					</li>
					<li class="list__separation">
						<a id="dashboardFilterList" href="javascript:void(0)" onclick="jgive.dashboard.toggleDiv('dashboardFilterOptions');"title="<?php echo Text::_('COM_JGIVE_FILTER_CAMPAIGN');?>">
						<i class="fa fa-filter text-dark"></i>
						</a>
					</li>
					<li class="campaign__search">
						<a id="searchCampBtn" href="javascript:void(0)" onclick="jgive.toggleSearch('SearchFilterInputBox');" title="<?php echo Text::_('COM_JGIVE_ENTER_CAMPAIGN_NAME')?>">
							<i class="fa fa-search text-dark" ></i>
						</a>
						<span class="pull-left search__campaign hide" id="SearchFilterInputBox">
							<input type="text"
								name="searchMyCamp"
								id="searchMyCamp"
								value="<?php echo $mainframe->getUserStateFromRequest("$option.filter_search", 'filter_search', '', 'string');?>"
								class="form-control col-xs-5"
								placeholder="<?php echo Text::_('COM_JGIVE_DASHBOARD_SEARCH_BY_CAMPAIGN_TITLE')?>"
								onchange="document.dashboardform.submit();" />
							<button
								onclick="localStorage.setItem('filter', 1);document.getElementById('filter_search').value = '';this.form.submit();"
								type="button"
								class="btn campaign__search--clear af-absolute"
								data-original-title="Clear"
								title="<?php echo Text::_('COM_JGIVE_CLEAR_TOOLTIP');?>">
								<i class="fa fa-close"></i>
							</button>
						</span>
					</li>
					<li>
						<?php echo HTMLHelper::_('select.genericlist', $this->promoterDashboardData['dashboardFilterOption'], "dashboard_filters", 'class="form-control" size="1" onchange="this.form.submit();" name="dashboard_filters"',"value", "text",$this->promoterDashboardData['filterData']->dashboard_filters); ?>
					</li>
				</ul>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 campaigns__filter af-pt-10 hide" id="dashboardFilterOptions">
				<?php
					echo $this->loadTemplate("filters_bs3");
				?>
			</div>
			<div class="col-xs-12 af-mt-15">
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active" id="home">
						<div class="row">
							<?php
							if (empty($this->promoterDashboardData['myCampData']))
							{
							?>
								<div class="alert alert-warning">
									<?php echo Text::_('COM_JGIVE_NO_CAMPAIGN_FOUND');?>
								</div>
							<?php
							}
							else
							{?>
								<div>
									<?php
										echo $this->loadTemplate("pin_" . JGIVE_LOAD_BOOTSTRAP_VERSION);
									?>
								</div>
							<?php
							}
							?>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane" id="profile">...</div>
				</div>
			</div>
		</div>
	</form>
</div>

<input type="hidden" id="user_id" name="user_id" value="<?php echo $this->promoterDashboardData['otherData']->logged_userid; ?>" />
<script type="text/javascript">
	var jgive_baseurl = "<?php echo Uri::root(); ?>";
	jgive.dashboard.init();
</script>
