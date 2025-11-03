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

// Load Quick donation template
if ($this->params->get('quick_donation') == 1)
{
	echo $this->loadTemplate('quick_'. JGIVE_LOAD_BOOTSTRAP_VERSION);
}
else
{
	echo $this->loadTemplate('paymentform_'. JGIVE_LOAD_BOOTSTRAP_VERSION);
}
