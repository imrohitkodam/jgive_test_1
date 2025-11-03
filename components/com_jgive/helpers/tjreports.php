<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

include_once  JPATH_SITE . '/components/com_jgive/includes/jgive.php';

/**
 * Class JGiveTjreportsHelper
 *
 * @since  2.1
 */
class JGiveTjreportsHelper
{
	public $app;

	/**
	 * Method acts as a consturctor
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		$this->app    = Factory::getApplication();
	}

	/**
	 * Method to get all published Campaigns
	 *
	 * @return campaigns array
	 *
	 * @since   1.6
	 */
	public function getAllCampaigns()
	{
		$user = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('c.id', 'id'));
		$query->select($db->quoteName('c.title', 'value'));
		$query->from($db->quoteName('#__jg_campaigns', 'c'));

		if (!$isSuperUser)
		{
			$query->where($db->quoteName('c.creator_id') . ' = ' . (int) $user->id);
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get Campaign's promoters
	 *
	 * @return promoter array
	 *
	 * @since   1.6
	 */
	public function getCampaignsPromoter()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('u.id'));
		$query->select($db->quoteName('v.vendor_title', 'value'));
		$query->from($db->quoteName('#__jg_campaigns', 'c'));
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('c.creator_id') . ' = ' . $db->quoteName('u.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tjvendors_vendors', 'v') .
		' ON (' . $db->quoteName('v.user_id') . ' = ' . $db->quoteName('u.id') . ')'
		);
		$query->group($db->quoteName('u.id'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get Donors
	 *
	 * @return donor array
	 *
	 * @since   1.6
	 */
	public function getDonors()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('d.first_name', 'id'));
		$query->select($db->quoteName('d.first_name', 'value'));
		$query->from($db->quoteName('#__jg_donors', 'd'));
		$query->join('LEFT', $db->quoteName('#__jg_orders', 'o') . ' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('o.donor_id') . ')');
		$query->where($db->quoteName('o.status') . '=' . $db->quote('C'));
		$query->group($db->quoteName('d.first_name'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to filter Values in different format
	 *
	 * @param   ARRAY   $filterOptions  Construct options array
	 * @param   STRING  $defaultOption  Set custom default option
	 *
	 * @return campaigns array select filter format
	 *
	 * @since   1.6
	 */
	public function getFilterOptions($filterOptions, $defaultOption='')
	{
		if (!empty($defaultOption))
		{
			$filterValues[] = JHTML::_('select.option', '', $defaultOption);
		}
		else
		{
			$filterValues[] = JHTML::_('select.option', '', Text::_('PLG_TJREPORTS_JGIVE_FILTER_SELECT_OPTION'));
		}

		if (!empty($filterOptions))
		{
			foreach ($filterOptions as $eachOption)
			{
				$filterValues[] = JHTML::_('select.option', $eachOption->id, (isset($eachOption->value)? $eachOption->value: $eachOption->id));
			}
		}

		return $filterValues;
	}

	/**
	 * Method to get order ids
	 *
	 * @return donor array
	 *
	 * @since   1.6
	 */
	public function getOrders()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('order_id', 'id'));
		$query->from($db->quoteName('#__jg_orders'));
		$query->where($db->quoteName('status') . '=' . $db->quote('C'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get list of Individual donor
	 *
	 * @return donor array
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function getIndividualDonors()
	{
		$user = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select("CONCAT(i.first_name, ' ', i.last_name) AS 'value'");
		$query->select("CONCAT('%',i.first_name, ' ', i.last_name,'%') AS 'id'");
		$query->from($db->quoteName('#__jg_individuals', 'i'));

		if (!$isSuperUser)
		{
			$utilityClass = JGive::utilities();
			$vendorId = $utilityClass->getVendorId($user->id, "com_jgive");

			if ($vendorId)
			{
				$query->where($db->quoteName('i.vendor_id') . ' = ' . (int) $vendorId);
			}
		}

		$query->order($db->quoteName('i.first_name') . ' ASC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get campaign categories
	 *
	 * @return categories array
	 *
	 * @since   1.6
	 */
	public function getCategories()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id', 'id'));
		$query->select($db->quoteName('title', 'value'));
		$query->from($db->quoteName('#__categories'));
		$query->where($db->quoteName('extension') . ' =' . $db->quote('com_jgive'));
		$query->where($db->quoteName('published') . ' = 1');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get list of organizations
	 *
	 * @return array
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function getOrganizationList()
	{
		$user = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('name', 'id'));
		$query->from($db->quoteName('#__jg_organizations'));

		if (!$isSuperUser)
		{
			$utilityClass = JGive::utilities();
			$vendorId = $utilityClass->getVendorId($user->id, "com_jgive");

			if ($vendorId)
			{
				$query->where($db->quoteName('#__jg_organizations.vendor_id') . ' = ' . (int) $vendorId);
			}
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get Promoters
	 *
	 * @return array
	 *
	 * @since  DEPLOY_VERSION
	 */
	public function getPromoters()
	{
		$user = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('u.name', 'id'));
		$query->from($db->quoteName('#__jg_campaigns', 'c'));
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('c.creator_id') . ' = ' . $db->quoteName('u.id') . ')');

		if (!$isSuperUser)
		{
			$query->where($db->quoteName('c.creator_id') . ' = ' . (int) $user->id);
		}

		$query->group($db->quoteName('u.name'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
