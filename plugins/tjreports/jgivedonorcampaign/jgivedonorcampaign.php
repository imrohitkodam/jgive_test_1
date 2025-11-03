<?php
/**
 * @package    JGive_DonorCampaigns
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009-2017 Techjoomla. All rights reserved.
 * @license    GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

// Include TJReport Model
JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Campaigns report plugin of TJReport
 *
 * @since  1.0.0
 */
class TjreportsModelJgivedonorcampaign extends TjreportsModelReports
{
	protected $default_order     = 'orderid';

	protected $default_order_dir = 'DESC';

	public  $columns             = array();

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
			'orderid' => array('table_column' => 'o.id', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_ORDER_ID'),
			'name' => array('table_column' => 'd.first_name','title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_NAME'),
			'donoremail' => array('table_column' => 'd.email', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_EMAIL'),
			'donorphone' => array('table_column' => 'd.phone', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_PHONE_NUMBER', 'disable_sorting' => true),
			'donor_type' => array('table_column' => 'd.donor_type', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_TYPE'),
			'campaignname' => array('table_column' => 'c.title', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_NAME'),
			'campaigntype' => array('table_column' => 'c.type', 'title' => 'PLG_TJREPORTS_JGIVEDONORCAMPAIGN_TYPE'),
			'amountdonated' => array('table_column' => 'o.original_amount','title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_INVESTOR_PAID_AMOUNT',
									'disable_sorting' => true),
			'donation_status' => array('table_column' => 'o.status', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_PAYMENT_STATUS', 'disable_sorting' => true),
			'payment_method' => array('table_column' => 'o.processor', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_PAYMENT_METHOD',
									'disable_sorting' => true),
			'createdon' => array('table_column' => 'o.cdate', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONATION_CREATED_DATE',
								'disable_sorting' => true),
			'commission' => array('table_column' => 'o.fee', 'title' => 'PLG_TJREPORTS_JGIVE_DONORSREPORT_COMMISSION', 'disable_sorting' => true),
			'givebackitem' => array('table_column' => 'g.title', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_GIVEBACK_ITEM_NAME',
								'disable_sorting' => true),
			'comment' => array('table_column' => 'dt.comment', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_COMMENT', 'disable_sorting' => true),
			'isAnonymous' => array('table_column' => 'dt.annonymous_donation', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_IS_ANONYMOUS',
								'disable_sorting' => true),
			'country' => array('table_column' => 'tc.country', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_COUNTRY', 'disable_sorting' => true),
			'state' => array('table_column' => 'tr.region', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_STATE', 'disable_sorting' => true),
			'city' => array('table_column' => 'ct.city', 'title' => 'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_CITY', 'disable_sorting' => true)
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
		return $detail = array('client' => 'com_jgive', 'title' => Text::_('PLG_TJREPORTS_JGIVEDONORCAMPAIGN'));
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
		$campaignsListArray = $jGiveTjreportsHelper->getAllCampaigns();
		$allCampaignsList = $jGiveTjreportsHelper->getFilterOptions($campaignsListArray);

		$donorListArray = $jGiveTjreportsHelper->getDonors();
		$donorOptions = $jGiveTjreportsHelper->getFilterOptions($donorListArray);
		$orderListArray = $jGiveTjreportsHelper->getOrders();
		$orderOptions = $jGiveTjreportsHelper->getFilterOptions($orderListArray);
		$donationType = new stdclass;
		$donationType->id = 'donation';
		$donationType->value = Text::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION');

		$investment = new stdclass;
		$investment->id = 'investment';
		$investment->value = Text::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT');

		$campaigntypeOptions = $jGiveTjreportsHelper->getFilterOptions(array($donationType, $investment));

		$orgType = new stdclass;
		$orgType->id = 'org';
		$orgType->value = Text::_('PLG_TJREPORTS_JGIVE_ORGANIZATION');

		$indType = new stdclass;
		$indType->id = 'ind';
		$indType->value = Text::_('PLG_TJREPORTS_JGIVE_INDIVIDUAL');

		$donortypeOptions = $jGiveTjreportsHelper->getFilterOptions(array($orgType, $indType));

		$dispFilters = array(
			array(
				'orderid' => array(
					'search_type' => 'select', 'select_options' => $orderOptions, 'searchin' => 'o.order_id'
					),
				'donorname' => array(
					'search_type' => 'select', 'select_options' => $donorOptions, 'searchin' => 'd.first_name'
					),
				'campaignname' => array(
					'search_type' => 'select', 'select_options' => $allCampaignsList, 'type' => 'equal', 'searchin' => 'c.id'
					),
				'donoremail' => array(
					'search_type' => 'text', 'searchin' => 'd.email'
					),
				'campaigntype' => array(
						'search_type' => 'select', 'select_options' => $campaigntypeOptions, 'type' => 'equal', 'searchin' => 'c.type'
					),
				'donor_type' => array(
						'search_type' => 'select', 'select_options' => $donortypeOptions, 'type' => 'equal', 'searchin' => 'd.donor_type'
					)

			),
			array(
				'o.cdate' => array(
					'search_type' => 'date.range',
					'searchin' => 'o.cdate',
					'o.cdate_from' => array('attrib' => array('placeholder' => 'FROM (YYYY-MM-DD)')),
					'o.cdate_to' => array('attrib' => array('placeholder' => 'TO (YYYY-MM-DD)')),
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
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$input         = Factory::getApplication()->input;
		$contributorId = $input->getInt('contributor_id', 0);
		$donorType     = $input->get('donor_type', '', 'STRING');
		$campaign      = $input->get('campaign');

		$user        = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');
		$db          = $this->_db;
		$query       = parent::getListQuery();

		$query->select("CONCAT(d.first_name, ' ', d.last_name) AS 'name'");
		$query->select("o.order_id");
		$query->select("d.org_name");
		$query->select("c.creator_id");
		$query->from($db->quoteName('#__jg_donors', 'd'));
		$query->join('LEFT', $db->quoteName('#__jg_campaigns', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('d.campaign_id') . ')');
		$query->join('LEFT', $db->quoteName('#__jg_orders', 'o') . ' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('o.donor_id') . ')');
		$query->join('LEFT', $db->quoteName('#__jg_donations', 'dt') . ' ON (' . $db->quoteName('dt.donor_id') . ' = ' . $db->quoteName('d.id') . ')');
		$query->join(
		'LEFT', $db->quoteName('#__jg_campaigns_givebacks', 'g') . ' ON (' . $db->quoteName('g.id') . ' = ' . $db->quoteName('dt.giveback_id') . ')'
		);
		$query->where($db->quoteName('o.status') . '=' . $db->quote('C'));
		$query->join('LEFT', $db->quoteName('#__tj_region', 'tr') . ' ON (' . $db->quoteName('d.state') . ' = ' . $db->quoteName('tr.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_country', 'tc') . ' ON (' . $db->quoteName('d.country') . ' = ' . $db->quoteName('tc.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tj_city', 'ct') . ' ON (' . $db->quoteName('d.city') . ' = ' . $db->quoteName('ct.id') . ')');

		if (!$isSuperUser)
		{
			$query->where($db->quoteName('c.creator_id') . ' = ' . (int) $user->id);
		}

		if ($contributorId)
		{
			$query->where($db->quoteName('d.contributor_id') . ' = ' . (int) $contributorId);
		}

		if ($donorType)
		{
			$query->where($db->quoteName('d.donor_type') . ' = ' . $db->quote($donorType));
		}

		if ($campaign)
		{
			$query->where($db->quoteName('c.id') . ' = ' . (int) $campaign);
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

		jimport('techjoomla.common');
		$tjCommon = new TechjoomlaCommon;

		JLoader::import('components.com_jgive.helper', JPATH_SITE);
		$jGiveFrontendHelper = new JgiveFrontendHelper;
		$params = ComponentHelper::getParams('com_jgive');

		if (is_array($items))
		{
			foreach ($items as $item)
			{
				if (isset($item['campaignname']))
				{
					$item['campaignname'] = htmlspecialchars($item['campaignname'], ENT_COMPAT, 'UTF-8');
				}

				if ($item['donor_type'] == 'org' && !empty($item['org_name']))
				{
					$item['name'] = ucwords($item['org_name']);
				}

				$item['donorphone']   = !empty($item['donorphone']) ? $item['donorphone'] : "-";
				$item['givebackitem'] = !empty($item['givebackitem']) ? $item['givebackitem'] : "-";
				$item['campaigntype'] = !empty($item['campaigntype']) ? ucwords($item['campaigntype']) : "-";
				$item['city'] = !empty($item['city']) ? ucwords($item['city']) : "-";
				$item['state'] = !empty($item['state']) ? ucwords($item['state']) : "-";
				$item['country'] = !empty($item['country']) ? ucwords($item['country']) : "-";
				$item['comment'] = !empty($item['comment']) ? $item['comment'] : "-";
				$item['orderid'] = $item['order_id'];

				if (empty($item['createdon']) || $item['createdon'] == '0000-00-00 00:00:00')
				{
					$item['createdon'] = ' - ';
				}
				else
				{
					$item['createdon'] = $tjCommon->getDateInLocal($item['createdon'], 0, $params->get('date_format', 'j  M  Y'));
				}

				$item['isAnonymous'] = (isset($item['isAnonymous']) && $item['isAnonymous'] === '1') ? Text::_(
				'PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_ISANONYMOUS_YES'): Text::_('PLG_TJREPORTS_JGIVE_DONOR_CAMPAIGN_DONOR_ISANONYMOUS_NO');

				if (isset($item['amountdonated']))
				{
					$item['amountdonated'] = $jGiveFrontendHelper->getFormattedPrice($item['amountdonated']);
				}

				if (isset($item['donor_type']) && $item['donor_type'] == "org")
				{
					$item['donor_type'] = Text::_('PLG_TJREPORTS_JGIVE_ORGANIZATION');
				}
				else
				{
					$item['donor_type'] = Text::_('PLG_TJREPORTS_JGIVE_INDIVIDUAL');
				}

				if (empty($item['receiveddonation']) || $item['receiveddonation'] == '0000-00-00 00:00:00')
				{
					$item['receiveddonation'] = ' - ';
				}
				else
				{
					$item['receiveddonation'] = $tjCommon->getDateInLocal($item['receiveddonation'], 0, $params->get('date_format', 'j  M  Y'));
				}

				if ($item['donation_status'])
				{
					$statusArray = JGive::utilities()->getOrderStatusText($item['donation_status']);
					$item['donation_status'] = $statusArray['statusText'];
				}

				if ($item['payment_method'])
				{
					$plugin = PluginHelper::getPlugin('payment', $item['payment_method']);
					$plgname = (json_decode($plugin->params)->plugin_name) ? (json_decode($plugin->params)->plugin_name) : $item['payment_method'];
					$item['payment_method'] = htmlspecialchars($plgname, ENT_COMPAT, 'UTF-8');
				}

				$item['commission'] = $jGiveFrontendHelper->getFormattedPrice($item['commission']);

				$newItems[] = $item;
			}
		}

		return $newItems;
	}
}
