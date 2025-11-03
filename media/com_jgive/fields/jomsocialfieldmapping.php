<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\TextareaField;
use Joomla\CMS\Form\FormHelper;

HTMLHelper::_('behavior.formvalidator');
$document = Factory::getDocument();

// Load techjoomla bootstrapper

FormHelper::loadFieldClass('textarea');

/**
 * Custom Mapping field for component params.
 *
 * @package  JGive
 *
 * @since    2.2
 */
class JFormFieldjomsocialfieldmapping extends JFormFieldTextarea
{
	protected $type = 'jomsocialfieldmapping';

	/**
	 * Method to get the field input markup.
	 *
	 * @return string  The field input markup.
	 *
	 * @since 1.6
	 */
	public function getInput()
	{
		$controlName = (isset($this->options['control'])) ? $this->options['control'] : '';
		return $textarea = $this->fetchElement($this->name, $this->value, $this->element, $controlName);
	}

	protected $name = 'jomsocial_fieldmap';

	/**
	 * Function fetchElement
	 *
	 * @param   string  $name          name of field
	 * @param   string  $value         value of field
	 * @param   string  &$node         node of field
	 * @param   string  $control_name  control_name of field
	 *
	 * @return  HTML
	 *
	 * @since  1.0.0
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$rows = $node->attributes()->rows;
		$cols = $node->attributes()->cols;
		$class = ( $node->attributes('class') ? 'class="' . $node->attributes('class') . '"' : 'class="text_area"' );

		// To render field which already saved in db
		$fieldvalue = trim($this->renderedfield());

		// For first time installation check value or textarea is empty
		if (($fieldvalue == ''))
		{
			$fieldvalue  = 'first_name=name' . "\n";
			$fieldvalue .= 'address= FIELD_ADDRESS ' . "\n";
			$fieldvalue .= 'city= FIELD_CITY ' . "\n";
			$fieldvalue .= 'phone= FIELD_LANDPHONE ' . "\n";
			$fieldvalue .= 'website_address= FIELD_WEBSITE ' . "\n";
			$fieldvalue .= 'paypal_email=email' . "\n";
		}

		$fieldavi = 'first_name=name' . "\n";
		$fieldavi .= 'last_name=' . "\n";
		$fieldavi .= 'address= FIELD_ADDRESS ' . "\n";
		$fieldavi .= 'address2=' . "\n";
		$fieldavi .= 'city= FIELD_CITY ' . "\n";
		$fieldavi .= 'zip=' . "\n";
		$fieldavi .= 'phone= FIELD_LANDPHONE ' . "\n";
		$fieldavi .= 'website_address= FIELD_WEBSITE ' . "\n";
		$fieldavi .= 'paypal_email=email' . "\n";

		$html = '<textarea name="' . $control_name . $name . '" cols="' . $cols . '" rows="' .
		$rows . '" ' . $class . ' id="' . $control_name . $name . '" >' . $fieldvalue . '</textarea>';

		return $html .= '&nbsp;&nbsp<textarea cols="' . $cols . '" rows="' . $rows . '" ' . $class . ' disabled="disabled" >' . $fieldavi . '</textarea>';
	}

	/**
	 * Method to renderedfield
	 *
	 * @return array
	 *
	 * @since 1.6
	 */
	public function renderedfield()
	{
		$params = ComponentHelper::getParams('com_jgive');
		$mapping = trim($params->get('jomsocial_fieldmap'));
		$field_explode = explode('\n', $mapping);
		$fieldvalue = '';

		if (isset($mapping))
		{
			foreach ($field_explode as $field)
			{
				$fieldvalue .= $field . "\n";
			}
		}

		return $fieldvalue;
	}
}
