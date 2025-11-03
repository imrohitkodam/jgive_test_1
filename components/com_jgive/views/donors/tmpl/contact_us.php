<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Editor\Editor;

HTMLHelper::_('bootstrap.tooltip');

// Menu Item Id for redirecting url
$mainframe = Factory::getApplication();
$input = Factory::getApplication()->input;
$itemid = $input->get('Itemid', '', 'INT');

?>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12">
			<div class="page-header">
				<h2>
					<?php echo Text::_('COM_JGIVE_MASS_MAIL');?>
				</h2>
			</div>
			<div class="col-lg-12 col-md-12 col-sm-12">
				<form action="" method="post" name="adminForm" id="adminForm">
					<div class="form-horizontal">
						<div class="form-group form-group row mt-2">
							<label class="control-label col-sm-3" title="<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_ID_TOOLTIP');?>">
								<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_ID');?>
								<span class="star" aria-hidden="true">&nbsp;*</span>
							</label>
							<div class="col-sm-9">
								<textarea class="form-control" id="selected_emails" name="selected_emails" readonly="true"><?php echo implode(", ", array_column($this->selected_emails, 'email'));?></textarea>
							</div>
						</div>
						<div class="form-group form-group row mt-2">
							<label class="control-label col-sm-3" title="<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_SUBJECT_TOOLTIP');?>">
								<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_SUBJECT');?>
								<span class="star" aria-hidden="true">&nbsp;*</span>
							</label>
							<div class="col-sm-9">
								<input type="text" id="jgive_subject"
								name="jgive_subject"  class="form-control"
								placeholder="<?php echo  Text::_('COM_JGIVE_ENTER_EMAIL_SUBJECT') ?>">
							</div>
						</div>
						<div class="form-group form-group row mt-2">
							<label class="control-label col-sm-3" title="<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_BODY_TOOLTIP');?>">
								<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_BODY');?>
								<span class="star" aria-hidden="true">&nbsp;*</span>
							</label>
							<div class="col-sm-9">
								<?php
								$editor      = Editor::getInstance(Factory::getApplication()->get('editor'));
								echo $editor->display("jgive_message", "", 670, 600, 60, 20, false);
								?>
							</div>
						</div>
						<div class="col-lg-12 col-md-12 col-sm-12 mt-3">
							<?php echo $this->toolbarHTML;?>
						</div>
					</div>

					<input type="hidden" name="option" value="com_jgive" />
					<input type="hidden" name="task" id="task" value="" />
					<input type="hidden" name="view" value="donors" />
					<input type="hidden" name="layout" value="contact_us" />
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if(task == 'donors.emailtoSelected')
		{
			// Check if Email Subject is empty
			if(document.getElementById("jgive_subject").value == 0  && document.getElementById("jgive_message").value == 0)
			{
				// If user want to send mail without subject or text
				var msg = "<?php echo Text::_('COM_JGIVE_CONFIRM_MSG_FOR_SEND_MAIL_WITHOUT_SUB_AND_TEXT'); ?>";

				alert(msg);
				return false;
			}
			else
			{
				Joomla.submitform(task);
			}
		}
		else
		{
			Joomla.submitform(task);
		}
	}
</script>
