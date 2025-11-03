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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
Use Joomla\String\StringHelper;

/**
 * JGive media Helper.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JgiveMediaHelper
{
	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$params                        = ComponentHelper::getParams('com_jgive');
		$this->sa_config['image_size'] = $params->get('max_size');

		if (!$this->sa_config['image_size'])
		{
			$this->sa_config['image_size'] = 9024;
		}
	}

	/**
	 * Generate video embed link.
	 *
	 * @param   string  $video_provider  Video Provider name eg. youtube
	 * @param   string  $url             Video URL
	 *
	 * @return  Video embed link.
	 *
	 * @since   1.6
	 **/
	public function geturl($video_provider, $url)
	{
		switch ($video_provider)
		{
			case 'youtube':
				require_once JPATH_SITE . "/components/com_jgive/helpers/video/youtube.php";
				$helperVideoYoutube = new helperVideoYoutube;

				return $result = $helperVideoYoutube->getlink($url);
				break;

			case 'vimeo':
				require_once JPATH_SITE . "/components/com_jgive/helpers/video/vimeo.php";
				$helperVideoVimeo = new helperVideoVimeo;

				return $result = $helperVideoVimeo->getlink($url);
				break;
		}
	}

	/**
	 * Image upload
	 *
	 * @param   Object  $file_field             File Field
	 * @param   Array   $img_dimensions_config  Image dimensions config
	 * @param   STRING  $upload_orig            Upload origin
	 *
	 * @return  true/false Is file uploaded
	 *
	 * @since  1.7
	 */
	public function imageupload($file_field, $img_dimensions_config, $upload_orig = '1')
	{
		$app           = Factory::getApplication();

		// Get uploaded media details
		$params        = ComponentHelper::getParams('com_jgive');

		// Original file name
		$file_name     = $_FILES[$file_field]['name'];

		// Convert name to lowercase
		$file_name     = strtolower($_FILES[$file_field]['name']);

		// Replace "spaces" with "_" in filename
		$file_name     = preg_replace('/\s/', '_', $file_name);
		$file_type     = $_FILES[$file_field]['type'];
		$file_tmp_name = $_FILES[$file_field]['tmp_name'];
		$file_size     = $_FILES[$file_field]['size'];
		$file_error    = $_FILES[$file_field]['error'];

		// Set error flag, if any error occurs set this to 1
		$error_flag      = 0;

		// Check for max media size allowed for upload
		$max_size_exceed = $this->check_max_size($file_size);

		if ($max_size_exceed)
		{
			$max_size = $params->get('max_size');

			if (!$max_size)
			{
				// KB
				$max_size = 1024;
			}

			$errorList[] = Text::_('FILE_BIG') . " " . $max_size . "KB<br>";
			$app->enqueueMessage(Text::_('COM_JGIVE_MAX_FILE_SIZE') . ' ' . $max_size . 'KB<br>', 'error');
			$error_flag = 1;
		}

		if (!$error_flag)
		{
			// Detect file type & detect media group type image/video/flash
			$media_type_group = $this->check_media_type_group($file_type);

			if (!$media_type_group['allowed'])
			{
				$errorList[] = Text::_('COM_JGIVE_FILE_TYPE_NOT_SUPPORTED');
				$app->enqueueMessage(Text::_('COM_JGIVE_FILE_TYPE_NOT_SUPPORTED'), 'error');
				$error_flag = 1;
			}

			if (!$error_flag)
			{
				$media_extension = $this->get_media_extension($file_name);

				// Upload original img
				$timestamp = time();

				$original_file_name = $original_file_name_with_extension = $timestamp . '_' . $file_name;

				// Always use constants when making file paths, to avoid the possibilty of remote file inclusion
				$fullPath = JPATH_SITE . '/images/jGive/';
				$relPath  = 'images/jGive/';

				// If folder is not present create it
				if (!Folder::exists(JPATH_SITE . '/images/jGive'))
				{
					@mkdir(JPATH_SITE . '/images/jGive');
				}

				// Determine if resizing is needed for images
				foreach ($img_dimensions_config as $config)
				{
					$media_dimnesions = new stdClass;

					// If component optins saved the get the image dimentions
					if ($params->get($config . '_width'))
					{
						$media_dimnesions->img_width = $params->get($config . '_width');
					}
					else // If there is no value exist then get default value
					{
						switch ($config . '_width')
						{
							case 'small_width':
								$media_dimnesions->img_width = 64;
								break;

							case 'medium_width':
								$media_dimnesions->img_width = 120;
								break;

							case 'large_width':
								$media_dimnesions->img_width = 400;
								break;

							default:
								$media_dimnesions->img_width = 400;
								break;
						}
					}

					if ($params->get($config . '_height'))
					{
						$media_dimnesions->img_height = $params->get($config . '_height');
					}
					else
					{
						switch ($config . '_height')
						{
							case 'small_height':
								$media_dimnesions->img_height = 64;
								break;

							case 'medium_height':
								$media_dimnesions->img_height = 120;
								break;

							case 'large_height':
								$media_dimnesions->img_height = 400;
								break;

							default:
								$media_dimnesions->img_height = 400;
								break;
						}
					}

					$max_zone_width  = $media_dimnesions->img_width;
					$max_zone_height = $media_dimnesions->img_height;

					switch ($config)
					{
						case 'small':
							$file_name_with_extension_size = "S_" . $original_file_name_with_extension;
							break;
						case 'medium':
							$file_name_with_extension_size = "M_" . $original_file_name_with_extension;
							break;
						case 'large':
							$file_name_with_extension_size = "L_" . $original_file_name_with_extension;
							break;
						default:
							$file_name_with_extension_size = $original_file_name_with_extension;
							break;
					}

					if ($media_type_group['media_type_group'] == "image")
					{
						// Get uploaded image dimensions
						$media_size_info = $this->check_media_resizing_needed($media_dimnesions, $file_tmp_name);
						$resizing = 0;

						if ($media_size_info['resize'])
						{
							$resizing = 1;
						}

						switch ($resizing)
						{
							case 0:
								$new_media_width  = $media_size_info['width_img'];
								$new_media_height = $media_size_info['height_img'];

								// @TODO not sure abt this
								$top_offset       = 0;

								// @TODO not sure abt this
								$blank_height     = $new_media_height;
								break;
							case 1:
								$new_dimensions   = $this->get_new_dimensions($max_zone_width, $max_zone_height, 'auto');
								$new_media_width  = $new_dimensions['new_calculated_width'];
								$new_media_height = $new_dimensions['new_calculated_height'];
								$top_offset       = $new_dimensions['top_offset'];
								$blank_height     = $new_dimensions['blank_height'];
								break;
						}
					}
					else // As we skipped resizing for video , we will use zone dimensions
					{
						$new_media_width  = $media_dimnesions->img_width;
						$new_media_height = $media_dimnesions->img_height;

						// @TODO not sure abt this
						$top_offset       = 0;
						$blank_height     = $new_media_height;
					}

					$colorR = 255;
					$colorG = 255;
					$colorB = 255;

					$image_upload_param = array();

					$image_upload_param['file_field']                    = $file_field;
					$image_upload_param['max_zone_width']                = $max_zone_width;
					$image_upload_param['max_zone_height']               = $max_zone_height;
					$image_upload_param['fullPath']                      = $fullPath;
					$image_upload_param['relPath']                       = $relPath;
					$image_upload_param['colorR']                        = $colorR;
					$image_upload_param['colorG']                        = $colorG;
					$image_upload_param['colorB']                        = $colorB;
					$image_upload_param['new_media_width']               = $new_media_width;
					$image_upload_param['new_media_height']              = $new_media_height;
					$image_upload_param['blank_height']                  = $blank_height;
					$image_upload_param['top_offset']                    = $top_offset;
					$image_upload_param['media_extension']               = $media_extension;
					$image_upload_param['file_name_with_extension_size'] = $file_name_with_extension_size;

					$upload_image = $this->uploadImage($image_upload_param);
				}

				if ($upload_orig == '1')
				{
					$upload_path = $fullPath . $original_file_name;

					if (!File::upload($file_tmp_name, $upload_path))
					{
						$app->enqueueMessage(Text::_('COM_JGIVE_ERROR_MOVING_FILE'), 'error');
						echo Text::_('COM_JGIVE_ERROR_MOVING_FILE');

						return false;
					}
				}

				return $original_file_name;
			}
		}

		return false;
	}

	/**
	 * Check for max media size allowed for upload
	 *
	 * @param   INT  $file_size  File Size
	 *
	 * @return  0/1 According to allowed file size
	 *
	 * @since  1.7
	 */
	public function check_max_size($file_size)
	{
		// @TODO needed?
		$this->media_size = $file_size;
		$max_media_size   = $this->sa_config['image_size'] * 1024;

		if ($file_size > $max_media_size)
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Check media type group
	 *
	 * @param   STRING  $file_type  File type
	 *
	 * @return  file type
	 *
	 * @since  1.7
	 */
	public function check_media_type_group($file_type)
	{
		$allowed_media_types = array(
			'image' => array(
				'image/png',
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg'
			)
		);

		$media_type_group    = '';
		$flag                = 0;

		foreach ($allowed_media_types as $key => $value)
		{
			if (in_array($file_type, $value))
			{
				$media_type_group = $key;
				$flag             = 1;
				break;
			}
		}

		$this->media_type       = $file_type;
		$this->media_type_group = $media_type_group;

		$return['media_type']       = $file_type;
		$return['media_type_group'] = $media_type_group;

		if (!$flag)
		{
			// File type not allowed
			$return['allowed'] = 0;

			return $return;
		}

		// Allowed file type
		$return['allowed'] = 1;

		return $return;
	}

	/**
	 * Get media file name without extension
	 *
	 * @param   STRING  $file_name  File name
	 *
	 * @return  Media file name without extension
	 *
	 * @since  1.7
	 */
	public function get_media_extension($file_name)
	{
		$media_extension       = pathinfo($file_name);
		$this->media_extension = $media_extension['extension'];

		return $media_extension['extension'];
	}

	/**
	 * Get media file name without extension
	 *
	 * @param   STRING  $file_name  File name
	 *
	 * @return  Media file name without extension
	 */
	public function get_media_file_name_without_extension($file_name)
	{
		$media_extension = pathinfo($file_name);

		return $media_extension['filename'];
	}

	/**
	 * Check media resizing needed
	 *
	 * @param   Object  $adzone_media_dimnesions  Adzone media dimnesions
	 * @param   STRING  $file_tmp_name            File tmp name
	 *
	 * @return  Dimensions
	 */
	public function check_media_resizing_needed($adzone_media_dimnesions, $file_tmp_name)
	{
		// Get uploaded image height and width
		// This will work for all images + swf files
		list($width_img, $height_img) = getimagesize($file_tmp_name);
		$return['width_img']  = $width_img;
		$return['height_img'] = $height_img;
		$this->width          = $width_img;
		$this->height         = $height_img;

		if ($width_img == $adzone_media_dimnesions->img_width && $height_img == $adzone_media_dimnesions->img_height)
		{
			$return['resize'] = 0;

			// No resizing needed
			return $return;
		}

		$return['resize'] = 1;

		// Resizing needed
		return $return;
	}

	/**
	 * Get new image dimensions
	 *
	 * @param   INT     $max_zone_width   Zone max width
	 * @param   INT     $max_zone_height  Zone max height
	 * @param   STRING  $option           Options
	 *
	 * @return  Dimensions
	 */
	public function get_new_dimensions($max_zone_width, $max_zone_height, $option)
	{
		switch ($option)
		{
			case 'exact':
				$new_calculated_width  = $max_zone_width;
				$new_calculated_height = $max_zone_height;
				break;
			case 'auto':
				$new_dimensions        = $this->get_optimal_dimensions($max_zone_width, $max_zone_height);
				$new_calculated_width  = $new_dimensions['new_calculated_width'];
				$new_calculated_height = $new_dimensions['new_calculated_height'];
				break;
		}

		$new_dimensions['new_calculated_width']  = $new_calculated_width;
		$new_dimensions['new_calculated_height'] = $new_calculated_height;

		return $new_dimensions;
	}

	/**
	 * Upload image to server
	 *
	 * @param   Array  $image_upload_param  Parameter
	 *
	 * @return  Message is file uploaded
	 */
	public function uploadImage($image_upload_param = array())
	{
		$file_field                    = $image_upload_param['file_field'];
		$max_zone_width                = $image_upload_param['max_zone_width'];
		$max_zone_height               = $image_upload_param['max_zone_height'];
		$fullPath                      = $image_upload_param['fullPath'];
		$relPath                       = $image_upload_param['relPath'];
		$colorR                        = $image_upload_param['colorR'];
		$colorG                        = $image_upload_param['colorG'];
		$colorB                        = $image_upload_param['colorB'];
		$new_media_width               = $image_upload_param['new_media_width'];
		$new_media_height              = $image_upload_param['new_media_height'];
		$blank_height                  = $image_upload_param['blank_height'];
		$top_offset                    = $image_upload_param['top_offset'];
		$media_extension               = $image_upload_param['media_extension'];
		$file_name_with_extension_size = $image_upload_param['file_name_with_extension_size'];

		switch ($this->media_type_group)
		{
			case "flash":

				// Retrieve file details from uploaded file, sent from upload form
				$file     = $_FILES[$file_field];

				// Clean up filename to get rid of strange characters like spaces etc
				$filename = File::makeSafe($file['name']);

				// Set up the source and destination of the file
				$src      = $file['tmp_name'];

				$filename  = strtolower($filename);
				$filename  = preg_replace('/\s/', '_', $filename);
				$timestamp = time();
				$filename  = $file_name_with_extension_size;
				$dest      = $fullPath . "swf/" . $filename;

				// First check if the file has the right extension, we need swf only
				if (File::upload($src, $dest))
				{
					$dest = $fullPath . "swf/" . $filename;

					return $dest;
				}
				break;
			case "video":

				// Retrieve file details from uploaded file, sent from upload form
				$file     = $_FILES[$file_field];

				// Clean up filename to get rid of strange characters like spaces etc
				$filename = File::makeSafe($file['name']);

				// Set up the source and destination of the file
				$src      = $file['tmp_name'];

				$filename  = strtolower($filename);
				$filename  = preg_replace('/\s/', '_', $filename);
				$timestamp = time();
				$filename  = $timestamp . "_" . $file_name_with_extension_size;

				$dest = $fullPath . "vids/" . $filename;

				if (File::upload($src, $dest))
				{
					$dest = $fullPath . "vids/" . $filename;

					return $dest;
				}

				break;
		}

		$errorList = array();

		// ADDED BY @VIDYASAGAR
		$folder    = $fullPath;
		$match     = "";
		$filesize  = $_FILES[$file_field]['size'];

		if ($filesize > 0)
		{
			$filename = strtolower($_FILES[$file_field]['name']);
			$filename = preg_replace('/\s/', '_', $filename);

			if ($filesize < 1)
			{
				$errorList[] = Text::_('FILE_EMPTY');
			}

			if (count($errorList) < 1)
			{
				// File is allowed
				$match       = "1";
				$NUM         = time();
				$newfilename = $file_name_with_extension_size;
				$save        = $folder . $newfilename;

				if (!file_exists($save))
				{
					list($this->width, $this->height) = getimagesize($_FILES[$file_field]['tmp_name']);
					$image_p = imagecreatetruecolor($new_media_width, $blank_height);
					$white   = imagecolorallocate($image_p, $colorR, $colorG, $colorB);

					// START added to preserve transparency
					imagealphablending($image_p, false);
					imagesavealpha($image_p, true);

					$transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);
					imagefill($image_p, 0, 0, $transparent);

					switch ($media_extension)
					{
						case "jpg":
							$image = @imagecreatefromjpeg($_FILES[$file_field]['tmp_name']);
							@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $new_media_width, $new_media_height, $this->width, $this->height);
							break;

						case "jpeg":
							$image = @imagecreatefromjpeg($_FILES[$file_field]['tmp_name']);
							@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $new_media_width, $new_media_height, $this->width, $this->height);
							break;

						case "png":
							$image = @imagecreatefrompng($_FILES[$file_field]['tmp_name']);
							@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $new_media_width, $new_media_height, $this->width, $this->height);
							break;
					}

					switch ($media_extension)
					{
						/*
						case "gif":
						if(!@imagegif($image_p, $save)){
						$errorList[]= Text::_('FILE_GIF');
						}

						break;
						*/
						case "jpg":
							if (!@imagejpeg($image_p, $save, 100))
							{
								$errorList[] = Text::_('FILE_JPG');
							}
							break;
						case "jpeg":
							if (!@imagejpeg($image_p, $save, 100))
							{
								$errorList[] = Text::_('FILE_JPEG');
							}
							break;
						case "png":
							if (!@imagepng($image_p, $save, 0))
							{
								$errorList[] = Text::_('FILE_PNG');
							}
							break;
					}

					@imagedestroy($filename);
				}
				else
				{
					$errorList[] = Text::_('FILE_EXIST');
				}
			}
		}
		else
		{
			$errorList[] = Text::_('FILE_NO');
		}

		if (!$match)
		{
			$errorList[] = Text::_('FILE_ALLOW') . ":" . $filename;
		}

		if (sizeof($errorList) == 0)
		{
			return $fullPath . $newfilename;
		}
		else
		{
			$eMessage = array();

			for ($x = 0; $x < sizeof($errorList); $x++)
			{
				$eMessage[] = $errorList[$x];
			}

			return $eMessage;
		}
	}

	/**
	 * Get optimal dimensions
	 *
	 * @param   INT  $max_zone_width   Zone max width
	 * @param   INT  $max_zone_height  Zone max height
	 *
	 * @return  Dimensions
	 */
	public function get_optimal_dimensions($max_zone_width, $max_zone_height)
	{
		// @TODO not sure abt this
		$top_offset = 0;

		if ($max_zone_height == null)
		{
			if ($this->width < $max_zone_width)
			{
				$new_calculated_width = $this->width;
			}
			else
			{
				$new_calculated_width = $max_zone_width;
			}

			$ratio_orig            = $this->width / $this->height;
			$new_calculated_height = $new_calculated_width / $ratio_orig;

			$blank_height = $new_calculated_height;
			$top_offset   = 0;
		}
		else
		{
			if ($this->width <= $max_zone_width && $this->height <= $max_zone_height)
			{
				$new_calculated_height = $this->height;
				$new_calculated_width  = $this->width;
			}
			else
			{
				if ($this->width > $max_zone_width)
				{
					$ratio                 = ($this->width / $max_zone_width);
					$new_calculated_width  = $max_zone_width;
					$new_calculated_height = ($this->height / $ratio);

					if ($new_calculated_height > $max_zone_height)
					{
						$ratio                 = ($new_calculated_height / $max_zone_height);
						$new_calculated_height = $max_zone_height;
						$new_calculated_width  = ($new_calculated_width / $ratio);
					}
				}

				if ($this->height > $max_zone_height)
				{
					$ratio                 = ($this->height / $max_zone_height);
					$new_calculated_height = $max_zone_height;
					$new_calculated_width  = ($this->width / $ratio);

					if ($new_calculated_width > $max_zone_width)
					{
						$ratio                 = ($new_calculated_width / $max_zone_width);
						$new_calculated_width  = $max_zone_width;
						$new_calculated_height = ($new_calculated_height / $ratio);
					}
				}
			}

			if ($new_calculated_height < 45)
			{
				$blank_height = 45;
				$top_offset   = round(($blank_height - $new_calculated_height) / 2);
			}
			else
			{
				$blank_height = $new_calculated_height;
			}
		}

		$new_dimensions['new_calculated_width']  = $new_calculated_width;
		$new_dimensions['new_calculated_height'] = $new_calculated_height;
		$new_dimensions['top_offset']            = $top_offset;
		$new_dimensions['blank_height']          = $blank_height;

		return $new_dimensions;
	}

	/**
	 * Mirror to getVideoFromProvider()
	 *
	 * @param   STRING  $videoLink  Video Provider
	 *
	 * @return  Video Provider
	 */
	public function getProvider($videoLink)
	{
		$providerName = 'invalid';

		if (!empty($videoLink))
		{
			$origvideolink = $videoLink;

			// If it using https
			$videoLink = str_ireplace('https://', 'http://', $videoLink);
			$videoLink = str_ireplace('http://', '', $videoLink);

			if ($videoLink === $origvideolink)
			{
				$videoLink = str_ireplace('http://', '', $videoLink);
			}

			$videoLink  = 'http://' . $videoLink;
			$parsedLink = parse_url($videoLink);

			preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedLink['host'], $matches);

			if (!empty($matches['domain']))
			{
				$domain       = $matches['domain'];
				$provider     = explode('.', $domain);
				$providerName = StringHelper::strtolower($provider[0]);

				// For youtube, they might be using youtu.be address
				if ($domain == 'youtu.be')
				{
					$providerName = 'youtube';
				}

				if ($parsedLink['host'] === 'new.myspace.com')
				{
					$providerName = 'invalid';
				}
			}
		}

		$libraryPath = JPATH_ROOT . '/components/com_jgive/helpers/video' . '/' . $providerName . '.php';

		if (!File::exists($libraryPath))
		{
			$providerName = 'invalid';
		}

		return $providerName;
	}

	/**
	 * Get Video Thumbnail source
	 *
	 * @param   STRING  $videoProvider  Video Provider
	 * @param   INT     $videoId        Video Id
	 *
	 * @return  Object Video thumbnail
	 */
	public static function videoThumbnail($videoProvider, $videoId)
	{
		$explodedUrl = explode('?', $videoId);

		switch ($videoProvider)
		{
			// For video provider youtube
			case 'youtube':
				// Get youtube video ID from embed url, after explode in array 4th index contain actual video id
				$thumbSrc = 'https://i.ytimg.com/vi/' . $explodedUrl[0] . '/sddefault.jpg';
				break;

			// For video provider vimeo
			case 'vimeo':
				// Get vimeo video ID from embed url, after explode in array 4th index contain actual video id
				$hash     = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$videoId.php"));
				$thumbSrc = $hash[0]['thumbnail_medium'];
				break;

			// Other video provider than above
			default:
				$thumbSrc = Uri::root(true) . '/media/com_jgive/images/no_thumb.png';
				break;
		}

		return $thumbSrc;
	}

	/**
	 * Get Video Id From embed url
	 *
	 * @param   STRING  $videoProvider  Video Provider
	 * @param   INT     $videoEmbedUrl  Video embed url
	 *
	 * @return  INT  Video Id
	 */
	public static function videoId($videoProvider = null, $videoEmbedUrl = null)
	{
		$videoId = null;

		switch ($videoProvider)
		{
			// For video provider youtube & vimeo
			case 'youtube':
			case 'vimeo':

				$explodedUrl = explode('/', $videoEmbedUrl);

				if (!empty($explodedUrl))
				{
					$videoId = end($explodedUrl);
				}

			break;

			// Other video provider than above
			default:
					// For future
			break;
		}

		return $videoId;
	}

	/**
	 * Function to generate video params
	 *
	 * @param   integer  $vid  Video id
	 *
	 * @return  array|boolean  Video params
	 */
	public function getVideoParams($vid)
	{
		if (empty($vid))
		{
			return false;
		}

		// Get video data
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'media');
		$jgiveModelCampaignFrom = BaseDatabaseModel::getInstance('media', 'JGiveModel');
		$videoDetails = $jgiveModelCampaignFrom->getItem($vid);

		if (empty($videoDetails))
		{
			return false;
		}

		$video_params = array();
		$params       = ComponentHelper::getParams('com_jgive');
		$video_upload = $params->get('video_upload', '1');
		$fileType     = explode(".", $videoDetails->type);

		// Set params according to upload option
		if (!empty($videoDetails->type) && $video_upload == 1&& ($fileType[1] == 'youtube' || $fileType[1] == 'vimeo'))
		{
			switch ($fileType[1])
			{
				// Video provider youtube
				case 'youtube':
					// Get youtube video ID from embed url, after explode in array 4th index contain actual video id
					$explodedUrl = explode('/', $videoDetails->path);

					if (!empty($explodedUrl))
					{
						$videoId = end($explodedUrl);
						$video_params['file'] = 'https://www.youtube.com/watch?v=' . $videoId;
						$video_params['videoId'] = $videoId;

						// Plugin to call to pay video
						$video_params['plugin'] = 'jwplayer';
					}
				break;

				// Video provider vimeo
				case 'vimeo':
					$explodedUrl = explode('/', $videoDetails->path);

					if (!empty($explodedUrl))
					{
						$videoId = end($explodedUrl);

						// Get youtube video ID from embed url, after explode in array 4th index contain actual video id
						$video_params['videoId'] = $videoId;

						// Plugin to call to pay video
						$video_params['plugin'] = 'vimeo';
					}
				break;

				// Other video provider than above
				default:
					// For future
				break;
			}
		}
		else
		{
			// For uploaded files
			$video_params['file'] = Uri::root() . $videoDetails->path . "/videos/" . $videoDetails->source;

			// Plugin to call to play video
			$video_params['plugin'] = 'jwplayer';
		}

		return $video_params;
	}
}
