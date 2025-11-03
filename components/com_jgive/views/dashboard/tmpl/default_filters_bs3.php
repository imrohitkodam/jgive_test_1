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
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$document=Factory::getDocument();
?>
<div class="row">
	<div class="col-xs-12 campaign__filter af-pt-10">
		<a id="displayToggleFilter" href="javascript:void(0)" onclick="jgive.dashboard.toggleDiv('dashboardFilterOptions');" title="<?php echo Text::_('COM_JGIVE_FILTER_CAMPAIGN');?>" class="visible-xs pull-right">
			<i class="fa fa-remove" aria-hidden="true"></i>
		</a>
		<h5><strong><?php echo Text::_('COM_JGIVE_FILTERS');?></strong></h5>
		<div class="row">
			<div class="col-xs-12 col-sm-3 campaign__filter--ht">
				<h5><strong><?php echo Text::_('COM_JGIVE_CATEGORY');?></strong></h5>
				<?php
				foreach ($this->promoterDashboardData['categories'] as $category)
				{
					if ($this->promoterDashboardData['filterCatList'] == $category->value)
					{
						$check = "checked";
					}
					else
					{
						$check = "";
					}
				?>
					<div>
						<label>
							<input type="radio" name="cat" value="<?php echo $category->value;?>"
							<?php echo $check;?>>
							<?php echo $category->text;?>
						</label>
					</div>
				<?php
				}
				?>
			</div>
			<div class="col-xs-12 col-sm-3 campaign__filter--ht">
				<h5><strong><?php echo Text::_('COM_JGIVE_DONATION_STATUS');?></strong></h5>
				<?php

					if (isset($this->promoterDashboardData['filterCampStatus']) && $this->promoterDashboardData['filterCampStatus'] != null)
					{
						if ($this->promoterDashboardData['filterCampStatus'] == 1)
						{
							$checkpublish = "checked";
							$checkUnpublish = "";
						}
						elseif ($this->promoterDashboardData['filterCampStatus'] == 0)
						{
							$checkpublish = "";
							$checkUnpublish = "checked";
						}
						$checkall = "";
					}
					else
					{
						$checkall = "checked";
						$checkpublish = "";
						$checkUnpublish = "";
					}
				?>
				<div>
					<label>
						<input type="radio" name="campStatus" value="" <?php echo $checkall;?>>
						<?php echo Text::_('COM_JGIVE_ALL');?>
					</label>
				</div>
				<div>
					<label>
						<input type="radio" name="campStatus" value="1" <?php echo $checkpublish;?>>
						<?php echo Text::_('COM_JGIVE_PUBLISHED');?>
					</label>
				</div>
				<div>
					<label>
						<input type="radio" name="campStatus" value="0" <?php echo $checkUnpublish;?>>
						<?php echo Text::_('COM_JGIVE_UNPUBLISHED');?>
					</label>
				</div>
			</div>
		<?php
		if (count($this->promoterDashboardData['params']['camp_type']) == 2)
		{
		?>
			<div class="col-xs-12 col-sm-3 campaign__filter--ht">
				<h5><strong><?php echo Text::_('COM_JGIVE_CAMP_TYPE');?></strong></h5>
				<?php
				foreach ($this->promoterDashboardData['campaignType'] as $campTypearr)
				{
					if ($this->promoterDashboardData['filterCampType'] == $campTypearr->value)
					{
						$check = "checked";
					}
					else
					{
						$check = "";
					}
					?>
					<div>
						<label>
							<input type="radio" name="campType" value="<?php echo $campTypearr->value;?>"
							<?php echo $check;?> />
							<?php echo $campTypearr->text; ?>
						</label>
					</div>
				<?php
				}
				?>
			</div>
		<?php
		}?>

		<div class="col-xs-12 col-sm-3 campaign__filter--ht">
			<h5><strong><?php echo Text::_('COM_JGIVE_ORG_IND_TYPE');?></strong></h5>
			<?php
			foreach ($this->promoterDashboardData['organizationType'] as $organizationType)
			{
				if ($this->promoterDashboardData['filterOrgTypeList'] == $organizationType->value)
				{
					$check = "checked";
				}
				else
				{
					$check = "";
				}
			?>
				<div>
					<label>
						<input type="radio" name="orgType" value="<?php echo $organizationType->value;?>"
						<?php echo $check;?>/>
						<?php echo $organizationType->text; ?>
					</label>
				</div>
			<?php
			}
			?>
		</div>
		<div class="col-xs-12">
			<ul class="pull-right list-inline">
				<li>
					<a class="pull-right" onclick="jgive.dashboard.clearFilter()" href="javascript:void(0);">
						<i class="fa fa-repeat" aria-hidden="true"></i>
						<?php echo Text::_('COM_JGIVE_REST_FILTERS');?>
					</a>
				</li>
				<li>
					<button type="button" onclick="document.dashboardFilterform.submit();" class="btn btn-mini btn-donate btn-primary pull-right">
						<?php echo Text::_('COM_JGIVE_DASHBOARD_APPLY_FILTER'); ?>
					</button>
				</li>
			</ul>
		</div>
	</div>
	</div>
</div>
<?php echo HTMLHelper::_('form.token'); ?>
