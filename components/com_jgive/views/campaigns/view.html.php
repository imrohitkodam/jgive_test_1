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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::import('campaigns', JPATH_SITE . '/components/com_jgive/models');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');
JLoader::import('donors', JPATH_SITE . '/components/com_jgive/models');

/**
 * JgiveViewCampaigns
 *
 * @package     Jgive
 * @subpackage  Jgive controller
 * @since       1.0
 */
class JgiveViewCampaigns extends HtmlView
{
	protected $pagination;

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
		$this->campaignHelper = new campaignHelper;
		$this->jgiveFrontendHelper = new jgiveFrontendHelper;

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

		// Get data from the model
		$this->model = $this->getModel();
		$this->model->setState('displayPins', 1);
		$this->data = $this->model->getItems();
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->params = $this->state->params;
		$this->currentUrl = Uri::getInstance()->toString();

		// This is frontend
		$user = Factory::getUser();
		$this->logged_userid = $user->id;

		// Condition for checking to show "Launch a campaign" link to user or not
		$this->canCreate  = $user->authorise('core.create', 'com_jgive');

		$layout = $mainframe->input->get('layout', 'all');

		// My Campaign view
		if ($layout == 'my')
		{
			if (!$this->logged_userid)
			{
				$msg = Text::_('COM_JGIVE_LOGIN_MSG');
				$uri = $mainframe->input->get('REQUEST_URI', '', 'server', 'string');
				$url = base64_encode($uri);
				$mainframe->enqueueMessage($msg, 'error');
				$mainframe->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url));
			}
		}

		if (($this->params->get('integration') == 'jomsocial') && $this->params->get('jomsocial_toolbar'))
		{
			$this->jomsocialToolbarHtml = $this->jgiveFrontendHelper->jomsocialToolbarHtml();
		}

		// Get itemids
		$menu = $mainframe->getMenu();

		$activeMenu = $menu->getActive();

		if (!empty($activeMenu))
		{
			$menuItemId = $activeMenu->id;
		}

		$this->singleCampaignItemid = !empty($menuItemId)?$menuItemId:'';
		$this->allCampaignsItemid = !empty($menuItemId)?$menuItemId:'';

		// All Pin or Blog view
		$this->ordering_options = $this->get('CampignsOrderingOptions');
		$this->ordering_direction_options = $this->get('getCampignsOrderingDirection');
		$this->cat_options = $this->campaignHelper->getCampaignsCategories();
		$this->campaigns_to_show = $this->campaignHelper->campaignsToShowOptions();
		$this->campaign_type_filter_options = $this->campaignHelper->getCampaignTypeFilterOptions();
		$this->filter_org_ind_type = $this->campaignHelper->organization_individual_type();

		// List of available filters on campaigns list view
		$this->availableFilters = array(
			"filter_campaign_type", "filter_campaign_cat", "filter_org_ind_type",
			"limit","limitstart", "filter_order", "filter_order_Dir", "filter_campaigns_to_show",
			"filter_campaign_countries", "filter_campaign_states", "filter_campaign_city", "filter_search"
		);

		if ($this->params->get('show_search_filter'))
		{
			$this->lists['filter_search'] = $this->escape($this->state->get('filter_search'));
		}

		if ($this->params->get('show_place_filter'))
		{
			// For countries
			$countryArray = array();
			$countryArray[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELONE_COUNTRY'));
			$campaign_countries = $this->get('FilterCountries');

			foreach ($campaign_countries  as $campaign_country)
			{
				$value  = $campaign_country->country_id;
				$option = $campaign_country->country;
				$countryArray[] = HTMLHelper::_('select.option', $value, $option);
			}

			$this->countryoption = $countryArray;

			// For state
			$stateArray   = array();
			$stateArray[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELECT_STATE'));
			$campaign_states = $this->get('CampaignsFilterStates');

			if (isset($campaign_states))
			{
				foreach ($campaign_states  as $campaign_state)
				{
					$value        = $campaign_state->id;
					$option       = $campaign_state->region;
					$stateArray[] = HTMLHelper::_('select.option', $value, $option);
				}
			}

			$this->campaign_states = $stateArray;

			// For city
			$cityArray   = array();
			$cityArray[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELECT_CITY'));
			$campaign_cities = $this->get('CampaignsFilterCities');

			if (isset($campaign_cities))
			{
				foreach ($campaign_cities  as $campaign_city)
				{
					if ($campaign_city->id )
					{
						$value       = $campaign_city->id;
						$option      = $campaign_city->city;
						$cityArray[] = HTMLHelper::_('select.option', $value, $option);
					}
					elseif (empty($campaign_city->id) && isset($campaign_city->othercity))
					{
						$value       = $campaign_city->othercity;
						$option      = $campaign_city->othercity;
						$cityArray[] = HTMLHelper::_('select.option', $value, $option);
					}
				}
			}

			$this->campaign_city = $cityArray;
		}

		$filterOrder                             = $mainframe->input->get('filter_order', '', "STRING");
		$this->lists['filter_order']             = ($filterOrder) ? $filterOrder : $this->state->get('filter_order');
		$this->lists['filter_order_Dir']         = $this->state->get('filter_order_Dir');
		$this->lists['filter_campaign_cat']      = $this->state->get(
		'filter_campaign_cat') ? $this->state->get(
		'filter_campaign_cat') : $this->state->params->get('defaultCatId');
		$this->lists['filter_campaigns_to_show'] = $this->state->get('filter_campaigns_to_show');
		$this->lists['filter_campaign_type']     = $this->state->get('filter_campaign_type');
		$this->lists['filter_org_ind_type']      = $this->state->get('filter_org_ind_type');

		$this->otherData['createCampItemid']     = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaignform');
		$this->otherData['myCampaignsItemid']    = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');
		$this->otherData['singleCampaignItemid'] = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default');
		$this->otherData['allCampaignsItemid']   = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');

		// Default layout is all
		$this->setLayout($layout);

		parent::display($tpl);
	}
}
