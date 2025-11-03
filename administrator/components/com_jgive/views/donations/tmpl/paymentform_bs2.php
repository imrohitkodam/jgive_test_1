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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;


HTMLHelper::_('behavior.formvalidator');

if (JVERSION < '4.0.0')
{
	HTMLHelper::_('behavior.framework', true);
}

HTMLHelper::_('bootstrap.renderModal', 'a.modal');

$modalConfig = array('width' => '100%', 'height' => '100%', 'modalWidth' => 80, 'bodyHeight' => 70);

$modalConfig['url'] = "index.php?option=com_jgive&view=individual&layout=edit&tmpl=component";
$modalConfig['title'] = Text::_('COM_JGIVE_NEW_INDIVIDUAL');
echo HTMLHelper::_('bootstrap.renderModal', 'newIndividualDonor', $modalConfig);

$modalConfig['url'] = "index.php?option=com_jgive&view=organization&layout=edit&tmpl=component";
$modalConfig['title'] = Text::_('COM_JGIVE_NEW_ORGANIZATION');
echo HTMLHelper::_('bootstrap.renderModal', 'newIndividualOrganization', $modalConfig);

$modalConfig['url'] = "index.php?option=com_jgive&view=campaigns&layout=all_list_select&tmpl=component&field=jform_created_by";
$modalConfig['title'] = Text::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL');
echo HTMLHelper::_('bootstrap.renderModal', 'donationSelectCampaign', $modalConfig);

$pagetitle = Text::_('COM_JGIVE_DONATE_NOW');
$document = Factory::getDocument();
$document->setTitle($pagetitle);
$baseurl = Route::_(Uri::root() . 'index.php');

$isguest = $this->user->id;
$isguest = empty($isguest)? 1 : 0;
$registr_field_display = ($this->user->id)?"display:none":"display:display";

$js = "
var isgst=" . $isguest . ";
var jgive_baseurl='" . $baseurl . "';
";

// Get the data to idetify which field to show on donation view
$show_selected_fields_on_donate = $this->params->get('show_selected_fields_on_donation');
$donationfield = array();
$show_field      = 0;
$first_name      = 0;
$last_name       = 0;
$email           = 0;
$address         = 0;
$address2        = 0;
$hide_country    = 0;
$state           = 0;
$city            = 0;
$zip             = 0;
$phone_no        = 0;
$donation_type   = 0;
$donation_anonym = 0;

if ($show_selected_fields_on_donate)
{
	$donationfields = $this->params->get('donationfield');

	if (isset($donationfields))
	{
		foreach ($donationfields as $donationfield)
		{
			switch ($donationfield)
			{
				case 'first_name':
					$first_name      = 1;
				break;

				case 'last_name':
					$last_name       = 1;
				break;

				case 'email':
					$email           = 1;
				break;

				case 'address':
					$address         = 1;
				break;

				case 'address2':
					$address2        = 1;
				break;

				case 'country':
					$hide_country    = 1;
				break;

				case 'state':
					$state           = 1;
				break;

				case 'city':
					$city            = 1;
				break;

				case 'zip':
					$zip             = 1;
				break;

				case 'phone_no':
					$phone_no        = 1;
				break;

				case 'donation_type':
					$donation_type   = 1;
				break;

				case 'donation_anonym':
					$donation_anonym = 1;
				break;
			}
		}
	}
}
else
{
	$show_field = 1;
}

$document->addScriptDeclaration($js);
HTMLHelper::_('script', 'media/com_jgive/vendors/js/typeahead/typeahead.bundle.min.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/donation.js');
HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive_typeahead.css');

?>
<script type="text/javascript">
	const jgiveBaseUrl = '<?php echo Uri::base();?>';
	techjoomla.jQuery(document).ready(function()
	{
		var userid=techjoomla.jQuery('#userid').val();
		techjoomla.jQuery('#payment-info-tab').hide();
		techjoomla.jQuery('#payment_received_date').attr('disabled','disabled');
		com_jgive.UI.Donation.contact();
	});

	function jSelectUser_jform_created_by(id, title) {

			var old_id = document.getElementById("donor_id").value;
			if (old_id != id) {
				document.getElementById("donor_id").value = id;
				document.getElementById("donor_name").value = title;

			}
			SqueezeBox.close();

				var compaignuserid=document.getElementById("donor_id").value;

			techjoomla.jQuery.ajax(
			{
				url:'index.php?option=com_jgive&task=donations.loadprofiledata&compaignuserid='+compaignuserid+'&tmpl=component',
				type:'GET',
				dataType:'json',
				success:function(data)
				{
					if (data === undefined || data == null || data.length <= 0)
					{

					}
					else
					{
							jgiveAdmin.donations.fillprofiledata(data)
					}
				}
			});
		}

	function SelectCampaign(id, title, minAmount){
		var old_id = document.getElementById("campaign_id").value;
		window.minimun_amount = parseFloat(minAmount);

		if (old_id != id) {
			document.getElementById("campaign_id").value = id;
			document.getElementById("cid").value = id;
			document.getElementById("campaign_name").value = title;

			if(window.minimun_amount !== 0)
			{
				document.getElementById("donation_amount").value = parseFloat(minAmount);
				com_jgive.UI.Donation.updatePlatformFee(document.getElementById("donation_amount"),id);
			}
			if(window.minimun_amount === 0)
			{
				document.getElementById("donation_amount").value = null;
			}

		}
		parent.jQuery("#donationSelectCampaign").modal("hide");		

		// GetGiveBack Against Selected Campaign
		jgiveAdmin.donations.getGiveBackAgainstCampaign(id);
	}

	Joomla.submitbutton = function(action)
	{
		var form = document.adminForm;

		if(action=='donations.placeOrder')
		{
			var fieldstatus  = com_jgive.UI.Common.checkWhitespace();
			if (fieldstatus == false) {
				return false;
			}
			var donation_amount=document.getElementById('donation_amount').value;
			donation_amount=parseFloat(donation_amount);
			if(donation_amount <=0 && window.minimun_amount === 0)
			{
				alert("<?php echo Text::_('COM_JGIVE_MINIMUM_DONATION_AMOUNT'); ?>"+ " "+window.minimun_amount);
				return false;
			}
			if(donation_amount < window.minimun_amount)
			{
				alert("<?php echo Text::_('COM_JGIVE_ATLEAST_DONATION_AMOUNT'); ?>"+ " "+window.minimun_amount);
				return false;
			}

			/* Donation amount - currency validation*/
			let returnValue = jgiveCommon.validateAmountAsPerCurrency(jQuery('#donation_amount'));
			if (returnValue === false)
			{
				return false;
			}

			var validateflag = document.formvalidator.isValid(document.getElementById('adminForm'));
			var errorRes = jgiveAdmin.donations.validateGiveBackAmount(donation_amount);

			if(!errorRes)
			{
				return false;
			}

			if(validateflag)
			{
				techjoomla.jQuery('#payment_received_date').removeAttr("disabled");
				var paymentDate= document.getElementById('payment_received_date').value;

				var today = new Date();
				var month = ((today.getMonth()+1) < 10) ? '0'+(today.getMonth()+1) : (today.getMonth()+1);
				var day = ((today.getDate()) < 10) ? '0'+(today.getDate()) : (today.getDate());

				var currentDate = today.getFullYear()+'-'+month+'-'+day;

				var pdate = Date.parse(paymentDate);
				var cdate = Date.parse(currentDate);

				if (pdate > cdate) {
				alert ("<?php echo Text::_('COM_JGIVE_PAYMENT_RECEIVED_DATE_ERROR_MESSAGE'); ?>");
				return false;
				}

				Joomla.submitform(action );
			}
		}
		else
			Joomla.submitform(action);
	}

var givebackDetails=" ";
</script>
<div class="techjoomla-bootstrap jgive" id="jgive-checkout">
	<form action="" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-horizontal form-validate form-validate">
		<div class="com_jgive_float_left alert alert-danger" id="white_space_alert_group" style="display: none;">
			<em><?php echo Text::_('COM_JGIVE_WHITE_SPACE_NOT_ALLOWED'); ?></em>
		</div>
		<!-- Start OF billing_info_tab-->
			<div id="billing-info" class="jgive-checkout-steps">
				<h3><?php echo Text::_('COM_JGIVE_DONOR_DONATION_DETAILS');?></h3>
				<div class="checkout-content  checkout-first-step-billing-info" id="billing-info-tab">
					 <!-- Row fluid start -->
					<div  class="row-fluid form-horizontal">
						<!-- Donor -->
						<div class="control-group jgive_select_user">
							<label class="control-label" for="contact_name">
								<?php echo HTMLHelper::tooltip(Text::_('COM_JGIVE_DONAR_USER_LABEL_TOOLTIP'), Text::_('COM_JGIVE_DONOR_USER_LABEL'), '', Text::_('COM_JGIVE_DONOR_USER_LABEL')); ?>
							</label>
							<div class="controls">
								<div id="bloodhound">
									<input class="typeahead" id="contact_name" type="text" placeholder="<?php echo Text::_('COM_JGIVE_SELECT_DONOR');?>" style="z-index: inherit;" autocomplete="unique-field-name" name="unique-name">

									<button type="button" class="btn btn-info btn-small" data-target="#newIndividualDonor" data-toggle="modal" title='<?php echo Text::_('COM_JGIVE_NEW_INDIVIDUAL');?>'>
										<?php echo Text::_('COM_JGIVE_NEW_INDIVIDUAL');?></button>
									<button type="button" class="btn btn-info btn-small" data-target="#newIndividualOrganization" data-toggle="modal" title="<?php echo Text::_('COM_JGIVE_NEW_ORGANIZATION');?>">
										<?php echo Text::_('COM_JGIVE_NEW_ORGANIZATION');?></button>
								</div>
								<div class="alert alert-info" role="alert" id="contact_individual_msg">
									<?php echo Text::_('COM_JGIVE_DONOR_TYPE_INDIVIDUAL'); ?>
								</div>
								<div class="alert alert-info" role="alert" id="contact_organization_msg">
									<?php echo Text::_('COM_JGIVE_DONOR_TYPE_ORGANIZATION'); ?>
								</div>
							</div>
						</div>
						<!-- End of Donor -->
						<!-- Donate anonymously -->
						<?php
						if ($show_field == 1 || $donation_anonym == 0)
						{
						?>
						<div class="control-group">
							<label class="control-label">
								<?php echo HTMLHelper::tooltip(
								Text::_('COM_JGIVE_DONATE_ANONYMOUSLY_TOOLTIP'),
								Text::_('COM_JGIVE_DONATE_ANONYMOUSLY'), '', Text::_('COM_JGIVE_DONATE_ANONYMOUSLY')
								);?>
							</label>
							<div class="controls">
								<label class="radio inline">
									<input type="radio" name="annonymousDonation" id="annonymousDonation1" value="1" >
										<?php echo Text::_('COM_JGIVE_YES');?>
								</label>
								<label class="radio inline">
									<input type="radio" name="annonymousDonation" id="annonymousDonation2" value="0" checked>
										<?php echo Text::_('COM_JGIVE_NO');?>
								</label>
							</div>
						</div>
						<?php
						}
						?>
						<!-- End of Donate anonymously -->
						<!-- Select campaign  -->
						<div class="control-group">
							<label for="campaign_name" class="control-label">
								<?php echo HTMLHelper::tooltip(Text::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL_TOOLTIP'), Text::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL'), '', Text::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL') . ' * ');?>
							</label>
							<div class="controls">
								<input type="text" id="campaign_name" name="campaign_name" class="required" readonly="readonly"
								placeholder="<?php echo Text::_('COM_JGIVE_SELECT_CAMPAIGN_TITLE');?>" value="">

								<input type="hidden" id="campaign_id" name="campaign_id" class="required"
								 value="">
								 <button type="button" class="button btn btn-info btn-small" data-target="#donationSelectCampaign" data-toggle="modal" title="<?php echo Text::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL');?>">
									<?php echo Text::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL');?> 
								</button>
							</div>
						</div>
						<!-- End of Select campaign  -->
						<!--Avail Giveback -->
						<div id="show_giveback_box">
							<div class="control-group">
								<label class="control-label" for="no_giveback">
									<?php echo HTMLHelper::tooltip(
									Text::_('COM_JGIVE_NO_GIVEBACK_TOOLTIP'),
									Text::_('COM_JGIVE_NO_GIVEBACK'), '', Text::_('COM_JGIVE_NO_GIVEBACK')
									); ?>
								</label>

								<div class="controls">
									<label class="checkbox">
										<input type="checkbox" id="no_giveback" name="no_giveback" class="checkbox" value="1"
										onchange="jgiveAdmin.donations.noGiveBack()" >
										<span class="checkboxtext"><?php  echo Text::_('COM_JGIVE_THANKS_MSG'); ?></span>
									</label>
								</div>
							</div>
							<div id="hide_giveback" class="control-group">
								<label class="control-label" for="givebacks">
									<?php echo HTMLHelper::tooltip(Text::_('COM_JGIVE_GIVEBACKS_TOOLTIP'), Text::_('COM_JGIVE_GIVEBACK'), '', Text::_('COM_JGIVE_GIVEBACK')); ?>
								</label>
								<div class="controls">
									<select name="givebacks" id="givebacks" onchange="jgiveAdmin.donations.populateGiveback()" >
									</select>
									<br/><br/>
								</div>
							</div>
							<div id="hide_giveback_desc" class="control-group af-d-none">
								<label class="control-label" for="givebacks">
									<?php echo HTMLHelper::tooltip(
									Text::_('COM_JGIVE_GIVEBACKS_DESC_TOOLTIP'),
									Text::_('COM_JGIVE_GIVEBACK_DESC'), '', Text::_('COM_JGIVE_GIVEBACK_DESC')
									); ?>
								</label>
								<div class="controls">
									<div id="giveback_des" class="well span6">
									</div>
								</div>
							</div>
						</div>
						<!-- End of Avail Giveback-->
						<!-- Donation amount  -->
						<div class="control-group">
							<label class="control-label" for="donation_amount">
								<?php echo HTMLHelper::tooltip(
								Text::_('COM_JGIVE_DONATION_AMOUNT_TOOLTIP'),
								Text::_('COM_JGIVE_DONATION_AMOUNT'), '', Text::_('COM_JGIVE_DONATION_AMOUNT') . ' * '
								); ?>
							</label>
							<div class="controls">
								<div class="input-prepend input-append">
									<input type="number" id="donation_amount" name="donation_amount" class="required"
									placeholder="<?php echo Text::_('COM_JGIVE_DONATION_AMOUNT');?>"
									onchange="jgiveCommon.validateAmountAsPerCurrency(this);"
									onkeyup="com_jgive.UI.Donation.updatePlatformFee(this);">
									<span class="add-on"><?php echo $this->currencyCode;?></span>
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
									<input type="checkbox" id="exclusive_platform_fee" name="exclusive_platform_fee" checked="checked" onclick="com_jgive.UI.Donation.changePlatformFee();"/>
								<?php
								}?>
								<?php echo Text::_("COM_JGIVE_PLATFORM_FEE_PART1")?>
								<?php echo $this->currencyCode?>
								<span id="platform_fee"><?php echo $predefinedPlatformFee?></span>
								<?php echo Text::_("COM_JGIVE_PLATFORM_FEE_PART2")?>
							</div>
							<?php
						}
						?>
						<!-- End of Donation amount  -->
						<!--Donation type -->
						<div class="control-group af-d-none">
							<div class="controls">
								<label class="radio inline">
									<input type="radio" name="checkout_type" id="checkout_register" value="0" checked="checked" onclick="jgiveAdmin.donations.jGive_toggle_checkout(0)">
										<?php echo Text::_('COM_JGIVE_CHECKOUT_REGISTERED');?>
								</label>
								<?php
								if ($this->guest_donation)
								{
								?>
								<label class="radio inline">
									<input type="radio" name="checkout_type" id="checkout_guest" value="1"  onclick="jgiveAdmin.donations.jGive_toggle_checkout(1)">
										<?php echo Text::_('COM_JGIVE_CHECKOUT_GUEST');?>
								</label>
								<?php
								}
								?>
							</div>
						</div>

						<!-- Payment received date -->
						<div class="control-group">
							<label class="control-label" for="payment_received_date">
								<?php echo HTMLHelper::tooltip(Text::_('COM_JGIVE_PAYMENT_RECEIVED_DATE_TOOLTIP'), Text::_('COM_JGIVE_PAYMENT_RECEIVED_DATE'), '', Text::_('COM_JGIVE_PAYMENT_RECEIVED_DATE')); ?>
							</label>
							<div class="controls">
								<?php echo HTMLHelper::calendar(date(Text::_('COM_JGIVE_DONATION_DATE_FORMAT')), 'payment_received_date', 'payment_received_date', Text::_('COM_JGIVE_DATE_FORMAT'));?>
							</div>
						</div>
						<!-- End of Payment received date -->

						<?php
						if ($this->params->get('terms_condition'))
						{
							$link = '';

							if ($this->params->get('payment_terms_article'))
							{
								$link = Route::_(Uri::root() . "index.php?option=com_content&view=article&id=" . $this->params->get('payment_terms_article') . "&tmpl=component");
							}

							if ($link)
							{
							?>
								<div class="control-group">
									<label for="terms_condition" class="checkbox">
										<input type="checkbox" name="terms_condition" id="terms_condition" size="30" required/>
										<?php  echo Text::_('COM_JGIVE_DONATION_ACCEPT_USER_TERMS_CONDITIONS_FIRST'); ?>
										<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link;?>" class="modal">
											<?php echo Text::_('COM_JGIVE_DONATION_PRIVACY_POLICY');?>
										</a>
										<?php echo Text::_('COM_JGIVE_DONATION_ACCEPT_USER_TERMS_CONDITIONS_LAST'); ?>
									</label>
								</div>
							<?php
							}
						}
						?>

						<!-- Payment Details heading-->
						<div>
							<div>
								<span class="help-inline jgive_removeBottomMargin" id="billmail_msg"></span>
								<h3><?php echo Text::_('COM_JGIVE_PAYMENT_DETAILS');?></h3>
							</div>
						</div>
						<!-- End of Payment Details heading-->

						<div class="control-group">
							<label class="control-label" for="state">
								<?php echo HTMLHelper::tooltip(
								Text::_('COM_JGIVE_PAY_METHODS_TOOLTIP'),
								Text::_('COM_JGIVE_PAY_METHODS'), '', Text::_('COM_JGIVE_PAY_METHODS')
								); ?>
							</label>
							<div>
								<?php
									$select = array();
									$gateways = array_merge($select, $this->gateways);
									$gateways = array_filter($gateways);

									// If only one geteway then keep it as selected
									if (count($gateways) >= 1)
									{
										// Id and value is same
										$default = $gateways[0]->id;
									}

									if (empty($this->gateways))
									{
										echo Text::_('COM_JGIVE_NO_PAYMENT_GATEWAY');
									}
									else
									{
										$pg_list = HTMLHelper::_('select.radiolist', $gateways, 'gateways', 'class="inputbox required "', 'id', 'name', $default, false);
										echo $pg_list;
									}
								?>
							</div>
						</div>

						<!-- Donation status -->
						<div class="control-group">
							<label class="control-label" for="donation_status">
								<?php echo HTMLHelper::tooltip(
								Text::_('COM_JGIVE_DONATION_STATUS_TOOLTIP'),
								Text::_('COM_JGIVE_DONATION_STATUS'), '', Text::_('COM_JGIVE_DONATION_STATUS') . ' * '
								); ?>
							</label>
							<div class="controls">
								<div class="input-prepend input-append" id="donation_status" name="donation_status">
									<?php
										echo HTMLHelper::_(
													'select.genericlist',
													$this->pstatus,
													'pstatus',
													'class="input-medium pad_status" size="1"',
													'value',
													'text',
													'C',
													'donation_status'
										);
									?>
								</div>
							</div>
						</div>
						<!-- End of Donation status -->

						<!-- Transaction ID -->
						<div class="control-group">
							<label class="control-label" for="transaction_id">
								<?php echo HTMLHelper::tooltip(
								Text::_("COM_JGIVE_DONATION_TRANSACTION_ID_TOOLTIP"),
								Text::_('COM_JGIVE_DONATION_TRANSACTION_ID'), '', Text::_('COM_JGIVE_DONATION_TRANSACTION_ID')
								); ?>
							</label>
							<div class="controls">
								<div class="input-prepend input-append">
									<input type="text" id="transaction_id" name="transaction_id"
									placeholder="<?php echo Text::_('COM_JGIVE_DONATION_TRANSACTION_ID');?>" onchange="" class="no-whitespace">
								</div>
							</div>
						</div>
						<!-- End of Transaction ID -->

						<!-- Recuring donation-->
						<?php
						$recurring_donation = 0;
						$recurring_donation = $this->params->get('recurring_donation');

						if ($recurring_donation)
						{
							if ($show_field == 1 || $donation_type == 0)
							{
							?>
								<!--Donation type -->
								<div class="control-group">
									<label class="control-label">
										<?php echo HTMLHelper::tooltip(
										Text::_('COM_JGIVE_DONATION_TYPE_TOOLTIP'),
										Text::_('COM_JGIVE_DONATATION_TYPE'), '', Text::_('COM_JGIVE_DONATATION_TYPE')
										);?>
									</label>
									<div class="controls">
										<label class="radio inline">
											<input type="radio" name="donation_type" id="donation_one_time" value="0" checked onclick="jgiveAdmin.donations.jGive_RecurDonation(0)">
												<?php echo Text::_('COM_JGIVE_ONE_TIME');?>
										</label>
										<label class="radio inline">
											<input type="radio" name="donation_type" id="donation_recurring" value="1" onclick="jgiveAdmin.donations.jGive_RecurDonation(1)" >
												<?php echo Text::_('COM_JGIVE_RECURRING');?>
										</label>
									</div>
								</div>
							<?php
							}?>

							<!--recurring type -->
							<div id="recurring_freq_div" class="control-group jgive_display_none">
								<label class="control-label" for="recurring_freq">
									<?php echo HTMLHelper::tooltip(
									Text::_('COM_JGIVE_DONATE_RECR_TYPE_TOOLTIP'),
									Text::_('COM_JGIVE_RECURRING_TYPE'), '', Text::_('COM_JGIVE_RECURRING_TYPE')
									);?>
								</label>
								<div class="controls">
									<select name="recurring_freq" id="recurring_freq">
										<option value="DAY"><?php echo Text::_('COM_JGIVE_RECUR_DAILY');?></option>
										<option value="WEEK"><?php echo Text::_('COM_JGIVE_RECUR_WEEKLY');?></option>
										<option value="MONTH"><?php echo Text::_('COM_JGIVE_RECUR_MONTHLY');?></option>
										<option value="QUARTERLY"><?php echo Text::_('COM_JGIVE_RECUR_QUARTERLY');?></option>
										<option value="YEAR"><?php echo Text::_('COM_JGIVE_RECUR_ANNUALLY');?></option>
									</select>
								</div>
							</div>

							<!--recurring times -->
							<div id="recurring_count_div" class="control-group jgive_display_none">
								<label class="control-label" for="recurring_count">
									<?php echo HTMLHelper::tooltip(
									Text::_('COM_JGIVE_DONATION_RECUR_TIMES_TOOLTIP'),
									Text::_('COM_JGIVE_RECUR_TIMES'), '', Text::_('COM_JGIVE_RECUR_TIMES')
									); ?>
								</label>
								<div class="controls">
									 <div class="input-prepend input-append">
										<input type="text" id="recurring_count" name="recurring_count" class="validate-numeric"
										placeholder="<?php
										echo Text::_('COM_JGIVE_RECUR_TIMES');
										?>" >
									</div>
								</div>
							</div>
						<?php
						}

						/* Vat number*/
						if ($this->params->get('vat_for_donor'))
						{
						?>
							<div class="control-group">
								<label class="control-label" for="vat_number">
									<?php echo HTMLHelper::tooltip(Text::_('COM_JGIVE_VAT_NUMBER_TOOLTIP'), Text::_('COM_JGIVE_VAT_NUMBER'), '', Text::_('COM_JGIVE_VAT_NUMBER')); ?>
								</label>
								<div class="controls">
									 <div class="input-prepend input-append">
										<input type="text" id="vat_number" name="vat_number"
										placeholder="<?php
										echo Text::_('COM_JGIVE_VAT_NUMBER'); ?>" >
									</div>
								</div>
							</div>
						<?php
						}?>
						<!-- End of Recuring donation-->
						<div class="jtspacer">
							<input type="hidden" name="cid" id="cid" value="" />
							<input type="hidden" name="option" value="com_jgive" />
							<input type="hidden" name="view" value="donations" />
							<input type="hidden" id="controller" name="controller" value="donations" />
							<input type="hidden" name="Itemid" value="<?php echo $this->input->get('Itemid', '', 'INT');?>" />
							<input type="hidden" name="order_id" id="order_id" value="0" />
							<input type="hidden" name="platform_fee" id="platform_fee_value" value="0" />
							<input type="hidden" name="task" id="task" value="placeOrder" />
							<input type="hidden" name="userid" id="userid" value="<?php echo $this->user->id ?$this->user->id: '0';?>">
							<input type="hidden" name="donorid" id="contact_id" value="" />
							<?php echo HTMLHelper::_('form.token'); ?>
						</div>
					</div>
					<!-- End ofRow fluid start-->
				</div>
			</div>
		<!-- END OF billing_info_tab-->
		</div>
		<!--EOF Select Payment form-->
	</form>
</div>
<!-- EOF of techjoomla bootrap -->
