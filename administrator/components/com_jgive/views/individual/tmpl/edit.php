<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');

$bs = (JVERSION < '4.0.0') ? 'bs2' : 'bs5' ;
?>

<div class="com_jgive_float_left alert alert-danger" id="white_space_alert_group" style="display: none;">
	<em><?php echo Text::_('COM_JGIVE_WHITE_SPACE_NOT_ALLOWED'); ?></em>
</div>

<form action="<?php echo Route::_('index.php?option=com_jgive&layout=edit&id=' . (int) $this->item->id); ?>"method="post"
name="adminForm" id="adminForm" class="form-validate form-horizontal">

	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'info')); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'info', Text::_('COM_JGIVE_PERSONAL_DETAILS_HEADING')); ?>
			<?php echo $this->loadTemplate('info_' . $bs);?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'address', Text::_('COM_JGIVE_ADDRESS_DETAILS_HEADING')); ?>
			<?php echo $this->loadTemplate('address_' . $bs);?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php echo LayoutHelper::render('joomla.edit.params', $this);?>

	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
	<?php echo $this->form->renderField('id'); ?>

	<input type="hidden" name="jform[published]" value="1" >
	<input type="hidden" name="jform[created_by]"/>
	<input type="hidden" name="jform[modified_by]" value="<?php echo (isset($this->item->id)) ? $this->user->id : '0';?>" />
	<input type="hidden" name="jform[created_date]" value="<?php echo $this->item->created_date; ?>" />
	<input type="hidden" name="jform[modified_date]" value="<?php echo $this->item->modified_date; ?>" />
	<input type="hidden" name="jform[user_id]" id="jform_user_id">
	<input type="hidden" name="task" value="" >
	<input type="hidden" id="jform_id" name="jform[id]" value="<?php echo (!empty($this->item->id)) ? $this->item->id : 0;?>">
	<input type="hidden" name="tmpl" value="<?php echo $this->tmpl; ?>">
	<input type="hidden" name="token" id="token" value="<?php echo Session::getFormToken(); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>

	<?php
	if ($this->tmpl == 'component')
	{
		?>
		<div class="form-actions af-pt-5 af-pb-5 span9 col-md-12">
			<div class="btn-toolbar clearfix af-text-right float-end" data-js-attr="form-actions">
				<div id="toolbar-cancel" class="btn-wrapper">
					<button type="button" class="btn com_tmt_button btn-danger" onclick ="parent.jQuery('#newIndividualDonor').modal('hide');">
						<span class="af-valign-middle"></span><?php echo Text::_('COM_JGIVE_BUTTON_CANCEL'); ?>
					</button>
				</div>&nbsp;&nbsp;

				<div id="toolbar-save" class="btn-wrapper">
					<button type="button"  class="btn btn-success com_tmt_button" onclick="Joomla.submitbutton('individual.save');"
					style="display: inline-block;">
					<?php echo Text::_('COM_JGIVE_BUTTON_SAVE'); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</form>

<script type="text/javascript">
	var user_id = '<?php echo (int) $this->item->user_id; ?>';
	com_jgive.UI.Individual.hideUserCheckbox(user_id);

	Joomla.submitbutton = function(action) {
		if (action == 'individual.apply' || action == 'individual.save2new' || action == 'individual.save') {
			var fieldstatus  = com_jgive.UI.Common.checkWhitespace();
			var validateflag = document.formvalidator.isValid(document.getElementById('adminForm'));

			if (fieldstatus == false) {
				return false;
			}

			if (validateflag) {
				com_jgive.UI.Individual.CheckFormOnSubmit(action);
			}
		}
		else {
			Joomla.submitform(action);
		}
	}
</script>
