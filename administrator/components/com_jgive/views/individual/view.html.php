<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

JLoader::import('fronthelper', JPATH_SITE . '../../components/com_tjvendors/helpers');

/**
 * Individual view class.
 *
 * @package  JGive
 * @since    2.3.0
 */
class JgiveViewIndividual extends HtmlView
{
	public $form = null;

	protected $item;

	protected $params;

	protected $default_country;

	protected $countries;

	protected $tmpl;

	protected $user;

	/**
	 * Display the Individual form view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$app = Factory::getApplication();
		$this->form            = $this->get('Form');
		$this->item            = $this->get('Item');

		$this->params          = ComponentHelper::getParams('com_jgive');
		$this->default_country = $this->params->get('default_country');
		$this->tmpl            = $app->input->get('tmpl', '', 'STRING');
		$this->user            = Factory::getUser();
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$this->countries     = $jgiveFrontendHelper->getCountries();


		// Set the toolbar
		$this->addToolBar();
		$this->_setToolBar();

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
		$input = Factory::getApplication()->input;

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);

		ToolbarHelper::apply('individual.apply');
		ToolbarHelper::save('individual.save');
		ToolbarHelper::save2new('individual.save2new');
		ToolbarHelper::cancel(
			'individual.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);
	}

	/**
	 * Function to set tool bar.
	 *
	 * @return void
	 *
	 * @since	1.8
	 */
	public function _setToolBar()
	{
		$title = ($this->item->id == 0) ? Text::_('COM_JGIVE_NEW_INDIVIDUAL'): Text::_('COM_JGIVE_EDIT_INDIVIDUAL');

		// Get the toolbar object instance
		ToolbarHelper::title(Text::_("COM_JGIVE") . ": " . $title, 'pencil.png');
		ToolbarHelper::preferences('com_jgive');
	}
}
