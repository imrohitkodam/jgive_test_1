/*
 * @version    SVN:<SVN_ID>
 * @package    com_jgive
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
'use strict';

window.com_jgive.Services.Organization = new (window.com_jgive.Services.Base.extend({
getOrgsUrl: window.tjSiteRoot + "administrator/index.php?option=com_jgive&format=json&task=organizations.getOrganizations",

	config: {
		headers: {}
	},
	response: {
		"success": "",
		"message": ""
	},
	getEmailValidate: function (email, vendorId, isAdmin, id, token, callback) {
		var url;
		var urllink;

		if (email !="") {
			urllink = window.tjSiteRoot + "index.php?option=com_jgive&format=json&task=organizationform.Checkemail";

			if(isAdmin)
			{
				urllink = window.tjSiteRoot + "administrator/index.php?option=com_jgive&format=json&task=organization.Checkemail";
			}
			url = urllink + "&email=" + ''+email + "&id=" + ''+id + "&vendor_id=" + vendorId + '&' + token + '=1';
		} else {
			this.response.success = false;
			this.response.message = Joomla.JText._('COM_JGIVE_CONTROLLER_FALSE_RESPONSE');

			callback(this.response);

			return false;
		}

		return this.get(url, this.config, callback);
	},

	getOrgs: function (query, callback)
	{
		var url;

		if (query) {
			url = this.getOrgsUrl + "&search=" + query + "&published=" + 1;
		} else {
			this.response.success = false;
			this.response.message = Joomla.JText._('COM_JGIVE_SEARCH_NULL');

			callback(this.response);

			return false;
		}

	  return this.get(url, this.config, callback);
	}

}));
