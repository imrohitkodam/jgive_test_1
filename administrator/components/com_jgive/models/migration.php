<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;

JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
JLoader::import('tjvendors', JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers');

/**
 * Jlike Manage Model
 *
 * @since  2.0
 */
class JgiveModelMigration extends BaseDatabaseModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.1
	 */
	public function __construct($config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_activitystream/tables');
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models');
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models');

		parent::__construct($config);
	}

	/**
	 * Method to add avtivity for old campaigns prior to v 2.0
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function createActivity()
	{
		require_once JPATH_SITE . '/plugins/system/jgiveactivities/helper.php';
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');
		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('id');
		$query->from($db->quoteName('#__jg_campaigns'));
		$db->setQuery($query);
		$campaignIds = $db->loadColumn();

		$query = $db->getQuery(true);
		$query->select('DISTINCT target_id');
		$query->from($db->quoteName('#__tj_activities'));
		$db->setQuery($query);
		$targetIds = $db->loadColumn();

		foreach ($campaignIds as $campaignId)
		{
			// Add campaign create activity
			if (!in_array($campaignId, $targetIds))
			{
				$jgiveModelCampaign = BaseDatabaseModel::getInstance('Campaign', 'JgiveModel');

				$campaignDetail = $jgiveModelCampaign->getItem($campaignId);

				$user = Factory::getUser($campaignDetail->creator_id);

				$activityData = array();
				$activityData['id'] = '';
				$activityData['created_date'] = $campaignDetail->created;
				$actorData = $this->getActorData($user);
				$activityData['actor'] = json_encode($actorData);
				$activityData['actor_id'] = $user->get('id');

				$objectData = array();
				$objectData['type'] = 'campaign';
				$objectData['name'] = $campaignDetail->get('title', '', 'STRING');

				$cid = $campaignDetail->id;

				$objectData['id'] = $cid;
				$objectData['url'] = Route::_(Uri::root() . '/index.php?option=com_jgive&view=campaign&layout=default&id=' . $cid);
				$activityData['object'] = json_encode($objectData);
				$activityData['object_id'] = $cid;

				$targetData = array();
				$targetData['type'] = 'campaign';
				$targetData['name'] = $campaignDetail->get('title', '', 'STRING');
				$targetData['id'] = $cid;
				$targetData['url'] = Route::_(Uri::root() . '/index.php?option=com_jgive&view=campaign&layout=default&id=' . $cid);
				$activityData['target'] = json_encode($targetData);
				$activityData['target_id'] = $cid;

				$activityData['type'] = 'jgive.addcampaign';
				$activityData['template'] = 'addcampaign.mustache';

				$activityStreamModelActivity->save($activityData);
			}
		}

		// Push donation activity only is there are no activities
		if (empty($targetIds))
		{
			$this->pushDonationActivity();
		}

		$this->migrateUpdatesToActivity();

		return true;
	}

	/**
	 * Method to get actor data
	 *
	 * @param   OBJECT  $user  user object
	 *
	 * @return  ARRAY  actor data
	 *
	 * @since   1.0
	 */
	public function getActorData($user)
	{
		$userData = array();
		$userData['type'] = 'person';
		$userData['id'] = $user->get('id');
		$userData['name'] = $user->get('name');
		$params = ComponentHelper::getParams('com_jgive');
		$integration = $params->get('integration');

		switch (strtolower($integration))
		{
			case 'jomsocial':
				jimport('techjoomla.jsocial.jomsocial');
				$sociallibraryclass = new JSocialJomsocial;
				break;
			case 'easysocial':
				jimport('techjoomla.jsocial.easysocial');
				$sociallibraryclass = new JSocialEasysocial;
				break;
			case 'joomla':
				jimport('techjoomla.jsocial.joomla');
				$sociallibraryclass = new JSocialJoomla;
				break;
			case 'cb':
				jimport('techjoomla.jsocial.cb');
				$sociallibraryclass = new JSocialCB;
				break;
			case 'jomwall':
				jimport('techjoomla.jsocial.jomwall');
				$sociallibraryclass = new JSocialJomwall;
				break;
			default:
				jimport('techjoomla.jsocial.joomla');
				$sociallibraryclass = new JSocialJoomla;
				break;
		}

		/*Added By Deepa*/
		JLoader::import('helpers.integrations', JPATH_SITE . '/components/com_jgive');
		$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
		$user->avatar      = $JgiveIntegrationsHelper->getUserAvatar($user->id);
		/*End here*/

		$userData['url'] = $sociallibraryclass->getProfileUrl($user);
		$imageData = array();
		$imageData['type'] = "link";

		/* Commented by Deepa*/
		if (!$user->avatar)
		{
			// If no avatar, use default avatar
			$user->avatar = Uri::root(true) . '/media/com_jgive/images/default_avatar.png';
		}

		$imageData['avatar'] = $user->avatar;
		$userData['image'] = json_encode($imageData);

		return $userData;
	}

	/**
	 * Method to add avtivity for campaign donation
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function pushDonationActivity()
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');
		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

		// Actitivty for donations - start
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jg_orders'));
		$query->where($db->quoteName('status') . "=" . "'C'");
		$query->order('order_id ASC');
		$db->setQuery($query);

		$completedDonations = $db->loadColumn();

		JLoader::import('helper', JPATH_SITE . '/components/com_jgive');
		$jGiveFrontendHelper = new JgiveFrontendHelper;

		$completedCampaign = array();

		foreach ($completedDonations as $completedDonation)
		{
			JLoader::import('models.donation', JPATH_SITE . '/components/com_jgive');
			$donationModel = new JgiveModelDonation;
			$donationDetails  = $donationModel->getItem($completedDonation);

			$user = Factory::getUser($donationDetails['donor']->user_id);
			$activityData = array();
			$activityData['id'] = '';

			$activityData['created_date'] = $donationDetails['payment']->cdate;

			if ($donationDetails['payment']->annonymous_donation == 1)
			{
				// For annonymous donation
				$imageData = array();
				$imageData['type'] = "link";
				$imageData['avatar'] = Uri::root(true) . '/media/com_jgive/images/default_avatar.png';
				$activityData['actor'] = json_encode(array('image' => json_encode($imageData)));
				$activityData['actor_id'] = $user->get('id');
			}
			else
			{
				$actorData = $this->getActorData($user);
				$activityData['actor'] = json_encode($actorData);
				$activityData['actor_id'] = $user->get('id');
			}

			$objectData = array();
			$objectData['type'] = 'donation';
			$objectData['amount'] = str_replace("&nbsp;", "", strip_tags($jGiveFrontendHelper->getFormattedPrice($donationDetails['payment']->amount)));
			$activityData['object'] = json_encode($objectData);
			$activityData['object_id'] = 'donation';

			// Get campaign-target data
			$targetData = array();
			$targetData['id'] = $donationDetails['campaign']->id;
			$targetData['type'] = 'campaign';
			$targetData['url'] = Uri::root() . '/index.php?option=com_jgive&view=campaign&layout=default&id=' . $donationDetails['campaign']->id;
			$targetData['name'] = $donationDetails['campaign']->title;
			$activityData['target'] = json_encode($targetData);
			$activityData['target_id'] = $donationDetails['campaign']->id;

			$activityData['type'] = 'jgive.donation';

			// For annonymous donation
			if ($donationDetails['payment']->annonymous_donation == 1)
			{
				$activityData['template'] = 'anonymousdonation.mustache';
			}
			else
			{
				$activityData['template'] = 'donation.mustache';
			}

			// For campaign complete activity
			if (!in_array($donationDetails['campaign']->id, $completedCampaign))
			{
				if ($donationDetails['campaign']->remaining_amount <= 0)
				{
					$completedCampaign[] = $donationDetails['campaign']->id;
					$this->pushCampaignCompletedActivity($donationDetails);
				}
			}

			$activityStreamModelActivity->save($activityData);
		}

		// Actitivty for donations - end
	}

	/**
	 * Method to add avtivity for campaign complition
	 *
	 * @param   ARRAY|boolean  $donationDetails  donation details, false on failure
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function pushCampaignCompletedActivity($donationDetails)
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');
		$user = Factory::getUser($donationDetails['donor']->user_id);
		$data = array();

		$data['id'] = '';
		$actorData = $this->getActorData($user);
		$data['actor'] = json_encode($actorData);
		$data['actor_id'] = $user->get('id');

		$data['object_id'] = $donationDetails['campaign']->id;
		$objectData = array();
		$objectData['id'] = $donationDetails['campaign']->id;
		$objectData['name'] = $donationDetails['campaign']->title;
		$objectData['url'] = Uri::root() . '/index.php?option=com_jgive&view=campaign&layout=default&id=' . $donationDetails['campaign']->id;
		$objectData['type'] = 'campaign';
		$data['object'] = json_encode($objectData);
		$data['type'] = 'campaign.completed';
		$data['template'] = 'completed.mustache';

		$targetData = array();
		$targetData['type'] = 'campaign';
		$targetData['name'] = $donationDetails['campaign']->title;
		$targetData['id'] = $donationDetails['campaign']->id;
		$targetData['url'] = Route::_(Uri::root() . '/index.php?option=com_jgive&view=campaign&layout=default&id=' . $donationDetails['campaign']->id);
		$data['target'] = json_encode($targetData);
		$data['target_id'] = $donationDetails['campaign']->id;

		$activityStreamModelActivity = BaseDatabaseModel::getInstance('Activity', 'ActivityStreamModel');

		$activityStreamModelActivity->save($data);
	}

	/**
	 * Method to migrate Campaigns Updates into Activity
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function migrateUpdatesToActivity()
	{
		// Get table prefix
		$config   = Factory::getConfig();
		$dbprefix = $config->get('dbprefix');

		// Checking table is exist or not
		$query = "SHOW TABLES LIKE '" . $dbprefix . "jg_updates'";
		$this->_db->setQuery($query);
		$fields = $this->_db->loadResult();

		// Checking if table is exist then record is exist or not
		if ($fields)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->qn('#__jg_updates'));
			$db->setQuery($query);
			$campaignUpdatesData = $db->loadAssocList();

			if (!empty($campaignUpdatesData))
			{
				foreach ($campaignUpdatesData as $campUpdatedata)
				{
					$updateActivityData = array();
					$updateActivityData['postData'] = $campUpdatedata['title'] . ' ' . $campUpdatedata['description'];
					$updateActivityData['cid'] = $campUpdatedata['campaign_id'];
					$updateActivityData['cdate'] = $campUpdatedata['cdate'];
					$updateActivityData['mdate'] = $campUpdatedata['mdate'];

					// Trigger jgiveactivity plugin to add test activity
					PluginHelper::importPlugin('system');

					Factory::getApplication()->triggerEvent('onPostActivity', array($updateActivityData));
				}
			}

			$query = $db->getQuery(true);
			$query->delete($db->qn('#__jg_updates'));
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Method to migrate all the media data to jg_media_files and _jg_media_file_xref
	 *
	 * @return  INT
	 *
	 * @since   2.0
	 */
	public function migrateMedia()
	{
		$this->migrateImages();
		$this->migrateVideos();
		$this->migrateGivebacks();

		return 1;
	}

	/**
	 * Method to migrate campaign images
	 *
	 * @return  array
	 *
	 * @since   2.1
	 */
	public function migrateImages()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'path', 'campaign_id', 'gallery')));
		$query->from($db->quoteName('#__jg_campaigns_images'));
		$db->setQuery($query);
		$campaignImages = $db->loadAssocList();

		foreach ($campaignImages as $image)
		{
			if (!empty($image['path']))
			{
				$mediaData = array();
				$mediaData['name'] = str_replace("images/jGive/", "", $image['path']);

				$type = explode(".", $mediaData['name']);
				$mediaData['type'] = "image." . end($type);
				$base = empty($image['gallery'])?JPATH_SITE . '/images/jGive/L_':JPATH_SITE . '/images/jGive/';
				$path = $base . str_replace("images/jGive/", "", $image['path']);
				$mediaData['size'] = file_exists($path)?filesize($path):'0';

				if (!file_exists($path))
				{
					continue;
				}

				$mediaData['state'] = 1;
				$mediaData['tmp_name'] = $path;
				$mediaData['upload_type'] = "move";

				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'media');
				$jgiveMediaModel = BaseDatabaseModel::getInstance('Media', 'JgiveModel');

				if ($returnData = $jgiveMediaModel->save($mediaData))
				{
					BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models', 'mediaxref');
					$jtMediaxrefModel = BaseDatabaseModel::getInstance('MediaXref', 'JgiveModel');
					$mediaXref = array();
					$mediaXref['media_id'] = $returnData['id'];
					$mediaXref['client_id'] = $image['campaign_id'];
					$mediaXref['client'] = 'com_jgive.campaign';
					$mediaXref['is_gallery'] = $image['gallery'];

					if ($jtMediaxrefModel->save($mediaXref))
					{
						// Delete data from old tables
						$query = $db->getQuery(true);
						$conditions = array($db->quoteName('id') . ' = ' . $image['id']);
						$query->delete($db->quoteName('#__jg_campaigns_images'));
						$query->where($conditions);
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}
	}

	/**
	 * Method to migrate campaign videos
	 *
	 * @return  array
	 *
	 * @since   2.1
	 */
	public function migrateVideos()
	{
		$checkTableExist = Factory::getConfig()->get('dbprefix') . 'jg_campaigns_media';
		$tables          = Factory::getDbo()->getTableList();

		if (in_array($checkTableExist, $tables))
		{
			// Migrate Videos
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id', 'type', 'url', 'path', 'content_id', )));
			$query->from($db->quoteName('#__jg_campaigns_media'));
			$db->setQuery($query);
			$campaignVideos = $db->loadAssocList();

			foreach ($campaignVideos as $video)
			{
				if (empty($video['path']) && empty($video['url']))
				{
					continue;
				}

				$mediaData = array();

				if (!empty($video['path']))
				{
					$mediaData['name'] = str_replace('/media/com_jgive/videos/', '', $video['path']);
					$type = explode(".", $mediaData['name']);
					$mediaData['type'] = "video." . end($type);
					$path = JPATH_SITE . $video['path'];
					$mediaData['size'] = file_exists($path)?filesize($path):'0';
					$mediaData['state'] = 1;
					$mediaData['tmp_name'] = $path;
					$mediaData['upload_type'] = "move";
				}
				elseif (!empty($video['url']))
				{
					$mediaData['name'] = $video['url'];
					$mediaData['type'] = $video['type'];
					$mediaData['state'] = 1;
					$mediaData['tmp_name'] = $video['url'];
					$mediaData['upload_type'] = "link";
				}

				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'media');
				$jgiveMediaModel = BaseDatabaseModel::getInstance('Media', 'JgiveModel');

				if ($returnData = $jgiveMediaModel->save($mediaData))
				{
					BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models', 'mediaxref');
					$jtMediaxrefModel = BaseDatabaseModel::getInstance('MediaXref', 'JgiveModel');
					$mediaXref = array();
					$mediaXref['media_id'] = $returnData['id'];
					$mediaXref['client_id'] = $video['content_id'];
					$mediaXref['client'] = 'com_jgive.campaign';
					$mediaXref['is_gallery'] = 1;

					if ($jtMediaxrefModel->save($mediaXref))
					{
						// Delete data from old tables
						$query = $db->getQuery(true);
						$conditions = array($db->quoteName('id') . ' = ' . $video['id']);
						$query->delete($db->quoteName('#__jg_campaigns_media'));
						$query->where($conditions);
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}
	}

	/**
	 * Method to migrate Campaigns Updates into Activity
	 *
	 * @return  INT
	 *
	 * @since   2.0
	 */
	public function migrateActivities()
	{
		$allActivities = $this->getAllActivities();

		// Load JGive activity plugin helper
		JLoader::import('plugins.system.jgiveactivities.helper', JPATH_SITE);
		$plgSystemJgiveActivitiesHelper = new PlgSystemJgiveActivitiesHelper;

		// Load JGive frontend helper
		JLoader::import('components.com_jgive.helper', JPATH_SITE);
		$jGiveFrontendHelper = new JgiveFrontendHelper;

		foreach ($allActivities as $activity)
		{
			// Load single activity data
			$db = Factory::getDbo();
			$activityStreamActivityTable = Table::getInstance('Activity', 'ActivityStreamTable', array('dbo', $db));
			$activityStreamActivityTable->load(array('id' => $activity->id));

			// Replace actor URLS
			$user = Factory::getUser($activity->actor_id);
			$actorData = $plgSystemJgiveActivitiesHelper->getActorData($user);

			if ($activityStreamActivityTable->type == 'jgive.donation')
			{
				if ($activityStreamActivityTable->template == 'anonymousdonation.mustache')
				{
					$avatarData = json_decode($actorData['image']);
					$avatarData->avatar = 'media/com_jgive/images/default_avatar.png';
					$avatarData->gravatar = null;
					$actorData['image'] = json_encode($avatarData);
				}
			}

			$activityStreamActivityTable->actor = json_encode($actorData);

			// Replace object URLS
			$objectData = json_decode($activityStreamActivityTable->object);

			switch ($activityStreamActivityTable->type)
			{
				case 'campaign.addimage':
					$objectData = $this->migrateAddImageActivities($objectData);
				break;

				case 'jgive.addcampaign':
					$objectData->url = $jGiveFrontendHelper->getCampaignUrl($objectData->id, true);
				break;

				case 'campaign.completed':
					$objectData->url = $jGiveFrontendHelper->getCampaignUrl($objectData->id, true);
				break;

				case 'campaign.extended':
					$objectData->url = $jGiveFrontendHelper->getCampaignUrl($activityStreamActivityTable->object_id, true);
				break;

				case 'campaign.addvideo':
					$objectData = $this->migrateAddVideoActivities($objectData);
				break;

				case 'campaign.addgiveback':
					$objectData = $this->migrateAddGiveBackActivities($objectData);
				break;
			}

			$activityStreamActivityTable->object = json_encode($objectData);

			// Replace target URLS
			$targetData = json_decode($activityStreamActivityTable->target);
			$targetData->url = $jGiveFrontendHelper->getCampaignUrl($targetData->id, true);
			$activityStreamActivityTable->target = json_encode($targetData);

			// Update activity data
			$activityStreamActivityTable->store();
		}

		return 1;
	}

	/**
	 * Method to migrate campaign givebacks
	 *
	 * @return  array
	 *
	 * @since   2.1
	 */
	public function migrateGivebacks()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'image_path', 'campaign_id')));
		$query->from($db->quoteName('#__jg_campaigns_givebacks'));
		$db->setQuery($query);
		$giveBacks = $db->loadAssocList();

		foreach ($giveBacks as $giveBack)
		{
			if (!empty($giveBack['image_path']))
			{
				$mediaData = array();
				$mediaData['name'] = str_replace("images/jGive/", "", $giveBack['image_path']);
				$type = explode(".", $mediaData['name']);
				$mediaData['type'] = "image." . $type[1];
				$path = JPATH_SITE . "/" . $giveBack['image_path'];
				$mediaData['size'] = file_exists($path)?filesize($path):'0';
				$mediaData['state'] = 1;
				$mediaData['tmp_name'] = $path;
				$mediaData['upload_type'] = "move";

				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'media');
				$jgiveMediaModel = BaseDatabaseModel::getInstance('Media', 'JgiveModel');

				if ($returnData = $jgiveMediaModel->save($mediaData))
				{
					BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models', 'mediaxref');
					$jtMediaxrefModel = BaseDatabaseModel::getInstance('MediaXref', 'JgiveModel');
					$mediaXref = array();
					$mediaXref['media_id'] = $returnData['id'];
					$mediaXref['client_id'] = $giveBack['campaign_id'];
					$mediaXref['client'] = 'com_jgive.givebacks.' . $giveBack['id'];

					if ($jtMediaxrefModel->save($mediaXref))
					{
						$giveBackTable = Table::getInstance('GiveBacks', 'JGiveTable', array('dbo', $db));
						$giveBackTable->load(array('id' => $giveBack['id']));
						$giveBackTable->image_path = '';
						$giveBackTable->store();
					}
				}
			}
		}
	}

	/**
	 * Method to migrate add image activities
	 *
	 * @param   OBJECT  $objectData  Activity object data
	 *
	 * @return  OBJECT
	 *
	 * @since   2.1
	 */
	private function migrateAddImageActivities($objectData)
	{
		$images = json_decode($objectData->url);
		$updatedUrls = array();
		$db = Factory::getDbo();

		foreach ($images as $image)
		{
			$jGiveMediaTable = Table::getInstance('Media', 'JGiveTable', array('dbo', $db));
			$result = explode("/", $image);
			$originalName = end($result);
			$jGiveMediaTable->load(array('original_filename' => $originalName));

			if (!empty($jGiveMediaTable->id))
			{
				$updatedUrls[] = $jGiveMediaTable->path . "/images/" . "M_" . $jGiveMediaTable->source;
			}
		}

		if (!empty($updatedUrls))
		{
			$objectData->count = count($updatedUrls);
			$objectData->url = json_encode($updatedUrls);
		}

		return $objectData;
	}

	/**
	 * Method to migrate add video activities
	 *
	 * @param   OBJECT  $objectData  Activity object data
	 *
	 * @return  OBJECT
	 *
	 * @since   2.1
	 */
	private function migrateAddVideoActivities($objectData)
	{
		$db = Factory::getDbo();
		$videos = json_decode($objectData->videos);
		$updatedVideoData = array();

		// Load media helper
		JLoader::import('media', JPATH_SITE . '/components/com_jgive/helpers');
		$jgiveMediaHelper = new jgiveMediaHelper;

		foreach ($videos as $video)
		{
			$video->external = true;

			$type = 'video';

			if ((strpos($video->url, 'https://www.youtube.com/') !== false))
			{
				$type = 'youtube';
			}
			elseif ((strpos($video->url, 'https://player.vimeo.com') !== false))
			{
				$type = 'vimeo';
			}

			$jGiveMediaTable = Table::getInstance('Media', 'JGiveTable', array('dbo', $db));

			if ($type == 'video')
			{
				$result = explode("/", $video->url);
				$originalName = end($result);
				$jGiveMediaTable->load(array('original_filename' => $originalName));

				$video->external = false;
				$video->thumbSrc = "media/com_jgive/images/no_thumb.png";
				$video->url = $jGiveMediaTable->path . "/videos/" . $jGiveMediaTable->source;
			}
			else
			{
				$explodedUrl = explode('/', $video->url);

				if (!empty($explodedUrl))
				{
					$videoId = end($explodedUrl);
				}

				if (!empty($videoId))
				{
					$video->thumbSrc = $jgiveMediaHelper->videoThumbnail($type, $videoId);
				}

				$jGiveMediaTable->load(array('original_filename' => $video->url));
			}

			$base = 'index.php?option=com_jgive&view=campaign&layout=default_playvideo&vid=' . $jGiveMediaTable->id . '&type=';
			$video->link = $base . $type . '&tmpl=component';
			$video->playIcon = 'media/com_jgive/images/play_icon.png';

			$updatedVideoData[] = $video;
		}

		if (count($updatedVideoData))
		{
			$objectData->videos = json_encode($updatedVideoData);
		}

		return $objectData;
	}

	/**
	 * Method to migrate add give back activities
	 *
	 * @param   OBJECT  $objectData  Activity object data
	 *
	 * @return  OBJECT
	 *
	 * @since   2.1
	 */
	private function migrateAddGiveBackActivities($objectData)
	{
		$giveBacks = json_decode($objectData->url);
		$db = Factory::getDbo();
		$updateGiveBacks = array();

		foreach ($giveBacks as $giveBack)
		{
			$jGiveMediaTable = Table::getInstance('Media', 'JGiveTable', array('dbo', $db));
			$result = explode("/", $giveBack->giveback_image);
			$originalName = end($result);
			$jGiveMediaTable->load(array('original_filename' => $originalName));

			if (!empty($jGiveMediaTable->id))
			{
				$giveBack->giveback_image = $jGiveMediaTable->path . "/images/" . "M_" . $jGiveMediaTable->source;
			}

			$updateGiveBacks[] = $giveBack;
		}

		$objectData->url = json_encode($updateGiveBacks);

		return $objectData;
	}

	/**
	 * Method to get all activities
	 *
	 * @return  OBJECT
	 *
	 * @since   2.1
	 */
	private function getAllActivities()
	{
		// Get activity data for JGive
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__tj_activities'));
		$query->where(
			$db->quoteName('type') . " IN ('jgive.textpost','jgive.addcampaign','jgive.donation','campaign.completed',"
			. "'campaign.extended','campaign.addimage','campaign.addvideo','campaign.addgiveback') "
			);

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/** Add a user as a vendor
	 *
	 * @param   string  $user_id  The user id.
	 *
	 * @return int
	 *
	 * @since 2.1
	 */
	private function addOldVendor($user_id)
	{
		$tjvendorFrontHelper           = new TjvendorFrontHelper;
		$vendorData = array();
		$vendorData['userName']        = Factory::getUser($user_id)->name;
		$vendorData['vendor_client']   = "com_jgive";
		$vendorData['user_id']         = $user_id;
		$vendorData['vendor_title']    = $vendorData['userName'];
		$vendorData['state']           = "1";

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendor');
		$TjvendorsModelVendors = BaseDatabaseModel::getInstance('Vendor', 'TjvendorsModel');
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables', 'vendor');
		$TjvendorsModelVendors->save($vendorData);
		$vendorId = $tjvendorFrontHelper->checkVendor($user_id, 'com_jgive');

		return $vendorId;
	}

	/**
	 * Add vendor id in campaigns table against a campaign
	 *
	 * @return boolean
	 *
	 * @since 2.1
	 */
	private function addOldCampaignVendor()
	{
		$campaignModel       = BaseDatabaseModel::getInstance('Campaigns', 'JGiveModel');
		$oldCampaignData     = $campaignModel->getItems();
		$tjvendorFrontHelper = new TjvendorFrontHelper;

		$params = ComponentHelper::getParams('com_give');
		$directPaymentToOwner = $params->get('send_payments_to_owner');

		// Loop through the old vendor data and insert it in the TJVendors table
		foreach ($oldCampaignData as $campaignData)
		{
			$vendorCheck = $tjvendorFrontHelper->checkVendor($campaignData->creator_id, 'com_jgive');

			if (empty($vendorCheck))
			{
				// Check if the campaign has paypal email attached to it
				if (!empty($campaignData->paypal_email))
				{
					$vendorId = $this->addOldVendor($campaignData->creator_id);

					if ($directPaymentToOwner == 1)
					{
						$payment_gateway = "paypal";
					}
					else
					{
						$payment_gateway = "adaptive_paypal";
					}

					$param1 = new stdClass;
					$param1->payment_gateway = $payment_gateway;
					$gatewayDetails = array("payment_gateway" => $payment_gateway, "payment_email_id" => $campaignData->paypal_email);

					$params = (object) array_merge((array) $param1, $gatewayDetails);

					$paymentArray = array();
					$paymentArray['payment_gateway0'] = $params;
					$paymentArrayList['payment_gateway'] = $paymentArray;

					$vendorParams = json_encode($paymentArrayList);

					$vendorData = new stdClass;
					$vendorData->vendor_id = $vendorId;
					$vendorData->params    = $vendorParams;

					Factory::getDbo()->updateObject('#__vendor_client_xref', $vendorData, 'vendor_id');
				}
				else
				{
					$vendorId = $this->addOldVendor($campaignData->creator_id);
				}

				$newcampaignData = new stdClass;
				$newcampaignData->vendor_id = $vendorId;
				$newcampaignData->id = $campaignData->id;

				// Insert the object into the campaigns table.
				Factory::getDbo()->updateObject('#__jg_campaigns', $newcampaignData, 'id');
			}
			else
			{
				$vendorId = $vendorCheck;

				if (!empty($campaignData->paypal_email))
				{
					$params               = ComponentHelper::getParams('com_jgive');
					$directPaymentToOwner = $params->get('send_payments_to_owner');

					if ($directPaymentToOwner == 1)
					{
						$payment_gateway = "paypal";
					}
					else
					{
						$payment_gateway = "adaptive_paypal";
					}

					$param1 = new stdClass;
					$param1->payment_gateway = $payment_gateway;
					$gatewayDetails = array("payment_gateway" => $payment_gateway, "payment_email_id" => $campaignData->paypal_email);

					$params = (object) array_merge((array) $param1, $gatewayDetails);

					$paymentArray = array();
					$paymentArray['payment_gateway0'] = $params;
					$paymentArrayList['payment_gateway'] = $paymentArray;

					$vendorParams = json_encode($paymentArrayList);

					$vendorData = new stdClass;
					$vendorData->vendor_id = $vendorId;
					$vendorData->params    = $vendorParams;

					Factory::getDbo()->updateObject('#__vendor_client_xref', $vendorData, 'vendor_id');
				}

				$newcampaignData            = new stdClass;
				$newcampaignData->vendor_id = $vendorId;
				$newcampaignData->id        = $campaignData->id;

				// Insert the object into the user integration table.
				Factory::getDbo()->updateObject('#__jg_campaigns', $newcampaignData, 'id');
			}
		}

		return true;
	}

	/**
	 * get old orders data
	 *
	 * @return object|boolean
	 *
	 * @since 2.1
	 */
	private function getOldData()
	{
		$com_params = ComponentHelper::getParams('com_give');
		$handle_transactions = $com_params->get('send_payments_to_owner', 0);

		if ($handle_transactions)
		{
			return false;
		}
		else
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__jg_orders'));
			$query->where($db->quoteName('status') . ' = ' . $db->quote('C'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
	}

	/**
	 * get old payouts data
	 *
	 * @return object
	 *
	 * @since 2.1
	 */
	private function getOldPayoutData()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jg_payouts'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * add credit entries in the passbook table
	 *
	 * @param   object  $data  The credit data of tickets.
	 *
	 * @return void|boolean
	 *
	 * @since 2.1
	 */
	private function addCreditData($data)
	{
		$data->client        = "com_jgive";
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$campaignModel       = BaseDatabaseModel::getInstance('Campaign', 'JGiveModel');
		$CampaignData        = $campaignModel->getItem($data->campaign_id);

		if (empty($data))
		{
			return false;
		}

		$vendorCheck = $tjvendorFrontHelper->checkVendor($CampaignData->creator_id, 'com_jgive');

		if (!$vendorCheck)
		{
			$vendor_id = $this->addOldVendor($data->userid);
		}
		else
		{
			$vendor_id = $vendorCheck;
		}

		$entry_data                       = array();
		$com_params                       = ComponentHelper::getParams($data->client);
		$currency                         = $com_params->get('currency');
		$entry_data['vendor_id']          = $vendor_id;
		$totalAmount                      = TjvendorsHelper::getTotalAmount($entry_data['vendor_id'], $currency, 'com_jgive');
		$entry_data['reference_order_id'] = $data->order_id;
		$transactionClient                = Text::_('COM_JGIVE');

		$entry_data['transaction_id']   = $transactionClient . '-' . $currency . '-' . $entry_data['vendor_id'] . '-';
		$entry_data['transaction_time'] = $data->cdate;
		$entry_data['credit']           = $data->amount - $data->fee;
		$entry_data['total']            = $totalAmount['total'] + $entry_data['credit'];
		$entry_data['debit']            = 0;
		$entry_status                   = "credit_for_ticket_buy";
		$params                         = array("customer_note" => "","entry_status" => $entry_status);
		$entry_data['params']           = json_encode($params);
		$entry_data['currency']         = $currency;
		$entry_data['client']           = $data->client;

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'payout');
		$TjvendorsModelPayout = BaseDatabaseModel::getInstance('Payout', 'TjvendorsModel');
		$TjvendorsModelPayout->addCreditEntry($entry_data);
	}

	/**
	 * remove old payouts data
	 *
	 * @param   object  $payoutData  The payout data.
	 *
	 * @return void
	 *
	 * @since 2.1
	 */
	private function removeOldPayoutData($payoutData)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('id') . ' = ' . $payoutData->id,
		);

		$query->delete($db->quoteName('#__jg_payouts'));
		$query->where($conditions);

		$db->setQuery($query);

		$db->execute();
	}

	/**
	 * add debit entries in the passbook table
	 *
	 * @param   object  $payoutData  The payout old data.
	 *
	 * @return void
	 *
	 * @since 2.1
	 */
	private function addPayoutData($payoutData)
	{
		$com_params          = ComponentHelper::getParams('com_jgive');
		$currency            = $com_params->get('currency');
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$vendorCheck         = $tjvendorFrontHelper->checkVendor($payoutData->user_id, 'com_jgive');

		if (!$vendorCheck)
		{
			$vendor_id = $this->addOldVendor($payoutData->user_id);
		}
		else
		{
			$vendor_id = $vendorCheck;
		}

		$newPayoutData = new stdClass;
		$newPayoutData->debit            = $payoutData->amount;
		$payableAmount                   = TjvendorsHelper::getTotalAmount($vendor_id, $currency, 'com_jgive');
		$newPayoutData->total            = $payableAmount['total'] - $newPayoutData->debit;
		$newPayoutData->transaction_time = $payoutData->date;
		$newPayoutData->client           = 'com_jgive';
		$newPayoutData->currency         = $currency;
		$transactionClient               = "JGive";
		$newPayoutData->transaction_id   = $transactionClient . '-' . $currency . '-' . $vendor_id . '-';
		$newPayoutData->id               = '';
		$newPayoutData->vendor_id        = $vendor_id;
		$newPayoutData->status           = $payoutData->status;
		$newPayoutData->credit           = '0.00';
		$params                          = array("customer_note" => "", "entry_status" => "debit_payout");
		$newPayoutData->params           = json_encode($params);

		// Insert the object into the user passbook table.
		Factory::getDbo()->insertObject('#__tjvendors_passbook', $newPayoutData);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('max(' . $db->quotename('id') . ')');
		$query->from($db->quoteName('#__tjvendors_passbook'));
		$db->setQuery($query);

		$payout_id     = $db->loadResult();
		$payout_update = new stdClass;

		// Must be a valid primary key value.
		$payout_update->id             = $payout_id;
		$payout_update->transaction_id = $newPayoutData->transaction_id . $payout_update->id;

		// Update their details in the passbook table using id as the primary key.
		Factory::getDbo()->updateObject('#__tjvendors_passbook', $payout_update, 'id');
	}

	/**
	 * format the payout data according to date
	 *
	 * @param   object|boolean  $oldCreditData  The credit data of tickets.
	 *
	 * @param   object|boolean  $oldPayoutData  The debit data of payouts.
	 *
	 * @return  boolean
	 *
	 * @since 2.1
	 */
	private function formatPayoutData($oldCreditData, $oldPayoutData)
	{
		$dataSize = sizeof($oldCreditData, 0);
		$count = 0;

		foreach ($oldCreditData as $data)
		{
			$count++;

			foreach ($oldPayoutData as $payoutData)
			{
				$date = new Date($payoutData->date . ' +23 hour +59 minutes');

				if ($date <= $data->cdate)
				{
					$this->addPayoutData($payoutData);
					$this->removeOldPayoutData($payoutData);
				}
			}

			$this->addCreditData($data);

			if ($dataSize == $count)
			{
				foreach ($oldPayoutData as $payoutData)
				{
					$date = new Date($payoutData->date . ' +23 hour +59 minutes');

					if ($date > $data->cdate)
					{
						$this->addPayoutData($payoutData);
						$this->removeOldPayoutData($payoutData);
					}
				}
			}
		}

		return true;
	}

	/**
	 * transfer credit and debit entries in passbook table of vendors
	 *
	 * @return  boolean
	 *
	 * @since   2.1
	 */
	private function fixPayoutsTable()
	{
		$oldCreditData = $this->getOldData();
		$oldPayoutData = $this->getOldPayoutData();

		if (!empty($oldPayoutData))
		{
			$this->formatPayoutData($oldCreditData, $oldPayoutData);
		}

		return true;
	}

	/**
	 * Migrates vendor data
	 *
	 * @return  int 1
	 *
	 * @since   2.1
	 */
	public function migrateVendorData()
	{
		$this->addOldcampaignVendor();
		$this->fixPayoutsTable();
		$this->migratePassbookData();

		return 1;
	}

	/**
	 * Migrates passbook data
	 *
	 * @return  boolean
	 *
	 * @since   2.1.2
	 */
	public function migratePassbookData()
	{
		$app = Factory::getApplication();
		$reportsModel = BaseDatabaseModel::getInstance('Reports', 'TJVendorsModel');
		$app->setUserState('com_tjvendors.reports.filter.client', "com_jgive");
		$vendorPassbookDetails = $reportsModel->getItems();

		if (!empty($vendorPassbookDetails))
		{
			// Delete invalid debit entries
			$proceedMigration = $this->deleteInvalidEntries();
		}

		// Proceed for migration only if the passbook has invalid entries
		if (!empty($proceedMigration))
		{
			// Once validated for invalid entries then proceed for migration
			$this->proceedMigration();
		}

		return true;
	}

	/**
	 * Proceed to migrate passbook data
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function proceedMigration()
	{
		$app = Factory::getApplication();

		// Get Passbook details
		$reportsModel = BaseDatabaseModel::getInstance('Reports', 'TJVendorsModel');
		$app->setUserState('com_tjvendors.reports.filter.client', "com_jgive");
		$passbookDetails = $reportsModel->getItems();

		$vendorsArray    = array();

		// Loop through passbook details to get all vendors against which there is an entry
		foreach ($passbookDetails as $passbookDetail)
		{
			$vendorsArray[] = $passbookDetail->vendor_id;
		}

		// To avoid duplicate vendor_id
		$VendorIds = array_unique($vendorsArray);

		// Loop through all the vendors
		foreach ($VendorIds as $vendorId)
		{
			// Set condition to get passbook details specific to a vendor.
			$reportsModel = BaseDatabaseModel::getInstance('Reports', 'TJVendorsModel');
			$app->setUserState('com_tjvendors.reports.filter.client', "com_jgive");
			$app->setUserState('com_tjvendors.reports.filter.vendor_id', $vendorId);
			$vendorPassbookDetails = $reportsModel->getItems();

			// Loop through the specific vendor's passbook details
			foreach ($vendorPassbookDetails as $key => $passbookDetail)
			{
				$this->updatePassbookData($passbookDetail, $vendorId, $key);
			}
		}
	}

	/**
	 * Update passbook data
	 *
	 * @param   object   $passbookDetail  passbook details of a vendor
	 *
	 * @param   integer  $vendorId        vendor's id
	 *
	 * @param   integer  $key             key at which update has to be done
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function updatePassbookData($passbookDetail, $vendorId, $key)
	{
		// Check for vendor_id and client
		if ($passbookDetail->vendor_id == $vendorId && $passbookDetail->client == "com_jgive")
		{
			// This is done o check if the first entry has the correct total amount if not then credit amount is set to total amount
			$this->updateFirstInvlaidRecord($passbookDetail, $key);

			// Enter only when the entry is not first because the first entry does not need updated total amount
			$this->updateInvalidRecords($passbookDetail, $key, $vendorId);
		}
	}

	/**
	 * Update all invalid records
	 *
	 * @param   object   $passbookDetail  passbook details of a vendor
	 *
	 * @param   integer  $key             key to which update has to be done
	 *
	 * @param   integer  $vendorId        vendor's id on which update has to take place
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function updateInvalidRecords($passbookDetail, $key, $vendorId)
	{
		$total = 0;

		$app = Factory::getApplication();

		if ($key > 0)
		{
			// Get the previous record's key
			$previouskey  = $key - 1;

			// Get the updated records after the total amount is updated
			$reportsModel = BaseDatabaseModel::getInstance('Reports', 'TJVendorsModel');
			$app->setUserState('com_tjvendors.reports.filter.client', "com_jgive");
			$app->setUserState('com_tjvendors.reports.filter.vendor_id', $vendorId);
			$updatedvendorPassbookDetails = $reportsModel->getItems();

			// Get the previous record of the current record to use its total amount
			$previousPassbookDetails = $updatedvendorPassbookDetails[$previouskey];

			$passbookUpdateDetails     = new stdClass;
			$passbookUpdateDetails->id = $passbookDetail->id;

			$status = json_decode($passbookDetail->params)->entry_status;

			// If its a confirmed entry
			if ($status == "credit_for_ticket_buy")
			{
				$total = $previousPassbookDetails->total + $passbookDetail->credit;
			}

			// If the entry is pending or debit or a payout entry
			if ($status == "debit_payout" || $status == "debit_pending" || $status == "debit_refund")
			{
				$total = $previousPassbookDetails->total - $passbookDetail->debit;
			}

			// Total amount to be updated for current record
			$passbookUpdateDetails->total = $total;

			try
			{
				// Update their details in the passbook table using id as the primary key.
				Factory::getDbo()->updateObject('#__tjvendors_passbook', $passbookUpdateDetails, 'id');
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_JGIVE_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
			}
		}
	}

	/**
	 * Update first invalid record
	 *
	 * @param   object   $passbookDetail  passbook details of a vendor
	 *
	 * @param   integer  $key             key at which the record has to be updated
	 *
	 * @return  void
	 *
	 * @since   2.1.2
	 */
	public function updateFirstInvlaidRecord($passbookDetail, $key)
	{
		if ($key == 0 && $passbookDetail->total <= 0)
		{
			$firstTotalUpdate = new stdClass;
			$firstTotalUpdate->total = $passbookDetail->credit;
			$firstTotalUpdate->id    = $passbookDetail->id;

			try
			{
				// Update their details in the passbook table using id as the primary key.
				Factory::getDbo()->updateObject('#__tjvendors_passbook', $firstTotalUpdate, 'id');
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_JGIVE_DB_EXCEPTION_WARNING_MESSAGE'), 'error');
			}
		}
	}

	/**
	 * Delete Invalid Passbook Entries
	 *
	 * @return  Integer
	 *
	 * @since   2.1.2
	 */
	public function deleteInvalidEntries()
	{
		$app         = Factory::getApplication();
		$db          = Factory::getDbo();
		$deleteCount = 0;

		$tjvendorsTable = Table::getInstance('Payout', 'TJVendorsTable', array('dbo', $db));

		$reportsModel = BaseDatabaseModel::getInstance('Reports', 'TJVendorsModel');
		$app->setUserState('com_tjvendors.reports.filter.client', "com_jgive");
		$vendorPassbookDetails = $reportsModel->getItems();

		// Loop through the passbook details to indentify if its an invalid record
		foreach ($vendorPassbookDetails as $passbookDetail)
		{
			$status = json_decode($passbookDetail->params)->entry_status;

			// Check if the record is with a debit_pending status
			if ($status == 'debit_pending' && is_numeric($passbookDetail->reference_order_id))
			{
				// Increment the delete count and delete the current record.
				$deleteCount ++;
				$tjvendorsTable->delete($passbookDetail->id);
			}
		}

		return $deleteCount;
	}
}
