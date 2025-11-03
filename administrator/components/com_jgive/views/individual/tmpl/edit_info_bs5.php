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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.formvalidator');
?>
</br>
<div>
	<!--Personal Details heading-->
	<h3>
		<?php echo Text::_('COM_JGIVE_PERSONAL_DETAILS_HEADING'); ?>
	</h3>
	</br>

	<div class="control-group" id="vendor_id_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('vendor_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('vendor_id'); ?>
		</div>
	</div>

	<div class="control-group" id="first_name_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('first_name'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('first_name'); ?>
		</div>
	</div>

	<div class="control-group" id="last_name_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('last_name'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('last_name'); ?>
		</div>
	</div>

	<br>
	<div class="com_jgive_float_left alert alert-info" id="create_user_alert_group">
		<em><?php echo Text::_('COM_JGIVE_FILL_EMAIL_OR_CONTACT1'); ?>
		<strong><?php echo Text::_('COM_JGIVE_EMAIL_ID'); ?></strong>
		<?php echo Text::_('COM_JGIVE_FILL_EMAIL_OR_CONTACT2'); ?>
		<strong><?php echo Text::_('COM_JGIVE_CONTACT_NO'); ?></strong>
		<?php echo Text::_('COM_JGIVE_FILL_EMAIL_OR_CONTACT3'); ?>
	</em>
	</div>
	<!--Contact Details heading-->
	<h3>
		<?php echo Text::_('COM_JGIVE_CONTACT_DETAILS_HEADING'); ?>
	</h3>

	<!--blank email alert-->
	<div class="com_jgive_float_left alert alert-danger" id="bank_email_phone_alert_group" style="display: none;">
		<em><?php echo Text::_('COM_JGIVE_BLANK_EMAIL_PHONE');?></em>
	</div>

	<div class="control-group" id="email_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('email'); ?>
		</div>
		<div class="controls">
		<input type="email" class="form-control validate-email no-whitespace" name="jform[email]" id="jform_email"
			onchange="com_jgive.UI.Individual.validateEmail(this.value, jform_vendor_id.value, 1, '<?php
			echo (!empty($this->item->id))?$this->item->id:0;?>', '<?php echo Session::getFormToken()?>')"
			placeholder="<?php echo Text::_('COM_JGIVE_COMMON_EMAIL_HINT');?>"/>
		</div>
	</div>

	<div class="com_jgive_float_left alert alert-error" id="emailcheck" style="display: none;">
		<em><?php echo Text::_('COM_JGIVE_INDIVIDUAL_UNIQUE_EMAIL');?></em>
	</div>

	<div class="com_jgive_float_left alert alert-warning" id="emailcheck2" style="display: none;">
		<em><?php echo Text::_('COM_JGIVE_INDIVIDUAL_USER_EXITS');?>
		<strong><?php echo Text::_('COM_JGIVE_INDIVIDUAL_USER_EXITS2');?></strong></em>
	</div>

	<div class="control-group" id="phone_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('phone'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('phone'); ?>
		</div>
	</div>

	<div class="control-group" id="create_user_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('create_user'); ?>
		</div>
		<div class="controls">
			<!-- Not saving this field in database, only used to check if checked or not in individual model -->
			<input type="checkbox" class="checkox" style="vertical-align:middle;" name="jform[create_user]"
			id="jform_create_user" onchange="com_jgive.UI.Individual.checkBlankEmail()" default="0" value="1"/>
		</div>
	</div>
	<div class="com_jgive_float_left alert alert-info" id="create_user_alert_group">
		<em><?php echo Text::_('COM_JGIVE_INDIVIDUAL_CREATEUSER_NOTE'); ?></em>
	</div>
</div>
<script>
var email = '<?php echo $this->item->email; ?>';
document.getElementById('jform_email').value= email;
</script>
