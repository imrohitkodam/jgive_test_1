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


/**
 * Supports an HTML select list of courses
 *
 * @since  1.0.0
 */
class JFormFieldImagedisplay extends JFormField
{
	protected $type = 'Imagedisplay';

	/**
	 * Methods to display giveback image
	 *
	 * @return string $html
	 *
	 * @since    2.1
	 */
	public function getInput()
	{
		if ($this->value)
		{
			$html = '<img src="' . $this->value . '"class=" span2 giveback_img" id="uploaded_media">';

			return $html;
		}
	}
}
