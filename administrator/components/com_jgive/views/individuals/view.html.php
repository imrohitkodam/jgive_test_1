<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

jimport('techjoomla.tjtoolbar.button.csvexport');

/**
 * Individuals view class.
 *
 * @package  JGive
 * @since    2.3.0
 */

class JgiveViewIndividuals extends HtmlView
{
	public $state;

	public $items;

	public $pagination;

	public $sidebar;

	public $filterForm;

	public $activeFilters;

	/**
	 * Function to display.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths
	 *
	 * @return  boolean
	 *
	 * @since   2.3.0
	 */
	public function display($tpl = null)
	{
		$jgiveHelper = new JgiveHelper;
		$app         = Factory::getApplication();
		$user        = Factory::getUser();

		if (!Factory::getUser($user->id)->authorise('core.manage', 'com_jgive'))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_AUTH_ERROR'), 'error');

			return false;
		}

		// Load submenu
		$jgiveHelper->addSubmenu('individuals');

		// Get state
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Get the toolbar object instance
		ToolbarHelper::title(Text::_("COM_JGIVE") . ": " . Text::_('COM_JGIVE_INDIVIDUALS'), 'list');

		// Set the toolbar
		$this->addToolBar();
		ToolbarHelper::preferences('com_jgive');

		$this->sidebar = JHtmlSidebar::render();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.3.0
	 */
	protected function addToolBar()
	{
		$user      = Factory::getUser();
		$canCreate = $user->authorise('core.create', 'com_jgive');
		$canEdit   = $user->authorise('core.edit', 'com_jgive');
		$canDelete = $user->authorise('core.delete', 'com_jgive');
		$bar       = ToolBar::getInstance('toolbar');

		if ($canCreate)
		{
			// Add buttons on toolbar
			ToolbarHelper::addNew('individual.add');
		}

		if ($canEdit && !empty($this->items))
		{
			ToolbarHelper::editList('individual.edit');
		}

		if ($canDelete && !empty($this->items))
		{
			ToolbarHelper::deleteList('', 'individuals.delete');
			ToolbarHelper::divider();

			$message = array();
			$message['success']    = Text::_("COM_JGIVE_EXPORT_FILE_SUCCESS");
			$message['error']      = Text::_("COM_JGIVE_EXPORT_FILE_ERROR");
			$message['inprogress'] = Text::_("COM_JGIVE_EXPORT_FILE_NOTICE");

			$bar->appendButton('CSVExport', $message);
		}
	}
}
