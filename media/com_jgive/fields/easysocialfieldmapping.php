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
use Joomla\CMS\Form\FormHelper;

HTMLHelper::_('behavior.formvalidator');

$document = Factory::getDocument();

// Load techjoomla bootstrapper

FormHelper::loadFieldClass('textarea');

/**
 * Custom Social field for component params.
 *
 * @package  JGive
 *
 * @since    2.2
 */
class JFormFieldeasysocialfieldmapping extends JFormFieldTextarea
{
	protected $type = 'easysocialfieldmapping';

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
		// Require_once(JPATH_SITE . '/libraries/joomla/html/parameter/element/textarea.php');
		$rows       = $node->attributes()->rows;
		$cols       = $node->attributes()->cols;
		$class      = ($node->attributes('class') ? 'class="' . $node->attributes('class') . '"' : 'class="text_area"');

		// To render field which already saved in db
		$fieldvalue = trim($this->renderedfield());

		// For first time installation check value or textarea is empty
		if (($fieldvalue == ''))
		{
			$fieldvalue = 'first_name=first' . "\n";
			$fieldvalue .= 'last_name=last' . "\n";
			$fieldvalue .= 'paypal_email=email' . "\n";
			$fieldvalue .= 'address=address1' . "\n";
			$fieldvalue .= 'address2=address2' . "\n";
			$fieldvalue .= 'country=country' . "\n";
			$fieldvalue .= 'state=state' . "\n";
			$fieldvalue .= 'city=city' . "\n";
			$fieldvalue .= 'zip=zip' . "\n";
		}

		$fieldavi = 'first_name=first' . "\n";
		$fieldavi .= 'last_name=last' . "\n";
		$fieldavi .= 'paypal_email=email' . "\n";
		$fieldavi .= 'address=address1' . "\n";
		$fieldavi .= 'address2=address2' . "\n";
		$fieldavi .= 'country=country' . "\n";
		$fieldavi .= 'state=state' . "\n";
		$fieldavi .= 'city=city' . "\n";
		$fieldavi .= 'zip=zip' . "\n";

		$html = '<textarea name="' . $control_name . $name . '" cols="' . $cols . '" rows="'
		. $rows . '" ' . $class . ' id="' . $control_name . $name . '" >' . $fieldvalue . '</textarea>';

		return $html .= '&nbsp;&nbsp<textarea  cols="' . $cols . '" rows="' . $rows . '" ' . $class . ' disabled="disabled" >' . $fieldavi . '</textarea>';
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
		$params        = ComponentHelper::getParams('com_jgive');
		$mapping       = trim($params->get('easysocial_fieldmap'));
		$field_explode = explode('\n', $mapping);
		$fieldvalue    = '';

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
