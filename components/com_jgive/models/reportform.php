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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;

JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);

/**
 * Reports form model class
 *
 * @package  JGive
 * @since    2.2.0
 */
class JgiveModelReportform extends AdminModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		$config['event_after_delete'] = 'onAfterJGReportDelete';

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
	 * @since   2.2.0
	 */
	public function getTable($type = 'Report', $prefix = 'JGiveTable', $config = array())
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
	 * @since   2.2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_jgive.report', 'report',
			array(
				'control'   => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 * As this form is for add, we're not prefilling the form with an existing record
	 * But if the user has previously hit submit and the validation has found an error,
	 *   then we inject what was previously entered.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   2.2.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_jgive.edit.reportform.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to save a campaign data.
	 *
	 * @param   array  $data  data
	 *
	 * @return  integer|boolean
	 *
	 * @since    2.2.0
	 */
	public function save($data)
	{
		$user        = Factory::getUser();
		$addActivity = false;

		if (!isset($data['id']))
		{
			$addActivity = true;
		}
		else
		{
			// Allow to user to edit own created report only
			$reportData = $this->getItem($data['id']);

			if ($reportData->created_by != $data['created_by'] || $reportData->campaign_id != $data['campaign_id'])
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_REQUES'));

				return false;
			}
		}

		$data['status']      = 1;
		$data['created_by']  = (int) $user->id;
		$data['created_on']  = Factory::getDate()->toSql();
		$data['campaign_id'] = (int) $data['campaign_id'];
		$attachments         = $data['attachments'];

		if (parent::save($data))
		{
			$data['id']             = (int) $this->getState($this->getName() . '.id');
			$data['creator_id']     = (int) $user->id;
			JLoader::register('JGiveModelCampaignForm', JPATH_SITE . '/components/com_jgive/models/campaignform.php');
			$modelCampaignForm      = BaseDatabaseModel::getInstance('CampaignForm', 'JGiveModel');
			$campaignData           = $modelCampaignForm->getItem($data['campaign_id']);
			$data['campaign_title'] = $campaignData->title;
			PluginHelper::importPlugin('actionlog');

			if ($addActivity)
			{
				Factory::getApplication()->triggerEvent('onAfterJGReportCreate', array($data));
				Factory::getApplication()->triggerEvent('onAfterJGReportSave', array($data, true));
			}
			else
			{
				Factory::getApplication()->triggerEvent('onAfterJGReportSave', array($data, false));
			}

			if ($data['media_id'] > 0)
			{
				$mediaIdOld = (int) $data['media_id_old'];
				$params   = ComponentHelper::getParams('com_jgive');
				$storagePath = $params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

				if ($mediaIdOld > 0)
				{
					JLoader::register('JGiveModelMedia', JPATH_SITE . '/components/com_jgive/models/media.php');
					$modelMedia      = BaseDatabaseModel::getInstance('Media', 'JGiveModel');
					$modelMedia->deleteMedia($mediaIdOld, $storagePath, 'com_jgive.reports', $data['id']);
				}

				$modelCampaignForm->saveMedia($data['media_id'], 0, $data['id'], 'com_jgive.reports');
			}

			// Store attachment xref values
			if (!empty($attachments))
			{
				$reportId = (int) $this->getState($this->getName() . '.id');

				foreach ($attachments as $mediaId)
				{
					if ($mediaId)
					{
						$mediaXref = array();
						$mediaXref['id'] = '';
						$mediaXref['client_id'] = $reportId;
						$mediaXref['media_id'] = $mediaId;
						$mediaXref['is_gallery'] = 0;
						$mediaXref['client'] = 'com_jgive.reportAttachment';

						$mediaModelXref = TJMediaXref::getInstance($mediaXref['id']);
						$mediaModelXref->bind($mediaXref);
						$mediaModelXref->save();
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @param   Integer  $pk  The primary key
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   2.2.0
	 */
	public function getItem($pk = null)
	{
		$item = (object) parent::getItem($pk);

		if (!empty($item))
		{
			JLoader::register('JGiveModelCampaignForm', JPATH_SITE . '/components/com_jgive/models/campaignform.php');
			$modelCampaignForm  = BaseDatabaseModel::getInstance('CampaignForm', 'JGiveModel');
			$campaignData       = $modelCampaignForm->getItem($item->campaign_id);
			$jgiveParams        = ComponentHelper::getParams('com_jgive');
			$reportImagePath    = $jgiveParams->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

			if (!empty($campaignData))
			{
				$item->created_by = $campaignData->creator_id;
			}

			JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
			JLoader::register('JGiveModelMediaXref', JPATH_SITE . '/components/com_jgive/models/mediaxref.php');
			$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');
			$mediaXrefData  = (object) $modelMediaXref->getMedia($item->get('id'), 'com_jgive.reports', 0, false);

			if (isset($mediaXrefData->media_id))
			{
				$filetable      = Table::getInstance('Files', 'TJMediaTable');
				$filetable->load($mediaXrefData->media_id);
				$mediaType       = explode(".", $filetable->type);
				$imgPath         = $reportImagePath . '/' . $mediaType[0] . 's';
				$mediaConfig     = array('id' => $mediaXrefData->media_id, 'uploadPath' => $imgPath);
				$item->mediaData = TJMediaStorageLocal::getInstance($mediaConfig);
			}

			// Fetching Report Attachments
			$mediaXrefAttachmentData  = (object) $modelMediaXref->getMedia($item->get('id'), 'com_jgive.reportAttachment', 0, true);

			if (!empty($mediaXrefAttachmentData))
			{
				foreach ($mediaXrefAttachmentData as $attachmentData)
				{
					if (isset($attachmentData->media_id))
					{
						$mediaId                     = (int) $attachmentData->media_id;
						JLoader::register('JGiveModelMedia', JPATH_SITE . '/components/com_jgive/models/media.php');
						$modelMedia                  = BaseDatabaseModel::getInstance('Media', 'JGiveModel');
						$mediaAttachmentData         = $modelMedia->getItem($mediaId);
						$item->mediaAttachmentData[] = $mediaAttachmentData;
					}
				}
			}
		}

		return $item;
	}

	/**
	 * Function to delete the report attachment
	 *
	 * @param   Integer  $cid  Campaign Id
	 * @param   Integer  $id   Report Id
	 * @param   Integer  $mid  Media Id
	 *
	 * @return  array   $result
	 *
	 * @since  2.2.0
	 */
	public function deleteAttachment($cid = 0, $id = 0, $mid = 0)
	{
		// Get the current user id
		$jgiveParams = ComponentHelper::getParams('com_jgive');
		$app         = Factory::getApplication();
		$jinput      = $app->input;

		// Report Id
		$clientId = $jinput->get('id', 0, 'INT') ? $jinput->get('id', 0, 'INT'): $id;
		$mediaId  = $jinput->get('mid', 0, 'INT') ? $jinput->get('mid', 0, 'INT'): $mid;
		$result   = array();

		// Check ownership
		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jgive/tables');

		$reportTable = Table::getInstance('report', 'JgiveTable');
		$reportTable->load(array('id' => $clientId));

		$campaignTable = Table::getInstance('campaign', 'JgiveTable');
		$campaignTable->load(array('id' => (int) $reportTable->campaign_id));

		JLoader::register('JGiveModelCampaignForm', JPATH_SITE . '/components/com_jgive/models/campaignform.php');
		$modelCampaignForm  = BaseDatabaseModel::getInstance('CampaignForm', 'JGiveModel');
		$authorised = $modelCampaignForm->checkOwnership($campaignTable->vendor_id, 'delete');

		if (!$mediaId || !$authorised)
		{
			$result['success'] = false;
			$result['message'] = Text::_('COM_JGIVE_REPORTS_ATTACHMENT_DELETION_FAILED');

			return $result;
		}

		JLoader::import("/techjoomla/media/tables/xref", JPATH_LIBRARIES);
		JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
		$tableXref = Table::getInstance('Xref', 'TJMediaTable');
		$filetable = Table::getInstance('Files', 'TJMediaTable');
		$checkMediaDataExist = 0;

		if ($clientId)
		{
			$client              = 'com_jgive.reportAttachment';

			// CheckMediaDataExist will return 1 when media is present clientId is Report Id
			$checkMediaDataExist = $tableXref->load($data = array('client_id' => $clientId, 'client' => $client, 'media_id' => $mediaId));
		}

		// Making file delete path
		$filetable->load($mediaId);
		$mediaType  = explode(".", $filetable->type);
		$deletePath = $jgiveParams->get('report_attachment_upload_path', 'media/com_jgive/campaigns/reports/attachments') . '/' . $mediaType[0] . 's';
		$mediaPresent = $tableXref->load(array('media_id' => $mediaId));

		// If Media is present
		if ($checkMediaDataExist)
		{
			$mediaXrefLib = TJMediaXref::getInstance($config = array('id' => (int) $tableXref->id));

			// If media is not deleted it will return false here
			if (!$mediaXrefLib->delete())
			{
				$result['success'] = false;
				$result['message'] = Text::_($mediaXrefLib->getError());
			}
			// If media is deleted it will return true here
			else
			{
				$xrefMediaPresent = $tableXref->load(array('media_id' => $mediaId));

				if (!$xrefMediaPresent)
				{
					$mediaLib = TJMediaStorageLocal::getInstance($mediaConfig = array('id' => $mediaId, 'uploadPath' => $deletePath));

					if ($mediaLib->id)
					{
						if (!$mediaLib->delete())
						{
							$result['success'] = false;
							$result['message'] = Text::_($mediaLib->getError());

							return $result;
						}
					}
					else
					{
						$result['success'] = false;
						$result['message'] = Text::_($mediaLib->getError());

						return $result;
					}
				}
				else
				{
					$result['success'] = false;
					$result['message'] = Text::_($xrefMediaPresent->getError());

					return $result;
				}

				$result['success'] = true;
				$result['message'] = Text::_('COM_JGIVE_REPORTS_ATTACHMENT_SUCCESSFUL_DELETION');
			}
		}
		elseif (!$mediaPresent)
		{
			$mediaLib = TJMediaStorageLocal::getInstance($mediaConfig = array('id' => $mediaId, 'uploadPath' => $deletePath));

			if ($mediaLib->id)
			{
				if ($mediaLib->delete())
				{
					$result['success'] = true;
					$result['message'] = Text::_('COM_JGIVE_REPORTS_ATTACHMENT_SUCCESSFUL_DELETION');
				}
				else
				{
					$result['success'] = false;
					$result['message'] = Text::_($mediaLib->getError());
				}
			}
		}
		else
		{
			$result['success'] = false;
			$result['message'] = Text::_('COM_JGIVE_REPORTS_ATTACHMENT_DELETION_FAILED');
		}

		return $result;
	}
}
