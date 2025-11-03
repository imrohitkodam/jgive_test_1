<?php
/**
 * @package    JGive_Campaigns
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009-2017 Techjoomla. All rights reserved.
 * @license    GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

// Include TJReport Model
JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');
JLoader::import('filterFields', JPATH_SITE . '/components/com_tjfields');

/**
 * Campaigns report plugin of TJReport
 *
 * @since  1.0.0
 */
class TjreportsModelJgivecampaigns extends TjreportsModelReports
{
	use TjfieldsFilterField;

	protected $default_order = 'c.id';

	protected $default_order_dir = 'DESC';

	public $showSearchResetButton = false;

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
			'title' => array('table_column' => 'c.title', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_CAMPAIGN_TITLE'),
			'promoter' => array('table_column' => 'v.vendor_title', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PROMOTER_NAME'),
			'orgindtype' => array('table_column' => 'c.org_ind_type', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_ORGIND_TYPE'),
			'category' => array('table_column' => 'cat.title', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_CATEGORY'),
			'type' => array('table_column' => 'c.type', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_TYPE'),
			'numberOfDonors' => array('table_column' => '','title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_NUMBER_OF_DONORS'),
			'start_date' => array('table_column' => 'c.start_date', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_START_DATE'),
			'end_date' => array('table_column' => 'c.end_date', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_END_DATE'),
			'goal_amount' => array('table_column' => 'c.goal_amount', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_GOAL_AMOUNT'),
			'amountReceived' => array('title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_AMOUNT_RECIEVED', 'disable_sorting' => true),
			'published' => array('table_column' => 'c.published', 'title' => 'PLG_TJREPORTS_JGIVE_CAMPAIGNS_PUBLISHED_STATUS')
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
		return $detail = array('client' => 'com_jgive', 'title' => Text::_('PLG_TJREPORTS_JGIVECAMPAIGNS'));
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
		$user = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');

		JLoader::import('components.com_jgive.helpers.tjreports', JPATH_SITE);
		$jGiveTjreportsHelper = new JGiveTjreportsHelper;
		$campaignsListArray = $jGiveTjreportsHelper->getAllCampaigns();
		$allCampaignsList = $jGiveTjreportsHelper->getFilterOptions($campaignsListArray);

		$promoterListArray = $jGiveTjreportsHelper->getCampaignsPromoter();
		$promotersList = $jGiveTjreportsHelper->getFilterOptions($promoterListArray, Text::_('PLG_TJREPORTS_JGIVE_FILTER_SELECT_PROMOTER'));

		$statusList = array();
		$statusList[] = HTMLHelper::_('select.option', '', Text::_('PLG_TJREPORTS_JGIVE_FILTER_SELECT_OPTION'));
		$statusList[] = HTMLHelper::_('select.option', 1, Text::_('PLG_TJREPORTS_JGIVE_CAMPAIGNS_PUBLISHED_STATUS_ACTIVE'));
		$statusList[] = HTMLHelper::_('select.option', 0, Text::_('PLG_TJREPORTS_JGIVE_CAMPAIGNS_PUBLISHED_STATUS_INACTIVE'));

		$orgIndTypeList = array();
		$orgIndTypeList[] = HTMLHelper::_('select.option', '', Text::_('PLG_TJREPORTS_JGIVE_FILTER_SELECT_OPTION'));
		$orgIndTypeList[] = HTMLHelper::_('select.option', 'non_profit', Text::_('COM_JGIVE_ORG_NON_PROFIT'));
		$orgIndTypeList[] = HTMLHelper::_('select.option', 'self_help', Text::_('COM_JGIVE_SELF_HELP'));
		$orgIndTypeList[] = HTMLHelper::_('select.option', 'individuals', Text::_('COM_JGIVE_SELF_INDIVIDUALS'));

		$campaignTypeList = array();
		$campaignTypeList[] = HTMLHelper::_('select.option', '', Text::_('PLG_TJREPORTS_JGIVE_FILTER_SELECT_OPTION'));
		$campaignTypeList[] = HTMLHelper::_('select.option', 'donation', Text::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION'));
		$campaignTypeList[] = HTMLHelper::_('select.option', 'investment', Text::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT'));

		$categoriesListArray = $jGiveTjreportsHelper->getCategories();
		$categoriesList = $jGiveTjreportsHelper->getFilterOptions($categoriesListArray);

		$dispFilters = array(
			array(
				'title' => array(
					'search_type' => 'select', 'select_options' => $allCampaignsList,'type' => 'equal', 'searchin' => 'c.id'
					),
				'published' => array(
					'search_type' => 'select', 'select_options' => $statusList, 'type' => 'equal', 'searchin' => 'c.published'
					),
				'orgindtype' => array(
					'search_type' => 'select', 'select_options' => $orgIndTypeList, 'type' => 'equal', 'searchin' => 'c.org_ind_type'
					),
				'category' => array(
					'search_type' => 'select', 'select_options' => $categoriesList, 'type' => 'equal', 'searchin' => 'cat.id'
					),
				'type' => array(
					'search_type' => 'select', 'select_options' => $campaignTypeList, 'type' => 'equal', 'searchin' => 'c.type'
					),
				'published' => array(
					'search_type' => 'select', 'select_options' => $statusList, 'type' => 'equal', 'searchin' => 'c.published'
					)
				),
			);

		if ($isSuperUser)
		{
			$dispFilters[1]['promoter'] = array(
				'search_type' => 'select', 'select_options' => $promotersList, 'type' => 'equal', 'searchin' => 'c.creator_id'
			);
		}

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
		$query->select('c.id');
		$query->select('c.creator_id');
		$query->select('SUM(o.amount) AS amountReceived');
		$query->select('COUNT(o.campaign_id) AS numberOfDonors');
		$query->from($db->quoteName('#__jg_campaigns', 'c'));
		$query->join('LEFT', $db->quoteName('#__tjvendors_vendors', 'v') .
		' ON (' . $db->quoteName('c.vendor_id') . ' = ' . $db->quoteName('v.vendor_id') . ')'
		);
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('v.user_id') . ' = ' . $db->quoteName('u.id') . ')');
		$query->join(
		'LEFT', $db->quoteName('#__jg_orders', 'o') . ' ON (' . $db->quoteName('o.campaign_id') . ' = ' . $db->quoteName('c.id') .
		' AND ' . $db->quoteName('o.status') . ' = ' . $db->quote('C') . ')'
		);
		$query->join('LEFT', $db->quoteName('#__jg_donors', 'd') . ' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('o.donor_id') . ')');
		$query->join('LEFT', $db->quoteName('#__categories', 'cat') . ' ON (' . $db->quoteName('c.category_id') . ' = ' . $db->quoteName('cat.id') . ')');

		if (!$isSuperUser)
		{
			$query->where($db->quoteName('c.creator_id') . ' = ' . (int) $user->id);
		}

		$query->group($db->quoteName('c.id'));

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
		$items = parent::getItems();

		JLoader::import('components.com_jgive.helper', JPATH_SITE);
		$jGiveFrontendHelper = new JgiveFrontendHelper;

		$params = ComponentHelper::getParams('com_jgive');

		// Get tj fields
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');
		$campModel = BaseDatabaseModel::getInstance('Campaign', 'JGiveModel', array("ignore_request" => true));

		$data = array(
			"client" => 'com_jgive.campaign',
		);

		$colToshow = $this->getState('colToshow', array());

		foreach ($items as $key => $item)
		{
			if (isset($item['title']))
			{
				$item['title'] = htmlspecialchars($item['title'], ENT_COMPAT, 'UTF-8');
			}

			if (isset($item['goal_amount']))
			{
				$item['goal_amount'] = $jGiveFrontendHelper->getFormattedPrice($item['goal_amount']);
			}

			$item['amountReceived'] = $item['amountReceived'] ? $jGiveFrontendHelper->getFormattedPrice($item['amountReceived']) : "-";
			$item['type'] = $item['type'] ? ucwords($item['type']) : "-";

			if (isset($item['start_date']))
			{
				$item['start_date'] = HTMLHelper::_('date', $item['start_date'], $params->get('date_format', 'j  M  Y'));
			}

			if (isset($item['end_date']))
			{
				$item['end_date'] = HTMLHelper::_('date', $item['end_date'], $params->get('date_format', 'j  M  Y'));
			}

			if (isset($item['published']))
			{
				switch ($item['published'])
				{
					case '1':
					$item['published'] = Text::_('PLG_TJREPORTS_JGIVE_CAMPAIGNS_PUBLISHED_STATUS_ACTIVE');
					break;

					case '0':
					$item['published'] = Text::_('PLG_TJREPORTS_JGIVE_CAMPAIGNS_PUBLISHED_STATUS_INACTIVE');
					break;
				}
			}

			if (isset($item['orgindtype']))
			{
				switch ($item['orgindtype'])
				{
					case 'non_profit':
					$item['orgindtype'] = Text::_('COM_JGIVE_ORG_NON_PROFIT');
					break;

					case 'self_help':
					$item['orgindtype'] = Text::_('COM_JGIVE_SELF_HELP');
					break;

					case 'individuals':
					$item['orgindtype'] = Text::_('COM_JGIVE_SELF_INDIVIDUALS');
					break;
				}
			}

			$items[$key] = $item;

			$extraFields = $campModel->getDataExtra($item['id']);

			$valueArray = array();

			foreach ($extraFields as $extraField)
			{
				if (!is_array($extraField->value))
				{
					$items[$key][$extraField->label]  = $extraField->value;
				}
				else
				{
					$optionArray = array();

					foreach ($extraField->value as $option)
					{
						if (in_array($extraField->label, $valueArray))
						{
							$items[$key][$extraField->label] .= ', ' . isset($option->options) ? $option->options : (($option->value ? $option->value : ''));
						}
						else
						{
							$items[$key][$extraField->label]  = isset($option->options) ? $option->options : (($option->value ? $option->value : ''));
						}
					}
				}

				$valueArray[] = $extraField->label;

				$colToshow[$extraField->label] = $extraField->label;
				$this->columns[$extraField->label] = array('title' => ucwords($extraField->label));
			}

			$this->setState('colToshow', $colToshow);
		}

		return $items;
	}
}
