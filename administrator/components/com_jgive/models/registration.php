<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die(';)');

JLoader::import('com_jgive.models.registration', JPATH_SITE . '/components');

/**
 * JgiveModeluserRegistration model class.
 *
 * @package  JGive
 * @since    2.3.0
 */
class JgiveModeluserRegistration extends JgiveModelregistration
{
	/**
	 * Method to Stor Client Data.
	 *
	 * @param   Array  $data  Data
	 *
	 * @return  int|boolean
	 *
	 * @since    2.3.0
	 */
	public function store($data)
	{
		$JgiveModelregistration = new JgiveModelregistration;

		$randpass = $JgiveModelregistration->rand_str(6);
		$userid = $JgiveModelregistration->createnewuser($data, $randpass);

		if ($userid)
		{
			$JgiveModelregistration->SendMailNewUser($data, $randpass);
		}

		return $userid;
	}
}
