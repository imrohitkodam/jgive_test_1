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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

$jgiveFrontendHelper = new jgiveFrontendHelper;
$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individualform&layout=default');

$modalConfig = array('width' => '100%', 'height' => '100%', 'modalWidth' => 80, 'bodyHeight' => 70);

$addIndividualLink = Route::_('index.php?option=com_jgive&view=individualform&layout=default&tmpl=component&Itemid=' . $itemId, false);

$modalConfig['url'] = $addIndividualLink;
$modalConfig['title'] = Text::_('COM_JGIVE_ADD_NEW_INDIVIDUAL');
echo HTMLHelper::_('bootstrap.renderModal', 'newContact', $modalConfig);
?>
<div class="container row">
	<div class="col-xs-12">
		<div class="control-group" id="name_group">
			<div class="control-label">
				<?php echo $this->form->getLabel('name'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('name'); ?>
			</div>
		</div>

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
				<input type="email" class="validate-email validate-blankspace form-control"  name="jform[email]"
				id="jform_email" value = '<?php if (!empty($this->item->email)) echo $this->item->email ?>'
				onchange="com_jgive.UI.Organization.validateEmail(jform_email.value,jform_vendor_id.value, 0, '<?php echo (!empty($this->item->id))?$this->item->id:0; ?>', '<?php echo Session::getFormToken()?>')"
				placeholder="<?php echo Text::_('COM_JGIVE_ORGANIZATION_HINT_EMAIL');?>"/>
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
					<input class="typeahead col-sm-10 form-control"  id="jform_contact_name" type="text"
						placeholder="<?php echo Text::_('COM_JGIVE_ORGANIZATION_HINT_CONTACT_PERSON');?>"
						value='<?php echo (!empty($this->item->contact_name)) ? $this->item->contact_name : "" ?>'
						style="z-index: inherit;"
						autocomplete="unique-field-name" name="unique-name">
					<button class="btn btn-primary btn-lg ms-2 position-absolute"
						id="addNewIndivisualContact"
						data-bs-target="#newContact" data-bs-toggle="modal"
						title="<?php echo Text::_('COM_JGIVE_ADD_NEW_INDIVIDUAL');?>">
						<?php echo Text::_('COM_JGIVE_ADD_NEW_INDIVIDUAL');?>
					</button>
				</div>
			</div>
		</div>
		<input type="hidden" name="jform[contact_id]" id="jform_contact_id"
		value='<?php echo (!empty($this->item->contact_id)) ? 'ind.' . $this->item->contact_id : "";?>'/>
	</div>
</div>

<script type="text/javascript">
	techjoomla.jQuery(document).ready(function()
	{
		jQuery('#addNewIndivisualContact').click(function() {
			event. preventDefault();
			console.log('show modal')
			jQuery('#newContact').modal('show');
		});
	});
</script>
