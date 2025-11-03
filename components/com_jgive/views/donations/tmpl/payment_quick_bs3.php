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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('formbehavior.chosen', 'select');

// Fetched data
$cdata     = $this->cdata;
$pagetitle = ($this->campaignFlag) ? Text::sprintf('COM_JGIVE_CHECKOUT_TITLE', $this->cdata['campaign']->title) : Text::sprintf('COM_JGIVE_CHECKOUT_TITLE_INVESTMENT', $this->cdata['campaign']->title);

$document = Factory::getDocument();
$document->setTitle($pagetitle);

$jgiveFrontendHelper = new jgiveFrontendHelper;
$show_selected_fields_on_donate = $this->params->get('show_selected_fields_on_donation');
$donationfield = array();
$show_field = 0;
$donation_anonym = 0;
?>
<script>
	const jgiveBaseUrl = '<?php echo Uri::root();?>';
</script>
<?php
if ($show_selected_fields_on_donate)
{
	$donationfield = $this->params->get('donationfield');

	if (isset($donationfield))
	{
		if (in_array("donation_anonym", $donationfield))
		{
			$donation_anonym = 1;
		}
	}
}
else
{
	$show_field = 1;
}
?>

<div id="jgiveWrapper">
	<div class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
		<div id="jgive_content" class="container-fluid">
			<header>
				<div class="page-header center">
					<h2 itemprop="name">
						<?php echo ($this->campaignFlag) ? Text::_("COM_JGIVE_DONATE_TO"): Text::_("COM_JGIVE_INVEST_TO"); ?>
						&nbsp;<?php echo $this->cdata['campaign']->title; ?>
					</h2>
				</div>
			</header>

			<form action="" method="post" id="payment_quick" name="payment_quick" class="form-validate form-vertical" enctype="multipart/form-data">
				<!-- Edit order button -->
				<button
					id="jgive_edit_button"
					type="button"
					class="btn btn-defaultbtn-medium btn-primary jgive_edit_button pull-right"
					onclick="jgive.donations.editDetails()">
					<span ><i class="fa fa-pencil-square-o"></i><?php echo Text::_("COM_JGIVE_EDIT");?></span>
				</button>

				<div class="clearfix">&nbsp;</div>

				<!-- Edit amount & other details fields -->
				<div id="jgive_form_field">
					<div class="row" >
						<div id="jgive_disabled_fields" >
							<aside class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
								<div class="form-group">
									<label class="control-label" title="<?php echo Text::_('COM_JGIVE_ENTER_AMOUNT_TP');?>">
										<strong>
											<?php echo Text::_("COM_JGIVE_ENTER_AMOUNT");?><span class="star">&nbsp;*</span>
										</strong>
									</label>
									<div class="controls">
										<div class="input-group">
											<span class="input-group-addon" id="basic-addon1">
												<?php echo $this->currencySymbol;?>
											</span>
											<?php
												// Minimum donation amount
												$predefinedAmount = $minAmount = 0;
												$campaignId = $this->input->get('cid', 0, 'INT');

												if (!empty( $cdata['campaign']->minimum_amount))
												{
													$predefinedAmount = $minAmount = $cdata['campaign']->minimum_amount;
												}

												// Get selected giveback amount
												if (!empty($this->giveback_id))
												{
													foreach ($this->campaignGivebacks as $giveback)
													{
														if ($this->giveback_id == $giveback->id)
														{
															$predefinedAmount = $giveback->amount;
															break;
														}
													}
												}
											?>
											<input type="number"
												class="jgive-amount-field quick-donation required form-control"
												id="donation_amount"
												name="donation_amount"
												onchange="jgiveCommon.validateAmountAsPerCurrency(this);"
												onkeyup="com_jgive.UI.Donation.updatePlatformFee(this, <?php echo $campaignId;?>);"
												title="<?php echo Text::_("COM_JGIVE_ENTER_AMOUNT_TP");?>"
												min="<?php echo $minAmount; ?>"
												value="<?php echo floatval($predefinedAmount); ?>"
												class="required"
												required />
										</div>
									</div>
								</div>
								<?php
								$feeMode = $this->params->get('fee_mode', 'inclusive', 'string');
								$sendPaymentsToOwner = $this->params->get('send_payments_to_owner');

								if (!$sendPaymentsToOwner && $feeMode == 'exclusive')
								{
									$exclusiveFeeOptional = $this->params->get('exclusive_fee_optional', '0', 'string');
									$predefinedPlatformFee = 0;
								?>
									<div>
									<?php
									if ((int) $exclusiveFeeOptional === 1)
									{
									?>
										<input 
												type="checkbox" 
												id="exclusive_platform_fee" 
												name="exclusive_platform_fee" 
												checked="checked" 
												onclick="com_jgive.UI.Donation.changePlatformFee();"/>
									<?php
									}?>
										<?php echo Text::_("COM_JGIVE_PLATFORM_FEE_PART1")?>
										<?php echo $this->currencySymbol?>
										<span id="platform_fee"><?php echo $predefinedPlatformFee?></span>
										<?php echo Text::_("COM_JGIVE_PLATFORM_FEE_PART2")?>
									</div>
									<?php
								}
								?>
								<div class="form-group">
									<?php
									if ($cdata['campaign']->type == "donation")
									{
										if ($show_field == 1 || $donation_anonym == 0 )
										{
										?>
										<label class="control-label" title="<?php echo Text::_('COM_JGIVE_DONATE_ANONYMOUSLY_TOOLTIP');?>">
											<strong>
												<?php echo Text::_("COM_JGIVE_DONATE_ANONYMOUSLY");?>
											</strong>
										</label>
										<div class="controls radio">
											<label class="radio inline">
												<input type="radio" name="annonymousDonation" id="annonymousDonation1" value="1" >
													<?php echo Text::_('COM_JGIVE_YES');?>
											</label>
											<label class="radio inline">
												<input type="radio" name="annonymousDonation" id="annonymousDonation2" value="0" checked>
													<?php echo Text::_('COM_JGIVE_NO');?>
											</label>
										</div>
										<?php
										}
									}
									?>
								</div>
								<?php
								if ($this->params->get('terms_condition') && $this->params->get('payment_terms_article'))
								{
									$link = Route::_(
									Uri::root() . "index.php?option=com_content&view=article&id=" .
									$this->params->get('payment_terms_article') . "&tmpl=component"
									);
									?>
									<div class="form-group">
										<label class="">
											<input type="checkbox" id="terms_condition" name="terms_condition"/>
											<?php  echo Text::_('COM_JGIVE_DONATION_ACCEPT_USER_TERMS_CONDITIONS_FIRST'); ?>
											<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link;?>"
											class="modal" />
												<?php echo Text::_('COM_JGIVE_DONATION_PRIVACY_POLICY');?>
											</a>
											<?php echo Text::_('COM_JGIVE_DONATION_ACCEPT_USER_TERMS_CONDITIONS_LAST'); ?>
										</label>
									</div>
									<?php
								}
								?>
							</aside>

							<aside class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
								<div class="form-group">
									<div class="controls">
										<label label-default class="control-label" title="<?php echo Text::_('COM_JGIVE_DONOR_NAME_TP');?>">
											<strong><?php echo Text::_("COM_JGIVE_DONOR_NAME");?><span class="star">&nbsp;*</span></strong>
											</label>
										<input type="text"
											id="user_first_last_name"
											name="user_first_last_name"
											title="<?php echo Text::_("COM_JGIVE_FIRST_LAST_NAME");?>"
											placeholder="<?php echo Text::_("COM_JGIVE_FIRST_LAST_NAME");?>"
											value="<?php echo $this->session->get('JGIVE_user_first_last_name');?>"
											class="required"
											required />
									</div>
									&nbsp;
									<div class="controls">
										<label label-default class="control-label" title="<?php echo Text::_('COM_JGIVE_DONOR_EMAIL_TP');?>">
											<strong><?php echo Text::_("COM_JGIVE_DONOR_EMAIL");?><span class="star">&nbsp;*</span></strong>
											</label>
										<input type="email"
											id="paypal_email"
											name="paypal_email"
											title="<?php echo Text::_("COM_JGIVE_EMAIL");?>"
											placeholder="<?php echo Text::_("COM_JGIVE_EMAIL");?>"
											value="<?php echo $this->session->get('JGIVE_paypal_email');?>"
											class="required"
											required />
									</div>
								</div>
							</aside>
							<?php
							if (count($this->campaignGivebacks))
							{
								if ($this->givebacksAvailable)
								{
								?>
									<div id="jgive_givebacks" class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<h4><?php echo Text::_("COM_JGIVE_SELECT_YOUR_GIVEBACK");?></h4>

										<div class="radio">
											<label>
												<input alt="0.00"
													type="radio"
													name="givebacks"
													value="0"
													title="<?php echo Text::_("COM_JGIVE_NO_GIVEBACK_TITLE"); ?>"
													onclick="jgive.donations.populateSelected_GivebackAmount(<?php echo $minAmount;?>, this)" />
													<?php echo Text::_("COM_JGIVE_THANKS_MSG"); ?>
											</label>
										</div>
										<?php
										foreach ($this->campaignGivebacks as $giveback)
										{
											if ($giveback->sold_giveback < $giveback->total_quantity)
											{
												$checked = "";
												$class = "";

												// Highlight selected giveback
												if ($this->giveback_id == $giveback->id)
												{
													$checked = ' checked="checked" ';
													$class = " jgive_active ";
												} ?>
												<div class="radio">
													<label>
														<input alt="<?php echo $giveback->amount; ?>"
															type="radio"
															name="givebacks"
															value="<?php echo $giveback->id; ?>"
															<?php echo $checked; ?>
															onclick="jgive.donations.populateSelected_GivebackAmount(<?php echo $giveback->amount; ?>, this)"
															 />
														<strong>
															<?php
															echo (!empty($giveback->title)) ? htmlspecialchars($giveback->title, ENT_COMPAT, 'UTF-8') : $jgiveFrontendHelper->getFormattedPrice($giveback->amount);
															?>
														</strong> &nbsp;

														<?php echo !empty($giveback->description) ? htmlspecialchars($giveback->description, ENT_COMPAT, 'UTF-8') : Text::_("COM_JGIVE_NO_DESC_AVAILABLE"); ?>
													</label>
												</div>
											<?php
											}?>

										<?php
										} ?>
									</div>
								<?php
								}
							}
							?>
						</div>
					</div>
					<div class="clear-fix">&nbsp;</div>
					<hr/>
				</div>
				<footer id="jgive_footer">
					<div class="form-group">
						<?php
							$select = array();
							$gateways = array_merge($select, $this->gateways);
							$gateways = array_filter($gateways);
							$displayLabel = (count($gateways) == 1) ? "hide" : "";

							// If only one geteway then keep it as selected
							if (count($gateways) >= 1)
							{
								// Id and value is same
								$default = $gateways[0]->id;
							}
						?>
						<label label-default class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label <?php echo $displayLabel?>"
						title="<?php echo Text::_("COM_JGIVE_SELECT_PAYMENT_METHOD_TP");?>">
							<strong>
								<?php echo Text::_("COM_JGIVE_SELECT_PAYMENT_METHOD");?>
								<span class="star">&nbsp;*</span>
							</strong>
						</label>

						<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12 radio">
							<?php
							if (empty($this->gateways))
							{
								echo Text::_('COM_JGIVE_NO_PAYMENT_GATEWAY');
							}
							elseif (count($this->gateways) == 1)
							{
								?>
								<label class="radio-inline hide me-3">
									<input type="radio" class="me-2"
									name="gateways" id="jgive_<?php echo $this->gateways[0]->id; ?>" 
									value="<?php echo $this->gateways[0]->id;?>" 
									checked><?php echo $this->gateways[0]->name;?>
								</label>
							<?php
							}
							else
							{
								foreach ($this->gateways as $gateway)
								{
								?>
									<label class="radio-inline me-3">
										<input type="radio" name="gateways"
										id="jgive_<?php echo $gateway->id; ?>"
										value="<?php echo $gateway->id; ?>">
										<?php echo $gateway->name; ?>
									</label>
								<?php
								}
							}
							?>
						</div>
					</div>
					<button type="button"
						id="jgive_continue_btn"
						class="btn btn-large btn-primary  "
						onclick="jgive.donations.placeQuickPayment()"
						>
						<?php echo Text::_("COM_JGIVE_COUNTINUE_NEXT_STEP"); ?>
						<i class="fa fa-chevron-right"></i>
					</button>
					<div class="clearfix"></div>
				</footer>

				<input type="hidden" name="option" value="com_jgive" />
				<input type="hidden" name="view" value="donations" />
				<input type="hidden" name="task" value="donations.placeOrder" />
				<input type="hidden" name="cid" value="<?php echo $this->input->get('cid', '', 'INT');?>" />
				<input type="hidden" name="Itemid" value="<?php echo $this->input->get('Itemid', '', 'INT');?>"/>
				<input type="hidden" name="userid" id="userid" value="<?php echo !empty($this->user->id) ? $this->user->id : 0; ?>">
				<input type="hidden" name="order_id" id="order_id" value="0" />
				<input type="hidden" name="platform_fee" id="platform_fee_value" value="0" />
				<input type="hidden" name="account" value="guest" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
			<div id="payment_tab_table_html"></div>
		</div>
	</div>
</div>

<script>
	var jgive_baseurl = "<?php echo Uri::root(); ?>";
	var minimum_amount = "<?php echo $cdata['campaign']->minimum_amount;?>";
	minimum_amount = parseInt(minimum_amount);
	var send_payments_to_owner = "<?php echo $this->params->get('send_payments_to_owner');?>";
	var commission_fee = "<?php echo $this->params->get('commission_fee');?>";
	var fixed_commissionfee = "<?php echo $this->params->get('fixed_commissionfee');?>";
	var givebackDetails = <?php echo !empty($this->campaignGivebacks) ? json_encode($this->campaignGivebacks, 1) : 0;?>;
	var donationTermsCondition = <?php echo $this->params->get('terms_condition')?>;
	var donationTermsConditionArticle = <?php echo $this->params->get('payment_terms_article')?>;
	var cid = <?php echo $this->input->get('cid', 0, 'INT')?>;
	jgive.donations.init();
</script>
