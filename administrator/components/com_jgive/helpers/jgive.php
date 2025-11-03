<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Jgive helper class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveHelper
{
	/**
	 * Function addSubmenu.
	 *
	 * @param   string  $vName  The
	 *
	 * @return  void.
	 *
	 * @since	1.8
	 */
	public static function addSubmenu($vName = '')
	{
		if (JVERSION < '4.0.0')
		{
			$cp              = '';
			$campaigns       = '';
			$donations       = '';
			$reports         = '';
			$vendor          = '';
			$categories      = '';
			$notifications   = '';
			/* To do Uncomment this code when we add subcription support
			$subscriptions   = '';*/
			$tjreports       = '';
			$donors          = '';
			$individuals     = '';
			$organizations   = '';
			$receiptTemplate = '';
			$app             = Factory::getApplication();
			$queue           = $app->input->get('layout');
			$option          = $app->input->get('option');

			switch ($vName)
			{
				case 'cp':
					$cp = true;
					break;

				case 'campaigns':
					$campaigns = true;
					break;

				case 'donations':
					$donations = true;
					break;

				case 'tjreports':
					$tjreports = true;
					break;

				case 'reports':
					if ($option === 'com_tjreports')
					{
						$tjreports = true;
					}
					elseif ($option === 'com_tjvendors')
					{
						$vendor = true;
					}
					else
					{
						$reports = true;
					}
					break;
				case 'payouts':
					if ($option === 'com_tjvendors')
					{
						$vendor = true;
					}
					break;

				case 'vendors':
					$vendor = true;
					break;

				case 'categories':
					$categories = true;
					break;

				case 'donors':
					$donors = true;
					break;

				case 'notifications':
					$notifications = true;
					break;

				/* To do Uncomment this code when we add subcription support
				case 'subscriptions':
					$subscriptions = true;
					break;
				*/

				case 'individuals':
					$individuals = true;
					break;

				case 'organizations':
					$organizations = true;
					break;

				case 'receipttemplate':
					$receiptTemplate = true;
					break;
			}

			$document = Factory::getDocument();
			$document->addStyleDeclaration('.icon-48-helloworld ' . '{background-image: url(../media/com_helloworld/images/tux-48x48.png);}');
			$jgive_field_group_view = 'index.php?option=com_tjfields&view=groups&client=com_jgive.campaign';
			$field_link             = 'index.php?option=com_tjfields&view=fields&client=com_jgive.campaign';

			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_CP'), 'index.php?option=com_jgive&view=cp&layout=dashboard', $cp);
			\JHtmlSidebar::addEntry(
			Text::_('COM_JGIVE_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&view=categories&extension=com_jgive', $categories
			);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_CAMPAIGNS'), 'index.php?option=com_jgive&view=campaigns&layout=default', $campaigns);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_REPORTS'), 'index.php?option=com_jgive&view=reports&layout=default', $reports);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_VENDOR'), 'index.php?option=com_tjvendors&view=vendors&client=com_jgive', $vendor);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_DONATIONS'), 'index.php?option=com_jgive&view=donations', $donations);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_INDIVIDUALS'), 'index.php?option=com_jgive&view=individuals', $individuals);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_ORGANIZATIONS'), 'index.php?option=com_jgive&view=organizations', $organizations);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_RECEIPT_TEMPLATE'), 'index.php?option=com_jgive&view=receipttemplate', $receiptTemplate);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_CAMPAIGNS_DONORS'), 'index.php?option=com_jgive&view=donors&layout=default', $donors);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_EMAIL_TEMPLATE'), 'index.php?option=com_tjnotifications&extension=com_jgive', $notifications);
			/* To do Uncomment this code when we add subcription support
			\JHtmlSidebar::addEntry(
				Text::_('COM_JGIVE_NOTIFICATIONS_SUBSCRIPTIONS'), 'index.php?option=com_tjnotifications&view=subscriptions&extension=com_jgive', $subscriptions
			);*/
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_TJREPORTS'), 'index.php?option=com_tjreports&client=com_jgive&task=reports.defaultReport', $tjreports);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_CAMPAIGN_FIELD'), $field_link, $vName == 'fields');
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_JGIVE_CAMPAIGN_FIELDS_GROUP'), $jgive_field_group_view, $vName == 'groups');
			\JHtmlSidebar::addEntry(
			Text::_('COM_JGIVE_TITLE_COUNTRIES'),
			'index.php?option=com_tjfields&view=countries&client=com_jgive', $vName == 'countries'
			);
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_TITLE_REGIONS'), 'index.php?option=com_tjfields&view=regions&client=com_jgive', $vName == 'regions');
			\JHtmlSidebar::addEntry(Text::_('COM_JGIVE_TITLE_CITIES'), 'index.php?option=com_tjfields&view=cities&client=com_jgive', $vName == 'cities');

			if (ComponentHelper::isEnabled('com_fields'))
			{
				\JHtmlSidebar::addEntry(Text::_('JGLOBAL_FIELDS'), 'index.php?option=com_fields&context=com_jgive.individual', $vName == 'fields.fields');

				\JHtmlSidebar::addEntry(
					Text::_('JGLOBAL_FIELD_GROUPS'), 'index.php?option=com_fields&view=groups&context=com_jgive.individual', $vName == 'fields.groups'
				);
			}

			// Load bootsraped filter
			HTMLHelper::_('bootstrap.tooltip');

			if ($vName != 'donations' and $queue != 'paymentform')
			{
				HTMLHelper::_('behavior.multiselect');
				HTMLHelper::_('formbehavior.chosen', 'select');
			}
		}
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   2.3.5
	 */
	public static function getContexts()
	{
		Factory::getLanguage()->load('com_jgive', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_jgive.individual'   => Text::_('COM_JGIVE_INDIVIDUALS'),
			'com_jgive.organization' => Text::_('COM_JGIVE_ORGANIZATIONS')
		);

		return $contexts;
	}
}
