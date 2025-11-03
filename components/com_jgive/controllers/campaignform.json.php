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
jimport('techjoomla.common');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Response\JsonResponse;

/**
 * Campaign controller class
 *
 * @since  2.1.0
 */
class JGiveControllerCampaignForm extends JGiveController
{
	/**
	 * upload media files and links
	 *
	 * @return JSON
	 *
	 * @since   2.1
	 */
	public function uploadMedia()
	{
		$app        = Factory::getApplication();
		$input      = $app->input;
		$uploadFile = $input->post->get('upload_type', '', 'string');
		$isGallary  = $input->post->get('isGallary', '', 'INT');
		$model      = $this->getModel('Media', 'JGiveModel');
		$returnData = array();

		// Saving a link eg. youtube link
		if ($uploadFile == "link")
		{
			$data                = array();
			$data['name']        = $input->post->get('name', '', 'string');
			$data['type']        = $input->post->get('type', '', 'string');
			$data['upload_type'] = $uploadFile;
			$returnData[0]       = $model->uploadLink($data);

			if ($returnData[0] == false)
			{
				$returnData[0]['valid'] = 0;
				echo new JsonResponse($returnData[0], Text::_('COM_JGIVE_MEDIA_INVALID_URL_TYPE'), true);
				$app->close();
			}
		}
		else
		{
			// Saving an uploaded file
			$files    = $input->files->get('file', '', 'array');
			$fileType = explode("/", $files[0]['type']);

			if (empty($files))
			{
				echo new JsonResponse('', Text::_('COM_JGIVE_MEDIA_INVALID_FILE_TYPE'), true);
			}

			$jgiveParams = ComponentHelper::getParams('com_jgive');
			$storagePath = $jgiveParams->get('jgive_media_upload_path', 'media/com_jgive/campaigns');
			$mediaPath   = JPATH_SITE . '/' . $storagePath . '/' . strtolower($fileType[0] . 's');

			// Image and video specific validation

			if ($isGallary && ($fileType[0] === 'video' || $fileType[0] === 'image'))
			{
				// Saving a video
				$returnData = $model->uploadFile($files, $mediaPath, 1);
			}
			elseif (!$isGallary && $fileType[0] === 'image')
			{
				// Saving an image
				$returnData = $model->uploadFile($files, $mediaPath, 1);
			}
			else
			{
				echo new JsonResponse($returnData, Text::_('COM_JGIVE_MEDIA_INVALID_FILE_TYPE'), true);
			}
		}

		if ($returnData)
		{
			echo new JsonResponse($returnData, Text::_('COM_JGIVE_MEDIA_FILE_UPLOADED'));
		}
	}

	/**
	 * Delete media file
	 *
	 * @return JSON
	 *
	 * @since   2.1
	 */
	public function deleteMedia()
	{
		// Prevent CSRF attack
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		$mediaId  = $this->input->get('id', '0', 'INT');
		$client   = $this->input->get('client', '', 'STRING');
		$clientId = $this->input->get('clientId', '0', 'INT');
		$params   = ComponentHelper::getParams('com_jgive');

		if ($client == 'com_jgive.reportAttachment')
		{
			$storagePath = $params->get('report_attachment_upload_path', 'media/com_jgive/campaigns/reports/attachments');
		}
		else
		{
			$storagePath = $params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');
		}

		if (!$mediaId)
		{
			return false;
		}

		$model = $this->getModel('media');

		// Deleting the media
		$result = $model->deleteMedia($mediaId, $storagePath, $client, $clientId);

		if ($result)
		{
			echo new JsonResponse($result, Text::_('COM_JGIVE_MEDIA_FILE_DELETED'));
		}
		else
		{
			echo new JsonResponse(null, Text::_('COM_JGIVE_MEDIA_DELETION_FAILED'));
		}
	}

	/**
	 * sets default campaign video
	 * called via jquery ajax
	 *
	 * @return  void
	 */
	public function setDefaultMedia()
	{
		$mediaIds = $this->input->get('mediaIds', 'array()', 'array');
		$cid      = $this->input->get('cid', 0, 'INT');
		$model    = $this->getModel('media');
		$model->setDefaultMedia($mediaIds, $cid);
		echo new JsonResponse(1, Text::_('COM_JGIVE_MEDIA_FILE_SET_TO_DEFAULT'));
	}

	/**
	 * Get jomsocial groups
	 *
	 * called via jquery ajax
	 *
	 * @return  void
	 */
	public function getJSGroups()
	{
		$userId = $this->input->get('user', 0, 'INTEGER');
		$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
		$result = $JgiveIntegrationsHelper->getJS_usergroup($userId);

		echo new JsonResponse($result, Text::_('COM_JGIVE_JS_GROUPS'));
	}
}
