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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;


/**
 * JgiveModelregistration model class.
 *
 * @package  JGive
 * @since    1.8.1
 */
class JgiveModelregistration extends BaseDatabaseModel
{
	/**
	 * Method to Stor Client Data.
	 *
	 * @param   Array  $data  Data
	 *
	 * @return boolean
	 *
	 * @since    1.8.1
	 */
	public function store($data)
	{
		$app    = Factory::getApplication();
		$input  = $app->input;
		$id     = $input->get('cid');
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$user   = Factory::getUser();

		if (!$user->id)
		{
			$jgiveModelRegistration = new jgiveModelregistration;

			if (!$data['user_email'])
			{
				return false;
			}

			$query->select('id');
			$query->from($db->qn('#__users'));
			$query->where(
				$db->qn('#__users.email') . ' = ' . $db->quote($db->escape($data['user_email'])) . ' OR ' .
				$db->qn('#__users.username') . ' = ' . $db->quote($db->escape($data['user_name']))
			);
			$db->setQuery($query);
			$userExist = $db->loadResult();

			if ($userExist)
			{
				$input->set('message', Text::_('COM_JGIVE_USER_EXIST'));

				return false;
			}

			// Generate the random password & create a new user
			$randPass = $jgiveModelRegistration->rand_str(6);
			$userId   = $jgiveModelRegistration->createnewuser($data, $randPass);

			if ($userId)
			{
				PluginHelper::importPlugin('user');

				if (!$userExist)
				{
					$jgiveModelRegistration->SendMailNewUser($data, $randPass);
				}

				$cb_params   = ComponentHelper::getParams('com_jgive');
				$integration = $cb_params->get('integration');

				if ($integration == 'cb')
				{
					$cbobj            = new stdClass;
					$cbobj->user_id   = $userId;
					$cbobj->id        = $userId;
					$cbobj->firstname = $data['first_name'];
					$cbobj->lastname  = $data['last_name'];
					$cbobj->confirmed = 1;
					$db->insertObject('#__comprofiler', $cbobj, 'user_id');
				}

				$remember = $input->get('remember', false, 'BOOLEAN');
				$options                 = array('remember' => $remember);
				$options['autoregister'] = 0;
				$user                    = array();
				$user['username']        = $data['user_name'];
				$user['email']           = $data['user_email'];
				$user['password']        = $randPass;
				$app->login(
					array('username' => $data['user_name'],'password' => $randPass),
					array('silent' => true)
				);
			}
		}

		return true;
	}

	/**
	 * Method to Create a new User
	 *
	 * @param   Array   $data      Data
	 * @param   String  $randPass  Random Password
	 *
	 * @return boolean|integer
	 *
	 * @since    1.8.1
	 */
	public function createnewuser($data, $randPass)
	{
		$user      = clone Factory::getUser();
		$user->set('username', $data['user_name']);
		$user->set('password1', $randPass);
		$user->set('name', (isset($data['name']) && !empty($data['name'])) ? $data['name'] : $data['user_name']);
		$user->set('email', $data['user_email']);

		if (isset($data['user_id']) && !empty($data['user_id']))
		{
			$user->set('id', $data['user_id']);
		}

		// Password encryption
		$salt           = UserHelper::genRandomPassword(32);
		$crypt          = UserHelper::hashPassword($user->password1);

		$user->password = "$crypt";
		$user->password_clear = $randPass;

		// User group/type
		if (isset($data['user_id']) && !empty($data['user_id']))
		{
			$user->set('id', $data['user_id']);
		}
		else
		{
			$user->set('id', '');
		}

		$user->set('usertype', 'Registered');

		$userConfig = ComponentHelper::getParams('com_users');

		// Default to Registered.
		$defaultUserGroup = $userConfig->get('new_usertype', 2);
		$user->set('groups', array($defaultUserGroup));

		$date = Factory::getDate();
		$user->set('registerDate', $date->toSQL());

		if (!empty($data['user_id']))
		{
			$user->set('lastvisitDate', $date->toSQL());
		}
		else
		{
			if (JVERSION < '4.0.0')
			{
				$user->set('lastvisitDate', Text::_('JNEVER'));
			}
		}

		// True on success, false otherwise
		if (!$user->save())
		{
			return false;
		}

		return $user->id;
	}

	/**
	 * Method to update a User
	 *
	 * @param   Array  $data  Data
	 *
	 * @return integer
	 *
	 * @since    2.3.0
	 */
	public function updateUser($data)
	{
		$user      = clone Factory::getUser($data['user_id']);
		$user->set('username', $data['user_name']);
		$user->set('name', $data['name']);
		$user->set('email', $data['user_email']);
		$user->set('id', $data['user_id']);
		$user->save();

		return $user->id;
	}

	/**
	 * Method to Create a random character generator for password
	 *
	 * @param   INT     $length  Length
	 * @param   String  $chars   Characters and Numbers
	 *
	 * @return string
	 *
	 * @since    1.8.1
	 */
	public function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
		// Length of character list
		$chars_length = (strlen($chars) - 1);

		// Start our string
		$string = $chars[rand(0, $chars_length)];

		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			// Grab a random character from our list
			$r = $chars[rand(0, $chars_length)];

			// Make sure the same two characters don't appear next to each other
			if ($r != $string[$i - 1])
			{
				$string .= $r;
			}
		}

		// Return the string
		return $string;
	}

	/**
	 * Method to Sending mail to user
	 *
	 * @param   Array   $data      Data
	 * @param   String  $randPass  Random Password
	 *
	 * @return boolean
	 *
	 * @since    1.8.1
	 */
	public function SendMailNewUser($data, $randPass)
	{
		$app      = Factory::getApplication();
		$mailfrom = $app->getCfg('mailfrom');
		$fromname = $app->getCfg('fromname');
		$sitename = $app->getCfg('sitename');

		$email    = $data['user_email'];
		$subject  = Text::_('COM_JGIVE_SA_REGISTRATION_SUBJECT');
		$find1    = array('{sitename}');
		$replace1 = array($sitename);
		$subject  = str_replace($find1, $replace1, $subject);

		$message = Text::_('COM_JGIVE_SA_REGISTRATION_USER');
		$find    = array(
			'{firstname}',
			'{sitename}',
			'{register_url}',
			'{username}',
			'{password}'
		);
		$replace = array($data['user_name'], $sitename, Uri::root(), $data['user_name'], $randPass);
		$message = str_replace($find, $replace, $message);
		Factory::getMailer()->sendMail($mailfrom, $fromname, $email, $subject, $message);

		$messageadmin = Text::_('COM_JGIVE_SA_REGISTRATION_ADMIN');
		$find2        = array('{sitename}', '{username}');
		$replace2     = array($sitename, $data['user_name']);
		$messageadmin = str_replace($find2, $replace2, $messageadmin);
		Factory::getMailer()->sendMail($mailfrom, $fromname, $mailfrom, $subject, $messageadmin);

		return true;
	}
}
