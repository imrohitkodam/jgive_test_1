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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>
<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">
	<div id="jgiveWrapper" class="j-main-container row">
	<div class="col-md-12">
	<div>
		<div class="alert alert-info">
			<i><?php echo Text::_('COM_JGIVE_FUND_HOLDER_DESC');?></i>
		</div>
	</div>
	<?php
		// Sorting and Searching Options
		echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>

	<?php
	if (empty($this->items))
	{
	?>
		<div>
			<div class="alert alert-no-items">
				<?php echo Text::_("COM_JGIVE_NO_MATCHING_RESULTS");?>
			</div>
		</div>
	<?php
	}
	else
	{
	?>
		<table class='table itemList'>
			<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"/>
					</th>
					<th class="center" width="5%"><?php echo Text::_('COM_JGIVE_NO'); ?></th>
					<th class="center" width="15%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_DONATION_ID', 'id', $listDirn, $listOrder);?>
					</th>
					<th class="center" width="9%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_DONORNAME', 'd.user_id', $listDirn, $listOrder);?>
					</th>
					<th class="center" width="9%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_AMOUNT', 'original_amount', $listDirn, $listOrder);?>
					</th>

					<th class="center" width="9%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_COMMISSION_AMOUNT', 'fee', $listDirn, $listOrder);?>
					</th>

					<th class="center" width="15%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_DONATION_STATUS', 'status', $listDirn, $listOrder);?>
					</th>

					<th class="center" width="10%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_FUND_HOLDER', 'fund_holder', $listDirn, $listOrder);?>
					</th>

					<th class="center" width="10%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_GATEWAY', 'processor', $listDirn, $listOrder);?>
					</th>

					<th class="center" width="9%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_DONATION_DATE', 'cdate', $listDirn, $listOrder);?>
					</th>
					<!--<th class="center" width="9%">
						<?php //echo HTMLHelper::_('grid.sort', 'COM_JGIVE_PAYMENT_RECEIVED_DATE', 'payment_received_date', $listDirn, $listOrder);?>
					</th>-->

					<!--<th class="center" width="15%">
						<?php //echo Text::_('COM_JGIVE_DONATION_DONOR_TYPE'); ?>
					</th>-->
					<!--<th class="center" width="15%">
						<?php //echo Text::_('COM_JGIVE_DONATION_DONOR_COMMENT'); ?>
					</th>-->
				</tr>
			</thead>
			<tbody>
				<?php
				$id = 1;
				$filid = 0;

				foreach ($this->items as $i => $item)
				{
				?>
					<tr class="<?php echo $i % 2;?>">
						<td align="center">
							<?php echo HTMLHelper::_('grid.id', $id, $item->id);?>
						</td>
						<td class="center"><?php echo $id++;?></td>
						<td class="center break-word">
						<?php $link = Route::_('index.php?option=com_jgive&view=donation&layout=default&donationid=' . $item->id);?>
							<a href="<?php echo $link;?>">
								<?php echo $item->order_id?$item->order_id:$item->id;?>
							</a>
						</td>
						<td class="center break-word">
							<?php
							/* Check donor as Annonymous donation set as "Yes" */
							if ($item->annonymous_donation == 1)
							{
								echo Text::_('COM_JGIVE_NO_USER');
							}
							/* Check did donation from organization */
							elseif ($item->donor_type == 'org' && !empty($item->org_name))
							{
								echo htmlspecialchars($item->org_name,  ENT_COMPAT, 'UTF-8');
							}
							/* Check donor as Annonymous donation set as "No" */
							else
							{
								$usertable = User::getTable();
								$user_id   = intval($item->donor_id);
								$creator   = '';

								if ($usertable->load($user_id))
								{
									$creator = Factory::getUser($item->donor_id);
								}

								/* Checking donor as Guest donor and Annonymous donation set as "No" */
								if (!$creator)
								{
									echo htmlspecialchars($item->first_name,  ENT_COMPAT, 'UTF-8') . ' ' . htmlspecialchars($item->last_name,  ENT_COMPAT, 'UTF-8');
								}
								/* Checking donor as Register user and Annonymous donation set as "No" */
								else
								{
									$userLink = Route::_('index.php?option=com_users&task=user.edit&id=' . $item->donor_id);
									?>
										<a href="<?php echo $userLink;?>">
											<?php echo htmlspecialchars($creator->name,  ENT_COMPAT, 'UTF-8');?>
										</a>
									<?php
								}
							}
							?>
						</td>
						<td class="center">
							<?php echo $this->jgiveFrontendHelper->getFormattedPrice($item->original_amount);?>
						</td>
						<td class="center">
							<?php echo $this->jgiveFrontendHelper->getFormattedPrice($item->fee);?>
						</td>
						<td class="center">
							<?php
								echo HTMLHelper::_(
									'select.genericlist',
									$this->pstatus,
									'pstatus' . $filid,
									'class="input-medium pad_status form-select-sm" size="1"
									onChange="jgiveCommon.selectstatusorder(' . $item->id . ',this);"',
									"value", "text", $item->status
								);
							$filid++;
							?>
						</td>
						<td class="center">
						<?php
							echo htmlspecialchars($item->fund_holder?Text::_('COM_JGIVE_PROMOTOR'):Text::_('COM_JGIVE_SITE_ADMIN'), ENT_COMPAT, 'UTF-8');
						?>
						</td>
						<td class="center">
							<?php
								// Gettng plugin name which is set in plugin option
								$plugin = PluginHelper::getPlugin('payment', $item->processor);
								$plgname = (json_decode($plugin->params)->plugin_name) ? (json_decode($plugin->params)->plugin_name) : $item->processor;
								echo htmlspecialchars($plgname, ENT_COMPAT, 'UTF-8');
							?>
						</td>
						<td class="center">
							<?php echo HTMLHelper::_('date', $item->cdate, $this->params->get('date_format', 'j  M  Y')); ?>
						</td>
						<!--<td class="center">
							<?php //echo ($item->payment_received_date == 0) ? '-' : HTMLHelper::_('date', $item->payment_received_date, $this->params->get('date_format', 'j  M  Y')); ?>
						</td>-->

						<!--<td class="center">
							<?php //echo ($item->donor_type == 'org')? Text::_('COM_JGIVE_DONATION_ORGANIZATION'): Text::_('COM_JGIVE_DONATION_INDIVIDUAL');?>
						</td>-->
						<!--<td class="center">
							<?php
								$commentLimit = 50;
								if (strlen($item->comment) > $commentLimit)
								{
									//echo substr(strip_tags($item->comment), 0, $commentLimit);?>
									<div class="mid" style="display:none" id="HiddenDiv_<?php //echo $i ?>">
										<?php //echo substr(strip_tags($item->comment), $commentLimit, strlen($item->comment));?>
									</div>
									<a href="javascript:void(0);" class="manage-comment-more_<?php //echo $i ?>" onclick="com_jgive.UI.Donations.showHide('HiddenDiv_<?php //echo $i ?>')">
										<?php //echo Text::_('COM_JGIVE_DONATION_READ_MORE');?>
									</a>
									<a href="javascript:void(0);" class="manage-comment-less_<?php //echo $i ?>" style="display:none"onclick="com_jgive.UI.Donations.showHide('HiddenDiv_<?php //echo $i ?>')">
										<?php //echo Text::_('COM_JGIVE_DONATION_READ_LESS');?>
									</a>
								<?php
								}
								else
								{
									//echo $item->comment;
								}
							?>
						</td>-->
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>
	<?php
	}
	?>
	<input type="hidden" name="option" value="com_jgive" />
	<input type="hidden" id='hidid' name="id" value="" />
	<input type="hidden" id='hidstat' name="status" value="" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="view" value="donations" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_type'];?>" />
	<input type="hidden" name="notify_chk" value="1"/>
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
