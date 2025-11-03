<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

jimport('techjoomla.tjmoney.tjmoney');

/**
 * Dashboard form controller class.
 *
 * @package  JGive
 * @since    1.8
 */
class JGiveViewCp extends HtmlView
{
	protected $params;

	protected $currencySymbol;

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
		// Load submenu
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if (!Factory::getUser($user->id)->authorise('core.manage', 'com_jgive'))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_AUTH_ERROR'), 'error');

			return false;
		}

		// Get download id
		$this->params         = ComponentHelper::getParams('com_jgive');
		$this->downloadid     = $this->params->get('downloadid');
		$currency             = $this->params->get('currency');
		$currencySymbolOrCode = $this->params->get('currency_symbol');

		$tjCurrency            = new TjMoney($currency);
		$this->currencySymbol = $tjCurrency->getSymbol();

		if ($currencySymbolOrCode === 'code')
		{
			$this->currencySymbol = $tjCurrency->getCode();
		}

		// Get installed version from xml file
		$xml     = simplexml_load_file(JPATH_COMPONENT . '/jgive.xml');
		$version = (string) $xml->version;
		$this->version = $version;

		$model = $this->getModel();
		$model->onRefreshUpdateSite();

		// Get dashboard data
		$this->dashBoard = $this->get('DashboardData');
		$this->allMonthName = $this->get('AllMonths');
		$this->monthDonation = $this->get('MonthDonation');
		$this->recentDonationDetails = $this->get('RecentDonationDetails');
		$this->tot_periodicDonationsCount = $this->get('PeriodicDonationsCount');
		$this->statsForPie = $model->statsforpie();

		$this->pendingPayouts = $this->get('PendingPayouts');

		// Get new version
		$this->latestVersion = $this->get('LatestVersion');

		$JgiveHelper = new JgiveHelper;
		$JgiveHelper->addSubmenu('cp');

		$this->_setToolBar();

		$this->sidebar = JHtmlSidebar::render();

		if (!Factory::getApplication()->input->get('layout') || Factory::getApplication()->input->get('layout') != 'dashboard')
		{
			$this->setLayout('dashboard');
		}

		parent::display($tpl);
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
		$document = Factory::getDocument();
		HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive_admin.css');

		$toolbar = Toolbar::getInstance('toolbar');
		$toolbar->appendButton(
		'Custom', '<a id="tjHouseKeepingFixDatabasebutton" class="btn btn-default hidden"><span class="icon-refresh"></span>'
		. Text::_('COM_JGIVE_FIX_DATABASE') . '</a>');

		ToolbarHelper::title(Text::_("COM_JGIVE") . ": " . Text::_('COM_JGIVE_CP'), 'list');
		/*ToolbarHelper::custom('cp.fixDatabase', 'refresh', 'refresh', 'COM_JGIVE_FIX_DATABASE', false);*/
		ToolbarHelper::preferences('com_jgive');
	}
}
