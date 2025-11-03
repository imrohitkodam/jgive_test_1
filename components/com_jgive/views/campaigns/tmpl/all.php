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

// Jomsocial toolbar
if (isset($this->jomsocialToolbarHtml))
{
	echo $this->jomsocialToolbarHtml;
}

if ($this->params['layout_to_load'] == 'blog_layout')
{
	echo $this->loadTemplate('blog_' . JGIVE_LOAD_BOOTSTRAP_VERSION);
}
else
{
	echo $this->loadTemplate('pin_' . JGIVE_LOAD_BOOTSTRAP_VERSION);
}
