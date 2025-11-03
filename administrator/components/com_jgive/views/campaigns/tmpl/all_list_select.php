<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$document = Factory::getDocument();
ToolbarHelper::DeleteList(Text::_('COM_JGIVE_DELETE_CONFRIM'), 'remove', 'JTOOLBAR_DELETE');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'a.ordering';

$core_js = Uri::root() . 'media/system/js/core.js';
$flg     = 0;

foreach ($document->_scripts as $name => $ar)
{
	if ($name == $core_js)
	{
		$flg = 1;
	}
}

if ($flg == 0)
{
	echo "<script type='text/javascript' src='" . $core_js . "'></script>";
}

$js_joomla16 = "Joomla.submitbutton = function(prm)
{
	window.location = 'index.php?option=com_jgive&view=campaigns&layout=default';
}";

$document->addScriptDeclaration($js_joomla16);
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<?php
	if (empty($this->items))
	{
		?>
		<div class="alert alert-no-items">
			<?php echo Text::_('COM_JGIVE_NO_CAMPAIGN_FOUND');?>
		</div>
		<?php
	}
	else
	{
	?>
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<table class="table table-striped" width="100%">
			<thead>
				<tr>
					<th class='left'>
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_CAMPAIGN_DETAILS', 'a.title', $listDirn, $listOrder); ?>
					</th>

					<th class="center hidden-phone">
						<?php echo Text::_('COM_JGIVE_START_DATE'); ?>
					</th>

					<th class="center hidden-phone">
						<?php echo Text::_('COM_JGIVE_END_DATE'); ?>
					</th>

					<th class='left hidden-phone'>
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_GOAL_AMOUNT', 'a.goal_amount', $listDirn, $listOrder); ?>
					</th>

					<th class='left hidden-phone'>
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_AMOUNT_RECEIVED', 'a.goal_amount', $listDirn, $listOrder); ?>
					</th>

					<th class='left hidden-phone'>
						<?php echo Text::_('COM_JGIVE_DONORS'); ?>
					</th>

					<?php
					if (isset($this->items[0]->id))
					{
						?>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					<?php
					}
					?>
				</tr>
			</thead>
			<?php
				$j = 0;

				foreach ($this->items as $data)
				{
					?>
					<tr class="row<?php echo $j % 2;?>">
						<td>
							<div>
								<a class="pointer" href="JavaScript:Void(0);"
								onclick="window.parent.SelectCampaign(<?php echo $data->id ?>, '<?php echo addslashes($data->title); ?>', '<?php echo $data->minimum_amount ?>')"
								title="<?php echo Text::_('COM_JGIVE_CLICK_TO_VIEW_CAMP_TOOLTIP');?>">
									<?php echo $data->title;?>
								</a>
							</div>
							<div class="com_jgive_clear_both"></div>
						</td>

						<td><?php echo Factory::getDate($data->start_date)->Format($this->params->get('date_format', 'j  M  Y')); ?></td>

						<td><?php echo Factory::getDate($data->end_date)->Format($this->params->get('date_format', 'j  M  Y')); ?></td>

						<td><?php echo $this->jgiveFrontendHelper->getFormattedPrice($data->goal_amount); ?></td>

						<td><?php echo $this->jgiveFrontendHelper->getFormattedPrice($data->amount_received); ?></td>

						<td><?php echo $data->donor_count;?></td>

						<td><?php echo $data->id;?></td>
					</tr>
					<?php
					$j++;
				}
				?>
				<tr>
					<?php $class_pagination = '';?>
					<td colspan="11" class="com_jgive_align_center <?php echo $class_pagination; ?> ">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
		</table>
	<?php
	}
	?>
	<input type="hidden" name="option" value="com_jgive" />
	<input type="hidden" name="view" value="campaigns" />
	<input type="hidden" name="layout" value="all_list_select" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
	<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" id="controller" name="controller" value="campaigns"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

