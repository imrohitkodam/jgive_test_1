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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');

if (!empty($this->sidebar))
{
?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar;?>
	</div>
	<div id="j-main-container" class="span10">
<?php
}
else
{
?>
	<div id="j-main-container">
<?php
}
?>
<div class="techjoomla-bootstrap" id="jgive-massmailing">
	<div class="row-fluid">
		<form action="" id="adminForm" name="adminForm" method="post" class="form-validate">
			<div class="form-horizontal">
				<div class="control-group">
					<label class="control-label">
						<?php echo  JText::_('COM_JGIVE_ENTER_EMAIL_ID') . ' * ' ?>
					</label>
					<div class="controls">
						<textarea class="form-control" id="selected_emails" name="selected_emails" 
						readonly="true"><?php echo implode(",", array_column($this->selected_emails, 'email'));?></textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">
						<?php echo  Text::_('COM_JGIVE_ENTER_EMAIL_SUBJECT') ?>
					</label>
					<div class="controls">
						<input type="text" id="jgive_subject"
						name="jgive_subject"  class="form-control"
						placeholder="<?php echo  Text::_('COM_JGIVE_ENTER_EMAIL_SUBJECT') ?>">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">
						<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_BODY') ?>
					</label>
					<div class="controls">
						<?php
						$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
						echo $editor->display("jgive_message", "", 670, 600, 60, 20, false);
						?>
					</div>
				</div>
			</div>
				<input type="hidden" name="option" value="com_jgive" />
				<input type="hidden" name="task" id="task" value="" />
				<input type="hidden" name="view" value="donations" />
				<input type="hidden" name="layout" value="mass_mailing" />
				<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if(task == 'donations.emailToSelected')
		{
			var messageBody     = tinyMCE.activeEditor.getContent();
			// Check if Email Subject is empty
			if (document.getElementById("jgive_subject").value == 0 || document.getElementById("jgive_message").value == null || messageBody === '')
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


