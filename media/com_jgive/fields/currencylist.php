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

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

jimport('techjoomla.tjmoney.tjmoney');
JFormHelper::loadFieldClass('list');

/**
 * JFormFieldCurrencyList class
 *
 * @package     JGive
 * @subpackage  component
 * @since       2.2.0
 */

class JFormFieldCurrencyList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 2.2.0
	 */
	protected $type = 'currencylist';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var   integer
	 * @since 2.2.0
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   2.2.0
	 */
	protected function getOptions()
	{
		$options = array();
		$currencies = TjMoney::getCurrencies();

		if (!empty($currencies))
		{
			foreach ($currencies as $key => $currency)
			{
				$currencyCode    = $currency['alphabeticCode'];
				$currencyTitle   = $currency['currency'];

				$options[] = HTMLHelper::_('select.option', $currencyCode, $currencyTitle);
			}
		}

		return $options;
	}
}
