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

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;


$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (File::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jgive');
}

/**
 * Custom Legend field for component params.
 *
 * @package  JGive
 *
 * @since    2.2
 */
class JFormFieldLegend extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Legend';

	/**
	 * Method to get the field input markup.
	 *
	 * @return string  The field input markup.
	 *
	 * @since 1.6
	 */
	public function getInput()
	{
		$document = Factory::getDocument();
		HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive_admin.css');

		$legendClass = 'jgive-elements-legend';
		$hintClass = "jgive-elements-legend-hint";

		if (JVERSION < '3.0')
		{
			$element = (array) $this->element;
			$hint = $element['@attributes']['hint'];
		}
		else
		{
			$hint = $this->hint;

			$script = 'techjoomla.jQuery(document).ready(function(){
				techjoomla.jQuery("#' . $this->id . '").parent().removeClass("controls");
				techjoomla.jQuery("#' . $this->id . '").parent().parent().removeClass("control-group");
			});';

			$document->addScriptDeclaration($script);
		}

		// Show them a legend.
		$return = '<legend class="clearfix ' . $legendClass . '" id="' . $this->id . '">' . Text::_($this->value) . '</legend>';

		// Show them a hint below the legend.
		// Let them go - GaGa about the legend.
		if (!empty($hint))
		{
			$return .= '<span class="disabled ' . $hintClass . '">' . Text::_($hint) . '</span>';
			$return .= '<br/><br/>';
		}

		return $return;
	}
}
