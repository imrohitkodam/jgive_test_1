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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.multiselect');

$user        = Factory::getUser();
$userId      = $user->get('id');
$listOrder   = $this->state->get('list.ordering', '');
$listDirn    = $this->state->get('list.direction', '');
$saveOrder   = $listOrder == 'a.ordering';
$sortFields  = $this->getSortFields();
$jgivehelper = $this->jgiveFrontendHelper;
$params      = ComponentHelper::getParams('com_jgive');
?>
<form action="<?php echo Route::_('index.php?option=com_jgive&view=donors&layout=' . $this->input->get('layout', '', 'STRING')); ?>"
method="post" name="adminForm" id="adminForm">
	<?php
	if(!empty($this->sidebar))
	{
	?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar;?>
		</div>
		<div id="j-main-container" class="span10">
	<?php
	}
	else
	{
	?>
		<div id="j-main-container">
	<?php
	}

	// Sorting and Searching Options
	echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>

	<div class="clearfix"> </div>

	<?php
	if (empty($this->items))
	{
	?>
		<div class="clearfix">&nbsp;</div>
		<div class="alert alert-no-items">
			<?php echo Text::_('COM_JGIVE_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php
	}
	else
	{
	?>
		<table class="table table-striped" id="all_donorslist">
			<thead>
				<tr>
					<th width="1%" class="center hidden-phone">
						<input type="checkbox" name="checkall-toggle" value=""
						title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
						onclick="Joomla.checkAll(this)" />
					</th>
					<th class="center" width="5%">
						<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DASHBOARD_DONOR_NAME', 'a.first_name', $listDirn, $listOrder); ?>
					</th>
					<th class="center" width="5%">
						<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DASHBOARD_DONOR_EMAIL', 'a.email', $listDirn, $listOrder); ?>
					</th>
					<th class="center" width="5%">
						<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DONORS_ADDRESS', 'a.address', $listDirn, $listOrder); ?>
					</th>
					<th class="center" width="5%">
						<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DONORS_PHONE', 'a.phone', $listDirn, $listOrder); ?>
					</th>
					<th class="center" width="5%">
						<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_CAMPAIGN_DETAILS', 'a.campaign_id', $listDirn, $listOrder); ?>
					</th>
					<th class="center" width="5%">
						<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DONORS_DONATION_AMOUNT', 'o.original_amount', $listDirn, $listOrder); ?>
					</th>
					<th class="center" width="5%">
						<?php echo Text::_('COM_JGIVE_DONORS_GIVEBACK_DESC'); ?>
					</th>
					<th class="center" width="5%">
						<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DONORS_CDATE', 'o.cdate', $listDirn, $listOrder); ?>
					</th>
					<th class="center" width="5%">
						<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_PAYMENT_RECEIVED_DATE', 'o.payment_received_date', $listDirn, $listOrder); ?>
					</th>
					<th class="center" width="5%">
						<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DONATION_DONOR_TYPE', 'a.donor_type', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="center nowrap center hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<?php
				if (isset($this->items[0]))
				{
					$colspan = count(get_object_vars($this->items[0]));
				}
				else
				{
					$colspan = 10;
				}
				?>
				<tr>
					<td colspan="<?php echo $colspan ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$num = 1;

				foreach ($this->items as $i => $item)
				{
					$canEdit = $user->authorise('core.edit', 'com_jgive');

					if (!$canEdit && $user->authorise('core.edit.own', 'com_jgive'))
					{
						$canEdit = Factory::getUser()->id == $item->created_by;
					}
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center hidden-phone">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center" data-title="<?php echo Text::_("COM_JGIVE_DASHBOARD_DONOR_NAME"); ?>">
							<?php
							if (isset($item->checked_out) && $item->checked_out)
							{
							?>
								<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'donors.', $canCheckin); ?>
							<?php
							}

							if (!empty($item->org_name) && $item->donor_type == 'org')
							{
								echo htmlspecialchars($item->org_name, ENT_COMPAT, 'UTF-8');
							}
							else
							{
								echo htmlspecialchars($item->first_name, ENT_COMPAT, 'UTF-8') . " " . htmlspecialchars($item->last_name, ENT_COMPAT, 'UTF-8');
							}
							?>
						</td>
						<td class="center wordbreak" data-title="<?php echo Text::_("COM_JGIVE_DASHBOARD_DONOR_EMAIL"); ?>">
							<?php echo $item->email; ?>
						</td>
						<td class="center" data-title="<?php echo Text::_("COM_JGIVE_DONORS_ADDRESS"); ?>">
							<?php
							if (empty($item->address) && empty($item->city) && empty($item->state))
							{
								echo '-';
							}
							else
							{
							?>
								<?php echo !empty($item->address) ? htmlspecialchars($item->address, ENT_COMPAT, 'UTF-8') . ',</br>' : ''; ?>
								<?php echo !empty($item->city) ? $item->city . ',</br>' : ''; ?>
								<?php echo !empty($item->state) ? $item->state . ',</br>' : ''; ?>
								<?php echo !empty($item->country) ? $item->country . ',</br>' : ''; ?>
								<?php echo !empty($item->zip) ? htmlspecialchars($item->zip, ENT_COMPAT, 'UTF-8') . ',</br>' : '-'; ?>
								<?php echo !empty($item->address2) ? htmlspecialchars($item->address2, ENT_COMPAT, 'UTF-8') . ',</br>' : '-'; ?>
							<?php
							}?>
						</td>
						<td class="center" data-title="<?php echo Text::_("COM_JGIVE_DONORS_PHONE"); ?>">
							<?php echo $item->phone ? htmlspecialchars($item->phone, ENT_COMPAT, 'UTF-8') : " - "; ?>
						</td>
						<td class="center" data-title="<?php echo Text::_("COM_JGIVE_CAMPAIGN_DETAILS"); ?>">
							<a target="_blank" href="<?php echo Uri::root() .
							substr(
								Route::_(
									'index.php?option=com_jgive&view=campaign&layout=default&id=' . $item->campaign_id . '&Itemid=' . $this->singleCampaignItemid
								),
								strlen(Uri::base(true)) + 1
							);?>"
							title="<?php echo Text::_('COM_JGIVE_DASHBOARD_TOOLTIP_VIEW_ORDER_MSG');?>">
							<?php echo htmlspecialchars($item->campaigns_title, ENT_COMPAT, 'UTF-8');?>
							</a>
						</td>
						<td class="center" data-title="<?php echo Text::_("COM_JGIVE_DONORS_DONATION_AMOUNT"); ?>">
							<?php echo $jgivehelper->getFormattedPrice($item->donation_amount);?>
						</td>
						<td class="center" data-title="<?php echo Text::_("COM_JGIVE_DONORS_GIVEBACK_DESC"); ?>">
							<?php echo $item->gdesc ? htmlspecialchars($item->gdesc, ENT_COMPAT, 'UTF-8') : ' - ';?>
						</td>
						<td class="center hidden-phone">
							<?php echo HTMLHelper::_('date', $item->cdate, $params->get('date_format', 'j  M  Y'));?>
						</td>
						<td class="center hidden-phone">
							<?php
							if ($item->payment_received_date == 0)
							{
								echo '-';
							}
							else
							{
								echo HTMLHelper::_('date', $item->payment_received_date, $params->get('date_format', 'j  M  Y'));
							}?>
						</td>
						<td class="center">
							<?php echo ($item->donor_type == 'org')? Text::_('COM_JGIVE_DONATION_ORGANIZATION'): Text::_('COM_JGIVE_DONATION_INDIVIDUAL');?>
						</td>
						<td class="center hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php
				}?>
			</tbody>
		</table>
	<?php
	}?>

		<input type="hidden" id="task" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if(task == 'donors.redirectToMassmailing')
		{
			if(document.adminForm.boxchecked.value == 0)
			{
				var msg = "<?php echo Text::_('COM_JGIVE_MSG_FOR_SELECT_USER'); ?>";
				alert(msg);
				return false;
			}
			else
			{
				Joomla.submitform(task);
			}
		}
		else
		{
			Joomla.submitform(task);
		}
	}
</script>
