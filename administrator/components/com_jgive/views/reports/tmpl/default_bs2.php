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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

if (JVERSION < '4.0.0')
{
	HTMLHelper::_('behavior.framework', true);
}

HTMLHelper::stylesheet('media/com_jgive/css/jgive_bs3.min.css');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$user      = Factory::getUser();
?>
<form action="<?php echo Route::_('index.php?option=com_jgive&view=reports'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="span10 alert alert-info pull-right">
		<i><?php echo Text::_('COM_JGIVE_EXCLUDE_AMOUNT_DESC');?></i>
	</div>
	<?php
	if (!empty( $this->sidebar))
	{
		?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
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
		return;
	}
	?>
	<table class='table table-striped'>
		<thead>
			<tr>
				<th class="center"><?php echo Text::_('COM_JGIVE_NUMBER');?></th>
				<th class="center">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_NAME', 'c.title', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_CAMPAIGN_USER', 'v.vendor_title', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_TOTAL_GOAL_AMOUNT', 'c.goal_amount', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_NOF_DONATIONS', 'donations_count', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_TOTAL_AMOUNT_DONATION', 'total_amount', $listDirn, $listOrder); ?><br/>(A)
				</th>
				<th class="center">
					<?php echo Text::_('COM_JGIVE_TOTAL_AMOUNT_EXCLUDE')?><br/>(B)
				</th>
				<th class="center">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_TOTAL_AMOUNT_COMMISSION', 'total_commission', $listDirn, $listOrder); ?>
					<br/>(C)
				</th>
				<th class="center">
					<?php echo Text::_('COM_JGIVE_TOTAL_AMOUNT_TOBE_PAID');?>
					<br/>=(A-B-C)
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9" class="center">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$i = 0;
			$totalDonations = $totalAmount = $totalCommission = 0;
			$totalAmountToBePaid = $totalExclude = $totalGoalAmount = 0;

			foreach ($this->items as $key => $item)
			{
				$totalDonations      = $totalDonations + $item->donations_count;
				$totalAmount         = $totalAmount + $item->total_amount;
				$totalExclude        = $totalExclude + $item->exclude_amount;
				$totalCommission     = $totalCommission + $item->total_commission;
				$totalAmountToBePaid = $totalAmountToBePaid + ($item->total_amount - $item->total_commission - $item->exclude_amount);
				$totalGoalAmount     = $totalGoalAmount + $item->goal_amount;
				$link = Route::_(Uri::base() . 'index.php?option=com_jgive&view=donations&cid=' . $item->cid);
				?>
				<tr>
					<td class="center"><?php echo $key + 1;?></td>
					<td class="center">
						<a href="<?php echo $link;?>" 
						title="<?php echo Text::_('COM_JGIVE_NAME_TOOLTIP');?>">
							<?php echo htmlspecialchars(ucfirst($item->title), ENT_COMPAT, 'UTF-8');?>
						</a>
					</td>
					<td class="center">
						<?php $ulink = Route::_(
						Uri::base() . 'index.php?option=com_tjvendors&view=vendor&layout=update&client=com_jgive&vendor_id=' . $item->vendor_id
						);
						?>
						<a href="<?php echo $ulink;?>" >
							<?php echo htmlspecialchars($item->vendor_title, ENT_COMPAT, 'UTF-8');?>
						</a>
					</td>
					<td class="center">
						<?php echo $this->jgiveFrontendHelper->getFormattedPrice($item->goal_amount);?>
					</td>
					<td class="center"><?php echo $item->donations_count;?></td>
					<td class="center">
						<?php echo $this->jgiveFrontendHelper->getFormattedPrice($item->total_amount);?>
					</td>
					<td class="center">
						<?php echo $this->jgiveFrontendHelper->getFormattedPrice($item->exclude_amount);?>
					</td>
					<td class="center">
						<?php echo $this->jgiveFrontendHelper->getFormattedPrice($item->total_commission);?>
					</td>
					<td class="center">
						<?php
						echo $this->jgiveFrontendHelper->getFormattedPrice($item->total_amount - $item->total_commission - $item->exclude_amount);
						?>
					</td>
				</tr>
			<?php
			}
			?>
			<tr>
				<td class="center" colspan="3" class="com_jgive_align_right">
					<b><?php echo Text::_('COM_JGIVE_TOTAL');?></b>
				</td>
				<td class="center">
					<b><?php echo $this->jgiveFrontendHelper->getFormattedPrice($totalGoalAmount);?></b>
				</td>
				<td class="center">
					<b><?php echo number_format($totalDonations, 0, '', '');?></b>
				</td>
				<td class="center">
					<b><?php echo $this->jgiveFrontendHelper->getFormattedPrice($totalAmount);?></b>
				</td>
				<td class="center">
					<b><?php echo $this->jgiveFrontendHelper->getFormattedPrice($totalExclude);?></b>
				 </td>
				<td class="center">
					<b><?php echo $this->jgiveFrontendHelper->getFormattedPrice($totalCommission);?></b>
				</td>
				<td class="center">
					<b><?php echo $this->jgiveFrontendHelper->getFormattedPrice($totalAmountToBePaid);?></b>
				</td>
			</tr>
			<tr rowspan="3">
				<td class="com_jgive_align_right" colspan="8"></td>
				<td></td>
			</tr>
			
			<tr>
				<td class="com_jgive_align_right" colspan="8">
					<b><?php echo Text::_('COM_JGIVE_SUBTOTAL'); ?></b>
				</td>
				<td class="center">
					<b><?php echo $this->jgiveFrontendHelper->getFormattedPrice($totalAmountToBePaid);?></b>
				</td>
			</tr>
			
			<tr>
				<td class="com_jgive_align_right" colspan="8">
					<b><?php echo Text::_('COM_JGIVE_PAID'); ?></b>
				</td>
				<td class="center">
					<?php $totalPaidOutAmount = $this->reportsHelper->getTotalPaidOutAmount();?>
					<b><?php echo $this->jgiveFrontendHelper->getFormattedPrice($totalPaidOutAmount);?></b>
				</td>
			</tr>
			
			<tr>
				<td class="com_jgive_align_right" colspan="8">
					<b><?php echo Text::_('COM_JGIVE_BALANCE'); ?></b>
				</td>
				<td class="center">
					<b>
					<?php
					$totalRemainingAmountToBePaid = $totalAmountToBePaid - $totalPaidOutAmount;
					$balanceamt = number_format($totalRemainingAmountToBePaid, 2, '.', '');

					if ($balanceamt == '-0.00')
					{
						$balanceamt = 0;
						echo $this->jgiveFrontendHelper->getFormattedPrice($balanceamt);
					}
					else
					{
						echo $this->jgiveFrontendHelper->getFormattedPrice($balanceamt);
					}
					?>
					</b>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>


