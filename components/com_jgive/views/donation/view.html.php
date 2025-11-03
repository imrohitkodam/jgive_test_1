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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * JgiveViewDonations form view class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JgiveViewDonation extends HtmlView
{
	protected $currency_code;

	protected $countries;

	protected $loggedInUserId;

	protected $session;

	protected $params;

	protected $pstatus;

	protected $sstatus;

	protected $retryPayment;

	protected $user;

	protected $guestDonation;

	protected $donationDetails;

	protected $gateWays;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app        = Factory::getApplication();
		$this->jgiveFrontendHelper = new jgiveFrontendHelper;
		$this->donationsHelper     = new DonationsHelper;

		$this->session        = Factory::getSession();
		$this->params         = ComponentHelper::getParams('com_jgive');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaign');
		$jgiveModelCampaign   = BaseDatabaseModel::getInstance('campaign', 'JGiveModel');

		$myDonationItemId     = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations');
		$myDonationLink       = Route::_('index.php?option=com_jgive&view=donations&Itemid=' . $myDonationItemId, false);
		$this->allCampaignItemId = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');

		// Check user loggdin or not
		$this->loggedInUserId  = Factory::getUser()->id;
		$this->guestDonation = $this->params->get('guest_donation');

		// Get country list options
		$this->countries       = $this->jgiveFrontendHelper->getCountries();
		$this->default_country = $this->params->get('default_country');

		// Used on donations view for payment status filter
		$this->pstatus         = $this->donationsHelper->getSStatusArray();
		$this->sstatus         = $this->donationsHelper->getSStatusArray();

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
			$this->gateWays = Factory::getApplication()->triggerEvent('onTP_GetInfo', array($gatewayParam));
		}

		$donationId = $app->input->get('donationid');
		$guestEmail = $app->input->get('email');

		if (!($donationId && $guestEmail))
		{
			$this->session->clear('JGIVE_order_id');

			if (!$this->loggedInUserId)
			{
				if ($this->guestDonation)
				{
					$msg         = Text::_('COM_JGIVE_LOGIN_MSG_SILENT');
					$uri         = $app->input->get('REQUEST_URI', '', 'server', 'string');
					$url         = base64_encode($uri);
					$guest_login = $this->session->get('quick_reg_no_login');
					$this->session->clear('quick_reg_no_login');
				}
				else
				{
					$itemId = $app->input->get('Itemid', '', 'INT');
					$msg = Text::_('COM_JGIVE_LOGIN_MSG');
					$uri    = 'index.php?option=com_jgive&view=donations&layout=payment&cid=' . $app->input->get('cid', '', 'INT') . '&Itemid=' . $itemId;
					$url    = base64_encode($uri);
					$app->enqueueMessage($msg, 'error');
					$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
				}
			}
		}

		// Get donation details
		$this->donationDetails = $this->get('Item');

		$params = ComponentHelper::getParams('com_jgive');
		$key = $this->params->get('pan_encryption_key');
		
		if (!empty($this->donationDetails['donor']->pannumber))
		{
			try
			{
				JLoader::register('DonationsHelper', JPATH_SITE . '/components/com_jgive/helpers/donations.php');
				$this->donationDetails['donor']->pannumber = DonationsHelper::decryptData($this->donationDetails['donor']->pannumber, $key);
			} 
			catch (Exception $e)
			{
				$this->donationDetails['donor']->pannumber = Text::_('COM_JGIVE_INVALID_PAN_FORMAT');
			}
		}

		if ($this->donationDetails['payment']->giveback_id)
		{
			$table = JGive::table('givebacks');
			$table->load(array('id' => (int) $this->donationDetails['payment']->giveback_id));
			$this->givebackTitle = ($table->title) ? htmlspecialchars($table->title, ENT_COMPAT, 'UTF-8') : $table->id;
		}

		$this->campaignTypeFlag = ($this->donationDetails['campaign']->type == 'donation') ? true : false;
		$this->retryPayment     = $this->donationsHelper->getAllowedRetryPayment($donationId);

		if ($this->guestDonation)
		{
			if (!$this->loggedInUserId)
			{
				$guestEmail = $app->input->get('email');
				$donarEmail = md5($this->donationDetails['donor']->email);

				if ($guestEmail != $donarEmail)
				{
					$msg    = Text::_('COM_JGIVE_NO_ACCESS_MSG');
					$app->enqueueMessage($msg, 'notice');
					$app->redirect($myDonationLink);
				}
			}
			elseif ($this->loggedInUserId != $this->donationDetails['donor']->user_id &&
			$this->loggedInUserId != $this->donationDetails['campaign']->creator_id)
			{
				$msg    = Text::_('COM_JGIVE_NO_ACCESS_MSG');
				$app->enqueueMessage($msg, 'error');
				$app->redirect($myDonationLink);
			}
		}
		elseif ($this->loggedInUserId != $this->donationDetails['donor']->user_id)
		{
			$msg    = Text::_('COM_JGIVE_NO_ACCESS_MSG');
			$app->enqueueMessage($msg, 'error');
			$app->redirect($myDonationLink);
		}

		// If recurring is 1 pass only paypal
		$this->recure_gateway[0]       = new stdclass;
		$this->recure_gateway[0]->name = 'paypal';
		$this->recure_gateway[0]->id   = 'paypal';

		if (empty($this->donationDetails))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_NO_DATA'), 'error');

			return false;
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return null
	 *
	 * @throws Exception
	 */
	protected function _prepareDocument()
	{
		$app = Factory::getApplication();

		if (isset($this->donationDetails['campaign']->type) && $this->donationDetails['campaign']->type == 'donation')
		{
			$title = Text::sprintf('COM_JGIVE_DONATION_PAGE_DEFAULT_TITLE', $app->get('sitename'));
			$this->params->def('page_heading', $title);
		}
		else
		{
			$title = Text::sprintf('COM_JGIVE_INVESTMENT_PAGE_DEFAULT_TITLE', $app->get('sitename'));
			$this->params->def('page_heading', $title);
		}

		$this->document->setTitle($title);
	}
}
