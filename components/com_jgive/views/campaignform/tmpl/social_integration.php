<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2021 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?> row justify-content-md-center">
	<div class="col-md-6 col-sm-6 col-md-offset-3 col-sm-offset-3  af-pt-20 border-b af-bg-faded campaignSharingOptions">
		<div class="alert alert-success" id="copyCampaignLinkMsg" style="display: none;">
			<em><?php echo Text::_('COM_JGIVE_CAMPAIGN_LINK_COPIED_SUCCESSFULLY'); ?></em>
		</div>

		<div class="af-text-center">
			<a id="myLink" href="<?php echo $this->campaignLink; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-md btn-primary af-mb-20 campaignDetailPage">
				<?php echo Text::_('COM_JGIVE_CAMPAIGN_DETAIL_BUTON'); ?>
			</a>
		</div>
		
		<h5 class="af-mt-0 af-font-bold fs-title af-text-center">
			<?php echo Text::_('COM_JGIVE_CAMPAIGN_LINK_COPY_AND_SHARE'); ?>
		</h5>

		<div class="col-md-12 af-text-center">
			<input type="text" id="campaignLink" value="<?php echo $this->campaignLink ?>" class="af-d-inline-block">
			<button class="btn btn-md" onclick="jgiveCommon.copyShareLink()"><i class="fa fa-link"></i></button>
		</div>

		<div class="clearfix"></div>
		
		<?php
		if ($this->allowVendorToShareCampaign && !empty($this->socialSharingOptions)) {
		?>
			<?php
			foreach ($this->socialSharingOptions as $key => $value) {
			?>
				<div class="row mt-4">
					<div class="col-md-12 col-xs-12 col-md-offset-3 col-xs-offset-2 ">
						<ul class="list-inline af-text-center af-px-15 row">
							<?php
							/*if ($value == 'sms')
							{
								$emailurl = "https://mail.google.com/mail/?view=cm&fs=1&tf=1&body=" . $this->encodedUrl;
								?>
								<li class="col-sm-5 col-xs-2">
									<i class="fa fa-envelope"></i>
								</li>
								<li class="col-sm-1 col-xs-1">
									<?php echo ucfirst($value);?>
								</li>
								<li class="col-sm-6 col-xs-6">
									<a 
									target="_blank"
									rel="noopener noreferrer"
									href="<?php echo $emailurl;?>"
									class="btn btn-default btn-sm campaignSharebutton">
										<?php echo Text::_("COM_JGIVE_SHARE_BTN")?>
									</a>
								</li>
								<?php
							}*/

							if ($value == 'email') {
								$emailurl = "mailto:?subject=" . $this->campaignTitle . "&body=" . $this->encodedUrl;
							?>
								<li class="col-sm-5 col-xs-2">
									<i class="fa fa-envelope"></i>
								</li>
								<li class="col-sm-1 col-xs-1">
									<?php echo ucfirst($value); ?>
								</li>
								<li class="col-sm-6 col-xs-6">
									<a href="<?php echo $emailurl; ?>" class="btn btn-default btn-sm campaignSharebutton">
										<?php echo Text::_("COM_JGIVE_SHARE_BTN") ?>
									</a>
								</li>
							<?php
							}

							if ($value == 'facebook') {
								$fburl = "https://www.facebook.com/sharer/sharer.php?u=" . $this->encodedUrl;
							?>
								<li class="col-sm-5 col-xs-2">
									<i class="fa fa-facebook-official"></i>
								</li>
								<li class="col-sm-1 col-xs-1">
									<?php echo ucfirst($value); ?>
								</li>
								<li class="col-sm-6 col-xs-6">
									<a href="<?php echo $fburl ?>" class="btn btn-default btn-sm campaignSharebutton" target="_blank" rel="noopener noreferrer">
										<?php echo Text::_("COM_JGIVE_SHARE_BTN") ?>
									</a>
								</li>
							<?php
							}

							if ($value == 'twitter') {
							?>
								<li class="col-sm-5 col-xs-2">
									<i class="fa fa-twitter-square"></i>
								</li>
								<li class="col-sm-1 col-xs-1">
									<?php echo ucfirst($value); ?>
								</li>
								<li class="col-sm-6 col-xs-6">
									<?php $twittURL = 'https://twitter.com/intent/tweet?text=' . $this->campaignTitle . '&url=' . $this->encodedUrl; ?>
									<a href="<?php echo $twittURL; ?>" class="btn btn-default btn-sm campaignSharebutton" data-url="<?php echo $this->encodedUrl ?>" data-counturl="<?php echo $this->encodedUrl ?>" target="_blank" rel="noopener noreferrer">
										<?php echo Text::_("COM_JGIVE_SHARE_BTN") ?>
									</a>
								</li>
							<?php
							}

							if ($value == 'pinterest') {
								$pinteresturl = 'http://pinterest.com/pin/create/link/?url=' . $this->encodedUrl . '&description=' . $this->campaignTitle;
							?>
								<li class="col-sm-5 col-xs-2">
									<i class="fa fa-pinterest-square"></i>
								</li>
								<li class="col-sm-1 col-xs-1">
									<?php echo ucfirst($value); ?>
								</li>
								<li class="col-sm-6 col-xs-6">
									<a href="<?php echo $pinteresturl ?>" class="btn btn-default btn-sm campaignSharebutton" target="_blank" rel="noopener noreferrer">
										<?php echo Text::_("COM_JGIVE_SHARE_BTN") ?>
									</a>
								</li>
							<?php
							}

							if ($value == 'linkedin') {
							?>
								<li class="col-sm-5 col-xs-2">
									<i class="fa fa-linkedin"></i>
								</li>
								<li class="col-sm-1 col-xs-1">
									<?php echo ucfirst($value); ?>
								</li>
								<li class="col-sm-6 col-xs-6">
									<a href="<?php echo 'https://www.linkedin.com/shareArticle?mini=true&url= ' . $this->encodedUrl . ' &title=' . $this->campaignTitle . '&source=LinkedIn' ?>" class="btn btn-default btn-sm campaignSharebutton" target="_blank" rel="noopener noreferrer">
										<?php echo Text::_("COM_JGIVE_SHARE_BTN") ?>
									</a>
								</li>
							<?php
							}

							if ($value == 'whatsapp') {
							?>
								<li class="col-sm-5 col-xs-2">
									<i class="fa fa-whatsapp"></i>
								</li>
								<li class="col-sm-1 col-xs-1">
									<?php echo ucfirst($value); ?>
								</li>
								<li class="col-sm-6 col-xs-6">
									<?php $whatsapplink = 'https://api.whatsapp.com/send?text=' . $this->encodedUrl?>
									<a 
									href="<?php echo $whatsapplink;?>" 
									data-action="share/whatsapp/share"
									class="btn btn-default btn-sm campaignSharebutton"
									target="_blank"
									rel="noopener noreferrer">
										<?php echo Text::_("COM_JGIVE_SHARE_BTN")?>
									</a>
								</li>
							<?php
							}
							?>
						</ul>
					</div>
				</div>
				<div class="clearfix"></div>
				<hr class="af-my-10">
			<?php
			}
		}
		?>
	</div>
</div>
