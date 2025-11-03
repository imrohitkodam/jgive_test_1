<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

jimport('techjoomla.jsocial.jsocial');
JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);

$lang = Factory::getLanguage();
$lang->load('plg_system_jgiveactivities', JPATH_ADMINISTRATOR);

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_activitystream/models', 'ActivityStreamModel');

/**
 * jgive_activities
 *
 * @package     Jgive_Activities
 * @subpackage  site
 * @since       1.0
 */
class PlgSystemJgiveActivitiesHelper
{
	public $jGiveFrontendHelper;

	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		if (!ComponentHelper::isEnabled('com_jgive', true))
		{
			return;
		}

		// Load activity component models
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_activitystream/models');

		// Load activity component models
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');

		// Load activity component tables
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_activitystream/tables');

		if (!class_exists('JgiveFrontendHelper'))
		{
			$JgiveFrontendHelperPath = JPATH_SITE . '/components/com_jgive/helper.php';
			JLoader::register('JgiveFrontendHelper', $JgiveFrontendHelperPath);
			JLoader::load('JgiveFrontendHelper');
			$this->jGiveFrontendHelper = new JgiveFrontendHelper;
		}
	}

	/**
	 * Method to get actor data
	 *
	 * @param   OBJECT  $user  user object
	 *
	 * @return  ARRAY  User Data
	 *
	 * @since   1.0
	 */
	public function getActorData($user)
	{
		$userData         = array();
		$userData['type'] = 'person';
		$userData['id']   = $user->id;
		$userData['name'] = $user->get('name') ? $user->get('name') : 'Guest';

		JLoader::import('integrations', JPATH_SITE . '/components/com_jgive/helpers');
		$jgiveIntegrationsHelper = new JgiveIntegrationsHelper;
		$userData['url']         = $jgiveIntegrationsHelper->getUserProfileUrl($user->id, true);

		$imageData           = array();
		$imageData['type']   = "link";
		$imageData['avatar'] = $jgiveIntegrationsHelper->getUserAvatar($user->id, true);

		if (strpos($imageData['avatar'], 'www.gravatar.com'))
		{
			$imageData['gravatar'] = true;
		}
		else
		{
			$imageData['gravatar'] = false;
		}

		$userData['image'] = json_encode($imageData);

		return $userData;
	}

	/**
	 * Method to add activity for adding images to the campaign
	 *
	 * @param   ARRAY    $newGalleryImages  array of newly added images
	 * @param   Integer  $campaignid        campaign Id
	 * @param   Array    $campaignData      campaignData
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function addMediaActivity($newGalleryImages, $campaignid, $campaignData)
	{
		if (!empty($newGalleryImages))
		{
			$com_params    = ComponentHelper::getParams('com_jgive');
			$storagePath   = $com_params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');
			$filetable     = Table::getInstance('Files', 'TJMediaTable');
			$user          = $campaignData['creator_id'] ? $campaignData['creator_id'] : Factory::getUser();
			$data          = array();
			$data['id']    = '';
			$actorData     = $this->getActorData(Factory::getUser($user));
			$data['actor'] = json_encode($actorData);

			foreach ($newGalleryImages as $newGalleryImage)
			{
				if ($newGalleryImage != 0)
				{
					$filetable->load($newGalleryImage);
					$mediaType         = explode(".", $filetable->type);
					$imgPath           = $storagePath . '/' . $mediaType[0] . 's';
					$mediaConfig       = array('id' => $newGalleryImage, 'uploadPath' => $imgPath);
					$campaignMediaData = TJMediaStorageLocal::getInstance($mediaConfig);
				}

				$campaignContentType = explode(".", $campaignMediaData->type);

				if ($campaignContentType[0] == "image")
				{
					$campaignImageGalleryData[] = $campaignMediaData->media;
				}
				else
				{
					$campaignVideoGalleryData[] = $campaignMediaData;
				}
			}

			$user = Factory::getUser();

			if (isset($campaignImageGalleryData))
			{
				// Get campaign-object data
				$objectData          = array();
				$objectData['type']  = 'image';
				$objectData['url']   = json_encode($campaignImageGalleryData);
				$objectData['count'] = count($campaignImageGalleryData);
				$data['object']      = json_encode($objectData);
				$data['actor_id']    = $user->id;
				$data['object_id']   = "image";
				$data['type']        = 'campaign.addimage';
				$data['template']    = 'image.mustache';
				$data['target_id']   = $campaignData['id'];
				$targetData          = array();
				$targetData['id']    = $campaignData['id'];
				$targetData['name']  = $campaignData['title'];
				$targetData['url']   = $this->jGiveFrontendHelper->getCampaignUrl($campaignData['id'], true);
				$targetData['type']  = 'campaign';
				$data['target']      = json_encode($targetData);

				$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');
				$result                      = $activityStreamModelActivity->save($data);
			}

			if (isset($campaignVideoGalleryData))
			{
				JLoader::import('media', JPATH_SITE . '/components/com_jgive/helpers');
				$jgiveMediaHelper = new jgiveMediaHelper;

				foreach ($campaignVideoGalleryData as $campaignVideoData)
				{
					$campaignVideoType = substr($campaignVideoData->type, 6);
					$videoIdThumb      = $jgiveMediaHelper->videoId($campaignVideoType, $campaignVideoData->original_filename);
					$videoThumbnail    = $jgiveMediaHelper->videoThumbnail($campaignVideoType, $videoIdThumb);

					$videoDetails = array();
					$videoDetails['external'] = true;

					if ($campaignVideoData->type != 'video.youtube' && $campaignVideoData->type != 'video.vimeo')
					{
						$videoDetails['external'] = false;
						$videoThumbnail = str_replace(Uri::root(true), '', $videoThumbnail);
						$videoThumbnail = substr($videoThumbnail, 1);
					}

					$videoDetails['thumbSrc'] = $videoThumbnail;
					$videoDetails['url']      = $campaignVideoData->media;
					$link                     = "index.php?option=com_jgive&view=campaign&layout=default_playvideo&id=";
					$videoDetails['link']     = $link . $campaignid . "&vid=" . $campaignVideoData->id . "&type=" . trim($campaignVideoType) . "&tmpl=component";
					$videoDetails['playIcon'] = 'media/com_jgive/images/play_icon.png';
					$VideoActivityData[]      = $videoDetails;
				}

				// Get campaign-object data
				$objectData           = array();
				$objectData['videos'] = json_encode($VideoActivityData);
				$objectData['count']  = count($VideoActivityData);
				$data['object']       = json_encode($objectData);
				$data['actor_id']     = $user->id;
				$data['object_id']    = "video";
				$data['type']         = 'campaign.addvideo';
				$data['template']     = 'video.mustache';
				$data['target_id']    = $campaignData['id'];
				$targetData           = array();
				$targetData['id']     = $campaignData['id'];
				$targetData['name']   = $campaignData['title'];
				$targetData['url']    = $this->jGiveFrontendHelper->getCampaignUrl($campaignData['id'], true);
				$targetData['type']   = 'campaign';
				$data['target']       = json_encode($targetData);

				$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');
				$result                      = $activityStreamModelActivity->save($data);
			}

			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to add avtivity for adding give back to the campaign
	 *
	 * @param   ARRAY  $newAddedGivebacks  array of newly added give backs
	 *
	 * @param   INT    $cid                campaign id
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function addGivebackActivity($newAddedGivebacks, $cid)
	{
		if (!empty($newAddedGivebacks))
		{
			foreach ($newAddedGivebacks as $k => $newAddedGiveback)
			{
				if (!empty($newAddedGiveback))
				{
					if (!empty($newAddedGiveback['giveback_image']))
					{
						$newAddedGivebacks[$k]['giveback_image'] = str_replace(Uri::root(), '', $newAddedGiveback['giveback_image']);
					}
					else
					{
						$newAddedGivebacks[$k]['giveback_image'] = '';
					}
				}
			}

			$user          = Factory::getUser();
			$data          = array();
			$data['id']    = '';
			$actorData     = $this->getActorData($user);
			$data['actor'] = json_encode($actorData);

			// Get campaign-object data
			$objectData          = array();
			$objectData['type']  = 'giveback';
			$objectData['url']   = json_encode($newAddedGivebacks);
			$objectData['count'] = count($newAddedGivebacks);
			$data['object']      = json_encode($objectData);
			$data['actor_id']    = $user->id;
			$data['object_id']   = "giveback";
			$data['type']        = 'campaign.addgiveback';
			$data['template']    = 'giveback.mustache';

			// Load component models
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');

			$jgiveCampaignModel = BaseDatabaseModel::getInstance('CampaignForm', 'JgiveModel');
			$campaignData       = $jgiveCampaignModel->getItem($cid);

			$data['target_id']           = $campaignData->id;
			$targetData                  = array();
			$targetData['id']            = $campaignData->id;
			$targetData['name']          = $campaignData->title;
			$targetData['url']           = $this->jGiveFrontendHelper->getCampaignUrl($campaignData->id, true);
			$targetData['type']          = 'campaign';
			$data['target']              = json_encode($targetData);
			$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');
			$result                      = $activityStreamModelActivity->save($data);

			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to add avtivity for campaign date extension
	 *
	 * @param   OBJECT  $newDetails  campaign details
	 *
	 * @param   OBJECT  $oldDetails  campaign details
	 *
	 * @param   INT     $cid         campaign id
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function endDateChangeActivity($newDetails, $oldDetails, $cid)
	{
		if (!empty($newDetails) && !empty($oldDetails))
		{
			$date_diff = date_diff(date_create($oldDetails), date_create($newDetails));

			if ($date_diff->days > 0)
			{
				$user             = Factory::getUser();
				$data             = array();
				$data['id']       = '';
				$actorData        = $this->getActorData($user);
				$data['actor_id'] = $user->id;
				$data['actor']    = json_encode($actorData);

				// Load component models
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');

				$jgiveCampaignModel = BaseDatabaseModel::getInstance('CampaignForm', 'JgiveModel');
				$campaignData       = $jgiveCampaignModel->getItem($cid);

				// Get campaign-object data
				$objectData         = array();
				$objectData['type'] = 'campaign';

				$objectData['newenddate'] = date("d M Y", strtotime($newDetails));
				$objectData['url']        = $this->jGiveFrontendHelper->getCampaignUrl($cid, true);

				$data['object']    = json_encode($objectData);
				$data['object_id'] = $campaignData->id;

				$targetData         = array();
				$targetData['type'] = 'campaign';
				$targetData['name'] = $campaignData->title;
				$targetData['id']   = $campaignData->id;
				$targetData['url']  = $this->jGiveFrontendHelper->getCampaignUrl($campaignData->id, true);

				$data['target']    = json_encode($targetData);
				$data['target_id'] = $campaignData->id;

				$data['type'] = 'campaign.extended';

				// If campaign end date is extended then use extended template else use datechange template
				if ($date_diff->invert == 0)
				{
					$data['template'] = 'extended.mustache';
				}
				else
				{
					$data['template'] = 'datechange.mustache';
				}

				$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');
				$result                      = $activityStreamModelActivity->save($data);

				return $result;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to add avtivity for campaign complition
	 *
	 * @param   ARRAY|boolean  $donationDetails  Donation / Order Array, false on failure
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function addCampaignCompleteActivity($donationDetails)
	{
		if ($donationDetails['campaign']->remaining_amount <= 0)
		{
			if ($donationDetails['campaign']->donation_amount >= abs($donationDetails['campaign']->remaining_amount))
			{
				$user = Factory::getUser($donationDetails['donor']->user_id);
				$data = array();

				$data['id']       = '';
				$actorData        = $this->getActorData($user);
				$data['actor']    = json_encode($actorData);
				$data['actor_id'] = $user->id;

				$data['object_id']  = $donationDetails['campaign']->id;
				$objectData         = array();
				$objectData['id']   = $donationDetails['campaign']->id;
				$objectData['name'] = $donationDetails['campaign']->title;
				$objectData['url']  = $this->getCampaignUrl($donationDetails['campaign']->id, true);

				$objectData['type'] = 'campaign';
				$data['object']     = json_encode($objectData);
				$data['type']       = 'campaign.completed';
				$data['template']   = 'completed.mustache';

				$targetData         = array();
				$targetData['type'] = 'campaign';
				$targetData['name'] = $donationDetails['campaign']->title;
				$targetData['id']   = $donationDetails['campaign']->id;
				$targetData['url']  = $this->getCampaignUrl($donationDetails['campaign']->id, true);

				$data['target']    = json_encode($targetData);
				$data['target_id'] = $donationDetails['campaign']->id;

				$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

				$result = $activityStreamModelActivity->save($data);

				return $result;
			}
		}

		return false;
	}

	/**
	 * Get campaign details page URL
	 *
	 * @param   INT      $id        campaign id
	 * @param   BOOLEAN  $relative  true for relative and false for absolute
	 * @param   BOOLEAN  $sef       true for sef anf false for non sef
	 *
	 * @return  STRING|BOOLEAN  campaign details page url else false
	 *
	 * @since   1.5
	 */
	public function getCampaignUrl($id, $relative = false, $sef = true)
	{
		if (!empty($id))
		{
			$itemId = $this->jGiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default');

			if ($relative)
			{
				$campaignUrl = 'index.php?option=com_jgive&view=campaign&layout=default&id=' . $id . '&Itemid=' . $itemId;
			}
			else
			{
				$campaignUrl = Uri::root() . 'index.php?option=com_jgive&view=campaign&layout=default&id=' . $id . '&Itemid=' . $itemId;
			}

			if ($sef)
			{
				// Get sef URL for campaign
				$campaignUrl = Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $id . '&Itemid=' . $itemId);

				// If function is called from backend and we want a SEF url
				if ($relative)
				{
					$campaignUrl = substr(
					$campaignUrl, strlen(Uri::base(true)) + 1
					);
				}
			}

			return $campaignUrl;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to add avtivity for campaign donation
	 *
	 * @param   ARRAY|boolean  $donationDetails  donation details, false on failure
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function addDonationActivity($donationDetails)
	{
		$user               = Factory::getUser($donationDetails['donor']->user_id);
		$activityData       = array();
		$activityData['id'] = '';

		if ($donationDetails['payment']->annonymous_donation == 1)
		{
			// For annonymous donation
			$imageData                = array();
			$imageData['type']        = "link";
			$imageData['avatar']      = 'media/com_jgive/images/default_avatar.png';
			$activityData['actor']    = json_encode(array('image' => json_encode($imageData)));
			$activityData['actor_id'] = $user->id ? $user->id : 'Guest';
		}
		else
		{
			$actorData                = $this->getActorData($user);

			// Show organization name if donor type is an organization.
			if ($donationDetails['donor']->donor_type == 'org' && !empty($donationDetails['donor']->org_name))
			{
				$actorData['name'] = $donationDetails['donor']->org_name;
			}

			$activityData['actor']    = json_encode($actorData);
			$activityData['actor_id'] = $user->id ? $user->id : 'Guest';
		}

		$objectData                = array();
		$objectData['type']        = $donationDetails['campaign']->type;
		$formattedPrice            = $this->jGiveFrontendHelper->getFormattedPrice($donationDetails['payment']->original_amount);
		$objectData['amount']      = str_replace("&nbsp;", "", strip_tags($formattedPrice));
		$activityData['object']    = json_encode($objectData);
		$activityData['object_id'] = 'donation';

		// Get campaign-target data
		$targetData                = array();
		$targetData['id']          = $donationDetails['campaign']->id;
		$targetData['type']        = 'campaign';
		$targetData['url']         = $this->getCampaignUrl($donationDetails['campaign']->id, true);
		$targetData['name']        = $donationDetails['campaign']->title;
		$activityData['target']    = json_encode($targetData);
		$activityData['target_id'] = $donationDetails['campaign']->id;

		$activityData['type'] = 'jgive.donation';

		// For annonymous donation
		if ($objectData['type'] == 'donation')
		{
			if ($donationDetails['payment']->annonymous_donation == 1)
			{
				$activityData['template'] = 'anonymousdonation.mustache';
			}
			else
			{
				$activityData['template'] = 'donation.mustache';
			}
		}
		elseif ($objectData['type'] == 'investment')
		{
			if ($donationDetails['payment']->annonymous_donation == 1)
			{
				$activityData['template'] = 'anonymousinvestment.mustache';
			}
			else
			{
				$activityData['template'] = 'investment.mustache';
			}
		}

		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');
		$result                      = $activityStreamModelActivity->save($activityData);

		return $result;
	}

	/**
	 * Function to add activity for new campaign added
	 *
	 * @param   INT    $campaignId    Campaign Id
	 *
	 * @param   array  $campaignData  campaign data
	 *
	 * @return  boolean
	 *
	 * @since	1.8
	 */
	public function addCampaignActivity($campaignId, $campaignData)
	{
		$userId                      = (!empty($campaignData['creator_id'])) ? $campaignData['creator_id'] : Factory::getUser()->id;
		$user                        = Factory::getUser($userId);
		$activityData                = array();
		$activityData['id']          = '';
		$actorData                   = $this->getActorData($user);
		$activityData['actor']       = json_encode($actorData);
		$activityData['actor_id']    = $user->id;

		$objectData                  = array();
		$objectData['type']          = 'campaign';
		$objectData['name']          = $campaignData['title'];
		$objectData['id']            = $campaignId;
		$objectData['url']           = $this->jGiveFrontendHelper->getCampaignUrl($campaignId, true);

		$activityData['object']      = json_encode($objectData);
		$activityData['object_id']   = $campaignId;

		$targetData                  = array();
		$targetData['type']          = 'campaign';
		$targetData['name']          = $campaignData['title'];
		$targetData['id']            = $campaignId;
		$targetData['url']           = $this->jGiveFrontendHelper->getCampaignUrl($campaignId, true);

		$activityData['target']      = json_encode($targetData);
		$activityData['target_id']   = $campaignId;

		$activityData['type']        = 'jgive.addcampaign';
		$activityData['template']    = 'addcampaign.mustache';

		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');
		$result                      = $activityStreamModelActivity->save($activityData);

		return $result;
	}

	/**
	 * Function to add activity for every new report added
	 *
	 * @param   array  $reportData  report data
	 *
	 * @return  boolean
	 *
	 * @since	2.2.0
	 */
	public function addReportActivity($reportData)
	{
		/* Create JSON for "actor" entity */
		$reportId   = $reportData['id'];
		$campaignId = $reportData['campaign_id'];

		$campaignTable = Table::getInstance('campaign', 'JGiveTable', array());
		$campaignTable->load(array('id' => $campaignId));
		$campaignName = $campaignTable->title;

		$userId                      = (!empty($reportData['creator_id'])) ? $reportData['creator_id'] : Factory::getUser()->id;
		$user                        = Factory::getUser($userId);
		$activityData                = array();
		$activityData['id']          = '';
		$actorData                   = $this->getActorData($user);
		$activityData['actor']       = json_encode($actorData);
		$activityData['actor_id']    = $user->id;

		/* Create JSON for "object" entity */
		$objectData                  = array();
		$objectData['type']          = 'report';
		$objectData['name']          = $reportData['title'];
		$objectData['id']            = $reportId;
		$objectData['url']           = $this->jGiveFrontendHelper->getReportUrl($reportId, true);

		$activityData['object']      = json_encode($objectData);
		$activityData['object_id']   = $reportId;

		/* Create JSON for "target" entity */
		$targetData                  = array();
		$targetData['type']          = 'campaign';
		$targetData['name']          = $campaignName;
		$targetData['id']            = $campaignId;
		$targetData['url']           = $this->jGiveFrontendHelper->getCampaignUrl($campaignId, true);

		$activityData['target']      = json_encode($targetData);
		$activityData['target_id']   = $campaignId;

		$activityData['type']        = 'campaign.addreport';
		$activityData['template']    = 'addreport.mustache';

		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');
		$result                      = $activityStreamModelActivity->save($activityData);

		return $result;
	}

	/**
	 * Method for remove media activity
	 *
	 * @param   Integer  $mediaId  Media Id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function removeMediaActivity($mediaId)
	{
		$mediaXrefTable = Table::getInstance('mediaxref', 'JGiveTable', array());
		$mediaXrefTable->load(array('media_id' => (int) $mediaId, 'client' => 'com_jgive.campaign'));

		// Getting campaign Id
		$campaignId = $mediaXrefTable->client_id;

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaignform');
		$jgiveModelCampaignFrom = BaseDatabaseModel::getInstance('campaignform', 'jgiveModel');

		// Getting campaign data from campaign id
		$campaignData = $jgiveModelCampaignFrom->getItem($campaignId);

		if (isset($campaignData->gallery) && !empty($campaignData->gallery))
		{
			foreach ($campaignData->gallery as $k => $campaignMediaData)
			{
				$mediaIsRemoved = 0;

				if ($campaignData->gallery[$k]->id == $mediaId)
				{
					$mediaIsRemoved = 1;
				}

				if ($mediaIsRemoved)
				{
					$campaignContentType = substr($campaignData->gallery[$k]->type, 0, 5);

					if ($campaignContentType == "image")
					{
						$type = "campaign.addimage";
						$deleteMediaPath = $campaignData->gallery[$k]->uploadPath . '/' . $campaignData->gallery[$k]->source;
					}
					elseif ($campaignContentType == "video")
					{
						$type = "campaign.addvideo";
						$deleteMediaPath = $campaignData->gallery[$k]->media;
					}

					$db = Factory::getDbo();
					$query = $db->getQuery(true);
					$query->select('*');
					$query->from($db->quoteName('#__tj_activities'));
					$query->where($db->quoteName('target_id') . ' = ' . $campaignId);
					$query->where($db->quoteName('type') . " = '" . $type . "'");
					$db->setQuery($query);
					$activities = $db->loadAssocList();

					$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

					if (!empty($activities))
					{
						foreach ($activities as $activity)
						{
							$objectData = json_decode($activity['object']);

							if (isset($objectData->url))
							{
								$images = json_decode($objectData->url);

								foreach ($images as $key => $image)
								{
									if ($objectData->count == '1')
									{
										if (strpos($image, $deleteMediaPath) !== false)
										{
											$activityStreamModelActivity->delete($activity['id']);
										}
									}
									else
									{
										if (strpos($image, $deleteMediaPath) !== false)
										{
											unset($images[$key]);
											$activityImages = array();

											foreach ($images as $img)
											{
												$activityImages[] = $img;
											}

											$objectData->url = json_encode($activityImages);
											$objectData->count -= 1;
											$activity['object'] = json_encode($objectData);
											$activityStreamModelActivity->save($activity);
										}
									}
								}
							}

							if (isset($objectData->videos))
							{
								$videos = json_decode($objectData->videos);

								foreach ($videos as $key => $video)
								{
									if ($objectData->count == '1')
									{
										if (strpos($video->url, $deleteMediaPath) !== false)
										{
											$activityStreamModelActivity->delete($activity['id']);
										}
									}
									else
									{
										if (strpos($video->url, $deleteMediaPath) !== false)
										{
											unset($videos[$key]);

											$activityVideos = array();

											foreach ($videos as $vid)
											{
												$activityVideos[] = $vid;
											}

											$objectData->videos = json_encode($activityVideos);
											$objectData->count -= 1;
											$activity['object'] = json_encode($objectData);
											$activityStreamModelActivity->save($activity);
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Function to Check campaign is published.
	 *
	 * @param   INT  $cid  Campaign Id
	 *
	 * @return  Integer
	 *
	 * @since	1.8
	 */
	public function checkCampaignIsPublished($cid)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('published'));
		$query->from($db->quoteName('#__jg_campaigns'));
		$query->where($db->quoteName('id') . ' = ' . $cid);
		$db->setQuery($query);
		$campaignStatus = $db->LoadResult();

		if ($campaignStatus)
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Function onPostActivity
	 *
	 * @param   MIXED  $data  data
	 *
	 * @return  Array|Boolean
	 *
	 * @since	1.8
	 */
	public function onPostActivity($data)
	{
		$user = Factory::getUser();
		$result = array();

		if (empty($user->id))
		{
			$result['error'] = Text::_('COM_JGIVE_TEXT_ACTIVITY_POST_GUEST_ERROR_MSG');

			return $result;
		}

		$activityData             = array();
		$activityData['id']       = '';
		$actorData                = $this->getActorData($user);
		$activityData['actor']    = json_encode($actorData);
		$activityData['actor_id'] = $user->id;

		$cid = $data['cid'];

		// Load component models
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');

		$jgiveCampaignModel = BaseDatabaseModel::getInstance('CampaignForm', 'JgiveModel');
		$campaignData       = $jgiveCampaignModel->getItem($cid);

		if ($campaignData->creator_id != $user->id)
		{
			$result['error'] = Text::_('COM_JGIVE_TEXT_ACTIVITY_POST_FAIL_MSG');

			return $result;
		}

		if (strlen($data['postData']) > 300)
		{
			$result['error'] = Text::_('COM_JGIVE_TEXT_ACTIVITY_POST_EXCEED_FAIL_MSG');

			return $result;
		}

		$objectData                = array();
		$objectData['type']        = 'text';
		$objectData['postData']    = $data['postData'];
		$activityData['object']    = json_encode($objectData);
		$activityData['object_id'] = 'text';

		$targetData         = array();
		$targetData['id']   = $campaignData->id;
		$targetData['name'] = $campaignData->title;
		$targetData['url']  = $this->jGiveFrontendHelper->getCampaignUrl($campaignData->id, true);
		$targetData['type'] = 'campaign';

		$activityData['target']    = json_encode($targetData);
		$activityData['target_id'] = $cid;
		$activityData['type']      = 'jgive.textpost';
		$activityData['template']  = 'textpost.mustache';

		if (isset($data['cdate']))
		{
			$activityData['created_date'] = $data['cdate'];
		}

		if (isset($data['mdate']))
		{
			$activityData['updated_date'] = $data['mdate'];
		}

		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');
		$result                      = $activityStreamModelActivity->save($activityData);

		return $result;
	}

	/**
	 * Function to get deleted givebacks
	 *
	 * @param   String  $deletedGiveBacks  deleted gievbacks array
	 *
	 * @param   String  $campaignId        Old Details
	 *
	 * @return  Boolean
	 *
	 * @since	1.8
	 */
	public function removeGiveBackActivity($deletedGiveBacks, $campaignId)
	{
		if (!empty($deletedGiveBacks))
		{
			foreach ($deletedGiveBacks as $giveBack)
			{
				$this->removeGiveAwayActivity($giveBack, $campaignId);
			}
		}

		return true;
	}

	/**
	 * Function to delete/update activities related to giveback
	 *
	 * @param   String  $deletedGivebackId  deleted giveback id
	 * @param   String  $cid                campaign Id
	 *
	 * @return  void
	 *
	 * @since	1.8
	 */
	public function removeGiveAwayActivity($deletedGivebackId, $cid)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__tj_activities'));
		$query->where($db->quoteName('target_id') . ' = ' . (int) $cid);
		$query->where($db->quoteName('type') . " = " . $db->quote('campaign.addgiveback'));
		$db->setQuery($query);
		$activities = $db->loadAssocList();
		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

		if (!empty($activities))
		{
			foreach ($activities as $activity)
			{
				$objectData = json_decode($activity['object']);

				// Delete giveback activity, if single activity generated single giveback
				if ($objectData->count == '1')
				{
					// Decode Object url data which contain list of giveback image path with giveback ids
					$objectUrlDataArr = json_decode($objectData->url);

					if ($objectUrlDataArr[0]->giveback_id == $deletedGivebackId)
					{
						$activityStreamModelActivity->delete($activity['id']);
					}
				}
				// Delete particular giveback activity, if single activity generated for more than one givebacks
				else
				{
					// Decode Object url data which contain list of giveback image path with giveback ids
					$objectUrlDataArr = json_decode($objectData->url);

					foreach ($objectUrlDataArr as $key => $objectUrlData)
					{
						if ($objectUrlData->giveback_id == $deletedGivebackId)
						{
							unset($objectUrlDataArr[$key]);

							$arrData = array();

							foreach ($objectUrlDataArr as $gid)
							{
								$arrData[] = $gid;
							}
						}
					}

					if (!empty($arrData))
					{
						// Remove only respected giveback details and remaining object save again
						$objectData->url = json_encode($arrData);
						$objectData->count -= 1;
						$activity['object'] = json_encode($objectData);
						$activityStreamModelActivity->save($activity);
					}
				}
			}
		}
	}

	/**
	 * Function to delete/update activities related to giveback
	 *
	 * @param   Int  $reportId  primary key of report to be deleted
	 *
	 * @return  void
	 *
	 * @since	1.8
	 */
	public function	deleteReportActivity($reportId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__tj_activities'));
		$query->where($db->quoteName('object_id') . ' = ' . (int) $reportId);
		$query->where($db->quoteName('type') . ' = "campaign.addreport"');
		$db->setQuery($query);
		$activities = $db->loadAssocList();

		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

		if (!empty($activities))
		{
			foreach ($activities as $activity)
			{
				$activityStreamModelActivity->delete($activity['id']);
			}
		}
	}
}
