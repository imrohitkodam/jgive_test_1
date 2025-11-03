<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('techjoomla.tjmoney.tjmoney');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * JgiveViewDonations form view class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JgiveViewDonations extends BaseHtmlView
{
	protected $myDonations;

	protected $pagination;

	protected $lists;

	protected $gateways;

	protected $countries;

	protected $logged_userid;

	protected $session;

	protected $layout;

	protected $params;

	protected $user;

	protected $guest_donation;

	protected $input;

	protected $cdata;

	protected $gateWays;

	protected $state;

	protected $donationDetails;

	protected $pstatus;

	protected $sstatus;

	protected $myDonationItemId;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		/* ----------------------------Since JGive 2.1------------------------------ */
		/* Common Data For all layout */
		$app          = Factory::getApplication();
		$this->layout = $app->input->get('layout', 'payment');

		$this->jgiveFrontendHelper = new jgiveFrontendHelper;
		$this->donationsHelper     = new DonationsHelper;

		$this->session    = Factory::getSession();
		$this->params     = ComponentHelper::getParams('com_jgive');
		$this->userParams = ComponentHelper::getParams('com_users');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaign');
		$jgiveModelCampaign = BaseDatabaseModel::getInstance('campaign', 'JGiveModel');
		$this->myDonationItemId   = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations');
		$myDonationLink     = Route::_('index.php?option=com_jgive&view=donations&Itemid=' . $this->myDonationItemId, false);

		// Check user loggdin or not
		$this->user           = Factory::getUser();
		$this->logged_userid  = $this->user->id;
		$this->guest_donation = $this->params->get('guest_donation');
		$this->hideShowFields = $this->params->get('show_selected_fields_on_donation');
		$this->hideFields     = $this->params->get('donationfield', array(), 'ARRAY');

		// Display Jomsocial Tool bar on layout page at top
		if (($this->params->get('integration') == 'jomsocial') && $this->params->get('jomsocial_toolbar'))
		{
			$this->jomsocialToolbarHtml = $this->jgiveFrontendHelper->jomsocialToolbarHtml();
		}

		/* Layout Payment */
		$this->input = $app->input;

		// Get country list options
		$this->countries       = $this->jgiveFrontendHelper->getCountries();
		$this->default_country = $this->params->get('default_country');
		$currency              = $this->params->get('currency');
		$currencySymbolOrCode  = $this->params->get('currency_symbol');
		$tjCurrency            = new TjMoney($currency);
		$this->currencySymbol  = $tjCurrency->getSymbol();

		if ($currencySymbolOrCode === 'code')
		{
			$this->currencySymbol = $tjCurrency->getCode();
		}

		// Used on donations view for payment status filter
		$this->pstatus = $this->donationsHelper->getSStatusArray();
		$this->sstatus = $this->donationsHelper->getSStatusArray();

		// Imp This is frontend
		$this->donations_site = 1;

		// PAYMENT
		PluginHelper::importPlugin('payment');
		$gatewayParam = array();

		if (!is_array($this->params->get('gateways')))
		{
			$gatewayParam[] = $this->params->get('gateways');
		}
		else
		{
			$gatewayParam = $this->params->get('gateways');
		}

		if (!empty($gatewayParam))
		{
			$this->gateWays = $app->triggerEvent('onTP_GetInfo', array($gatewayParam));
		}
		/* Common Data For all layout ended here*/

		/*Layout My & All Donations*/
		if ($this->layout == 'default' || $this->layout == 'all_donations')
		{
			if (!$this->logged_userid)
			{
				$msg = Text::_('COM_JGIVE_LOGIN_MSG');
				$uri = $_SERVER["REQUEST_URI"];
				$url = base64_encode($uri);
				$app->enqueueMessage($msg, 'error');
				$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
			}

			/* New code*/
			$this->isroot                  = $this->user->authorise('core.admin');
			$this->state                   = $this->get('State');
			$this->myDonations             = $this->get('Items');
			$this->pagination              = $this->get('Pagination');
			$this->filterForm              = $this->get('FilterForm');
			$this->activeFilters           = $this->get('ActiveFilters');
			$this->currentUrl              = Uri::getInstance()->toString();
			$this->lists['payment_status'] = $this->state->get('payment_status');
			$this->sortDirection           = $app->input->get('filter_order_Dir', 'desc', 'STRING');
			$this->commissionFee           = $this->params->get('commission_fee', 0, 'INT');
			$this->fixedCommissionFee      = $this->params->get('fixed_commissionfee', 0, 'INT');

			$vendorClass    = TJVendors::vendor();
			$vendor         = $vendorClass->loadByUserId($this->logged_userid, 'com_jgive');
			$vendorDetails  = $vendor->getProperties();
			$vendorFeeTable = Table::getInstance('vendorfee', 'TjvendorsTable', array());
			$vendorFeeTable->load(array('vendor_id' => $vendorDetails['vendor_id'], 'client' => 'com_jgive', 'currency' => $this->params->get('currency')));

			if ($vendorFeeTable->percent_commission != null)
			{
				$this->commissionFee = $vendorFeeTable->percent_commission;
			}

			if ($vendorFeeTable->flat_commission != null)
			{
				$this->fixedCommissionFee = $vendorFeeTable->flat_commission;
			}

			// List of available filters on donations list view
			$this->availableFilters = array("payment_status", "limit","limitstart", "filter_order", "filter_order_Dir");
			$this->orderModel       = JGive::model('order');
			$this->paymentStatus    = $this->orderModel->getOrderStatues('fullforms');
		}

		/*Layout Details Donation*/
		$donationId = $this->input->get('donationid');
		$guestEmail = $this->input->get('email');

		if (!(($this->layout == 'details') && $donationId && $guestEmail))
		{
			$this->session->clear('JGIVE_order_id');

			if (!$this->logged_userid)
			{
				if ($this->guest_donation)
				{
					$msg         = Text::_('COM_JGIVE_LOGIN_MSG_SILENT');
					$uri         = $app->input->get('REQUEST_URI', '', 'server', 'string');
					$url         = base64_encode($uri);
					$guest_login = $this->session->get('quick_reg_no_login');
					$this->session->clear('quick_reg_no_login');
				}
				else
				{
					$itemId = $this->input->get('Itemid', '', 'INT');
					$msg    = Text::_('COM_JGIVE_LOGIN_MSG');
					$uri    = 'index.php?option=com_jgive&view=donations&layout=payment&cid=' . $this->input->get('cid', '', 'INT') . '&Itemid=' . $itemId;
					$url    = base64_encode($uri);
					$app->enqueueMessage($msg, 'error');
					$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
				}
			}
		}

		if ($this->layout == 'details')
		{
			$donationDetails    = $this->donationDetails = $this->get('SingleDonationInfo');

			if ($this->guest_donation)
			{
				if (!$this->logged_userid)
				{
					$guestEmail = $this->input->get('email');
					$donarEmail = md5($donationDetails['donor']->email);

					if ($guestEmail != $donarEmail)
					{
						$msg    = Text::_('COM_JGIVE_NO_ACCESS_MSG');
						$app->enqueueMessage($msg, 'notice');
						$app->redirect($myDonationLink);
					}
				}
				elseif ($this->logged_userid != $donationDetails['donor']->user_id)
				{
					$msg    = Text::_('COM_JGIVE_NO_ACCESS_MSG');
					$app->enqueueMessage($msg, 'error');
					$app->redirect($myDonationLink);
				}
			}
			elseif ($this->logged_userid != $donationDetails['donor']->user_id)
			{
				$msg = Text::_('COM_JGIVE_NO_ACCESS_MSG');
				$app->enqueueMessage($msg, 'error');
				$app->redirect($myDonationLink);
			}

			// If recurring is 1 pass only paypal
			$this->recure_gateway[0]['name'] = 'paypal';
			$this->recure_gateway[0]['id'] = 'paypal';

			if (empty($donationDetails))
			{
				$app->enqueueMessage(Text::_('COM_JGIVE_NO_DATA'), 'error');

				return false;
			}

			$this->donation_details = $this->donationDetails;
		}

		/*Layout Payment*/
		if ($this->layout == 'payment')
		{
			$this->cdata = $jgiveModelCampaign->getItem($this->input->get('cid', '', 'INT'));

			if (empty($this->cdata))
			{
				return false;
			}

			$this->campaignFlag = ($this->cdata['campaign']->type == 'donation') ? true : false;

			$this->givebacksAvailable = 0;

			if (count($this->cdata['givebacks']))
			{
				foreach ($this->cdata['givebacks'] as $giveback)
				{
					if ($giveback->sold_out == 0)
					{
						$this->givebacksAvailable = 1;
					}
				}
			}

			// If no user data set in session then only import the user profile or only first time after login
			$nofirst = $this->session->get('No_first_donation');

			if ($this->logged_userid)
			{
				if (empty($nofirst))
				{
					// If recurring donor then get donor Infomation
					$profiledata = $this->get('RecurringDonorInfo');

					if (!$profiledata)
					{
						$profile_import = $this->params->get('profile_import');

						// If 'Auto fill jGive form fields from selected community' field enabled then only call profile import function
						if ($profile_import)
						{
							$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
							$profiledata             = $JgiveIntegrationsHelper->profileImport(1);
						}
					}
				}
				else
				{
					// If registered user record is present in individual table then, while donation fetch user data from individual table.
					Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jgive/tables');
					$individualTable = Table::getInstance('individual', 'JGiveTable', array());
					$individualTable->load(array('user_id' => (int) $this->logged_userid));

					if (!empty($individualTable))
					{
						$profiledata = array();
						$profiledata['first_name'] = (isset($individualTable->first_name)) ? $individualTable->first_name : '';
						$profiledata['last_name']  = (isset($individualTable->last_name)) ? $individualTable->last_name : '';
						$profiledata['address']    = (isset($individualTable->addr_line_1)) ? $individualTable->addr_line_1 : '';
						$profiledata['address2']   = (isset($individualTable->addr_line_2)) ? $individualTable->addr_line_2 : '';
						$profiledata['zip']        = (isset($individualTable->zip)) ? $individualTable->zip : '';
						$profiledata['phone']      = (isset($individualTable->phone)) ? $individualTable->phone : '';
						$profiledata['taxnumber']  = (isset($individualTable->taxnumber)) ? $individualTable->taxnumber : '';
					}
				}

				$this->_setDonorData($profiledata);
			}

			PluginHelper::importPlugin('payment');
			$this->gateways = $this->params->get('gateways');
			$gateways       = array();

			if (!empty($this->gateways))
			{
				$gateways = $app->triggerEvent('onTP_GetInfo', array((array) $this->gateways));
			}

			$this->gateways = $gateways;

			// Recurring payment gateway
			$this->recurringGateways = array();

			foreach ($gateways as $gateway)
			{
				// Add recurring supported payment gateways html
				if ($gateway->id == "paypal" || $gateway->id == "stripe")
				{
					$this->recurringGateways[] = $gateway;
				}
			}

			// Get campaign givebacks
			$this->giveback_id = $this->session->get('JGIVE_giveback_id');
			$JGIVE_cid         = $this->session->get('JGIVE_cid');

			$campaignHelper          = new campaignHelper;
			$this->campaignGivebacks = $campaignHelper->getCampaignGivebacks($JGIVE_cid);
		}

		/*Layout Confirm*/
		if ($this->layout == 'confirm')
		{
			// @TODO save all posted data somewhere
			$session       = Factory::getSession();
			$this->session = $session;
			$cid           = $this->get('CampaignId');
			$this->cid     = $cid;

			PluginHelper::importPlugin('payment');
			$this->gateways = $this->params->get('gateways');
			$gateways       = array();

			if (!empty($this->gateways))
			{
				$gateways = $app->triggerEvent('onTP_GetInfo', array((array) $this->gateways));
			}

			$this->gateways = $gateways;
		}

		$this->setLayout($this->layout);
		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Function to set Donor Data
	 *
	 * @param   array  $profiledata  The Profile Data contain donor information.
	 *
	 * @return  void
	 */
	public function _setDonorData($profiledata)
	{
		if (!empty($profiledata))
		{
			$session = Factory::getSession();

			foreach ($profiledata as $field => $value)
			{
				if (!empty($value))
				{
					$session->set('JGIVE_' . $field, $value);
				}
			}

			if (!empty($profiledata['first_name']) || !empty($profiledata['last_name']))
			{
				$session->set('JGIVE_user_first_last_name', $profiledata['first_name'] . ' ' . $profiledata['last_name']);
			}

			$session->set('No_first_donation', 1);
		}
	}

	/**
	 * Method PrepareDocument 
	 *
	 * @return  void
	 *
	 * @since   @since  2.4.0
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();

		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_JGIVe_ALL_DONATIONS_PAGE_HEADING'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
