<?php
/**
 * @package    JGive_Donor
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2011-2015 Techjoomla. All rights reserved.
 * @license    GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Include TJReport Model
JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Campaigns report plugin of TJReport
 *
 * @since  1.0.0
 */
class TjreportsModelJgivedonors extends TjreportsModelReports
{
	protected $default_order = 'd.first_name';

	protected $default_order_dir = 'ASC';

	public $columns;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		$lang     = Factory::getLanguage();
		$base_dir = JPATH_SITE . '/administrator';
		$lang->load('com_jgive', $base_dir);

		$this->columns = array(
			'name' => array('table_column' => 'u.name', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_NAME'),
			'donoremail' => array('table_column' => 'd.email', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_EMAIL'),
			'donorphone' => array('table_column' => 'd.phone', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_PHONE_NUMBER'),
			'campaign_id' => array('table_column' => '', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_DONOTED_TO_NUMBER_OF_CAMPAIGNS'),
			'address' => array('table_column' => 'd.address', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_DONOR_ADDRESS'),
			'totaldonatedamount' => array('table_column' => '', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_DONOR_PAID_AMOUNT'),
			'donor_type' => array('table_column' => 'd.donor_type', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_DONOR_TYPE'),
			'org_name' => array('table_column' => 'd.org_name', 'title' => 'PLG_TJREPORTS_JGIVE_ORG_NAME')
		);

		parent::__construct($config);
	}

	/**
	 * Get client of this plugin
	 *
	 * @return array<string,mixed|string> Plugin Details
	 *
	 * @since   2.0
	 * */
	public function getPluginDetail()
	{
		return $detail = array('client' => 'com_jgive', 'title' => Text::_('PLG_TJREPORTS_JGIVEDONORS'));
	}

	/**
	 * Create an array of filters
	 *
	 * @return    ARRAY Filters used in reports
	 *
	 * @since    1.0
	 */
	public function displayFilters()
	{
		JLoader::import('components.com_jgive.helpers.tjreports', JPATH_SITE);
		$jGiveTjreportsHelper = new JGiveTjreportsHelper;
		$donorsArray = $jGiveTjreportsHelper->getDonors();
		$donorsOptions = $jGiveTjreportsHelper->getFilterOptions($donorsArray);
		$typeOfDonor = $jGiveTjreportsHelper->getFilterOptions($donorsArray);
		$filters = $this->getState('filters');

		if (empty($filters['donor_type']))
		{
			$filters['donor_type'] = '';
		}

		if (empty($filters['typeOfDonor']))
		{
			$filters['typeOfDonor'] = '';
		}

		$dispFilters = array(
			array(
				'donorname' => array(
					'search_type' => 'select', 'select_options' => $donorsOptions, 'searchin' => 'd.first_name'),
				'donoremail' => array(
					'search_type' => 'text', 'searchin' => 'd.email'),
			),
			array(
				'typeOfDonor' => array(
					'search_type' => 'select', 'select_options' => $typeOfDonor,'searchin' => 'd.donor_type'),
			)
		);

		$statusList = array();
		$statusList[] = JHTML::_('select.option', '', Text::_('PLG_TJREPORTS_JGIVE_FILTER_SELECT_OPTION'));
		$statusList[] = JHTML::_('select.option', 'guest', Text::_('PLG_TJREPORTS_JGIVE_FILTER_SELECT_OPTION_GUEST'));
		$statusList[] = JHTML::_('select.option', 'registered', Text::_('PLG_TJREPORTS_JGIVE_FILTER_SELECT_OPTION_REGISTERED'));

		$filterHtml = HTMLHelper::_('select.genericlist', $statusList, 'filters[donor_type]',
				'class="filter-input input-medium" size="1" ' .
				'onchange="tjrContentUI.report.submitTJRData();"',
				'value', 'text', $filters['donor_type']
			);

		$typeOfDonorList = array();
		$typeOfDonorList[] = JHTML::_('select.option', '', Text::_('PLG_TJREPORTS_JGIVE_DONORSREPORT_DONOR_TYPE'));
		$typeOfDonorList[] = JHTML::_('select.option', 'org', Text::_('PLG_TJREPORTS_JGIVE_ORGANIZATION'));
		$typeOfDonorList[] = JHTML::_('select.option', 'ind', Text::_('PLG_TJREPORTS_JGIVE_INDIVIDUAL'));

		$donorTypefilterHtml = HTMLHelper::_('select.genericlist', $typeOfDonorList, 'filters[typeOfDonor]',
				'class="filter-input input-medium" size="1" ' .
				'onchange="tjrContentUI.report.submitTJRData();"',
				'value', 'text', $filters['typeOfDonor']
			);

		$dispFilters[1] = array('donor_type' => array( 'search_type' => 'html', 'html' => $filterHtml),
		'typeOfDonor' => array( 'search_type' => 'html', 'html' => $donorTypefilterHtml));

		return $dispFilters;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$user = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');
		$db        = $this->_db;
		$query     = parent::getListQuery();
		$colToshow = (array) $this->getState('colToshow');
		$filters   = $this->getState('filters');

		$query->select('COUNT(d.campaign_id) as campaign_id');
		$query->select('SUM(o.original_amount) as totaldonatedamount');
		$query->select('u.name');
		$query->from($db->quoteName('#__jg_donors', 'd'));
		$query->join('LEFT', $db->quoteName('#__jg_orders', 'o') . ' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('o.donor_id') . ')');
		$query->join('LEFT', $db->quoteName('#__jg_campaigns', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('o.campaign_id') . ')');
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('d.user_id') . ')');
		$query->where($db->quoteName('o.status') . '=' . $db->quote('C'));

		if (!$isSuperUser)
		{
			$utilityClass = JGive::utilities();
			$vendorId = $utilityClass->getVendorId($user->id, "com_jgive");
			$query->where($db->quoteName('c.vendor_id') . ' = ' . (int) $vendorId);
			$query->where($db->quoteName('c.vendor_id') . ' <> ' . (int) 0);
		}

		// Check here if Admin set filters
		if (!empty($filters['donor_type']))
		{
			if ($filters['donor_type'] == 'registered')
			{
				$query->where($db->quoteName('d.user_id') . '<> ' . 0);
				$query->group($db->quoteName('d.user_id'));
			}
			elseif ($filters['donor_type'] == 'guest')
			{
				$query->where($db->quoteName('d.user_id') . '= ' . (int) 0);
				$query->group($db->quoteName('d.email'));
			}
		}
		// If Admin does not any filters then show all records group by email id
		else
		{
			if (!empty($filters['typeOfDonor']))
			{
				$query->where($db->quoteName('d.donor_type') . '= ' . $db->quote($filters['typeOfDonor']));
			}

			$query->group($db->quoteName('d.email'));
		}

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		$newItems = array();
		$items = parent::getItems();

		JLoader::import('components.com_jgive.helper', JPATH_SITE);
		$jGiveFrontendHelper = new JgiveFrontendHelper;

		if (is_array($items))
		{
			foreach ($items as $item)
			{
				$item['name']               = !empty($item['name']) ? $item['name'] : Text::_('PLG_TJREPORTS_JGIVE_DONORSREPORT_GUEST_USERS');
				$item['donorphone']         = !empty($item['donorphone']) ? $item['donorphone'] : "-";
				$item['address']            = !empty($item['address']) ? $item['address'] : "-";
				$item['totaldonatedamount'] = $jGiveFrontendHelper->getFormattedPrice($item['totaldonatedamount']);

				if (!empty($item['org_name']) && $item['donor_type'] == "org")
				{
					$item['name'] = ucwords($item['org_name']);
					$item['donor_type'] = Text::_('PLG_TJREPORTS_JGIVE_ORGANIZATION');
				}
				else
				{
					$item['donor_type'] = Text::_('PLG_TJREPORTS_JGIVE_INDIVIDUAL');
				}

				$item['org_name'] = !empty($item['org_name']) ? ucwords($item['org_name']) : "-";

				$newItems[] = $item;
			}
		}

		return $newItems;
	}
}
