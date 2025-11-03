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

HTMLHelper::_('behavior.formvalidator');
?>
<br>
<div>
	<!--Address Details heading-->
	<h3>
		<?php echo Text::_('COM_JGIVE_ORGANIZATION_ADDRESS_DETAILS_HEADING'); ?>
	</h3>
	<br>
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
			$countries = $this->countries;
			$default = null;

			if (isset($this->item->country))
			{
				$default = $this->item->country;
			}

			$options = array();
			$options[] = HTMLHelper::_('select.option', "", Text::_('COM_JGIVE_COUNTRY'));

			foreach ($countries as $key => $value)
			{
				$country = $countries[$key];
				$id = $country['id'];
				$value = $country['country'];
				$options[] = HTMLHelper::_('select.option', $id, $value);
			}

			if (empty($this->item->state))
			{
				$this->item->state = '';
				$this->item->city = '';
			}

			echo $this->dropdown = HTMLHelper::_('select.genericlist', $options, 'jform[country]',
			'aria-invalid="false" class="form-select" onchange="com_jgive.UI.Common.generateStates(id,\'' .
			1 . '\',\'' . $this->item->state . '\',\'' . $this->item->city . '\')"', 'value', 'text', $default, 'jform_country');

		?>
		</div>
	</div>

	<!--Region-->
	<div class="control-group" id="region_group">
		<div class="control-label">
			<label for="jform_region">
				<?php echo $this->form->getLabel('region'); ?>
			</label>
		</div>
		<div class="controls">
			<select class="form-select" name="jform[region]" id="jform_state"></select>
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
			<select class="form-select" name="jform[city]" id="jform_city"></select>
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
			placeholder="<?php echo Text::_('COM_JGIVE_ENTER_OTHER_CITY_VALUE_HINT');?>"
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
<!-- EOF span6 -->

<script>
var country = '<?php echo $this->item->country; ?>';
var region = '<?php echo $this->item->region; ?>';
var city = '<?php echo $this->item->city; ?>';
var other_city_check = '<?php echo $this->item->other_city_check; ?>';
var other_city_value = '<?php echo $this->item->other_city_value; ?>';

com_jgive.UI.Organization.otherCity(other_city_check,other_city_value);
com_jgive.UI.Common.generateStates('jform_country', 1, region, city);
</script>
