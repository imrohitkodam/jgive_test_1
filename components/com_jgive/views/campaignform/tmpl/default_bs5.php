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

use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

if (JVERSION < '4.0.0') {
	HTMLHelper::_('behavior.calendar');
}

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::script('media/com_tjfields/js/tjfields.js');
HTMLHelper::_('stylesheet', 'https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css');
HTMLHelper::_('script', 'https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js');
$mediaGalleryObj = 0;

if ($this->allowed == 1) {
	if ($this->checkVendorApproval) {
		if ($this->vendorCheck) {
			if ($this->vendorProfileStatus < 100) {
				?>
				<div class="alert alert-warning">
					<?php echo Text::_('COM_JGIVE_USER_VENDOR_INCOMPLETE_PROFILE'); ?>
					<a href="<?php echo $this->editVendor; ?>"
						target="_blank"><?php echo Text::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_1'); ?></a>
				</div>
				<?php
			} else {
				?>
				<div class="alert alert-info">
					<?php echo Text::_('COM_JGIVE_USER_VENDOR_COMPLETE_PROFILE'); ?>
					<a href="<?php echo $this->editVendor; ?>"
						target="_blank"><?php echo Text::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_1'); ?></a>
				</div>
				<?php
			}
		} else {
			$newVendorForm = Uri::root() . substr(
				Route::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=com_jgive&Itemid=' . $this->vendorProfileMenuId),
				strlen(Uri::base(true)) + 1
			);
			?>
			<div class="alert alert-warning">
				<?php echo Text::_('COM_JGIVE_USER_NOT_VENDOR'); ?>
				<a href="<?php echo $newVendorForm; ?>"
					target="_blank"><?php echo Text::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_1'); ?></a>
			</div>
			<?php
		}
		?>
		<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS; ?>">
			<form action="" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data"
				class="form-validate launchForm">
				<div class="row">
					<div class="col-xs-12">
						<h1 class="fs-title af-mt-10">
							<strong>
								<?php echo Text::_('COM_JGIVE_CAMPAIGN_FORM_LAUNCH_A_CAMPAIGN'); ?>
							</strong>
						</h1>
						<div>&nbsp;</div>
						<?php
						if ($this->checkGatewayDetails === true && ($this->send_payments_to_owner == 1 || in_array('adaptive_paypal', $this->adaptivePayment))) {
							?>
							<div class="alert alert-warning">
								<?php
								$vendor_id = $this->vendorCheck;
								$link = 'index.php?option=com_tjvendors&view=vendor&layout=profile&client=com_jgive';
								echo Text::_('COM_JGIVE_PAYMENT_DETAILS_ERROR_MSG1');
								?>
								<a href="<?php echo Route::_($link . '&vendor_id=' . $vendor_id, false); ?>" target="_blank">
									<?php echo Text::_('COM_JGIVE_VENDOR_FORM_LINK'); ?></a>
								<?php echo Text::_('COM_JGIVE_PAYMENT_DETAILS_ERROR_MSG2'); ?>
							</div>
							<?php
						}
						?>
					</div>
					<?php echo $this->loadTemplate('campaignform_bs5'); ?>
				</div>

				<!--Tab-->
				<div class="row">
					<div class="col-xs-12">
						<div class="af-my-20">
							<ul id="launchForm_nav" class="list-unstyled launchForm__nav nav nav-tabs af-d-flex">
								<?php
								if (!empty($this->hideFields) && in_array('give_back', $this->hideFields) && $this->hideShowFields == 1) {
								} else {
									?>
									<li class="nav-item launchForm__panel">
										<a class="nav-link af-font-bold text-primary active" data-bs-toggle="tab"
											data-bs-target="#give-away">
											<span>
												<i class="fa fa-gift" aria-hidden="true"></i>
											</span>
											<?php echo Text::_('COM_JGIVE_CAMPAIGN_FORM_TAB_GIVE_AWAY'); ?>
										</a>
									</li>
									<?php
								}

								if ($this->imageGallery || $this->videoGallery) {
									?>
									<li class="nav-item launchForm__panel">
										<a data-bs-toggle="tab" data-bs-target="#gallery"
											class="nav-link af-font-bold text-primary">
											<span>
												<i class="fa fa-picture-o" aria-hidden="true"></i>
											</span>
											<?php echo Text::_('COM_JGIVE_CAMPAIGN_FORM_TAB_GALLERY'); ?>
										</a>
									</li>
									<?php
								}

								if ($this->form_extra) {
									?>
									<li class="nav-item launchForm__panel">
										<a data-bs-toggle="tab" data-bs-target="#extraFields"
											class="nav-link af-font-bold text-primary">
											<span>
												<i class="fa fa-info-circle" aria-hidden="true"></i>
											</span>
											<?php echo Text::_('COM_JGIVE_CAMPAIGN_FORM_TAB_EXTRA_FIELDS'); ?>
										</a>
									</li>
									<?php
								}
								// Show the tab only if beneficiary stories tab not hidden from config
								if (!empty($this->hideFields) && in_array('beneficiary_stories', $this->hideFields) && $this->hideShowFields == 1) {
								} else {
									?>
									<li class="nav-item launchForm__panel">
										<a class="nav-link af-font-bold text-primary" data-bs-toggle="tab"
											data-bs-target="#beneficiary-stories">
											<span>
												<i aria-hidden="true"></i>
											</span>
											<?php echo Text::_('COM_JGIVE_CAMPAIGN_FORM_TAB_BENEFICIARY_STORIES'); ?>
										</a>
									</li>
									<?php
								}
								?>
							</ul>
						</div>

						<div class="tab-content af-mb-15">
							<!---Tab - give away---->
							<?php
							if (!empty($this->hideFields) && in_array('give_back', $this->hideFields) && $this->hideShowFields == 1) {
							} else {
								?>
								<div id="give-away" class="tab-pane active">
									<div class="row">
										<div class="col-xs-12 col-sm-12">
											<div>
												<?php $this->form->setFieldAttribute('givebacks', 'layout', 'JGive.GiveBacksSubform.layouts.subform.bs5.repeatable'); ?>
												<?php echo $this->form->getInput('givebacks'); ?>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							?>

							<!---Tab - beneficiary stories---->
							<?php
							if (!empty($this->hideFields) && in_array('beneficiary_stories', $this->hideFields) && $this->hideShowFields == 1) {
							} else {
								?>
								<div id="beneficiary-stories" class="tab-pane">
									<div class="row">
										<div class="col-xs-12 col-sm-12">
											<div>
												<?php $this->form->setFieldAttribute('beneficiaryStories', 'layout', 'JGive.GiveBacksSubform.layouts.subform.bs5.repeatable'); ?>
												<?php echo $this->form->getInput('beneficiaryStories'); ?>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							?>

							<!---Tab - Gallery---->
							<?php
							if ($this->imageGallery || $this->videoGallery) {
								?>
								<div id="gallery" class="tab-pane fade">
									<div class="row">
										<div class="col-xs-12">
											<div class="alert alert-info">
												<span><?php echo $this->form->getInput('video_on_details_page'); ?>
													&nbsp;&nbsp;</span>
												<span><?php echo $this->form->getLabel('video_on_details_page'); ?></span>
											</div>
										</div>
									</div>
									<div class="row" id="display_errors">
									</div>
									<div class="row">
										<?php
										if ($this->imageGallery) {
											?>
											<div class="col-xs-12 col-md-6">
												<div class="form-group">
													<?php echo $this->form->getLabel('gallery_file'); ?>
													<?php echo $this->form->getInput('gallery_file'); ?>
												</div>
											</div>
											<?php
										}

										if ($this->videoGallery) {
											?>
											<div class="col-xs-12 col-md-6 af-mb-10">
												<div class="form-group col-xs-6">
													<?php echo $this->form->getLabel('gallery_link'); ?>
													<?php echo $this->form->getInput('gallery_link'); ?>
												</div>
												<div class="form-group col-xs-6">
													<br>
													<input type="button"
														class="validate_video_link btn btn-primary btn-success btn-donate af-mt-5"
														onclick="tjMediaFile.validateFile(this,1, <?php echo $this->isAdmin; ?>)"
														value="<?php echo Text::_('COM_JGIVE_ADD_VALIDATE_VIDEO_LINK'); ?>">
												</div>
											</div>
											<?php
										}

										$token = Session::getFormToken();
										?>
										<div class="col-xs-12">
											<div class="subform-wrapper">
												<div id="gallery_media" class="media_gallary_parent row">
													<div
														class="clone_media hide_jgdiv col-xs-6 col-sm-4 col-md-3 af-my-15 hoverEffect">
														<div class="overlay">
															<button class="btn p-0" id="delete_media"
																onclick="tjMediaFile.tjMediaGallery.deleteMedia(this, <?php echo $this->isAdmin; ?>, '<?php echo $token; ?>', 'com_jgive.campaign', '<?php echo $this->item->id ?>');return false;">
																<i class="fa fa-times-circle text-danger" aria-hidden="true"></i>
															</button>
															<input type="radio" id="jform_default_video" name="jform[default_video]"
																onchange="tjMediaFile.defaultMedia(this, <?php echo $this->item->id ? $this->item->id : 0; ?>,<?php echo $this->isAdmin; ?>);return false;"
																class=" pull-right p-0" value="" />
														</div>
														<input type="hidden" name="jform[gallery_file][media][]"
															class="media_field_value media__video" value="">
														<div class="thumbnail af-mb-0 af-border-0 img-responsive">
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							?>
							<!---Tab - Extra fields---->
							<?php
							if ($this->form_extra) {
								?>
								<div id="extraFields" class="tab-pane fade">
									<div class="row">
										<div class="col-xs-12">
											<?php
											if (empty($this->item->id)) {
												?>
												<div class="alert alert-info">
													<?php echo Text::_('COM_JGIVE_CAMPAIGN_OTHER_DETAILS_SAVE_PROD_MSG'); ?>
												</div>
												<?php
											} elseif ($this->form_extra) {
												echo $this->loadTemplate('extrafields_bs5');
											}
											?>
										</div>
									</div>
								</div>
								<?php
							} ?>
						</div>
					</div>
				</div>
				<div class="row">
					<?php
					if ($this->params->get('enable_campaign_terms_conditions') && !empty($this->params->get('camp_create_terms_article'))) {
						$link = '';

						if ($this->params->get('camp_create_terms_article')) {
							$link = Uri::root() . "index.php?option=com_content&view=article&id=" . $this->params->get('camp_create_terms_article') . "&tmpl=component";
						}

						if ($link) {
							$checked = '';

							if (isset($this->item->terms_condition) && $this->item->terms_condition != false) {
								$checked = 'checked';
							}
							?>
							<div class="col-xs-12 terms" id='launch-campaign-modal'>
								<div class="form-group">
									<label>
										<input class="af-mb-10" type="checkbox" name="jform[terms_condition]" id="jform_terms_condition"
											size="30" <?php echo $checked ?> required />
										<?php echo Text::_('COM_JGIVE_ACCEPT_USER_TERMS_CONDITIONS_FIRST'); ?>
										<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link; ?>" class="modal">
											<?php echo Text::_('COM_JGIVE_PRIVACY_POLICY'); ?>
										</a><?php echo Text::_('COM_JGIVE_ACCEPT_USER_TERMS_CONDITIONS_LAST'); ?>
									</label>
								</div>
							</div>
							<?php
						}
					}
					?>
				</div>
				<div class="row  af-mt-20">
					<div class="col-xs-12 form-group">
						<button type="button" class="btn btn-primary float-end ms-2"
							onclick="jgiveCommon.campaignForm.save('campaignform.save')">
							<span>
								<?php echo Text::_('JSUBMIT'); ?>
							</span>
						</button>

						<button type="button" class="btn btn-secondary float-end ms-2"
							onclick="Joomla.submitbutton('campaignform.cancel')">
							<span><?php echo Text::_('JCANCEL'); ?></span>
						</button>
					</div>
				</div>
				<!---Tab End-->
				<input type="hidden" name="option" value="com_jgive" />
				<input type="hidden" name="task" id="task" value="campaign.save" />
				<input type="hidden" name="jform[id]" value="<?php
				if (!empty($this->item->id)) {
					echo $this->item->id;
				}
				?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
		<?php
		if (!empty($this->item->gallery)) {
			$mediaGalleryObj = json_encode($this->item->gallery);
		}
	} else {
		?>
		<div class="alert alert-info">
			<?php echo Text::_('COM_JGIVE_VENDOR_NOT_APPROVED_MESSAGE'); ?>
		</div>
		<?php
	}
} else {
	?>
	<div class="alert alert-info alert-help-inline">
		<?php echo Text::_('COM_JGIVE_VENDOR_ENFORCEMENT_ERROR'); ?>
		<?php echo Text::_('COM_JGIVE_VENDOR_ENFORCEMENT_CAMPAIGN_REDIRECT_MESSAGE'); ?>
	</div>
	<div>
		<a href="<?php echo Route::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=com_jgive'); ?>"
			target="_blank">
			<button class="btn btn-primary">
				<?php echo Text::_('COM_JGIVE_VENDOR_ENFORCEMENT_CAMPAIGN_REDIRECT_LINK'); ?>
			</button>
		</a>
	</div>
	<?php
}
?>

<script type="text/javascript">
	var mediaGallery = <?php echo $mediaGalleryObj; ?>;
	var jgive_baseurl = '<?php echo Uri::root(); ?>';
	var campaignGalleryImage = '<?php echo $this->campaignGalleryImage; ?>';
	var campaignMainImage = '<?php echo $this->campaignMainImage; ?>';
	var isAdmin = '<?php echo $this->isAdmin; ?>';
	var imageUploadLimit = '<?php echo $this->imageUploadLimit; ?>';
	var videoUploadLimit = '<?php echo $this->videoUploadLimit; ?>';
	var allowedFileExtensions = '<?php echo $this->allowedFileExtensions; ?>';
	const allowedMediaCount = '<?php echo $this->allowedMediaCount; ?>';
	const allowedVideoCount = '<?php echo $this->allowedVideoCount; ?>';
	const jgiveBaseUrl = '<?php echo Uri::root(); ?>';
	const termsConditionsConfig = "<?php echo $this->params->get('enable_campaign_terms_conditions', 0); ?>";
	const termsConditionsArticleId = "<?php echo $this->params->get('camp_create_terms_article', 0); ?>"
	jgive.campaign.init();
</script>