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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

jimport('techjoomla.tjtoolbar.button.csvexport');

JLoader::import('campaigns', JPATH_ADMINISTRATOR . '/components/com_jgive/helpers');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');

/**
 * Report view class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveViewReports extends BaseHtmlView
{
	public $campaignHelper;

	public $campaignsHelper;

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
	 * @return  void.
	 *
	 * @since	1.8
	 */
	public function display($tpl = null)
	{
		$user = Factory::getUser();
		$app  = Factory::getApplication();

		if (!Factory::getUser($user->id)->authorise('core.manage', 'com_jgive'))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_AUTH_ERROR'), 'error');

			return false;
		}

		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->pagination    = $this->get('Pagination');

		$jgiveHelper = new JgiveHelper;
		$jgiveHelper->addSubmenu('reports');

		$this->jgiveFrontendHelper = new jgiveFrontendHelper;
		$this->reportsHelper = new reportsHelper;

		ToolbarHelper::title(Text::_("COM_JGIVE") . ": " . Text::_('COM_JGIVE_REPORTS'), 'list');
		ToolbarHelper::preferences('com_jgive');
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	protected function addToolBar()
	{
		$user       = Factory::getUser();
		$canCreate  = $user->authorise('core.create', 'com_jgive');
		$bar        = JToolBar::getInstance('toolbar');

		if (count($this->items) > 0 && $canCreate)
		{
			$message = array();
			$message['success']    = Text::_("COM_JGIVE_EXPORT_FILE_SUCCESS");
			$message['error']      = Text::_("COM_JGIVE_EXPORT_FILE_ERROR");
			$message['inprogress'] = Text::_("COM_JGIVE_EXPORT_FILE_NOTICE");

			$bar->appendButton('CSVExport', $message);
		}
	}
}
