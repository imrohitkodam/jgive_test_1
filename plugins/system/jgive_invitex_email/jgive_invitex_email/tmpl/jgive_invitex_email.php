<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

$message_body    = $vars->message_body;
$cdata           = $vars->cdata;
$connection_data = $vars->connection_data;

$campaign_link = 'index.php?option=com_jgive&view=campaign&layout=default&id=' . $cdata['campaign']->id;
$campaign_link = Uri::root() . substr(Route::_($campaign_link), strlen(Uri::base(true)) + 1);

// Replace campaign share heading
$inviter_name = '';

if ($connection_data->id != 0)
{
	$inviter_name = Factory::getUser($connection_data->id)->name;
}

$find = "[campaign_share_heading]";
$replacewith  = '' . Text::_("PLG_JGIVE_INVITEX_YOUR_FRND") . ' <b> ' . $inviter_name . ' </b> ' .
Text::_("PLG_JGIVE_INVITEX_SHARED") . '<br> "<b>' . $cdata['campaign']->title . '</b>" ' . Text::_("PLG_JGIVE_INVITEX_WITH_YOU") . '';

$message_body = str_replace($find, $replacewith, $message_body);

// Replace campaign image
$find         = "[campaign_img]";

$campaignImagePath = Uri::root() . 'media/com_jgive/images/default_campaign.png';

if (!empty($cdata['campaign']->image))
{
	$campaignImagePath  = $cdata['campaign']->image['media_m'];
}

$replacewith  = '<img align="left" alt="" src="' . $campaignImagePath . '" width="100%"
					style="max-width:851px;padding-bottom:0;display:inline!important;
					vertical-align:bottom;
					border:0;outline:none;text-decoration:
					none" class="CToWUd a6T" tabindex="0">';

$message_body = str_replace($find, $replacewith, $message_body);

// Replace campaign description
$find = "[campaign_desc]";

if (strlen(strip_tags($cdata['campaign']->long_description) > 350))
{
	$long_description = substr(strip_tags($cdata['campaign']->long_description), 0, 350) . '...';
}
else
{
	$long_description = $cdata['campaign']->long_description;
}

$replacewith  = $long_description;
$message_body = str_replace($find, $replacewith, $message_body);

// Load media helper
$helperPath = JPATH_SITE . '/components/com_jgive/helpers/video/vimeo.php';

if (!class_exists('helperVideoVimeo'))
{
	JLoader::register('helperVideoVimeo', $helperPath);
	JLoader::load('helperVideoVimeo');
}

// Load media helper
$helperPath = JPATH_SITE . '/components/com_jgive/helpers/media.php';

if (!class_exists('jgivemediaHelper'))
{
	JLoader::register('jgivemediaHelper', $helperPath);
	JLoader::load('jgivemediaHelper');
}

$find        = "[campaign_videos]";
$replacewith = '';

$replacewith .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
				<tbody>
				<tr>';

					// Show videos thumbnails
					$k     = 0;
					$count = count($cdata['video']);

					if ($count > 0)
					{
						// Show videos thumbnails
						foreach ($cdata['video'] as $video)
						{
							$link = Route::_(
							Uri::root() . "index.php?option=com_jgive&view=campaign&layout=default_playvideo&vid=" .
							$video->id . "&type=" . trim($video->type) . "&tmpl=component"
							);

							switch ($video->type)
							{
								case 'video':
									$video_found = 1;
									$thumbSrc    = Uri::root() . $video->thumb_path;
								break;

								case 'youtube' || 'vimeo':
									$video_found = 1;
									$videoId  = JgiveMediaHelper::videoId($video->type, $video->url);
									$thumbSrc = JgiveMediaHelper::videoThumbnail($video->type, $videoId);
								break;
							}

							$replacewith .=	'<td align="center" valign="middle" style="font-family:Arial;font-size:16px;padding:16px" >
												<a  href="' . $link . '" class="modal  thumbnail">
													<img src="' . $thumbSrc . '" data-src="holder.js/300x200" width="100px"/>
												</a>
											</td>';
							$k = $k ++;

							// Show only 4 videos in email not more than that
							if ($k == 4)
							{
								break;
							}
						}
					}

$replacewith .= '</tr>
			</tbody>
		</table>';

$message_body = str_replace($find, $replacewith, $message_body);

// Replace campaign donate button
$find         = "[campaign_donate_button]";
$replacewith  = $campaign_link;
$message_body = str_replace($find, $replacewith, $message_body);

echo $message_body;
