/*
 * @version    SVN:<SVN_ID>
 * @package    com_jgive
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
'use strict';

window.com_jgive.Services.Individual = new (window.com_jgive.Services.Base.extend({
	config: {
		headers: {}
	},
	response: {
		"success": "",
		"message": ""
	},

	getEmailValidate: function (email, vendor_id, isAdmin, id, token, callback) {
		var url;
		var urlLink;
		
		if (email !="") {
			urlLink = window.tjSiteRoot + "index.php?option=com_jgive&format=json&task=individualform.Checkemail";

			if (isAdmin)
			{
				urlLink = window.tjSiteRoot + "administrator/index.php?option=com_jgive&format=json&task=individual.Checkemail";
			}
			url = urlLink + "&email=" + ''+email + "&id=" + ''+id + '&vendor_id=' + vendor_id +'&' + token + '=1';
		} else {
			this.response.success = false;
			this.response.message = Joomla.JText._('COM_JGIVE_CONTROLLER_FALSE_RESPONSE');

			callback(this.response);

			return false;
		}

		return this.get(url, this.config, callback);
	},

	getInds: function (isAdmin, vendorId, query, callback)
	{
		var url;
		let urlLink;

		if (query) {
			urlLink = window.tjSiteRoot + "index.php?option=com_jgive&format=json&task=individuals.getIndividuals";

			if (isAdmin)
			{
				urlLink = window.tjSiteRoot + "administrator/index.php?option=com_jgive&format=json&task=individuals.getIndividuals";
			}

			url = urlLink + "&search=" + query + "&published=" + 1 + "&vendorId=" + vendorId;
		} else {
			this.response.success = false;
			this.response.message = Joomla.JText._('COM_JGIVE_SEARCH_NULL');
			callback(this.response);

			return false;
		}

		return this.get(url, this.config, callback);
	}
}));
