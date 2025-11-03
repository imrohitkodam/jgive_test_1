<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

JLoader::import('campaignform', JPATH_SITE . '/components/com_jgive/models');
JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);

/**
 * Methods supporting a jgive media.
 *
 * @since  2.1.0
 */
class JGiveModelMedia extends AdminModel
{
	private $fileStorage = 'local';

	private $fileAccess = 'public';

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$jgParams = ComponentHelper::getParams('com_jgive');
		$this->storagePath = $jgParams->get('jgive_media_upload_path', 'media/com_jgive/campaigns');
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return	boolean|JTable	A database object
	 *
	 * @since	2.1
	 */
	public function getTable($type = 'Media', $prefix = 'JGiveTable', $config = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient('administrator'))
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
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    2.1
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jgive.media', 'media', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data. @TODO Remove this function in 2.2.3
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since  2.1
	 */
	public function save($data)
	{
		$mediaData = array();

		if (!empty($data))
		{
			// Checking for file type link or upload file
			if (isset($data['upload_type']) && $data['upload_type'] == "link")
			{
				$mediaData = $this->uploadLink($data);
			}
			elseif (isset($data['upload_type']) && $data['upload_type'] == "move")
			{
				$mediaData = $this->moveFile($data);
			}
			else
			{
				$mediaData = $this->uploadFile($data);
			}
		}

		if (!empty($mediaData))
		{
			// Generate media data
			$mediaData['storage']      = $this->fileStorage;
			$mediaData['created_by']   = isset($data['created_by']) ? $data['created_by'] : Factory::getUser()->id;
			$mediaData['created_date'] = Factory::getDate()->toSql();
			$mediaData['access']       = $this->fileAccess;
			$mediaData['params']       = '';
			$mediaData['state']        = 1;

			if (parent::save($mediaData))
			{
				$mediaData['id'] = $this->getState($this->getName() . '.id');
				$mediaType = explode(".", $mediaData['type']);
				$mediaPath = $this->storagePath;

				// Generating media data on the basis of its type
				// Image data
				if ($mediaType[0] == 'image')
				{
					$mediaData['media']   = $mediaPath . '/images/' . $mediaData['source'];
					$mediaData['media_s'] = $mediaPath . '/images/S_' . $mediaData['source'];
					$mediaData['media_m'] = $mediaPath . '/images/M_' . $mediaData['source'];
					$mediaData['media_l'] = $mediaPath . '/images/L_' . $mediaData['source'];
				}
				elseif ($mediaType[0] == 'video')
				{
					// Link media and video upload data
					if ($mediaType[1] == 'youtube' || $mediaType[1] == 'vimeo')
					{
						$mediaData['media'] = $mediaData['source'];
					}
					else
					{
						$mediaData['media'] = $mediaPath . '/videos/' . $mediaData['source'];
					}
				}
				elseif ($mediaType[0] == 'application')
				{
					$mediaData['media'] = $mediaPath . '/applications/' . $mediaData['source'];
				}
				elseif ($mediaType[0] == 'audio')
				{
					$mediaData['media'] = $mediaPath . '/audios/' . $mediaData['source'];
				}

				return $mediaData;
			}
		}

		return;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 *
	 * @since	2.1
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			$jgParams    = ComponentHelper::getParams('com_jgive');
			$uploadPath  = $jgParams->get('small_width');
			$mediaType   = explode(".", $item->type);
			$mediaPath   = $item->path;
			$item->media = '';

			// Generating image data
			if ($mediaType[0] == 'image')
			{
				$item->media   = $mediaPath . '/images/' . $item->source;
				$item->media_s = $mediaPath . '/images/S_' . $item->source;
				$item->media_m = $mediaPath . '/images/M_' . $item->source;
				$item->media_l = $mediaPath . '/images/L_' . $item->source;
			}
			elseif ($mediaType[0] == 'video')
			{
				// Generating video data, if its a link or a file upload
				if ($mediaType[1] == 'youtube' || $mediaType[1] == 'vimeo')
				{
					$item->media = $item->source;
				}
				else
				{
					$item->media = $mediaPath . '/videos/' . $item->source;
				}
			}
			elseif ($mediaType[0] == 'application')
			{
				$item->media = $mediaPath . '/applications/' . $item->source;
			}
			elseif ($mediaType[0] == 'audio')
			{
				$item->media = $mediaPath . '/audios/' . $item->source;
			}

			return $item;
		}

		return false;
	}

	/**
	 * Method to delete media record
	 *
	 * @param   Integer  $mediaId      media Id of files table
	 * @param   STRING   $storagePath  file path from params in config
	 * @param   STRING   $client       client(example -'com_jgive.reports')
	 * @param   Integer  $clientId     clientId(example - Report Id, Campaign Id, Giveback ID)
	 *
	 * @return	boolean  True if successful, false if an error occurs.
	 *
	 * @since   2.1
	 */
	public function deleteMedia($mediaId, $storagePath, $client, $clientId)
	{
		if (!$mediaId)
		{
			return false;
		}
		
		$media = $this->getItem($mediaId);

		if (empty($media))
		{
			return false;
		}

		// Get the current user id
		$createdByUserId = (int) $media->created_by;

		// Get the vendor id
		JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$vendorId            = $tjvendorFrontHelper->checkVendor($createdByUserId, 'com_jgive');

		// Check ownership
		$modelCampaignForm = BaseDatabaseModel::getInstance('CampaignForm', 'JGiveModel');
		$authorised        = $modelCampaignForm->checkOwnership($vendorId, 'delete');

		if (!$authorised)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		JLoader::import("/techjoomla/media/tables/xref", JPATH_LIBRARIES);
		JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
		$tableXref = Table::getInstance('Xref', 'TJMediaTable');
		$filetable = Table::getInstance('Files', 'TJMediaTable');

		PluginHelper::importPlugin('system');
		Factory::getApplication()->triggerEvent('onBeforeJGMediaDelete', array($mediaId));

		// CheckMediaDataExist will return 1 when media is present clientId is Report Id
		$checkMediaDataExist = $tableXref->load($data = array('media_id' => $mediaId));

		// Making file delete path
		$filetable->load($mediaId);

		$mediaType    = explode(".", $filetable->type);
		$deletePath   = $storagePath . '/' . $mediaType[0] . 's';
		$mediaPresent = $tableXref->load(array('media_id' => $mediaId));

		// If Media is present
		if ($checkMediaDataExist)
		{
			// Get Object which include Media xref + Media File data of provided Media xref id
			$mediaXrefLib = TJMediaXref::getInstance($config = array('id' => $tableXref->id));

			// If media is not deleted it will return false here
			if (!$mediaXrefLib->delete())
			{
				return false;
			}
			// If media is deleted it will return true here
			else
			{
				$xrefMediaPresent = $tableXref->load(array('media_id' => $mediaId));

				// If media delete here get false
				if (!$xrefMediaPresent)
				{
					$mediaLib = TJMediaStorageLocal::getInstance($mediaConfig = array('id' => $mediaId, 'uploadPath' => $deletePath));

					// Checking Media is present or not
					if ($mediaLib->id)
					{
						// If Media is not deleted
						if (!$mediaLib->delete())
						{
							return false;
						}
					}
					else
					{
						return false;
					}
				}
				// If media is not delete will return true here
				else
				{
					return false;
				}

				return true;
			}
		}
		elseif (!$mediaPresent)
		{
			$mediaLib = TJMediaStorageLocal::getInstance($mediaConfig = array('id' => $mediaId, 'uploadPath' => $deletePath));

			if ($mediaLib->id)
			{
				if ($mediaLib->delete())
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to update media record
	 *
	 * @param   Array    $mediaIds  post data contains media Ids and its default values
	 * @param   Integer  $cid       Campaign Id
	 *
	 * @return boolean
	 *
	 * @since   2.1
	 */
	public function setDefaultMedia($mediaIds, $cid)
	{
		$user = Factory::getUser();

		// Is logged in user doing this action?
		if (!empty($user->id) && $user->authorise('core.create', 'com_jgive') || $user->authorise('core.edit', 'com_jgive'))
		{
			// Checking campaign id in edit scenario
			if ($cid)
			{
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaignform');
				$jgiveModelCampaignFrom = BaseDatabaseModel::getInstance('campaignform', 'JGiveModel');
				$item                   = $jgiveModelCampaignFrom->getItem($cid);

				if (!$user->authorise('core.admin'))
				{
					if ($user->id == $item->creator_id)
					{
						$authorise = ($user->authorise('core.edit', 'com_jgive') == 1 ? true : false);

						// User is not an admin and he is editing his own campaign
						if ($authorise === false)
						{
							return false;
						}
					}
					else
					{
						return false;
					}
				}
			}

			foreach ($mediaIds as $mediaId)
			{
				$defaultValue            = array();
				$defaultValue["default"] = $mediaId["default"];
				$table                   = $this->getTable('media');
				$table->params           = json_encode($defaultValue);
				$table->id               = $mediaId["mediaId"];
				$table->store();
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to upload the file (image/video/PDF/Audio)
	 *
	 * @param   ARRAY|Object  $fileData  fileData
	 *
	 * @param   STRING        $path      file path
	 *
	 * @param   Integer       $access    access for uploading
	 *
	 * @return	array
	 *
	 * @since   2.1
	 */
	public function uploadFile($fileData, $path, $access = null)
	{
		$fileData[0]['name'] = File::makeSafe($fileData[0]['name']);
		$jgiveParams         = ComponentHelper::getParams('com_jgive');
		$mediaMaxSize        = $jgiveParams->get('jgive_media_size', '15');

		$config                   = array();
		$config['uploadPath']     = $path;
		$config['saveData']       = 1;
		$config['state']          = '0';
		$config['size']           = $mediaMaxSize;
		$config['params']         = '';
		$allowedExtensionForVideo = explode(",", $jgiveParams->get('allowedFileExtensions'));
		array_push($allowedExtensionForVideo, "jpeg", "png", "jpg", "webp", "gif");
		$config['allowedExtension'] = $allowedExtensionForVideo;

		if ($access !== null)
		{
			$config['auth'] = $access;
		}

		$config['imageResizeSize']                            = array();
		$config['imageResizeSize']['small']['small_width']    = $jgiveParams->get('small_width', '128');
		$config['imageResizeSize']['small']['small_height']   = $jgiveParams->get('small_height', '128');
		$config['imageResizeSize']['medium']['medium_width']  = $jgiveParams->get('medium_width', '200');
		$config['imageResizeSize']['medium']['medium_height'] = $jgiveParams->get('medium_height', '150');
		$config['imageResizeSize']['large']['large_width']    = $jgiveParams->get('large_width', '400');
		$config['imageResizeSize']['large']['large_height']   = $jgiveParams->get('large_height', '300');

		$mediaLib = TJMediaStorageLocal::getInstance($config);

		return $mediaLib->upload($fileData);
	}

	/**
	 * Method to upload video file link
	 *
	 * @param   array  $uploadLink  post data
	 *
	 * @return array
	 *
	 * @since   2.1
	 */
	public function uploadLink($uploadLink)
	{
		$config   = array();
		$config['params'] = '';
		$mediaLib = TJMediaStorageLocal::getInstance($config);

		return $mediaLib->uploadLink($uploadLink);
	}

	/**
	 * Method to create small, medium and large images of original image
	 *
	 * @param   string  $src       source path with file name
	 *
	 * @param   string  $imgPath   destination path
	 *
	 * @param   string  $fileName  new file name
	 *
	 * @return	boolean
	 *
	 * @since   2.0
	 */
	public function resizeImage($src, $imgPath, $fileName)
	{
		// Creating a new JImage object, passing it an image path
		$image    = new Image($src);
		$file     = explode(".", $fileName);
		$destPath = JPATH_SITE . '/' . $imgPath;
		$format   = '';
		$jgParams = ComponentHelper::getParams('com_jgive');

		if ($file[1] == 'jpeg' || $file[1] == 'jpg')
		{
			$format = IMAGETYPE_JPEG;
		}
		elseif ($file[1] == 'png')
		{
			$format = IMAGETYPE_PNG;
		}
		elseif ($file[1] == 'gif')
		{
			$format = IMAGETYPE_GIF;
		}

		// Small image
		if ($format)
		{
			$smallWidth  = $jgParams->get('small_width', '128');
			$smallHeight = $jgParams->get('small_height', '128');
			$destFile    = 'S_' . $fileName;
			$newImage    = $image->resize($smallWidth, $smallHeight);
			$newImage->toFile($destPath . $destFile, $format);
		}

		// Medium image
		if ($format)
		{
			$mediumWidth  = $jgParams->get('medium_width', '240');
			$mediumHeight = $jgParams->get('medium_height', '240');
			$destFile     = 'M_' . $fileName;
			$newImage     = $image->resize($mediumWidth, $mediumHeight);
			$newImage->toFile($destPath . $destFile, $format);
		}

		// Large image
		if ($format)
		{
			$largeWidth  = $jgParams->get('large_width', '400');
			$largeHeight = $jgParams->get('large_height', '400');
			$destFile    = 'L_' . $fileName;

			// Resize the image using the SCALE_INSIDE method
			$newImage = $image->resize($largeWidth, $largeHeight);

			// Write it to disk
			$newImage->toFile($destPath . $destFile, $format);
		}

		return true;
	}

	/**
	 * Method to Move the file
	 *
	 * @param   ARRAY  $fileData  fileData
	 *
	 * @return array|boolean
	 *
	 * @since   2.0
	 */
	public function moveFile($fileData)
	{
		$fileName   = File::makeSafe($fileData['name']);
		$fileExt    = strtolower(File::getExt($fileName));
		$date       = Date::getInstance();
		$sourceFile = $date->toUnix() . '-' . rand(1, 999) . '.' . $fileExt;

		if (strpos($fileData['type'], 'image') === false)
		{
			$mediaTypePath = '/videos/';
		}
		else
		{
			$mediaTypePath = '/images/';
		}

		$destPath = $this->storagePath . $mediaTypePath . $sourceFile;

		if (!file_exists(JPATH_SITE . '/' . $this->storagePath . $mediaTypePath))
		{
			mkdir(JPATH_SITE . '/' . $this->storagePath . $mediaTypePath, 0777, true);
		}

		if (File::move($fileData['tmp_name'], JPATH_SITE . '/' . $destPath))
		{
			if ($mediaTypePath == '/images/')
			{
				$this->resizeImage(JPATH_SITE . '/' . $destPath, $this->storagePath . '/images/', $sourceFile);
			}

			$returnData = array();

			// File original name
			$returnData['name'] = $fileName;
			$returnData['original_filename'] = $fileName;
			$returnData['type'] = $fileData['type'];
			$returnData['source'] = $sourceFile;
			$returnData['size'] = '';
			$returnData['path'] = $this->storagePath;

			// Maybe this should be like this
			// $returnData['path'] = $this->storagePath . $mediaTypePath;

			return $returnData;
		}

		return false;
	}
}
