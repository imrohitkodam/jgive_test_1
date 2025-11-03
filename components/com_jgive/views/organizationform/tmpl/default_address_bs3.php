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
		<div class="control-group" id="addr_line_1_group">
			<div class="control-label">
				<?php echo $this->form->getLabel('addr_line_1'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('addr_line_1'); ?>
			</div>
		</div>

		<!--Address line 2-->
		<div class="control-group" id="addr_line_2_group">
			<div class="control-label">
				<?php echo $this->form->getLabel('addr_line_2'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('addr_line_2'); ?>
			</div>
		</div>

		<!--Country-->
		<div class="control-group" id="country_group">
			<div class="control-label">
				<label for="jform_country">
					<?php echo $this->form->getLabel('country'); ?>
				</label>
			</div>
			<div class="controls">
			<?php
				echo $this->dropdown = HTMLHelper::_('select.genericlist', $this->options, 'jform[country]',
				'aria-invalid="false" size="1" onchange="com_jgive.UI.Common.generateStates(id,\'' .
				1 . '\',\'' . $this->item->region . '\',\'' . $this->item->city . '\')"', 'value', 'text', $this->default, 'jform_country');
			?>
			</div>
		</div>

		<!--Region-->
		<div class="control-group" id="region_group">
			<div class="control-label">
				<label for="jform_state">
					<?php echo $this->form->getLabel('region'); ?>
				</label>
			</div>
			<div class="controls">
				<select name="jform[region]" id="jform_state"></select>
			</div>
		</div>

		<!--City-->
		<div class="control-group" id="city_group">
			<div class="control-label">
				<label for="jform_city">
					<?php echo $this->form->getLabel('city'); ?>
				</label>
			</div>
			<div class="controls">
				<select name="jform[city]" id="jform_city"></select>
			</div>
		</div>

		<!--Other city checkbox-->
		<div class="control-group" id="other_city_check_group">
			<div class="control-label">
				<label class="control-label" >
				<?php echo $this->form->getLabel('other_city_check'); ?>
				</label>
			</div>
			<div class="controls">
				<input type="checkbox" class="checkox" style="vertical-align:middle;" name="jform[other_city_check]" id="jform_other_city_check"
				onchange="com_jgive.UI.Individual.otherCityToggle()" default="0" value="1"/>
			</div>
		</div>

		<!--Other city value-->
		<div class="control-group" id="other_city_value_group" style="display:none;">
			<div class="control-label">
				<label class="control-label" >
				<?php echo $this->form->getLabel('other_city_value'); ?>
				</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[other_city_value]" id="jform_other_city_value"
				placeholder="<?php echo Text::_('COM_JGIVE_ORGANIZATION_HINT_OTHER_CITY_VALUE');?>"
				class="form-control no-whitespace">
			</div>
		</div>

		<!--Zip-->
		<div class="control-group" id="zip_group">
			<div class="control-label">
			<?php echo $this->form->getLabel('zip'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('zip'); ?>
			</div>
		</div>

		<div class="control-group" id="taxnumber">
			<div class="control-label">
			<?php echo $this->form->getLabel('taxnumber'); ?>
			</div>
			<div class="controls">
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

