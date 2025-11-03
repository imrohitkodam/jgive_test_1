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

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

JLoader::import('donors', JPATH_SITE . '/components/com_jgive/models');
JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');

/**
 * Dashboard view class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveViewDashboard extends HtmlView
{
	protected $receivedPayoutAmount;

	protected $pendingPayoutAmount;

	protected $graphColumnClass;

	protected $currency;

	/**
	 * Function dispaly
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.8
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();

		// Take option value
		$com_jgive_option = $app->input->get('option');

		$user = Factory::getUser();
		$this->logged_userid = $user->id;

		$plgDatat = PluginHelper::importPlugin('system');
		Factory::getApplication()->triggerEvent('onGetActivityScript', array('campaignfeed'));

		// Payout amounts taken from TJVendors
		$params               = ComponentHelper::getParams('com_jgive');
		$this->currency       = $params->get('currency', '');

		$tjvendorFrontHelper        = new TjvendorFrontHelper;
		$vendorId                   = $tjvendorFrontHelper->checkVendor('', 'com_jgive');
		$this->receivedPayoutAmount = $tjvendorFrontHelper->getPaidAmount($vendorId, $this->currency, 'com_jgive');

		JLoader::import('vendor', JPATH_SITE . '/components/com_tjvendors/models');
		$tjvendorsModelVendor = new TjvendorsModelVendor;
		$this->pendingPayoutAmount = $tjvendorsModelVendor->getPayableAmount($vendorId, 'com_jgive', $this->currency);
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		$path = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

		if (!class_exists('CampaignHelper'))
		{
			JLoader::register('CampaignHelper', $path);
			JLoader::load('CampaignHelper');
		}

		$campaignHelper = new campaignHelper;

		// Check login status
		if (!$this->logged_userid)
		{
			$msg = Text::_('COM_JGIVE_LOGIN_MSG');
			$uri = $_SERVER["REQUEST_URI"];
			$url = base64_encode($uri);
			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		// Get Top Donors List
		$model           = $this->getModel('dashboard', array('ignore_request' => true));

		$this->searchMyCamp = $app->getUserStateFromRequest("$com_jgive_option.searchMyCamp", 'searchMyCamp', '', 'string');
		$this->dashboard_filters = $app->getUserStateFromRequest("$com_jgive_option.dashboard_filters", 'dashboard_filters', '', 'integer');

		$this->promoterDashboardData['filterData'] = new stdClass;
		$this->promoterDashboardData['filterData']->searchMyCamp = $this->searchMyCamp;
		$this->promoterDashboardData['filterData']->dashboard_filters = $this->dashboard_filters;
		$this->promoterDashboardData['dashboardFilterOption'] = $model->dashboardDropDownOption();
		$this->promoterDashboardData['payoutData'] = new stdClass;
		$this->promoterDashboardData['activityData'] = $this->get('CampaignIds');
		$this->promoterDashboardData['myCampData'] = $model->getMyCampaign();
		$this->promoterDashboardData['topDonorsData'] = $model->getDonorsDetails();

		$this->promoterDashboardData['otherData'] = new stdClass;
		$this->promoterDashboardData['otherData']->logged_userid = $this->logged_userid;
		$this->promoterDashboardData['otherData']->createCampaignItemid = $jgiveFrontendHelper->getItemId(
		'index.php?option=com_jgive&view=campaignform'
		);
		$this->promoterDashboardData['otherData']->myCampaignItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my'
		);
		$this->promoterDashboardData['otherData']->donorsItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donors');
		$this->promoterDashboardData['otherData']->singleCampItemid = $jgiveFrontendHelper->getItemId(
		'index.php?option=com_jgive&view=campaign&layout=default'
		);
		$this->promoterDashboardData['params'] = ComponentHelper::getParams('com_jgive');
		$this->promoterDashboardData['categories'] = $campaignHelper->getCampaignsCategories();
		$this->promoterDashboardData['filterCatList'] = $app->input->get('cat');
		$this->promoterDashboardData['filterCampStatus'] = $app->input->get('campStatus');
		$this->promoterDashboardData['campaignType'] = $campaignHelper->getCampaignTypeFilterOptions();
		$this->promoterDashboardData['filterCampType'] = $app->input->get('campType');
		$this->promoterDashboardData['organizationType'] = $campaignHelper->organization_individual_type();
		$this->promoterDashboardData['filterOrgTypeList'] = $app->input->get('orgType');
		$this->promoterDashboardData['language'] = new stdClass;
		$this->promoterDashboardData['language']->lang = Factory::getLanguage()->getTag();

		if ($this->promoterDashboardData['params']['campaign_activity'] != '0')
		{
			$this->graphColumnClass = "col-md-6";
		}
		else
		{
			$this->graphColumnClass = "col-md-9";
		}

		// Check here user is a Super User or registered user
		$this->isroot = $user->authorise('core.admin');

		parent::display($tpl);
	}
}
