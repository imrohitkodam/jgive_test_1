<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Categories\Administrator\Table as categoryTable;

jimport('techjoomla.tjnotifications.tjnotifications');

include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * Class JGiveMailsHelper
 *
 * @since  2.1
 */
class JGiveMailsHelper
{
	protected $jgiveparams;

	protected $siteConfig;

	protected $sitename;

	protected $siteadminname;

	protected $user;

	protected $client;

	protected $tjnotifications;

	protected $siteinfo;
	/**
	 * Method acts as a consturctor
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		$this->jgiveparams         = ComponentHelper::getParams('com_jgive');
		$this->siteConfig          = Factory::getConfig();
		$this->sitename            = $this->siteConfig->get('sitename');
		$this->siteadminname       = $this->siteConfig->get('fromname');
		$this->user                = Factory::getUser();
		$this->client              = "com_jgive";
		$this->tjnotifications     = new Tjnotifications;
		$this->jgiveFrontendHelper = new jgiveFrontendHelper;

		$this->siteinfo            = new stdClass;
		$this->siteinfo->sitename  = $this->sitename;
		$this->siteinfo->adminname = Text::_('COM_JGIVE_SITEADMIN');
	}

	/**
	 * Send mails when campaign is created
	 *
	 * @param   OBJECT  $campaignDetails  Campaigns Detail
	 *
	 * @return void
	 */
	public function onAfterCreateCampaign($campaignDetails)
	{
		$adminEmailArray = array();
		$adminEmail      = (!empty($this->jgiveparams->get('email'))) ? $this->jgiveparams->get('email') : $this->siteConfig->get('mailfrom');
		$adminEmailArray = explode(',', $adminEmail);
		$userIdArray     = $this->getUserIdFromEmail($adminEmailArray);
		$adminRecipients = array(
			'email' => array(
				'to' => $adminEmailArray
			)
		);

		foreach ($userIdArray as $userId)
		{
			array_unshift($adminRecipients, Factory::getUser($userId));
		}

		$promoterEmailArray = array();

		if ($campaignDetails->creator_id)
		{
			$promoterEmailArray[] = Factory::getUser($campaignDetails->creator_id)->email;
		}

		$promoterRecipients = array('email' => array('to' => $promoterEmailArray));
		$vendorObj          = TJVendors::vendor($campaignDetails->vendor_id, 'com_jgive');

		// Checking if mobile number found then only add sms subarray
		if (!empty($vendorObj->getPhoneNumber()))
		{
			$promoterRecipients['sms'] = array($vendorObj->getPhoneNumber());
		}

		$admin_approval = $this->jgiveparams->get('admin_approval');
		$adminkey       = ($admin_approval) ? "createCampaignMailToAdminWaitingForApproval" : "createCampaignMailToAdmin";
		$promoterkey    = ($admin_approval) ? "createCampaignMailToPromoterWaitingForApproval" : "createCampaignMailToPromoter";

		$myCampsItemid   = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');
		$myCamps         = 'index.php?option=com_jgive&view=campaigns&layout=my&Itemid=' . $myCampsItemid;
		$myCampsLink     = Uri::root() . substr(Route::_($myCamps), strlen(Uri::base(true)) + 1);
		$campaignDetails->mycampaigns = $myCampsLink;

		$allCampsItemid = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		$allCamps       = 'index.php?option=com_jgive&view=campaigns&layout=all&Itemid=' . $allCampsItemid;
		$allCampsLink   = Uri::root() . substr(Route::_($allCamps), strlen(Uri::base(true)) + 1);
		$campaignDetails->allcampaigns = $allCampsLink;

		$allCampaignsBackendLink = Uri::root() . 'administrator/index.php?option=com_jgive&view=campaigns&layout=default';
		$campaignDetails->allcampaignsBackend = $allCampaignsBackendLink;

		$campaignDetails->status = ($campaignDetails->published == 0) ? Text::_('COM_JGIVE_UNPUBLISHED'):Text::_('COM_JGIVE_PUBLISHED');
		$campaignDetails->goal_amount = $this->jgiveFrontendHelper->getFormattedPrice($campaignDetails->goal_amount);

		$siteInfo           = new stdClass;
		$siteInfo->sitename = $this->sitename;

		$replacements           = new stdClass;
		$replacements->info     = $this->siteinfo;
		$replacements->campaign = $campaignDetails;

		$options = new Registry;
		$options->set('campaign', $campaignDetails);
		$options->set('info', $siteInfo);

		// Mail to site admin
		$this->tjnotifications->send($this->client, $adminkey, $adminRecipients, $replacements, $options);

		// Mail to Promoter
		$this->tjnotifications->send($this->client, $promoterkey, $promoterRecipients, $replacements, $options);

		return;
	}

	/**
	 * Send mails when campaign is editted
	 *
	 * @param   OBJECT  $campaignDetails  Camp details
	 *
	 * @return void
	 */
	public function onAfterCampaignEdit($campaignDetails)
	{
		$adminEmailArray = array();
		$adminEmail      = (!empty($this->jgiveparams->get('email'))) ? $this->jgiveparams->get('email') : $this->siteConfig->get('mailfrom');
		$adminEmailArray = explode(',', $adminEmail);
		$userIdArray     = $this->getUserIdFromEmail($adminEmailArray);
		$adminRecipients = array(
			'email' => array(
				'to' => $adminEmailArray
			)
		);

		foreach ($userIdArray as $userId)
		{
			array_unshift($adminRecipients, Factory::getUser($userId));
		}

		$promoterEmailArray   = array();

		if ($campaignDetails->creator_id)
		{
			$promoterEmailArray[] = Factory::getUser($campaignDetails->creator_id)->email;
		}

		$promoterRecipients   = array(
			'email' => array('to' => $promoterEmailArray)
		);

		$vendorObj = TJVendors::vendor($campaignDetails->vendor_id, 'com_jgive');

		// Checking if mobile number found then only add sms subarray
		if (!empty($vendorObj->getPhoneNumber()))
		{
			$promoterRecipients['sms'] = array($vendorObj->getPhoneNumber());
		}

		$replacements = new stdClass;

		$singleCampaignItemid = $this->jgiveFrontendHelper->getItemId(
			'index.php?option=com_jgive&view=campaign&layout=default&id=' . $campaignDetails->id
		);
		$singleCampaign = 'index.php?option=com_jgive&view=campaign&layout=default&id='
		. $campaignDetails->id . '&Itemid=' . $singleCampaignItemid;
		$singleCampaignLink = Uri::root() . substr(Route::_($singleCampaign), strlen(Uri::base(true)) + 1);
		$campaignDetails->campaignDetailed = $singleCampaignLink;
		$campaignDetails->goal_amount = $this->jgiveFrontendHelper->getFormattedPrice($campaignDetails->goal_amount);

		if (JVERSION < '4.0.0')
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
			$categoryDetails = Table::getInstance('Category', 'CategoriesTable');
		}
		else
		{
			$categoryDetails = Table::getInstance('CategoryTable', '\\Joomla\\Component\\Categories\\Administrator\\Table\\');
		}

		$categoryDetails->load(array('id' => $campaignDetails->category_id));

		$replacements->info = $this->siteinfo;
		$replacements->campaign = $campaignDetails;
		$replacements->category = $categoryDetails;

		$replacements->editor = new stdclass;
		$replacements->editor->id   = $campaignDetails->creator_id;
		$replacements->editor->name = $this->user->name;

		$siteinfo = new stdClass;
		$siteinfo->sitename = $this->sitename;

		$options = new Registry;
		$options->set('campaign', $campaignDetails);
		$options->set('info', $siteinfo);

		// Mail to site admin
		$this->tjnotifications->send($this->client, "editCampaignMailToAdmin", $adminRecipients, $replacements, $options);

		// Mail to Promoter
		$this->tjnotifications->send($this->client, "editCampaignMailToPromoter", $promoterRecipients, $replacements, $options);

		return;
	}

	/**
	 * Send mails when campaign is editted
	 *
	 * @param   OBJECT  $campaignDetails  Camp details
	 *
	 * @return void
	 */
	public function onAfterCampaignStateChange($campaignDetails)
	{
		$vendorObj = TJVendors::vendor($campaignDetails->vendor_id, 'com_jgive');
		$campaignDetails->name = $vendorObj->getTitle();
		$campaignItemid = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');

		$promoterArray      = array();
		$promoterArray[]    = $campaignDetails->email;
		$promoterRecipients = array('email' => array('to' => $promoterArray));

		$vendorObj = TJVendors::vendor($campaignDetails->vendor_id, 'com_jgive');
		$promoterContactArray = array();
		$promoterContactArray[] = $vendorObj->getPhoneNumber();

		// Checking if mobile number found then only add sms subarray
		if (!empty($vendorObj->getPhoneNumber()))
		{
			$promoterRecipients['sms'] = array($vendorObj->getPhoneNumber());
		}

		$mycampaigns     = 'index.php?option=com_jgive&view=campaigns&layout=my&Itemid=' . $campaignItemid;
		$mycampaignsLink = Uri::root() . substr(Route::_($mycampaigns), strlen(Uri::base(true)) + 1);
		$campaignDetails->mycampaigns = $mycampaignsLink;

		$replacements           = new stdClass;
		$replacements->info     = $this->siteinfo;
		$replacements->campaign = $campaignDetails;

		$siteinfo = new stdClass;
		$siteinfo->sitename = $this->sitename;

		$options = new Registry;
		$options->set('campaign', $campaignDetails);
		$options->set('info', $siteinfo);

		// Mail to Promoter
		$this->tjnotifications->send($this->client, "approvalOnCampaignMailToPromoter", $promoterRecipients, $replacements, $options);

		return;
	}

	/**
	 * Method _MailExceedingGoalAmount.
	 * Method MailOnExceedingGoalAmount.
	 *
	 * @param   Object  $campaignDetails  campaignDetails array.
	 *
	 * @return  void.
	 *
	 * @since	1.8
	 */
	public function MailOnExceedingGoalAmount($campaignDetails)
	{
		$vendorObj = TJVendors::vendor($campaignDetails->vendor_id, 'com_jgive');
		$campaignDetails->first_name = $vendorObj->getTitle();

		$replacements = new stdClass;
		$creator_id   = (int) $campaignDetails->creator_id;
		$promoter     = Factory::getUser($creator_id);

		$promoterEmailArray = array();
		$promoterEmailArray[] = $promoter->email;
		$promoterRecipients   = array('email' => array('to' => $promoterEmailArray));

		// Checking if mobile number found then only add sms subarray
		if (!empty($vendorObj->getPhoneNumber()))
		{
			$promoterRecipients['sms'] = array($vendorObj->getPhoneNumber());
		}

		$replacements->info     = $this->siteinfo;
		$replacements->campaign = $campaignDetails;

		$siteinfo = new stdClass;
		$siteinfo->sitename = $this->sitename;

		$options = new Registry;
		$options->set('campaign', $campaignDetails);
		$options->set('info', $siteinfo);

		// Mail to Promoter
		$this->tjnotifications->send($this->client, "reachGoalAmountForCampaignMailToPromoter", $promoterRecipients, $replacements, $options);

		return;
	}

	/**
	 * Method newDonationStatus.
	 *
	 * @param   ARRAY|boolean  $donationDetails  donation detail, false on failure
	 *
	 * @return  void
	 *
	 * @since	1.8
	 */
	public function newDonationStatus($donationDetails)
	{
		$replacements = new stdClass;
		$utilitiesObj = JGive::utilities();
		$statusArray  = $utilitiesObj->getOrderStatusText($donationDetails['payment']->status);
		$donationDetails['payment']->donationStatus = $statusArray['statusText'];

		$vendorObj = TJVendors::vendor($donationDetails['campaign']->vendor_id, 'com_jgive');
		$donationDetails['campaign']->first_name = $vendorObj->getTitle();

		$creator_id = $donationDetails['campaign']->creator_id;
		$creator    = Factory::getUser($creator_id);

		$myDonationItemid = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=default');
		$myDonationLink   = 'index.php?option=com_jgive&view=donations&layout=default&Itemid=' . $myDonationItemid;
		$myDonationLink   = Uri::root() . substr(Route::_($myDonationLink), strlen(Uri::base(true)) + 1);

		$donationDetails['payment']->amount     = $this->jgiveFrontendHelper->getFormattedPrice($donationDetails['payment']->original_amount);
		$donationDetails['payment']->anonymous  = ($donationDetails['payment']->annonymous_donation) ? Text::_('COM_JGIVE_YES') : Text::_('COM_JGIVE_NO');
		$donationDetails['payment']->donoremail = $this->user->email ? $this->user->email : $donationDetails['donor']->email;
		$donationDetails['payment']->donorname  = $this->user->name ? $this->user->name : $donationDetails['donor']->first_name;

		if ($donationDetails['donor']->donor_type == 'org' && !empty($donationDetails['donor']->org_name))
		{
			$donationDetails['payment']->donorname = $donationDetails['donor']->org_name;
		}

		$donationDetails['payment']->mydonations  = $myDonationLink;
		$donationDetails['payment']->cdate        = HTMLHelper::date($donationDetails['payment']->cdate, Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
		$donationDetails['payment']->donationType = Text::_('COM_JGIVE_ONE_TIME');

		if ($donationDetails['payment']->is_recurring == 1)
		{
			$donationDetails['payment']->donationType = Text::_('COM_JGIVE_RECURRING');
		}

		$donationDetails['payment']->processor         = $utilitiesObj->getPaymentGatewayName($donationDetails['payment']->processor);
		$donationDetails['campaign']->goal_amount      = $this->jgiveFrontendHelper->getFormattedPrice($donationDetails['campaign']->goal_amount);
		$donationDetails['campaign']->amount_received  = $this->jgiveFrontendHelper->getFormattedPrice($donationDetails['campaign']->amount_received);
		$donationDetails['campaign']->remaining_amount = $this->jgiveFrontendHelper->getFormattedPrice($donationDetails['campaign']->remaining_amount);

		$replacements->info     = $this->siteinfo;
		$replacements->payment  = $donationDetails['payment'];
		$replacements->campaign = $donationDetails['campaign'];
		$replacements->donor    = $donationDetails['donor'];
		$replacements->promoter = $creator;

		$options = new Registry;
		$options->set('order', $donationDetails['payment']);

		$siteinfo = new stdClass;
		$siteinfo->sitename = $this->sitename;
		$options->set('info', $siteinfo);

		/* Mail to Admin user*/
		$adminEmailArray = array();
		$adminEmail      = (!empty($this->jgiveparams->get('email'))) ? $this->jgiveparams->get('email') : $this->siteConfig->get('mailfrom');
		$adminEmailArray = explode(',', $adminEmail);
		$userIdArray = $this->getUserIdFromEmail($adminEmailArray);
		$adminRecipients = array(
			'email' => array(
				'to' => $adminEmailArray
			)
		);

		foreach ($userIdArray as $userId)
		{
			array_unshift($adminRecipients, Factory::getUser($userId));
		}

		$paidDonationInvestmentAdminKey = ($donationDetails['campaign']->type == 'donation') ? 'paidDonationMailToAdmin' : 'paidInvestementMailToAdmin';
		$this->tjnotifications->send($this->client, $paidDonationInvestmentAdminKey, $adminRecipients, $replacements, $options);

		/* Mail to Donor/investor*/
		$donorEmailArray = array();
		$donorEmailArray[] = $donationDetails['donor']->email ? $donationDetails['donor']->email : $this->user->email;

		$donorrecipients = array(
			'email' => array(
				'to' => $donorEmailArray
			)
		);

		if (!empty($donationDetails['donor']->phone))
		{
			$donorrecipients['sms'] = array($donationDetails['donor']->phone);
		}

		$paidDonationInvestmentDonorKey = ($donationDetails['campaign']->type == 'donation') ? 'paidDonationMailToDonor' : 'paidInvestmentMailToDonor';
		$this->tjnotifications->send($this->client, $paidDonationInvestmentDonorKey, $donorrecipients, $replacements, $options);

		/* Mail to Campaign Promoter*/
		$promoterEmailArray = array();
		$promoterEmailArray[] = $creator->email;

		$promoterRecipients = array(
			'email' => array(
				'to' => $promoterEmailArray
			)
		);

		// Checking if mobile number found then only add sms subarray
		if (!empty($vendorObj->getPhoneNumber()))
		{
			$promoterRecipients['sms'] = array($vendorObj->getPhoneNumber());
		}

		$paidDonationInvestmentPromoterKey = ($donationDetails['campaign']->type == 'donation')
		? 'paidDonationMailToPromoter' : 'paidInvestmentMailToPromoter';
		$this->tjnotifications->send($this->client, $paidDonationInvestmentPromoterKey, $promoterRecipients, $replacements, $options);

		return;
	}

	/**
	 * Method updateDonationStatus.
	 *
	 * @param   ARRAY|boolean  $donationDetails  donation detail, false on failure
	 *
	 * @return  void
	 *
	 * @since	1.8
	 */
	public function updateDonationStatus($donationDetails)
	{
		$replacements = new stdClass;
		$utilitiesObj = JGive::utilities();
		$statusArray  = $utilitiesObj->getOrderStatusText($donationDetails['payment']->status);
		$donationDetails['payment']->order_status = $statusArray['statusText'];
		$vendorObj = TJVendors::vendor($donationDetails['campaign']->vendor_id, 'com_jgive');
		$donationDetails['campaign']->first_name = $vendorObj->getTitle();

		$creator_id             = $donationDetails['campaign']->creator_id;
		$creator                = Factory::getUser($creator_id);
		$replacements->info     = $this->siteinfo;
		$replacements->campaign = $donationDetails['campaign'];
		$replacements->order    = $donationDetails['payment'];
		$replacements->donor    = $donationDetails['donor'];

		$options = new Registry;
		$options->set('order', $donationDetails['payment']);
		$options->set('campaign', $donationDetails['campaign']);

		$siteinfo = new stdClass;
		$siteinfo->sitename = $this->sitename;
		$options->set('info', $siteinfo);

		/* Mail to Admin user*/
		$adminEmailArray = array();
		$adminEmail      = (!empty($this->jgiveparams->get('email'))) ? $this->jgiveparams->get('email') : $this->siteConfig->get('mailfrom');
		$adminEmailArray = explode(',', $adminEmail);
		$userIdArray = $this->getUserIdFromEmail($adminEmailArray);
		$adminRecipients = array(
			'email' => array(
				'to' => $adminEmailArray
			)
		);

		foreach ($userIdArray as $userId)
		{
			array_unshift($adminRecipients, Factory::getUser($userId));
		}

		$this->tjnotifications->send($this->client, "orderStatusChangeMailToAdmin", $adminRecipients, $replacements, $options);

		/* Mail to Donor */
		$donorEmailArray = array();
		$donorEmailArray[] = $donationDetails['donor']->email;
		$donorrecipients = array('email' => array('to' => $donorEmailArray));

		if (!empty($donationDetails['donor']->phone))
		{
			$donorrecipients['sms'] = array($donationDetails['donor']->phone);
		}

		$this->tjnotifications->send($this->client, "orderStatusChangeMailToDonor", $donorrecipients, $replacements, $options);

		/* Mail to Campaign Promoter*/
		$pramoterEmailArray = array();
		$pramoterEmailArray[] = $creator->email;

		$promoterRecipients = array(
			'email' => array(
				'to' => $pramoterEmailArray
			)
		);

		// Checking if mobile number found then only add sms subarray
		if (!empty($vendorObj->getPhoneNumber()))
		{
			$promoterRecipients['sms'] = array($vendorObj->getPhoneNumber());
		}

		$this->tjnotifications->send($this->client, "orderStatusChangeMailToCampaignOwner", $promoterRecipients, $replacements, $options);

		return;
	}

	/**
	 * Method to generate receipt
	 *
	 * @param   ARRAY|boolean  $donationDetails  donation detail, false on failure
	 *
	 * @return void
	 *
	 * @since  1.8
	 */
	public function generateReceipt($donationDetails)
	{
		$replacements = $order = $campaign = new stdClass;
		$orderstatus  = '';

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
		$campaignDetails = Table::getInstance('campaign', 'JgiveTable');
		$campaignDetails->load(array('id' => $donationDetails['campaign']->id));

		$creator_id = $donationDetails['campaign']->creator_id;
		$creator    = Factory::getUser($creator_id);

		$vendorObj = TJVendors::vendor($donationDetails['campaign']->vendor_id, 'com_jgive');

		JLoader::import('components.com_jgive.helper', JPATH_SITE);
		$jgiveFrontendHelper = new JgiveFrontendHelper;

		$campaign->promotername    = $creator->name;
		$campaign->promoteremail   = $creator->email;
		$campaign->campaigntitle   = $donationDetails['campaign']->title;
		$campaign->goalamount      = $jgiveFrontendHelper->getFormattedPrice($donationDetails['campaign']->goal_amount);
		$campaign->donation_amount = $jgiveFrontendHelper->getFormattedPrice($donationDetails['payment']->original_amount);
		$campaign->remainingamount = $jgiveFrontendHelper->getFormattedPrice($donationDetails['campaign']->remaining_amount);

		$order->donor_first_name   = $donationDetails['donor']->first_name;

		if ($donationDetails['donor']->donor_type == 'org' && !empty($donationDetails['donor']->org_name))
		{
			$order->donor_first_name   = $donationDetails['donor']->org_name;
		}

		$order->donationStatus     = $orderstatus;
		$order->anonymous          = ($donationDetails['payment']->annonymous_donation) ? Text::_('COM_JGIVE_YES') : Text::_('COM_JGIVE_NO');
		$order->amount             = $jgiveFrontendHelper->getFormattedPrice($donationDetails['payment']->original_amount);
		$order->donationtype       = $donationDetails['campaign']->type;
		$donationDetails['payment']->cdate = HTMLHelper::date($donationDetails['payment']->cdate, Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));

		$replacements->info     = $this->siteinfo;
		$replacements->order    = $order;
		$replacements->campaign = $campaign;
		$replacements->payment  = $donationDetails['payment'];

		$options = new Registry;
		$options->set('campaign', $campaign);
		$options->set('info', $this->siteinfo);

		/* Mail to Donor */
		$donorEmailArray = array();
		$donorEmailArray[] = $donationDetails['donor']->email;

		$donorrecipients   = array(
			'email' => array(
				'to' => $donorEmailArray
			)
		);

		if (!empty($donationDetails['donor']->phone))
		{
			$donorrecipients['sms'] = array($donationDetails['donor']->phone);
		}

		$this->tjnotifications->send($this->client, "paymentRecieptMailToDonor", $donorrecipients, $replacements, $options);

		/* Mail to Campaign Promoter*/
		$promoterEmailArray = array();
		$promoterEmailArray[] = $creator->email;

		$promoterrecipients   = array(
			'email' => array(
				'to' => $promoterEmailArray
			)
		);

		if (!empty($vendorObj->getPhoneNumber()))
		{
			$promoterrecipients['sms'] = array($vendorObj->getPhoneNumber());
		}

		$this->tjnotifications->send($this->client, "paymentRecieptMailToCampaignOwner", $promoterrecipients, $replacements, $options);

		return;
	}

	/**
	 * Method to create recipient array
	 *
	 * @param   ARRAY  $adminRecipients  Contains email object
	 *
	 * @return  array.
	 *
	 * @since	2.5.3
	 */
	public function getUserIdFromEmail($adminRecipients)
	{
		$finalUserIdRecipient = [];

		if (!empty($adminRecipients))
		{
			$db = Factory::getDbo();

			foreach ($adminRecipients as $adminRecipient)
			{
				$query = $db->getQuery(true)
					->select($db->quoteName('id'))
					->from($db->quoteName('#__users'))
					->where($db->quoteName('email') . ' = ' . $db->quote($adminRecipient));
				$db->setQuery($query);
				$userId = $db->loadResult();

				$finalUserIdRecipient[] = $userId;
			}
		}

		return $finalUserIdRecipient;
	}

	/**
	 * Method to send notification to donor when a new campaign is added.
	 *
	 * This method prepares the donor's full name if missing, constructs
	 * replacement data for the notification template, and sends an email
	 * (and optionally SMS) to the donor with campaign details.
	 *
	 * @param   object  $campaignDetails  Contains the campaign name and title.
	 * @param   object  $donorDetails     Contains donor information like name, email, phone, and user ID.
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	public function newCampaignAddedNotification($campaignDetails, $donorDetails)
	{
		// Ensure donorDetails has first_name and last_name
		if (empty($donorDetails->first_name) || empty($donorDetails->last_name))
		{
			$userId = $donorDetails->user_id ?? null;
	
			if ($userId)
			{
				// Load user from database
				$user = JFactory::getUser($userId);
	
				$fullName = $user->name ?? '';
	
				// Split full name into first and last name
				$nameParts = preg_split('/\s+/', trim($fullName));
				$donorDetails->first_name = ucfirst($nameParts[0] ?? '');
	
				// If there's more than one word, set last name
				if (isset($nameParts[1])) {
					$donorDetails->last_name = ucfirst(implode(' ', array_slice($nameParts, 1)));
				} else {
					$donorDetails->last_name = '';
				}
			}
			else
			{
				$emailName = str_replace(['.', '+', '_'], ' ', explode('@', $donorDetails->email)[0]);
				$nameParts = preg_split('/\s+/', trim($emailName));
				$donorDetails->first_name = ucfirst($nameParts[0] ?? '');
				$donorDetails->last_name = isset($nameParts[1]) ? ucfirst($nameParts[1]) : '';
			}
		}
	
		$replacements = new stdClass;
	
		// Set campaign details
		$replacements->campaign = new stdClass;
		$replacements->campaign->name = $campaignDetails->name;
		$replacements->campaign->title = $campaignDetails->title;

		// Add campaign link
		$campaignLink = Uri::root() . 'index.php?option=com_jgive&view=campaign&layout=campaignview&id=' . $campaignDetails->id;
		$replacements->campaign->link = $campaignLink;

		// Set donor details
		$replacements->donor = new stdClass;
		$replacements->donor->name = trim($donorDetails->first_name . ' ' . $donorDetails->last_name);
		$replacements->donor->email = $donorDetails->email;
	
		$options = new Registry;
	
		$siteinfo = new stdClass;
		$siteinfo->sitename = $this->sitename;
		$options->set('info', $siteinfo);
	
		// Mail to Donor (Only donor gets the mail)
		$donorEmailArray = array($donorDetails->email);
	
		$donorRecipients = array(
			'email' => array(
				'to' => $donorEmailArray
			)
		);
	
		// If donor has phone, add to SMS
		if (!empty($donorDetails->phone))
		{
			$donorRecipients['sms'] = array($donorDetails->phone);
		}
	
		// Send email to Donor
		$this->tjnotifications->send($this->client, 'newCampaignMailToDonor', $donorRecipients, $replacements, $options);
	
		return;
	}
	
	
	/**
	 * Method to send email report to vendor.
	 *
	 * This method prepares the donor's full name if missing, constructs
	 * replacement data for the notification template, and sends an email
	 * (and optionally SMS) to the donor with campaign details.
	 *
	 * @param   object  $data  Contains the email id and public url of pdf file
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */

	public function sendReportToVendor($data)
	{
		$recipients = array(
			'email' => array(
				'to' => array($data['vendorEmail'])
			)
		);
		// Prepare the email content with the link to the PDF
		$replacements = new stdClass;
		$replacements->attach = new stdClass;
		$replacements->attach->pdfLink = $data['pdfUrl'];
		$replacements->attach->period = $data['period'];
		$replacements->attach->fromDate = $data['fromDate'];
		$replacements->attach->toDate = $data['toDate'];
		// print_r($pdfUrl);exit;

		$options = new Registry;
	
		// No attachments, just the link in the email body
		$this->tjnotifications->send('com_jgive', 'vendorReportMail', $recipients, $replacements, $options);

	}
}
