<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

JLoader::import('integrations', JPATH_SITE . '/components/com_jgive/helpers');
JLoader::import('filterFields', JPATH_SITE . '/components/com_tjfields');
JLoader::import('tjvendors', JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers');
JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
JLoader::import('integrations', JPATH_SITE . '/components/com_jgive/helpers');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jgive/models');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_tjprivacy/models');

Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjprivacy/tables');
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jgive/tables');
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');

include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);

/**
 * JGive Model
 *
 * @since  0.0.1
 */
class JGiveModelCampaignForm extends AdminModel
{
	use TjfieldsFilterField;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		PluginHelper::importPlugin('actionlog');

		$config['event_after_delete'] = 'jgOnAfterCampaignDelete';
		$config['event_change_state'] = 'onAfterJGCampaignChangeState';

		parent::__construct($config);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  boolean|JTable  A JTable object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Campaign', $prefix = 'JGiveTable', $config = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient("administrator"))
		{
			return Table::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');

			return Table::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.

		if (Factory::getApplication()->isClient('administrator'))
		{
			$form = $this->loadForm('com_jgive.campaign', 'campaign', array('control' => 'jform','load_data' => $loadData));
		}
		else
		{
			$form = $this->loadForm('com_jgive.campaignform', 'campaignform', array('control' => 'jform','load_data' => $loadData));
		}

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->id))
			{
				$com_params  = ComponentHelper::getParams('com_jgive');
				$storagePath = $com_params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

				JLoader::import('mediaxref', JPATH_SITE . '/components/com_jgive/models');
				$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');

				JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
				$filetable = Table::getInstance('Files', 'TJMediaTable');

				// Getting give back data
				$giveBacks      = $this->getGiveBacks($item->id);
				$giveBackImage  = $giveBackArray = array();
				$giveBackCount  = 0;

				foreach ($giveBacks as $giveback)
				{
					$givebackMainImage = $modelMediaXref->getCampaignMedia($item->id, 'com_jgive.givebacks.' . $giveback['id'], 0);

					if (!empty($givebackMainImage))
					{
						$filetable->load($givebackMainImage[0]->media_id);
						$mediaType              = explode(".", $filetable->type);
						$imgPath                = $storagePath . '/' . $mediaType[0] . 's';
						$mediaConfig            = array('id' => $givebackMainImage[0]->media_id, 'uploadPath' => $imgPath);
						$giveBackMediaImage     = TJMediaStorageLocal::getInstance($mediaConfig);
						$giveBackImage['image'] = $giveBackMediaImage->media_m;
						$giveBackArray[$giveBackCount] = array_merge($giveback, $giveBackImage);
					}
					else
					{
						$giveBackArray[$giveBackCount] = $giveback;
					}

					$giveBackArray[$giveBackCount]['amount'] = JGive::utilities()->getRoundedAmount($giveback['amount']);

					$giveBackCount++;
				}

				$item->givebacks = $giveBackArray;

				// Getting Beneficiary Stories data
				$beneficiaryStories = $this->getBeneficiaryStories($item->id);
				
				$storyImage  = $storyArray = array();
				$storyCount  = 0;

				foreach ($beneficiaryStories as $story)
				{
					// Fetch the main image for the beneficiary story
					$storyMainImage = $modelMediaXref->getCampaignMedia($item->id, 'com_jgive.beneficiarystories.' . $story['id'], 0);
					if (!empty($storyMainImage))
					{
						// Load media file details
						$filetable->load($storyMainImage[0]->media_id);
						$mediaType            = explode(".", $filetable->type);
						$imgPath              = $storagePath . '/' . $mediaType[0] . 's';
						$mediaConfig          = array('id' => $storyMainImage[0]->media_id, 'uploadPath' => $imgPath);
						$storyMediaImage      = TJMediaStorageLocal::getInstance($mediaConfig);
						$storyImage['image']  = $storyMediaImage->media_m;
						// Combine beneficiary story data with image
						$storyArray[$storyCount] = array_merge($story, $storyImage);
					}
					else
					{
						// Add story data without an image
						$storyArray[$storyCount] = $story;
					}

					$storyCount++;
				}

				// Attach beneficiary stories to the item
				$item->beneficiaryStories = $storyArray;

				// Getting gallery data
				$mediaGallery   = $modelMediaXref->getCampaignMedia($item->id, 'com_jgive.campaign', 1);

				if ($mediaGallery)
				{
					$galleryFiles = array();

					foreach ($mediaGallery as $mediaXref)
					{
						$filetable->load($mediaXref->media_id);
						$mediaType   = explode(".", $filetable->type);
						$imgPath     = $storagePath . '/' . $mediaType[0] . 's';
						$mediaConfig = array('id' => $mediaXref->media_id, 'uploadPath' => $imgPath);
						$galleryFiles[] = TJMediaStorageLocal::getInstance($mediaConfig);
					}

					$item->gallery = $galleryFiles;
				}

				// Getting campaign main image data
				$campaignMainImage = $modelMediaXref->getCampaignMedia($item->id, 'com_jgive.campaign', 0);

				if (!empty($campaignMainImage))
				{
					$filetable->load($campaignMainImage[0]->media_id);
					$mediaType   = explode(".", $filetable->type);
					$imgPath     = $storagePath . '/' . $mediaType[0] . 's';
					$mediaConfig = array('id' => $campaignMainImage[0]->media_id, 'uploadPath' => $imgPath);
					$item->image = TJMediaStorageLocal::getInstance($mediaConfig);
				}

				// Converting date to days if use days config is enabled
				$date       = new Date($item->start_date);
				$daysLimit  = $com_params->get('campaign_period_in_days', '0', 'INT');

				if ($daysLimit)
				{
					$startDate        = new DateTime($item->start_date);
					$endaDate         = new DateTime($item->end_date);
					$item->days_limit = $endaDate->diff($startDate)->format("%a");
				}

				// Assigning value to city in case of other city
				$item->option_city = isset($item->city) ? $item->city : '';

				if (!empty($com_params->get('enable_campaign_terms_conditions', 0)) && !empty($com_params->get('camp_create_terms_article', '0', 'INT')))
				{
					// Getting User privacy data value for campaign
					$userPrivacyTable = Table::getInstance('tj_consent', 'TjprivacyTable', array());
					$userPrivacyData = $userPrivacyTable->load(
													array(
															'client' => 'com_jgive.campaign',
															'client_id' => $item->id ,
															'user_id' => $item->creator_id
														)
												);
					$item->terms_condition = $userPrivacyData;
				}
			}
			else
			{
				$item->creator_id = Factory::getUser()->id;
				$profile_import   = ComponentHelper::getParams('com_jgive')->get('profile_import');

				// If profie import is on the call profile import function
				$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;

				if ($profile_import)
				{
					$profileData = $JgiveIntegrationsHelper->profileImport();

					foreach ($profileData as $data)
					{
						foreach ($data as $key => $profile)
						{
							$item->$key = $profile;
						}
					}
				}
			}
		}

		return $item;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   2.1
	 */
	protected function loadFormData()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Check admin and load admin form in case of admin venue form
		if ($app->isClient('administrator'))
		{
			// Check the session for previously entered form data.
			$data = $app->getUserState('com_jgive.edit.campaign.data', array());
		}
		else
		{
			$data = $app->getUserState('com_jgive.edit.campaignform.data', array());
		}

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get Givebacks data.
	 *
	 * @param   integer  $campaignId  campaign id
	 *
	 * @return  array    $res
	 *
	 * @since    2.1
	 */
	public function getGiveBacks($campaignId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jg_campaigns_givebacks'));

		if (!empty($campaignId))
		{
			$query->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaignId));
		}

		$db->setQuery($query);
		$res = $db->loadAssocList();

		return $res;
	}

	/**
	 * Method to get Beneficiary Stories data.
	 *
	 * @param   integer  $campaignId  campaign id
	 *
	 * @return  array    $res
	 *
	 * @since    2.1
	 */
	public function getBeneficiaryStories($campaignId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jg_campaigns_beneficiary_stories'))
			->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaignId))
			->order($db->quoteName('id') . ' DESC');
		$db->setQuery($query);
		$res = $db->loadAssocList();
		return $res;
	}

	/**
	 * Method to save a campaign data.
	 *
	 * @param   array  $data  data
	 *
	 * @return  integer|boolean
	 *
	 * @since    2.1
	 */
	public function save($data)
	{
		$com_params          = ComponentHelper::getParams('com_jgive');
		$storagePath         = $com_params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');
		$table               = $this->getTable();
		$user                = Factory::getUser();
		$jgiveFrontendHelper = new JgiveFrontendHelper;

		// Bind data
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Generating vendor
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$tjvendorsHelper     = new TjvendorsHelper;

		if (isset($data['creator_id']))
		{
			$creatorId = $data['creator_id'];
		}

		if ($data['id'] != 0)
		{
			$oldCampaignData = $this->getItem($data['id']);
		}

		// Checked if the user is a vendor
		$getVendorId = $tjvendorFrontHelper->checkVendor($creatorId, 'com_jgive');

		// Collecting vendor data
		$vendorData                  = array();
		$vendorData['vendor_client'] = "com_jgive";
		$vendorData['user_id']       = $creatorId;
		$vendorData['vendor_title']  = Factory::getUser($vendorData['user_id'])->name;
		$vendorData['state']         = "1";

		// Collecting payment gateway details
		$paymentDetails                    = array();
		$paymentDetails['payment_gateway'] = '';
		$vendorData['paymentDetails']      = json_encode($paymentDetails);

		$table = Table::getInstance('vendor', 'TJVendorsTable', array());
		$table->load(array('user_id' => $creatorId));

		// Check for vendor's id if not adds a vendor
		if (empty($table->vendor_id))
		{
			$data['vendor_id'] = $tjvendorsHelper->addVendor($vendorData);
		}
		elseif (empty($getVendorId))
		{
			$vendorData['vendor_id'] = $table->vendor_id;
			$data['vendor_id']       = $tjvendorsHelper->addVendor($vendorData);
		}
		else
		{
			$data['vendor_id'] = $getVendorId;
		}

		$data['isNew'] = ($data['id'] ? false : true);

		if ($data['isNew'])
		{
			$data['created'] = Factory::getDate()->toSql();
		}
		else
		{
			$data['modified'] = Factory::getDate()->toSql();
		}

		if (!empty($data['id']))
		{
			$data['isAuthorizedToEdit'] = $this->checkOwnership($data['vendor_id'], 'save');
		}

		// Campaign Publish/Unpublish validation
		if (!in_array(($data['published']), array('1', '0')))
		{
			$data['published'] = 0;
		}

		if (Factory::getApplication()->isClient('site') && ($com_params->get('admin_approval') == 1))
		{
			$data['published'] = 0;
		}

		// If allow to create only one type of campaign
		$campaignTypeConfig = (array) $com_params->get('camp_type', '', 'Array');

		if (count($campaignTypeConfig) == 1 && ($data['type'] != $campaignTypeConfig[0]))
		{
			$data['type'] = $campaignTypeConfig[0];
		}

		// If user is authorise to create or edit campaign.
		if (isset($data['isAuthorizedToEdit']) || ($user->authorise('core.create', 'com_jgive') && empty($data['id'])))
		{
			$data['short_description'] = isset($data['short_description']) ? $data['short_description'] : "";
			$data['group_name'] = isset($data['group_name']) ? $data['group_name'] : "";
			$data['website_address'] = isset($data['website_address']) ? $data['website_address'] : "";
			$data['js_groupid'] = isset($data['js_groupid']) ? $data['js_groupid'] : 0;
			$data['internal_use'] = isset($data['internal_use']) ? $data['internal_use'] : "";

			// Saving campaign data
			if (parent::save($data))
			{
				$id = (int) $this->getState($this->getName() . '.id');

				if ($id)
				{
					$giveBacks = $data['givebacks'];

					$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');
					BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models', 'giveback');

					$giveBacksModel = BaseDatabaseModel::getInstance('giveback', 'JGiveModel');
					$giveBacksArray = array();

					// Getting existing givebacks data
					$existingGiveBacks = $this->getGiveBacks($id);

					$newGiveBack   = array();
					$newAdded      = array();
					$existingId    = array();
					$validCount    = 0;
					$existingCount = 0;

					// Saving Give backs data
					foreach ($giveBacks as $key => $giveBack)
					{
						// If Giveback amount is not filled then remove that giveback from array
						if ($giveBack['amount'] <= 0)
						{
							unset ($giveBacks[$key]);
						}
						else
						{
							if (!empty($giveBack['id']))
							{
								$new = 0;
								$giveBacksArray['id'] = $giveBack['id'];
							}
							else
							{
								$new = 1;
								$giveBacksArray['id'] = "";
							}

							$giveBacksArray['campaign_id'] = $id;
							$giveBacksArray['amount']      = $jgiveFrontendHelper->getRoundedAmount($giveBack['amount']);
							$giveBacksArray['total_quantity'] = $giveBack['total_quantity'];
							$giveBacksArray['description']    = trim($giveBack['description']);
							$giveBacksArray['title']          = trim($giveBack['title']);

							$jgiveTableGiveBacks = Table::getInstance('givebacks', 'JGiveTable', array());
							$jgiveTableGiveBacks->save($giveBacksArray);

							if ($new)
							{
								// Saving the giveback image for new giveback
								if (isset($giveBack['giveback_image']))
								{
									$giveBackClient = "com_jgive.givebacks." . $jgiveTableGiveBacks->id;
									$this->saveMedia($giveBack['giveback_image'], 0, $id, $giveBackClient);
								}

								$giveBacksArray['id'] = $jgiveTableGiveBacks->id;
								$newAdded[] = $giveBacksArray;
							}
							else
							{
								// Saving the giveback image in edit case
								if (isset($giveBack['giveback_image']))
								{
									$giveBackClient = "com_jgive.givebacks." . $jgiveTableGiveBacks->id;
									$mediaxrefTbl = $this->getTable('mediaxref');
									$mediaxrefTbl->load(array('client' => $giveBackClient));

									if ($mediaxrefTbl->id)
									{
										$modelMediaXref->delete($mediaxrefTbl->id);
									}

									$this->saveMedia($giveBack['giveback_image'], 0, $id, $giveBackClient);
								}
							}
						}
					}

					$data['newAddedGiveBacks'] = $newAdded;

					// Collecting existing givebacks.
					if (!empty($existingGiveBacks))
					{
						foreach ($existingGiveBacks as $existingGiveBack)
						{
							$existingId[$existingCount] = $existingGiveBack['id'];
							$existingCount++;

							foreach ($giveBacks as $giveBack)
							{
								if ($giveBack['id'] == $existingGiveBack['id'])
								{
									$newGiveBack[$validCount] = $giveBack['id'];
									$validCount++;
								}
							}
						}

						// Collecting givebacks that should be deleted
						$invalidGiveBackIds = array_diff($existingId, $newGiveBack);
						$data['deletedGiveBacks'] = $invalidGiveBackIds;

						// Deleting the giveBacks
						foreach ($invalidGiveBackIds as $invalidId)
						{
							$giveBackClient = "com_jgive.givebacks." . $invalidId;
							$mediaXrefTable = Table::getInstance('mediaxref', 'JGiveTable', array());
							$mediaXrefTable->load(array('client' => $giveBackClient));

							// Deleting media from media_files table
							$modelMedia = BaseDatabaseModel::getInstance('Media', 'JGiveModel');
							$modelMedia->deleteMedia($mediaXrefTable->media_id, $storagePath, $giveBackClient, $id);

							// Deleting givebacks
							$givebackTable = Table::getInstance('givebacks', 'JGiveTable', array());
							$givebackTable->delete($invalidId);
						}
					}

					// Beneficiary stories
					$beneficiaryStories = $data['beneficiaryStories'];
					$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');
					BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models', 'beneficiarystory');
					$beneficiaryStoriesModel = BaseDatabaseModel::getInstance('beneficiarystory', 'JGiveModel');
					$beneficiaryStoriesArray = array();
					// Getting existing beneficiary stories data
					$existingbeneficiaryStories = $this->getBeneficiaryStories($id);
					$newStory = array();
					$newAdded = array();
					$existingId = array();
					$validCount = 0;
					$existingCount = 0;
					
					// Saving Beneficiary Stories data
					foreach ($beneficiaryStories as $key => $story) {
						// If story title is missing, remove it from the array
						if (empty($story['story_title']))
						{
							unset($beneficiaryStories[$key]);
						}
						else
						{
							if (!empty($story['id'])) {
								$new = 0;
								$beneficiaryStoriesArray['id'] = $story['id'];
							}
							else
							{
								$new = 1;
								$beneficiaryStoriesArray['id'] = "";
							}
					
							$beneficiaryStoriesArray['campaign_id'] = $id;
							$beneficiaryStoriesArray['beneficiary_name'] = trim($story['beneficiary_name']);
							$beneficiaryStoriesArray['beneficiary_position'] = trim($story['beneficiary_position']);
							$beneficiaryStoriesArray['story_title'] = trim($story['story_title']);
							$beneficiaryStoriesArray['story_description'] = trim($story['story_description']);
					
							// Save the story
							$beneficiaryStoriesTable = Table::getInstance('beneficiarystories', 'JGiveTable', array());
							$beneficiaryStoriesTable->save($beneficiaryStoriesArray);
					
							if ($new) {
								// Save the story image for new stories
								if (isset($story['image_url']))
								{
									$storyClient = "com_jgive.beneficiarystories." . $beneficiaryStoriesTable->id;
									$this->saveMedia($story['image_url'], 0, $id, $storyClient);
								}
					
								$beneficiaryStoriesArray['id'] = $beneficiaryStoriesTable->id;
								$newAdded[] = $beneficiaryStoriesArray;
							}
							else
							{
								// Save the story image in edit case
								if (isset($story['image_url'])) {
									$storyClient = "com_jgive.beneficiarystories." . $beneficiaryStoriesTable->id;
									$mediaxrefTbl = $this->getTable('mediaxref');
									$mediaxrefTbl->load(array('client' => $storyClient));
					
									if ($mediaxrefTbl->id) {
										$modelMediaXref->delete($mediaxrefTbl->id);
									}
					
									$this->saveMedia($story['image_url'], 0, $id, $storyClient);
								}
							}
						}
					}
					
					$data['newAddedbeneficiaryStories'] = $newAdded;

					// Collecting existing beneficiary stories
					if (!empty($existingbeneficiaryStories)) {
						foreach ($existingbeneficiaryStories as $existingStory)
						{
							$existingId[$existingCount] = $existingStory['id'];
							$existingCount++;
					
							foreach ($beneficiaryStories as $story)
							{
								if ($story['id'] == $existingStory['id'])
								{
									$newStory[$validCount] = $story['id'];
									$validCount++;
								}
							}
						}
					
						// Collecting beneficiary stories that should be deleted
						$invalidStoryIds = array_diff($existingId, $newStory);
						$data['deletedbeneficiaryStories'] = $invalidStoryIds;
					
						// Deleting the beneficiary stories
						foreach ($invalidStoryIds as $invalidId)
						{
							$storyClient = "com_jgive.beneficiarystories." . $invalidId;
							$mediaXrefTable = Table::getInstance('mediaxref', 'JGiveTable', array());
							$mediaXrefTable->load(array('client' => $storyClient));
					
							// Deleting media from media_files table
							$modelMedia = BaseDatabaseModel::getInstance('Media', 'JGiveModel');
							$modelMedia->deleteMedia($mediaXrefTable->media_id, $storagePath, $storyClient, $id);
					
							// Deleting beneficiary stories
							$storyTable = Table::getInstance('beneficiarystories', 'JGiveTable', array());
							$storyTable->delete($invalidId);
						}
					}
				
					if (!empty($com_params->get('enable_campaign_terms_conditions', 0)) && !empty($com_params->get('camp_create_terms_article', '0', 'INT')))
					{
						// Save User Privacy Terms and conditions Data
						$userPrivacyTable = Table::getInstance('tj_consent', 'TjprivacyTable', array());
						$userPrivacyData = $userPrivacyTable->load(
													array(
															'client' => 'com_jgive.campaign',
															'client_id' => $id ,
															'user_id' => $creatorId?$creatorId:$user->id
														)
												);

						if ($userPrivacyData === false)
						{
							$userPrivacyDataArr = array();
							$userPrivacyDataArr['client'] = 'com_jgive.campaign';
							$userPrivacyDataArr['client_id'] = $id;
							$userPrivacyDataArr['user_id'] = $creatorId?$creatorId:$user->id;
							$userPrivacyDataArr['purpose'] = Text::_('COM_JGIVE_USER_PRIVACY_TERMS_PURPOSE_FOR_CAMPAIGN');
							$userPrivacyDataArr['accepted'] = $data['userPrivacyJformData']['accepted'];
							$userPrivacyDataArr['date'] = Factory::getDate('now')->toSQL();
							$tjprivacyModelObj = BaseDatabaseModel::getInstance('tjprivacy', 'TjprivacyModel');
							$tjprivacyModelObj->save($userPrivacyDataArr);
						}
					}

					// Collecting Campaign images
					if (isset($data['image']['new_image']))
					{
						if (!empty($data['image']['new_image']))
						{
							if ($this->saveMedia($data['image']['new_image'], 0, $id, 'com_jgive.campaign'))
							{
								$mediaxrefTbl = $this->getTable('mediaxref');

								$mediaxrefTbl->load(
									array(
										'media_id' => (int) $data['image']['old_image']
									)
								);

								if ($mediaxrefTbl->id)
								{
									$modelMediaXref->delete($mediaxrefTbl->id);
								}
							}
						}
					}

					// Collecting gallery image data
					if (isset($data['gallery_file']['media']))
					{
						if (!empty($data['gallery_file']['media']))
						{
							$this->saveMedia($data['gallery_file']['media'], 1, $id, 'com_jgive.campaign');
						}
					}

					$campaignDetails = $this->getTable();
					$campaignDetails->load(array('id' => $id));

					/* Send mail on campaign creation */
					JLoader::import('components.com_jgive.events.campaign', JPATH_SITE);
					$jGiveTriggerCampaign = new JGiveTriggerCampaign;

					// Before saving convert the amount as per configured currency
					$data['goal_amount'] = $jgiveFrontendHelper->getRoundedAmount($data['goal_amount']);

					PluginHelper::importPlugin('jgive');
					PluginHelper::importPlugin('actionlog');

					$vendorObj = TJVendors::vendor($campaignDetails->vendor_id, 'com_jgive');
					$campaignDetails->first_name = $vendorObj->getTitle();

					if ($data['id'])
					{
						$jGiveTriggerCampaign->onAfterCampaignSave($campaignDetails, false);
						Factory::getApplication()->triggerEvent('onAfterJGCampaignSave', array($campaignDetails, false));
					}
					else
					{
						$jGiveTriggerCampaign->onAfterCampaignSave($campaignDetails, true);
						Factory::getApplication()->triggerEvent('onAfterJGCampaignSave', array($campaignDetails, true));
					}

					$data['campaignId'] = $id;
					$data['campaignOldData'] = $oldCampaignData;

					PluginHelper::importPlugin('system');

					// Old trigger
					Factory::getApplication()->triggerEvent('onAfterJGCampaignCreate',
						array(
							$data
						)
					);
				}

				return $id;
			}
			else
			{
				return false;
			}
		}

		foreach (array_slice($data['gallery_file']['media'], 1) as $key => $value)
		{
			if ((int) ($value) == 0)
			{
				Factory::getApplication()->enqueueMessage('COM_JGIVE_MEDIA_ERROR', 'error');

				return false;
			}
		}
	}

	/**
	 * Check ownership
	 *
	 * @param   integer  $campaignVendorid  vendor id
	 *
	 * @param   string   $task              like save, delete, publish
	 *
	 * @return boolean
	 *
	 * @since    2.1
	 */
	public function checkOwnership($campaignVendorid, $task)
	{
		$user = Factory::getUser();

		if ($user->authorise('core.admin'))
		{
			return true;
		}

		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$loggedInVendor = $tjvendorFrontHelper->checkVendor('', 'com_jgive');
		$authorise = false;

		if ($task == 'delete')
		{
			$authorise = ($user->authorise('core.delete', 'com_jgive') == 1 ? true : false);
		}
		elseif ($task == 'save')
		{
			$authorise = ($user->authorise('core.edit', 'com_jgive') == 1 ? true : false);
		}

		if ($authorise === true)
		{
			// Check if logged in vendor is owner of the campaign
			if ($loggedInVendor == $campaignVendorid)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Method to call media save function
	 *
	 * @param   INT     $mediaGallery  mediaGallery
	 *
	 * @param   INT     $isGallery     isGallery
	 *
	 * @param   INT     $campaignId    campaign id
	 *
	 * @param   STRING  $mediaClient   client for media
	 *
	 * @return   boolean
	 *
	 * @since    2.1
	 */
	public function saveMedia($mediaGallery, $isGallery, $campaignId, $mediaClient)
	{
		$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');

		$mediaGallery = (array) $mediaGallery;

		// Saving the media as per the client in the media_files_xref table
		foreach ($mediaGallery as $mediaId)
		{
			if ($mediaId)
			{
				$mediaXref               = array();
				$mediaXref['id']         = '';
				$mediaXref['client_id']  = $campaignId;
				$mediaXref['media_id']   = $mediaId;
				$mediaXref['is_gallery'] = $isGallery;
				$mediaXref['client']     = $mediaClient;
				$modelMediaXref->save($mediaXref);
			}
		}

		return true;
	}

	/**
	 * Method to toggle the featured setting of campaigns.
	 *
	 * @param   array    $pks    The ids of the items to toggle.
	 *
	 * @param   integer  $value  The value to toggle to.
	 *
	 * @return  boolean  True on success.
	 */
	public function featured($pks, $value = 0)
	{
		$table = $this->getTable();
		$pks   = (array) $pks;
		$pks   = ArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			$this->setError(Text::_('COM_JGIVE_NO_ITEM_SELECTED'));

			return false;
		}

		$table = $this->getTable('Campaign', 'JGiveTable');

		try
		{
			foreach ($pks as $pk)
			{
				$table           = $this->getTable('campaign');
				$table->featured = (int) $value;
				$table->id       = $pk;

				// Updating the featured value
				$table->store();
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to validate the Campaign form data from server side.
	 *
	 * @param   \JForm  $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   2.3.0
	 */
	public function validate($form, $data, $group = null)
	{
		$app         = Factory::getApplication();
		$params      = ComponentHelper::getParams('com_jgive');
		$return      = true;

		// Check hidden field validation
		$result = $this->campaignHiddenFieldValidations($data);

		if (!empty($data['id']))
		{
			$data['id'] = (int) $data['id'];

			if ($data['id'] == 0)
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_REQUES'));
				$return = false;
			}

			// To check if the hacker trying to edit other proter campaign
			if ($app->isClient('site') && ($this->getItem($data['id'])->creator_id != $data['creator_id']))
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_REQUES'));
				$return = false;
			}
		}

		$campaignPeriodInDays = $params->get('campaign_period_in_days', '0');

		if ($campaignPeriodInDays == '1')
		{
			if (!is_numeric($data['days_limit']))
			{
				$this->setError(Text::_('COM_JGIVE_CAMPAIGNFORM_END_DATE_DAYS_LIMIT_INVALID_INPUT'));
				$return = false;
			}
			elseif ((int) $data['days_limit'] <= 0)
			{
				$this->setError(Text::_('COM_JGIVE_CAMPAIGNFORM_END_DATE_DAYS_LIMIT_ERROR'));
				$return = false;
			}
		}
		else
		{
			if (strtotime($data['end_date']) < strtotime($data['start_date']))
			{
				$this->setError(Text::_('COM_JGIVE_CREATE_CAMPAIGN_DATEVALIDATION_FAIL_MSG'));
				$return = false;
			}
		}

		// Goal Amount
		if ($data['goal_amount'] <= 0)
		{
			$this->setError(Text::_('COM_JGIVE_CAMPAIGNFORM_GOAL_AMOUNT_ERROR'));
			$return = false;
		}

		// Max donor count should not be negative
		if ($data['max_donors'] < 0)
		{
			$this->setError(Text::_('COM_JGIVE_CAMPAIGNFORM_MAX_DONORS_ERROR'));
			$return = false;
		}

		// Minimum donation/investment amount should less than goal amount
		if (($data['minimum_amount'] > 0) && $data['goal_amount'] < $data['minimum_amount'])
		{
			$this->setError(Text::_('COM_JGIVE_GOAL_LESS_MINIMUM_AMT'));
			$return = false;
		}

		if ($data['givebacks'] && count($data['givebacks']) > 0)
		{
			foreach ($data['givebacks'] as $givbackData)
			{
				// Giveback Amount should greater than equal to Minimum donation/investment amount
				if ($givbackData['amount'] < $data['minimum_amount'])
				{
					$this->setError(Text::_('COM_JGIVE_CREATE_CAMPAIGN_GIVEAWAY_FAIL_MSG'));
					$return = false;
				}

				// Giveback Amount should not be zero
				if ($givbackData['amount'] == 0)
				{
					$this->setError(Text::_('COM_JGIVE_CREATE_CAMPAIGN_GIVEAWAY_AMOUNT_VALIDATION_FAIL_MSG'));
					$return = false;
				}

				// Giveback Total Quantity should not be zero
				if ($givbackData['total_quantity'] == 0)
				{
					$this->setError(Text::_('COM_JGIVE_CREATE_CAMPAIGN_GIVEAWAY_QUANTITY_VALIDATION_FAIL_MSG'));
					$return = false;
				}
			}
		}

		$data = parent::validate($form, $data, $group);

		return ($return === true && $result === true) ? $data: false;
	}

	/**
	 * Method to validate the Campaign form hidden field data from server side.
	 *
	 * @param   array  $data  The data to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since 2.3.4
	 */
	public function campaignHiddenFieldValidations($data)
	{
		$return = true;
		$params = ComponentHelper::getParams('com_jgive');
		$showSelectedFields = $params->get('show_selected_fields');
		$creatorField       = $params->get('creatorfield');

		if (isset($showSelectedFields) && !empty($showSelectedFields) && !empty($creatorField))
		{
			// Campaign type field is hidden array
			if (in_array('campaign_type', $creatorField) && ($data['type'] != 'donation'))
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_FIELD') . Text::_('COM_JGIVE_TYPE'));
				$return = false;
			}

			// Allow view donations field is hidden array
			if (in_array('show_public', $creatorField) && ($data['allow_view_donations'] != 0))
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_FIELD') . Text::_('COM_JGIVE_SHOW_DONATIONS_TO'));
				$return = false;
			}

			// Allow exceed donations field is hidden array
			if (in_array('allow_exceed', $creatorField) && ($data['allow_exceed'] != 0))
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_FIELD') . Text::_('COM_JGIVE_ALLOW_DONATIONS_EXCEED'));
				$return = false;
			}

			// Min donations field is hidden array
			if (in_array('min_donation', $creatorField) && ($data['minimum_amount'] != 0))
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_FIELD') . Text::_('COM_JGIVE_MINIMUM_AMOUNT'));
				$return = false;
			}

			// Max donations field is hidden array
			if (in_array('max_donation', $creatorField) && ($data['max_donors'] != 0))
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_FIELD') . Text::_('COM_JGIVE_MAX_DONORS'));
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function publish(&$pks, $value = 1)
	{
		return parent::publish($pks, $value);
	}

	/**
	 * Method to get donor emails from other campaigns in the same category.
	 *
	 * @param   int    $campaignId  ID of the current campaign.
	 *
	 * @return  array               List of unique donor email addresses.
	 *
	 * @since   4.1.0
	 */
	public function getCategoryDonorEmails($campaignId)
	{
		$db = Factory::getDbo();
	
		// Step 1: Get the category_id for the current campaign
		$query = $db->getQuery(true)
			->select($db->quoteName('category_id'))
			->from($db->quoteName('#__jg_campaigns'))
			->where($db->quoteName('id') . ' = ' . (int) $campaignId);
		$db->setQuery($query);
		$categoryId = $db->loadResult();
	
		if (empty($categoryId)) {
			return [];
		}
	
		// Step 2: Get distinct donor emails from other campaigns in the same category
		$query = $db->getQuery(true)
			->select('DISTINCT d.email')
			->from($db->quoteName('#__jg_donors', 'd'))
			->join('INNER', $db->quoteName('#__jg_campaigns', 'c') . ' ON d.campaign_id = c.id')
			->where('c.category_id = ' . (int) $categoryId)
			->where('d.campaign_id != ' . (int) $campaignId)
			->where('d.email IS NOT NULL'); // Extra safe: avoid nulls
		$db->setQuery($query);
		$emails = $db->loadColumn();

		return $emails;
	}

	/**
	 * Method to get pending campaigns the current user hasn't donated to.
	 *
	 * @return  array  List of campaign records (id, title, category_id).
	 *
	 * @since   4.1.0
	 */
	public function getPendingEmailCampaigns()
	{
		$db = Factory::getDbo();
	
		// Get the currently logged-in user
		$user = JFactory::getUser();
		$userId = $user->id;
	
		// Subquery: Get campaign_ids where current user has already donated
		$subQuery = $db->getQuery(true)
			->select('DISTINCT d.campaign_id')
			->from($db->quoteName('#__jg_donors', 'd'))
			->where('d.user_id = ' . (int) $userId);
	
		// Main query: Get all pending campaigns that current user has not donated to
		$query = $db->getQuery(true)
			->select('c.id, c.title, c.category_id')
			->from($db->quoteName('#__jg_campaigns', 'c'))
			->where('c.email_sent = 0')
			->where('c.published = 1')
			->where('c.id NOT IN (' . $subQuery . ')'); // Exclude campaigns where user has donated
	
		$db->setQuery($query);
	
		return $db->loadAssocList();
	}
}
