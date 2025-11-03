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

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');

/**
 * jgiveViewCampaign class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JgiveViewCampaigns extends HtmlView
{
	protected $campaignHelper;

	protected $lists;

	protected $params;

	/**
	 * Class constructor.
	 *
	 * @param   Array  $config  Config
	 *
	 * @since   2.1
	 */
	public function __construct($config = array())
	{
		$this->campaignHelper = new CampaignHelper;

		parent::__construct($config = array());
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$mainframe = Factory::getApplication();
		$input     = Factory::getApplication()->input;
		$option    = $input->get('option', '', 'STRING');

		$callback = $input->get('callback', '', 'STRING');

		// Get logged in user id
		$user                = Factory::getUser();
		$this->logged_userid = $user->id;

		// This is frontend
		$this->issite = 1;

		$jgiveFrontendHelper = new jgiveFrontendHelper;

		// Get itemid
		$this->singleCampaignItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		$this->myCampaignsItemid    = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');
		$this->allCampaignsItemid   = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		$this->createCampaignItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaignform');

		// Get some data from the models
		$state  = $this->get('State');
		$params = $state->params;
		$this->params        = $params;

		// Default layout is all
		$layout = Factory::getApplication()->input->get('layout', 'all');
		$this->setLayout($layout);

		// Category fillter
		$this->cat_options = $this->campaignHelper->getCampaignsCategories();

		// Get filter value and set list
		$filter_campaign_cat = $mainframe->getUserStateFromRequest(
															'com_jgive.filter_campaign_cat', 'filter_campaign_cat',
															$this->params->get('defaultCatId'), 'INT'
																	);

		$lists['filter_campaign_cat'] = $filter_campaign_cat;
		$this->lists                  = $lists;

		// Campaigns Type fillter

		// Get data from the model
		$this->data = $this->get('Data');

		$pagination = $this->get('Pagination');

		$this->pagination = $pagination;

		// Ordering
		$filter_order = $mainframe->getUserStateFromRequest(
														'com_jgive.filter_order', 'filter_order',
														$this->params->get('default_sort_by_option'), 'string'
															);

		$filter_order_Dir = $mainframe->getUserStateFromRequest(
		'com_jgive.filter_order_Dir', 'filter_order_Dir', $this->params->get('filter_order_Dir'), 'string'
		);

		// Load all filter values
		$this->campaign_type_filter_options = $this->get('CampaignTypeFilterOptions');
		$this->ordering_options             = $this->get('CampignsOrderingOptions');
		$this->ordering_direction_options   = $this->get('CampignsOrderingDirection');

		// Get Countries for filter
		$countries              = $jgiveFrontendHelper->getCountries();
		$this->countries_filter = $countries;

		// Organization_individual_type filter since version 1.5.1
		$org_ind_type              = $this->campaignHelper->organization_individual_type();
		$this->filter_org_ind_type = $org_ind_type;

		$filter_user          = $mainframe->getUserStateFromRequest('com_jgive' . 'filter_user', 'filter_user');
		$filter_campaign_type = $mainframe->getUserStateFromRequest('com_jgive' . 'filter_campaign_type', 'filter_campaign_type');

		$filter_org_ind_type    = $mainframe->getUserStateFromRequest('com_jgive' . 'filter_org_ind_type', 'filter_org_ind_type');
		$filter_org_ind_type_my = $mainframe->getUserStateFromRequest('com_jgive' . 'filter_org_ind_type_my', 'filter_org_ind_type_my');

		$campaign_countries_filter = $mainframe->getUserStateFromRequest('com_jgive' . 'campaign_countries', 'campaign_countries');
		$campaign_states_filter    = $mainframe->getUserStateFromRequest('com_jgive' . 'campaign_states', 'campaign_states');
		$campaign_city_filter      = $mainframe->getUserStateFromRequest('com_jgive' . 'campaign_city', 'campaign_city');

		// Default value O - Will show 'ongoing' campaigns by default.
		$filter_campaigns_to_show = $mainframe->getUserStateFromRequest('com_jgive' . 'campaigns_to_show', 'campaigns_to_show', '0');

		// For countries
		$countryarray       = array();
		$countryarray[]     = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELONE_COUNTRY'));

		// Get countries
		$campaign_countries = $this->get('countries');

		foreach ($campaign_countries as $tmp)
		{
			$value          = $tmp->country_id;
			$count          = $tmp->country;
			$countryarray[] = HTMLHelper::_('select.option', $value, $count);
		}

		$this->countryoption = $countryarray;

		// For state
		$statearray      = array();
		$statearray[]    = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELECT_STATE'));

		// Get states
		$campaign_states = $this->get('CampaignStates');

		if (isset($campaign_states))
		{
			foreach ($campaign_states as $tmp)
			{
				$value        = $tmp->id;
				$state        = $tmp->region;
				$statearray[] = HTMLHelper::_('select.option', $value, $state);
			}
		}

		$this->campaign_states = $statearray;

		// For city
		$cityarray             = array();
		$cityarray[]           = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELECT_CITY'));

		// Get states
		$campaign_city         = $this->get('CampaignCity');

		if (isset($campaign_city))
		{
			foreach ($campaign_city as $tmp)
			{
				$value       = $tmp->id;
				$city        = $tmp->city;
				$cityarray[] = HTMLHelper::_('select.option', $value, $city);
			}
		}

		$this->campaign_city = $cityarray;

		// Get Campaigns to show filter options

		$campaigns_to_show       = $this->campaignHelper->campaignsToShowOptions();
		$this->campaigns_to_show = $campaigns_to_show;

		// Set all filters in list
		$lists['filter_order']           = $filter_order;
		$lists['filter_order_Dir']       = $filter_order_Dir;
		$lists['filter_user']            = $filter_user;
		$lists['filter_campaign_type']   = $filter_campaign_type;
		$lists['campaign_countries']     = $campaign_countries_filter;
		$lists['campaign_states']        = $campaign_states_filter;
		$lists['campaign_city']          = $campaign_city_filter;
		$lists['filter_org_ind_type']    = $filter_org_ind_type;
		$lists['filter_org_ind_type_my'] = $filter_org_ind_type_my;
		$lists['campaigns_to_show']      = $filter_campaigns_to_show;

		$this->lists = $lists;

		// Added by Sneha for search and filter
		$filter_state = $mainframe->getUserStateFromRequest($option . 'search_list', 'search_list', '', 'string');
		$start_date   = $mainframe->getUserStateFromRequest($option . 'start_date', 'start_date', '', 'string');
		$end_date     = $mainframe->getUserStateFromRequest($option . 'end_date', 'end_date', '', 'string');

		$lists['search_list'] = $filter_state;
		$lists['start_date']  = $start_date;
		$lists['end_date']    = $end_date;

		$this->assignRef('lists', $lists);
		$this->assignRef('date', $date);

		$campaigns = $this->map($this->data);

		$filters = $this->mapFilters(
									$this->ordering_options, $this->ordering_direction_options,
									$this->user_filter_options, $this->filter_org_ind_type, $this->countryoption,
									$this->campaign_states, $this->campaign_city, $this->campaigns_to_show,
									$this->campaign_type_filter_options, $this->cat_options
									);

		$mapped_data = Array();

		$mapped_data['campaigns']        = $campaigns;
		$mapped_data['filters']          = $filters;
		$mapped_data['site_root_link']   = Uri::root();

		// Add component params
		$mapped_data['com_jgive_params'] = $params;

		if (!count($mapped_data))
		{
			echo $callback ? $callback . '(' . json_encode(array()) . ')' : json_encode(array());
			jexit();
		}

		echo $callback ? $callback . '(' . json_encode($mapped_data) . ')' : json_encode($mapped_data);
		jexit();
	}

	/**
	 * Method Mapp
	 *
	 * @param   Array  $data  Data
	 *
	 * @return $mapped
	 */
	public function map($data)
	{
		$mapped = Array();
		$i = 0;

		foreach ($data as $key => $item)
		{
			$mapped[$i]     = $this->campaignHelper->mapData($item, $this->singleCampaignItemid);
			$i++;
		}

		return $mapped;
	}

	/**
	 * Method  mapFilters
	 *
	 * @param   String  $ordering_options              Ordering options
	 * @param   String  $ordering_direction_options    Ordering Direction options
	 * @param   String  $user_filter_options           User filter options
	 * @param   String  $filter_org_ind_type           Filter org Type
	 * @param   String  $countryoption                 Country options
	 * @param   String  $campaign_states               Campaign States
	 * @param   String  $campaign_city                 Campaign city
	 * @param   String  $campaigns_to_show             Campaign
	 * @param   String  $campaign_type_filter_options  Campaign Type Filter options
	 * @param   String  $cat_options                   Category options
	 *
	 * @return  filters
	 */
	public function mapFilters(
		$ordering_options, $ordering_direction_options, $user_filter_options, $filter_org_ind_type,
		$countryoption, $campaign_states, $campaign_city, $campaigns_to_show,
		$campaign_type_filter_options, $cat_options)
	{
		$filters = Array();

		$filters['ordering_options']             = $ordering_options;
		$filters['ordering_direction_options']   = $ordering_direction_options;
		$filters['user_filter_options']          = $user_filter_options;
		$filters['filter_org_ind_type']          = $filter_org_ind_type;
		$filters['countryoption']                = $countryoption;
		$filters['campaign_states']              = $campaign_states;
		$filters['campaign_city']                = $campaign_city;
		$filters['campaigns_to_show']            = $campaigns_to_show;
		$filters['campaign_type_filter_options'] = $campaign_type_filter_options;
		$filters['cat_options']                  = $cat_options;

		return $filters;
	}
}
