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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\File;

// Load language file for plugin
$lang = Factory::getLanguage();
$lang->load('plg_system_jgive_invitex_email', JPATH_ADMINISTRATOR);

include_once  JPATH_SITE . '/components/com_jgive/includes/jgive.php';

/**
 * Invitex System plugin  class.
 *
 * @package  JGive
 *
 * @since    1.7
 **/
class PlgSystemjgive_Invitex_Email extends CMSPlugin
{
	/**
	 * Trigger on prepare invitex email
	 *
	 * @param   string  $message_body     Email Message body
	 * @param   Object  $connection_data  Inviter & invitee info
	 *
	 * @return  array|boolean|string
	 */
	public function onPrepareInvitexEmail($message_body, $connection_data = null)
	{
		$helperPath = JPATH_SITE . '/components/com_invitex/helper.php';

		if (!class_exists('cominvitexHelper'))
		{
			JLoader::register('cominvitexHelper', $helperPath);
			JLoader::load('cominvitexHelper');
		}

		$cominvitexHelper = new cominvitexHelper;
		$invite_type      = $cominvitexHelper->geTypeId_By_InernalName('jgive_email');

		if (!empty($invite_type) && !empty($connection_data->invite_type) && ( $invite_type == $connection_data->invite_type))
		{
			$invite_type_tag = explode("|", $connection_data->invite_type_tag);

			$campId = null;

			if (isset($invite_type_tag[1]))
			{
				$invite_type_tag = str_replace("cid=", "", $invite_type_tag[1]);
				$campId          = str_replace("]", "", $invite_type_tag);
			}

			// To get campaign id
			if (!$campId)
			{
				return false;
			}

			// Add campaign Id to plugin
			$message_body = $this->_jGiveCampaignEmail($campId, $message_body, $connection_data);
		}

		return $message_body;
	}

	/**
	 * Trigger on prepare email content
	 *
	 * @param   INT    $campId           ToDo
	 * @param   Date   $message_body     Todo
	 * @param   Array  $connection_data  User params array
	 *
	 * @return  string $html
	 *
	 * @since   1.0.0
	 */
	public function _jGiveCampaignEmail($campId, $message_body, $connection_data)
	{
		$areturn = array();

		// If no userid/or no guest user return blank array for html and css
		JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/models');
		$JgiveModelCampaign = new JgiveModelCampaign;

		$cdata = $JgiveModelCampaign->getItem($campId);
		$html  = '';

		if (!empty($cdata))
		{
			// Call helper function to get plugin layout
			$data                  = new stdclass;
			$data->message_body    = $message_body;
			$data->cdata           = $cdata;
			$data->connection_data = $connection_data;

			// @param should be $layout,$vars=false,$plugin_params,$plugin='',$group='emailalerts'
			$html = $this->getLayout($this->_name, '', '', 'system', $data);
		}

		return $html;
	}

	/**
	 * Trigger on After invite added to queue
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onAfterinvitesqueued()
	{
		return;
	}

	/**
	 * Trigger on After invite sent
	 *
	 * @param   INT     $inviter_id    Inviter user id
	 * @param   String  $invitee_mail  Invitee mail
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onAfterinvitesent($inviter_id, $invitee_mail)
	{
		return;
	}

	/**
	 * Gets the parsed layout file
	 *
	 * @param   string  $layout         The name of  the layout file
	 * @param   string  $plugin_params  Plugin params
	 * @param   string  $plugin         The name of the plugin
	 * @param   string  $group          The plugin's group
	 * @param   object  $vars           Variables to assign to
	 *
	 * @return  string
	 */
	public function getLayout($layout, $plugin_params, $plugin = '', $group = '', $vars = false)
	{
		$plugin = $layout;
		ob_start();

		$app = Factory::getApplication();

		// Get the template and default paths for the layout
		$templatePath = JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/plugins/' . $group . '/' . $plugin . '/' . $layout . '.php';

		$defaultPath = JPATH_SITE . '/plugins/' . $group . '/' . $plugin . '/' . $plugin . '/tmpl/' . $layout . '.php';

		// If the site template has a layout override, use it

		$layout = $defaultPath;

		if (File::exists($templatePath))
		{
			$layout = $templatePath;
		}

		include $layout;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}
