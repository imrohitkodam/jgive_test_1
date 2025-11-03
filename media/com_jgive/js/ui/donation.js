"use strict";
/** global: com_jgive */

window.com_jgive.UI.Donation = {
    contact: function () {
        jQuery('#bloodhound .typeahead').typeahead({
            highlight: true,
        },
            {
                name: "Organizations",
                display: 'name',
                limit: 5,
                source: function (query, syncResults, asyncResults) {

                    var cb = function (err, res) {

                        res = res.data;

                        res.forEach(function (entry) {
                            entry.type = 'org';
                            if(entry.email != "")
                            {
                                entry.name = entry.name + " (" + entry.email + ")";
                            }
                            else{
                                entry.name = entry.name;
                            }

                        });

                        return asyncResults(res);
                    }

                    window.com_jgive.Services.Organization.getOrgs(query, cb);
                },
                templates: {
                  header: '<h3 class="league-name">' + Joomla.JText._('COM_JGIVE_MATCHING_ORGANIZATIONS') + '</h3>'
                }
            },
            {
                name: "Individuals",
                display: 'name',
                limit: 5,
                source: function (query, syncResults, asyncResults) {

                    var cb = function (err, res) {

                        res = res.data;

                        res.forEach(function (entry) {
                                entry.type = 'ind';
                                if (entry.email !== "") {
                                    entry.name = entry.first_name + " " + entry.last_name + " (" + entry.email + ")";
                                }
                                else if (entry.phone !== "") {
                                    entry.name = entry.first_name + " " + entry.last_name + " (" + entry.phone + ")";
                                }
                                else{
                                    entry.name = entry.first_name + " " + entry.last_name;
                                }
                        });

                        return asyncResults(res);
                    }

                    window.com_jgive.Services.Individual.getInds(1, 0, query, cb);

                },
                templates:
                {
                header: '<h3 class="league-name">' + Joomla.JText._('COM_JGIVE_MATCHING_INDIVIDUALS') + '</h3>'
                }
            });

        jQuery('#bloodhound .typeahead').bind('typeahead:select', function (ev, suggestion) {
            suggestion.id = suggestion.type + '.'.concat(suggestion.id);
            document.getElementById("contact_id").value = suggestion.id;
            var res = suggestion.id.substr(0, 3);
            if(res === 'org')
            {
                document.getElementById("contact_individual_msg").style.display = "none";
                document.getElementById("contact_organization_msg").style.display = "block";
            }
            else
            {
                document.getElementById("contact_organization_msg").style.display = "none";
                document.getElementById("contact_individual_msg").style.display = "block";
            }


        });
    },
    generateReceipt: function(donationId, token) {
		 var callback = function (error, res) {
			if (error)
			{
				console.error(error);
			}
			else
			{
				var url = window.tjSiteRoot + 'administrator/index.php?option=com_jgive&view=donation&layout=generate_certificate&donationid='+donationId+'&tmpl=component';

				if (typeof SqueezeBox === 'undefined')
				{
					var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
					var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
					var screenWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
					var screenHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

					var left = ((screenWidth / 2) - ((window.innerWidth-250) / 2)) + dualScreenLeft;
					var top = ((screenHeight / 2) - ((window.innerHeight-150) / 2)) + dualScreenTop;

					var newWindowSize = 'width='+(window.innerWidth-250)+','+'height='+(window.innerHeight-150)+','+'top='+top+','+'left='+left;
					window.open(url, '', newWindowSize);
				}
				else
				{
					SqueezeBox.open(url ,{handler: 'iframe', size: {x: window.innerWidth-250, y: window.innerHeight-150}});
				}
			}
		};

		/** global: com_jgive */
		com_jgive.Services.Donation.generateReceipt(donationId, token, callback);
    },

	recurDonation: function (radioOption, gatewayArr){
		var paymentPluginList = '';

		if (gatewayArr.length === 0)
		{
			paymentPluginList = Joomla.JText._("COM_JGIVE_NO_PAYMENT_GATEWAY");
		}
		else
		{
			var checkAttribute  = "checked='checked'";
			for (var index = 0; index < gatewayArr.length; index++)
			{
				paymentPluginList += "<div class='radio col-xs-12 col-sm-6 col-md-12'><label><input type='radio' name='gateways' id='"+gatewayArr[index].id+"' value='"+gatewayArr[index].id+"' aria-label='...' "+checkAttribute+"> "+gatewayArr[index].name+"</label></div>";

				checkAttribute = "";
			}

		}

		jQuery('#gatewaysContent').html(paymentPluginList);

		if (radioOption === 0)
		{
			jQuery('#recurring_count').removeClass('required');
			jQuery('#recurring_count').removeClass('invalid');
			jQuery('#recurring_freq_div').hide('slow');
			jQuery('#recurring_count_div').hide('slow');
		}
		else if (radioOption === 1)
		{
			techjoomla.jQuery('#recurring_count').addClass('required');
			techjoomla.jQuery('#recurring_freq_div').show('slow');
			techjoomla.jQuery('#recurring_count_div').show('slow');
		}
	},

	updatePlatformFee: function(element, campaignId){
		let donationAmount = jQuery(element).val();
		campaignId = campaignId ? campaignId : jQuery("#campaign_id").val();

		if (donationAmount > 0){
			var callback = function (error, res){
				if (error)
				{
					console.error(error);
				}
				else
				{
					var PlatformFeeCheckbox = document.getElementById("exclusive_platform_fee");
					var updatedPlatformFee = res.data.platformFee;

					if (PlatformFeeCheckbox != null && PlatformFeeCheckbox.checked === true)
					{
						jQuery('#platform_fee_value').val(updatedPlatformFee);
						if (jQuery("#platform_fee").length)
						{
							document.getElementById("platform_fee").innerHTML = updatedPlatformFee;
						}
					}
					else if (PlatformFeeCheckbox == null)
					{
						jQuery('#platform_fee_value').val(updatedPlatformFee);
						if (jQuery("#platform_fee").length)
						{
							document.getElementById("platform_fee").innerHTML = updatedPlatformFee;
						}
					}
					else if (PlatformFeeCheckbox.checked === false)
					{
						jQuery('#platform_fee_value').val(0);
						if (jQuery("#platform_fee").length)
						{
							document.getElementById("platform_fee").innerHTML = 0;
						}
					}

					/** global: localStorage */
					localStorage.setItem("platform_fee", updatedPlatformFee);
				}
			};

			/** global: com_jgive */
			com_jgive.Services.Donation.updatePlatformFee(donationAmount, campaignId, callback);
		}
	},
	changePlatformFee: function(){
		var PlatformFeeCheckbox = document.getElementById("exclusive_platform_fee");

		if (PlatformFeeCheckbox.checked === false)
		{
			document.getElementById("platform_fee").innerHTML = 0;
			jQuery("#platform_fee_value").val(0);
		}
		else
		{
			/** global: localStorage */
			document.getElementById("platform_fee").innerHTML = localStorage.getItem("platform_fee");
			jQuery("#platform_fee_value").val(localStorage.getItem("platform_fee"));
		}
	}
}
