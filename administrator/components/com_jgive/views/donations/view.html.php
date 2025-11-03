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
defined('_JEXEC') or die(';)');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

jimport('techjoomla.tjmoney.tjmoney');
jimport('techjoomla.tjtoolbar.button.csvexport');

HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive_admin.css');
JLoader::import('campaigns', JPATH_ADMINISTRATOR . '/components/com_jgive/helpers');
JLoader::import('integrations', JPATH_SITE . '/components/com_jgive/helpers');

/**
 * Donations view class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveViewDonations extends BaseHtmlView
{
	protected $cData;

	protected $countries;

	protected $donations;

	protected $items;

	protected $pagination;

	protected $total;

	protected $lists;

	protected $layout;

	protected $logged_userid;

	protected $currencyCode;

	protected $params;

	protected $donationsHelper;

	protected $jgiveFrontendHelper;

	protected $user;

	protected $input;

	protected $session;

	protected $gateWays;

	protected $state;

	public $filterForm;

	public $activeFilters;

	public $donationDetails;

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
		/* ----------------------------Since JGive 2.1------------------------------ */
		/* Common Data For all layout */
		$app                        = Factory::getApplication();
		$this->session              = Factory::getSession();
		$this->input                = $app->input;
		$this->user                 = Factory::getUser();

		if (!Factory::getUser($this->user->id)->authorise('core.manage', 'com_jgive'))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_AUTH_ERROR'), 'error');

			return false;
		}

		$jgiveHelper                = new JgiveHelper;
		$this->jgiveFrontendHelper  = new jgiveFrontendHelper;
		$this->donationsHelper      = new DonationsHelper;

		$model                      = $this->getModel();

		// Load submenu
		$jgiveHelper->addSubmenu('donations');

		$this->params         = ComponentHelper::getParams('com_jgive');
		$currency             = $this->params->get('currency');
		$currencySymbolOrCode = $this->params->get('currency_symbol');

		$tjCurrency            = new TjMoney($currency);
		$this->currencyCode = $tjCurrency->getSymbol();

		if ($currencySymbolOrCode === 'code')
		{
			$this->currencyCode = $tjCurrency->getCode();
		}

		$this->logged_userid        = $this->user->id;
		$this->layout               = $app->input->get('layout', 'default');
		$this->retryPayment         = new StdClass;
		$this->retryPayment->status = '';
		$this->retryPayment->msg    = '';

		// Used on donations view for payment status filter
		$this->pstatus = $this->donationsHelper->getSStatusArray();

		/*Layout Donation Details */
		if ($this->layout == 'details')
		{
			// Load language file for component backend
			$lang = Factory::getLanguage();
			$lang->load('com_jgive', JPATH_SITE);
			$donationDetails = $this->get('SingleDonationInfo');

			if (empty($donationDetails))
			{
				$app->enqueueMessage(Text::_('COM_JGIVE_NO_DATA'), 'error');

				return false;
			}

			$this->donationDetails = $donationDetails;
		}

		/*Layout Default Donation */
		if ($this->layout == 'default')
		{
			/* JGive 2.2.0*/
			$this->state         = $this->get('State');
			$this->pagination    = $this->get('Pagination');
			$this->items         = $this->get('Items');

			// Get filter form.
			$this->filterForm    = $this->get('FilterForm');

			// Get active filters.
			$this->activeFilters = $this->get('ActiveFilters');

			$this->lists['filter_order']     = $app->getUserStateFromRequest('com_jgive.filter_order', 'filter_order', 'id', 'string');
			$this->lists['filter_order_Dir'] = $app->getUserStateFromRequest('com_jgive.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
			$this->lists['filter_campaign_type'] = $app->getUserStateFromRequest('com_jgive.filter_campaign_type', 'filter_campaign_type', '', 'string');
		}

		/*Layout Mass Mailing */
		if ($this->layout == 'mass_mailing')
		{
			$selected_donations_ids = $this->session->get('selected_donations_ids');
			$this->selected_emails  = $model->getDonorsEmailByDonationId($selected_donations_ids);
		}

		/*Layout PaymentForm */
		if ($this->layout == 'paymentform')
		{
			$this->countries = $this->jgiveFrontendHelper->getCountries();
			$this->cData     = $this->get('AllCampaigns');
			$noFirst         = $this->session->get('No_first_donation');

			// If no user data set in session then only import the user profile or only first time after login
			if (empty($noFirst))
			{
				$profileImport = $this->params->get('profile_import');

				// If profie import is on the call profile import function
				if ($profileImport)
				{
					$jgiveIntegrationsHelper = new JgiveIntegrationsHelper;
					$profiledata             = $jgiveIntegrationsHelper->profileImport(1);
				}
			}

			$this->guest_donation = $this->params->get('guest_donation');

			// Geteways
			PluginHelper::importPlugin('payment');

			$this->gateways = $this->params->get('gateways');

			$gateways = array();

			if (!empty($this->gateways))
			{
				$gateways = Factory::getApplication()->triggerEvent('onTP_GetInfo', array((array) $this->gateways));
			}

			$this->gateways = $gateways;
		}

		$this->_setToolBar($this->layout);
		$this->sidebar = JHtmlSidebar::render();
		$this->setLayout($this->layout);

		parent::display($tpl);
	}

	/**
	 * Function to set tool bar.
	 *
	 * @param   String  $layout  Get Layout
	 *
	 * @return void
	 *
	 * @since	1.8
	 */
	public function _setToolBar($layout)
	{
		$user       = Factory::getUser();
		$canCreate  = $user->authorise('core.create', 'com_jgive');
		$canEdit    = $user->authorise('core.edit', 'com_jgive');
		$canCheckin = $user->authorise('core.manage', 'com_jgive');
		$canChange  = $user->authorise('core.edit.state', 'com_jgive');
		$canDelete  = $user->authorise('core.delete', 'com_jgive');

		// Get the toolbar object instance
		$bar    = JToolBar::getInstance('toolbar');
		ToolbarHelper::title(Text::_("COM_JGIVE") . ": " . Text::_('COM_JGIVE_DONATIONS'), 'list');

		if ($layout == 'paymentform')
		{
			ToolbarHelper::save('donations.' . $task = 'placeOrder', $alt = Text::_('COM_JGIVE_CONTINUE_CONFIRM_FREE'));
			ToolbarHelper::cancel('donations.' . $task = 'cancelorder', $alt = Text::_('COM_JGIVE_CANCEL'));
		}

		if ($layout == 'mass_mailing')
		{
			ToolbarHelper::save('donations.' . $task = 'emailToSelected', $alt = Text::_('COM_JGIVE_SEND_EMAIL'));
			ToolbarHelper::cancel('donations.' . $task = 'cancelEmail', $alt = Text::_('COM_JGIVE_CANCEL_EMAIL'));
		}

		if ($layout == 'details')
		{
			ToolbarHelper::back('COM_JGIVE_BACK', 'index.php?option=com_jgive&view=donations');
		}

		if ($layout == 'default' or $layout == '')
		{
			if ($canCreate)
			{
				ToolbarHelper::addNew('donations.' . $task = 'addNewDonation');
			}

			if (count($this->items) > 0)
			{
				ToolbarHelper::custom('donations.redirectToMassmailing', 'mail.png', '', Text::_('COM_JGIVE_EMAIL_TO_DONORS'));

				if ($canCreate)
				{
					$message = array();
					$message['success'] = Text::_("COM_JGIVE_EXPORT_FILE_SUCCESS");
					$message['error'] = Text::_("COM_JGIVE_EXPORT_FILE_ERROR");
					$message['inprogress'] = Text::_("COM_JGIVE_EXPORT_FILE_NOTICE");

					$bar->appendButton('CSVExport', $message);
				}

				if ($canDelete)
				{
					ToolbarHelper::deleteList('', 'donations.deleteDonations');
				}
			}
		}

		ToolbarHelper::preferences('com_jgive');
	}
}
