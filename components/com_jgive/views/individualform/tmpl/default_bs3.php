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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::script(Uri::root() . 'libraries/techjoomla/assets/js/tjvalidator.js');
$script   = array();
$script[] = 'var user_id   = "' . $this->item->user_id . '"';
$script[] = 'jgive.individualform.init();';
Factory::getDocument()->addScriptDeclaration(implode("\n", $script));
?>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div id="individualform">
		<div class="page-header">
			<h1>
				<?php echo ($this->item->id) ? Text::_('COM_JGIVE_INDIVIDUAL_FORM_EDIT') : Text::_('COM_JGIVE_INDIVIDUAL_FORM_ADD');?>
			</h1>
		</div>
		<form name="adminForm" id="adminForm" method="post" class="form-validate form-horizontal" 
		action="<?php echo Route::_('index.php?option=com_jgive&task=individualform.save&id=' . (int) $this->item->id);?>" enctype="multipart/form-data">
			<div class="row">
				<div class="col-xs-12">
					<?php 
					echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'info'));
						echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'info', Text::_('COM_JGIVE_INDIVIDUAL_FORM_PERSONAL_DETAILS'));
							echo $this->loadTemplate('info_bs3');
						echo HTMLHelper::_('bootstrap.endTab');
						echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'address', Text::_('COM_JGIVE_INDIVIDUAL_FORM_ADDRESS_DETAILS'));
							echo $this->loadTemplate('address_bs3');
						echo HTMLHelper::_('bootstrap.endTab');

						echo LayoutHelper::render('joomla.edit.params', $this);
					echo HTMLHelper::_('bootstrap.endTabSet'); ?>
				</div>
			</div>

			<button class="btn float-end ms-2 btn-default" onclick="Joomla.submitbutton('individualform.cancel')">
				<?php echo Text::_('JCANCEL'); ?>
			</button>
			<button type="button" class="btn float-end ms-2 btn-primary" onclick="Joomla.submitbutton('individualform.save')">
				<span><?php echo Text::_('JSUBMIT'); ?></span>
			</button>
			<input type="hidden" name="jform[vendor_id]" 
			value="<?php echo (!empty($this->item->id)) ? $this->item->vendor_id : $this->isVendor; ?>" />
			<input type="hidden" name="jform[published]" value="1" />
			<input type="hidden" name="jform[created_by]" value="<?php echo (isset($this->item->id)) ? $this->item->created_by : '';?>"	/>
			<input type="hidden" name="jform[modified_by]" value="<?php echo (isset($this->item->id)) ? $this->user->id : '0';?>" />
			<input type="hidden" name="jform[created_date]" value="<?php echo $this->item->created_date; ?>" />
			<input type="hidden" name="jform[modified_date]" value="<?php echo $this->item->modified_date; ?>" />
			<input type="hidden" name="jform[user_id]" id="jform_user_id">
			<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
			<input type="hidden" name="option" value="com_jgive" />
			<input type="hidden" name="task" id="task" value="individualform.save" />
			<input type="hidden" name="tmpl" value="<?php echo $this->tmpl; ?>">
			<?php echo HTMLHelper::_('form.token');?>
		</form>
	</div>
</div>
