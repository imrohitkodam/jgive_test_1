<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class to manipulate data from YouTube
 *
 * @access	public
 *
 * @since  1.7
 */
class HelperVideoVimeo
{
	public $videoId = null;

	public $url = '';

	/**
	 * Get video info
	 *
	 * @param   INT  $url  Video Url
	 *
	 * @return  String  Thumbnail source
	 */
	public function getlink($url)
	{
		$this->url = $url;
		$result    = $this->getId();

		if (!empty($result))
		{
			$url = 'https://player.vimeo.com/video/' . $result;
		}

		return $url;
	}

	/**
	 * Extract Vimeo video id from the video url submitted by the user
	 *
	 * @access   public
	 *
	 * @return   INT  videoid  Video Id
	 *
	 * @since   1.7
	 */
	public function getId()
	{
		$pattern = '/vimeo.com\/(hd#)?(channels\/[a-zA-Z0-9]*#)?(\d*)/';
		preg_match($pattern, $this->url, $match);

		if (!empty($match[3]))
		{
			return $match[3];
		}
		else
		{
			return !empty($match[2]) ? $match[2] : null;
		}
	}

	/**
	 * Get video info
	 *
	 * @param   INT     $videoId         Video Id
	 * @param   STRING  $thumbnail_size  Thumbnail size
	 *
	 * @return  string  Thumbnail source
	 */
	public static function getThumbnail($videoId, $thumbnail_size = 'thumbnail_large')
	{
		$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$videoId.php"));

		return $hash[0][$thumbnail_size];
	}
}
