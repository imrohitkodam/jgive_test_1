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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Donations 80G certificate view class.
 *
 * @package  JGive
 * @since    2.3.0
 */
class JgiveViewreceiptTemplate extends BaseHtmlView
{
	protected $sidebar;

	protected $params;

	/**
	 * Function to display.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths
	 *
	 * @return  boolean|null
	 *
	 * @since	2.3.0
	 */
	public function display($tpl = null)
	{
		$app          = Factory::getApplication();
		$user         = Factory::getUser();
		$this->params = ComponentHelper::getParams('com_jgive');

		if (!Factory::getUser($user->id)->authorise('core.manage', 'com_jgive'))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_AUTH_ERROR'), 'error');

			return false;
		}

		// Load submenu
		$jgiveHelper = new JgiveHelper;
		$jgiveHelper->addSubmenu('receipttemplate');

		$this->_setToolBar();
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Function to set tool bar.
	 *
	 * @return void
	 *
	 * @since	2.3.0
	 */
	public function _setToolBar()
	{
		ToolbarHelper::title(Text::_("COM_JGIVE") . ": " . Text::_('COM_JGIVE_RECEIPT_TEMPLATE'), 'pencil.png');
		ToolbarHelper::preferences('com_jgive');
		ToolbarHelper::cancel('receipttemplate.cancel');
		ToolbarHelper::apply('receipttemplate.save');
	}
}
