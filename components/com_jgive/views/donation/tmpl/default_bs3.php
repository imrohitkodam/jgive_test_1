<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla      <extensions@techjoomla.com>
 * @copyright   Copyright   (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license GNU     General     Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

?>
<!-- New code- -->
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<h4 class="overflow-hidden">
		<input
			type="button"
			class="btn btn-default no-print pull-right"
			onclick="com_jgive.UI.Common.printReceipt()"
			value="<?php echo Text::_('COM_JGIVE_DONATION_PRINT');?>">
	</h4>

	<div class="row" id="printReceipt">
		<div class="col-sm-12">
			<div class="page-header">
				<h1 class="af-text-center af-font-600">
					<?php echo ucfirst($this->donationDetails['campaign']->type) . ' ' . Text::_('COM_JGIVE_DONATION_DETAIL_RECEIPT'); ?>
				</h1>
			</div>
		</div>
		<div class="col-sm-3">
			<?php 
			$campaignMainImagePath = Uri::root() . 'media/com_jgive/images/default_campaign.png';

			if (!empty($this->donationDetails['campaign']->image))
			{
				$campaignMainImagePath = $this->donationDetails['campaign']->image->media_l;
			}
			?>

			<img src='<?php echo $campaignMainImagePath;?>'alt="<?php echo Text::_("COM_JGIVE_PROMOTOR_AVATAR")?>" class="img-90"/>
			<p class="af-text-center print-text-left af-font-600 af-pt-10">
				<?php echo htmlspecialchars($this->donationDetails['campaign']->title, ENT_COMPAT, 'UTF-8');?>
			</p>
		</div>
		<div class="col-sm-9">
			<div class="col-sm-6">
				<h5 class="af-font-600 af-mt-0 my-2">
					<?php echo Text::_('COM_JGIVE_DATE') . ': ' .
					HTMLHelper::_('date', $this->donationDetails['payment']->cdate, $this->params->get('date_format', 'j  M  Y'));?>
				</h5>
			</div>
			<div class="col-sm-6">
				<h5 class="af-font-600 af-mt-0 my-2">
					<?php
					if (!$this->donationDetails['payment']->order_id)
					{
						$this->donationDetails['payment']->order_id = $this->donationDetails['payment']->id;
					}

					echo (($this->campaignTypeFlag) ? Text::_('COM_JGIVE_DONATION_ID') : Text::_('COM_JGIVE_INVESTMENT_ID')) . ': ' .
					htmlspecialchars($this->donationDetails['payment']->order_id, ENT_COMPAT, 'UTF-8');?>
				</h5>
			</div>
			<div class="col-sm-6">
				<p>
					<label class="af-font-600">
						<?php echo (($this->campaignTypeFlag) ? Text::_('COM_JGIVE_DONATION_DETAIL_DONATED_BY') : Text::_('COM_JGIVE_DONATION_DETAIL_INVESTEDTED_BY')) .
						': ';?>
					</label>
					<?php
					if ($this->donationDetails['donor']->first_name ||  $this->donationDetails['donor']->last_name)
					{
						$donorName = htmlspecialchars($this->donationDetails['donor']->first_name, ENT_COMPAT, 'UTF-8') . ' ' .
						htmlspecialchars($this->donationDetails['donor']->last_name, ENT_COMPAT, 'UTF-8')."<br/>";
						echo ucwords($donorName);
					}?>

					<?php
						if ($this->donationDetails['donor']->address || $this->donationDetails['donor']->address2)
						{
						?>
						<?php echo nl2br(htmlspecialchars($this->donationDetails['donor']->address, ENT_COMPAT, 'UTF-8') . ' ' . htmlspecialchars($this->donationDetails['donor']->address2, ENT_COMPAT, 'UTF-8')."<br/>");
						?>
					
					<?php
						}
						if ($this->donationDetails['donor']->country_name)
						{
							if ($this->params->get('show_selected_fields_on_donation')== 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('country', $this->params->get('donationfield'))))
							{
					?>
					
					<?php echo htmlspecialchars($this->donationDetails['donor']->country_name, ENT_COMPAT, 'UTF-8') . ",";?>
						
					<?php
				}
			}

			if ($this->donationDetails['donor']->state_name)
			{
				if ($this->params->get('show_selected_fields_on_donation')== 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('state', $this->params->get('donationfield'))))
				{
					?>
				<?php echo htmlspecialchars($this->donationDetails['donor']->state_name, ENT_COMPAT, 'UTF-8') . ",";?>
				<?php
				}
			}

			if ($this->donationDetails['donor']->city_name)
			{
				if ($this->params->get('show_selected_fields_on_donation')== 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('city', $this->params->get('donationfield'))))
				{
					?>
				<?php echo htmlspecialchars($this->donationDetails['donor']->city_name, ENT_COMPAT, 'UTF-8') . "<br/>";?>
					<?php
				}
			}

			if ($this->donationDetails['donor']->zip)
			{
				if ($this->params->get('show_selected_fields_on_donation')== 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('zip', $this->params->get('donationfield'))))
				{
					?>
							<?php echo htmlspecialchars($this->donationDetails['donor']->zip, ENT_COMPAT, 'UTF-8');?>
					<?php
				}
			}

			if (!empty($this->donationDetails['donor']->taxnumber))
			{
			?>
				<div class="col-sm-12">
					<p>
						<label class="af-font-600"><?php echo Text::_('COM_JGIVE_PAYMENTFORM_TAXNUMBER') . ': '?></label>
						<?php echo htmlspecialchars($this->donationDetails['donor']->taxnumber, ENT_COMPAT, 'UTF-8');?>
					</p>
				</div>
			<?php
			}
			?>
				</p>
			</div>
			<div class="col-sm-6">
				<p>
					<?php 
					$donationsParams = json_decode($this->donationDetails['payment']->params);
					$platormFee = "";

					if ($donationsParams->fee_mode == 'exclusive')
					{
						if ($donationsParams->exclusive_fee_optional == '1' && $this->donationDetails['payment']->fee > 0)
						{
							if ($donationsParams->paid_platform_fee == true)
							{
								$platormFee = Text::_('COM_JGIVE_DONATIONS_INCLUDING_PLATFORM_FEE') . $this->jgiveFrontendHelper->getFormattedPrice($this->donationDetails['payment']->fee);;
							}
						}
						elseif ($this->donationDetails['payment']->fee > 0)
						{
							$platormFee = Text::_('COM_JGIVE_DONATIONS_INCLUDING_PLATFORM_FEE') . $this->jgiveFrontendHelper->getFormattedPrice($this->donationDetails['payment']->fee);
						}
					}
					elseif ($donationsParams->fee_mode == 'inclusive' && $this->donationDetails['payment']->fee > 0)
					{
						$platormFee = Text::_('COM_JGIVE_DONATIONS_INCLUDING_PLATFORM_FEE');
					}?>
					<label class="af-font-600"><?php echo Text::_('COM_JGIVE_DONATION_DETAIL_AMOUNT_RECEIVED') . ': ';?></label>
					<?php echo $this->jgiveFrontendHelper->getFormattedPrice($this->donationDetails['payment']->original_amount);

					if (!empty($platormFee))
					{
						echo ' ( ' . $platormFee . ' )';
					}
					?>
				</p>
			</div>
			
			<div class="col-sm-6">
				<p><?php
					// Gettng plugin name which is set in plugin option
					$plugin = PluginHelper::getPlugin('payment', $this->donationDetails['payment']->processor);
					$plgname = (json_decode($plugin->params)->plugin_name) ? (json_decode($plugin->params)->plugin_name) : $this->donationDetails['payment']->processor;
					?>
					<label class="af-font-600"><?php echo Text::_('COM_JGIVE_PAYMENT_METHOD') . ': ';?></label>
					<?php echo htmlspecialchars($plgname, ENT_COMPAT, 'UTF-8');?>
				</p>
			</div>
			<div class="col-sm-6">
				<p>
					<?php $statusArray = JGive::utilities()->getOrderStatusText($this->donationDetails['payment']->status);?>
					<label class="af-font-600"><?php echo Text::_('COM_JGIVE_PAYMENT_STATUS') . ': '?></label>
					<?php echo $statusArray['statusText'];?>
				</p>
			</div>
			<div class="col-sm-12">
				<p>
					<label class="af-font-600"><?php echo Text::_('COM_JGIVE_EMAIL') . ': '?></label>
					<?php echo htmlspecialchars($this->donationDetails['donor']->email, ENT_COMPAT, 'UTF-8');?>
				</p>
				<?php
				if ($this->donationDetails['payment']->status == "P")
				{
					?>
				<div class="col-sm-6 alert alert-info no-print">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
						<?php echo Text::_("COM_JGIVE_ORDER_STATUS_NOTE_MSG"); ?>
				</div>
				<?php
				}
				?>
			</div>

			<div class="col-sm-12">
				<h4 class="af-font-600 af-mt-10 text-gray"><?php echo Text::_('COM_JGIVE_ADDITIONAL_DETAILS');?></h4>
			</div>
			<div class="col-sm-6">
				<?php
				if ($this->donationDetails['payment']->giveback_id)
				{
				?>
					<p>
						<label class="af-font-600"><?php echo Text::_('COM_JGIVE_ORDER_GIVEBACK_DESC') . ': '?></label>

						<?php echo htmlspecialchars($this->donationDetails['payment']->giveback_desc, ENT_COMPAT, 'UTF-8')? htmlspecialchars($this->donationDetails['payment']->giveback_desc, ENT_COMPAT, 'UTF-8'): '-';
						?>
					</p>

				<?php
				}?>
			</div>

			<?php
			if (!empty($this->donationDetails['payment']->transaction_id))
			{
			?>
				<div class="col-sm-6">
					<p>
						<label class="af-font-600"><?php echo Text::_('COM_JGIVE_TRANSACTION_ID') . ': '?></label>
						<?php echo htmlspecialchars($this->donationDetails['payment']->transaction_id, ENT_COMPAT, 'UTF-8');?>
					</p>
				</div>
			<?php
			}

			if ($this->params->get('show_selected_fields_on_donation')== 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('donation_anonym', $this->params->get('donationfield'))))
			{
				if($this->donationDetails['payment']->annonymous_donation==1)
				{
				?>
				<div class="col-sm-12">
					<p>
						<label class="af-font-600"><?php echo (($this->campaignTypeFlag) ? Text::_('COM_JGIVE_ANNONYMOUS_DONATION') : Text::_('COM_JGIVE_ANNONYMOUS_INVESTMENT')) . ': '?></label>
						<?php echo ((empty($this->donationDetails['payment']->annonymous_donation)) ? Text::_('JNO') : Text::_('JYES'));?>
					</p>
				</div>
				<?php
				}
			}?>

			<div class="col-sm-12">
				<p>
					<label class="af-font-600"><?php echo (($this->campaignTypeFlag) ? Text::_('COM_JGIVE_DONATATION_TYPE') : Text::_('COM_JGIVE_INVESTMENT_TYPE')) . ': ' ?></label>
					<?php echo (($this->donationDetails['payment']->is_recurring) ? Text::_("COM_JGIVE_RECURRING") : Text::_("COM_JGIVE_ONE_TIME"));?>
				</p>
			</div>
			<?php
			if ($this->donationDetails['payment']->is_recurring)
			{
			?>
				<div class="col-sm-6">
					<?php
						$diplay_amount_with_format = $this->jgiveFrontendHelper->getFormattedPrice($this->donationDetails['payment']->original_amount);
						$recurring_terms = str_replace("{AMOUNT}", $diplay_amount_with_format, Text::_("COM_JGIVE_RECURRING_DONATION_TERMS"));
						$recurring_terms = str_replace("{RECURRING_FREQ}", strtolower($this->donationDetails['payment']->recurring_frequency), $recurring_terms);
						$recurring_terms = str_replace("{RECURRING_TIMES}", $this->donationDetails['payment']->recurring_count, $recurring_terms);
					?>
					<p>
						<label class="af-font-600"><?php echo Text::_("COM_JGIVE_TERMS") . ': '?></label>
						<?php echo $recurring_terms;?>
					</p>
				</div>
				<?php
			}
			?>
		</div>

		<div class="col-sm-12 no-print">
			<?php
			if ($this->donationDetails['payment']->status == 'P' || $this->donationDetails['payment']->status == 'E')
			{
				$link = 'index.php?option=com_jgive&view=campaigns&layout=all&Itemid=' . $this->allCampaignItemId;
				$link = Uri::root() . substr(Route::_($link), strlen(Uri::base(true)) + 1);
				?>
				<button class="btn btn-default pull-right no-print" name="" id="" onclick="location.href='<?php echo $link;?>';">
					<?php echo Text::_('COM_JGIVE_CANCEL');?>
				</button>
				<?php
			}?>
			<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">
				<?php
				if ($this->donationDetails['payment']->status == 'P' || $this->donationDetails['payment']->status == 'E')
				{
					if (in_array($this->donationDetails['payment']->processor, ['byorder', 'bycheck']))
					{
						?>
							<div class="alert alert-info" role="alert">
								<?php echo Text::_('COM_JGIVE_ORDER_PENDING_WITH_OFFLINE_WARNING_MESSAGE'); ?>
							</div>
						<?php
					}?>

					<button
						class="btn btn-primary"
						name="show_getways"
						id="show_getways"
						onclick="return jgive.donation.showPaymentGetways('<?php echo $this->retryPayment->status; ?>', '<?php echo $this->retryPayment->msg; ?>');">
						<?php echo (($this->campaignTypeFlag) ? Text::_('COM_JGIVE_RETRY_DONATION') : Text::_('COM_JGIVE_RETRY_INVESTMENT'));?>
					</button>
					<div id="gatewaysContent" style="display:none;">
						<div class="form-group">
							<label for="gatewaysContent" class="col-sm-3 control-label"><?php echo Text::_('COM_PAY_METHODS');?></label>
							<div class="col-sm-9">
								<?php
								if (empty($this->gateWays))
								{
									echo Text::_('NO_PAYMENT_GATEWAY');
								}
								else
								{
									$gateways = ($this->donationDetails['payment']->is_recurring == 1) ? $this->recure_gateway : $this->gateWays;
									$plg_html = '';

									foreach ($gateways as $gateway)
									{
										$plg_html .=
										'<div class="radio">
											<label>
												<input
												type="radio"
												name="gateways"
												id="' . $gateway->id . '"
												value="' . $gateway->id . '"
												onchange="jgive.donation.retryPayment(' . $this->donationDetails['payment']->id . ')"
												aria-label="..." autocomplete="off">
													' . $gateway->name . '
											</label>
										</div>';
									}

									echo $plg_html;
								}
								?>
							</div>
						</div>
					</div>
				<?php
				}?>
				<input type="hidden" name="option" value="com_jgive" />
				<input type="hidden" id='hidid' name="id" value="" />
				<input type="hidden" id='hidstat' name="status" value="" />
				<input type="hidden" name="task" id="task" value="" />
				<input type="hidden" name="view" value="donations" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
			<div class="col-xs-12" id="html-container" name=""></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	var jgive_baseurl = "<?php echo Uri::root(); ?>";
</script>
