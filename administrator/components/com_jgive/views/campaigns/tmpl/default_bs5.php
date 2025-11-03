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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

Factory::getApplication()->getDocument()->getWebAssetManager()->useStyle('searchtools')->useScript('searchtools');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = ($listOrder == 'a.ordering');

$user      = Factory::getUser();
$params    = ComponentHelper::getParams('com_jgive');

if (! empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

$sortFields = $this->getSortFields();

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_jgive&task=campaigns.saveOrderAjax&tmpl=component' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

Factory::getDocument()->addScriptDeclaration('
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;

		if (order !== "' . $listOrder . '")
		{
			dirn = "asc";
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, "");
	}
');
?>
<form action="<?php echo Route::_('index.php?option=com_jgive&view=campaigns&layout=' . $this->input->get('layout', '', 'STRING')); ?>" method="post" name="adminForm" id="adminForm">
	<div id="jgiveWrapper" class="row jgive <?php echo JVERSION < '3.0' ? 'techjoomla-bootstrap' : ''; ?>">
		<div class="col-md-12">
			<div id="j-main-container">
				
				<div id="filter-bar">
					<div class="row">
						<div class="col-md-12">
							<div class="js-stools-container-selector btn-group float-start">
								<input type="text" name="filter_search" id="filter_search"
								placeholder="<?php echo Text::_('COM_JGIVE_ENTER_CAMPAIGN_NAME'); ?>"
								value="<?php echo $this->escape($this->state->get('filter.filter_search')); ?>"
								class="hasTooltip form-control"
								title="<?php echo Text::_('COM_JGIVE_ENTER_CAMPAIGN_NAME'); ?>" />
								<button type="submit" class="btn btn-primary hasTooltip"
								title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
									<i class="icon-search"></i>
								</button>
								<button type="button" class="btn btn-secondary hasTooltip"
								title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"
								onclick="document.getElementById('filter_search').value='';this.form.submit();">
									<i class="icon-remove"></i>
								</button>
							</div>

							<div class="js-stools-container-selector float-end hidden-phone">
								<label for="limit" class="element-invisible">
									<?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
								</label>
								<?php echo $this->pagination->getLimitBox(); ?>
							</div>

							<div class="btn-group float-end">
								<div class="js-stools-container-selector float-end hidden-phone">
									<?php
									echo HTMLHelper::_('select.genericlist', $this->publish_states, "publish_states", 'class="form-select" size="1" onchange="document.adminForm.submit();" name="publish_states"', "value", "text", $this->state->get('filter.publish_states'));
									?>
								</div>

								<div class="js-stools-container-selector hidden-phone float-end">
									<select name="campaign_category" class="inputbox form-select" onchange="this.form.submit()">
										<option value=""><?php echo Text::_('JOPTION_SELECT_CATEGORY');?></option>
										<?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.options', 'com_jgive', array('filter.published' => array(1))), 'value', 'text', $this->state->get('filter.campaign_category'));?>
									</select>
								</div>

								<div class="js-stools-container-selector float-end hidden-phone">
									<?php
									echo HTMLHelper::_('select.genericlist', $this->campaign_type, "campaign_type", 'class="form-select" size="1" onchange="document.adminForm.submit();" name="campaign_type"', "value", "text", $this->state->get('filter.campaign_type'));
									?>
								</div>

								<div class="js-stools-container-selector float-end hidden-phone">
									<?php
									echo HTMLHelper::_('select.genericlist', $this->org_ind_type, "org_ind_type", 'class="form-select" size="1" onchange="document.adminForm.submit();" name="org_ind_type"', "value", "text", $this->state->get('filter.org_ind_type'));
									?>
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php if (empty($this->items)) : ?>
					<div class="clearfix">&nbsp;</div>
					<div class="alert alert-no-items">
						<?php echo Text::_('COM_JGIVE_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php
				else : ?>
					<table class="table" id="countryList">
						<thead>
							<tr>
								<th width="1%" class="hidden-phone">
									<?php echo HTMLHelper::_('grid.checkall');?>
								</th>

								<?php if (isset($this->items[0]->ordering)): ?>
									<th width="1%" class="nowrap af-text-center hidden-phone">
										<?php echo HTMLHelper::_('grid.sort', '<span class="icon-menu-2"></span>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
									</th>
								<?php endif; ?>

								<?php
								if (Factory::getUser($user->id)->authorise('core.edit.state', 'com_jgive'))
								{ ?>
									<?php if (isset($this->items[0]->published)): ?>
										<th width="1%" class="nowrap af-text-center">
											<?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
										</th>
									<?php endif; ?>
								<?php
								} ?>

								<th class='left hidden-phone'>
									<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_FEATURED', 'a.featured', $listDirn, $listOrder); ?>
								</th>

								<th class='left'>
									<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_CAMPAIGN_DETAILS', 'a.title', $listDirn, $listOrder); ?>
								</th>

								<th class=''>
									<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_CAMPAIGN_PROMOTER_NAME', 'a.first_name', $listDirn, $listOrder); ?>
								</th>

								<!--<th class="af-text-center hidden-phone">
									<?php //echo HTMLHelper::_('grid.sort', 'COM_JGIVE_START_DATE', 'a.start_date', $listDirn, $listOrder); ?>
								</th>-->

								<!--<th class="af-text-center hidden-phone">
									<?php //echo HTMLHelper::_('grid.sort', 'COM_JGIVE_END_DATE', 'a.end_date', $listDirn, $listOrder); ?>
								</th>-->

								<th class='left hidden-phone'>
									<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_GOAL_AMOUNT', 'a.goal_amount', $listDirn, $listOrder); ?>
								</th>

								<th class='left hidden-phone'>
									<?php echo Text::_('COM_JGIVE_AMOUNT_RECEIVED'); ?>
								</th>

								<!--<th class='left hidden-phone'>
									<?php //echo Text::_('COM_JGIVE_NO_OF_DONORS'); ?>
								</th>-->

								<th class='left hidden-phone'>
									<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_CAMPAIGN_SUCCESS_STATUS', 'a.success_status', $listDirn, $listOrder); ?>
								</th>

								<th class="af-text-center">
									<?php echo Text::_('COM_JGIVE_EDIT_PREVIEW'); ?>
								</th>

								<?php if (isset($this->items[0]->id)): ?>
									<th width="1%" class="nowrap af-text-center hidden-phone">
										<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
							</tr>
						</thead>

						<tbody <?php if ($saveOrder) : ?> class="text-break js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
							<?php
							foreach ($this->items as $i => $item):
								$ordering = ($listOrder == 'a.ordering');
								$canCreate = $user->authorise('core.create', 'com_jgive');
								$canEdit = $user->authorise('core.edit', 'com_jgive');
								$canCheckin = $user->authorise('core.manage', 'com_jgive');
								$canChange = $user->authorise('core.edit.state', 'com_jgive'); ?>

							<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->category_id ?: 'none'; ?>">
								<td class="af-text-center hidden-phone">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>

								<?php if (isset($this->items[0]->ordering)): ?>
									<td class="order nowrap af-text-center hidden-phone">
										<?php
										if ($canChange):
											$disableClassName = '';
											$disabledLabel = '';

											if (!$saveOrder):
												$disabledLabel = Text::_('JORDERINGDISABLED');
												$disableClassName = 'inactive tip-top';
											endif;
										?>

										<span class="sortable-handler hasTooltip <?php echo $disableClassName;?>" title="<?php echo $disabledLabel;?>">
												<i class="icon-ellipsis-v"></i>
										</span>

										<input type="text" style="display: none" name="order[]"
											size="5" value="<?php echo $item->ordering; ?>"
											class="width-20 text-area-order " />

										<?php else : ?>
												<span class="sortable-handler inactive">
													<i class="icon-ellipsis-v"></i>
												</span>
										<?php endif; ?>
									</td>
								<?php endif; ?>
								<?php
								if (Factory::getUser($user->id)->authorise('core.edit.state', 'com_jgive'))
								{ ?>
									<?php if (isset($this->items[0]->published)): ?>
										<td class="af-text-center">
											<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'campaigns.', $canChange, 'cb'); ?>
										</td>
									<?php endif; ?>
								<?php
								} ?>

								<td class="nowrap af-text-center hidden-phone hidden-tablet">

									<a href="javascript:void(0);"

										class='tbody-icon btn btn-micro active hasTooltip'

										onclick="return Joomla.listItemTask('cb<?php echo $i;?>','<?php echo ($item->featured) ? 'campaigns.unfeatured' : 'campaigns.featured';?>')"

										title="<?php echo ($this->campaignHelper->isFeatured($item->id)) ? Text::_('COM_JGIVE_UNFEATURE_TOOLBAR') : Text::_('COM_JGIVE_FEATURE_TOOLBAR');?>" >

										<?php $fclass = ($this->campaignHelper->isFeatured($item->id)) ? 'icon-color-featured icon-star' : 'icon-unfeatured';?>
										<i class="<?php echo $fclass;?>"></i>
									</a>
								</td>

								<td>
									<?php if (isset($item->checked_out) && $item->checked_out) : ?>
										<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'campaigns.', $canCheckin); ?>
									<?php endif; ?>

									<?php if ($canEdit) : ?>

										<a href="<?php echo Route::_('index.php?option=com_jgive&task=campaign.edit&id=' . (int) $item->id); ?>">
											<?php echo htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'); ?>
										</a>

										<?php else : ?>
											<?php echo htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'); ?>
									<?php endif; ?>
								</td>

								<td>
									<?php if (isset($item->checked_out) && $item->checked_out)
									{ ?>
										<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'campaigns.', $canCheckin); ?>
									<?php
									}?>
									<?php echo htmlspecialchars($item->vendor_title, ENT_COMPAT, 'UTF-8'); ?>
								</td>

								<!--<td class="af-text-center hidden-phone">
									<?php //echo HTMLHelper::_('date', $item->start_date, $params->get('date_format', 'j  M  Y')); ?>
								</td>-->

								<!--<td class="af-text-center hidden-phone">
									<?php //echo HTMLHelper::_('date', $item->end_date, $params->get('date_format', 'j  M  Y')); ?>
								</td>-->

								<td class="left hidden-phone">
									<?php echo $this->jgiveFrontendHelper->getFormattedPrice($item->goal_amount); ?>
								</td>

								<td class="left hidden-phone">
									<?php echo $this->jgiveFrontendHelper->getFormattedPrice($item->amount_received); ?>
								</td>

								<!--<td class="left hidden-phone">
									<?php //echo (int) $item->donor_count; ?>
								</td>-->

								<td class="left hidden-phone">
									<?php
										if($item->success_status == 0 || $item->success_status == 1 || $item->success_status == -1)
										{
											echo HTMLHelper::_('select.genericlist', $this->campaignSuccessStatus,
											'campaignSuccessStatus' . $i,
											'class="form-select-sm" size="1" onChange="changeSuccessState(' . $item->id . ', this);"', "value", "text",
											$item->success_status);
										}
										else
										{
											echo $campaign_success_status;
										}
									?>
								</td>

								<td>
									<div class="btn-group">
										<?php if ($canEdit) : ?>
										<a class="btn btn-sm active hasTooltip"
											href="<?php echo Route::_('index.php?option=com_jgive&view=campaign&layout=edit&id=' . (int) $item->id); ?>"
											title="<?php echo Text::_('COM_JGIVE_CAMPAIGN_EDIT');?>">
											<i class="icon-edit"></i>
										</a>
										<?php endif; ?>
										<a target="_blank" class="btn btn-sm active hasTooltip"
											href="<?php echo Uri::root().substr(Route::_('index.php?option=com_jgive&view=campaign&layout=default&id='.$item->id.'&Itemid='.$this->singleCampaignItemid),strlen(Uri::base(true))+1);?>"
											title="<?php echo Text::_('COM_JGIVE_CAMPAIGN_PREV');?>">
										</a>
									</div>
								</td>

								<?php if (isset($this->items[0]->id)): ?>
									<td class="af-text-center hidden-phone">
										<?php echo (int) $item->id; ?>
									</td>
								<?php endif; ?>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" id="hiddenCid" name="hiddenCid" value="" />
				<input type="hidden" id="hiddenSuccessStatus" name="hiddenSuccessStatus" value="" />

				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>

<script type="text/javascript">

Joomla.submitbutton = function(action)
{
	var form = document.adminForm;

	// Show confirmation message before delete campaign
	if (action == 'campaigns.delete')
	{
		// Show confirmation message
		if (confirm('<?php echo Text::_("COM_JGIVE_DELETE_CAMPAIGN")?>')) {
			Joomla.submitform(action );
		}

		return false;
	}
	else
	{
		Joomla.submitform(action );
	}
}
</script>

<script type="text/javascript">

function changeSuccessState(cid, ele)
{
	var selInd = ele.selectedIndex;
	var status = ele.options[selInd].value;
	var r;

	if (status == 1)
	{
		r = confirm('<?php echo Text::_("COM_JGIVE_STATUS_CHANGE_CONFIRM_SUCCESS");?>');
	}

	if (status == -1)
	{
		r = confirm('<?php echo Text::_("COM_JGIVE_STATUS_CHANGE_CONFIRM_FAILED");?>');
	}

	if (status == 0)
	{
		r = confirm('<?php echo Text::_("COM_JGIVE_STATUS_CHANGE_CONFIRM_ONGOING");?>');
	}

	if (r == true)
	{
		document.getElementById('hiddenCid').value = cid;
		document.getElementById('hiddenSuccessStatus').value = status;
		Joomla.submitbutton('campaign.changeSuccessState');
	}
	else
	{
		return false;
	}
}

</script>
