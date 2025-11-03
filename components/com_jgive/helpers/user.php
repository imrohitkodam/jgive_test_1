<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

/**
 * User Helper.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class UserHelper
{
	/**
	 * Add customer
	 *
	 * @return  row
	 *
	 * @since   1.6
	 **/
	public function addCustomer()
	{
		$app  = Factory::getApplication();
		$db   = Factory::getDbo();
		$user = Factory::getUser();
		$post = $app->input->getArray($_POST);

		// First save data to the address table
		$row = Table::getInstance('Address', 'Table');

		// Set the id so that it updates the record rather than changing
		if (!$row->bind($post))
		{
			$this->setError($row->getError());

			return false;
		}

		if ($user->id)
		{
			$row->user_id = $user->id;
		}

		$row->type = 'billing';

		if (!$row->store())
		{
			$this->setError($row->getError());

			return false;
		}

		return $row->id;
	}

	/**
	 * Function for Verifying USer name is existing
	 *
	 * @param   String  $string  String
	 *
	 * @return  unknown_type
	 *
	 * @since   1.6
	 **/
	public function usernameExists($string)
	{
		// TODO Make this use ->load()

		$success  = false;
		$database = Factory::getDbo();
		$query    = "SELECT * FROM #__users WHERE username = " . $database->quoteuote($string) . " LIMIT 1";
		$database->setQuery($query);
		$result = $database->loadObject();

		if ($result)
		{
			$success = true;
		}

		return $success;
	}

	/**
	 * Function for Verifying email is existing
	 *
	 * @param   String  $string  String
	 * @param   String  $table   Table
	 *
	 * @return  unknown_type
	 *
	 * @since   1.6
	 **/
	public function emailExists($string, $table = 'users')
	{
		switch ($table)
		{
			case 'users':
			default:
				$table = '#__users';
		}

		$success  = false;
		$database = Factory::getDbo();

		$query = "SELECT * FROM $table WHERE email = " . $database->quote($string) . " LIMIT 1";
		$database->setQuery($query);
		$result = $database->loadObject();

		if ($result)
		{
			$success = true;
		}

		return $result;
	}

	/**
	 * Function for Creating new User
	 *
	 * @param   String   $details  Details
	 * @param   Boolean  &$msg     Msg
	 *
	 * @return  Array
	 *
	 * @since   1.6
	 **/
	public function createNewUser($details, &$msg)
	{
		$instance = User::getInstance();

		$config           = ComponentHelper::getParams('com_users');

		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);
		$md5_pass         = md5($details['password']);

		$instance->set('id', 0);
		$instance->set('name', $details['name']);
		$instance->set('username', $details['email']);
		$instance->set('password', $md5_pass);

		// Result should contain an email (check)
		$instance->set('email', $details['email']);
		$instance->set('usertype', 'deprecated');
		$instance->set('groups', array($defaultUserGroup));

		// If autoregister is set let's register the user
		$autoregister = isset($options['autoregister']) ? $options['autoregister'] : $config->get('autoregister', 1);

		if ($autoregister)
		{
			if (!$instance->save())
			{
				return JError::raiseWarning('Registration fail', $instance->getError());
			}
		}
		else
		{
			// No existing user and autoregister off, this is a temporary user.
			$instance->set('tmp_user', true);
		}

		$useractivation = '0';

		// Send registration confirmation mail
		JticketingHelperUser::_sendMail($instance, $details, $useractivation);

		return $instance;
	}

	/**
	 * Function for login
	 *
	 * @param   String   $credentials  Credentials
	 * @param   Boolean  $remember     Remember
	 * @param   String   $return       Return
	 *
	 * @return  array
	 *
	 * @since   1.6
	 **/
	public function login($credentials, $remember = true, $return = '')
	{
		$mainframe = Factory::getApplication();

		if (strpos($return, 'http') !== false && strpos($return, Uri::base()) !== 0)
		{
			$return = '';
		}

		$options             = array();
		$options['remember'] = (boolean) $remember;

		// Preform the login action
		$success = $mainframe->login($credentials);

		if ($return)
		{
			$mainframe->redirect($return);
		}

		return $success;
	}

	/**
	 * Function for logout
	 *
	 * @param   String  $return  Return
	 *
	 * @return  void
	 *
	 * @since   1.6
	 **/
	public function logout($return = '')
	{
		$mainframe = Factory::getApplication();

		/* Preform the logout action
		Check to see if user has a joomla account
		If so register with joomla userid else create joomla account*/
		$success = $mainframe->logout();

		if (strpos($return, 'http') !== false && strpos($return, Uri::base()) !== 0)
		{
			$return = '';
		}

		if ($return)
		{
			$mainframe->redirect($return);
		}

		return $success;
	}

	/**
	 * Getting Total Amount to be paid out
	 *
	 * @param   INT  $user_id  User id
	 * @param   INT  $unblock  Unblock
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 **/
	public function unblockUser($user_id, $unblock = 1)
	{
		$user = Factory::getUser((int) $user_id);

		if ($user->get('id'))
		{
			$user->set('block', !$unblock);

			if (!$user->save())
			{
				return false;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Getting Total Amount to be paid out
	 *
	 * @param   String  &$user           Ref Address of user
	 * @param   String  $details         Details
	 * @param   String  $useractivation  Useractivation
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 **/
	public function _sendMail(&$user, $details, $useractivation)
	{
		$mainframe = Factory::getApplication();

		$db = Factory::getDbo();

		$name = $user->get('name');

		if (empty($name))
		{
			$name = $user->get('email');
		}

		$email      = $user->get('email');
		$username   = $user->get('username');
		$activation = $user->get('activation');

		// Using the original generated pword for the email
		$password   = $details['password2'];
		$usersConfig = ComponentHelper::getParams('com_users');
		$sitename    = $mainframe->getCfg('sitename');
		$mailfrom    = $mainframe->getCfg('mailfrom');
		$fromname    = $mainframe->getCfg('fromname');
		$siteURL     = Uri::base();

		$subject = Text::sprintf('J2STORE_ACCOUNT_DETAILS', $name, $sitename);
		$subject = html_entity_decode($subject, ENT_QUOTES);

		if ($useractivation == 1)
		{
			$message = Text::sprintf(
			'J2STORE_SEND_MSG_ACTIVATE', $name, $sitename, $siteURL .
			"index.php?option=com_user&task=activate&activation=" . $activation, $siteURL,
			$email, $password
			);
		}
		else
		{
			$message = Text::sprintf('J2STORE_SEND_MSG', $name, $sitename, $siteURL, $email, $password);
		}

		$message = html_entity_decode($message, ENT_QUOTES);

		// Send email to user
		if (!$mailfrom || !$fromname)
		{
			$fromname = $rows[0]->name;
			$mailfrom = $rows[0]->email;
		}

		$success = JticketingHelperUser::_doMail($mailfrom, $fromname, $email, $subject, $message);

		return $success;
	}

	/**
	 * Getting Total Amount to be paid out
	 *
	 * @param   String   $from         From
	 * @param   String   $fromname     Fromname
	 * @param   String   $recipient    Recipient
	 * @param   String   $subject      Subject
	 * @param   String   $body         Body
	 * @param   String   $actions      Action
	 * @param   Boolean  $mode         Mode
	 * @param   INT      $cc           CC
	 * @param   String   $bcc          BCC
	 * @param   File     $attachment   Attachment
	 * @param   String   $replyto      Replyto
	 * @param   String   $replytoname  ReplyName
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 **/
	public function _doMail($from, $fromname, $recipient, $subject, $body, $actions = null,
		$mode = null, $cc = null, $bcc = null, $attachment = null, $replyto = null, $replytoname = null)
	{
		$success = false;

		$message = Factory::getMailer();
		$message->addRecipient($recipient);
		$message->setSubject($subject);
		$message->setBody($body);
		$sender = array(
			$from,
			$fromname
		);
		$message->setSender($sender);
		$sent = $message->send();

		if ($sent == '1')
		{
			$success = true;
		}

		return $success;
	}
}
