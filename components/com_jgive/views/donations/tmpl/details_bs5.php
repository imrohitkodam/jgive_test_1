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

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('script', 'media/system/js/core.js');

$document            = Factory::getDocument();
$cdata               = $this->donationDetails;
$status              = $this->retryPayment->status;
$msg                 = $this->retryPayment->msg;
$retryCondition_show = (isset($this->retryPayment)) ? $this->retryPayment : 1;
$this->params        = ComponentHelper::getParams('com_jgive');
$jgiveFrontendHelper = new jgiveFrontendHelper;
$donationsHelper     = new DonationsHelper;

// End Addded by Sneha
$donations_site  = (isset($this->donations_site)) ? $this->donations_site : 0;
$donations_email = (isset($this->donations_email)) ? $this->donations_email : 0;

// Showing Retry Donation Button is is not set
$retryPayment_show = (isset($this->retryPayment_show)) ? $this->retryPayment_show : 1;

// Url for retry donation
$url = Uri::root() . "index.php?option=com_jgive&tmpl=component&task=donations.retryPayment&order=" . $cdata['payment']->id . "&gateway_name=";

// Load Payment gateway form html
$ajax =
<<<EOT
techjoomla.jQuery(document).ready(function()
{
	techjoomla.jQuery("input[name='gateways']").change(function()
	{
		var url1 = '{$url}'+techjoomla.jQuery("input[name='gateways']:checked").val();
		techjoomla.jQuery('#html-container').empty().html('Loading...');
		techjoomla.jQuery.ajax({
								url: url1,
								type: 'GET',
								dataType: 'json',
								success: function(response)
								{
									techjoomla.jQuery('#html-container').removeClass('ajax-loading').html( response );
								}
								});
	});
});
EOT;

$document->addScriptDeclaration($ajax);
?>
<script type="text/javascript">

	// Hide/Show Payment gateways option
	function jgive_showpaymentgetways()
	{
		var status = "<?php echo $status;?>";
		var msg = "<?php echo $msg;?>";

		if (document.getElementById('gatewaysContent').style.display=='none')
		{
			if(status == '1')
			{
				alert(msg);
			}
			else if(status == '0')
			{
				document.getElementById('gatewaysContent').style.display='block';
			}
		}

		return false;
	}
</script>

<div class="page-header">
	<h2>
		<?php
		if ($donations_site)
		{
			echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONATION_DETAILS') : Text::_('COM_JGIVE_INVESTMENT_DETAILS'));
		}
		?>
	</h2>
</div>

<div class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?> container-fluid">
	<div class="row">
		<?php
		if ($donations_email)
		{
		?>
			<h4 style="background-color: #cccccc" >
				<?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONATION_DETAILS_INFO') : Text::_('COM_JGIVE_INVESTMENT_DETAILS_INFO'));
			?></h4>
		<?php
		}

		if ($donations_site)
		{
		?>
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" >
				<h3><?php echo Text::_('COM_JGIVE_PAYMENT_DETAILS_SHORT');?></h3>
		<?php
		}
		else
		{
		?>
			<fieldset>
				<legend><?php echo Text::_('COM_JGIVE_PAYMENT_DETAILS_SHORT');?></legend>
		<?php
		}
		?>
			<table class="table table-condensed adminlist table-striped table-bordered">
				<tr>
					<td>
						<?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONATION_ID') : Text::_('COM_JGIVE_INVESTMENT_ID'));?>
					</td>
					<td>
						<?php
							if (!$cdata['payment']->order_id)
							{
								$cdata['payment']->order_id = $cdata['payment']->id;
							}

							echo htmlspecialchars($cdata['payment']->order_id, ENT_COMPAT, 'UTF-8');
						?>
					</td>
				</tr>
				<?php
				if ($cdata['payment']->giveback_id && $cdata['payment']->giveback_title)
				{
				?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_ORDER_GIVEBACK_TITLE');?></td>
						<td><?php echo htmlspecialchars($cdata['payment']->giveback_title, ENT_COMPAT, 'UTF-8');?></td>
					</tr>
				<?php
				}

				if ($cdata['payment']->giveback_id && $cdata['payment']->giveback_desc)
				{
				?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_ORDER_GIVEBACK_DESC');?></td>
						<td><?php echo htmlspecialchars($cdata['payment']->giveback_desc, ENT_COMPAT, 'UTF-8');?></td>
					</tr>
				<?php
				}?>

				<tr>
					<td><?php echo Text::_('COM_JGIVE_DATE');?></td>
					<td><?php echo HTMLHelper::_('date', $cdata['payment']->cdate, Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));?></td>
				</tr>

				<?php
				if ($cdata['payment']->is_recurring)
				{
				?>
					<tr>
						<td><?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONATATION_TYPE') : Text::_('COM_JGIVE_INVESTMENT_TYPE'));?></td>
						<td><?php echo Text::_("COM_JGIVE_RECURRING");?></td>
					</tr>
				<?php
				}
				else
				{
				?>
					<tr>
						<td><?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONATATION_TYPE') : Text::_('COM_JGIVE_INVESTMENT_TYPE'));?></td>
						<td><?php echo Text::_("COM_JGIVE_ONE_TIME");?></td>
					</tr>
				<?php
				}

				if ($cdata['payment']->is_recurring):
				?>
					<tr>
						<td><?php echo Text::_("COM_JGIVE_TERMS");?></td>
						<?php
							$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($cdata['payment']->original_amount);
							$recurring_terms = str_replace("{AMOUNT}", $diplay_amount_with_format, Text::_("COM_JGIVE_RECURRING_DONATION_TERMS"));
							$recurring_terms = str_replace("{RECURRING_FREQ}", strtolower($cdata['payment']->recurring_frequency), $recurring_terms);
							$recurring_terms = str_replace("{RECURRING_TIMES}", $cdata['payment']->recurring_count, $recurring_terms);
						?>
						<td><?php echo $recurring_terms;?></td>
					</tr>
				<?php
				endif;?>

				<tr>
					<td><?php echo Text::_('COM_JGIVE_AMOUNT');?></td>
					<td>
						<?php
							$commissionParams = json_decode($cdata['payment']->params);

							// Inclusive
							$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($cdata['payment']->amount);
							$donationsParams = json_decode($this->donationDetails['payment']->params);
							$platormFee = "";

							if (!empty($commissionParams ))
							{
								// Exclusive commission
								if ($commissionParams->fee_mode == 'exclusive')
								{
									// Optional Exclusive commission
									if ($commissionParams->exclusive_fee_optional == '1' && $this->donationDetails['payment']->fee > 0)
									{
										// Optional Exclusive commission + donor has not paid platform fee
										$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($cdata['payment']->amount);

										if ($commissionParams->paid_platform_fee == true)
										{
											// Optional Exclusive commission + donor has paid platform fee
											$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($cdata['payment']->original_amount);
											$platormFee = Text::_('COM_JGIVE_DONATIONS_INCLUDING_PLATFORM_FEE') . $jgiveFrontendHelper->getFormattedPrice($cdata['payment']->fee);
										}
									}
									// Compulsory Exclusive commission
									elseif ($this->donationDetails['payment']->fee > 0)
									{
										$platormFee = Text::_('COM_JGIVE_DONATIONS_INCLUDING_PLATFORM_FEE') . $jgiveFrontendHelper->getFormattedPrice($cdata['payment']->fee);
										$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($cdata['payment']->original_amount);
									}
								}
								elseif ($donationsParams->fee_mode == 'inclusive' && $this->donationDetails['payment']->fee > 0)
								{
									$platormFee = Text::_('COM_JGIVE_DONATIONS_INCLUDING_PLATFORM_FEE');
								}
							}

							echo $diplay_amount_with_format;

							if (!empty($platormFee))
							{
								echo ' ( ' . $platormFee . ' )';
							}
						?>
					</td>
				</tr>
				<?php
				if ($this->params->get('vat_for_donor'))
				{
				?>
				<tr>
					<td><?php echo Text::_('COM_JGIVE_VAT_NUMBER');?></td>
					<td><?php echo htmlspecialchars($cdata['payment']->vat_number, ENT_COMPAT, 'UTF-8');?></td>
				</tr>
				<?php
				}?>
				<tr>
					<td><?php echo Text::_('COM_JGIVE_IP_ADDRESS');?></td>
					<td><?php echo htmlspecialchars($cdata['payment']->ip_address, ENT_COMPAT, 'UTF-8');?></td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JGIVE_PAYMENT_METHOD');?></td>
					<td><?php
						// Gettng plugin name which is set in plugin option
						$plugin = PluginHelper::getPlugin('payment', $cdata['payment']->processor);
						$plgname = (json_decode($plugin->params)->plugin_name) ? (json_decode($plugin->params)->plugin_name) : $cdata['payment']->processor;

						echo htmlspecialchars($plgname, ENT_COMPAT, 'UTF-8');?>
					</td>
				</tr>
				<?php
				if (!empty($cdata['payment']->transaction_id))
				{
				?>
				<tr>
					<td><?php echo Text::_('COM_JGIVE_TRANSACTION_ID');?></td>
					<td><?php echo htmlspecialchars($cdata['payment']->transaction_id, ENT_COMPAT, 'UTF-8');?></td>
				</tr>
				<?php
				}

				$annonymous_donation = '';

				switch ($cdata['payment']->annonymous_donation)
				{
					case 0 :
						$annonymous_donation = Text::_('COM_JGIVE_NO');
					break;
					case 1 :
						$annonymous_donation = Text::_('COM_JGIVE_YES');
					break;
				}

				if ($this->params->get('show_selected_fields_on_donation') == 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !in_array('donation_anonym', $this->params->get('donationfield', array(), 'ARRAY'))))
				{
					?>
					<tr>
						<td><?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_ANNONYMOUS_DONATION') : Text::_('COM_JGIVE_ANNONYMOUS_INVESTMENT'));
						?></td>
						<td><?php echo $annonymous_donation;?></td>
					</tr>
					<?php
				}
				?>
			</table>
		<?php
		if ($donations_site)
		{
		?>
			</div>
		<?php
		}
		else
		{
		?>
			</fieldset>
		<?php
		}
		?>

		<?php
		$whichever = '';
		$info_text = '';

		switch ($cdata['payment']->status)
		{
			case 'C' :
				$whichever = Text::_('COM_JGIVE_CONFIRMED');
			break;

			case 'RF' :
				$whichever = Text::_('COM_JGIVE_REFUND');
			break;

			case 'P' :
				if($donations_site)
				{
					$whichever       = Text::_('COM_JGIVE_PENDING');

					if (!$donations_email)
					{
						$info_text = Text::_("COM_JGIVE_ORDER_STATUS_NOTE_MSG");
					}
				}
			break;

			case 'E' :
				$whichever = Text::_('COM_JGIVE_CANCELED');
			break;

			case 'D' :
				$whichever = Text::_('COM_JGIVE_DENIED');
			break;
		}

		if ($donations_site)
		{
		?>
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
				<h3><?php echo Text::_('COM_JGIVE_PAYMENT_STATUS');?></h3>
				<!-- To do not show note while checkout, checked layout -->
				<?php
				if (($info_text) && isset($this->layout))
				{
					?>
					<div class="alert alert-info">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
							<?php echo $info_text; ?>
					</div>
				<?php
				}
		}
		else
		{
		?>
			<fieldset>
				<legend><?php echo Text::_('COM_JGIVE_PAYMENT_STATUS'); ?></legend>
		<?php
		}
		?>
				<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">
					<table class="table table-condensed adminlist table-striped table-bordered" >
						<tr>
							<td><?php echo Text::_('COM_JGIVE_PAYMENT_STATUS');?></td>
							<td>
							<?php
								if ( ($cdata['payment']->status == 'P' || $cdata['payment']->status == 'C' || $cdata['payment']->status == 'E') && !($donations_site))
								{
									echo HTMLHelper::_('select.genericlist', $this->pstatus, "pstatus",
									'class="pad_status form-select" size="1" onChange="jgiveCommon.selectstatusorder(' .
									$cdata['payment']->id . ',this);"', "value", "text",
									$cdata['payment']->status);
								}
								else
								{
									echo $whichever;
								}
								?>
							</td>
						</tr>
						<?php
						if ($cdata['payment']->status == 'P' || $cdata['payment']->status == 'E')
						{
							if ($retryPayment_show == 0)
							{
							?>
								<tr>
									<td colspan="2">
										<button class="btn btn-primary btn-xs" name="show_getways" id="show_getways"
											onclick="return jgive_showpaymentgetways();">
											<?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_RETRY_DONATION') : Text::_('COM_JGIVE_RETRY_INVESTMENT'));?>
										</button>
										<div id="gatewaysContent" style="display:none;">
											<div class="form-group">
												<label for="gatewaysContent" class="col-lg-8 col-md-8 col-sm-8 col-xs-12 control-label">
													<?php echo Text::_('COM_PAY_METHODS');?>
												</label>
												<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 pull-right">
													<?php
													if (empty($this->gateWays))
													{
														echo Text::_('NO_PAYMENT_GATEWAY');
													}
													else
													{
														if ($cdata['payment']->is_recurring == 1)
														{
															$gateways = $this->recure_gateway;
															$plg_html = '';

																foreach ($gateways as $gateway)
																{
																	$plg_html .=
																	'<div class="radio">
																		<label>
																			<input type="radio" name="gateways" id="' . $gateway['id'] . '" value="' . $gateway['id'] . '" aria-label="..." autocomplete="off">
																				' . $gateway['name'] . '
																		  </label>
																		</div>';
																}

																echo $plg_html;
														}
														else
														{
															$gateways = $this->gateWays;
															$plg_html = '';

															foreach ($gateways as $gateway)
															{
																$plg_html .=
																'<div class="radio">
																	<label>
																		<input type="radio" name="gateways" id="' . $gateway->id . '" value="' . $gateway->id . '" aria-label="..." autocomplete="off">
																			' . $gateway->name . '
																	  </label>
																	</div>';
															}

															echo $plg_html;
														}
													}?>
												</div>
											</div>
										</div>
									</td>
								</tr>
							<?php
							}
						}

						if (!$donations_site)
						{
						?>
							<tr>
								<td><?php echo Text::_('COM_JGIVE_NOTIFY');?></td>
								<td>
									<input type="checkbox" id = "notify_chk"  name = "notify_chk" value="1" size= "10" checked />
								</td>
							</tr>
							<tr>
								<td><?php echo Text::_('COM_JGIVE_COMMENT');?></td>
								<td><textarea id="" name="comment" rows="3" size="28" value=""></textarea></td>
							</tr>
						<?php
						}
						?>
					</table>
					<?php
					if (!isset($this->mailContent))
					{
					?>
					<input type="hidden" name="option" value="com_jgive" />
					<input type="hidden" id='hidid' name="id" value="" />
					<input type="hidden" id='hidstat' name="status" value="" />
					<input type="hidden" name="task" id="task" value="" />
					<input type="hidden" name="view" value="donations" />
					<?php echo HTMLHelper::_('form.token'); ?>
					<?php
					}
					?>
				</form>
		<?php
		if ($donations_site)
		{
		?>
			</div>
		<?php
		}
		else
		{
		?>
			</fieldset>
		<?php
		}
		?>
		<div style="clear:both;"></div>
		<?php
		if ($donations_site)
		{
		?>
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
				<h3>
					<?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONOR_DETAILS_SHORT') : Text::_('COM_JGIVE_INVESTOR_DETAILS_SHORT'));
					?>
				</h3>
		<?php
		}
		else
		{
		?>
		<fieldset>
			<legend>
				<?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONOR_DETAILS_SHORT') : Text::_('COM_JGIVE_INVESTOR_DETAILS_SHORT')); ?>
			</legend>
		<?php
		}
		?>
			<table class="table table-condensed adminlist table-striped table-bordered" >
				<?php if ( $cdata['donor']->first_name ||  $cdata['donor']->last_name):
				?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_NAME');?></td>
						<td><?php echo htmlspecialchars($cdata['donor']->first_name, ENT_COMPAT, 'UTF-8') . ' ' .
							htmlspecialchars($cdata['donor']->last_name, ENT_COMPAT, 'UTF-8');
						?></td>
					</tr>
				<?php endif; ?>

				<?php if ( $cdata['donor']->address ||  $cdata['donor']->address2):
				?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_ADDRESS');?></td>
						<td>
							<?php
								echo htmlspecialchars($cdata['donor']->address, ENT_COMPAT, 'UTF-8');
								echo "<br/>";
								echo htmlspecialchars($cdata['donor']->address2, ENT_COMPAT, 'UTF-8');
							?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ( $cdata['donor']->zip):
					if ($this->params->get('show_selected_fields_on_donation')== 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !in_array('zip', $this->params->get('donationfield', array(), 'ARRAY'))))
					{
				?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_ZIP');?></td>
						<td><?php echo htmlspecialchars($cdata['donor']->zip, ENT_COMPAT, 'UTF-8');?></td>
					</tr>
				<?php }
				endif; ?>


				<?php if ( $cdata['donor']->country_name):
					if ($this->params->get('show_selected_fields_on_donation')== 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !in_array('country', $this->params->get('donationfield', array(), 'ARRAY'))))
					{
				?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_COUNTRY');?></td>
						<td><?php echo htmlspecialchars($cdata['donor']->country_name, ENT_COMPAT, 'UTF-8');?></td>
					</tr>
				<?php }
				endif; ?>

				<?php if ( $cdata['donor']->state_name):
					if ($this->params->get('show_selected_fields_on_donation')== 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !in_array('state', $this->params->get('donationfield', array(), 'ARRAY'))))
					{
				?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_STATE');?></td>
						<td><?php echo htmlspecialchars($cdata['donor']->state_name, ENT_COMPAT, 'UTF-8');?></td>
					</tr>
				<?php }
				endif; ?>

				<?php if ( $cdata['donor']->city_name):
					if ($this->params->get('show_selected_fields_on_donation')== 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !in_array('city', $this->params->get('donationfield', array(), 'ARRAY'))))
					{?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_CITY');?></td>
						<td><?php echo htmlspecialchars($cdata['donor']->city_name, ENT_COMPAT, 'UTF-8');?></td>
					</tr>
				<?php }
				endif;?>

				<?php if ( $cdata['donor']->phone):
					if ($this->params->get('show_selected_fields_on_donation') == 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !in_array('phone_no', $this->params->get('donationfield', array(), 'ARRAY'))))
					{
					?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_PHONE');?></td>
						<td><?php echo htmlspecialchars($cdata['donor']->phone, ENT_COMPAT, 'UTF-8');?></td>
					</tr>
					<?php
					}

					endif; ?>
					
				<?php if ( $cdata['donor']->pannumber):
					?>
					<tr>
						<td><?php echo Text::_('COM_JGIVE_PAN_FIELD')?></td>
						<td><?php 
						$key = $params->get('pan_encryption_key');
						echo $donationsHelper->decryptData(htmlspecialchars($cdata['donor']->pannumber, ENT_COMPAT, 'UTF-8'), $key );?></td>
					</tr>
				<?php
				endif;?>

				<tr>
					<td><?php echo Text::_('COM_JGIVE_EMAIL');?></td>
					<td><?php echo htmlspecialchars($cdata['donor']->email, ENT_COMPAT, 'UTF-8');?></td>
				</tr>
			</table>

	<?php
	if ($donations_site)
	{
	?>
		</div>
		<?php
	}
	else
	{
	?>
		</fieldset>
		<?php
	}

	if ($donations_site)
	{
	?>
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<h3><?php echo Text::_('COM_JGIVE_CAMPAIGN_DETAILS_SHORT');?></h3>
	<?php
	}
	else
	{
	?>
	<fieldset>
		<legend><?php echo Text::_('COM_JGIVE_CAMPAIGN_DETAILS_SHORT'); ?></legend>
	<?php
	}?>
		<table class="table table-condensed adminlist table-striped table-bordered" width="50">
			<tr>
				<td><?php echo Text::_('COM_JGIVE_TITLE');?></td>
				<td><?php echo htmlspecialchars($cdata['campaign']->title, ENT_COMPAT, 'UTF-8');?></td>
			</tr>

			<tr>
				<td><?php echo Text::_('COM_JGIVE_GOAL_AMOUNT');?></td>
				<td><?php
					$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($cdata['campaign']->goal_amount);
					echo $diplay_amount_with_format;
					?>
				</td>
			</tr>

			<tr>
				<td><?php echo Text::_('COM_JGIVE_AMOUNT_RECEIVED');?></td>
				<td><?php
					$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($cdata['campaign']->amount_received);
					echo $diplay_amount_with_format;
					?>
				</td>
			</tr>

			<tr>
				<td><?php echo Text::_('COM_JGIVE_REMAINING_AMOUNT');?></td>
				<td>
					<?php
					if ($cdata['campaign']->amount_received > $cdata['campaign']->goal_amount)
					{
						echo Text::_('COM_JGIVE_GOAL_ACHIEVED');
					}
					else
					{
						$diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($cdata['campaign']->remaining_amount);
						echo $diplay_amount_with_format;
					}
					?>
				</td>
			</tr>
		</table>
		<?php
		if ($cdata['payment']->status == 'P' || $cdata['payment']->status == 'E')
		{
			if ($retryPayment_show == 0)
			{
				?>
				<button
					class="pull-right"
					onclick="jgive.donations.cancelRetryDonation()"
					type="button" class="btn btn-mini col-xs-2"
					id="cancelRetryDonation"
					data-original-title="Clear"
					title="<?php echo Text::_('COM_JGIVE_CLEAR_TOOLTIP');?>">
					<?php echo Text::_('COM_JGIVE_CANCEL')?>
				</button>
		<?php
			}
		}?>

	<?php
	if ($donations_site)
	{
	?>
		</div>
		<?php
	}
	else
	{
	?>
		</fieldset>
		<?php
	} ?>
	</div>
</div>

<div style="clear:both;"></div>
<!--PAYMENT HIDDEN DATA WILL COME HERE -->
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"id="html-container" name=""></div>
<script type="text/javascript">
	var jgive_baseurl = "<?php echo Uri::root(); ?>";
</script>
