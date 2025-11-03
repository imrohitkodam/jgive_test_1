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
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

if (JVERSION < '4.0.0')
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

JFormHelper::loadFieldClass('list');

/**
 * Custom Legend field for component params.
 *
 * @package  JGive
 *
 * @since    2.1
 */
class JFormFieldCreateSilentVendor extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	2.1
	 */
	protected $type = 'createsilentvendor';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   2.1
	 */
	protected function getInput()
	{
		$params = ComponentHelper::getParams('com_tjvendors');
		$vendorApproval = $params->get('vendor_approval');
		$options = array();

		if ($vendorApproval)
		{
			$class     = (JVERSION < '4.0.0') ? '' : ' class="form-select" ';
			$options[] = HTMLHelper::_('select.option', '0', Text::_('COM_JGIVE_SILENT_VENDOR_NO'));
			$html = HTMLHelper::_('select.genericlist', $options, $this->name, ' ' . $class . ' ', 'value', 'text', '', $this->id);
			$html = '
				<div class="span8">
					<div class="span5">
						<div>'
							. HTMLHelper::_('select.genericlist', $options, $this->name, ' ' . $class . ' ', 'value', 'text', '', $this->id) .
						'</div>
						<br>
						<div class="pull-left alert alert-info control-label">'
							. Text::_("COM_JGIVE_VENDOR_APPROVAL_ENABLED") .
							' <a href="' . JURI::root() . 'administrator/index.php?option=com_config&view=component&component=com_tjvendors" target="_blank">' .
							Text::_("COM_JGIVE_VENDOR_APPROVAL_ENABLED_HERE") . '</a> ' . Text::_("COM_JGIVE_VENDOR_APPROVAL_ENABLED_2") . '
						</div>
					</div>
				</div>';
		}
		else
		{
			$options[] = HTMLHelper::_('select.option', '1', Text::_('COM_JGIVE_SILENT_VENDOR_YES'));
			$options[] = HTMLHelper::_('select.option', '0', Text::_('COM_JGIVE_SILENT_VENDOR_NO'));

			$selectClass  = (JVERSION < '4.0.0') ? ' inputbox ' : ' form-select ';

			$html = HTMLHelper::_('select.genericlist', $options, $this->name, 'class="' . $selectClass . '"  ', 'value', 'text', $this->value, $this->name);
		}

		return  $html;
	}
}
