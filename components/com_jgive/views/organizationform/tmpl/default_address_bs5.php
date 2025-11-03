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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<div class="container row">
	<div>
		<!--Address line 1-->
		<div class="form-group row" id="addr_line_1_group">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('addr_line_1'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('addr_line_1'); ?>
			</div>
		</div>

		<!--Address line 2-->
		<div class="form-group row" id="addr_line_2_group">
			<div class="form-label col-md-4">
				<?php echo $this->form->getLabel('addr_line_2'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('addr_line_2'); ?>
			</div>
		</div>

		<!--Country-->
		<div class="form-group row" id="country_group">
			<div class="form-label col-md-4">
				<label for="jform_country">
					<?php echo $this->form->getLabel('country'); ?>
				</label>
			</div>
			<div class="col-md-8">
			<?php
				echo $this->dropdown = HTMLHelper::_('select.genericlist', $this->options, 'jform[country]',
				'aria-invalid="false" size="1" class="form-select" onchange="com_jgive.UI.Common.generateStates(id,\'' .
				1 . '\',\'' . $this->item->region . '\',\'' . $this->item->city . '\')"', 'value', 'text', $this->default, 'jform_country');
			?>
			</div>
		</div>

		<!--Region-->
		<div class="form-group row" id="region_group">
			<div class="form-label col-md-4">
				<label for="jform_state">
					<?php echo $this->form->getLabel('region'); ?>
				</label>
			</div>
			<div class="col-md-8">
				<select name="jform[region]" class="form-select" id="jform_state"></select>
			</div>
		</div>

		<!--City-->
		<div class="form-group row" id="city_group">
			<div class="form-label col-md-4">
				<label for="jform_city">
					<?php echo $this->form->getLabel('city'); ?>
				</label>
			</div>
			<div class="col-md-8">
				<select name="jform[city]" class="form-select" id="jform_city"></select>
			</div>
		</div>

		<!--Other city checkbox-->
		<div class="form-group row" id="other_city_check_group">
			<div class="form-label col-md-4">
				<label class="form-label col-md-4" >
				<?php echo $this->form->getLabel('other_city_check'); ?>
				</label>
			</div>
			<div class="col-md-8">
				<input type="checkbox" class="checkox" style="vertical-align:middle;" name="jform[other_city_check]" id="jform_other_city_check"
				onchange="com_jgive.UI.Individual.otherCityToggle()" default="0" value="1"/>
			</div>
		</div>

		<!--Other city value-->
		<div class="form-group row" id="other_city_value_group" style="display:none;">
			<div class="form-label col-md-4">
				<label class="form-label col-md-4" >
				<?php echo $this->form->getLabel('other_city_value'); ?>
				</label>
			</div>
			<div class="col-md-8">
				<input type="text" name="jform[other_city_value]" id="jform_other_city_value"
				placeholder="<?php echo Text::_('COM_JGIVE_ORGANIZATION_HINT_OTHER_CITY_VALUE');?>"
				class="form-control no-whitespace">
			</div>
		</div>

		<!--Zip-->
		<div class="form-group row" id="zip_group">
			<div class="form-label col-md-4">
			<?php echo $this->form->getLabel('zip'); ?>
			</div>
			<div class="col-md-8">
				<?php echo $this->form->getInput('zip'); ?>
			</div>
		</div>

		<div class="form-group row" id="taxnumber">
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
	var country = '<?php echo $this->item->country; ?>';
	var region = '<?php echo $this->item->region; ?>';
	var city = '<?php echo $this->item->city; ?>';
	var other_city_check = '<?php echo $this->item->other_city_check; ?>';
	var other_city_value = '<?php echo $this->item->other_city_value; ?>';
</script>

