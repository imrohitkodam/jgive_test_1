<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.renderModal', 'a.modal');

$document = Factory::getDocument();
HTMLHelper::_('stylesheet', 'media/com_jgive/vendors/css/magnific-popup.css');

if (isset($this->item['campaign']->gallery))
{
	$campaignVideoData = array();
	$campaignImageData = array();

	for ($i = 0; $i <= count($this->item['campaign']->gallery); $i++)
	{
		if (isset($this->item['campaign']->gallery[$i]['type']))
		{
			$campaignContentType = substr($this->item['campaign']->gallery[$i]['type'], 0, 5);

			if ($campaignContentType == 'image')
			{
				$campaignImageData[$i] = $this->item['campaign']->gallery[$i];
			}
			elseif ($campaignContentType == 'video')
			{
				$campaignVideoData[$i] = $this->item['campaign']->gallery[$i];
			}
		}
	}
	?>

	<div class="row af-mb-15">
		<?php
		if ($this->params->get('video_gallery') && $this->params->get('img_gallery') && !empty($campaignVideoData) && !empty($campaignImageData))
		{
		?>
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 gallary-filters">
				<div class="pull-right">
					<select id="gallary_filter" class="form-select">
						<option value="0"><?php echo Text::_('COM_JGIVE_CAMP_TYPE');?></option>
						<option value="1"><?php echo Text::_('COM_JGIVE_SINGLE_GALLERY_VIDEOS');?></option>
						<option value="2"><?php echo Text::_('COM_JGIVE_SINGLE_GALLERY_IMAGES');?></option>
					</select>
				</div>
			</div>
		<?php
		}
		?>
	</div>

	<?php
	if (!empty($campaignVideoData) && $this->params->get('video_gallery'))
	{
	?>
		<div class="row af-mb-15">
			<div class="col-xs-12 col-sm-6 af-mt-20 videosText af-font-bold text-uppercase">
				<?php echo Text::_('COM_JGIVE_SINGLE_GALLERY_VIDEOS');?>
			</div>
		</div>
		<div id="videos">
			<div id="campaignVideo">
				<div class="campaignVideo">
					<div class="media row" id="jg_video_gallery">
						<?php
						foreach ($campaignVideoData as $campaignVideo)
						{
							$campaignVideoType = substr($campaignVideo['type'], 6);
							$videoId  = JGiveMediaHelper::videoId($campaignVideoType, $campaignVideo['media']);
							$thumbSrc = JGiveMediaHelper::videoThumbnail($campaignVideoType, $videoId);
							?>
							<div class="col-xs-6 col-sm-3 af-mb-15 af-relative jg_gallery_image_item">
								<a class="tjmodal" onclick="jgive.campaignDetails.playVideo('<?php echo $campaignVideo["media"]?>')">
									<img src="<?php echo Uri::root(true) . '/media/com_jgive/images/play_icon.png';?>"class="play_icon af-center-xy af-absolute af-transform"/>
									<img src="<?php echo $thumbSrc; ?>" width="100%"/>
								</a>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	if (!empty($campaignImageData) && $this->params->get('img_gallery'))
	{
	?>
		<div class="row af-mb-15">
			<div class="col-xs-12 col-sm-6 af-mt-20 imagesText af-font-bold text-uppercase">
				<?php echo Text::_('COM_JGIVE_SINGLE_GALLERY_IMAGES');?>
			</div>
		</div>
		<div id="images">
			<div id="campaignImages">
				<div class="row">
					<div class="media" id="jg_image_gallery">
						<div class="popup-gallery row">
							<?php
							foreach ($campaignImageData as $campaignImage)
							{
								$img_path = $campaignImage[$this->params->get('front_campaign_gallery_view')];
								?>
								<div class="col-xs-6 col-sm-3 jg_image_item af-mb-20">
									<a href="<?php echo $img_path;?>" title="" class="" >
										<div class="af-d-block bg-center af-bg-contain af-bg-repn af-responsive-embed af-responsive-embed-16by9" style="background-image: url('<?php echo $img_path;?>');"></div>
									</a>
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
	}
}
