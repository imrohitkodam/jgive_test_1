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
defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

JLoader::import('csvexport', JPATH_SITE . '/libraries/techjoomla/tjtoolbar/button');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');
JLoader::import('campaigns', JPATH_ADMINISTRATOR . '/components/com_jgive/helpers');

/**
 * View class for a list of cities.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.7
 */

class JgiveViewCampaigns extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $input;

	protected $jgiveFrontendHelper;

	protected $campaignsHelper;

	protected $campaign_type;

	protected $lists;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display ($tpl = null)
	{
		$app    = Factory::getApplication();
		$user   = Factory::getUser();
		$layout = $app->input->get('layout');

		if (!Factory::getUser($user->id)->authorise('core.manage', 'com_jgive'))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_AUTH_ERROR'), 'error');

			return false;
		}

		$this->state = $this->get('State');

		if ($layout == 'all_list_select' || $layout == 'modal')
		{
			$model = $this->getModel();
			$model->setState("publishedCampaigns", "1");
			$model->setState("filter.filter_search", "");
			$model->setState("filter.campaign_category", "");
			$model->setState("filter.publish_states", "");
			$model->setState("filter.campaign_type", "");
			$model->setState("filter.org_ind_type", "");
			$this->items = $this->get('ActiveCampaigns');
		}
		else
		{
			$this->items               = $this->get('Items');
		}

		$this->pagination          = $this->get('Pagination');
		$this->activeFilters       = $this->get('ActiveFilters');
		$this->input               = $app->input;
		$this->params              = ComponentHelper::getParams('com_jgive');

		// Declare helpers objects
		$this->jgiveFrontendHelper = new jgiveFrontendHelper;
		$this->campaignHelper      = new campaignHelper;
		$this->campaignsHelper     = new campaignsHelper;

		// Get campaign status
		$this->campaignSuccessStatus = $this->campaignsHelper->getCampaignSuccessStatusArray();

		// Get campaign itemid
		$this->singleCampaignItemid  = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Add submenu
		JgiveHelper::addSubmenu('campaigns');

		$this->publish_states = array(
			'' => Text::_('JOPTION_SELECT_PUBLISHED'),
			'1'  => Text::_('JPUBLISHED'),
			'0'  => Text::_('JUNPUBLISHED')
		);

		$this->campaign_type = $this->campaignHelper->getCampaignTypeFilterOptions();
		$this->org_ind_type  = $this->campaignHelper->organization_individual_type();

		$filter_order = $app->getUserStateFromRequest('com_jgive.filter_order', 'filter_order', '', '');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jgive.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_campaign_cat = $app->getUserStateFromRequest('com_jgive.filter_campaign_cat', 'filter_campaign_cat', '', 'INT');

		$this->lists['filter_order_Dir'] = $filter_order_Dir;
		$this->lists['filter_order'] = $filter_order;
		$this->lists['filter_campaign_cat'] = $filter_campaign_cat;

		$this->issite = 0;

		$this->sidebar = JHtmlSidebar::render();
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   1.7
	 */
	protected function getSortFields ()
	{
		return array(
			'a.ordering' => Text::_('COM_JGIVE_ORDERING'),
			'a.published' => Text::_('COM_JGIVE_PUBLISHED'),
			'a.title' => Text::_('COM_JGIVE_TITLE'),
			'a.start_date' => Text::_('COM_JGIVE_START_DATE'),
			'a.end_date' => Text::_('COM_JGIVE_END_DATE'),
			'a.goal_amount' => Text::_('COM_JGIVE_GOAL_AMOUNT'),
			'a.featured' => Text::_('COM_JGIVE_FEATURED'),
			'a.success_status' => Text::_('COM_JGIVE_SUCCESS_STATUS'),
			'a.id' => Text::_('COM_JGIVE_ID')
		);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/jgive.php';

		$state      = $this->get('State');
		$user       = Factory::getUser();
		$canCreate  = $user->authorise('core.create', 'com_jgive');
		$canEdit    = $user->authorise('core.edit', 'com_jgive');
		$canCheckin = $user->authorise('core.manage', 'com_jgive');
		$canChange  = $user->authorise('core.edit.state', 'com_jgive');
		$canDelete  = $user->authorise('core.delete', 'com_jgive');

		// Get the toolbar object instance
		$bar = ToolBar::getInstance('toolbar');

		ToolbarHelper::title(Text::_("COM_JGIVE") . ": " . Text::_('COM_JGIVE_CAMPAIGNS'), 'list');

		if ($canCreate)
		{
			// Add buttons on toolbar
			ToolbarHelper::addNew('campaigns.addNew');
		}


		if (JVERSION >= '4.0.0')
		{
			$dropdown = $bar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();
		}

		if (JVERSION < '4.0.0')
		{
			ToolbarHelper::custom('campaigns.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
			ToolbarHelper::custom('campaigns.unfeatured', 'star-empty', '', 'JUNFEATURED');
		}
		else
		{
			$childBar->standardButton('featured')->text('JFEATURE')->task('campaigns.featured')->listCheck(true);
			$childBar->standardButton('featured')->text('JUNFEATURED')->task('campaigns.unfeatured')->listCheck(true);
		}

		if ($canChange)
		{
			if (JVERSION < '4.0.0')
			{
				ToolbarHelper::publish('campaigns.publish', 'JTOOLBAR_PUBLISH', true);
				ToolbarHelper::unpublish('campaigns.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			}
			else
			{
				$childBar->publish('campaigns.publish')->listCheck(true);
				$childBar->unpublish('campaigns.unpublish')->listCheck(true);
			}
		}

		if ($canDelete)
		{
			if (JVERSION < '4.0.0')
			{
				ToolbarHelper::deleteList('', 'campaigns.delete', 'COM_JGIVE_TOOLBAR_DELETE');
			}
			else
			{
				$childBar->delete('campaigns.delete')->listCheck(true);
			}
		}

		if ($canChange)
		{
			$message               = array();
			$message['success']    = Text::_("COM_JGIVE_EXPORT_FILE_SUCCESS");
			$message['error']      = Text::_("COM_JGIVE_EXPORT_FILE_ERROR");
			$message['inprogress'] = Text::_("COM_JGIVE_EXPORT_FILE_NOTICE");
			$bar->appendButton('CSVExport', $message);
		}

		HTMLHelper::_('bootstrap.modal', 'collapseModal');
		ToolbarHelper::preferences('com_jgive');
	}
}
