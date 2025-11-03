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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Class for custom gateway element
 *
 * @since  1.0.0
 */
class JFormFieldIntegrations extends JFormField
{
	/**
	 * Function to genarate html of custom element
	 *
	 * @return  HTML
	 *
	 * @since  2.0
	 */
	public function getInput()
	{
		$controlName = (isset($this->options['control'])) ? $this->options['control'] : '';
		return $this->fetchElement($this->name, $this->value, $this->element, $controlName);
	}

	/**
	 * Function to fetch a tooltip
	 *
	 * @param   string  $name          name of field
	 * @param   string  $value         value of field
	 * @param   string  &$node         node of field
	 * @param   string  $control_name  control_name of field
	 *
	 * @return  HTML
	 *
	 * @since  2.0
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$cbfolder = JPATH_SITE . '/components/com_comprofiler';
		$jomsocialfolder = JPATH_SITE . '/components/com_community';
		$jwfolder = JPATH_SITE . '/components/com_awdwall';
		$esfolder = JPATH_SITE . '/components/com_easysocial';
		$epfolder = JPATH_SITE . '/components/com_jsn';

		$jsString =	"
		<script>
			function checkIfExtInstalled(selectBoxName, extention)
			{
				var flag = 0;
				if (extention == 'cb')
				{";

					if (!Folder::exists($cbfolder))
					{
						$jsString .= " flag = 1";
					}

					$jsString .= "
				}
				else if (extention == 'jomsocial')
				{
					";

						if (!Folder::exists($jomsocialfolder))
						{
							$jsString .= " flag = 1";
						}

					$jsString .= "
				}
				else if (extention == 'jomwall')
				{
					";

						if (!Folder::exists($jwfolder))
						{
							$jsString .= " flag = 1";
						}

					$jsString .= "
				}
				else if (extention == 'easySocial')
				{
					";

						if (!Folder::exists($esfolder))
						{
							$jsString .= " flag = 1";
						}

					$jsString .= "
				}
				else if (extention == 'easyprofile')
				{
					";

						if (!Folder::exists($epfolder))
						{
							$jsString .= " flag = 1";
						}

					$jsString .= "
				}


				if (flag == 1)
				{
					var extentionName = jQuery('#jformintegration').val();
					alert('Selected component is not installed');
					jQuery('#jformintegration').val('joomla');
					jQuery('select').trigger('liszt:updated');
					jQuery('select').trigger('chosen:updated');
				}
			}
		</script>";

		echo   $jsString;

		$options[] = JHTML::_('select.option', 'joomla', Text::_('COM_JGIVE_INTERATION_JOOMLA'));
		$options[] = JHTML::_('select.option', 'cb', Text::_('COM_JGIVE_INTERATION_CB'));
		$options[] = JHTML::_('select.option', 'jomsocial', Text::_('COM_JGIVE_INTERATION_JOMSOCIAL'));
		$options[] = JHTML::_('select.option', 'jomwall', Text::_('COM_JGIVE_INTERATION_JOMWALL'));
		$options[] = JHTML::_('select.option', 'easySocial', Text::_('COM_JGIVE_EASYSOCIAL'));
		$options[] = JHTML::_('select.option', 'easyprofile', Text::_('COM_JGIVE_EASYPROFILE'));

		$fieldName = $name;

		$selectClass  = (JVERSION < '4.0.0') ? ' inputbox ' : ' form-select ';

		return HTMLHelper::_('select.genericlist',
			$options, $fieldName,
			'class="' . $selectClass . ' btn-group" onchange="checkIfExtInstalled(this.name, this.value)" ',
			'value', 'text', $value, $control_name . $name
		);
	}
}
