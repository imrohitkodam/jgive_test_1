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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');
HTMLHelper::script(Uri::root() . 'libraries/techjoomla/assets/js/tjvalidator.js');
HTMLHelper::stylesheet('media/com_jgive/css/jgive_typeahead.css');
HTMLHelper::script('media/com_jgive/vendors/js/typeahead/typeahead.bundle.min.js');
?>
<script>
	jgive.organizationform.init();
</script>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div id="organizationform">
		<div class="page-header">
			<h1>
				<?php echo ($this->item->id) ? Text::_('COM_JGIVE_ORGANIZATION_FORM_EDIT') : Text::_('COM_JGIVE_ORGANIZATION_FORM_ADD');?>
			</h1>
		</div>
		<form name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_jgive&task=organizationform.save&id=' . (int) $this->item->id);?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			<?php echo $this->form->renderField('id');?>
			<input type="hidden" name="jform[vendor_id]" id="jform_vendor_id" value="<?php echo (!empty($this->item->id)) ? $this->item->vendor_id : $this->isVendor; ?>" />
			<input type="hidden" name="jform[published]" value="1" />
			<input type="hidden" name="jform[created_by]" value="<?php echo $this->user->id;?>" />
			<input type="hidden" name="jform[modified_by]" value="<?php echo (isset($this->item->id)) ? $this->user->id : '0';?>" />
			<input type="hidden" name="jform[created_date]" value="<?php echo $this->item->created_date; ?>" />
			<input type="hidden" name="jform[modified_date]" value="<?php echo $this->item->modified_date; ?>" />
			<input type="hidden" name="jform[user_id]" id="jform_user_id">
			<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
			<div class="row">
				<div class="col-xs-12">
					<?php 
					echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'info')); 
						echo HTMLHelper::_('uitab.addTab', 'myTab', 'info', Text::_('COM_JGIVE_ORGANIZATION_DETAILS_HEADING')); 
							echo $this->loadTemplate('info_bs5');
						echo HTMLHelper::_('uitab.endTab');

						echo HTMLHelper::_('uitab.addTab', 'myTab', 'address', Text::_('COM_JGIVE_ADDRESS_DETAILS_HEADING')); 
							echo $this->loadTemplate('address_bs5');
						echo HTMLHelper::_('uitab.endTab');

						echo LayoutHelper::render('joomla.edit.params', $this);
					echo HTMLHelper::_('uitab.endTabSet'); ?>
				</div>
			</div>
			<br>
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('organizationform.save')">
				<span><?php echo Text::_('JSUBMIT'); ?></span>
			</button>
			<button class="btn  btn-default" onclick="Joomla.submitbutton('organizationform.cancel')">
				<?php echo Text::_('JCANCEL'); ?>
			</button>
			<input type="hidden" name="option" value="com_jgive" />
			<input type="hidden" name="task" id="task" value="organizationform.save" />
			<?php echo HTMLHelper::_('form.token');?>
		</form>
	</div>
</div>
