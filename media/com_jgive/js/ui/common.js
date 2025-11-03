"use strict";
window.com_jgive.UI.Common = {
    checkWhitespace: function () {
        var flag = 0;
        var x = document.getElementsByClassName("no-whitespace");
        var i;
        for (i = 0; i < x.length; i++) {
            if (x[i].value != "") {
                if (x[i].value.trim() == "") {
                    flag = 1;
                    x[i].style.borderColor = "darkred";
                }
            }
        }
        if (flag === 1) {
            techjoomla.jQuery("#white_space_alert_group").show();
            return false;
        }

        return true;
    },
    printReceipt: function() {
        var printContents       = document.getElementById('printReceipt').innerHTML;
        printContents = printContents.replace("informationBody", "ms-4");
        var originalContents    = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    },

	generateStates: function (countryId, isAdmin, state, city) {
        var jform_state = jQuery('#jform_state');

        if (jform_state.length < 1)
        {
            return;
        }

        jform_state.find('option').remove().end();
        var jform_city = jQuery('#jform_city');
        jform_city.find('option').remove().end();

        jQuery('#jform_state').prepend(jQuery('<option></option>').html(Joomla.JText._("COM_JGIVE_STATE")));
        jQuery('#jform_city').prepend(jQuery('<option></option>').html(Joomla.JText._("COM_JGIVE_CITY")));
        jQuery("#jform_state").trigger("liszt:updated");
        jQuery("#jform_state").trigger("chosen:updated");
        jQuery("#jform_city").trigger("liszt:updated");
        jQuery("#jform_city").trigger("chosen:updated");

        this.generateCitys(countryId, city);

        var country = jQuery("#" + countryId).val();
        var callback = function (error, res) {
            if (error) {
                console.error(error);
            } else if (res.data === undefined || res.data === null || res.data.length <= 0) {
                var op =
                    '<option value="">' + Joomla.JText._("COM_JGIVE_STATE") + "</option>";
                var select = jQuery("#jform_state");
                select
                    .find("option")
                    .remove()
                    .end();
                select.prepend(op);
                jQuery("#jform_state").trigger("liszt:updated");
                jQuery("#jform_state").trigger("chosen:updated");
            } else {
                com_jgive.UI.Common.generateOptions(res.data, countryId, state);
            }
        };

        com_jgive.Services.Common.getStates(country, callback);
    },
    generateCitys: function (countryId, city) {
        var jform_city = jQuery('#jform_city');

        if (jform_city.length < 1)
        {
            return;
        }

        var country = jQuery('#' + countryId).val();
        var callback = function (error, res) {
            if (error) {
                console.error(error);
            } else if (res.data === undefined || res.data === null || res.data.length <= 0) {
                var op = '<option value="">' + Joomla.JText._("COM_JGIVE_CITY") + '</option>';
                var select = jQuery('#jform_city');
                select.find('option').remove().end();
                select.prepend(op);
                jQuery("#jform_city").trigger("liszt:updated");
                jQuery("#jform_city").trigger("chosen:updated");
            } else {
                com_jgive.UI.Common.generateOptionsCitys(res.data, countryId, city);
            }
        };

        com_jgive.Services.Common.getCitys(country, callback);
    },
    generateOptions: function (data, countryId, state) {
        var index, select, region, op;

        if (countryId == 'jform_country')
        {
            select = jQuery('#jform_state');
            select.find('option').remove().end();
        }

        for (index = 0; index < data.length; ++index)
        {
            region = data[index];
            if (state === region.id)
            {
                op = "<option value=" + region.id + " selected='selected'>" + region.region + '</option>';
            }
            else
            {
                op = "<option value=" + region.id + ">" + region.region + '</option>';
            }

            if (countryId == 'jform_country') {
                jQuery('#jform_state').append(op);
            }

            if (index + 1 == data.length) {
                jQuery("#jform_state").trigger("liszt:updated");
                jQuery("#jform_state").trigger("chosen:updated");
            }
        }

        jQuery("#jform_state").trigger("liszt:updated");
        jQuery("#jform_state").trigger("chosen:updated");
    },

    /* Generate options for city */
    generateOptionsCitys: function (data, countryId, cityDefault) {
        var index, select, city, op;

        if (countryId == 'jform_country') {
            select = jQuery('#jform_city');
            select.find('option').remove().end();
        }

        // Generating options for city
        for (index = 0; index < data.length; ++index)
        {
            city = data[index];

            if (cityDefault == city.id)
            {
                op = "<option value=" + city.id + " selected='selected'>" + city.city + '</option>';
            }
            else
            {
                op = "<option value=" + city.id + ">" + city.city + '</option>';
            }
            if (countryId == 'jform_country') {
                jQuery('#jform_city').append(op);
            }

            if (index + 1 === data.length) {
                jQuery("#jform_city").trigger("liszt:updated");
                jQuery("#jform_city").trigger("chosen:updated");
            }
        }

        jQuery("jform_city").trigger("liszt:updated");
        jQuery("jform_city").trigger("chosen:updated");
    },
    
    init: (function() {})(),
    sendEmail: function(task)
    {
        if(document.getElementById("jgive_subject").value == ""  && document.getElementById("jgive_message").value == 0)
        {
            alert(Joomla.JText._("COM_JGIVE_CONFIRM_MSG_FOR_SEND_MAIL_WITHOUT_SUB_AND_TEXT"));
            return false;
        }

        return Joomla.submitform(task);
        
    }    
};
