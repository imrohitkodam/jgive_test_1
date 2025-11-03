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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

// Component Helper

$helperPath = JPATH_SITE . '/components/com_jgive/helper.php';

if (!class_exists('jgiveFrontendHelper'))
{
	// Require_once $path;
	JLoader::register('jgiveFrontendHelper', $helperPath);
	JLoader::load('jgiveFrontendHelper');
}

/**
 * IntegrationsHelper form controller class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JgiveIntegrationsHelper
{
	/**
	 * IntegrationHelper constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$params                   = ComponentHelper::getParams('com_jgive');
		$socialIntegrationOption  = $params->get('integration');

		if ($socialIntegrationOption != 'none')
		{
			if ($socialIntegrationOption == 'joomla')
			{
				jimport('techjoomla.jsocial.joomla');
			}
			elseif ($socialIntegrationOption == 'jomsocial')
			{
				jimport('techjoomla.jsocial.jomsocial');
			}
			elseif ($socialIntegrationOption == 'easySocial')
			{
				jimport('techjoomla.jsocial.easysocial');
			}
			elseif($socialIntegrationOption == 'cb')
			{
				jimport('techjoomla.jsocial.cb');
			}
			elseif($socialIntegrationOption == 'jomwall')
			{
				jimport('techjoomla.jsocial.jomwall');
			}
		}
	}

	/** Function for  getting User profile url
	 *
	 * @param   INT      $userid    UserId
	 * @param   BOOLEAN  $relative  whether to return relative or absolute url
	 *
	 * @return  link
	 */
	public function getUserProfileUrl($userid, $relative = false)
	{
		$params                   = ComponentHelper::getParams('com_jgive');
		$socialIntegrationOption  = $params->get('integration');
		$link                     = '';
		$user                     = Factory::getUser($userid);

		if ($socialIntegrationOption == 'joomla')
		{
			$sociallibraryclass = new JSocialJoomla;
		}
		elseif ($socialIntegrationOption == 'cb')
		{
			$sociallibraryclass = new JSocialCB;
		}
		elseif ($socialIntegrationOption == 'jomsocial')
		{
			$sociallibraryclass = new JSocialJomsocial;
		}
		elseif ($socialIntegrationOption == 'easySocial')
		{
			$sociallibraryclass = new JSocialEasysocial;
		}
		elseif ($socialIntegrationOption == 'jomwall')
		{
			$sociallibraryclass = new JSocialJomwall;
		}
		elseif ($socialIntegrationOption == 'easyprofile')
		{
			if (!class_exists('JsnHelper'))
			{
				JLoader::import('components.com_jsn.helpers.helper', JPATH_SITE);
			}

			$user = JsnHelper::getUser($userid);
			$link = $user->getLink();

			return $link;
		}

		$link = $sociallibraryclass->getProfileUrl($user, $relative);

		return $link;
	}

	/** Function for  getting Easy social User Avtar Image
	 *
	 * @param   INT  $userid  UserId
	 *
	 * @return  void
	 */
	public function getEasySocialUserAvatar($userid)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
		$user   = Foundry::user($userid);
		$uimage = $user->getAvatar();

		return $uimage;
	}

	/** Function for  getting User Avtar Image
	 *
	 * @param   INT      $userid    UserId
	 * @param   BOOLEAN  $relative  whether to return relative or absolute url
	 *
	 * @return  STRING  Avatar Url
	 */
	public function getUserAvatar($userid, $relative = false)
	{
		$user                    = Factory::getUser($userid);
		$params                  = ComponentHelper::getParams('com_jgive');
		$socialIntegrationOption = $params->get('integration');
		$gravatar                = $params->get('gravatar');
		$uimage                  = '';
		$app                     = Factory::getApplication();
		$siteHTTPS               = $app->getCfg('force_ssl');

		if ($socialIntegrationOption == "joomla")
		{
			if ($gravatar)
			{
				$user     = Factory::getUser($userid);
				$usermail = $user->get('email', '');

				// Refer https://en.gravatar.com/site/implement/images/php/
				$hash   = md5(strtolower(trim($usermail ? $usermail : '')));
				$uimage   = 'http://www.gravatar.com/avatar/' . $hash . '?s=32';

				if ($siteHTTPS == 2)
				{
					$uimage   = 'https://www.gravatar.com/avatar/' . $hash . '?s=32';
				}

				return $uimage;
			}
			else
			{
				if ($relative)
				{
					$uimage = 'media/com_jgive/images/default_avatar.png';
				}
				else
				{
					Uri::root() . 'media/com_jgive/images/default_avatar.png';
				}
			}
		}
		else
		{
			if ($socialIntegrationOption == "cb")
			{
				$sociallibraryclass = new JSocialCB;
			}
			elseif ($socialIntegrationOption == "jomsocial")
			{
				$sociallibraryclass = new JSocialJomsocial;
			}
			elseif ($socialIntegrationOption == "easySocial")
			{
				$sociallibraryclass = new JSocialEasysocial;
			}
			elseif ($socialIntegrationOption == "jomwall")
			{
				$sociallibraryclass = new JSocialJomwall;
			}
			elseif ($socialIntegrationOption == "easyprofile")
			{
				if (!class_exists('JsnHelper'))
				{
					JLoader::import('components.com_jsn.helpers.helper', JPATH_SITE);
				}

				if ($userid)
				{
					$user   = JsnHelper::getUser($userid);
					$uimage = ($relative)?$user->avatar:Uri::root(true) . '/' . $user->avatar;
				}
				else
				{
					// Default user avtar for guest user
					$uimage = ($relative)?'components/com_jsn/assets/img/default.jpg':Uri::root(true) . '/' . 'components/com_jsn/assets/img/default.jpg';
				}

				return $uimage;
			}

			$uimage = $sociallibraryclass->getAvatar($user, '', $relative);
		}

		return $uimage;
	}

	/** Function for  getting CB user Image
	 *
	 * @param   INT  $userid  UserId
	 *
	 * @return  void
	 */
	public function getCBUserAvatar($userid)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('a.id');
		$query->select('a.username');
		$query->select('a.name');
		$query->select('b.avatar');
		$query->select('b.avatarapproved');
		$query->from($db->qn('#__users', 'a'));
		$query->join('INNER', $db->qn('#__comprofiler', 'b') . 'ON (' . $db->qn('a.id') . ' = ' . $db->qn('b.user_id') . ')');
		$query->where($db->qn('a.id') . ' = ' . $userid);

		$db->setQuery($query);
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

	/** Function for  getting Jomsocial user Image
	 *
	 * @param   INT  $userid  UserId
	 *
	 * @return  void
	 */
	public function getJomsocialUserAvatar($userid)
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

	/** Function for  getting Jom wall user Image
	 *
	 * @param   INT  $userid  UserId
	 *
	 * @return  void
	 */
	public function getJomwallUserAvatar($userid)
	{
		if (!class_exists('AwdwallHelperUser'))
		{
			require_once JPATH_SITE . '/components/com_awdwall/helpers/user.php';
		}

		$awduser = new AwdwallHelperUser;
		$uimage  = $awduser->getAvatar($userid);

		return $uimage;
	}

	/** Function for  load Script
	 *
	 * @param   File  $script  Script
	 *
	 * @return  void
	 */
	public function loadScriptOnce($script)
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

	/** Function for  profile import
	 *
	 * @param   String  $paymentform  Layout name
	 *
	 * @return  Array  cdata
	 */
	public function profileImport($paymentform = '')
	{
		$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
		$cdata['campaign'] = new stdclass;

		$params      = ComponentHelper::getparams('com_jgive');
		$integration = $params->get('integration');

		if ($integration == 'joomla')
		{
			$cdata = $JgiveIntegrationsHelper->joomlaProfileimport($paymentform);
		}
		elseif ($integration == 'jomsocial')
		{
			$cdata = $JgiveIntegrationsHelper->jomsocialProfileimport($paymentform);
		}
		elseif ($integration == 'cb')
		{
			$cdata = $JgiveIntegrationsHelper->cbProfileimport($paymentform);
		}
		elseif ($integration == 'easySocial')
		{
			$cdata = $JgiveIntegrationsHelper->EasySocialProfileimport($paymentform);
		}
		elseif ($integration == 'easyprofile')
		{
			$cdata = $JgiveIntegrationsHelper->EasyProfileimport($paymentform);
		}

		return $cdata;
	}

	/** Function profile import for joomla
	 *
	 * @param   String  $paymentform  Layout name
	 *
	 * @return  Array  cdata
	 */
	public function joomlaProfileimport($paymentform = '')
	{
		$cdata['campaign'] = new stdclass;
		$params            = ComponentHelper::getparams('com_jgive');
		$user              = Factory::getuser();
		$userinfo          = ArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$user_profile      = UserHelper::getProfile($user->id);

		// Convert object to array
		$user_profile      = ArrayHelper::fromObject($user_profile, $recurse = true, $regex = null);
		$mapping           = $params->get('fieldmap');
		$mapping_field     = explode("\n", $mapping);

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
					// For create campaign layout
					else
					{
						$cdata['campaign']->$jgive_field = $userinfo[$joomla_field];
					}
				}
				else
				{
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

	/** Function profile import for cb
	 *
	 * @param   String  $paymentform  Layout name
	 *
	 * @return  Array  cdata
	 */
	public function cbProfileimport($paymentform)
	{
		// Load CB framework
		global $_CB_framework, $mainframe, $_CB_database, $ueConfig;

		if (defined('JPATH_ADMINISTRATOR'))
		{
			if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
			{
				echo Text::_('COM_JGIVE_CB_NOT_INSTALLED');

				return false;
			}

			include_once JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';
		}
		else
		{
			if (!file_exists($mainframe->getCfg('absolute_path') . '/administrator/components/com_comprofiler/plugin.foundation.php'))
			{
				echo Text::_('COM_JGIVE_CB_NOT_INSTALLED');

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

		$myId = $_CB_framework->myId();
		$cbUser =& CBuser::getInstance($myId);

		if (!$cbUser)
		{
			$cbUser =& CBuser::getInstance(null);
		}

		$user =& $cbUser->getUserData();

		$cdata         = array();
		$cdata['campaign'] = new StdClass;

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
						if ($userinfo[$CB_field] != null && $jgive_field != null && $CB_field != null)
						{
							$cdata['campaign']->$jgive_field = $userinfo[$CB_field];
						}
					}
				}
			}
		}

		return $cdata;
	}

	/** Function profile import for EasySocial
	 *
	 * @param   String  $paymentform  Layout name
	 *
	 * @return  array|boolean mapped data object
	 */
	public function EasySocialProfileimport($paymentform = '')
	{
		$db     = Factory::getDbo();
		$params = ComponentHelper::getparams('com_jgive');

		if (defined('JPATH_ADMINISTRATOR'))
		{
			if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php'))
			{
				echo Text::_('COM_JGIVE_ES_NOT_INSTALLED');

				return false;
			}
		}
		else
		{
			if (!file_exists($mainframe->getCfg('absolute_path') . '/administrator/components/com_easysocial/includes/foundry.php'))
			{
				echo Text::_('COM_JGIVE_ES_NOT_INSTALLED');

				return false;
			}
		}

		$cdata             = array();
		$cdata['campaign'] = new stdclass;

		$mapping       = $params->get('easysocial_fieldmap');
		$mapping_field = explode("\n", $mapping);
		$socialtypes   = '';

		foreach ($mapping_field as $each_field)
		{
			$field = explode("=", $each_field);

			if (isset($field[1]))
			{
				$jgive_field   = trim($field[0]);
				$Esocial_field = trim($field[1]);

				// Remove campalsory star
				$socialtypes .= "'" . trim(str_replace('*', '', $Esocial_field)) . "',";
			}
		}

		$socialtypes = substr($socialtypes, 0, -1);
		$userid      = Factory::getUser()->id;

		// Create a new query object.
		if (!empty($socialtypes))
		{
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('data', 'datakey')));
			$query->from($db->quoteName('#__social_fields_data'));
			$query->where($db->quoteName('uid') . ' = ' . $userid);
			$query->where($db->quoteName('datakey') . ' IN (' . $socialtypes . ')');
			$query->where($db->quoteName('datakey') . ' IN (' . $socialtypes . ')');

			$db->setQuery($query);

			$results = $db->loadObjectList('datakey');

			foreach ($results as $k => $row)
			{
				// For payment_paymentform layout
				if ($paymentform)
				{
					switch ($k)
					{
						case 'first':
							$cdata['first_name'] = $row->data;
							break;

						case 'last':
							$cdata['last_name'] = $row->data;
							break;

						case 'address1':
							$cdata['address'] = $row->data;
							break;

						case 'address2':
							$cdata['address2'] = $row->data;
							break;

						case 'country':
							$cdata['country']    = $row->data;
							$country             = "`country` LIKE '$row->data'";
							$cdata['country_id'] = $this->getdata('id', '#__tj_country', $country);
							break;

						case 'state':
							$state          = "`region` LIKE '$row->data'";
							$cdata['state'] = $this->getdata('id', '#__tj_region', $state);
							break;

						case 'city':
							$city          = "`city` LIKE '$row->data'";
							$cdata['city'] = $this->getdata('id', '#__tj_city', $city);
							break;

						case 'zip':
							$cdata['zip'] = $row->data;
							break;
					}
				}

				// For create campaign layout
				else
				{
					switch ($k)
					{
						case 'first':
							$cdata['campaign']->first_name = $row->data;
							break;

						case 'last':
							$cdata['campaign']->last_name = $row->data;
							break;

						case 'address1':
							$cdata['campaign']->address = $row->data;
							break;

						case 'address2':
							$cdata['campaign']->address2 = $row->data;
							break;

						case 'country':
							$country                    = "`country` LIKE '$row->data'";
							$cdata['campaign']->country = $this->getdata('id', '#__tj_country', $country);
							break;

						case 'state':
							$state                    = "`region` LIKE '$row->data'";
							$cdata['campaign']->state = $this->getdata('id', '#__tj_region', $state);
							break;

						case 'city':
							$city                    = "`city` LIKE '$row->data'";
							$cdata['campaign']->city = $this->getdata('id', '#__tj_city', $city);
							break;

						case 'zip':
							$cdata['campaign']->zip = $row->data;
							break;
					}
				}
			}

			// For payment_paymentform layout
			if ($paymentform)
			{
				$cdata['paypal_email'] = Factory::getUser()->email;
			}
			else
			{
				$cdata['campaign']->paypal_email = Factory::getUser()->email;
			}
		}

		return $cdata;
	}

	/**
	 * Function EasyProfileimport
	 *
	 * @param   String  $paymentform  Paymentform provide a layout
	 *
	 * @return  Array
	 */
	public function EasyProfileimport($paymentform = '')
	{
		$cdata['campaign'] = new stdclass;
		$params            = ComponentHelper::getparams('com_jgive');

		if (!class_exists('JsnHelper'))
		{
			require_once JPATH_SITE . '/components/com_jsn/helpers/helper.php';
		}

		$user = JsnHelper::getUser();

		$mapping       = $params->get('ep_fieldmap');
		$mapping_field = explode("\n", $mapping);

		foreach ($mapping_field as $each_field)
		{
			$field       = explode("=", $each_field);
			$jgive_field = '';
			$ep_field    = '';

			if (isset($field[1]))
			{
				$jgive_field = trim($field[0]);
				$ep_field    = trim($field[1]);

				// Remove campalsory star
				$ep_field    = trim(str_replace(',*', '', $ep_field));
			}

			// For security mapping not allowed for user password
			if ($ep_field != 'password')
			{
				$userinfo = $user->getValue($ep_field);

				// For paymentform layout
				if ($paymentform)
				{
					if (!empty($userinfo))
					{
						$cdata[$jgive_field] = $userinfo;
					}
				}
				// For create campaign layout
				else
				{
					if (!empty($userinfo))
					{
						$cdata['campaign']->$jgive_field = $userinfo;
					}
				}
			}
		}

		return $cdata;
	}

	/**
	 * Function To get easy social data
	 *
	 * @param   Array  $compare_fields_array  Data
	 *
	 * @return  Array
	 */
	public function getUserInfo($compare_fields_array = null)
	{
		$db           = Factory::getDbo();
		$result_array = array();
		$user         = Factory::getUser();

		foreach ($compare_fields_array as $ind => $filedid)
		{
			$query = $db->getQuery(true);
			$query->select('fdata.data');
			$query->from($db->qn('#__social_fields_data'));
			$query->where($db->qn('fdata.uid') . ' = ' . $user->id . ' AND fdata.field_id=' . $filedid->value);

			$db->setQuery($query);

			$result = $db->loadResult();

			if ($db->getErrorNum())
			{
				JError::raiseError(500, $db->stderr());
			}

			if ($result)
			{
				$result_array[$filedid->text] = $result;
			}
			else
			{
				$result_array[$filedid->text] = '';
			}
		}

		$result_array['email'] = $user->email;

		return $result_array;
	}

	/**
	 * Function to get address
	 *
	 * @param   String  $data   Data
	 * @param   String  $table  Table name
	 * @param   String  $cond   Condition for query
	 *
	 * @return  row
	 */
	public function getdata($data, $table, $cond)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($data);
		$query->from($table);
		$query->where($cond);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Function profile import for jomsocial
	 *
	 * @param   String  $paymentform  Paymentform provide a layout
	 *
	 * @return  Array
	 */
	public function jomsocialProfileimport($paymentform = '')
	{
		$cdata['campaign'] = new stdclass;

		$params = ComponentHelper::getparams('com_jgive');
		$jspath = JPATH_ROOT . '/components/com_community';

		if (!file_exists($jspath))
		{
			return;
		}

		include_once $jspath . '/libraries/core.php';
		$userpro = CFactory::getUser();

		// Get jomsocial user profile info
		$user          = CFactory::getUser();
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
	 * @return  msg_field_required
	 */
	public function profileChecking()
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
			// $msg_field_required=JgiveIntegrationsHelper::jomsocialProfileChecking($params);
		}

		return $msg_field_required;
	}

	/**
	 * Function to check integration joomla user profile complete
	 *
	 * @param   Array  $params  Params
	 *
	 * @return  msg_field_required
	 */
	public function joomlaProfileChecking($params)
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
					if ($required_field != 'password') // for security mapping not allowed for user password
					{
						// If field not set is user array  then check  field in user profile array
						if ((array_key_exists($required_field, $user)) OR (array_key_exists($required_field, $user_profile['profile'])))
						{
							$userfield        = "";
							$userProfilefield = "";

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
	 * @param   Array  $params  Params
	 *
	 * @return  msg_field_required
	 */
	public function jomsocialProfileChecking($params)
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
					if ($required_field != 'password') // for security mapping not allowed for user password
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

	/**
	 * Method getJS_usergroup
	 *
	 * @param   Integer  $userid  User's Id
	 *
	 * @return  array
	 */
	public function getJS_usergroup($userid)
	{
		$params      = ComponentHelper::getParams('com_jgive');
		$integration = $params->get('integration');

		if (empty($userid))
		{
			$user        = Factory::getUser();
			$userid      = $user->id;
		}

		if ($integration != 'jomsocial')
		{
			return;
		}

		$jspath = JPATH_ROOT . '/components/com_community';

		if (!file_exists($jspath))
		{
			return;
		}

		if (!ComponentHelper::isEnabled('com_community', true))
		{
			return;
		}

		$jspath = JPATH_ROOT . '/components/com_community';
		include_once $jspath . '/libraries/core.php';

		$js_user  = CFactory::getUser($userid);
		$groupids = explode(',', $js_user->_groups);

		$grouptitles = array();
		$i           = 0;

		if ($groupids[0])
		{
			foreach ($groupids as $key => $id)
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->qn('name'))
					->from($db->qn('#__community_groups'))
					->where($db->qn('id') . ' = ' . $db->quote($id) . ' AND ' . $db->qn('ownerid') . ' = ' . $db->quote($userid));

				$db->setQuery($query);
				$group_title = $db->loadResult();

				if (empty($group_title))
				{
					unset($groupids[$key]);
					continue;
				}

				$grouptitles[$i]['id']    = $id;
				$grouptitles[$i]['title'] = $group_title;
				$i++;
			}
		}

		return $grouptitles;
	}
}
