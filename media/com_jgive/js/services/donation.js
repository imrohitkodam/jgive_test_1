/*
 * @version    SVN:<SVN_ID>
 * @package    com_jgive
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
'use strict';

/** global: com_jgive */
com_jgive.Services.Donation = new (com_jgive.Services.Base.extend({
    getDonationUrl: window.tjSiteRoot + "administrator/index.php?option=com_jgive&format=json&task=receipttemplate.generateReceipt",
    getPlatformFeeUrl: window.tjSiteRoot + "index.php?option=com_jgive&format=json&task=donations.updatePlatformFee",

    config: {
        headers: {}
    },
    response: {
        "success": "",
        "message": ""
    },
    generateReceipt: function (donationId, token, callback) {
        var url;

        if (donationId) {
            url = this.getDonationUrl + "&donationId=" + donationId + '&' + token + '=1';
        } else {
            this.response.success = false;
            this.response.message = Joomla.JText._('COM_JGIVE_ERROR_NULL_DONATION_ID');
            callback(this.response);

            return false;
        }

        return this.get(url, this.config, callback);
    },
    updatePlatformFee: function (donationAmount, campaignId, callback) {
        var url;

        if (donationAmount > 0) {
            url = this.getPlatformFeeUrl + "&donationAmount=" + donationAmount + "&campaignId=" + campaignId;
        } else {
            this.response.success = false;
            this.response.message = Joomla.JText._('COM_JGIVE_ERROR_NULL_DONATION_ID');
            callback(this.response);

            return false;
        }

        return this.get(url, this.config, callback);
   }
}));
