<?php
/**
 * @package    JGive_Donor_Organization
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009-2020 Techjoomla. All rights reserved.
 * @license    GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

include_once  JPATH_SITE . '/components/com_jgive/includes/jgive.php';

// Include TJReport Model
JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Jgive Donor Organization report plugin of TJReport
 *
 * @since  _DEPLOY_VERSION_
 */
class TjreportsModelJgiveDonorOrganization extends TjreportsModelReports
{
	protected $default_order = 'name';

	protected $default_order_dir = 'ASC';

	public $columns;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   _DEPLOY_VERSION_
	 */
	public function __construct($config = array())
	{
		$lang     = Factory::getLanguage();
		$base_dir = JPATH_SITE . '/administrator';
		$lang->load('com_jgive', $base_dir);

		$this->columns = array(
			'name' => array('table_column' => 'a.name', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_NAME'),
			'contactperson' => array('table_column' => 'c.first_name', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_CONTACT_PERSON'),
			'email' => array('table_column' => 'a.email', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_EMAIL'),
			'phone' => array('table_column' => 'a.phone', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_PHONE'),
			'website' => array('table_column' => 'a.website', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_WEBSITE', 'disable_sorting' => true),
			'donationcount' => array('table_column' => '', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_DONATION_COUNT'),
			'amount' => array('table_column' => '', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_AMOUNT'),
			'address' => array('table_column' => 'a.addr_line_1', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_ADDRESS', 'disable_sorting' => true),
			'country' => array('table_column' => 'tc.country', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_COUNTRY'),
			'state' => array('table_column' => 'tr.region', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_STATE'),
			'city' => array('table_column' => 'ct.city', 'title' => 'PLG_TJREPORTS_JGIVEDONORORGANIZATION_CITY')
		);

		parent::__construct($config);
	}

	/**
	 * Get client of this plugin
	 *
	 * @return array<string,mixed|string> Plugin Details
	 *
	 * @since   _DEPLOY_VERSION_
	 * */
	public function getPluginDetail()
	{
		return $detail = array('client' => 'com_jgive', 'title' => Text::_('PLG_TJREPORTS_JGIVEDONORORGANIZATION'));
	}

	/**
	 * Create an array of filters
	 *
	 * @return    ARRAY Filters used in reports
	 *
	 * @since    _DEPLOY_VERSION_
	 */
	public function displayFilters()
	{
		JLoader::import('components.com_jgive.helpers.tjreports', JPATH_SITE);
		$jGiveTjreportsHelper = new JGiveTjreportsHelper;
		$organizationListArray = $jGiveTjreportsHelper->getOrganizationList();
		$allOrganizationList = $jGiveTjreportsHelper->getFilterOptions($organizationListArray);

		$dispFilters = array(
			array(
				'name' => array(
					'search_type' => 'select', 'select_options' => $allOrganizationList, 'searchin' => 'name'
					),
				'contactperson' => array(
					'search_type' => 'text', 'searchin' => 'c.first_name'
					)
			)
		);

		return $dispFilters;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	protected function getListQuery()
	{
		$user = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');
		$db        = $this->_db;
		$query     = parent::getListQuery();
		$colToshow = (array) $this->getState('colToshow');
		$filters   = $this->getState('filters');

		$query->select("CONCAT(c.first_name, ' ', c.last_name) AS 'contactperson'");
		$query->select("CONCAT(a.addr_line_1, ' ', a.addr_line_2) AS 'address'");
		$query->select("a.id");
		$query->from($db->quoteName('#__jg_organizations', 'a'));
		$query->join(
			'LEFT', $db->quoteName('#__jg_organization_contacts', 'b') .
			' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.organization_id') . ')'
		);
		$query->join('LEFT', $db->quoteName('#__jg_individuals', 'c') . ' ON (' . $db->quoteName('b.individual_id') . ' = ' . $db->quoteName('c.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_region', 'tr') . ' ON (' . $db->quoteName('a.region') . ' = ' . $db->quoteName('tr.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_country', 'tc') . ' ON (' . $db->quoteName('a.country') . ' = ' . $db->quoteName('tc.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_city', 'ct') . ' ON (' . $db->quoteName('a.city') . ' = ' . $db->quoteName('ct.id') . ')');

		if (!$isSuperUser)
		{
			$utilityClass = JGive::utilities();
			$vendorId = $utilityClass->getVendorId($user->id, "com_jgive");
			$query->where($db->quoteName('a.vendor_id') . ' = ' . (int) $vendorId);
		}

		$query->where($db->quoteName('a.vendor_id') . ' <> ' . (int) 0);
		$subQuery = $db->getQuery(true);
		$subQuery1 = $db->getQuery(true);

		// Get total amount
		$subQuery1->select('sum(d.original_amount)');
		$subQuery1->from($db->quoteName('#__jg_orders', 'd'));
		$subQuery1->join('LEFT', $db->quoteName('#__jg_donors', 'e') . ' ON (' . $db->quoteName('d.donor_id') . ' = ' . $db->quoteName('e.id') . ')');
		$subQuery1->join('LEFT', $db->quoteName('#__jg_campaigns', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('e.campaign_id') . ')');
		$subQuery1->where($db->quoteName('e.donor_type') . ' = ' . $db->quote('org'));
		$subQuery1->where($db->quoteName('e.contributor_id') . ' = ' . $db->quoteName('a.id'));
		$subQuery1->where($db->quoteName('d.status') . ' = ' . $db->quote('C'));

		if (!$isSuperUser && $vendorId)
		{
			$subQuery1->where($db->quoteName('c.vendor_id') . ' = ' . (int) $vendorId);
		}

		$subQuery1->where($db->quoteName('c.vendor_id') . ' <> ' . (int) 0);

		$query->select('( ' . $subQuery1 . ' ) AS amount');

		// Get total donation count
		$subQuery->select('count(d.campaign_id)');
		$subQuery->from($db->quoteName('#__jg_orders', 'd'));
		$subQuery->join('LEFT', $db->quoteName('#__jg_donors', 'e') . ' ON (' . $db->quoteName('d.donor_id') . ' = ' . $db->quoteName('e.id') . ')');
		$subQuery->join('LEFT', $db->quoteName('#__jg_campaigns', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('e.campaign_id') . ')');
		$subQuery->where($db->quoteName('e.donor_type') . ' = ' . $db->quote('org'));
		$subQuery->where($db->quoteName('e.contributor_id') . ' = ' . $db->quoteName('a.id'));

		if (!$isSuperUser && $vendorId)
		{
			$subQuery->where($db->quoteName('c.vendor_id') . ' = ' . (int) $vendorId);
		}

		$subQuery->where($db->quoteName('c.vendor_id') . ' <> ' . (int) 0);
		$query->select('( ' . $subQuery . ' ) AS donationcount');

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function getItems()
	{
		$newItems = array();
		$items = parent::getItems();

		JLoader::import('components.com_jgive.helper', JPATH_SITE);
		$jGiveFrontendHelper = new JgiveFrontendHelper;

		$colToshow = $this->getState('colToshow');

		if (is_array($items))
		{
			JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

			foreach ($items as $key => $item)
			{
				$item['phone'] = !empty($item['phone']) ? $item['phone'] : "-";
				$item['email'] = !empty($item['email']) ? $item['email'] : "-";
				$item['zip'] = !empty($item['zip']) ? $item['zip'] : "-";
				$item['country'] = !empty($item['country']) ? $item['country'] : "-";
				$item['state'] = !empty($item['state']) ? $item['state'] : "-";
				$item['city'] = !empty($item['city']) ? $item['city'] : "-";
				$item['address'] = !empty(trim($item['address'])) ? $item['address'] : "-";
				$item['website'] = !empty($item['website']) ? $item['website'] : "-";
				$item['amount'] = !empty($item['amount']) ? $jGiveFrontendHelper->getFormattedPrice($item['amount']) : "-";
				$item['donationcount'] = !empty($item['donationcount']) ? $item['donationcount'] : "-";
				$item['contactperson'] = !empty($item['contactperson']) ? $item['contactperson'] : "-";

				$individualFields = FieldsHelper::getFields('com_jgive.organization', $item, true);
				$items[$key] = $item;

				if (!empty($individualFields))
				{
					foreach ($individualFields as $field)
					{
						$colToshow[$field->label] = $field->label;
						$this->columns[$field->label] = array('title' => ucwords($field->label));
						$items[$key][$field->label] = $field->value;
					}

					$this->setState('colToshow', $colToshow);
				}
			}
		}

		return $items;
	}
}
