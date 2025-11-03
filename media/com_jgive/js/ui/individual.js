"use strict";
window.com_jgive.UI.Individual = {
    validateEmail: function (email, vendor_id, isAdmin, id, token, cb) {

        if (typeof cb !== 'function') {
            cb = (response) => { };
        }

        var callback = function (error, response) {
            if (error) {
                console.error(error);
            } else {

                // When email found in individuals tables
                if (Number(response.data) === 0) {
                    document.getElementById('emailcheck').style.display = "block";
                    cb(false);
                }
                if (Number(response.data) !== 0) {
                    document.getElementById('emailcheck').style.display = "none";

                    // When email not found in individuals table, but found in users table.
                    if (Number(response.data) === 2) {
                        document.getElementById('emailcheck2').style.display = "block";
                        document.getElementById('jform_create_user').checked = true;
                        document.getElementById('create_user_group').style.display = "none";
                        cb(true);
                    }

                    // When email not found in individuals as well as in users table.
                    if (Number(response.data) === 1) {
                        document.getElementById('emailcheck2').style.display = "none";
                        document.getElementById('create_user_group').style.display = "block";
                        cb(true);
                    }
                }
            }
        };
        window.com_jgive.Services.Individual.getEmailValidate(email, vendor_id, isAdmin, id, token, callback);
    },
    checkBlankEmail: function () {
        var createuser = document.getElementById('jform_create_user');
        var emailVal = jQuery("#jform_email").val();
        if (createuser.checked && emailVal === "") {
            alert(Joomla.JText._('COM_JGIVE_BLANK_EMAIL_ALERT'));
        }
    },
    otherCity: function (other_city_check, other_city_value) {

        if (Number(other_city_check) === 1) {
            document.getElementById('jform_other_city_check').checked = true;
            window.com_jgive.UI.Individual.otherCityToggle();

            /** global: other_city_value */
            document.getElementById('jform_other_city_value').value = other_city_value;
        }
    },
    otherCityToggle: function () {
        if (document.adminForm.jform_other_city_check.checked === true) {
            jQuery("#other_city_value_group").show();
            jQuery("#city_group").hide();
        }
        else {
            jQuery("#other_city_value_group").hide();
            jQuery("#city_group").show();
        }
    },
    CheckFormOnSubmit: function (action) {

        /** global: jform_email */
        var email = jQuery("#jform_email").val();

        /** global: jform_phone */
        var phone = jQuery("#jform_phone").val();
        
        var vendor_id = jQuery("#jform_vendor_id").val();

        /** global: jform_id */
        var id = jQuery("#jform_id").val();

        const token = jQuery("#token").val();

        if (email === "" && phone === "") {
            jQuery("#bank_email_phone_alert_group").show();
            return false;
        }
        if (email === "") {
            Joomla.submitform(action);
            return true;
        }

        if (email !== "") {
            var cb = function (res) {
                if (res === true) {
                    Joomla.submitform(action);
                    return undefined;
                }
                return false;
            }
            window.com_jgive.UI.Individual.validateEmail(email, vendor_id, 1, id, token, cb);
            return true;
        }
        return false;
    },
    hideUserCheckbox: function (user_id) {
        document.getElementById('jform_user_id').value = user_id;
        if (Number(user_id) !== 0) {
            document.getElementById('jform_create_user').checked = "true";
            document.getElementById('create_user_group').style.display = "none";
            document.getElementById('create_user_alert_group').style.display = "none";
        }
    },
    init: (function () { })(),
};
