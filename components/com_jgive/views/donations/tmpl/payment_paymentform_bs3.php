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
defined('_JEXEC') or die(';)');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::_('formbehavior.chosen', '#jform_country', null);
HTMLHelper::_('formbehavior.chosen', '#jform_state', null);
HTMLHelper::_('formbehavior.chosen', '#jform_city', null);
HTMLHelper::_('formbehavior.chosen', '#givebacks', null);
HTMLHelper::_('formbehavior.chosen', '#recurring_freq', null);

$pagetitle = ($this->campaignFlag) ? Text::sprintf('COM_JGIVE_CHECKOUT_TITLE', $this->cdata['campaign']->title) : Text::sprintf('COM_JGIVE_CHECKOUT_TITLE_INVESTMENT', $this->cdata['campaign']->title);

$document = Factory::getDocument();
$document->setTitle(htmlspecialchars($pagetitle, ENT_COMPAT, 'UTF-8'));
$baseurl = Route::_(Uri::root() . 'index.php');

// Check Donor is as Guest or registered user
$registr_field_display = ($this->user->id) ? "display:none" : "display:display";
$isguest = $this->user->id;
$isguest = empty($isguest) ? 1 : 0;

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

if ($show_selected_fields_on_donate) {
    $donationfield = $this->params->get('donationfield');

    if (isset($donationfield)) {
        foreach ($donationfield as $tmp) {
            switch ($tmp) {
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
} else {
    $show_field = 1;
}

$js = "
var isgst=" . $isguest . ";
var jgive_baseurl='" . $baseurl . "';

	function jGive_submitbutton(pressbutton)
	{
		var form = document.jGivePaymentForm;

		if(pressbutton)
			{
				techjoomla.jQuery('#payment-info-tab').hide();
				techjoomla.jQuery('#system-message').remove();
				if (document.formvalidator.isValid(document.getElementById('jGivePaymentForm')))
				{
					techjoomla.jQuery('#system-message').remove();
				}
				else
				{
					techjoomla.jQuery('#payment-info-tab').hide();
					var msg = 'Some values are not acceptable.  Please retry.';
					alert(msg);
					return false;
				}

				values=techjoomla.jQuery('#jGivePaymentForm').serialize();
				var order_id=techjoomla.jQuery('#order_id').val();
				techjoomla.jQuery.ajax({
					url: jgive_baseurl+'?option=com_jgive&task=donations.placeOrder&tmpl=component',
					type: 'POST',
					data:values,
					dataType: 'json',
					beforeSend: function()
					{
						techjoomla.jQuery('#confirm-order').after('<div class=\"com_jgive_ajax_loading\"><div class=\"com_jgive_ajax_loading_text\">" . Text::_('COM_JGIVE_LOADING_PAYMET_FORM_MSG') . "</div><img class=\"com_jgive_ajax_loading_img\" src=\"" . Uri::base() . "media/com_jgive/images/ajax.gif\"></div>');

						// CODE TO HIDE EDIT LINK
						jgive.donations.jgive_hideAllEditLinks();
					},
					complete: function()
					{
						techjoomla.jQuery('#jgive_order_details_tab').show()
						techjoomla.jQuery('.com_jgive_ajax_loading').remove();
						jgive.donations.jgive_showAllEditLinks();
					},
					success: function(data)
					{
						if(data['success'] == 1)
						{
							if(isgst==1)
							{
								techjoomla.jQuery('#user-info').html('');
							}
							techjoomla.jQuery('#payment-info-tab').hide();
							techjoomla.jQuery('#order_id').val(data['order_id']);
							techjoomla.jQuery('#payment-info .checkout-heading a').remove();
							jgive.donations.addEditLink(techjoomla.jQuery('#payment-info'+' .checkout-heading'),'payment-info');

							techjoomla.jQuery('#payment_tab_table_html').html(data['payhtml']);
							techjoomla.jQuery('#order_summary_tab_table_html').html(data['orderHTML']);
							techjoomla.jQuery('#payment_tab').show();
							techjoomla.jQuery('#payment_tab_table').show();
							techjoomla.jQuery('#order_summary_tab').show();
							techjoomla.jQuery('#order_summary_tab_table').show();
							techjoomla.jQuery('#payment-info-tab').hide();
							techjoomla.jQuery('#payment_tab_table_html').html(data['gatewayhtml']);
							techjoomla.jQuery('#payment_tab_table strong').text('Payment: '+data['gatewayName']);
							techjoomla.jQuery('#payment_tab').show();
							techjoomla.jQuery('#payment_tab_table').show();
							techjoomla.jQuery('#payment_tab_table_html').show();
						}
						else
						{
							if (data.success_msg.length > 0)
							{
								for (x of data.success_msg)
								{
									Joomla.renderMessages({'error':[x]});
									jQuery('html, body').animate({scrollTop: 0}, 500);
								}
							}

							techjoomla.jQuery('#payment-info').hide();
							techjoomla.jQuery('#payment-info-tab').hide();
							techjoomla.jQuery('#payment_tab').hide();
							techjoomla.jQuery('#payment_tab_table').html();
							techjoomla.jQuery('#order_summary_tab_table_html').html();
							techjoomla.jQuery('#payment_tab_table').hide();
							techjoomla.jQuery('#order_summary_tab_table').hide();
						}
					}
				});
				return;
			}
		return;
	}
";

$document->addScriptDeclaration($js);
?>
<script type="text/javascript">
    //get Commission for identifying minimum donation amount
    var minimum_amount = "<?php echo $this->cdata['campaign']->minimum_amount; ?>";
    minimum_amount = parseFloat(minimum_amount);
    var send_payments_to_owner = "<?php echo $this->params->get('send_payments_to_owner'); ?>";
    var commission_fee = "<?php echo $this->params->get('commission_fee'); ?>";
    var fixed_commissionfee = "<?php echo $this->params->get('fixed_commissionfee'); ?>";
    const jgiveBaseUrl = '<?php echo Uri::root(); ?>';
    var gatewayArr = <?php echo json_encode($this->gateways); ?>;
    var recurringGateways = <?php echo json_encode($this->recurringGateways); ?>;
    var campaign_type = "<?php echo $this->cdata['campaign']->type; ?>";
    var currency_symbol = "<?php echo $this->currencySymbol; ?>";

    function validatedatainsteps(thistepobj, nextstepid, stepno, currentstepname) {
        if (parseInt(stepno) == 2) {
            GotonextStep(thistepobj, nextstepid, currentstepname);
        }

        if (parseInt(stepno) == 3) //donation form
        {
            //check donation amount is valid
            var donation_amount = document.getElementById('donation_amount').value;
            donation_amount = parseFloat(donation_amount);

            if (donation_amount) {
                if (!send_payments_to_owner) {
                    if (commission_fee) {
                        total_commission_amount = ((donation_amount * commission_fee) / 100) + fixed_commissionfee;
                    } else {
                        total_commission_amount = fixed_commissionfee;
                    }
                } else {
                    total_commission_amount = 0;
                }

                if (total_commission_amount < minimum_amount) {
                    total_commission_amount = minimum_amount; //trikey don't bother it
                }

                if (total_commission_amount > donation_amount) {
                    alert("<?php echo Text::_('COM_JGIVE_MINIMUM_DONATION_AMOUNT'); ?>" + total_commission_amount);
                    return false;
                }

                var response = jgive.donations.paymentFormValidateGiveBackAmount(donation_amount);

                if (!response) {
                    return false;
                }

                let returnValue = jgiveCommon.validateAmountAsPerCurrency(document.getElementById('donation_amount'));
                if (returnValue === false) {
                    return false;
                }
            }


            if (document.formvalidator.isValid(document.getElementById('jGivePaymentForm'))) {
                techjoomla.jQuery('#payment-info-tab').hide();
                techjoomla.jQuery('#system-message').remove();
            } else {
                techjoomla.jQuery('#payment-info-tab').hide();
                var msg = "<?php echo Text::_('COM_JGIVE_FORM_INVALID') ?>";
                alert(msg);
                return false;
            }
            <?php
            if ($this->params->get('terms_condition') && ($this->params->get('payment_terms_article') != 0)) {
            ?>
                if (document.jGivePaymentForm.terms_condition.checked == false) {
                    alert(Joomla.Text._('COM_JGIVE_CHECK_TERMS'));
                    return false;
                }
            <?php
            } ?>

            /* If only one payment gateway then do not show option to select the gateway*/
            let gateWayCount = jQuery('#jgiveWrapper #gatewaysContent div.radio').length;

            if (gateWayCount === 1) {
                jGive_submitbutton('save');
            }

            GotonextStep(thistepobj, nextstepid, currentstepname);
        }
    }

    function GotonextStep(thistepobj, nextstepid, currentstepname) {
        techjoomla.jQuery('.checkout-content').hide();
        techjoomla.jQuery('#' + currentstepname.toString() + ' .checkout-heading a').remove();

        var order_id = techjoomla.jQuery('#order_id').val();
        var finalamt = techjoomla.jQuery('#net_amt_pay_inputbox').val();
        if (parseFloat(finalamt) <= 0)
            techjoomla.jQuery('#payment_info-tab-method').hide();
        else
            techjoomla.jQuery('#payment_info-tab-method').show();

        if (parseInt(order_id) >= 1) {
            jGive_submitbutton(thistepobj.id)
            jgive.donations.addEditLink(techjoomla.jQuery('#' + currentstepname.toString() + ' .checkout-heading'),
                currentstepname.toString());
            return;
        }

        techjoomla.jQuery('#' + nextstepid).slideDown('slow');
        techjoomla.jQuery('#' + nextstepid).show();
        //MOVE CURSOR TO CURRENT STEP
        var parentid = techjoomla.jQuery('#' + nextstepid).parent().attr('id');
        jgive.donations.goToByScroll(parentid);
        jgive.donations.addEditLink(techjoomla.jQuery('#' + currentstepname.toString() + ' .checkout-heading'),
            currentstepname.toString());
        jgive.donations.jgive_showAllEditLinks()
    }

    techjoomla.jQuery(document).ready(function() {
        var userid = techjoomla.jQuery('#userid').val();

        if (parseInt(userid) == 0) {
            techjoomla.jQuery(".checkout-first-step-billing-info").hide();
            techjoomla.jQuery(".checkout-first-step-user-info").show();
        } else {
            techjoomla.jQuery(".checkout-first-step-billing-info").show();
        }

        techjoomla.jQuery('#payment-info-tab').hide();

        var DBuserbill = "<?php echo (isset($this->userbill->state_code)) ? $this->userbill->state_code : ''; ?>";
        var state = '',
            city = '',
            category = '';

        <?php
        $jgive_state = $this->session->get('JGIVE_state', '');
        $jgive_city = $this->session->get('JGIVE_city', '');

        if (!empty($jgive_state)) {
        ?>
            state = "<?php echo $this->session->get('JGIVE_state', ''); ?>";
        <?php
        }

        if (!empty($jgive_city)) {
        ?>
            city = "<?php echo $this->session->get('JGIVE_city', ''); ?>";
        <?php
        } ?>

        com_jgive.UI.Common.generateStates('jform_country', 0, state, city);
        jgive.donations.populateGiveback();

        //payment gateways html
        com_jgive.UI.Donation.recurDonation(0, gatewayArr);
        //jGive_RecurDonation(0);

        /* If only one payment gateway then do not show option to select the gateway*/
        let gateWayCount = jQuery('#jgiveWrapper #gatewaysContent div.radio').length;

        if (gateWayCount === 1) {
            jQuery('#jgiveWrapper #payment-info').addClass('d-none');
        }

        com_jgive.UI.Donation.updatePlatformFee(document.getElementById("donation_amount"),
            <?php echo $this->input->get('cid', 0, 'INT') ?>);
    });

    //Populate selectd giveback amount in donation amount field
    var givebackDetails = <?php

                            if (!empty($this->cdata['campaign']->givebacks)) {
                                echo json_encode($this->cdata['campaign']->givebacks, 1);
                            } else {
                                echo 0;
                            } ?>;
</script>

<?php
// Jomsocial toolprogress-bar
if (isset($this->jomsocialToolbarHtml)) {
    echo $this->jomsocialToolbarHtml;
}
?>
<div id="jgiveWrapper">
    <div class="<?php echo COM_JGIVE_WRAPPAER_CLASS; ?> jgive" id="jgive-checkout">
        <div class="page-header">
            <h1 class="fs-title af-mt-10">
                <?php
                if ($this->campaignFlag) {
                    echo Text::sprintf('COM_JGIVE_CHECKOUT_TITLE', $this->cdata['campaign']->title ? htmlspecialchars($this->cdata['campaign']->title, ENT_COMPAT, 'UTF-8') : '');
                } else {
                    echo Text::sprintf('COM_JGIVE_CHECKOUT_TITLE_INVESTMENT', $this->cdata['campaign']->title ? htmlspecialchars($this->cdata['campaign']->title, ENT_COMPAT, 'UTF-8') : '');
                }
                ?>
            </h1>
        </div>
        <div class="container-fluid">
            <div class="col-xs-12">
                <form action="" method="post" name="jGivePaymentForm" id="jGivePaymentForm" class="form-validate">
                    <?php
                    if (!$this->user->id) {
                    ?>
                        <div id="user-info" class="jgive-checkout-steps row">
                            <div class="checkout-heading border-gray af-mb-15 af-p-10 alert-info">
                                <strong><?php echo Text::_('COM_JGIVE_USER_INFO'); ?></strong>
                            </div>
                            <div class="checkout-content checkout-first-step-user-info" id="user-info-tab">
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <h2>
                                        <?php echo Text::_('COM_JGIVE_CHECKOUT_NEW_DONOR'); ?>
                                    </h2>
                                    <h4>
                                        <?php echo Text::_('COM_JGIVE_CHECKOUT_OPTIONS'); ?>
                                    </h4>

                                    <?php
                                    if ($this->guest_donation == '1') {
                                        /*Registration*/
                                        if ($this->userParams->get('allowUserRegistration') == '1') {
                                    ?>
                                            <label for="register">
                                                <input type="radio" name="account" value="register" id="register"
                                                    checked="checked" />
                                                <b><?php echo Text::_('COM_JGIVE_CHECKOUT_REGISTER'); ?></b>
                                            </label>
                                            <br />
                                        <?php
                                        }

                                        /*Guest Donation*/
                                        $check = ($this->userParams->get('allowUserRegistration') == '0') ? "checked" : "";
                                        ?>
                                        <label for="guest">
                                            <input type="radio" name="account" value="guest" id="guest" <?php echo $check; ?> />
                                            <b><?php echo Text::_('COM_JGIVE_CHECKOUT_GUEST'); ?></b>
                                        </label>
                                        <?php
                                        if ($this->userParams->get('allowUserRegistration') == '1') {
                                        ?>
                                            <p class="text-info">
                                                <?php echo Text::_('COM_JGIVE_CHECKOUT_REGISTER_ACCOUNT_HELP_TEXT'); ?>
                                            </p>
                                    <?php
                                        }
                                    }
                                    ?>
                                    <br>
                                    <input type="button" class="btn btn-primary" id="button-user-info"
                                        value="<?php echo Text::_('COM_JGIVE_CONTINUE'); ?>"
                                        onclick="validatedatainsteps(this, '<?php echo "billing-info-tab" ?>', 2, 'user-info')" />
                                    <br />
                                </div>

                                <div id="login" class="col-md-6 col-sm-6 col-xs-12">
                                    <h2><?php echo Text::_('COM_JGIVE_CHECKOUT_RETURNING_DONOR'); ?></h2>
                                    <h4><?php echo Text::_('COM_JGIVE_CHECKOUT_RETURNING_DONOR_WELCOME'); ?></h4>
                                    <b><?php echo Text::_('COM_JGIVE_CHECKOUT_USERNAME'); ?></b>
                                    <br />
                                    <input type="text" name="email" value="" />
                                    <b><?php echo Text::_('COM_JGIVE_CHECKOUT_PASSWORD'); ?></b>
                                    <br />
                                    <input type="password" name="password" value="" />
                                    <br />
                                    <input type="hidden" name="cid"
                                        value="<?php echo $this->input->get('cid', '', 'INT'); ?>" />
                                    <input type="button" value="<?php echo Text::_('COM_JGIVE_CHECKOUT_LOGIN'); ?>"
                                        id="button-login" onclick="jgive.donations.jgive_login()" class="btn btn-primary" />
                                    <br />
                                    <br />
                                </div>
                                <div class="span5 pull-left">
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>

                    <!-- Start OF billing_info_tab-->
                    <div id="billing-info" class="jgive-checkout-steps row">
                        <div class="checkout-heading border-gray af-mb-15 af-p-10 alert-info">
                            <strong>
                                <?php echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONOR_DETAILS') : Text::_('COM_JGIVE_INVESTOR_DETAILS')); ?>
                            </strong>
                        </div>
                        <div class="checkout-content  checkout-first-step-billing-info" id="billing-info-tab">
                            <div class="form-horizontal">
                                <div class="row">
                                    <div class="form-group" id="jgive_billmail_msg_div">
                                        <span class="help-inline jgive_removeBottomMargin" id="billmail_msg"></span>
                                    </div>
                                </div>
                                <?php
                                if ($show_field == 1 || $first_name == 0) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="first_name"
                                                    title="<?php echo Text::_('COM_JGIVE_FIRST_NAME_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_FIRST_NAME') . '*'; ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <input type="text" id="first_name" name="first_name"
                                                        class="validate-name required form-control"
                                                        placeholder="<?php echo Text::_('COM_JGIVE_FIRST_NAME_PH'); ?>"
                                                        value="<?php echo htmlspecialchars($this->session->get('JGIVE_first_name', ''), ENT_COMPAT, 'UTF-8'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($show_field == 1 or $last_name == 0) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="last_name"
                                                    title="<?php echo Text::_('COM_JGIVE_LAST_NAME_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_LAST_NAME') . '*'; ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <input type="text" id="last_name" name="last_name"
                                                        class="required form-control"
                                                        placeholder="<?php echo Text::_('COM_JGIVE_LAST_NAME_PH'); ?>"
                                                        value="<?php echo htmlspecialchars($this->session->get('JGIVE_last_name', ''), ENT_COMPAT, 'UTF-8'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($show_field == 1 or $address == 0) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="address"
                                                    title="<?php echo Text::_('COM_JGIVE_ADDRESS_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_ADDRESS') . '*'; ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <input type="text" id="address" name="address"
                                                        class="required form-control"
                                                        placeholder="<?php echo Text::_('COM_JGIVE_ADDRESS_PH'); ?>"
                                                        value="<?php echo htmlspecialchars($this->session->get('JGIVE_address', ''), ENT_COMPAT, 'UTF-8'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($show_field == 1 or $address2 == 0) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="address2"
                                                    title="<?php echo Text::_('COM_JGIVE_ADDRESS2_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_ADDRESS2'); ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <input type="text" id="address2" name="address2"
                                                        placeholder="<?php echo Text::_('COM_JGIVE_ADDRESS2'); ?>"
                                                        value="<?php echo htmlspecialchars($this->session->get('JGIVE_address2', ''), ENT_COMPAT, 'UTF-8'); ?>"
                                                        class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($show_field == 1 or $hide_country == 0) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="country"
                                                    title="<?php echo Text::_('COM_JGIVE_COUNTRY_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_COUNTRY') . '*'; ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <?php
                                                    $sessionCountryId = $this->session->get('JGIVE_country', $this->default_country);
                                                    $default = !empty($sessionCountryId) ? $sessionCountryId : $this->default_country;

                                                    $options = array();
                                                    $options[] = HTMLHelper::_('select.option', "", Text::_('COM_JGIVE_COUNTRY'));

                                                    foreach ($this->countries as $key => $value) {
                                                        $country = $this->countries[$key];
                                                        $id = $country['id'];
                                                        $value = $country['country'];
                                                        $options[] = HTMLHelper::_('select.option', $id, $value);
                                                    }

                                                    echo $this->dropdown = HTMLHelper::_(
                                                        'select.genericlist',
                                                        $options,
                                                        'country',
                                                        'required="required" aria-invalid="false"
												class="input-large" onchange="com_jgive.UI.Common.generateStates(id,0,\'' .
                                                            $this->session->get('JGIVE_state', '') . '\',\'' . $this->session->get('JGIVE_city', '') . '\')"',
                                                        'value',
                                                        'text',
                                                        $default,
                                                        'jform_country'
                                                    );

                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($show_field == 1 || ($hide_country == 0 && $state == 0)) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="state"
                                                    title="<?php echo Text::_('COM_JGIVE_STATE_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_STATE'); ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <select name="state" id="jform_state" class="input-large"></select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($show_field == 1 || ($hide_country == 0 && $city == 0)) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group" id="hide_city">
                                                <label class="col-sm-3 col-xs-12 control-label" for="city"
                                                    title="<?php echo Text::_('COM_JGIVE_CITY_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_CITY'); ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <select name="city" id="jform_city" class="input-large"></select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="other_city_check"
                                                    id="other_city_lbl"
                                                    title="<?php echo Text::_('COM_JGIVE_OTHER_CITY_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_OTHER_CITY') . '&nbsp;&nbsp;'; ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12" id="other_city_control">
                                                    <input type="checkbox" class="checkox" style="vertical-align:middle;"
                                                        name="other_city_check" id="other_city_check"
                                                        onchange="jgive.donations.otherCity()" />
                                                    <input type="text" name="other_city" id="other_city"
                                                        placeholder="<?php echo Text::_('COM_JGIVE_ENTER_OTHER_CITY'); ?>"
                                                        class="form-control" value="" style="display:none;"
                                                        pattern="[a-zA-Z\s\.]+">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($show_field == 1 or $zip == 0) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="zip"
                                                    title="<?php echo Text::_('COM_JGIVE_ZIP_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_ZIP') . '*'; ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <input type="text" id="zip" name="zip" class="required form-control"
                                                        placeholder="<?php echo Text::_('COM_JGIVE_ZIP_PH'); ?>"
                                                        value="<?php echo $this->session->get('JGIVE_zip', ''); ?>"
                                                        pattern="[a-zA-Z0-9\s\.]+">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                                <div class="col-xs-12 col-md-6">
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="col-sm-3 col-xs-12 control-label" for="paypal_email"
                                                title="<?php echo Text::_('COM_JGIVE_PAYPAL_EMAIL_TOOLTIP'); ?>">
                                                <?php echo Text::_('COM_JGIVE_EMAIL') . '*'; ?>
                                            </label>
                                            <div class="col-sm-9 col-xs-12">
                                                <input type="text" id="paypal_email"
                                                    <?php echo ((!$this->user->id) ?  'onchange="jgive.donations.chkmail(this.value);"' : ''); ?>
                                                    name="paypal_email" class="required validate-email form-control"
                                                    placeholder="<?php echo Text::_('COM_JGIVE_EMAIL_PH'); ?>"
                                                    value="<?php echo htmlspecialchars($this->session->get('JGIVE_paypal_email', ''), ENT_COMPAT, 'UTF-8'); ?>"
                                                    onchange='jgive.donations.validateCheckoutFields(document.getElementById("paypal_email").value)'>
                                                <span class="help-inline" id="email_reg"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if ($show_field == 1 or $phone_no == 0) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="phone"
                                                    title="<?php echo Text::_('COM_JGIVE_PHONE_TOOLTIP'); ?>">
                                                    <?php echo Text::_('COM_JGIVE_PHONE'); ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <input type="text" id="phone" name="phone"
                                                        placeholder="<?php echo Text::_('COM_JGIVE_PHONE_PH'); ?>"
                                                        class="form-control"
                                                        value="<?php echo htmlspecialchars($this->session->get('JGIVE_phone', ''), ENT_COMPAT, 'UTF-8'); ?>"
                                                        pattern=^[0-9\-\(\)\/\+\s]*$>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                } ?>

                                <div class="col-xs-12 col-md-6">
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="col-sm-3 col-xs-12 control-label" for="taxnumber"
                                                title="<?php echo Text::_('COM_JGIVE_PAYMENTFORM_TAXNUMBER_TOOLTIP'); ?>">
                                                <?php echo Text::_('COM_JGIVE_PAYMENTFORM_TAXNUMBER'); ?>
                                            </label>
                                            <div class="col-sm-9 col-xs-12">
                                                <input type="text" id="taxnumber" name="taxnumber"
                                                    placeholder="<?php echo Text::_('COM_JGIVE_PAYMENTFORM_TAXNUMBER'); ?>"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($this->session->get('JGIVE_taxnumber', ''), ENT_COMPAT, 'UTF-8'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12">
                                    <div class="row">
                                        <div class="border-gray af-mb-15 af-p-10 alert-info">
                                            <strong>
                                                <?php echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATION_DETAILS') : Text::_('COM_JGIVE_INVESTMENT_DETAILS'));
                                                ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-6">
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="col-sm-3 col-xs-12 control-label" for="donation_amount" title="<?php echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATION_AMOUNT_TOOLTIP') : Text::_('COM_JGIVE_INVESTMENT_AMOUNT_TOOLTIP'));
                                                                                                                            ?>">
                                                <?php echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATION_AMOUNT') : Text::_('COM_JGIVE_INVESTMENT_AMOUNT'));
                                                ?> *
                                            </label>
                                            <div class="col-sm-9 col-xs-12">
                                                <div class="input-group">
                                                    <?php
                                                    if (!empty($this->giveback_id)) {
                                                        $predefinedAmount = '';

                                                        foreach ($this->cdata['givebacks'] as $giveback) {
                                                            if ($this->giveback_id == $giveback->id) {
                                                                $predefinedAmount = $giveback->amount;
                                                            }
                                                        }
                                                    } else {
                                                        $predefinedAmount = $this->cdata['campaign']->minimum_amount ? $this->cdata['campaign']->minimum_amount : 0;
                                                    }

                                                    $campaignId = $this->input->get('cid', 0, 'INT');
                                                    $allow_high_value_donation = $this->params->get('allow_high_value_donation');
                                                    $max_allow_high_donation_value = $this->params->get('max_allow_high_donation_value');

                                                    echo '<input type="hidden" value="'.$allow_high_value_donation.'" name="hidden_allow_high_value_donation" id="hidden_allow_high_value_donation"/>';
                                                    echo '<input type="hidden" value="'.$max_allow_high_donation_value.'" name="hidden_max_allow_high_donation_value" id="hidden_max_allow_high_donation_value"/>';
                                                    
                                                    ?>

                                                    <input type="number" id="donation_amount" name="donation_amount"
                                                        class="required form-control check-amount-limit"
                                                        onblur="jgive.donations.validateAmount(id,'<?php echo Text::sprintf("COM_JGIVE_INVALID_DONATION_AMOUNT", $this->cdata["campaign"]->type); ?>')"
                                                        placeholder="<?php echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATION_AMOUNT_PH') : Text::_('COM_JGIVE_INVESTMENT_AMOUNT_PH')); ?>"
                                                        value="<?php echo floatval($predefinedAmount); ?>"
                                                        onchange="jgiveCommon.validateAmountAsPerCurrency(this);"
                                                        onkeyup="com_jgive.UI.Donation.updatePlatformFee(this,<?php echo $campaignId ?>);">
                                                    <span
                                                        class="input-group-addon"><?php echo $this->currencySymbol; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-6">
                                    <div class="row">
                                        <?php
                                        $feeMode = $this->params->get('fee_mode', 'inclusive', 'string');
                                        $sendPaymentsToOwner = $this->params->get('send_payments_to_owner');

                                        if (!$sendPaymentsToOwner && $feeMode == 'exclusive') {
                                            $exclusiveFeeOptional = $this->params->get('exclusive_fee_optional', '0', 'string');
                                            $predefinedPlatformFee = 0;
                                        ?>
                                            <div class="col-md-6">
                                                <?php
                                                if ((int) $exclusiveFeeOptional === 1) {
                                                ?>
                                                    <input type="checkbox" id="exclusive_platform_fee"
                                                        name="exclusive_platform_fee" checked="checked"
                                                        onchange="com_jgive.UI.Donation.changePlatformFee();" />
                                                    <?php
                                                    echo Text::_("COM_JGIVE_PLATFORM_FEE_PART1") ?>
                                                    <span id="platform_fee"><?php echo $predefinedPlatformFee ?></span>
                                                    <?php echo $this->currencySymbol ?>
                                                <?php echo Text::_("COM_JGIVE_PLATFORM_FEE_PART2");
                                                }
                                                ?>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>

                                <?php
                                if (count($this->cdata['givebacks'])) {
                                    $givebackDescription = '';

                                    if ($this->givebacksAvailable) {
                                ?>
                                        <div class="col-xs-12">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-6">
                                                    <div class="form-group">
                                                        <label class="col-sm-3 col-xs-12 control-label " for="no_giveback"
                                                            title="<?php echo Text::_('COM_JGIVE_NO_GIVEBACK');
                                                                    ?>">
                                                            <?php echo Text::_('COM_JGIVE_NO_GIVEBACK'); ?>
                                                        </label>
                                                        <div class="col-sm-9 col-xs-12 ">
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" id="no_giveback" name="no_giveback"
                                                                    value="1" onchange="jgive.donations.noGiveBack()"> &nbsp;
                                                                <?php echo Text::_('COM_JGIVE_THANKS_MSG'); ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div id="hide_giveback" class="form-group">
                                                    <div class="col-xs-12 col-md-6 af-pr-0">
                                                        <label class="col-sm-3 col-xs-12 control-label" for="givebacks"
                                                            title="<?php echo Text::_('COM_JGIVE_GIVEBACKS_TOOLTIP'); ?>">
                                                            <?php echo Text::_('COM_JGIVE_GIVEBACK'); ?>
                                                        </label>
                                                        <div class="col-sm-9 col-xs-12 af-pr-0">
                                                            <select name="givebacks" id="givebacks"
                                                                onchange="jgive.donations.populateGiveback()" class="af-w-100">
                                                                <option value="0" selected="selected">
                                                                    <?php echo Text::_('COM_JGIVE_DONATION_SELECT_GIVEBACK'); ?>
                                                                </option>
                                                                <?php
                                                                foreach ($this->cdata['givebacks'] as $giveback) {
                                                                    if ($giveback->sold_giveback < $giveback->total_quantity) {
                                                                        $selectedGiveback = '';

                                                                        if ($this->giveback_id == $giveback->id) {
                                                                            $givebackDescription = $giveback->description;
                                                                            $selectedGiveback = 'selected = "selected"';
                                                                        }
                                                                ?>
                                                                        <option value="<?php echo $giveback->id; ?>"
                                                                            <?php echo $selectedGiveback; ?>>
                                                                            <?php echo (!empty($giveback->title)) ? htmlspecialchars($giveback->title, ENT_COMPAT, 'UTF-8') : $this->jgiveFrontendHelper->getFormattedPrice($giveback->amount); ?>
                                                                        </option>
                                                                <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-md-6 af-pl-30">
                                                        <div id="hide_giveback_desc" class="af-d-none">
                                                            <div>
                                                                <div id="giveback_des" class="well">
                                                                    <?php echo Text::_('COM_JGIVE_GIVEBACK_DESC') . " : " . htmlspecialchars($givebackDescription, ENT_COMPAT, 'UTF-8'); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                }

                                if ($this->params->get('recurring_donation')) {
                                    ?>
                                    <?php
                                    if ($show_field == 1 || $donation_type == 0) {
                                    ?>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="row">
                                                <div class="form-group">
                                                    <label class="col-sm-3 col-xs-12 control-label" title="<?php
                                                                                                            echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATION_TYPE_TOOLTIP') : Text::_('COM_JGIVE_INVESTMENT_TYPE_TOOLTIP'));
                                                                                                            ?>">
                                                        <?php echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATATION_TYPE') : Text::_('COM_JGIVE_INVESTMENT_TYPE')); ?>
                                                    </label>
                                                    <div class="col-sm-9 col-xs-12">
                                                        <label class="radio-inline">
                                                            <input type="radio" name="donation_type" id="donation_one_time"
                                                                value="0" checked
                                                                onclick="com_jgive.UI.Donation.recurDonation(0, gatewayArr)">
                                                            <?php echo Text::_('COM_JGIVE_ONE_TIME'); ?>
                                                        </label>
                                                        <label class="radio-inline">
                                                            <input type="radio" name="donation_type" id="donation_recurring"
                                                                value="1"
                                                                onclick="com_jgive.UI.Donation.recurDonation(1, recurringGateways)">
                                                            <?php echo Text::_('COM_JGIVE_RECURRING'); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div id="recurring_freq_div" class="form-group jgive_display_none">
                                                <label class="col-sm-3 col-xs-12 control-label" for="recurring_freq" title="<?php echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATE_RECR_TYPE_TOOLTIP') : Text::_('COM_JGIVE_INVEST_RECR_TYPE_TOOLTIP'));
                                                                                                                            ?>">
                                                    <?php echo Text::_('COM_JGIVE_RECURRING_TYPE'); ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <select name="recurring_freq" id="recurring_freq" class="af-w-100">
                                                        <option value="D"><?php echo Text::_('COM_JGIVE_RECUR_DAILY'); ?>
                                                        </option>
                                                        <option value="W"><?php echo Text::_('COM_JGIVE_RECUR_WEEKLY'); ?>
                                                        </option>
                                                        <option value="M"><?php echo Text::_('COM_JGIVE_RECUR_MONTHLY'); ?>
                                                        </option>
                                                        <option value="Y"><?php echo Text::_('COM_JGIVE_RECUR_ANNUALLY'); ?>
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!--recurring times -->
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div id="recurring_count_div" class="form-group jgive_display_none">
                                                <label class="col-sm-3 col-xs-12 control-label" for="recurring_count" title="<?php echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATION_RECUR_TIMES_TOOLTIP') : Text::_('COM_JGIVE_INVESTMENT_RECUR_TIMES_TOOLTIP'));
                                                                                                                                ?>">
                                                    <?php echo Text::_('COM_JGIVE_RECUR_TIMES'); ?> *
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <div class="input-group input-group">
                                                        <input type="number" id="recurring_count" name="recurring_count"
                                                            onblur='jgive.donations.validateAmount(id,"<?php echo Text::_('COM_JGIVE_INVALID_RECURRING_TIMES'); ?>")'
                                                            class="validate-numeric" placeholder="<?php
                                                                                                    echo Text::_('COM_JGIVE_RECUR_TIMES');
                                                                                                    ?>" min="2">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($this->params->get('vat_for_donor')) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" for="vat_number"
                                                    title="<?php echo Text::_('COM_JGIVE_VAT_NUMBER_TOOLTIP') ?>">
                                                    <?php echo Text::_('COM_JGIVE_VAT_NUMBER'); ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <div class="input-group input-group">
                                                        <input type="text" id="vat_number" name="vat_number"
                                                            placeholder="<?php echo Text::_('COM_JGIVE_VAT_NUMBER'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($this->campaignFlag && ($show_field == 1 || $donation_anonym == 0)) {
                                ?>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-3 col-xs-12 control-label" title="<?php
                                                                                                        echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATE_ANONYMOUSLY_TOOLTIP') : Text::_('COM_JGIVE_INVEST_ANONYMOUSLY_TOOLTIP'));
                                                                                                        ?>">
                                                    <?php echo (($this->campaignFlag) ? Text::_('COM_JGIVE_DONATE_ANONYMOUSLY') : Text::_('COM_JGIVE_INVEST_ANONYMOUSLY'));
                                                    ?>
                                                </label>
                                                <div class="col-sm-9 col-xs-12">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="annonymousDonation"
                                                            id="annonymousDonation1" value="1">
                                                        <?php echo Text::_('COM_JGIVE_YES'); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="annonymousDonation"
                                                            id="annonymousDonation2" value="0" checked>
                                                        <?php echo Text::_('COM_JGIVE_NO'); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }

                                if ($this->params->get('terms_condition') && $this->params->get('payment_terms_article')) {
                                    $link = Route::_(
                                        Uri::root() . "index.php?option=com_content&view=article&id=" . $this->params->get('payment_terms_article') . "&tmpl=component"
                                    );
                                ?>
                                    <div class="form-group">
                                        <div class="col-xs-12 col-sm-12">
                                            <label class="checkbox-inline">
                                                <input type="checkbox" id="terms_condition" name="terms_condition" />
                                                <?php echo Text::_('COM_JGIVE_DONATION_ACCEPT_USER_TERMS_CONDITIONS_FIRST'); ?>
                                                <a rel="{handler: 'iframe', size: {x: 600, y: 600}}"
                                                    href="<?php echo $link; ?>" class="modal jgive-bs3-modal" />
                                                <?php echo Text::_('COM_JGIVE_DONATION_PRIVACY_POLICY'); ?>
                                                </a>
                                                <?php echo Text::_('COM_JGIVE_DONATION_ACCEPT_USER_TERMS_CONDITIONS_LAST'); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <div class="col-md-5 col-sm-9 col-xs-12">
                                                <input type="button" class="btn btn-primary" id="button-billing-info"
                                                    value="<?php echo Text::_('COM_JGIVE_CONTINUE'); ?>"
                                                    onclick="validatedatainsteps(this,'<?php echo "payment-info-tab" ?>',3,'billing-info')">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END OF billing_info_tab-->

                    <!--Start of Select Payment method  -->
                    <div id="payment-info" class="jgive-checkout-steps form-horizontal row">
                        <div class="checkout-heading border-gray af-mb-15 af-p-10 alert-info">
                            <strong><?php echo Text::_('COM_JGIVE_PAY_METHODS_TAB'); ?></strong>
                        </div>
                        <div class="checkout-content" id="payment-info-tab">
                            <div id="payment_info-tab-method">
                                <div class="form-group">
                                    <div id="gatewaysContent" class="col-md-9 col-sm-9 col-xs-12"></div>
                                </div>

                                <input type="button" class="btn btn-primary" name="save" id="save"
                                    value="<?php echo Text::_('COM_JGIVE_CONTINUE_CONFIRM_FREE'); ?>"
                                    onclick="jGive_submitbutton('save');">

                                <input type="hidden" name="cid" id="cid"
                                    value="<?php echo $this->input->get('cid', '', 'INT'); ?>" />
                                <input type="hidden" name="option" value="com_jgive" />
                                <input type="hidden" name="view" value="donations" />
                                <input type="hidden" name="Itemid"
                                    value="<?php echo $this->input->get('Itemid', '', 'INT'); ?>" />
                                <input type="hidden" name="order_id" id="order_id" value="0" />
                                <input type="hidden" name="platform_fee" id="platform_fee_value" value="0" />
                                <input type="hidden" name="task" value="donations.placeOrder" />
                                <input type="hidden" name="userid" id="userid"
                                    value="<?php echo ($this->user->id) ? $this->user->id : '0'; ?>" />
                                <div class="clearfix">&nbsp;</div>
                                <?php echo HTMLHelper::_('form.token'); ?>
                            </div>
                        </div>
                    </div>
                    <!--EOF Select Payment method  -->
                </form>

                <!-- start confirm order -->
                <div id="confirm-order" class="jgive-checkout-steps row">
                    <div class="checkout-heading border-gray af-mb-15 af-p-10 alert-info">
                        <strong><?php echo Text::_('COM_JGIVE_PAYMENT_INFO'); ?></strong>
                        <span class="pull-right" id="jgive_order_details_tab">
                            <a href="javascript:void('0');"
                                onclick="jgive.donations.jgive_toggleOrder('order_summary_tab_table')"></a>
                        </span>
                    </div>
                    <div class="checkout-content" id="payment_tab">
                        <div id="order_summary_tab_table" class="table table-striped- table-hover">
                            <div id="order_summary_tab_table_html"></div>
                        </div>

                        <!--start of payment tab-->
                        <div id="payment_tab_table">
                            <div class="checkout-heading  border-gray af-mb-15 af-p-10 alert-info">
                                <strong><?php echo Text::_('COM_JGIVE_PAY_FORM'); ?></strong>
                            </div>
                            <div id="payment_tab_table_html" class=""></div>
                        </div>
                        <!--end of payment tab-->
                    </div>
                </div>
                <!-- EOF of confirm order -->
            </div>
        </div>
    </div>
</div>
<!-- EOF of tjBs3 -->

<script>
    // Javascript global variable
    var decimal_separator = "<?php echo $this->params->get('amount_separator'); ?>";
    
</script>