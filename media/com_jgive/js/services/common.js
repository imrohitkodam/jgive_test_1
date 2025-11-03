/*
 * @version    SVN:<SVN_ID>
 * @package    com_jgive
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
'use strict';
/** global: com_jgive */
com_jgive.Services.Common = new (com_jgive.Services.Base.extend({
    getStatesUrl: window.tjSiteRoot + "index.php?option=com_jgive&format=json&task=campaign.getState",
    getCitysUrl: window.tjSiteRoot + "index.php?option=com_jgive&format=json&task=campaign.getCity",
    getJoomlaUsersUrl: window.tjSiteRoot + "administrator/index.php?option=com_jgive&task=individuals.getUsers",
    config: {
        headers: {}
    },
    response: {
        "success": "",
        "message": ""
    },
    getStates: function (country, callback) {
        let url;

        if (country) {
            url = this.getStatesUrl + "&country=" + country;
        } else {
            this.response.success = false;

            this.response.message = Joomla.JText._('COM_JGIVE_ERROR_NULL_COUNTRY');
            callback(this.response);

            return false;
        }

        return this.get(url, this.config, callback);
    },
    getCitys: function (country, callback) {
        let url;

        if (country) {
            url = this.getCitysUrl + "&country=" + country;
        } else {
            this.response.success = false;
            this.response.message = Joomla.JText._('COM_JGIVE_ERROR_NULL_COUNTRY');
            callback(this.response);

            return false;
        }

        return this.get(url, this.config, callback);
    },
    getJoomlaUsers: function (query, callback) {
        let url;

        if (query) {
            url = this.getJoomlaUsersUrl + "&search=" + ''+query;
        } else {
            this.response.success = false;
            this.response.message = Joomla.JText._('COM_JGIVE_ERROR_NULL_SEARCH_VALUE');
            callback(this.response);

            return false;
        }

      return this.get(url, this.config, callback);
    }
}));