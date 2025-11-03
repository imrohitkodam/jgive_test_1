<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;


/**
 * JFormFieldCountries form custom element class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JFormFieldCountries extends JFormField
{
	protected $type = 'Countries';

	protected $name = 'Countries';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	public function getInput()
	{
		$controlName = (isset($this->options['control'])) ? $this->options['control'] : '';
		return $this->fetchElement($this->name, $this->value, $this->element, $controlName);
	}

	/**
	 * Get needed field data
	 *
	 * @param   string  $name          Name of the field
	 * @param   string  $value         Value of the field
	 * @param   string  &$node         Node of the field
	 * @param   string  $control_name  Field control name
	 *
	 * @return   string  Field HTML
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;

		$countries = $this->TjGeoHelper->getCountryList();
		$options = array();

		foreach ($countries as $country)
		{
			$options[] = HTMLHelper::_('select.option', $country['id'], $country['country']);
		}

		if (JVERSION >= 1.6)
		{
			$fieldName = $name;
		}
		else
		{
			$fieldName = $control_name . '[' . $name . ']';
		}

		$selectClass  = (JVERSION < '4.0.0') ? ' inputbox ' : ' form-select ';

		return HTMLHelper::_('select.genericlist', $options, $fieldName, 'class="' . $selectClass . ' required"', 'value', 'text', $value, $control_name . $name);
	}

	/**
	 * Get field tooltip
	 *
	 * @param   string  $label         Label of the field
	 * @param   string  $description   Description of the field
	 * @param   string  &$node         Node of the field
	 * @param   string  $control_name  Field control name
	 * @param   string  $name          Field name
	 *
	 * @return   string  Field HTML
	 */
	public function fetchTooltip($label, $description, &$node, $control_name, $name)
	{
		return null;
	}
}
