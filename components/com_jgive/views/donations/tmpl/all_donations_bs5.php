<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$utilitiesObj = JGive::utilities();
?>

<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div id="jgive_all_donations" class="row">
		<div class="col-xs-12">
			<div class="container-fluid">
				<?php
				if ($this->params->get('show_page_heading', 1))
				{
					?>
					<div class="page-header">
						<h1><?php echo $this->escape($this->params->get('page_heading'));?></h1>
					</div>
					<?php
				}
				?>
			</div>
			<form action="<?php echo Route::_('index.php?option=com_jgive&view=donations&layout=all_donations&Itemid='. $this->myDonationItemId); ?>" 
			method="post" name="adminForm" id="adminForm">
				<div class="row">
					<?php
					if ($this->isroot == false)
					{
					?>
						<div class="col-xs-12 alert alert-info center">
							<?php echo Text::_('COM_JGIVE_COMMISSION_PERCENT') . ' - ' . $this->commissionFee;?>
							<?php echo Text::_('COM_JGIVE_COMMISSION_FIXED') . ' - ' . $this->fixedCommissionFee;?>
						</div>
					<?php
					}
					?>
					<div class="col-xs-12 af-mb-5">
						<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));?>
					</div>
					<?php 
					if (empty($this->myDonations))
					{
						?>
						<div class="clearfix">&nbsp;</div>
						<div class="alert alert-info" role="alert">
							<?php echo Text::_('COM_JGIVE_NO_DATA_FOUND');?>
						</div>
						<?php
					}
					else
					{
						$totalDonationAmount = $totalFee = $totalPaid = 0;
						?>
							<div class="col-xs-12 table-responsive">
								<div class="row no-more-tables">
									<table class="table table-striped table-bordered table-hover mt-3" id="allDonationsList">
										<thead class="text-break table-primary text-light">
											<tr>
												<th>
													<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_DONATIONS_ORDERID', 'o.id', $listDirn, $listOrder);?>
												</th>
												<th>
													<?php echo Text::_('COM_JGIVE_DONATIONS_DONOR_NAME');?>
												</th>
												<th>
													<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_DONATIONS_DONOR_TYPE', 'd.donor_type', $listDirn, $listOrder);?>
												</th>
												<th>
													<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_DONATIONS_STATUS', 'o.status', $listDirn, $listOrder);?>
												</th>
												<th>
													<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_DONATIONS_PROCESSOR', 'o.processor', $listDirn, $listOrder);?>
												</th>
												<th>
													<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_DONATIONS_CDATE', 'o.cdate', $listDirn, $listOrder);?>
												</th>
												<th>
													<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_DONATIONS_AMOUNT', 'o.original_amount', $listDirn, $listOrder);?>
												</th>
												<th>
													<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_DONATIONS_FEE', 'o.fee', $listDirn, $listOrder);?>
												</th>
												<th>
													<?php echo Text::_('COM_JGIVE_DONATIONS_PLATFORM_FEE_EXCLUDE_AMOUNT');?>
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
											foreach ($this->myDonations as $i => $item)
											{
												if ($item->status == COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED)
												{
													$totalDonationAmount = $totalDonationAmount + $item->original_amount;
													$totalFee            = $totalFee + $item->fee;
													$totalPaid           = $totalPaid + ($item->original_amount - $item->fee);
												}
											?>
												<tr>
													<td data-title="<?php echo Text::_('COM_JGIVE_DONATIONS_ORDERID');?>">
														<a href="<?php echo Route::_('index.php?option=com_jgive&view=donation&layout=default&Itemid='. $this->myDonationItemId .'&donationid=' . $item->id);?>">
															<?php echo $this->escape($item->order_id);?>
														</a>
													</td>
													<td data-title="<?php echo Text::_('COM_JGIVE_DONATIONS_DONOR_NAME');?>">
														<?php 
														if ($item->annonymous_donation)
														{
															echo Text::_("COM_JGIVE_DONOR_ANNONYMOUS_NAME");
														}
														else
														{
															echo ($item->donor_type == 'org') ? $this->escape($item->org_name): $this->escape($item->first_name) . ' ' . $this->escape($item->last_name);
														}?>
													</td>
													<td data-title="<?php echo Text::_('COM_JGIVE_DONATIONS_DONOR_TYPE');?>">
														<?php echo ($item->donor_type == 'org')? Text::_('COM_JGIVE_DONATION_ORGANIZATION'): Text::_('COM_JGIVE_DONATION_INDIVIDUAL');?>
													</td>
													<td data-title="<?php echo Text::_('COM_JGIVE_DONATIONS_STATUS');?>">
														<?php
														$validStatus = $this->orderModel->getValidOrderStatus($item->status, $this->paymentStatus);

														echo HTMLHelper::_('select.genericlist', $validStatus, "pstatus" . $i, 'onChange="jgiveCommon.selectstatusorder(' . $item->id . ',this);"', "value", " text", $item->status);
														?>
													</td>
													<td data-title="<?php echo Text::_('COM_JGIVE_DONATIONS_PROCESSOR');?>">
														<?php echo $this->escape($utilitiesObj->getPaymentGatewayName($item->processor));?>
													</td>
													<td data-title="<?php echo Text::_('COM_JGIVE_DONATIONS_CDATE');?>">
														<?php echo $this->escape($utilitiesObj->getFormattedDate($item->cdate));?>
													</td>
													<td data-title="<?php echo Text::_('COM_JGIVE_DONATIONS_AMOUNT');?>">
														<?php echo $this->escape($utilitiesObj->getFormattedPrice($item->original_amount));?>
													</td>
													<td data-title="<?php echo Text::_('COM_JGIVE_DONATIONS_FEE');?>">
														<?php echo $this->escape($utilitiesObj->getFormattedPrice($item->fee));?>
													</td>
													<td data-title="<?php echo Text::_('COM_JGIVE_DONATIONS_PLATFORM_FEE_EXCLUDE_AMOUNT');?>">
														<?php echo $this->escape($utilitiesObj->getFormattedPrice($item->original_amount - $item->fee));?>
													</td>
												</tr>
											<?php
											}?>
										</tbody>
									</table>
									<div class="col-xs-12">
										<strong class="float-end me-2"><?php echo Text::_('COM_JGIVE_DONATIONS_AMOUNT');?>
											<?php echo $utilitiesObj->getFormattedPrice($totalDonationAmount);?>
										</strong>
									</div>
									<div class="col-xs-12">
										<strong class="float-end me-2"><?php echo Text::_('COM_JGIVE_DONATIONS_FEE');?>
											<?php echo $utilitiesObj->getFormattedPrice($totalFee);?>
										</strong>
									</div>
									<div class="col-xs-12">
										<strong class="float-end me-2"><?php echo Text::_('COM_JGIVE_DONATIONS_PLATFORM_FEE_EXCLUDE_AMOUNT');?>
											<?php echo $utilitiesObj->getFormattedPrice($totalPaid);?>
										</strong>
									</div>
									<div class="col-xs-12 no-more-tables">
										<div class="float-end me-2">
											<?php echo $this->pagination->getListFooter(); ?>
										</div>
									</div>
								</div>
							</div>
						<?php
					}
					?>
					<input type="hidden" name="task" value=""/>
					<input type="hidden" name="boxchecked" value="0"/>
					<input type="hidden" name="id" id="hidid" value="" />
					<input type="hidden" name="status" id="hidstat" value="" />
					<?php echo HTMLHelper::_('form.token');?>
				</div>
			</form>
		</div>
	</div>
</div>
