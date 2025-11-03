<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Component\ComponentHelper;

jimport('techjoomla.tjmoney.tjmoney');

/**
 * Supports an HTML select list of courses
 *
 * @since  2.1.0
 */
class JFormFieldAmount extends JFormField
{
	protected $type = 'Goalamount';

	/**
	 * Methods to display giveback amount
	 *
	 * @return string $html
	 *
	 * @since    2.1
	 */
	public function getInput()
	{
		$com_params     = ComponentHelper::getParams('com_jgive');
		$currency             = $com_params->get('currency');
		$currencySymbolOrCode = $com_params->get('currency_symbol');

		$tjCurrency     = new TjMoney($currency);
		$currencySymbol = $tjCurrency->getSymbol();

		if ($currencySymbolOrCode === 'code')
		{
			$currencySymbol = $tjCurrency->getCode();
		}

		$required = $validateClass = '';

		if ($this->required == 1 || $this->required == true)
		{
			$required = 'required=true';
		}

		if ($this->type)
		{
			$validateClass = "validate-" . $this->type;
		}

		$inputClass   = (JVERSION < '4.0.0') ? ' input-group-addon ' : ' input-group-text ';

		if (!empty($this->value))
		{
			$this->value = floatval($this->value);
		}

		$html = '<div class="input-group">
					<input class="form-control ' . $validateClass . '" onchange="jgiveCommon.validations.validationAmount()" type="number" step="0.1" min="0" '
						. $required . ' name="jform[goal_amount]" id="jform_goal_amount" value="' . $this->value . '">
					<span class="' . $inputClass . '" id="basic-addon2">' . $currencySymbol . '</span>
				</div>';

		return $html;
	}
}
