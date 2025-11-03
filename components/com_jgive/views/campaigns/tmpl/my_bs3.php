<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2024 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('formbehavior.chosen', 'select');

$sortDirection       = Factory::getApplication()->input->get('filter_order_Dir', 'desc', 'STRING');
$campaignHelper      = new campaignHelper;
$jgiveFrontendHelper = new jgiveFrontendHelper;

// Jomsocial toolbar
if (isset($this->jomsocialToolbarHtml))
{
	echo $this->jomsocialToolbarHtml;
}

$user                    = Factory::getUser();
$canEdit                 = $user->authorise('core.edit', 'com_jgive');
$campaign_period_in_days = $this->params->get('campaign_period_in_days');
?>

<div id="jgiveWrapper" class="row">
	<div class="col-md-12">
		<div class="page-header">
			<h1 class="fs-title af-mt-10"><?php echo Text::_('COM_JGIVE_MY_CAMPAIGNS');?></h1>
		</div>
		<form action="<?php echo Route::_('index.php?option=com_jgive&view=campaigns&layout=my&Itemid=' . $this->otherData['myCampaignsItemid']);?>" method="GET" name="adminForm" id="adminForm">
			<!-- show pagination limit box and filters -->
			<div class="row">
				<div class="col-md-12">
					<div class="btn-toolbar pull-right mb-3">
						<div class="btn-group clearfix">
							<?php
							if ($this->params->get('show_type_filter_my_camp') && $campaignHelper->filedToShowOrHide('campaign_type'))
							{
								if (isset($this->campaign_type_filter_options[0]->text))
								{
									$this->campaign_type_filter_options[0]->text = Text::_('COM_JGIVE_SELECT_CAMPAIGN_TYPE');
								}

								echo HTMLHelper::_('select.genericlist', $this->campaign_type_filter_options, "filter_campaign_type", ' size="1"
								onchange="jgiveCommon.filters.submitFilters(\'adminForm\');" name="filter_campaign_type"', "value", "text", $this->lists['filter_campaign_type']);
								?>
								&nbsp;
								<?php
							}?>
						</div>
						<div class="btn-group clearfix">
							<?php
							if ($this->params->get('show_category_filter_my'))
							{
								if (isset($this->cat_options[0]->text))
								{
									$this->cat_options[0]->text = Text::_('COM_JGIVE_CAMPAIGN_CATEGORIES');
								}

								echo HTMLHelper::_('select.genericlist', $this->cat_options, "filter_campaign_cat", 'class="" size="1"
								onchange="jgiveCommon.filters.submitFilters(\'adminForm\');" name="filter_campaign_cat"', "value", "text", $this->lists['filter_campaign_cat']);
								?>
								&nbsp;
								<?php
							}?>
						</div>
						<div class="btn-group clearfix">
							<?php
							if ($this->params->get('show_org_ind_type_filter_my'))
							{
								if (isset($this->filter_org_ind_type[0]->text))
								{
									$this->filter_org_ind_type[0]->text = Text::_('COM_JGIVE_SELECT_TYPE_ORG_INDIVIDUALS');
								}

								echo HTMLHelper::_('select.genericlist', $this->filter_org_ind_type, "filter_org_ind_type", 'class="" size="1"
								onchange="jgiveCommon.filters.submitFilters(\'adminForm\');" name="filter_org_ind_type"',
								"value", "text", $this->lists['filter_org_ind_type']);
								?>
								&nbsp;
								<?php
							}
							?>
						</div>

						<div class="btn-group pull-right hidden-phone">
							<label for="limit" class="element-invisible">
								<?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
							</label>
							<?php echo $this->pagination->getLimitBox(); ?>
						</div>
					</div>
				</div>
			</div>

			<!-- show message if no items found -->
			<?php
			if (empty($this->data))
			{ ?>
				<div class="row">
					<div class="col-md-12 alert alert-warning">
						<?php echo Text::_('COM_JGIVE_NO_CAMPAIGN_FOUND');?>
					</div>
				</div>
			<?php
			}
			else
			{?>
				<div class="no-more-tables">
					<table class="table table-striped table-bordered table-hover table-light border mt-1">
						<thead class="text-break table-primary text-light">
							<tr>
								<th><?php echo Text::_('COM_JGIVE_GIVEBACK_NUMBER');?></th>
								<th>
									<a href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'title', $sortDirection);?>" class="hasPopover" data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>" data-placement="top" data-original-title="<?php echo Text::_('COM_JGIVE_CAMPAIGN_DETAILS');?>"><?php echo Text::_('COM_JGIVE_CAMPAIGN_DETAILS');?></a>
								</th>
								<th>
									<a href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'start_date', $sortDirection);?>" class="hasPopover" data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>" data-placement="top" data-original-title="<?php echo Text::_('COM_JGIVE_START_DATE');?>"><?php echo Text::_('COM_JGIVE_START_DATE');?></a>
								</th>
								<th>
									<?php
									if(empty($campaign_period_in_days) || $campaign_period_in_days == 0 )
									{
										?>
										<a href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'end_date', $sortDirection);?>" class="hasPopover" data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>" data-placement="top" data-original-title="<?php echo Text::_('COM_JGIVE_END_DATE');?>"><?php echo Text::_('COM_JGIVE_END_DATE');?></a>
										<?php
									}
									else
									{
										echo Text::_('COM_JGIVE_END_DURATION');
									}?>
								</th>
								<th>
									<a href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'goal_amount', $sortDirection);?>" class="hasPopover" data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>" data-placement="top" data-original-title="<?php echo Text::_('COM_JGIVE_GOAL_AMOUNT');?>"><?php echo Text::_('COM_JGIVE_GOAL_AMOUNT');?></a>
								</th>

								<th>
									<a href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'amount_received', $sortDirection);?>" class="hasPopover" data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>" data-placement="top" data-original-title="<?php echo Text::_('COM_JGIVE_AMOUNT_RECEIVED');?>"><?php echo Text::_('COM_JGIVE_AMOUNT_RECEIVED');?></a>
								</th>

								<th>
									<a href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'campaignDonorsCount', $sortDirection);?>" class="hasPopover" data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>" data-placement="top" data-original-title="<?php echo Text::_('COM_JGIVE_DONORS');?>"><?php echo Text::_('COM_JGIVE_DONORS');?></a>
								</th>

								<th>
									<?php echo Text::_('COM_JGIVE_ACTION');?>
								</th>
								<th>
									<a
									href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'type', $sortDirection);?>"
									class="hasPopover"
									data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>" data-placement="top"
									data-original-title="<?php echo Text::_('COM_JGIVE_TYPE');?>">
										<?php echo Text::_('COM_JGIVE_TYPE');?>
									</a>
								</th>
							</tr>
						</thead>

						<tbody>
						<?php
						$i = 1;

						foreach ($this->data as $data)
						{
							$amounts = $campaignHelper->getCampaignAmounts($data['id']);
							$data['amount_received']  = $amounts['amount_received'];
							$data['remaining_amount'] = $amounts['remaining_amount'];

							// Count donors(donations)
							$data['donor_count']      = $campaignHelper->getCampaignDonorsCount($data['id']);
							?>
							<tr>
								<td data-title="<?php echo Text::_("COM_JGIVE_GIVEBACK_NUMBER"); ?>"><?php echo (int) $i;?></td>

								<td data-title="<?php echo Text::_("COM_JGIVE_CAMPAIGN_DETAILS"); ?>">
									<div>
										<a href="<?php echo Uri::root() . substr(Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . (int) $data['id'] . '&Itemid=' . $this->singleCampaignItemid), strlen(Uri::base(true)) + 1);?>">
											<?php echo htmlspecialchars($data['title'], ENT_COMPAT, 'UTF-8'); ?>
									</div>
									<div class="com_jgive_clear_both"></div>
								</td>
								<td data-title="<?php echo Text::_("COM_JGIVE_START_DATE"); ?>">
									<?php echo HTMLHelper::_('date', $data['start_date'], $this->params->get('date_format', 'j  M  Y'));?>
								</td>

								<?php
								if (empty($campaign_period_in_days) || $campaign_period_in_days == 0 )
								{
								?>
									<td data-title="<?php echo Text::_("COM_JGIVE_END_DATE"); ?>">
										<?php echo HTMLHelper::_('date', $data['end_date'], $this->params->get('date_format', 'j  M  Y'));?>
									</td>
									<?php
								}
								else
								{
								?>
									<td data-title="<?php echo Text::_("COM_JGIVE_END_DURATION"); ?>">
										<?php echo htmlspecialchars($data['days_limit'], ENT_COMPAT, 'UTF-8'); ?>
									</td>
								<?php
								}?>
								<td data-title="<?php echo Text::_("COM_JGIVE_GOAL_AMOUNT"); ?>">
									<?php
									$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($data['goal_amount']);
									echo $diplay_amount_with_format;
									?>
								</td>
								<td data-title="<?php echo Text::_("COM_JGIVE_AMOUNT_RECEIVED"); ?>">
									<?php
									$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($data['amount_received']);
									echo $diplay_amount_with_format;
									?>
								</td>

								<td data-title="<?php echo Text::_("COM_JGIVE_DONORS"); ?>">
									<?php echo $data['donor_count'];?>
								</td>

								<td data-title="<?php echo Text::_("COM_JGIVE_PUBLISHED"); ?>">
									<?php
									if ($data['published'])
									{
									?>
										<i class="fa fa-check-circle-o" aria-hidden="true" title="<?php echo Text::_("COM_JGIVE_PUBLISH"); ?>"></i>
									<?php
									}
									else
									{
									?>
										<i class="fa fa-times-circle-o" aria-hidden="true" title="<?php echo Text::_("COM_JGIVE_UNPUBLISHED"); ?>"></i>
									<?php
									}
									
									if ($canEdit)
									{
				
									?>
										<a href="<?php echo Route::_('index.php?option=com_jgive&view=campaignform&layout=default&Itemid='. $this->otherData['singleCampaignItemid'] .'&id=' . (int) $data['id']); ?>">
											<i class="fa fa-pencil-square-o" aria-hidden="true" title="<?php echo Text::_("COM_JGIVE_EDIT_CAMPAIGN"); ?>"></i>
										</a>
									<?php
									}
									?>
								</td>
								<td data-title="<?php echo Text::_("COM_JGIVE_TYPE"); ?>">
									<?php echo htmlspecialchars(ucfirst($data['type']), ENT_COMPAT, 'UTF-8');?>
								</td>
							</tr>
							<?php
							$i++;
						}
						?>
						</tbody>
					</table>
				</div>
			<?php
			} ?>

			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<?php echo $this->pagination->getListFooter(); ?>
			</div>
			<input type="hidden" name="option" value="com_jgive" />
			<input type="hidden" name="view" value="campaigns" />
			<input type="hidden" name="layout" value="my" />
			<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>

<script type="text/javascript">
	var tjListFilters = [];

	jQuery(document).ready(function(){
		jQuery("#limit").attr('onchange', 'jgiveCommon.filters.submitFilters(\'adminForm\')');

		<?php
		foreach ($this->availableFilters as $availableFilter)
		{
			?>
			tjListFilters.push('<?php echo $availableFilter; ?>');
			<?php
		}
		?>
	});

	jgive_baseurl = "<?php echo Uri::root(); ?>";
	menuItemId = "<?php echo $this->otherData['myCampaignsItemid']; ?>";
</script>
