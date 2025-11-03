<?php
/**
 * @package    JGive_CampaignsPromoter
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009-2017 Techjoomla. All rights reserved.
 * @license    GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Include TJReport Model
JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Campaigns report plugin of TJReport
 *
 * @since  1.0.0
 */
class TjreportsModelJgivecampaignspromoter extends TjreportsModelReports
{
	protected $default_order = 'v.vendor_title';

	protected $default_order_dir = 'ASC';

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
			'promotername' => array('table_column' => 'v.vendor_title', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_PROMOTER_NAME'),
			'promoteremail' => array('table_column' => 'u.email', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_PROMOTER_EMAIL'),
			'promoteraddress' => array('table_column' => 'v.address', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_ADDRESS'),
			'promoterphone' => array('table_column' => 'v.phone_number', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_PHONE_NUMBER'),
			'no_of_campaigns' => array('table_column' => 'c.id', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_NUMBER_OF_CAMPAIGNS'),
			'total_goal_amount' => array('table_column' => 'c.goal_amount', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_TOTAL_GOAL_AMOUNT'),
			'donor_count' => array('table_column' => '', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_TOTAL_DONORS'),
			'country' => array('table_column' => 'con.country', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_COUNTRY'),
			'region' => array('table_column' => 'r.region', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_STATE'),
			'city' => array('table_column' => 'ci.city', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_CITY'),
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
		return $detail = array('client' => 'com_jgive', 'title' => Text::_('PLG_TJREPORTS_JGIVECAMPAIGNSPROMOTER'));
	}

	/**
	 * Create an array of filters
	 *
	 * @return    Boolean|ARRAY Filters used in reports
	 *
	 * @since    1.0
	 */
	public function displayFilters()
	{
		$user = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');
		$dispFilters = array();

		if (!$isSuperUser)
		{
			return $dispFilters;
		}

		JLoader::import('components.com_jgive.helpers.tjreports', JPATH_SITE);
		$jGiveTjreportsHelper = new JGiveTjreportsHelper;
		$promotersArray = $jGiveTjreportsHelper->getPromoters();
		$promotersList = $jGiveTjreportsHelper->getFilterOptions($promotersArray);

		$dispFilters = array(
			array(
				'promotername' => array(
					'search_type' => 'select', 'select_options' => $promotersList, 'searchin' => 'v.vendor_title'),
				'promoteremail' => array(
					'search_type' => 'text', 'searchin' => 'u.email'),
			)
		);

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
		$filters = $this->getState('filters');
		$query->select($db->quoteName('v.other_city'));
		$query->select('COUNT(c.id) as no_of_campaigns');
		$query->select('SUM(c.goal_amount) as total_goal_amount');
		$query->from($db->quoteName('#__jg_campaigns', 'c'));
		$query->join('LEFT', $db->quoteName('#__tjvendors_vendors', 'v') .
		' ON (' . $db->quoteName('c.vendor_id') . ' = ' . $db->quoteName('v.vendor_id') . ')'
		);
		$subQuery = $db->getQuery(true);
		$subQuery->select('count(DISTINCT(user_id))');
		$subQuery->from($db->quoteName('#__jg_donors', 'jd'));
		$subQuery->where($db->quoteName('jd.campaign_id') . ' = ' . $db->qn('c.id'));
		$query->select('(' . $subQuery . ') AS donor_count');
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('v.user_id') . ' = ' . $db->quoteName('u.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_region', 'r') . ' ON (' . $db->quoteName('v.region') . ' = ' . $db->quoteName('r.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_country', 'con') . ' ON (' . $db->quoteName('v.country') . ' = ' . $db->quoteName('con.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_city', 'ci') . ' ON (' . $db->quoteName('v.city') . ' = ' . $db->quoteName('ci.id') . ')');
		$query->group($db->quoteName('c.vendor_id'));

		if (!$isSuperUser)
		{
			$query->where($db->quoteName('c.creator_id') . ' = ' . (int) $user->id);
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

		foreach ($items as $item)
		{
			$item['total_goal_amount'] = $jGiveFrontendHelper->getFormattedPrice($item['total_goal_amount']);
			$item['promoterphone'] = !empty($item['promoterphone'])	 ? $item['promoterphone'] : "-";
			$item['country'] = !empty($item['country'])	 ? $item['country'] : "-";
			$item['region'] = !empty($item['region'])	 ? $item['region'] : "-";

			if (empty($item['city']) && !empty($item['other_city']))
			{
				$item['city'] = $item['other_city'];
			}

			$item['city'] = !empty($item['city'])	 ? $item['city'] : "-";
			$item['promoteraddress'] = !empty($item['promoteraddress'])	 ? $item['promoteraddress'] : "-";
			$newItems[] = $item;
		}

		return $newItems;
	}
}
