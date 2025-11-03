"use strict";
window.com_jgive.UI.Organization = {
    validateEmail: function (email, vendorId, isAdmin, id, token, cb) {
        if (typeof cb !== 'function') {
            cb = (response) => { };
        }
        var callback = function (error, response) {
            if (error) {
                console.error(error);
            } else {
                if (Number(response.data) === 0) {
                    document.getElementById('emailcheck').style.display = "block";
                    cb(false);
                } else {
                    document.getElementById('emailcheck').style.display = "none";
                    cb(true);
                }
            }
        };
        window.com_jgive.Services.Organization.getEmailValidate(email, vendorId, isAdmin, id, token, callback);
    },
    hideUserCheckbox: function (user_id) {
        document.getElementById('jform_user_id').value = user_id;
        if (Number(user_id) !== 0) {
            document.getElementById('jform_create_user').checked = "true";
            document.getElementById('create_user_group').style.display = "none";
            document.getElementById('create_user_alert_group').style.display = "none";
        }
    },
    CheckFormOnSubmit: function (action) {
        /** global: jform_email */
        var email = document.getElementById('jform_email').value;

        var VendorId = document.getElementById('jform_vendor_id').value;

        /** global: jform_id */
        var id = document.getElementById('jform_id').value;

        const token = jQuery("#token").val();

        if (email === "") {
            Joomla.submitform(action);
            return undefined;
        }

        if (email !== "") {
            var cb = function (res) {
                if (res === true) {
                    Joomla.submitform(action);
                    return undefined;
                }
                return false;

            }
            window.com_jgive.UI.Organization.validateEmail(email, VendorId, 1, id, token, cb);
            return true;
        }
        return false;
    },
    otherCity: function (other_city_check, other_city_value) {
        if (Number(other_city_check) === 1) {
            document.getElementById('jform_other_city_check').checked = true;
            window.com_jgive.UI.Individual.otherCityToggle();
            /** global: other_city_value */
            document.getElementById('jform_other_city_value').value = other_city_value;

        }
    },
    checkBlankEmail: function () {
        var createuser = document.getElementById('jform_create_user');
        var emailVal = document.getElementById('jform_email').value;
        if (createuser.checked && emailVal === "") {
            alert(Joomla.JText._('COM_JGIVE_BLANK_EMAIL_ALERT'));
        }
    },

    contact: function (isAdmin, vendor) {
		 var vendorId = jQuery("#" + vendor).val();
        jQuery('#bloodhound .typeahead').typeahead({
            highlight: true,
        },
		{
			name: "Individuals",
			display: 'name',
			source: function (query, syncResults, asyncResults) {

				var cb = function (err, res) {
					res = res.data;
					res.forEach(function (entry) {
							entry.type = 'ind';
							if (entry.email !== "") {
								entry.name = entry.first_name + " " + entry.last_name + "(" + entry.email + ")";
							}
							else {
								entry.name = entry.first_name + " " + entry.last_name + " (" + entry.phone + ")";
							}
					});

					return asyncResults(res);
				}

				window.com_jgive.Services.Individual.getInds(isAdmin, vendorId, query, cb);
            }
        });

        jQuery('#bloodhound .typeahead').bind('typeahead:select', function (ev, suggestion) {
            suggestion.id = suggestion.type + '.'.concat(suggestion.id);
            document.getElementById("jform_contact_id").value = suggestion.id;
        });
    },
    init: (function () { })(),
};
