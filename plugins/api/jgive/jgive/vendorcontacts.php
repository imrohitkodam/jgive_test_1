<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;


/**
 * Class for getting vendor contacts
 *
 * @package  JGive
 *
 * @since    _DEPLOY_VERSION_
 */
class JgiveApiResourceVendorcontacts extends ApiResource
{
	/**
	 *  API Plugin for get method
	 *
	 * @return  void
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function get()
	{
		$user = Factory::getUser();
		$input    = Factory::getApplication()->input;
		$result = new stdClass;
		$result->results = array();
		$result->empty_message = '';

		$limitstart = $input->get('limitstart', 0, 'INT');
		$limit = $input->get('limit', 10, 'INT');
		$contactType = $input->get('contact_type', '', 'STRING');
		$utilityClass = JGive::utilities();
		$vendorId = $utilityClass->getVendorId($user->id, "com_jgive");

		if (empty($vendorId))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_VENDORID_NOT_FOUND"));
		}

		$individualsModel = JGive::model('individuals', array('ignore_request' => true));
		$individualsModel->setState('list.start', $limitstart);
		$individualsModel->setState('list.limit', $limit);
		$individualsModel->setState('filter.vendor_id', $vendorId);
		$individualsModel->setState('filter.search', $input->get('search', '', 'STRING'));
		$individualsModel->setState('published', 1);
		$individualContacts = $individualsModel->getItems();

		$organizationsModel = JGive::model('organizations', array('ignore_request' => true));
		$organizationsModel->setState('list.start', $limitstart);
		$organizationsModel->setState('list.limit', $limit);
		$organizationsModel->setState('filter.vendor_id', $vendorId);
		$organizationsModel->setState('filter.search', $input->get('search', '', 'STRING'));
		$organizationsModel->setState('published', 1);
		$organizationContacts = $organizationsModel->getItems();

		if ($contactType == 'ind')
		{
			$this->items   = $individualContacts;
			$result->total = $individualsModel->getTotal();
		}
		elseif ($contactType == 'org')
		{
			$this->items   = $organizationContacts;
			$result->total = $organizationsModel->getTotal();
		}
		else
		{
			$this->items = array_merge($individualContacts, $organizationContacts);
			$result->total = $individualsModel->getTotal() + $organizationsModel->getTotal();
		}

		if (empty($this->items))
		{
			$result->empty_message	= Text::_('PLG_API_JGIVE_NO_DATA_FOUND');
			$this->plugin->setResponse($result);

			return;
		}

		$result->results = $this->items;
		$this->plugin->setResponse($result);
	}
}
