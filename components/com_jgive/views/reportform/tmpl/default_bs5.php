<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.formvalidator');

$reportId = (int) $this->item->id;
$reportAttachmentCount = 0;

?>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?> container">
	<form action="" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate launchForm">
		<div class="row">
			<div class="col-xs-12">
				<h1 class="fs-title af-mt-10">
					<strong>
						<?php
						if ($reportId == 0)
						{
							echo Text::_('COM_JGIVE_BUTTON_CREATE_REPORT');
						}
						else
						{
							echo Text::_('COM_JGIVE_REPORT_EDIT');
						}
						?>
					</strong>
				</h1>
			</div>
			<div class="col-xs-12 col-md-12">
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $this->form->getLabel('title'); ?>
							<?php echo $this->form->getInput('title'); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
							<?php echo $this->form->getLabel('description'); ?>
							<?php echo $this->form->getInput('description'); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<?php
						$reportMedia = Uri::root() . 'media/com_jgive/images/logo.png';

						if (isset($this->item->mediaData->media_m))
						{
							$reportMedia = $this->item->mediaData->media_m;
						}
					?>
					<div class="col-xs-12 col-sm-4 col-md-3 ">
						<div class="form-group jgive_campaign_upload">
							<img src="<?php echo $reportMedia; ?>" id="uploaded_media" class="img-responsive">
						</div>
					</div>
					<div class="col-xs-12 col-sm-4">
						<div class="form-group">
							<?php echo $this->form->getLabel('image'); ?>
							<?php echo $this->form->getInput('image'); ?>
						</div>
						<div class="col-xs-12 alert alert-info">
							<p><?php echo Text::sprintf('COM_JGIVE_ALLOWED_ATTACHMENTS_FILE_TYPE', 'jpeg,png,jpg');?></p>
						</div>
					</div>
					<div class="col-xs-12 col-md-4">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label
										id="jform_attachments-lbl"
										for="jform_attachments"
										class="hasPopover"
										title=""
										data-content="<?php echo Text::_("COM_JGIVE_REPORT_ADD_ATTACHMENT");?>"
										data-original-title="<?php echo Text::_("COM_JGIVE_REPORT_ATTACHMENTS");?>">
										<strong><?php echo Text::_('COM_JGIVE_REPORT_ATTACHMENTS')?></strong>
									</label>
								</div>
							</div>
						</div>
						<?php
						if (isset($this->item->mediaAttachmentData) && count($this->item->mediaAttachmentData) > 0)
						{
							$reportAttachmentCount = count($this->item->mediaAttachmentData);
							$token = Session::getFormToken();
							?>
							<div class="row">
								<?php
								foreach ($this->item->mediaAttachmentData as $attachment)
								{
									$downloadAttachmentLink = Uri::root() . 'index.php?option=com_jgive&task=report.downloadAttachment&' . Session::getFormToken() . '=1' .
									'&id=' . $attachment->id . '&reportId=' . $this->item->id;
								?>
										<div class="col-xs-9">
											<a
											href="<?php echo $downloadAttachmentLink;?>"
											target=""
											title="<?php echo $attachment->original_filename;?>">
												<?php echo $attachment->original_filename;?>
											</a>
										</div>
										<div class="col-xs-3">
											<a href="JavaScript:void(0);">
											<i class="fa fa-trash"
											title="<?php echo Text::_('COM_JGIVE_REPORT_ATTACHMENT_DELETE');?>"
											data-mid="<?php echo $attachment->id;?>"
											data-rid="<?php echo $this->item->id;?>"
											data-cid="<?php echo $this->item->campaign_id;?>"
											onclick="jgive.report.deleteReportAttachment('reportform.deleteAttachment', this, '<?php echo $token ?>')"></i></a>
										</div>
								<?php
								}?>
							</div>
						<?php
						}
						?>
						<div class="row jgreports__attachemts">
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<button class="btn btn-success btn-sm" type="button" id="addAttachment" onclick="jgive.report.addAttachment();" title="Add">
										<i class="fa fa-plus"></i>
									</button>
								</div>
							</div>
							<div class="col-xs-12 alert alert-info">
								<p><?php echo Text::sprintf('COM_JGIVE_ALLOWED_ATTACHMENTS_FILE_TYPE', $this->allowed_report_attachments);?>
								</p>
								<p><?php echo Text::sprintf('COM_JGIVE_ALLOWED_ATTACHMENTS_FILE_SIZE', $this->max_report_attachment_size);?></p>
							</div>
						</div>
						<div class="jgreports__attachemts-clone-div af-d-none">
							<div class="jgreports__attachemts-clone col-xs-12 form-group">
								<div class="row">
									<div class="col-xs-9 col-md-9">
										<input type="file" name="jform[attachments][]" id="jform_attachments" onchange="jgive.report.validateFile(this)">
										<input type="hidden" name="jform[attachmentsvalue][]" id="jform_attachmentsvalue" onchange="jgive.report.validateFile(this)">
									</div>
									<div class="col-xs-3 col-md-3">
										<button class="btn btn-danger btn-sm" type="button" name= "jform[removeAttachment][]"
											id="jform_removeAttachment"
											onclick="jgive.report.removeAttachment(this, '<?php echo Session::getFormToken();?>', 'com_jgive.reportAttachment', '<?php echo $reportId;?>');"
											title="Remove">
											<i class="fa fa-minus"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 form-group">
						<button type="button" class="validate btn btn-primary" onclick="jgiveCommon.reportForm.submit('reportform.save');">
							<span class="icon-ok"></span><?php echo Text::_('JSAVE') ?>
						</button>
					
						<button type="button" class="btn btn-default" onclick="Joomla.submitbutton('reportform.cancel')">
						<?php echo Text::_('JCANCEL') ?>
						</button>
					</div>

				</div>
			</div>
			<input type="hidden" name="task" />
			<input type="hidden" name="option" value="com_jgive" />
			<input type="hidden" name="jform[media_id_old]" value="<?php echo isset($this->item->mediaData->id)?$this->item->mediaData->id:''; ?>" />
			<div id="reportFormtoken">
			<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>

<script>
var uploadFileDivId         = 0;
var reportAttachmentCount   = '<?php echo $reportAttachmentCount;?>';
var jgive_baseurl           = '<?php echo Uri::root()?>';
var isAdmin                 = '<?php echo $this->isAdmin?>';
var imageUploadLimit        = '<?php echo $this->imageUploadLimit?>';
var campaignMainImage       = '<?php echo $this->campaignMainImage;?>';
const allowedFileExtensions = '<?php echo $this->allowedFileExtensions?>';
const allowedMediaCount     = '<?php echo $this->allowedMediaCount?>';
const allowedAttachments    = '<?php echo $this->allowed_report_attachments;?>';
const attachmentMaxSize     = '<?php echo $this->max_report_attachment_size;?>';
const attachmentMaxLimit    = '<?php echo $this->max_report_attachments;?>';
</script>
