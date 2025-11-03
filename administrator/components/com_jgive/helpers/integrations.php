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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

// Component Helper

/**
 * Integrations Helper
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JgiveIntegrationsHelper_Backend
{
	/**
	 * Get User Profile Url
	 *
	 * @param   INT  $userid  User Id
	 *
	 * @return  link
	 */
	public function getUserProfileUrl($userid)
	{
		$path = JPATH_SITE . '/components/com_jgive/helper.php';

		if (!class_exists('jgiveFrontendHelper'))
		{
			JLoader::register('jgiveFrontendHelper', $path);
			JLoader::load('jgiveFrontendHelper');
		}

		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$params              = ComponentHelper::getParams('com_jgive');
		$integration_option  = $params->get('integration');
		$link                = '';

		if ($integration_option == 'joomla')
		{
			$link = '';
		}
		elseif ($integration_option == 'cb')
		{
			$itemid = $jgiveFrontendHelper->getItemId('option=com_comprofiler');
			$link   = Uri::root() . substr(
			Route::_('index.php?option=com_comprofiler&task=userprofile&user=' . $userid . '&Itemid=' . $itemid),
			strlen(Uri::base(true)) + 1
			);
		}
		elseif ($integration_option == 'jomsocial')
		{
			$link   = '';
			$jspath = JPATH_ROOT . '/components/com_community';

			if (file_exists($jspath))
			{
				include_once $jspath . '/libraries/core.php';
				$link = Uri::root() . substr(CRoute::_('index.php?option=com_community&view=profile&userid=' . $userid), strlen(Uri::base(true)) + 1);
			}
		}
		elseif ($integration_option == 'jomwall')
		{
			if (!class_exists('AwdwallHelperUser'))
			{
				require_once JPATH_SITE . '/components/com_awdwall/helpers/user.php';
			}

			$awduser = new AwdwallHelperUser;
			$Itemid  = $awduser->getComItemId();
			$link    = Route::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $userid . '&Itemid=' . $Itemid);
		}

		return $link;
	}

	/**
	 * Get user avatar
	 *
	 * @param   INT  $userid  User Id
	 *
	 * @return  STRING  Avatar Url
	 */
	public function getUserAvatar($userid)
	{
		$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
		$params                  = ComponentHelper::getParams('com_jgive');
		$integration_option      = $params->get('integration');
		$uimage                  = '';

		if ($integration_option == "joomla")
		{
			$uimage = '';
		}
		elseif ($integration_option == "cb")
		{
			$uimage = $JgiveIntegrationsHelper->getCBUserAvatar($userid);
		}
		elseif ($integration_option == "jomsocial")
		{
			$uimage = $JgiveIntegrationsHelper->getJomsocialUserAvatar($userid);
		}
		elseif ($integration_option == "jomwall")
		{
			$uimage = $JgiveIntegrationsHelper->getJomwallUserAvatar($userid);
		}

		return $uimage;
	}

	/**
	 * Function getCBUserAvatar
	 *
	 * @param   INT  $userid  User Id
	 *
	 * @return  campaign title
	 */
	public function  getCBUserAvatar($userid)
	{
		$db = Factory::getDbo();
		$q  = "SELECT a.id,a.username,a.name, b.avatar, b.avatarapproved
            FROM #__users a, #__comprofiler b
            WHERE a.id=b.user_id AND a.id=" . $userid;
		$db->setQuery($q);
		$user     = $db->loadObject();
		$img_path = Uri::root() . "images/comprofiler";

		if (isset($user->avatar) && isset($user->avatarapproved))
		{
			if (substr_count($user->avatar, "/") == 0)
			{
				$uimage = $img_path . '/tn' . $user->avatar;
			}
			else
			{
				$uimage = $img_path . '/' . $user->avatar;
			}
		}
		elseif (isset($user->avatar))
		// Avatar not approved
		{
			$uimage = Uri::root() . "/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}
		else
		// No avatar
		{
			$uimage = Uri::root() . "/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}

		return $uimage;
	}

	/**
	 * Get getJomsocialUserAvatar
	 *
	 * @param   INT  $userid  User Id
	 *
	 * @return  image
	 */
	public function  getJomsocialUserAvatar($userid)
	{
		$mainframe = Factory::getApplication();

		// Included to get jomsocial avatar
		$uimage    = '';
		$jspath    = JPATH_ROOT . '/components/com_community';

		if (file_exists($jspath))
		{
			include_once $jspath . '/libraries/core.php';
			$user   = CFactory::getUser($userid);
			$uimage = $user->getThumbAvatar();

			if ($mainframe->isClient('administrator'))
			{
				$uimage = str_replace('administrator/', '', $uimage);
			}
		}

		return $uimage;
	}

	/**
	 * Function getJomwallUserAvatar
	 *
	 * @param   INT  $userid  User Id
	 *
	 * @return  campaign title
	 */
	public function  getJomwallUserAvatar($userid)
	{
		if (!class_exists('AwdwallHelperUser'))
		{
			require_once JPATH_SITE . '/components/com_awdwall/helpers/user.php';
		}

		$awduser = new AwdwallHelperUser;
		$uimage  = $awduser->getAvatar($userid);

		return $uimage;
	}

	/**
	 * Function loadScriptOnce
	 *
	 * @param   File  $script  Script
	 *
	 * @return  campaign title
	 */
	public function  loadScriptOnce($script)
	{
		$doc = Factory::getDocument();
		$flg = 0;

		foreach ($doc->_scripts as $name => $ar)
		{
			if ($name == $script)
			{
				$flg = 1;
			}
		}

		if ($flg == 0)
		{
			HTMLHelper::_('script', $script);
		}
	}

	/**
	 * Function for  profile import
	 *
	 * @param   String  $paymentform  Paymentform
	 * @param   INT     $userid       UserId
	 *
	 * @return  campaign title
	 */
	public function  profileImport($paymentform = '', $userid = '')
	{
		$JgiveIntegrationsHelper = new JgiveIntegrationsHelper_Backend;

		$cdata['campaign'] = new stdclass;

		$params      = ComponentHelper::getparams('com_jgive');
		$integration = $params->get('integration');

		if ($integration == 'joomla')
		{
			$cdata = $JgiveIntegrationsHelper->joomlaProfileimport($paymentform, $userid);
		}
		elseif ($integration == 'jomsocial')
		{
			$cdata = $JgiveIntegrationsHelper->jomsocialProfileimport($paymentform, $userid);
		}
		elseif ($integration == 'cb')
		{
			$cdata = $JgiveIntegrationsHelper->cbProfileimport($paymentform, $userid);
		}

		return $cdata;
	}

	/**
	 * Function profile import for joomla
	 *
	 * @param   String  $paymentform  Paymentform
	 * @param   INT     $userid       UserId
	 *
	 * @return  campaign title
	 */
	public function  joomlaProfileimport($paymentform = '', $userid = '')
	{
		$cdata['campaign'] = new stdclass;
		$params            = ComponentHelper::getparams('com_jgive');

		if ($userid)
		{
			$user = Factory::getuser($userid);
		}
		else
		{
			$user = Factory::getuser();
		}

		$userinfo      = ArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$user_profile  = UserHelper::getProfile($user->id);

		// Convert object to array
		$user_profile  = ArrayHelper::fromObject($user_profile, $recurse = true, $regex = null);
		$mapping       = $params->get('fieldmap');
		$mapping_field = explode("\n", $mapping);

		foreach ($mapping_field as $each_field)
		{
			$field        = explode("=", $each_field);
			$jgive_field  = '';
			$joomla_field = '';

			if (isset($field[1]))
			{
				$jgive_field  = trim($field[0]);
				$joomla_field = trim($field[1]);

				// Remove campalsory star
				$joomla_field = trim(str_replace(',*', '', $joomla_field));
			}

			// For security mapping not allowed for user password
			if ($joomla_field != 'password')
			{
				if (array_key_exists($joomla_field, $userinfo))
				{
					// For paymentform layout
					if ($paymentform)
					{
						$cdata[$jgive_field] = $userinfo[$joomla_field];
					}
					else
					{
						$cdata['campaign']->$jgive_field = $userinfo[$joomla_field];
					}
				}
				else
				{
					// TO DO For country/state/city
					if (!empty($user_profile['profile']))
					{
						if (array_key_exists($joomla_field, $user_profile['profile']))
						{
							if ($paymentform)
							{
								$cdata[$jgive_field] = $user_profile['profile'][trim($joomla_field)];
							}
							else
							{
								$cdata['campaign']->$jgive_field = $user_profile['profile'][trim($joomla_field)];
							}
						}
					}
				}
			}
		}

		return $cdata;
	}

	/**
	 * Function profile import for cb
	 *
	 * @param   String  $paymentform  Paymentform
	 * @param   INT     $userid       UserId
	 *
	 * @return  campaign title
	 */
	public function  cbProfileimport($paymentform, $userid = '')
	{
		// Load CB framework
		global $_CB_framework, $mainframe, $_CB_database, $ueConfig;

		if (defined('JPATH_ADMINISTRATOR'))
		{
			if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';

				return false;
			}

			include_once JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';
		}
		else
		{
			if (!file_exists($mainframe->getCfg('absolute_path') . '/administrator/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';

				return false;
			}

			include_once $mainframe->getCfg('absolute_path') . '/administrator/components/com_comprofiler/plugin.foundation.php';
		}

		cbimport('cb.plugins');
		cbimport('cb.html');
		cbimport('cb.database');
		cbimport('language.front');
		cbimport('cb.snoopy');
		cbimport('cb.imgtoolbox');

		if ($userid)
		{
			$myId = $_CB_framework->myId($userid);
		}
		else
		{
			$myId = $_CB_framework->myId();
		}

		$cbUser = CBuser::getInstance($myId);

		if (!$cbUser)
		{
			$cbUser =& CBuser::getInstance(null);
		}

		$user =& $cbUser->getUserData();

		$cdata         = array();
		$params        = ComponentHelper::getparams('com_jgive');
		$userinfo      = ArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$mapping       = $params->get('cb_fieldmap');
		$mapping_field = explode("\n", $mapping);

		foreach ($mapping_field as $each_field)
		{
			$field       = explode("=", $each_field);
			$jgive_field = '';
			$CB_field    = '';

			if (isset($field[1]))
			{
				$jgive_field = trim($field[0]);
				$CB_field    = trim($field[1]);

				// Remove campalsory star
				$CB_field    = trim(str_replace(',*', '', $CB_field));
			}

			// For security mapping not allowed for user password
			if ($CB_field != 'password')
			{
				if (array_key_exists($CB_field, $userinfo))
				{
					// For paymentform layout
					if ($paymentform)
					{
						$cdata[$jgive_field] = $userinfo[$CB_field];
					}

					// For create campaign layout
					else
					{
						$cdata['campaign']->$jgive_field = $userinfo[$CB_field];
					}
				}
			}
		}

		return $cdata;
	}

	/**
	 * Get Campaign Title
	 *
	 * @param   String  $paymentform  Paymentform
	 * @param   INT     $userid       UserId
	 *
	 * @return  array
	 */
	public function  jomsocialProfileimport($paymentform = '', $userid = '')
	{
		$cdata['campaign'] = new stdclass;
		$params            = ComponentHelper::getparams('com_jgive');
		$jspath            = JPATH_ROOT . '/components/com_community';

		if (!file_exists($jspath))
		{
			return;
		}

		include_once $jspath . '/libraries/core.php';
		$userpro = CFactory::getUser();

		// Get jomsocial user profile info
		if ($userid)
		{
			$user = CFactory::getUser($userid);
		}
		else
		{
			$user = CFactory::getUser();
		}

		$userinfo      = ArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$mapping       = $params->get('jomsocial_fieldmap');
		$mapping_field = explode("\n", $mapping);

		foreach ($mapping_field as $each_field)
		{
			$field           = explode("=", $each_field);
			$jgive_field     = '';
			$jomsocial_field = '';

			if (isset($field[1]))
			{
				$jgive_field     = trim($field[0]);
				$jomsocial_field = trim($field[1]);

				// Remove campalsory star
				$jomsocial_field = trim(str_replace(',*', '', $jomsocial_field));
			}

			// For security mapping not allowed for user password
			if ($jomsocial_field != 'password')
			{
				if (array_key_exists($jomsocial_field, $userinfo))
				{
					// For paymentform layout
					if ($paymentform)
					{
						if (!empty($userinfo[$jomsocial_field]))
						{
							$cdata[$jgive_field] = $userinfo[$jomsocial_field];
						}
					}
					// For create campaign layout
					else
					{
						if (!empty($userinfo[$jomsocial_field]))
						{
							$cdata['campaign']->$jgive_field = $userinfo[$jomsocial_field];
						}
					}
				}
				else
				{
					$userInfo = $userpro->getInfo($jomsocial_field);

					if (!empty($userInfo))
					{
						if ($paymentform)
						{
							$cdata[$jgive_field] = $userInfo;
						}
						else
						{
							$cdata['campaign']->$jgive_field = $userInfo;
						}
					}
				}
			}
		}

		return $cdata;
	}

	/**
	 * Function to check profile completion
	 *
	 * @return  campaign title
	 */
	public function  profileChecking()
	{
		$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
		$params                  = ComponentHelper::getParams('com_jgive');
		$integration             = $params->get('integration');
		$msg_field_required      = array();

		if ($integration == 'joomla')
		{
			$msg_field_required = $JgiveIntegrationsHelper->joomlaProfileChecking($params);
		}
		elseif ($integration == 'jomsocial')
		{
			// The $msg_field_required=JgiveIntegrationsHelper::jomsocialProfileChecking($params);
		}

		return $msg_field_required;
	}

	/**
	 * Function to check integration joomla user profile complete
	 *
	 * @param   String  $params  Param
	 *
	 * @return  Array
	 */
	public function  joomlaProfileChecking($params)
	{
		$msg_field_required = array();
		$user               = Factory::getUser();

		// Convert object to array
		$user_profile       = UserHelper::getProfile($user->id);

		// Convert object to array
		$user               = ArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$user_profile       = ArrayHelper::fromObject($user_profile, $recurse = true, $regex = null);
		$mapping            = $params->get('fieldmap');
		$required_field     = explode("\n", $mapping);

		if (isset($required_field))
		{
			foreach ($required_field as $eachfield)
			{
				$eachfield = explode(",", $eachfield);

				// Indentify required field
				if (isset($eachfield[1]))
				{
					$row            = $eachfield[0];
					$required_tmp   = explode("=", $row);

					// Get required field name
					$required_field = $required_tmp[1];

					// Check user value present or not in user table
					// For security mapping not allowed for user password
					if ($required_field != 'password')
					{
						// If field not set is user array  then check  field in user profile array
						if ((array_key_exists($required_field, $user)) OR (array_key_exists($required_field, $user_profile['profile'])))
						{
							$userfield        = '';
							$userProfilefield = '';

							if (!empty($user[$required_field]))
							{
								$userfield = trim($user[$required_field]);
							}

							if (empty($userfield))
							{
								if (!empty($user_profile['profile'][$required_field]))
								{
									$userProfilefield = trim($user_profile['profile'][$required_field]);
								}

								if (empty($userProfilefield))
								{
									$msg_field_required[] = $required_field;
								}
							}
						}

						// If user not edit his account first time after profile plugin is enabled
						elseif (empty($user_profile['profile']))
						{
							$msg_field_required[] = $required_field;
						}
					}
				}
			}
		}

		return $msg_field_required;
	}

	/**
	 * Function to check integration jomsocial user profile complete
	 *
	 * @param   String  $params  Params
	 *
	 * @return  campaign title
	 */
	public function  jomsocialProfileChecking($params)
	{
		$jspath = JPATH_ROOT . '/components/com_community';

		if (!file_exists($jspath))
		{
			return;
		}

		include_once $jspath . '/libraries/core.php';

		$user =& CFactory::getUser();
		$msg_field_required = array();

		// Convert object to array
		$user               = ArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$mapping            = $params->get('jomsocial_fieldmap');
		$required_field     = explode("\n", $mapping);

		if (isset($required_field))
		{
			foreach ($required_field as $eachfield)
			{
				$eachfield = explode(",", $eachfield);

				// Indentify required field
				if (isset($eachfield[1]))
				{
					$row            = $eachfield[0];
					$required_tmp   = explode("=", $row);

					// Get required field name
					$required_field = trim($required_tmp[1]);

					// Check user value present or not in user table
					// for security mapping not allowed for user password
					if ($required_field != 'password')
					{
						// If field not set is user array  then check  field in user profile array
						if (array_key_exists($required_field, $user))
						{
							$userfield        = '';
							$userProfilefield = '';

							if (!empty($user[$required_field]))
							{
								$userfield = trim($user[$required_field]);
							}
						}
						else
						{
							$userpro =& CFactory::getUser();
							$userInfo = $userpro->getInfo($required_field);

							if (empty($userInfo))
							{
								$msg_field_required[] = $required_field;
							}
						}
					}
				}
			}
		}

		return $msg_field_required;
	}
}
