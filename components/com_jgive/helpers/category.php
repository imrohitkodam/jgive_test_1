<?php
/**
 * @package     Jgive
 * @subpackage  com_Jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2024 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Categories\Categories;

/**
 * Jgive Categories helper.
 *
 * @since  1.0.0
 */
class JGiveCategories extends Categories
{
	/**
	 * Method acts as a consturctor
	 *
	 * @param   ARRAY  $options  categories with parent
	 *
	 * @since   1.0.0
	 */
	public function __construct($options = array())
	{
		$options['table'] = '#__categories';
		$options['extension'] = 'com_jgive';
		parent::__construct($options);
	}
}
