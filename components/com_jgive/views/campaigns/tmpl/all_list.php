<?php
/**
* @version		1.0.0 jgive $
* @package		jgive
* @copyright	Copyright © 2012 - All rights reserved.
* @license		GNU/GPL
* @author		TechJoomla
* @author mail	extensions@techjoomla.com
* @website		http://techjoomla.com
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

ToolbarHelper::publishList();
ToolbarHelper::unpublishList();
ToolbarHelper::preferences( 'com_jgive' );

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$campaignHelper =new campaignHelper();

$js_joomla16 ="Joomla.submitbutton = function(prm)
{
	if(prm=='publish' || prm=='unpublish' || prm=='remove')
	{
		Joomla.submitform(prm);
	}
	else
	{
		window.location = 'index.php?option=com_jgive&view=campaigns&layout=all_list';
	}
}";
$document->addScriptDeclaration($js_joomla16);
?>

<?php
	if($this->issite)
	{
		?>
		<div class="well" >
			<div class="alert alert-error">
				<span ><?php echo Text::_('COM_JGIVE_NO_ACCESS_MSG'); ?> </span>
			</div>
		</div>
		</div><!-- eoc akeeba-bootstrap -->
		<?php
			return false;
	}
	?>

	<?php
	if($this->issite)
	{
		?>
		<!--page header-->
		<div class="componentheading">
			<?php echo Text::_('COM_JGIVE_ALL_CAMPAIGNS');?>
		</div>
		<hr/>
		<?php
	}
	?>


	<form action="index.php" method="post" name="adminForm" id="adminForm">

	<?php
	// @ sice version 3.0 Jhtmlsidebar for menu
		if (!empty( $this->sidebar)) : ?>
			<div id="j-sidebar-container" class="span2">
				<?php echo $this->sidebar; ?>
			</div>
			<div id="j-main-container" class="span10">
		<?php else : ?>
			<div id="j-main-container">
		<?php endif;?>

	<div class="btn-group pull-right hidden-phone">
		<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
	<?php $tblclass = 'table table-striped';?>
	<table class="<?php echo $tblclass; ?>" width="100%">
		<thead>
			<tr>
				<th width="1%" >
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
				</th>
				<th width="15%"><?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_CAMPAIGN_DETAILS', 'title', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
			<!-- categories -->
				<?php
				if (!$this->issite)
				{
					?>
					<th><?php echo Text::_('COM_JGIVE_INTERNAL_USE');?></th>
					<?php
				}
				?>
				<!--Added by Sneha-->
				<th width="5%"><?php echo Text::_('COM_JGIVE_EDIT_LINK'); ?></th>
				<th width="10%"><?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_START_DATE', 'start_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="10%"><?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_END_DATE', 'end_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="15%"><?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_GOAL_AMOUNT', 'goal_amount', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="15%"><?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_AMOUNT_RECEIVED', 'amount_received', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="5%"><?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_DONORS', 'donor_count', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="5%"><?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_PUBLISHED', 'published', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="9%"><?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_FEATURED', 'featured', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="7%"><?php echo Text::_('COM_JGIVE_CAMPAIGN_STATUS');?></th>
				<th width="2%"><?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_ID', 'id', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
			</tr>
	</thead>
			<?php
			if (!empty($this->data))
			{
				$i = 1;
				$j = 0;
				$k = 0;

				foreach ($this->data as $camp_data)
				{
					$data = $camp_data['campaign'];
					$images = $camp_data['images'];
					$row = $data;
					$published = HTMLHelper::_('jgrid.published', $row->published, $j);

					?>
					<tr class="row<?php echo $j % 2;?>">

						<td align="center">
							<?php echo HTMLHelper::_('grid.id', $j, $row->id);?>
						</td>

						<td>
							<div>
								<a target="_blank" href="<?php echo Uri::root() . substr(Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $data->id . '&Itemid=' . $this->singleCampaignItemid), strlen(Uri::base(true)) + 1);?>" title="<?php echo Text::_('COM_JGIVE_CLICK_TO_VIEW_CAMP_TOOLTIP');?>">
									<?php echo htmlspecialchars($data->title, ENT_COMPAT, 'UTF-8'); ?>
								</a>
							</div>
							<div class="com_jgive_clear_both"></div>
						</td>
						<!-- categories -->
						<?php
						if (!$this->issite)
						{
							?>
							<td>
								<?php
								if (isset($data->internal_use))
								{
									if ($data->internal_use)
									{
										?>
										<div>
											<pre><?php echo htmlspecialchars($data->internal_use, ENT_COMPAT, 'UTF-8'); ?></pre>
										</div>
										<?php
									}
								}
								?>
							</td>
							<?php
						}
						?>
						<!--Added by Sneha-->
						<td>
							<a href="<?php echo Uri::Base() . 'index.php?option=com_jgive&view=campaign&layout=default&id=' . (int) $data->id;?>" >
								<?php echo Text::_('COM_JGIVE_EDIT_LINK'); ?>
							</a>
						</td>
						<td><?php echo HTMLHelper::_('date', $data->start_date, Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));?></td>

						<td><?php echo HTMLHelper::_('date', $data->end_date, Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'))?></td>

						<td><?php echo $this->jgiveFrontendHelper->getFormattedPrice($data->goal_amount);?></td>

						<td><?php echo $this->jgiveFrontendHelper->getFormattedPrice($data->amount_received)?></td>

						<td><?php echo (int) $data->donor_count; ?></td>

						<td>
							<?php
								echo $published;
							?>
						</td>
						<td align="center">
							<a href="javascript:void(0);" onclick=" listItemTask('cb<?php echo $k;?>','<?php echo ($campaignHelper->isFeatured($data->id)) ? 'unfeature' : 'feature';?>')">
								<img src="<?php echo Uri::root(true);?>/media/com_jgive/images/<?php echo ($campaignHelper->isFeatured($data->id )) ? 'default.png' : 'nodefault.png';?>" width="16" height="16" border="0" />
							</a>
						</td>
						<td>
							<?php
							if ($data->status == 'closed')
							{
								echo Text::_('COM_JGIVE_CAMP_CLOSED');
							}
							elseif ($data->status == 'active')
							{
								echo Text::_('COM_JGIVE_CAMP_ACTIVE');
							}
							else
							{
								echo Text::_('COM_JGIVE_CAMP_SUCCESSFUL');
							}
							?>
						</td>
						<td><?php echo (int) $data->id;?></td>
					</tr>
					<?php
					$i++;
					$j++;
					$k++;
				}
			}
			else{
				?>
				<tr>
					<td colspan="11">
						<?php echo Text::_('COM_JGIVE_NO_DATA');?>
						<!--<input type="hidden" name="defaltevent" value="<?php //echo $this->lists['filter_campaign'];?>" />-->
					</td>
				</tr>
				<?php
			}

			if (!$this->issite)
			{
				?>
				<tr>
					<?php $class_pagination = '';?>
					<td colspan="11" class="com_jgive_align_center <?php echo $class_pagination; ?> ">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				<?php
			}
			?>


		</table>

		<?php
		if ($this->issite)
		{
			?>
			<?php
				$class_pagination = '';
			?>
			<div class="<?php echo $class_pagination; ?> com_jgive_align_center">
				<?php echo $this->pagination->getListFooter(); ?>
			</div>
			<?php
		}
		?>

		<input type="hidden" name="option" value="com_jgive" />
		<input type="hidden" name="view" value="campaigns" />
		<input type="hidden" name="layout" value="all_list" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
