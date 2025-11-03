<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

$document = Factory::getDocument();
HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.min.css');
?>

<div id="jgiveWrapper" class="tjBs3 print-receipt">
	<div class="row">
		<div class="col-sm-12 fs-15">
			<div class="panel panel-default">
				<div class="panel-body">
					<div id="printReceipt">
						<div class="receipt-title af-mb-20">
							<h2 class="af-text-center af-mt-0 af-font-600">
								<?php echo ucfirst($this->donationDetails['campaign']->type) . ' ' . Text::_('COM_JGIVE_DONATION_DETAIL_RECEIPT'); ?>
							</h2>
						</div>
						<div class="row">
							<div class="col-sm-3 print-col-3">
								<!--Campaign Img-->
								<?php 
								$campaignMainImagePath = Uri::root() . 'media/com_jgive/images/default_campaign.png';

								if (!empty($this->donationDetails['campaign']->image))
								{
									$campaignMainImagePath = $this->donationDetails['campaign']->image->media_m;
								}?>

								<img src='<?php echo $campaignMainImagePath;?>'alt="<?php echo Text::_("COM_JGIVE_PROMOTOR_AVATAR")?>" class="img-90"/>
								<p class="print-text-left af-font-600 af-pt-10"><?php echo htmlspecialchars($this->donationDetails['campaign']->title, ENT_COMPAT, 'UTF-8');?></p>
							</div>
							<div class="col-sm-9 print-col-9">
								<div class="row">
									<div class="col-sm-6 print-col-6">
											<label class="af-font-600">
												<?php echo Text::_('COM_JGIVE_DATE') . ': ' . HTMLHelper::_('date', $this->donationDetails['payment']->cdate, $this->params->get('date_format', 'j  M  Y'));?>
											</label>
									</div>
									<div class="col-sm-6 print-col-6">
											<label class="af-font-600">
													<?php
													if (!$this->donationDetails['payment']->order_id)
													{
														$this->donationDetails['payment']->order_id = $this->donationDetails['payment']->id;
													}
													echo (($this->donationDetails['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONATION_ID') : Text::_('COM_JGIVE_INVESTMENT_ID')) . ': ' . htmlspecialchars($this->donationDetails['payment']->order_id, ENT_COMPAT, 'UTF-8');
											?>
										</label>
									</div>
									<div class="col-sm-6 print-col-6">
										<p>
											<?php
											if ($this->donationDetails['donor']->first_name ||  $this->donationDetails['donor']->last_name)
											{
												?>
												<label class="af-font-600">
												<?php
													echo (($this->donationDetails['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONATION_DETAIL_DONATED_BY') : Text::_('COM_JGIVE_DONATION_DETAIL_INVESTEDTED_BY')) . ': ';
												?>
												</label>
												<?php
												$donorName = htmlspecialchars($this->donationDetails['donor']->first_name, ENT_COMPAT, 'UTF-8') . ' ' .
														htmlspecialchars($this->donationDetails['donor']->last_name, ENT_COMPAT, 'UTF-8');
												echo ucwords($donorName);
											}?>

											<?php
											if ($this->donationDetails['donor']->address || $this->donationDetails['donor']->address2)
											{
											?>
												<p>
													<?php echo htmlspecialchars($this->donationDetails['donor']->address, ENT_COMPAT, 'UTF-8') . ",<br/>" . htmlspecialchars($this->donationDetails['donor']->address2, ENT_COMPAT, 'UTF-8');?>
												</p>
											<?php
											}?>

											<?php
											if ($this->donationDetails['donor']->country_name)
											{
												if ($this->params->get('show_selected_fields_on_donation') == 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('country', $this->params->get('donationfield'))))
												{
													?>
													<?php echo htmlspecialchars($this->donationDetails['donor']->country_name, ENT_COMPAT, 'UTF-8') . ",";
												}
											}
											?>

											<?php
											if ($this->donationDetails['donor']->state_name)
											{
												if ($this->params->get('show_selected_fields_on_donation') == 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('state', $this->params->get('donationfield'))))
												{
													?>
													<?php echo htmlspecialchars($this->donationDetails['donor']->state_name, ENT_COMPAT, 'UTF-8') . ",";
												}
											}
											?>	

											<?php
											if ($this->donationDetails['donor']->city_name)
											{
												if ($this->params->get('show_selected_fields_on_donation') == 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('city', $this->params->get('donationfield'))))
												{
													?>
													<?php echo htmlspecialchars($this->donationDetails['donor']->city_name, ENT_COMPAT, 'UTF-8') . "<br/>";
												}
											}
											?>

											<?php
											if ($this->donationDetails['donor']->zip)
											{
												if ($this->params->get('show_selected_fields_on_donation') == 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('zip', $this->params->get('donationfield'))))
												{?>
													<?php echo htmlspecialchars($this->donationDetails['donor']->zip, ENT_COMPAT, 'UTF-8');
												}
											}?>
										</p>
									</div>

									<div class="col-sm-6 print-col-6">
										<p>
											<?php 
											$donationsParams = json_decode($this->donationDetails['payment']->params);
											$platormFee = "";

											if ($donationsParams->fee_mode == 'exclusive')
											{
												if ($donationsParams->exclusive_fee_optional == '1' && $this->donationDetails['payment']->fee > 0 && $donationsParams->paid_platform_fee == true)
												{
													$platormFee = Text::_('COM_JGIVE_DONATIONS_INCLUDING_PLATFORM_FEE') . $this->jgiveFrontendHelper->getFormattedPrice($this->donationDetails['payment']->fee);
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
											<?php 
											echo $this->jgiveFrontendHelper->getFormattedPrice($this->donationDetails['campaign']->donation_amount);

											if (!empty($platormFee))
											{
												echo ' ( ' . $platormFee . ' )';
											}
											?>
										</p>
									</div>

									<div class="col-sm-6 print-col-6">
										<p>
											<?php
											if (!empty($this->donationDetails['donor']->taxnumber))
											{
												?>
													<p>
														<label class="af-font-600"><?php echo Text::_('COM_JGIVE_PAYMENTFORM_TAXNUMBER') . ': '?></label>
														<?php echo htmlspecialchars($this->donationDetails['donor']->taxnumber, ENT_COMPAT, 'UTF-8');?>
													</p>
												<?php
											}
											?>
										</p>
									</div>

									<?php
										if (!empty($this->donationDetails['donor']->pannumber))
										{
										?>
											<div class="col-sm-12">
												<p>
													<label class="af-font-600"><?php echo Text::_('COM_JGIVE_ENTER_PAN'); ?></label>
													<?php echo htmlspecialchars($this->donationDetails['donor']->pannumber, ENT_COMPAT, 'UTF-8'); ?>
												</p>
											</div>
									<?php } ?>

									<div class="col-sm-6 print-col-6">
										<p><?php
											// Gettng plugin name which is set in plugin option
											$plugin = PluginHelper::getPlugin('payment', $this->donationDetails['payment']->processor);
											$plgname = (json_decode($plugin->params)->plugin_name) ? (json_decode($plugin->params)->plugin_name) : $this->donationDetails['payment']->processor;
											?>
											<label class="af-font-600"><?php echo Text::_('COM_JGIVE_PAYMENT_METHOD') . ': ';?></label>
											<?php echo htmlspecialchars($plgname, ENT_COMPAT, 'UTF-8');?>
										</p>
									</div>

									<div class="col-sm-6 print-col-6">
										<p>
										<?php
											$statusArray = JGive::utilities()->getOrderStatusText($this->donationDetails['payment']->status);
											?>
											<label class="af-font-600"><?php echo Text::_('COM_JGIVE_PAYMENT_STATUS') . ': '?></label>
											<?php echo $statusArray['statusText'];
										?>
										</p>
									</div>

									<?php
									if (!empty($this->donationDetails['donor']->email))
									{
										?>
										<div class="col-sm-12 print-col-12">
										<p>
											<label class="af-font-600"><?php echo Text::_('COM_JGIVE_EMAIL') . ': '?></label>
											<?php echo htmlspecialchars($this->donationDetails['donor']->email, ENT_COMPAT, 'UTF-8');?>
										</p>
										</div>
										<?php
									}
									?>
									<div class="af-hidden-print row">
										<div class="col-sm-12 print-col-12">
											<h4 class="af-font-600 af-mt-10 text-gray"><?php echo Text::_('COM_JGIVE_ADDITIONAL_DETAILS');?></h4>
										</div>
											<?php
											if ($this->donationDetails['payment']->giveback_id)
											{
											?>
												<div class="col-sm-6">
												<p>
													<label class="af-font-600">
														<?php echo Text::_('COM_JGIVE_ORDER_GIVEBACK_DESC') . ': '?>
													</label>

													<?php echo htmlspecialchars($this->donationDetails['payment']->giveback_desc, ENT_COMPAT, 'UTF-8')? htmlspecialchars($this->donationDetails['payment']->giveback_desc, ENT_COMPAT, 'UTF-8'): '-';
													?>
												</p>
												</div>
											<?php
											}?>
											<?php
											if (!empty($this->donationDetails['payment']->transaction_id))
											{
											?>
											<div class="col-sm-6 print-col-6">
											<p>
												<label class="af-font-600"><?php echo Text::_('COM_JGIVE_TRANSACTION_ID') . ': '?></label>
												<?php echo htmlspecialchars($this->donationDetails['payment']->transaction_id, ENT_COMPAT, 'UTF-8');?>
											</p>
											</div>
											<?php
											}?>
											<?php
											if ($this->params->get('show_selected_fields_on_donation') == 0 || ($this->params->get('show_selected_fields_on_donation') == 1 && !empty($this->params->get('donationfield')) && !in_array('donation_anonym', $this->params->get('donationfield'))))
											{
												if ($this->donationDetails['payment']->annonymous_donation == 1)
												{
												?>
												<div class="col-sm-6 print-col-6">
												<p>
													<label class="af-font-600"><?php echo (($this->donationDetails['campaign']->type == 'donation') ? Text::_('COM_JGIVE_ANNONYMOUS_DONATION') : Text::_('COM_JGIVE_ANNONYMOUS_INVESTMENT')) . ': '?></label>
													<?php echo ((empty($this->donationDetails['payment']->annonymous_donation)) ? Text::_('JNO') : Text::_('JYES'));?>
												</p>
												</div>
												<?php
												}
											}?>
										<div class="col-sm-12 print-col-12">
											<p>
												<label class="af-font-600"><?php echo (($this->donationDetails['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONATATION_TYPE') : Text::_('COM_JGIVE_INVESTMENT_TYPE')) . ': ' ?></label>
												<?php echo (($this->donationDetails['payment']->is_recurring) ? JTEXT::_("COM_JGIVE_RECURRING") : JTEXT::_("COM_JGIVE_ONE_TIME"));?>
											</p>
										</div>
										<?php
											if ($this->donationDetails['payment']->is_recurring)
											{
											?>
												<div class="col-sm-6 print-col-6">
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
								<!--Print div hide close -->
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
	var jgive_baseurl = "<?php echo Uri::root(); ?>";
</script>
