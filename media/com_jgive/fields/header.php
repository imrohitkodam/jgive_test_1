<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('JPATH_BASE') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;


/**
 * Custom Header field for component params.
 *
 * @package  JGive
 *
 * @since    2.2
 */
class JFormFieldHeader extends JFormField
{
	protected $type = 'Header';

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
		$return = '<div class="jbolo_div_outer">
			<div class="jbolo_div_inner">
				' . Text::_($this->value) . '
			</div>
		</div>';

		return $return;
	}
}
