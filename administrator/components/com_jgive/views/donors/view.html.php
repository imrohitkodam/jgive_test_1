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
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

jimport('techjoomla.tjtoolbar.button.csvexport');

/**
 * View class for a list of Donors.
 *
 * @since  1.6
 */
class JGiveViewDonors extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$mainframe = Factory::getApplication();
		$user      = Factory::getUser();

		// Declare FrontendHelper object
		$this->jgiveFrontendHelper = new jgiveFrontendHelper;

		// Default layout is default
		$this->input = Factory::getApplication()->input;
		$layout = $this->input->get('layout', 'default');
		$this->setLayout($layout);

		if (!Factory::getUser($user->id)->authorise('core.manage', 'com_jgive'))
		{
			$mainframe->enqueueMessage(Text::_('COM_JGIVE_AUTH_ERROR'), 'error');

			return false;
		}

		// Get campaign itemid
		$this->singleCampaignItemid = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default');

		$this->state = $this->get('State');
		$this->items = $this->get('Items');

		$this->pagination = $this->get('Pagination');

		// Get filter form.
		$this->filterForm = $this->get('FilterForm');

		// Get active filters.
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$JgiveHelper = new JgiveHelper;
		$JgiveHelper->addSubmenu('donors');

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/donors.php';
		$state = $this->get('State');
		$canDo = JGiveHelpersDonors::getActions();
		$bar    = JToolBar::getInstance('toolbar');

		ToolbarHelper::title(Text::_("COM_JGIVE") . ": " . Text::_('COM_JGIVE_TITLE_DONORS'), 'list');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/donor';

		if (count($this->items) > 0 && $canDo->get('core.create'))
		{
			ToolbarHelper::custom('donors.redirectToMassmailing', 'mail.png', '', Text::_('COM_JGIVE_EMAIL_TO_DONORS'));

			$message = array();
			$message['success'] = Text::_("COM_JGIVE_EXPORT_FILE_SUCCESS");
			$message['error'] = Text::_("COM_JGIVE_EXPORT_FILE_ERROR");
			$message['inprogress'] = Text::_("COM_JGIVE_EXPORT_FILE_NOTICE");

			$bar->appendButton('CSVExport', $message);
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences('com_jgive');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_jgive&view=donors');

		$this->extra_sidebar = '';
	}

	/**
	 * Method to order fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array
		(
			'a.`id`' => Text::_('JGRID_HEADING_ID'),
			'a.`ordering`' => Text::_('JGRID_HEADING_ORDERING'),
			'a.`state`' => Text::_('JSTATUS'),
			'a.`first_name`' => Text::_('COM_JGIVE_DONORS_FIRST_NAME'),
			'a.`last_name`' => Text::_('COM_JGIVE_DONORS_LAST_NAME'),
			'a.`address`' => Text::_('COM_JGIVE_DONORS_ADDRESS'),
			'a.`address2`' => Text::_('COM_JGIVE_DONORS_ADDRESS2'),
			'a.`city`' => Text::_('COM_JGIVE_DONORS_CITY'),
			'a.`country`' => Text::_('COM_JGIVE_DONORS_COUNTRY'),
			'a.`zip`' => Text::_('COM_JGIVE_DONORS_ZIP'),
			'a.`phone`' => Text::_('COM_JGIVE_DONORS_PHONE'),
			'a.`user_id`' => Text::_('COM_JGIVE_DONORS_USER_ID'),
			'a.`campaign_id`' => Text::_('COM_JGIVE_DONORS_CAMPAIGN_ID'),
			'a.`email`' => Text::_('COM_JGIVE_DONORS_EMAIL'),
		);
	}
}
