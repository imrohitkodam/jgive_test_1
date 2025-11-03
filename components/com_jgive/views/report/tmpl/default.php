<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

?>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div class="row">
		<div class="col-xs-12 af-mb-15 campaignBack">
			<a href="<?php echo $this->item->campaignUrl;?>" class="text-muted af-font-bold">
				<i class="fa fa-angle-left af-pr-5 af-font-bold" aria-hidden="true"></i>
				<span><?php echo Text::_('COM_JGIVE_REPORT_BACK_TO_CAMPAIGN');?></span>
			</a>
		</div>

		<div class="col-xs-12">
			<h1><?php echo $this->item->title;?></h1>
			<?php
				if (isset($this->item->reportImage->media_l))
				{
					$reportImage = $this->item->reportImage->media_l;
				}
				else
				{
					$reportImage = Uri::root() . 'media/com_jgive/images/logo.png';
				}
			?>
			<img id="report__coverimage" src="<?php echo $reportImage;?>" class="img-responsive" alt="" />

			<?php echo $this->loadTemplate('jlike');?>

			<div class="col-xs-12 reports__content--wordwrap">
				<?php echo $this->item->description;?>
			</div>

			<?php
				if (isset($this->item->mediaAttachmentData) && count($this->item->mediaAttachmentData) > 0)
				{
				?>
					<div class="col-xs-12">
						<strong><?php echo Text::_('COM_JGIVE_REPORT_ATTACHMENTS');?></strong>
					</div>
					<div class="col-xs-12">
						<ul>
							<?php
							$i = 1;

							foreach ($this->item->mediaAttachmentData as $attachment)
							{
								$downloadAttachmentLink = Uri::root() . 'index.php?option=com_jgive&task=report.downloadAttachment&' .
								Session::getFormToken() . '=1' . '&id=' . $attachment->id . '&reportId=' . $this->item->id;
							?>
								<li>
									<span><i class="fa fa-download" aria-hidden="true"></i></span>
									<a
										href="<?php echo $downloadAttachmentLink;?>"
										target=""
										title="<?php echo $attachment->original_filename;?>">
										<?php echo Text::sprintf('COM_JGIVE_REPORT_ATTACHMENT', $i);?>
									</a>
								</li>
							<?php
								$i++;
							}?>
						</ul>
					</div>
				<?php
				}
			?>
			<div class="col-xs-12">
				<p><?php echo $this->item->event->afterDisplayContent;?></p>
			</div>
		</div>
	</div>
</div>
