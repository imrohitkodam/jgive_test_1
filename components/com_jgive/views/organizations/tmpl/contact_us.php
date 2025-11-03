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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Editor\Editor;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

if (JVERSION < '4.0.0')
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}
?>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div class="row">
		<div class="col-sm-12">
			<div class="container-fluid">
				<?php
				if ($this->params->get('show_page_heading', 1))
				{
					?>
					<div class="page-header">
						<h1>
							<?php echo $this->escape($this->params->get('page_heading')) . ' ' . Text::_('COM_JGIVE_MASS_MAIL');?>
						</h1>
					</div>
					<?php
				}
				?>
			</div>
			<form action="" method="post" name="adminForm" id="adminForm">
				<div class="form-horizontal">
					<div class="form-group row mt-2">
						<label class="form-label col-md-3 required" title="<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_ID_TOOLTIP');?>">
							<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_ID');?> 
							<span class="star" aria-hidden="true">&nbsp;*</span>
						</label>
						<div class="col-md-9">
							<textarea class="form-control" id="selected_emails" name="selected_emails" readonly="true"><?php echo $this->emails;?></textarea>
						</div>
					</div>
					<div class="form-group row mt-2" title="<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_SUBJECT_TOOLTIP');?>">
						<label class="form-label col-md-3">
							<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_SUBJECT');?> 
							<span class="star" aria-hidden="true">&nbsp;*</span>
						</label>
						<div class="col-md-9">
							<input type="text" id="jgive_subject"
							name="jgive_subject"  class="form-control"
							placeholder="<?php echo  Text::_('COM_JGIVE_ENTER_EMAIL_SUBJECT') ?>">
						</div>
					</div>
					<div class="form-group row mt-2">
						<label class="form-label col-md-3" title="<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_BODY_TOOLTIP');?>">
							<?php echo Text::_('COM_JGIVE_ENTER_EMAIL_BODY');?> 
							<span class="star" aria-hidden="true">&nbsp;*</span>
						</label>
						<div class="col-md-9">
							<?php
							$getEditor  = Factory::getConfig()->get('editor');
							$editor     = Editor::getInstance($getEditor);
							echo $editor->display("jgive_message", "", 670, 600, 60, 20, false);
							?>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 float-end af-pr-25 mt-2">
							<?php echo $this->toolbarHTML;?>
						</div>
					</div>
				</div>
				<input type="hidden" name="option" value="com_jgive" />
				<input type="hidden" name="task" id="task" value="" />
				<input type="hidden" name="view" value="individuals" />
				<input type="hidden" name="layout" value="contact_us" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>
