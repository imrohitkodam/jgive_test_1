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
				<?php echo $this->form->getLabel('addr_line_1'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('addr_line_1'); ?>
			</div>
		</div>

		<div class="form-group row">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('addr_line_2'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('addr_line_2'); ?>
			</div>
		</div>

		<div class="form-group row" id="country_group">
			<div class="form-label col-md-4">
				<label for="jform_country"><?php echo $this->form->getLabel('country'); ?></label>
			</div>
			<div class="col-md-8">
				<?php echo HTMLHelper::_('select.genericlist', $this->options, 'jform[country]',
					'aria-invalid="false" class="form-select" size="1" onchange="com_jgive.UI.Common.generateStates(id,\'' .
					0 . '\',\'' . $this->item->region . '\',\'' . $this->item->city . '\')"', 'value', 'text', $this->default, 'jform_country');
				?>
			</div>
		</div>

		<div class="form-group row" id="region_group">
			<div class="form-label col-md-4">
				<label for="jform_state"><?php echo $this->form->getLabel('region'); ?></label>
			</div>
			<div class="col-md-8">
				<select name="jform[region]" class="form-select" id="jform_state"></select>
			</div>
		</div>

		<div class="form-group row" id="city_group">
			<div class="form-label col-md-4">
				<label for="jform_city"><?php echo $this->form->getLabel('city'); ?></label>
			</div>
			<div class="col-md-8">
				<select name="jform[city]" class="form-select" id="jform_city"></select>
			</div>
		</div>

		<div class="form-group row" id="other_city_check_group">
			<div class="form-label col-md-4">
				<label class="form-label col-md-4" ><?php echo $this->form->getLabel('other_city_check'); ?></label>
			</div>
			<div class="col-md-8">
				<input type="checkbox" class="checkox" style="vertical-align:middle;" name="jform[other_city_check]" id="jform_other_city_check"
				onchange="com_jgive.UI.Individual.otherCityToggle()" default="0" value="1"/>
			</div>
		</div>

		<div class="form-group row" id="other_city_value_group" style="display:none;">
			<div class="form-label col-md-4">
				<label class="form-label col-md-4" >
				<?php echo $this->form->getLabel('other_city_value'); ?>
				</label>
			</div>
			<div class="col-md-8">
				<input type="text" name="jform[other_city_value]" id="jform_other_city_value"
				placeholder="<?php echo Text::_('COM_JGIVE_ENTER_OTHER_CITY_VALUE_HINT');?>"
				class="form-control validate-blankspace">
			</div>
		</div>

		<div class="form-group row">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('zip'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('zip'); ?>
			</div>
		</div>
	</div>
</div>
<script>
var country = '<?php echo $this->item->country; ?>';
var region = '<?php echo $this->item->region; ?>';
var city = '<?php echo $this->item->city; ?>';
var other_city_check = '<?php echo $this->item->other_city_check; ?>';
var other_city_value = '<?php echo $this->item->other_city_value; ?>';
com_jgive.UI.Individual.otherCity(other_city_check,other_city_value);
com_jgive.UI.Common.generateStates('jform_country', 1, region, city);
</script>
