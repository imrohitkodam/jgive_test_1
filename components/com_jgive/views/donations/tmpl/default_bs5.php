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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
$itemId              = (isset($this->itemId))?$this->itemId:0;
$jgiveFrontendHelper = new jgiveFrontendHelper;

// Jomsocial toolbar
if (isset($this->jomsocialToolbarHtml))
{
	echo $this->jomsocialToolbarHtml;
}
?>
<script>
	var tjListFilters = [];

	jQuery(document).ready(function(){
		jQuery("#limit").attr('onchange', 'jgiveCommon.filters.submitFilters(\'adminForm\')');
		jQuery("#payment_status").attr('onchange', 'jgiveCommon.filters.submitFilters(\'adminForm\')');

		<?php
		foreach ($this->availableFilters as $availableFilter)
		{
			?>
			tjListFilters.push('<?php echo $availableFilter; ?>');
			<?php
		}
		?>
	});

	var jgive_baseurl = "<?php echo Uri::root(); ?>";
</script>
<div id="jgive_my_donations" class="row">
	<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?> col-xs-12">
		<div class="page-header">
			<h1><?php echo Text::_('COM_JGIVE_MY_DONATIONS');?></h1>
		</div>
		<form action="" name="adminForm" id="adminForm" class="form-validate" method="POST">
			<div class="col-xs-12 af-mb-5">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));?>
			</div>
			<!-- show message if no items found -->
			<?php
			if (empty($this->myDonations))
			{
			?>
				<div class="alert alert-warning">
					<?php echo Text::_('COM_JGIVE_NO_DATA_FOUND');?>
				</div>
			<?php
			}
			else
			{
			?>
				<div class="no-more-tables">
					<table class="table table-striped table-bordered table-hover table-hover table-light border mt-4">
						<thead class="table-primary text-light ">
							<tr>
								<th class="center nowrap"
									<?php echo Text::_('COM_JGIVE_NO');?>
								</th>

								<th class="center nowrap">
									<a
									href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'id', $this->sortDirection);?>"
									class="hasPopover"
									data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>"
									data-placement="top"
									data-original-title="<?php echo Text::_('COM_JGIVE_DONATION_ID');?>"
									>
									<?php echo Text::_('COM_JGIVE_DONATION_ID');?>
									</a>
								</th>

								<th class="center nowrap">
									<a
									href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'processor', $this->sortDirection);?>"
									class="hasPopover"
									data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>"
									data-placement="top"
									data-original-title="<?php echo Text::_('COM_JGIVE_GATEWAY');?>"
									>
									<?php echo Text::_('COM_JGIVE_GATEWAY');?>
									</a>
								</th>

								<th class="center nowrap">
									<a
									href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'status', $this->sortDirection);?>"
									class="hasPopover text-dark text-decoration-none fw-bold"
									data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>"
									data-placement="top"
									data-original-title="<?php echo Text::_('COM_JGIVE_DONATION_STATUS');?>"
									>
									<?php echo Text::_('COM_JGIVE_DONATION_STATUS');?>
									</a>
								</th>

								<th class="center nowrap">
									<a
									href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'cdate', $this->sortDirection);?>"
									class="hasPopover text-dark text-decoration-none fw-bold"
									data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>"
									data-placement="top"
									data-original-title="<?php echo Text::_('COM_JGIVE_DONATION_DATE');?>"
									>
									<?php echo Text::_('COM_JGIVE_DONATION_DATE');?>
									</a>
								</th>
								<th class="center nowrap">
									<a
									href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'payment_received_date', $this->sortDirection);?>"
									class="hasPopover"
									data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>"
									data-placement="top"
									data-original-title="<?php echo Text::_('COM_JGIVE_PAYMENT_RECEIVED_DATE');?>"
									>
									<?php echo Text::_('COM_JGIVE_PAYMENT_RECEIVED_DATE');?>
									</a>
								</th>

								<th class="center nowrap">
									<a
									href="<?php echo $jgiveFrontendHelper->getTableSortUrl($this->currentUrl, 'original_amount', $this->sortDirection);?>"
									class="hasPopover"
									data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');?>"
									data-placement="top"
									data-original-title="<?php echo Text::_('COM_JGIVE_AMOUNT');?>"
									>
									<?php echo Text::_('COM_JGIVE_AMOUNT');?>
									</a>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$id = 1;
							$totalPaid = 0;

							foreach ($this->myDonations as $donation)
							{
								$totalPaid = $totalPaid + $donation->original_amount;
							?>
								<tr>
									<td class="center nowrap" data-title="<?php echo Text::_("COM_JGIVE_NO"); ?>">
										<?php echo $id++;?>
									</td>
									<td class="center nowrap small" data-title="<?php echo Text::_("COM_JGIVE_DONATION_ID"); ?>">
										<a href="<?php echo Route::_('index.php?option=com_jgive&view=donation&layout=default&donationid=' . $donation->id . '&Itemid=' . $itemId);?>">
											<?php echo htmlspecialchars($donation->order_id?$donation->order_id:$donation->id, ENT_COMPAT, 'UTF-8');?>
										</a>
									</td>
									<td class="center nowrap small" data-title="<?php echo Text::_("COM_JGIVE_GATEWAY"); ?>">
										<?php
										$plugin = PluginHelper::getPlugin('payment', $donation->processor);
										$plgname = (json_decode($plugin->params)->plugin_name) ? (json_decode($plugin->params)->plugin_name) : $donation->processor;
										echo htmlspecialchars($plgname, ENT_COMPAT, 'UTF-8');
										?>
									</td>
									<td class="center nowrap small" data-title="<?php echo Text::_("COM_JGIVE_DONATION_STATUS"); ?>">
										<?php
										$statusArray = JGive::utilities()->getOrderStatusText($donation->status);
										?>
										<span class="small center badge badge-<?php echo $statusArray['statusClass'];?> text-dark">
											<?php echo htmlspecialchars($statusArray['statusText'], ENT_COMPAT, 'UTF-8');?>
										</span>
										<?php
										if ($donation->status == 'P' || $donation->status == 'E')
										{
										?>
											<a
												class="btn btn-primary btn-xs btn-sm"
												href="<?php echo Route::_('index.php?option=com_jgive&view=donation&donationid=' .
												$donation->id . '&Itemid=' . $itemId);?>">
												<small>
													<?php echo (($donation->ctype == 'donation') ? Text::_('COM_JGIVE_RETRY_DONATION') : Text::_('COM_JGIVE_RETRY_INVESTMENT'));?>
												</small>
											</a>
										<?php
										}?>
									</td>
									<td class="center nowrap small" data-title="<?php echo Text::_("COM_JGIVE_DONATION_DATE"); ?>">
										<?php echo HTMLHelper::_('date', $donation->cdate, $this->params->get('date_format', 'j  M  Y')); ?>
									</td>
									<td class="center nowrap small" data-title="<?php echo Text::_("COM_JGIVE_DONATION_DATE"); ?>">
										<?php

										if ($donation->payment_received_date == 0)
										{
											echo "-";
										}
										else
										{
											echo HTMLHelper::_('date', $donation->payment_received_date, $this->params->get('date_format', 'j  M  Y'));
										}?>
									</td>
									<td class="center nowrap small" data-title="<?php echo Text::_("COM_JGIVE_AMOUNT"); ?>">
										<?php echo $this->jgiveFrontendHelper->getFormattedPrice($donation->original_amount);
										?>
									</td>
								</tr>
							<?php
							}
							?>
							<?php
							if (JVERSION > '4.0.0')
							{
								?>
								<tr>
									<td colspan="6" class="text-end" data-title="<?php echo Text::_("COM_JGIVE_TOTAL_DONATION"); ?>">
										<strong><?php echo Text::_('COM_JGIVE_TOTAL_DONATION');?></strong>
									</td>
									<td>
										<?php echo $this->jgiveFrontendHelper->getFormattedPrice($totalPaid);?>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				</div>

				<?php
				if (JVERSION < '4.0.0')
				{
					?>
					<div class="col-xs-12">
						<span class="pull-right">
							<strong><?php echo Text::_('COM_JGIVE_TOTAL_DONATION');?></strong>
							<?php echo $this->jgiveFrontendHelper->getFormattedPrice($totalPaid);?>
						</span>
					</div>
					<?php
				}
				?>
				<hr class="hr hr-condensed"/>
			<?php
			}
			?>
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="float-end me-2">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
			</div>

			<input type="hidden" name="option" value="com_jgive" />
			<input type="hidden" name="view" value="donations" />
			<input type="hidden" name="layout" value="default" />
			<input type="hidden" name="task" id="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>
