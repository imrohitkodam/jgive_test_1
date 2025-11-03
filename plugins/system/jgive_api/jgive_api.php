<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;


/**
 * PlgSystemJgive_api class.
 *
 * @package  JGive
 * @since    1.8
 */
class PlgSystemJgive_Api extends CMSPlugin
{
	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   3.9.0
	 */
	public function __construct(&$subject, $config)
	{
		$lang = Factory::getLanguage();
		$lang->load('com_jgive', JPATH_SITE, 'en-GB', true);

		parent::__construct($subject, $config);
	}

	/**
	 * Function onAfterJGCampaignCreate
	 *
	 * @param   Array  $campaignData  Campaign Post Data
	 *
	 * @return  boolean.
	 *
	 * @since	2.1.4
	 */
	public function onAfterJGCampaignCreate($campaignData)
	{
		$campaignId = $campaignData['campaignId'];

		if (!empty($campaignId))
		{
			// Check the campaign is published.
			$published = $this->checkCampaignIsPublished($campaignId);

			if (!$published)
			{
				return false;
			}

			// Push activity to various activity streams.
			// Set some of the data for activity.
			$act_subtype     = 'campaign';

			// Remove function OnAfterJGiveCampaignEdit(). Edit campaign case handle here in same function.
			if (isset($campaignData['campaignOldData']))
			{
				$act_description = Text::_('COM_JGIVE_ACTIVITY_EDIT_CAMPAIGN') . ' ';
			}
			else
			{
				$act_description = Text::_('COM_JGIVE_ACTIVITY_CREATED_CAMPAIGN') . ' ';
			}

			$result          = $this->pushActivity($campaignId, $act_subtype, $act_description);

			if (!$result)
			{
				return false;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function OnAfterJGiveCampaignDelete
	 *
	 * @param   INT  $cids  Campaign Id
	 *
	 * @return  void.
	 *
	 * @since	1.8
	 */
	public function OnAfterJGiveCampaignDelete($cids)
	{
	}

	/**
	 * Function onAfterJGPaymentStatusChange
	 *
	 * @param   INT     $order_id_key  Order Id Key
	 * @param   String  $status        Status
	 * @param   String  $comment       Comment
	 * @param   String  $send_mail     Send mail
	 *
	 * @return  void.
	 *
	 * @since	1.8
	 */
	public function onAfterJGPaymentStatusChange($order_id_key, $status, $comment, $send_mail)
	{
	}

	/**
	 * Function onAfterJGPaymentStatusChange
	 *
	 * @param   INT     $orderId      Order Id
	 * @param   String  $orderStatus  Order Status
	 *
	 * @return  void.
	 *
	 * @since	1.8
	 */
	public function OnAfterJGivePaymentStatusProcess($orderId, $orderStatus)
	{
	}

	/**
	 * Function onAfterJGivePaymentSuccess
	 *
	 * @param   INT  $orderId  Order Id
	 *
	 * @return  boolean.
	 *
	 * @since	1.8
	 */
	public function onAfterJGivePaymentSuccess($orderId)
	{
		// Push activity to various activity streams.
		JLoader::import('models.donation', JPATH_SITE . '/components/com_jgive');
		$donationModel = new JgiveModelDonation;
		$donationDetails = $donationModel->getItem($orderId);
		$cid                 = $donationDetails['campaign']->id;
		$uid                 = $donationDetails['donor']->user_id;
		$isAnonymousDonation = $donationDetails['payment']->annonymous_donation;

		$jgiveCampaignModel = BaseDatabaseModel::getInstance('CampaignForm', 'JgiveModel');
		$campaign           = $jgiveCampaignModel->getItem((int) $cid);

		// If is not anonymous donation then only push activity stream
		if (!$isAnonymousDonation)
		{
			// Set some of the data for activity
			$act_subtype     = 'payment';
			$act_description = ($campaign->type == 'donation') ? Text::_('COM_JGIVE_ACTIVITY_DONATED') : Text::_('COM_JGIVE_ACTIVITY_INVESTMENT');
			$result          = $this->pushActivity($cid, $act_subtype, $act_description, $uid);

			if (!$result)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Function pushActivity
	 *
	 * @param   INT     $cid              Id
	 * @param   String  $act_subtype      Subtype
	 * @param   String  $act_description  Description
	 * @param   INT     $uid              UID
	 *
	 * @return  boolean.
	 *
	 * @since	1.8
	 */
	public function pushActivity($cid, $act_subtype, $act_description, $uid = 0)
	{
		$params              = ComponentHelper::getParams('com_jgive');
		$integration_option  = $params->get('integration');
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		if (empty($uid))
		{
			$user = Factory::getUser();
			$actor_id = $user->get('id');
		}
		else
		{
			$actor_id = $uid;
		}

		$act_type = 'jgive';

		$singleCampaignItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all', 1);
		$act_link  = Uri::root() . substr(
			Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $cid . '&Itemid=' . $singleCampaignItemid), strlen(Uri::base(true)) + 1
		);

		$path = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

		if (!class_exists('campaignHelper'))
		{
			JLoader::register('campaignHelper', $path);
			JLoader::load('campaignHelper');
		}

		$campaignHelper = new campaignHelper;

		$act_title = $campaignHelper->getCampaignTitleFromCid($cid);

		if ($integration_option == 'joomla')
		{
			return true;
		}
		elseif ($integration_option == 'cb')
		{
			$result = $this->pushToCBActivity($actor_id, $act_type, $act_subtype, $act_description, $act_link, $act_title);

			if (!$result)
			{
				return false;
			}
		}
		elseif ($integration_option == 'jomsocial')
		{
			$result = $this->pushToJomsocialActivity($actor_id, $cid, $act_type, $act_subtype, $act_description, $act_link, $act_title);

			if (!$result)
			{
				return false;
			}
		}
		elseif ($integration_option == 'jomwall')
		{
			$result = $this->pushToJomwallActivity($actor_id, $act_type, $act_subtype, $act_description, $act_link, $act_title);

			if (!$result)
			{
				return false;
			}
		}
		elseif ($integration_option == 'easySocial')
		{
			$result = $this->pushToEasySocialActivity($actor_id, $cid, $act_type, $act_subtype, $act_description, $act_link, $act_title);

			if (!$result)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Function pushToCBActivity
	 *
	 * @param   Integer  $actor_id         Id
	 * @param   String   $cid              Campaign Id
	 * @param   String   $act_type         Type
	 * @param   String   $act_subtype      Subtype
	 * @param   String   $act_description  Description
	 * @param   String   $act_link         Link
	 * @param   String   $act_title        Title
	 *
	 * @return  boolean|Array
	 *
	 * @since	1.8
	 */
	public function pushToEasySocialActivity($actor_id, $cid, $act_type = '', $act_subtype = '', $act_description = '', $act_link = '', $act_title = '')
	{
		$elementInfo = new stdclass;
		$elementInfo->id = $cid;
		$elementInfo->title = $act_title;
		$elementInfo->url = $act_link;
		$elementInfo->html = $act_description;
		$elementInfo->params = array('id' => $cid);

		$streamOption = array();
		$streamOption['actorId'] = $actor_id;
		$streamOption['contextType'] = 'jgive_camps';
		$streamOption['actType'] = 'full';
		$streamOption['title'] = $act_title;
		$streamOption['content'] = $act_description;
		$streamOption['targetId'] = '';

		$actionArray = explode(" ", $act_description);

		if (in_array("created", $actionArray))
		{
			$streamOption['action'] = "create";
		}
		elseif (in_array("edited", $actionArray))
		{
			$streamOption['action'] = "edit";
		}
		elseif(in_array("donation", $actionArray))
		{
			$streamOption['action'] = "donation";
		}
		elseif(in_array("invested", $actionArray))
		{
			$streamOption['action'] = "investment";
		}

		$streamOption['actAccess']   = '';
		$streamOption['elementInfo'] = $elementInfo;

		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$result              = $jgiveFrontendHelper->socialLibraryObject->advPushActivity($streamOption);

		return $result;
	}

	/**
	 * Function pushToCBActivity
	 *
	 * @param   INteger  $actor_id         Id
	 * @param   String   $act_type         Type
	 * @param   String   $act_subtype      Subtype
	 * @param   String   $act_description  Description
	 * @param   String   $act_link         Link
	 * @param   String   $act_title        Title
	 *
	 * @return  boolean|Array
	 *
	 * @since	1.8
	 */
	public function pushToCBActivity($actor_id, $act_type, $act_subtype, $act_description, $act_link, $act_title)
	{
		// Load CB framework
		global $_CB_framework, $mainframe;

		if (defined('JPATH_ADMINISTRATOR'))
		{
			if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';

				return false;
			}

			include_once JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';
		}
		else
		{
			if (!file_exists($mainframe->getCfg('absolute_path') . '/administrator/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';

				return false;
			}

			include_once $mainframe->getCfg('absolute_path') . '/administrator/components/com_comprofiler/plugin.foundation.php';
		}

		cbimport('cb.plugins');
		cbimport('cb.html');
		cbimport('cb.database');
		cbimport('language.front');
		cbimport('cb.snoopy');
		cbimport('cb.imgtoolbox');

		global $_CB_framework, $_CB_database, $ueConfig;

		// Load cb activity plugin class
		$cbactivity_path = JPATH_SITE . "/components/com_comprofiler/plugin/user/plug_cbactivity/cbactivity.class.php";

		if (!file_exists($cbactivity_path))
		{
			return false;
		}

		require_once JPATH_SITE . "/components/com_comprofiler/plugin/user/plug_cbactivity/cbactivity.class.php";

		// Push activity
		$linkHTML = '<a href="' . $act_link . '">' . $act_title . '</a>';

		$activity = cbactivityData::getActivity(array('id', '=', $id), null, null, false);
		$activity->set('user_id', $actor_id);
		$activity->set('type', $act_type);
		$activity->set('subtype', $act_subtype);
		$activity->set('title', $act_description . ' ' . $linkHTML);
		$activity->set('icon', 'nameplate');
		$activity->set('date', cbactivityClass::getUTCDate());
		$activity->store();

		return true;
	}

	/**
	 * Function pushToJomsocialActivity
	 *
	 * @param   Integer  $actor_id         Id
	 * @param   Integer  $cid              Campaign Id
	 * @param   String   $act_type         Type
	 * @param   String   $act_subtype      Subtype
	 * @param   String   $act_description  Description
	 * @param   String   $act_link         Link
	 * @param   String   $act_title        Title
	 *
	 * @return  boolean|Array
	 *
	 * @since	1.8
	 */
	public function pushToJomsocialActivity($actor_id, $cid, $act_type, $act_subtype, $act_description, $act_link, $act_title)
	{
		/*load Jomsocial core*/
		$jspath = JPATH_ROOT . '/components/com_community';

		if (file_exists($jspath))
		{
			include_once $jspath . '/libraries/core.php';
		}

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');
		$jgiveCampaignModel = BaseDatabaseModel::getInstance('CampaignForm', 'JgiveModel');
		$campaign           = $jgiveCampaignModel->getItem((int) $cid);

		$campaignImagePath = Uri::root() . 'media/com_jgive/images/default_campaign.png';

		if (!empty($campaign->image))
		{
			$campaignImagePath  = $campaign->image->media_m;
		}

		// Push activity
		$linkHTML     = '<a href="' . $act_link . '">' . $act_title . '</a>';
		$act          = new stdClass;
		$act->cmd     = 'wall.write';
		$act->actor   = $actor_id;
		$act->target  = 0;
		$act->title   = '{actor} ' . $act_description . ' ' . $linkHTML;
		$act->content = '<img src="' . $campaignImagePath . '" alt="' . $act_title . '"/>';
		$act->app     = 'wall';
		$act->cid     = 0;
		$jspath       = JPATH_ROOT . '/components/com_community';

		if (file_exists($jspath))
		{
			CFactory::load('libraries', 'activities');
			CActivityStream::add($act);

			return true;
		}

		return false;
	}

	/**
	 * Function pushToJomwallActivity
	 *
	 * @param   Integer  $actor_id         Id
	 * @param   String   $act_type         Type
	 * @param   String   $act_subtype      Subtype
	 * @param   String   $act_description  Description
	 * @param   String   $act_link         Link
	 * @param   String   $act_title        Title
	 *
	 * @return  boolean|Array
	 *
	 * @since	1.8
	 */
	public function pushToJomwallActivity($actor_id, $act_type, $act_subtype, $act_description, $act_link, $act_title)
	{
		/*load jomwall core*/
		if (!class_exists('AwdwallHelperUser'))
		{
			require_once JPATH_SITE . '/components/com_awdwall/helpers/user.php';
		}

		$linkHTML   = '<a href="' . $act_link . '">' . $act_title . '</a>';
		$comment    = $act_description . ' ' . $linkHTML;
		$attachment = $act_link;
		$type       = 'text';
		$imgpath    = null;
		$params     = array();

		AwdwallHelperUser::addtostream($comment, $attachment, $type, $actor_id, $imgpath, $params);

		return true;
	}

	/**
	 * Function to Check campaign is published.
	 *
	 * @param   INT  $cid  Campaign Id
	 *
	 * @return  boolean.
	 *
	 * @since	1.8
	 */
	public function checkCampaignIsPublished($cid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->qn('published'));
		$query->from($db->qn('#__jg_campaigns', 'c'));
		$query->where($db->qn('c.id') . ' = ' . (int) $cid);
		$db->setQuery($query);
		$campaignStatus = $db->LoadResult();

		if ($campaignStatus)
		{
			return 1;
		}

		return 0;
	}

	public function onAfterJGiveCampaignGoalAmountReached($orderId)
	{
		// Import the donation model from the JGive component
		JLoader::import('models.donation', JPATH_SITE . '/components/com_jgive');
		$donationModel = new JgiveModelDonation;

		// Get donation details for the provided order ID
		$donationDetails = $donationModel->getItem($orderId);
		// Extract the campaign ID and donor user ID from the donation details
		$cid = $donationDetails['campaign']->id;
		$uid = $donationDetails['donor']->user_id;
		// Check if the campaign ID exists
		if (!empty($cid))
		{
			// Set the activity subtype and description for the campaign goal amount reached
			$act_subtype     = 'campaign';
			$act_description = Text::_('COM_JGIVE_ACTIVITY_CAMPAIGN_GOAL_AMOUNT_REACHED') . ' ';
			
			// Push the activity to the activity streams
			$result = $this->pushActivity($cid, $act_subtype, $act_description, $uid);

			// If activity push fails, return false
			if (!$result)
			{
				return false;
			}
			
			// Return true if activity push succeeds
			return true;
		}
		else
		{
			// Return false if no campaign ID is found
			return false;
		}
	}
	
	public function onAfterJGiveDonarTakingGiveback($orderId)
	{
		// Import the donation model from the JGive component
		JLoader::import('models.donation', JPATH_SITE . '/components/com_jgive');
		$donationModel = new JgiveModelDonation;
		
		// Get donation details for the provided order ID
		$donationDetails = $donationModel->getItem($orderId);
		
		// Extract the campaign ID and donor user ID from the donation details
		$cid = $donationDetails['campaign']->id;
		$uid = $donationDetails['donor']->user_id;
		
		// Check if the campaign ID exists
		if (!empty($cid))
		{
			// Set the activity subtype and description for the donor taking giveback
			$act_subtype     = 'campaign';
			$act_description = Text::_('COM_JGIVE_ACTIVITY_CAMPAIGN_DONAR_TAKING_GIVEBACK') . ' ';
			
			// Push the activity to the activity streams
			$result = $this->pushActivity($cid, $act_subtype, $act_description, $uid);
			
			// If activity push fails, return false
			if (!$result)
			{
				return false;
			}
			
			// Return true if activity push succeeds
			return true;
		}
		else 
		{
			// Return false if no campaign ID is found
			return false;
		}
	}
}
