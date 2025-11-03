<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

/**
 * JGive Model
 *
 * @since  2.5.0
 */
class JGiveModelDonationForm extends AdminModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   2.5.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.

		$form = $this->loadForm('com_jgive.donationform', 'donationform', array('control' => 'jform','load_data' => $loadData));

		return (empty($form)) ? false : $form;
	}
}
