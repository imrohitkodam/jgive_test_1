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
 * @access  public
 *
 * @since  1.8.1
 */

class HelperVideoYoutube
{
	protected $videoId = null;

	protected $url = '';

	/**
	 * Get video info
	 *
	 * @param   INT  $url  Video Url
	 *
	 * @return  String  url
	 */
	public function getlink($url)
	{
		$this->url = $url;
		$result    = $this->getId();

		if (!empty($result))
		{
			return $url = 'https://www.youtube.com/embed/' . $result;
		}
		else
		{
			return $url;
		}
	}

	/**
	 * Extract  Video id from the video url submitted by the user
	 *
	 * @access   public
	 *
	 * @return   INT  videoid  Video Id
	 *
	 * @since   1.7
	 */
	public function getId()
	{
		if ($this->videoId)
		{
			return $this->videoId;
		}

		preg_match_all('~
			# Match non-linked youtube URL in the wild. (Rev:20111012)
			https?://         # Required scheme. Either http or https.
			(?:[0-9A-Z-]+\.)? # Optional subdomain.
			(?:               # Group host alternatives.
			  youtu\.be/      # Either youtu.be,
			| youtube\.com    # or youtube.com followed by
			  \S*             # Allow anything up to VIDEO_ID,
			  [^\w\-\s;]       # but char before ID is non-ID char.
			)                 # End host alternatives.
			([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
			(?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
			(?!               # Assert URL is not pre-linked.
			  [?=&+%\w]*      # Allow URL (query) remainder.
			  (?:             # Group pre-linked alternatives.
				[\'"][^<>]*>  # Either inside a start tag,
			  | </a>          # or inside <a> element text contents.
			  )               # End recognized pre-linked alts.
			)                 # End negative lookahead assertion.
			[?=&+%\w]*        # Consume any URL (query) remainder.
			~ix', $this->url, $matches
			);

		if (isset($matches) && !empty($matches[1]))
		{
			return $matches[1][0];
		}

		return false;
	}
}
