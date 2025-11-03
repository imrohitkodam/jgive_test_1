<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');

$mediaGalleryObj = 0;

if (!empty($this->item->gallery))
{
	$mediaGalleryObj = json_encode($this->item->gallery);
}

HTMLHelper::script('media/com_tjfields/js/tjfields.js');
?>
<style>
#jgiveWrapper .field-calendar .btn, #jgiveWrapper input[type="number"] {
	height:  inherit !important;
}

#jgiveWrapper .field-calendar .btn {
	padding: 10px;
}
</style>

<form action="<?php echo Route::_('index.php?option=com_jgive&view=campaign&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<div class="row campaignForm" id="jgiveWrapper">
			<?php
			echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));
				echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_JGIVE_CAMPAIGN_TAB_DETAILS', true));
					echo $this->loadTemplate('details_bs5');
				echo HTMLHelper::_('bootstrap.endTab');

				// Tab for giveaways.
				if (in_array('give_back', $this->hideFields) && $this->hideShowFields == 1)
				{
				}
				else
				{
					echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'giveaways', Text::_('COM_JGIVE_CAMPAIGN_FORM_GIVEBACKS', true));
					?><br><?php
					echo $this->form->renderField('givebacks');
					echo HTMLHelper::_('bootstrap.endTab');
				}

				if ($this->imageGallery || $this->videoGallery)
				{
					echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'gallery', Text::_('COM_JGIVE_CAMPAIGN_GALLERY', true));
				?>

						<div class="alert alert-info">
							<?php echo $this->form->getInput('video_on_details_page');?>
							<?php echo $this->form->getLabel('video_on_details_page');?>
						</div>
						<div id="display_errors"></div>
						<div class="row">
							<?php
							if ($this->imageGallery)
							{
								?>
								<div class="col-md-6 well">
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('gallery_file'); ?></div>
										<div class="controls">
											<?php echo $this->form->getInput('gallery_file'); ?>
										</div>
									</div>
								</div>
								<?php
							}

							if ($this->videoGallery)
							{
								?>
								<div class="col-md-6 well">
									<div>
										<?php echo $this->form->renderField('gallery_link'); ?>
									</div>
									<div>
										<input type="button" class="validate_video_link float-end" onclick="tjMediaFile.validateFile(this,1, <?php echo $this->isAdmin;?>)"
										value="<?php echo Text::_('COM_TJMEDIA_ADD_VIDEO_LINK');?>">
									</div>
								</div>
								<?php
							}

							$token = Session::getFormToken();
							?>
						</div>

						<div>
							<div class="subform-wrapper">
								<div id="gallery_media" class="media_gallary_parent row">
								  <div class="clone_media hide_jgdiv  col-xs-6 col-sm-4 col-md-2 af-my-10 hoverEffect">
									<div class="overlay af-my-10">
										<button class="btn p-0 af-border-0 btn btn-outline-danger btn-sm" id="delete_media"
											onclick="tjMediaFile.tjMediaGallery.deleteMedia(this, <?php echo $this->isAdmin;?>, '<?php echo $token;?>', 'com_jgive.campaign','<?php echo $this->item->id?>');return false;">
											<span class="icon-cancel"></span>
										</button>
										<input type="radio" id="jform_default_video" name="jform[default_video]" onchange="tjMediaFile.defaultMedia(this, <?php echo $this->item->id?$this->item->id:0;?>)" class="pull-right p-0" value="" />
									</div>
									<input type="hidden" name="jform[gallery_file][media][]" class="media_field_value media__video" value="">
									<div class="thumbnail af-border-0 img-responsive position-relative"></div>
								  </div>
								</div>
							</div>
						</div>
					<?php
					echo HTMLHelper::_('bootstrap.endTab');
				}

				if ($this->form_extra)
				{
					echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'extrafields', Text::_('COM_JGIVE_CAMPAIGN_TAB_EXTRA_FIELDS', true));

					if (empty($this->item->id))
					{
						?>
						<div class="alert alert-info">
							<?php echo Text::_('COM_JGIVE_CAMPAIGN_OTHER_DETAILS_SAVE_PROD_MSG');?>
						</div>
						<?php
					}
					else
					{
						echo $this->loadTemplate('extrafields_bs5');
					}

					echo HTMLHelper::_('bootstrap.endTab');
				}

				// Tab for beneficiary stories
				if (in_array('beneficiary_stories', $this->hideFields) && $this->hideShowFields == 1)
				{
				}
				else
				{
					echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'beneficiaryStories', Text::_('COM_JGIVE_CAMPAIGN_FORM_BENEFICIARY_STORIES', true));
					?><br><?php
					echo $this->form->renderField('beneficiaryStories');
					echo HTMLHelper::_('bootstrap.endTab');
				}

			echo HTMLHelper::_('bootstrap.endTabSet');
		?>
		<?php
		if ($this->com_params->get('enable_campaign_terms_conditions', 0))
		{
			$link='';

			if ($this->com_params->get('camp_create_terms_article', 0))
			{
				$link = Uri::root()."index.php?option=com_content&view=article&id=".$this->com_params->get('camp_create_terms_article', 0)."&tmpl=component";
			}

			if ($link)
			{?>
				<div class="control-group">
					<div class="checkbox">
						<?php
						$checked = '';

						if (!empty($this->item->terms_condition))
						{
							$checked = 'checked';
						}
						?>
						<label for="terms_condition">
							<input class="af-mb-10 terms" type="checkbox" name="terms_condition" id="terms_condition" size="30" <?php echo $checked ?> required/>
							<?php echo Text::_('COM_JGIVE_ACCEPT_USER_TERMS_CONDITIONS_FIRST');?>
							<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link;?>" class="modal">
								<?php echo Text::_('COM_JGIVE_PRIVACY_POLICY');?>
							</a><?php echo Text::_('COM_JGIVE_ACCEPT_USER_TERMS_CONDITIONS_LAST');?>
						</label>
					</div>
				</div>
			<?php
			}
		}?>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" id="jform_id" name="jform[id]" value="<?php if (!empty($this->item->id)){ echo $this->item->id;}?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</div>
</form>
<script type="text/javascript">
	var mediaGallery          = <?php echo $mediaGalleryObj; ?>;
	var jgive_baseurl         = '<?php echo Uri::root(); ?>';
	var campaignGalleryImage  = '<?php echo $this->campaignGalleryImage; ?>';
	var campaignMainImage     = '<?php echo $this->campaignMainImage; ?>';
	var isAdmin               = '<?php echo $this->isAdmin; ?>';
	var imageUploadLimit      = '<?php echo $this->imageUploadLimit; ?>';
	var videoUploadLimit      = '<?php echo $this->videoUploadLimit; ?>';
	var allowedFileExtensions = '<?php echo $this->allowedFileExtensions; ?>';
	var jsGroupId             = '<?php echo $this->item->js_groupid; ?>';
	var integration           = '<?php echo $this->integration; ?>';
	const allowedMediaCount   = '<?php echo $this->allowedMediaCount;?>';
	const allowedVideoCount   = '<?php echo $this->allowedVideoCount;?>';
	const jgiveBaseUrl        = '<?php echo Uri::base();?>';
	jgiveAdmin.campaign.init();
</script>
