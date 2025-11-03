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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
$document = Factory::getDocument();
HTMLHelper::_('script', 'media/com_jgive/vendors/js/typeahead/typeahead.bundle.min.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/donation.js');
HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive_typeahead.css');
?>
<br>
<div>
	<!--Personal Details heading-->
	<h3>
		<?php echo Text::_('COM_JGIVE_ORGANIZATION_DETAILS_HEADING'); ?>
	</h3>
	<br>
	<div class="control-group" id="vendor_id_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('vendor_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('vendor_id'); ?>
		</div>
	</div>

	<!--Organization name-->
	<div class="control-group" id="name_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('name'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('name'); ?>
		</div>
	</div>

	<!--Organization website-->
	<div class="control-group" id="website_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('website'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('website'); ?>
		</div>
	</div>

	<!--Email-->
	<div class="control-group" id="email_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('email'); ?>
		</div>
		<div class="controls">
			<input type="email" class="form-control validate-email validate-blankspace"  name="jform[email]"
			id="jform_email" value = '<?php if (!empty($this->item->email)) echo $this->item->email ?>'
			onchange="com_jgive.UI.Organization.validateEmail(jform_email.value, jform_vendor_id.value, 1,'<?php echo (!empty($this->item->id))?$this->item->id:0; ?>', '<?php echo Session::getFormToken()?>')"
			placeholder="<?php echo Text::_('COM_JGIVE_COMMON_EMAIL_HINT');?>"/>
		</div>
	</div>

	<!--Create user-->
	<div class="com_jgive_float_left alert alert-error" id="emailcheck" style="display: none;">
		<i><?php echo Text::_('COM_JGIVE_ORGANIZATION_UNIQUE_EMAIL');?></i>
	</div>

	<!--Contact no-->
	<div class="control-group" id="phone_group">
		<div class="control-label">
			<?php echo $this->form->getLabel('phone'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('phone'); ?>
		</div>
	</div>

	<!--Select contact-->
	<div class="control-group jgive_select_user">
		<div class="control-label">
			<?php echo $this->form->getLabel('contact_person'); ?>
		</div>
		<div class="controls">
			<div id="bloodhound">

				<input class="typeahead form-control"  id="jform_contact_name" type="text"
					placeholder="<?php echo Text::_('COM_JGIVE_SEARCH_CONTACT_PERSON');?>"
					value='<?php echo (!empty($this->item->contact_name)) ? $this->item->contact_name : "" ?>'
					style="z-index: inherit;"
					autocomplete="off" name="unique-name">

					<?php
					if ($this->tmpl != 'component')
					{
						$modalConfig = array('width' => '100%', 'height' => '100%', 'modalWidth' => 80, 'bodyHeight' => 70);

						$modalConfig['url'] = "index.php?option=com_jgive&view=individual&layout=edit&tmpl=component";
						$modalConfig['title'] = Text::_('COM_JGIVE_ADD_NEW_INDIVIDUAL');
						echo HTMLHelper::_('bootstrap.renderModal', 'newIndividualDonor', $modalConfig);
						?>

						<button type="button" class="button btn btn-info btn-small margin12" data-bs-target="#newIndividualDonor" data-bs-toggle="modal" title='<?php echo Text::_('COM_JGIVE_NEW_INDIVIDUAL');?>'>
							<?php echo Text::_('COM_JGIVE_ADD_NEW_INDIVIDUAL');?>
						</button>
						<?php
					}
					?>
			</div>
		</div>
	</div>

	<!--contact_id-->
	<input type="hidden" name="jform[contact_id]" id="jform_contact_id"
	value='<?php echo (!empty($this->item->contact_id)) ? 'ind.' . $this->item->contact_id : "";?>'/>
</div>
<!-- EOF span6 -->
<script>
techjoomla.jQuery(document).ready(function() {
		com_jgive.UI.Organization.contact(1, 'jform_vendor_id');
	});
</script>
