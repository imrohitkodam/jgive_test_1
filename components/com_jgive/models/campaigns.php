<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Categories\Categories;

JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);
include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * Campaigns form model class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveModelCampaigns extends ListModel
{
	protected $clearFilters = 0;

	protected $sortingFields = array();

	protected $derivedSortingFields = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'title',
				'state',
				'published',
				'created',
				'start_date',
				'end_date',
				'category_id',
				'featured',
				'modified',
				'success_status',
				'creator_id',
				'js_groupid',
				'country',
				'city',
				'goal_amount',
				'id'
			);

			$this->sortingFields = array_merge(array(''), $config['filter_fields']);
		}

		// Derived Sorting Fields
		$this->derivedSortingFields = array(
		'amount_received', 'remaining_amount', 'campaignDonorsCount', 'mostAmountRaised',
		'farthestFromGoal', 'campaignDonorsCount', 'campaignMostDonors', 'oldest'
		);

		$this->filterValues = array('0', '1', '-1', 'featured');
		parent::__construct($config);
	}

	/**
	 * Function getDonteButtonStatusFlag
	 *
	 * @param   Array  $campaignData  All Campaign data
	 *
	 * @return  Integer  DonteButtonStatusFlag
	 *
	 * @since   2.0
	 */
	public function getDonateButtonStatusFlag($campaignData)
	{
		$camp_startdate = Factory::getDate($campaignData['start_date'] ? $campaignData['start_date'] : '')->Format('Y-m-d H:i:s');
		$camp_enddate = Factory::getDate($campaignData['end_date'] ? $campaignData['end_date'] : '')->Format('Y-m-d H:i:s');
		$curr_date = Factory::getDate()->Format('Y-m-d H:i:s');

		if ($curr_date < $camp_startdate)
		{
			// Campaign Not yet started
			return -1;
		}
		elseif ($curr_date > $camp_enddate)
		{
			// Campaign closed
			return 0;
		}
		else
		{
			// Check Allow donations to exceed goal amount configuration
			$allow_exceed    = $campaignData['allow_exceed'] ? $campaignData['allow_exceed']: '0';

			// Check max donors or investor if set don't consider is value is 0
			$max_donors      = $campaignData['max_donors'] ? $campaignData['max_donors']: '0';

			// Check campaign goal amout
			$goal_amount     = isset($campaignData['goal_amount']) ? $campaignData['goal_amount'] : 0;

			// Check campaign received amout
			$amount_received = isset($campaignData['amount_received']) ? $campaignData['amount_received'] : 0;

			// If not allowed exceed amount than goal amount
			if ($allow_exceed == '0' && $goal_amount <= $amount_received)
			{
				return 0;
			}

			// If max donors count is reached
			if ($max_donors != '0' && ($max_donors <= $campaignData['campaignDonorsCount']))
			{
				return 0;
			}

			return 1;
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 *
	 * @since	1.6
	 */
	public function getListQuery()
	{
		$app = Factory::getApplication();
		$config = Factory::getConfig();
		$user = Factory::getUser();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('camp.*,cat.title as cat_name');
		$query->from($db->quoteName('#__jg_campaigns', 'camp'));
		$query->join('LEFT', $db->quoteName('#__categories', 'cat') .
			' ON (' . $db->quoteName('camp.category_id') . ' = ' . $db->quoteName('cat.id') . ')');
		$query->join('LEFT', $db->quoteName('#__users', 'user') .
			' ON (' . $db->quoteName('camp.creator_id') . ' = ' . $db->quoteName('user.id') . ')');
		$query->join('LEFT', $db->quoteName('#__tjvendors_vendors', 'vendors') .
			' ON (' . $db->quoteName('camp.vendor_id') . ' = ' . $db->quoteName('vendors.vendor_id') . ')');

		// Get all filters
		$filters = $this->get('filter_fields');

		foreach ($filters as $filter)
		{
			$filterValue = $this->getState('filter.' . $filter);

			if ((isset($filterValue) && $filterValue != '') && ($filter == 'country' || $filter == 'state' || $filter == 'city'))
			{
				$filter = $filter == 'state' ? 'vendors.region': 'vendors.' . $filter;
				$query->where($db->quoteName($filter) . ' = ' . $filterValue);
			}
			elseif (isset($filterValue) && $filterValue != '')
			{
				$query->where($db->quoteName($filter) . ' = ' . $filterValue);
			}
		}

		// Get value of other filters which are not added in Filters Field
		$filterSearch = $this->getState('filter_search');

		if (!empty($filterSearch))
		{
			$filterSearch = $db->quote('%' . $db->escape($filterSearch, true) . '%');
			$query->where('( camp.title LIKE ' . $filterSearch .
					' OR camp.short_description LIKE ' . $filterSearch .
					' OR camp.group_name LIKE ' . $filterSearch .
				' )');
		}

		// TJ-fields filter - Start
		JLoader::import('tjfields', JPATH_SITE . '/components/com_tjfields/helpers');

		$TjfieldsHelper = new TjfieldsHelper;
		$tjfieldItem_ids = $TjfieldsHelper->getFilterResults();

		$client = $app->input->get('client', '', 'STRING');

		if (!empty($client))
		{
			if ($tjfieldItem_ids != '-2')
			{
				$query->where($db->quoteName("camp.id") . " IN (" . $db->escape($tjfieldItem_ids) . ") ");
			}
		}

		// TJ-fields filter - End

		$filterCampaignCat = $this->getState('filter_campaign_cat');

		if (!empty($filterCampaignCat))
		{
			// Shows campaigns as per selected category as well as child categrories
			$categories = Categories::getInstance('com_jgive');
			$cat = $categories->get($filterCampaignCat);
			$categoryIds = [$filterCampaignCat];

			if ($cat)
			{
				$subCategories = $cat->getChildren();

				foreach ($subCategories as $subCategory)
				{
					array_push($categoryIds, $subCategory->id);
				}
			}

			$query->where($db->quoteName('camp.category_id') . ' IN (' . implode(',', $categoryIds) . ')');
		}

		$filterCampaignsToShow = $this->getState('filter_campaigns_to_show');

		if ($filterCampaignsToShow == 'featured')
		{
			$query->where($db->quoteName('camp.featured') . ' = 1');
		}
		elseif (isset($filterCampaignsToShow) && $filterCampaignsToShow == 1)
		{
			// Fetch successful campaigns
			$query->where($db->quoteName('camp.success_status') . ' = ' . (int) $filterCampaignsToShow);
		}
		elseif (isset($filterCampaignsToShow) && $filterCampaignsToShow == 0)
		{
			// Fetch campaigns that are not successful or successful with exceeding allowed
			$query->where(
				'(' . $db->quoteName('camp.success_status') . ' = 0 OR (' .
				$db->quoteName('camp.success_status') . ' = 1 AND ' . $db->quoteName('camp.allow_exceed') . ' = 1))'
			);
		}

		$filterOrgIndType = $this->getState('filter_org_ind_type');

		if (!empty($filterOrgIndType))
		{
			$query->where($db->quoteName('camp.org_ind_type') . ' = ' . $db->quote($filterOrgIndType));
		}

		$filterCampaignType = $this->getState('filter_campaign_type');

		if (!empty($filterCampaignType))
		{
			$query->where($db->quoteName('camp.type') . ' = ' . $db->quote($filterCampaignType));
		}

		// Display layoutwise campaign- publish/unpublish
		if ($app->input->get('layout', '', 'STRING') != 'my')
		{
			$query->where($db->quoteName('camp.published') . ' = ' . $db->quote('1'));
		}

		// Display login user created campaign on my campaign view
		if ($app->input->get('layout', '', 'STRING') == 'my')
		{
			if (!empty($user->id))
			{
				$query->where($db->quoteName('camp.creator_id') . ' = ' . (int) $user->id);
			}
		}

		// Display single campaign in Pin module
		$selectSingleCampaignToShow = $this->getState('select_single_campaign_to_show');

		if ($selectSingleCampaignToShow)
		{
			$query->where($db->quoteName('camp.id') . ' = ' . (int) $selectSingleCampaignToShow);
		}

		$listlimit = $this->getState('list.limit') ? $this->getState('list.limit') : $this->getState('mod_limit');

		// Default pagination for first page load
		if ($listlimit == '' && $listlimit != '0')
		{
			$listlimit = $config->get('list_limit');
		}

		$this->setState('list.limit', $listlimit);

		// Add the list ordering clause.
		$orderCol  = $this->getState('list.ordering');
		$orderDirn = $this->getState('list.direction');

		if (empty($orderCol))
		{
			$orderCol = 'camp.ordering';
		}

		// Dont change - Default ordering direction is ASC as default ordering column is ordering
		if (empty($orderDirn))
		{
			$orderDirn = 'ASC';
		}

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		$db->setQuery($query);

		return $query;
	}

	/**
	 * Method populateState
	 *
	 * @param   String  $ordering   Ordering
	 * @param   String  $direction  Direction
	 *
	 * @return void
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app    = Factory::getApplication();
		$params = $app->getParams('com_jgive');

		$shouldResetFilters = (int) $app->input->get('reset_filters', 0);

		if ($shouldResetFilters === 1)
		{
			$keysToReset = array(
				'filter_search',
				'filter_campaign_countries',
				'filter_campaign_states',
				'filter_campaign_city',
				'user_filter',
				'filter_campaign_type',
				'filter_org_ind_type',
				'filter_campaigns_to_show',
				'filter_campaign_cat',
				'filter_order',
				'filter_order_Dir',
				'list.start',
				'list.limit'
			);

			foreach ($keysToReset as $key)
			{
				$app->setUserState($this->context . '.' . $key, null);
			}
		}

		$campaignTypeArrayValues = array('donation', 'investment');
		$campaignOrgIndTypeArrayValues = array('non_profit', 'self_help', 'individuals');

		// Campaign Filter
		$filterSearch = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '', 'STRING');
		$this->setState('filter_search', $filterSearch);

		// Country filter
		$filterCampaignCountries = $app->getUserStateFromRequest($this->context . '.filter_campaign_countries', 'filter_campaign_countries', '', 'INT');
		$this->setState('filter_campaign_countries', $filterCampaignCountries);
		$this->setState('filter.country', $filterCampaignCountries);

		// State filter
		$filterCampaignStates = $app->getUserStateFromRequest($this->context . '.filter_campaign_states', 'filter_campaign_states', '', 'INT');
		$this->setState('filter_campaign_states', $filterCampaignStates);
		$this->setState('filter.state', $filterCampaignStates);

		// City filter
		$filterCampaignCity = $app->getUserStateFromRequest($this->context . '.filter_campaign_city', 'filter_campaign_city', '', 'INT');
		$this->setState('filter_campaign_city', $filterCampaignCity);
		$this->setState('filter.city', $filterCampaignCity);

		// Filter by campaign creator
		$filterCampaignCreator = $app->getUserStateFromRequest($this->context . '.user_filter', 'user_filter', '', 'INT');

		if (!empty($filterCampaignCreator))
		{
			$this->setState('filter.creator_id', $filterCampaignCreator);
		}

		// Set campaigns start limit
		$listStart = $app->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'INT');
		$this->setState('list.start', $listStart);

		// Set campaigns limit (allow 0 to mean All)
		$listlimit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', null, 'INT');
		if ($listlimit === null)
		{
			// Defer to global default when not provided in request and not in user state
			$listlimit = (int) $params->get('list_limit', 0);
		}
		$this->setState('list.limit', $listlimit);

		// Campaign Type filter
		$filterCampaignType = $app->getUserStateFromRequest($this->context . '.filter_campaign_type', 'filter_campaign_type', '', 'STRING');

		if (!in_array($filterCampaignType, $campaignTypeArrayValues))
		{
			$filterCampaignType = '';
		}

		$this->setState('filter_campaign_type', $filterCampaignType);

		// Campaign Organization Individual Type
		$filterOrgIndType = $app->getUserStateFromRequest($this->context . '.filter_org_ind_type', 'filter_org_ind_type', '', 'STRING');

		if (!in_array($filterOrgIndType, $campaignOrgIndTypeArrayValues))
		{
			$filterOrgIndType = '';
		}

		$this->setState('filter_org_ind_type', $filterOrgIndType);

		// Campaigns to show
		$filterCampaignsToShow = $app->getUserStateFromRequest($this->context . '.filter_campaigns_to_show', 'filter_campaigns_to_show', '', 'STRING');

		if (!in_array($filterCampaignsToShow, $this->filterValues))
		{
			$filterCampaignsToShow = '';
		}

		$this->setState('filter_campaigns_to_show', $filterCampaignsToShow);

		// Campaign Category filter
		$filterCampaignCat = $app->getUserStateFromRequest($this->context . '.filter_campaign_cat', 'filter_campaign_cat', $params->get('defaultCatId'), 'INT');
		$this->setState('filter_campaign_cat', $filterCampaignCat);

		// Campaign Sorting order
		$ListViewSort = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'STRING');

		if (!empty($ListViewSort))
		{
			// Set flag that the sorting is triggered from list view
			$this->setState('ListViewSort', 1);
		}

		// Set ordering column
		$ordering = $ListViewSort ? $ListViewSort : $params->get('default_sort_by_option');

		if (!empty($ordering) && in_array($ordering, $this->sortingFields))
		{
			$this->setState('list.ordering', $ordering);
		}
		elseif (!empty($ordering) && in_array($ordering, $this->derivedSortingFields))
		{
			$this->setState('derivedSortFieldOption', $ordering);
		}

		// Get sorting direction from URL/user state
		$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'STRING');

		// Default sorting direction for columns - for sorting from the list view
		if (!empty($ListViewSort) && empty($direction))
		{
			if ($ordering == 'created')
			{
				$direction = 'DESC';
			}
			elseif ($ordering == 'title' || $ordering == 'end_date')
			{
				$direction = 'ASC';
			}
		}

		// Get sorting direction from params
		if (empty($direction))
		{
			$direction = $params->get('filter_order_Dir');
		}

		if (!empty($direction))
		{
			if (!in_array(strtoupper($direction), array('ASC', 'DESC')))
			{
				// Dont change - Default ordering direction is ASC as default ordering column is ordering
				$direction = 'ASC';
			}
		}

		// Set ordering direction
		if (!empty($direction))
		{
			$this->setState('list.direction', $direction);
		}

		if ($app->isClient("administrator"))
		{
			return;
		}

		// Load the parameters. Merge Global and Menu Item params into new object
		$menuParams = new Registry;

		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->getParams());
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);

		$this->setState('params', $mergedParams);
	}

	/**
	 * Method to get a list of courses.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$dataToDisplayPins      = $this->getState('displayPins');
		$derivedSortFieldOption = $this->getState('derivedSortFieldOption');

		if ($dataToDisplayPins)
		{
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');
			$jgiveFrontendHelper = new jgiveFrontendHelper;
			$campaignHelper      = new campaignHelper;
			$jgiveModelDonorsObj = BaseDatabaseModel::getInstance('Donors', 'JgiveModel');

			$viewItemIds = array();
			$viewItemIds['createCampItemid']     = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaignform');
			$viewItemIds['myCampaignsItemid']    = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');
			$viewItemIds['singleCampaignItemid'] = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default');
			$viewItemIds['allCampaignsItemid']   = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');

			$items = json_decode(json_encode($items), true);

			if ($items)
			{
				JLoader::register('JGiveModelMediaXref', JPATH_SITE . '/components/com_jgive/models/mediaxref.php');
				$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');
				JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
				$filetable   = Table::getInstance('Files', 'TJMediaTable');
				$com_params  = ComponentHelper::getParams('com_jgive');
				$storagePath = $com_params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

				foreach ($items as $k => $item)
				{
					$campaignAmounts = $campaignHelper->getCampaignAmounts($item['id']);

					$item['amount_received']       = $campaignAmounts['amount_received'];
					$item['remaining_amount']      = $campaignAmounts['remaining_amount'];
					$item['campaignDonorsCount']   = $jgiveModelDonorsObj->getDonorsPerCamp($item['id']);
					$item['donor_count']           = $campaignHelper->getCampaignDonorsCount($item['id']);
					$item['donteButtonStatusFlag'] = $this->getDonateButtonStatusFlag($item);
					$item['otherData']             = $viewItemIds;
					$item['params']                = $this->getState('params');
					$item['days_limit']            = $campaignHelper->getDateDiffInDays($item['start_date'], $item['end_date']);

					// Campaign main image data
					$campaignMainImage = $modelMediaXref->getCampaignMedia($item['id'], 'com_jgive.campaign', 0);

					if (!empty($campaignMainImage))
					{
						$filetable->load($campaignMainImage[0]->media_id);
						$mediaType     = explode(".", $filetable->type);
						$imgPath       = $storagePath . '/' . $mediaType[0] . 's';
						$mediaConfig   = array('id' => $campaignMainImage[0]->media_id, 'uploadPath' => $imgPath);
						$item['image'] = TJMediaStorageLocal::getInstance($mediaConfig);
					}

					$items[$k] = $item;
				}
			}

			/* If site admin sorting the campaigns by the derived field (i.e not a column of the table, comes by calculation)
			in that case only using this method */
			if ($derivedSortFieldOption != null && in_array($derivedSortFieldOption, $this->derivedSortingFields))
			{
				// Default ordering for derived fields is ASC
				$orderDirection = Factory::getApplication()->input->get("filter_order_Dir", "asc", "STRING");

				if (!in_array(strtolower($orderDirection), array("asc", "desc")))
				{
					$orderDirection = 'asc';
				}

				// Check if sorting action is triggered from menu or the sort option on pin view
				$ListViewSort = $this->getState('ListViewSort');

				if (empty($ListViewSort))
				{
					$orderDirection = $this->getState('list.direction');
				}

				// Sorting direction for derived fields, for other fields direction is ASC (default direction)
				if (in_array($derivedSortFieldOption, array('mostAmountRaised', 'farthestFromGoal', 'campaignMostDonors')))
				{
					$orderDirection = "desc";
				}

				// Get the right field (column) for sorting data as per the derived fields
				$derivedSortFieldOption = $this->getDerivedFieldSortingColumn($derivedSortFieldOption);

				// Sort the data as per the selcted sorting field
				$items = $jgiveFrontendHelper->multiDimensionalSort($items, $derivedSortFieldOption, $orderDirection);
			}
		}

		return $items;
	}

	/**
	 * Function to get column to sort the campaigns according to selected derived field
	 *
	 * @param   STRING  $derivedSortFieldOption  derived field
	 *
	 * @return  column to sort the campaign
	 *
	 * @since   2.2
	 */
	protected function getDerivedFieldSortingColumn($derivedSortFieldOption)
	{
		// For sorting the campaigns by most donors and least donors the sorting field used is 'campaignDonorsCount'
		if (in_array($derivedSortFieldOption,  array("campaignMostDonors", "campaignDonorsCount")))
		{
			$derivedSortFieldOption = "campaignDonorsCount";
		}

		// For sorting the campaigns as per goal the sorting field used is 'remaining_amount'
		if (in_array($derivedSortFieldOption,  array("farthestFromGoal")))
		{
			$derivedSortFieldOption = "remaining_amount";
		}

		// For sorting the campaigns as per amount received the sorting field used is 'amount_received'
		if (in_array($derivedSortFieldOption,  array("mostAmountRaised")))
		{
			$derivedSortFieldOption = "amount_received";
		}

		// For sorting the campaigns as per campaign creation dates
		if (in_array($derivedSortFieldOption,  array("oldest")))
		{
			$derivedSortFieldOption = "created";
		}

		return $derivedSortFieldOption;
	}

	/**
	 * Function getFilterCountries fetch the countries and related data. Which are selected while creating campaign
	 *
	 * @return  Object
	 *
	 * @since   1.8
	 */
	public function getFilterCountries()
	{
		$countryArray = TJVendors::utilities()->getVendorCountries(array('extension' => 'com_jgive'));

		return $countryArray;
	}

	/**
	 * Function getCampaignsFilterStates fetch the states and related data. Which are selected while creating campaign
	 *
	 * @return  Object
	 *
	 * @since   1.8
	 */
	public function getCampaignsFilterStates()
	{
		// Get country to generate state
		$country = $this->getState('filter_campaign_countries');
		$stateArray = TJVendors::utilities()->getVendorRegion(array('extension' => 'com_jgive', 'country' => $country));

		return $stateArray;
	}

	/**
	 * Function getCampaignCity
	 *
	 * @return  Object
	 *
	 * @since   1.8
	 */
	public function getCampaignsFilterCities()
	{
		// Get state to generate city
		$region = $this->getState('filter_campaign_states');
		$cityArray = TJVendors::utilities()->getVendorCity(array('extension' => 'com_jgive', 'region' => $region));

		return $cityArray;
	}

	/**
	 * Function getOrderingOptions
	 *
	 * @return  Array
	 *
	 * @since   1.8
	 */
	public function getCampignsOrderingOptions()
	{
		// Get the data to idetify which field to show on donation view
		$params               = ComponentHelper::getParams('com_jgive');
		$show_selected_fields = $params->get('show_selected_fields');
		$show_field           = 0;
		$goalAmount          = 0;

		if ($show_selected_fields)
		{
			$creatorFields = $params->get('creatorfield');

			if (isset($creatorFields))
			{
				foreach ($creatorFields as $creatorField)
				{
					switch ($creatorField)
					{
						case 'goal_amount':
							$goalAmount = 1;
							break;
					}
				}
			}
		}
		else
		{
			$show_field = 1;
		}

		$sortingOptions   = array();
		$sortingOptions[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_FILTER_SELECT_OREDERING'));
		$sortingOptions[] = HTMLHelper::_('select.option', 'title', Text::_('COM_JGIVE_SORT_CAMPAIGN_ALPHABETICALLY'));
		$sortingOptions[] = HTMLHelper::_('select.option', 'created', Text::_('COM_JGIVE_SORT_CAMPAIGN_LATEST'));
		$sortingOptions[] = HTMLHelper::_('select.option', 'oldest', Text::_('COM_JGIVE_SORT_CAMPAIGN_OLDEST'));
		$sortingOptions[] = HTMLHelper::_('select.option', 'end_date', Text::_('COM_JGIVE_SORT_CAMPAIGN_ENDING_SOON'));

		if ($show_field == 1 || $goalAmount == 0)
		{
			$sortingOptions[] = HTMLHelper::_('select.option', 'amount_received', Text::_('COM_JGIVE_SORT_CAMPAIGN_LEAST_AMOUNT_RAISED'));
			$sortingOptions[] = HTMLHelper::_('select.option', 'mostAmountRaised', Text::_('COM_JGIVE_SORT_CAMPAIGN_MOST_AMOUNT_RAISED'));
			$sortingOptions[] = HTMLHelper::_('select.option', 'remaining_amount', Text::_('COM_JGIVE_SORT_CAMPAIGN_CLOSEST_TO_GOAL'));
			$sortingOptions[] = HTMLHelper::_('select.option', 'farthestFromGoal', Text::_('COM_JGIVE_SORT_CAMPAIGN_FARTHEST_FROM_GOAL'));
			$sortingOptions[] = HTMLHelper::_('select.option', 'campaignDonorsCount', Text::_('COM_JGIVE_SORT_CAMPAIGN_LEAST_DONORS'));
			$sortingOptions[] = HTMLHelper::_('select.option', 'campaignMostDonors', Text::_('COM_JGIVE_SORT_CAMPAIGN_MOST_DONORS'));
		}

		return $sortingOptions;
	}

	/**
	 * Function getOrderingDirectionOptions
	 *
	 * @return  Array
	 *
	 * @since   1.8
	 */
	public function getCampignsOrderingDirection()
	{
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_FILTER_SELECT_OREDERING_DIRECTION'));
		$options[] = HTMLHelper::_('select.option', 'ASC', Text::_('COM_JGIVE_ASCENDING'));
		$options[] = HTMLHelper::_('select.option', 'DESC', Text::_('COM_JGIVE_DESCENDING'));

		return $options;
	}
}
