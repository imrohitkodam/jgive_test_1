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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Component\ComponentHelper;

HTMLHelper::_('behavior.formvalidator');

$document = Factory::getDocument();

$removeIconClass = (JVERSION > '4.0.0') ? " icon-cancel " : " icon-minus-sign ";
$removeCloneButtonStyle = (JVERSION > '4.0.0') ? " <br><br> " : "  ";
// Load techjoomla bootstrapper
?>

<script type="text/javascript">
	/*add clone script*/
	function addClone(rId,rClass)
	{
		var num=techjoomla.jQuery('.'+rClass).length;
		var removeButton="<div class='com_jgive_remove_button' style='float:right;' >";
		removeButton+="<button class='btn btn-mini' type='button' id='remove"+num+"'";
		removeButton+="onclick=\"removeClone('jgive_container"+num+"','jgive_container');\" title=\"<?php
echo Text::_('COM_JGIVE_REMOVE_TOOLTIP');
?>\" >";
		removeButton+="<i class=\"<?php echo $removeIconClass;?>\"></i></button>";
		removeButton+="</div><?php echo $removeCloneButtonStyle;?>";

		var newElem=techjoomla.jQuery('#'+rId).clone().attr('id',rId+num);

		techjoomla.jQuery(newElem).children('.com_jgive_repeating_block').children('.controls').
		children('.control-group').children('.controls').children('.input-prepend,.input-append').children().each(function()
		{
			var kid=techjoomla.jQuery(this);

			if(kid.attr('id')!=undefined)
			{
				var idN=kid.attr('id');
				kid.attr('id',idN+num).attr('id',idN+num);
				kid.attr('value','');
			}

			kid.attr('value','');

			//for joomla 3.0 change select element style
			var s = kid.attr('id');

			if(s.indexOf("jformusergroup_chzn"))
			{
				kid.attr('style', "display: block;");
			}
			else
			{
				kid.attr('style', "display: none;");
			}
		});

		techjoomla.jQuery('.'+rClass+':last').after(newElem);
		techjoomla.jQuery('.'+rClass+':last').prepend(removeButton);
	}
	/* remove clone script */
	function removeClone(rId,rClass,ids){
		if(ids==undefined)
			techjoomla.jQuery('#'+rId).remove();
		else
			techjoomla.jQuery('#'+'jgive_container'+ids).remove();
	}
</script>

<?php

/**
 * Custom Commission field for component params.
 *
 * @package  JGive
 *
 * @since    2.2
 */
class JFormFieldCommision extends JFormField
{
	protected $type = 'Commision';

	/**
	 * Method to get the field input markup.
	 *
	 * @return string  The field input markup.
	 *
	 * @since 1.6
	 */
	public function getInput()
	{
		$script = 'techjoomla.jQuery(document).ready(function(){
			techjoomla.jQuery("#jform_jgive_usr_group_commision-lbl").parent().removeClass("control-label");
			techjoomla.jQuery("#jgive_container").parent().parent().removeClass("controls");
			techjoomla.jQuery("#jgive_container").parent().parent().removeClass("control-group");
		});';

		$document = Factory::getDocument();
		$document->addScriptDeclaration($script);

		$jgive_icon_plus = (JVERSION > '4.0.0') ? "icon-new" : "icon-plus-2 btn";

		$html = '';

		$params     = ComponentHelper::getParams('com_jgive');
		$group_info = $params->get('usergroup');
		$controlName = (isset($this->options['control'])) ? $this->options['control'] : '';
		$removeIconClass = (JVERSION > '4.0.0') ? " icon-cancel " : " icon-minus-sign ";
		$removeCloneButtonStyle = (JVERSION > '4.0.0') ? " <br><br> " : "  ";

		// For edit - recreate giveback blocks
		if (isset($group_info))
		{
			$count = count((array) $group_info);
			$j     = 0;

			for ($i = 0; $i < $count; $i = $i + 3)
			{
				if (!empty($group_info[$i]))
				{
					$html .= '
							<div class="techjoomla-bootstrap">
								<div id="jgive_container' . $j . '" class="jgive_container" >
									<div class="com_jgive_repeating_block">

										<div class="com_jgive_remove_button" style="float:right;  ">
												<button class="btn btn-mini" type="button" id="remove' . $j . '"
													onclick="removeClone(\'jgive_container\',\'jgive_container\',' . $j . ');" title="' . Text::_('COM_JGIVE_REMOVE_TOOLTIP') . '" >
													<i class="' . $removeIconClass . '"></i>
												</button>
										</div>
										' . $removeCloneButtonStyle . '

										<div class="control-group">
											<label class="control-label" for="give_back_value" title="' . Text::_('COM_JGIVE_GIVE_USERGROUP_TOOLTIP') . '">
												' . Text::_('COM_JGIVE_GIVE_USERGROUP_VALUE') . '
											</label>
											<div class="controls chzn-done"">
													' . $this->fetchElement($this->name, $group_info[$i], $this->element, $controlName) . '
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="give_back_details" title="' . Text::_('COM_JGIVE_GIVE_DONATE_PERCENT_TOOLTIP') . '">
												' . Text::_('COM_JGIVE_GIVE_DONATE_PERCENT') . '
											</label>
											<div class="controls">
												' . $this->fetchDonation($this->name, $group_info[$i + 1], $this->element, $controlName) . '
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="give_back_details" title="' . Text::_('COM_JGIVE_GIVE_INVEST_PERCENT_TOOLTIP') . '">
												' . Text::_('COM_JGIVE_GIVE_INVEST_PERCENT') . '
											</label>
											<div class="controls">
												' . $this->fetchInvest($this->name, $group_info[$i + 2], $this->element, $controlName) . '
											</div>
										</div>
									</div>
								<hr/>
								</div>
							</div>';
				}

				$j++;
			}
		}
?>

		<?php
		// Fields
		$html .= '
				<div class="techjoomla-bootstrap">
					<div id="jgive_container" class="jgive_container" >
						<div class="com_jgive_repeating_block" >
							<div class="controls" style="margin-left: 0px !important;">
								<div class="control-group" >
									<label class="control-label" for="give_back_value" title="' . Text::_('COM_JGIVE_GIVE_USERGROUP_TOOLTIP') . '">' .
									Text::_('COM_JGIVE_GIVE_USERGROUP_VALUE') . '
									</label>
									<div class="controls">
										<div class="input-prepend input-append chzn-done"">
											' . $this->fetchElement($this->name, '', $this->element, $controlName) . '
										</div>
									</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="give_back_details" title="' . Text::_('COM_JGIVE_GIVE_DONATE_PERCENT_TOOLTIP') . '">
									' . Text::_('COM_JGIVE_GIVE_DONATE_PERCENT') . '
								</label>
								<div class="controls">
									' . $this->fetchDonation($this->name, '', $this->element, $controlName) . '
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="give_back_details" title="' . Text::_('COM_JGIVE_GIVE_INVEST_PERCENT_TOOLTIP') . '">
									' . Text::_('COM_JGIVE_GIVE_INVEST_PERCENT') . '
								</label>
								<div class="controls">
									' . $this->fetchInvest($this->name, '', $this->element, $controlName) . '
								</div>
							</div>
						</div>
						<hr/>
						<div>&nbsp;</div>
					</div>';
		$buttonClass = (JVERSION > '4.0.0') ? " btn-small btn-success " : " btn-mini " ;
		$html .= '<div class="com_jgive_add_button" style="float:right ;>
						<button class="btn ' . $buttonClass . '" type="button" id="addbtn"
							onclick="addClone(\'jgive_container\',\'jgive_container\');"
								title="' . Text::_('COM_JGIVE_ADD_MORE_TOOLTIP') . '">
							<i class="' . $jgive_icon_plus . '"></i>
						</button>
					</div>
				</div>';

		return $html;
	}

	protected $name = 'Commision';

	/**
	 * Function fetchElement
	 *
	 * @param   string  $fieldName     name of field
	 * @param   string  $value         value of field
	 * @param   string  &$node         node of field
	 * @param   string  $control_name  control_name of field
	 *
	 * @return  HTML
	 *
	 * @since  1.0.0
	 */
	public function fetchElement($fieldName, $value, &$node, $control_name)
	{
		$selectClass = (JVERSION > '4.0.0') ? 'form-select' : 'chzn-done';

		return $usergrp = HTMLHelper::_('access.usergroup', $fieldName . '[]', $value, 'class="' . $selectClass . '"');
	}

	/**
	 * Function fetchDonation
	 *
	 * @param   string  $fieldName     name of field
	 * @param   string  $value         value of field
	 * @param   string  &$node         node of field
	 * @param   string  $control_name  control_name of field
	 *
	 * @return  HTML
	 *
	 * @since  1.0.0
	 */
	public function fetchDonation($fieldName, $value, &$node, $control_name)
	{
		$inputClass = (JVERSION > '4.0.0') ? 'form-control': '';

		return '<input type="text" class="' . $inputClass . '" name="' . $fieldName . '[]' . '"  value="' . $value . '" placeholder="Donate %" "/>';
	}

	/**
	 * Function fetchInvest
	 *
	 * @param   string  $fieldName     name of field
	 * @param   string  $value         value of field
	 * @param   string  &$node         node of field
	 * @param   string  $control_name  control_name of field
	 *
	 * @return  HTML
	 *
	 * @since  1.0.0
	 */
	public function fetchInvest($fieldName, $value, &$node, $control_name)
	{
		$inputClass = (JVERSION > '4.0.0') ? 'form-control': '';

		return '<input type="text" class="' . $inputClass . '" name="' . $fieldName . '[]' . '" value="' . $value . '" placeholder="Invest %""/>';
	}
}
