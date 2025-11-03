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
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Language\Text;

JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);

/**
 * ReportForm controller class
 *
 * @since  2.2
 */
class JGiveControllerReportForm extends JGiveController
{
	/**
	 * Function to upload file upload files
	 *
	 * @return JSON
	 *
	 * @since   2.2
	 */
	public function uploadFile()
	{
		// CSRF token check
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$user   = Factory::getUser();
		$params = ComponentHelper::getParams('com_jgive');

		// Get the vendor id
		JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$vendorId            = $tjvendorFrontHelper->checkVendor($user->id, 'com_jgive');

		// Check ownership
		$modelCampaignForm = BaseDatabaseModel::getInstance('CampaignForm', 'JGiveModel');
		$authorised        = $modelCampaignForm->checkOwnership($vendorId, 'save');

		$input = Factory::getApplication()->input;

		// Default return message
		$message = Text::_('COM_JGIVE_FILE_ERROR');

		// Default media Id
		$mediaId = 0;

		// Saving an uploaded file
		$file                       = $input->files->get('file', '', 'array');

		if (empty($file))
		{
			echo new JsonResponse('', Text::_('COM_JGIVE_MEDIA_INVALID_FILE_TYPE'), true);
		}

		$fileType                   = explode("/", $file['type']);
		$reportAttachmentUploadPath = $params->get('report_attachment_upload_path', 'media/com_jgive/campaigns/reports/attachments');
		$reportAttachmentUploadPath = JPATH_SITE . '/' . $reportAttachmentUploadPath . '/' . strtolower($fileType[0] . 's');

		if (!empty($file))
		{
			$config               = array();
			$config['title']      = $file['name'];
			$config['uploadPath'] = $reportAttachmentUploadPath;
			$config['access']     = '0';
			$config['state']      = '0';
			$config['size']       = $params->get('max_report_attachment_size', '6');
			$config['auth']       = $authorised;

			$media     = TJMediaStorageLocal::getInstance($config);
			$mediaData = $media->upload(array($file));
			$mediaId   = $mediaData[0]['id'];

			if (!empty($mediaId))
			{
				$message = Text::_('COM_JGIVE_MEDIA_FILE_UPLOADED');
			}
		}

		echo new JsonResponse(array('mediaId' => $mediaId), $message);
	}

	/**
	 * Function to delete the report attachment
	 *
	 * @return  void
	 *
	 * @since  2.2.0
	 */
	public function deleteAttachment()
	{
		// Prevent CSRF attack
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		// Get the current user id
		$userID = Factory::getuser()->id;

		if (!$userID)
		{
			return false;
		}

		$reportformModelObj = $this->getModel('reportform');
		$result             = $reportformModelObj->deleteAttachment();

		echo json_encode($result);
		jexit();
	}

	/**
	 * Function to check the uploaded file mime type
	 *
	 * @return  string
	 *
	 * @since  2.2.0
	 */
	public function checkMimeType()
	{
		$app                   = Factory::getApplication();
		$input                 = $app->input;
		$uploadedFileExtension = strtolower($input->get('fileExtension', '', 'STRING'));
		$media                 = TJMediaStorageLocal::getInstance();
		$result                = $media->getMime($uploadedFileExtension);

		echo json_encode($result);
		jexit();
	}
}
