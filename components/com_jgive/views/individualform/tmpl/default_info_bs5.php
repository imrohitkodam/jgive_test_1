<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

?>
<div class="row">
	<div class="col-sm-12">
		<div class="form-group row">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('first_name'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('first_name'); ?>
			</div>
		</div>

		<div class="form-group row">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('last_name'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('last_name'); ?>
			</div>
		</div>

		<p class="com_jgive_float_left alert alert-info" id="create_user_alert_group">
			<?php echo Text::_('COM_JGIVE_INDIVIDUAL_FORM_FILL_EMAIL_OR_CONTACT'); ?>
		</p>
		<div class="com_jgive_float_left alert alert-danger" id="bank_email_phone_alert_group" style="display: none;">
			<em><?php echo Text::_('COM_JGIVE_BLANK_EMAIL_PHONE');?></em>
		</div>

		<div class="form-group row" id="email_group">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('email'); ?>
			</div>
			<div class="col-md-8">
				<input type="email" class="validate-email validate-blankspace form-control" name="jform[email]" id="jform_email"
				onchange="com_jgive.UI.Individual.validateEmail(this.value, '<?php echo (!empty($this->item->id)) ? $this->item->vendor_id : $this->isVendor;?>', 0, '<?php echo (!empty($this->item->id))?$this->item->id:0;?>', '<?php echo Session::getFormToken()?>')"
				placeholder="<?php echo Text::_('COM_JGIVE_COMMON_EMAIL_HINT');?>"/>
			</div>
		</div>

		<div class="com_jgive_float_left alert alert-error" id="emailcheck" style="display: none;">
			<em><?php echo Text::_('COM_JGIVE_INDIVIDUAL_UNIQUE_EMAIL');?></em>
		</div>

		<div class="com_jgive_float_left alert alert-warning" id="emailcheck2" style="display: none;">
			<em>
				<?php echo Text::_('COM_JGIVE_INDIVIDUAL_USER_EXITS');?>
				<strong><?php echo Text::_('COM_JGIVE_INDIVIDUAL_USER_EXITS2');?></strong>
			</em>
		</div>

		<div class="form-group row">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('phone'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('phone'); ?>
			</div>
		</div>

		<div class="form-group row" id="create_user_group">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('create_user'); ?>
			</div>
			<div class="col-md-8">
				<!-- Not saving this field in database, only used to check if checked or not in individual model -->
				<input type="checkbox" class="checkox" style="vertical-align:middle;" name="jform[create_user]"
				id="jform_create_user" onchange="com_jgive.UI.Individual.checkBlankEmail()" default="0" value="1"/>
			</div>
		</div>
		<p class="com_jgive_float_left alert alert-info" id="create_user_alert_group">
			<?php echo Text::_('COM_JGIVE_BLANK_EMAIL_ALERT'); ?>
		</p>

		<div class="form-group row">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('taxnumber'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('taxnumber'); ?>
			</div>
		</div>
	</div>
</div>
<script>
var email = '<?php echo $this->item->email; ?>';
document.getElementById('jform_email').value= email;
</script>
