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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

// Component Helper
jimport('techjoomla.tjmail.mail');
jimport('techjoomla.tjmoney.tjmoney');

/**
 * Controller
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.7
 */
class JgiveFrontendHelper
{
	public $socialLibraryObject = null, $TjGeoHelper;

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;

		$this->socialLibraryObject = $this->getSocialLibraryObject();
	}

	/**
	 * Methods to get Social Library Object as per integration.
	 *
	 * @param   String  $integration_option  Integrated Extension Name
	 *
	 * @return  Object  Social Library Object
	 *
	 * @since   2.2.0
	 */
	public function getSocialLibraryObject($integration_option = '')
	{
		if (!$integration_option)
		{
			$params             = ComponentHelper::getParams('com_jgive');
			$integration_option = $params->get('integration', 'joomla');
		}

		if ($integration_option == 'cb')
		{
			jimport('techjoomla.jsocial.cb');
			$socialLibraryObject = new JSocialCB;
		}
		elseif ($integration_option == 'jomsocial')
		{
			jimport('techjoomla.jsocial.jomsocial');
			$socialLibraryObject = new JSocialJomsocial;
		}
		elseif ($integration_option == 'jomwall')
		{
			jimport('techjoomla.jsocial.jomwall');
			$socialLibraryObject = new JSocialJomwall;
		}
		elseif ($integration_option == 'easySocial')
		{
			jimport('techjoomla.jsocial.easysocial');
			$socialLibraryObject = new JSocialEasysocial;
		}
		else
		{
			jimport('techjoomla.jsocial.joomla');
			$socialLibraryObject = new JSocialJoomla;
		}

		return $socialLibraryObject;
	}

	/**
	 * Methods to get Menu item id.
	 *
	 * @param   String  $link          Menu link
	 * @param   Int     $skipIfNoMenu  Flag
	 *
	 * @return  Int  MenuId
	 *
	 * @since       1.7
	 */
	public function getItemId($link, $skipIfNoMenu = 0)
	{
		parse_str($link, $parsedLinked);

		$app  = Factory::getApplication();
		$menu = $app->getMenu();

		// If active menu if campaigns list view then return Itemid of current menu
		if ($parsedLinked['view'] == 'campaigns' && $parsedLinked['layout'] == 'all')
		{
			if ($activeMenu = $app->getMenu()->getActive())
			{
				$menuParams = $activeMenu->getParams();

				if (!empty($menuParams))
				{
					$layout = $menuParams->get('layout_to_load');

					if (!empty($layout))
					{
						return $activeMenu->id;
					}
				}
			}
		}

		// Get itemId for campaign detail view
		if ($parsedLinked['view'] == 'campaign' && isset($parsedLinked['layout']) && $parsedLinked['layout'] == 'default')
		{
			$campaignLink        = "index.php?option=com_jgive&view=campaign&layout=default";

			if (isset($parsedLinked['id']) &&  !empty($parsedLinked['id']))
			{
				$campaignLink .= "&id=" . $parsedLinked['id'];
			}

			$menuItems = $menu->getItems('link', $campaignLink);

			if (!empty($menuItems[0]->id))
			{
				// Return itemId of campaign menu
				return $menuItems[0]->id;
			}
			else
			{
				$menuItems = $menu->getItems('link', "index.php?option=com_jgive&view=campaigns&layout=all");

				if (!empty(!empty($menuItems[0]->id)))
				{
					// If no menu for campaign then return itemId of list view
					return $menuItems[0]->id;
				}
				else
				{
					// If no menu even for list view then return null
					return 0;
				}
			}
		}

		if ($parsedLinked['view'] == 'donation')
		{
			$menuItems = $menu->getItems('link', "index.php?option=com_jgive&view=donations");

			if (!empty($menuItems))
			{
				foreach ($menuItems as $menuItem)
				{
					if (!empty($menuItem->id))
					{
						$itemId = $menuItem->id;
						break;
					}
				}

				return $itemId;
			}
		}

		$itemId    = 0;
		$mainframe = Factory::getApplication();

		if ($mainframe->isClient('site'))
		{
			$menu  = $mainframe->getMenu();
			$items = $menu->getItems('link', $link);

			if (isset($items[0]))
			{
				$itemId = $items[0]->id;
			}
		}

		if (!$itemId)
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			$query = "SELECT id FROM #__menu
			WHERE link LIKE '%" . $link . "%'
			AND published =1
			LIMIT 1";

			$db->setQuery($query);
			$itemId = $db->loadResult();
		}

		if (!$itemId)
		{
			if ($skipIfNoMenu)
			{
				$itemId = 0;
			}
			else
			{
				$itemId = $mainframe->input->get('Itemid', 0, 'INT');
			}
		}

		return (int) $itemId;
	}

	/**
	 * Methods to get countries
	 *
	 * @return  countries
	 *
	 * @since       1.7
	 */
	public function getCountries()
	{
		$rows = $this->TjGeoHelper->getCountryList('com_jgive');

		return $rows;
	}

	/**
	 * Loads states for given country
	 *
	 * @param   INT  $country_id  Country Id
	 *
	 * @return  countries
	 *
	 * @since       1.7
	 */
	public function getState($country_id)
	{
		if (!$country_id)
		{
			return;
		}

		$rows = $this->TjGeoHelper->getRegionList($country_id, 'com_jgive');

		return $rows;
	}

	/**
	 * Loads cities for given cities
	 *
	 * @param   INT  $country_id  Country Id
	 *
	 * @return  countries
	 *
	 * @since       1.7
	 */
	public function getCity($country_id)
	{
		if (!$country_id)
		{
			return;
		}

		$rows = $this->TjGeoHelper->getCityList($country_id, 'com_jgive');

		return $rows;
	}

	/**
	 * Loads country name from country id, used when saving campaign
	 *
	 * @param   INT  $country_id  Country Id
	 *
	 * @return  countries
	 *
	 * @since       1.7
	 */
	public function getCountryNameFromId($country_id)
	{
		if (!$country_id)
		{
			return;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
				->select($db->qn('country'))
				->from($db->qn('#__tj_country'))
				->where($db->qn('id') . ' = ' . $db->quote($country_id) . ' AND ' . $db->qn('com_jgive') . ' = ' . '1');

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Loads country id from country name, used when showing campaign details
	 *
	 * @param   INT  $country_name  Country name
	 *
	 * @return  countries
	 *
	 * @since       1.7
	 */
	public function getCountryIdFromName($country_name)
	{
		if (!$country_name)
		{
			return;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__tj_country'))
			->where($db->qn('country') . ' = ' . $db->quote($country_name));

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Loads region name from region id, country id, used when saving campaign
	 *
	 * @param   INT  $region_id   Region Id
	 * @param   INT  $country_id  Country Id
	 *
	 * @return  countries
	 *
	 * @since       1.7
	 */
	public function getRegionNameFromId($region_id, $country_id)
	{
		if (!$region_id)
		{
			return;
		}

		if (!$country_id)
		{
			return;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('r.region'))
			->from($db->qn('#__tj_region', 'r'))
			->join('LEFT', $db->qn('#__tj_country', 'c') . ' ON (' . $db->qn('r.country_id') . ' = ' . $db->qn('c.id') . ')')
			->where($db->qn('c.id') . ' = ' . $db->quote($country_id) . ' AND ' . $db->qn('r.id') . ' = ' . $db->quote($region_id));

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Loads city name from city id, country id, used when saving campaign
	 *
	 * @param   INT  $city_id     City Id
	 * @param   INT  $country_id  Country Id
	 *
	 * @return  countries
	 *
	 * @since       1.7
	 */
	public function getCityNameFromId($city_id, $country_id)
	{
		if (!$city_id)
		{
			return;
		}

		if (!$country_id)
		{
			return;
		}

		$db    = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('c.city'))
			->from($db->qn('#__tj_city', 'c'))
			->join('LEFT', $db->qn('#__tj_country', 'con') . 'ON (' . $db->qn('c.country_id') . ' = ' . $db->qn('con.id') . ')')
			->where($db->qn('con.id') . ' = ' . $db->quote($country_id) . ' AND' . $db->qn('c.id') . ' = ' . $db->quote($city_id));

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * To sort the column which are not in table
	 *
	 * @param   array   $data    Array to sort
	 * @param   string  $column  Column to sory by
	 * @param   string  $order   Asc or desc
	 *
	 * @return  array  Sorted array
	 *
	 * @since       1.7
	 */
	public function multiDimensionalSort($data, $column, $order)
	{
		if (isset($data) && count($data))
		{
			foreach ($data as $key => $row)
			{
				if (is_array($row))
				{
					$orderby[$key] = $row[$column];
				}
				else
				{
					$orderby[$key] = $row->$column;
				}
			}

			if ($order == 'asc')
			{
				array_multisort($orderby, SORT_ASC, $data);
			}
			else
			{
				array_multisort($orderby, SORT_DESC, $data);
			}
		}

		return $data;
	}

	/**
	 * To sort the column which are not in table
	 *
	 * @param   string  $ad_url             Campaign link
	 * @param   int     $id                 Campaign id
	 * @param   string  $title              Campaign title
	 * @param   int     $show_comments      Flag to show or not to show commets
	 * @param   int     $show_like_buttons  Flag to show or not to show like buttons
	 * @param   string  $context            The context value example: com_jgive.report
	 *
	 * @return  array  Sorted array
	 *
	 * @since       1.7
	 */
	public function DisplayjlikeButton($ad_url, $id, $title, $show_comments, $show_like_buttons, $context = 'com_jgive.compaign')
	{
		$jlikeparams               = array();
		$jlikeparams['url']        = $ad_url;
		$jlikeparams['campaignid'] = $id;
		$jlikeparams['title']      = $title;
		PluginHelper::importPlugin('content', 'jlike_jgive');

		$grt_response = Factory::getApplication()->triggerEvent('onBeforeDisplaylike', array($context, $jlikeparams, $show_comments, $show_like_buttons));

		if (!empty($grt_response['0']))
		{
			return $grt_response['0'];
		}
		else
		{
			return '';
		}
	}

	/**
	 * Push to activity stream
	 *
	 * @param   array  $contentdata  Data required for stream
	 *
	 * @return  array  Sorted array
	 *
	 * @since       1.7
	 *//*
	public function pushtoactivitystream($contentdata)
	{
		$a_id           = $contentdata['user_id'];
		$i_opt = $contentdata['integration_option'];
		$a_acc         = 0;
		$a_des    = $contentdata['act_description'];
		$a_type           = '';
		$act_subtype        = '';
		$act_link           = '';
		$act_title          = '';
		$act_access         = 0;

		$activityintegrationstream = new activityintegrationstream;

		$result                    = $activityintegrationstream->pushActivity($a_id, $a_type, $act_subtype, $a_des, $act_link, $act_title, $a_acc, $i_opt);

		if (!$result)
		{
			return false;
		}

		return true;
	}*/

	/**
	 * Push to activity stream
	 *
	 * @param   Float   $price  Amount
	 * @param   String  $curr   Currency
	 *
	 * @return  STRING   formatted price-currency
	 *
	 * @since       1.7
	 */
	public function getFormattedPrice($price, $curr = null)
	{
		$params                        = ComponentHelper::getParams('com_jgive');
		$currency                      = $params->get('currency');
		$currencySymbolOrCode          = $params->get('currency_symbol');
		$currencyDisplayFormat         = $params->get('currency_display_format');

		$config                        = new stdClass();
		$config->CurrencyDisplayFormat = $currencyDisplayFormat;
		$config->CurrencyCodeOrSymbol  = $currencySymbolOrCode;

		$tjCurrency = new TjMoney($currency);

		// Get formatted output to display directly
		$formattedAmount = $tjCurrency->displayFormattedValue($price, $config);
		$html            = $formattedAmount;

		/*To do check in view and if needed the add <span> tag in that view*/
		/*$html            = "<span>" . $formattedAmount . "</span>";*/

		return $html;
	}

	/**
	 * Get rounded Amount (Campaign Goal Amount, Minimum Amount, Donation Amount)
	 *
	 * @param   float  $amount  Campaign Goal Amount, Minimum Amount, Donation Amount
	 *
	 * @return  float  Rounded Amount like $12.123 = $12.12
	 */
	public function getRoundedAmount($amount)
	{
		$params     = ComponentHelper::getParams('com_jgive');
		$currency   = $params->get('currency', '', 'STRING');
		$tjCurrency = new TjMoney($currency);

		// Get rounded output to display directly
		// $roundedAmount = $tjCurrency->getRoundedValue($amount);
		$roundedAmount = round($amount, $tjCurrency->getSubunit());

		return $roundedAmount;
	}

	/**
	 * Get jomsocial toobar html
	 *
	 * @return  Js Toolbar
	 *
	 * @since       1.7
	 */
	public function jomsocialToolbarHtml()
	{
		$params = ComponentHelper::getParams('com_jgive');
		$html   = '';

		if (($params->get('integration') == 'jomsocial') && $params->get('jomsocial_toolbar'))
		{
			// Added for JS toolbar inclusion.
			if (Folder::exists(JPATH_SITE . '/components/com_community'))
			{
				require_once JPATH_ROOT . '/components/com_community/libraries/toolbar.php';
				$toolbar = CFactory::getToolbar();
				$tool    = CToolbarLibrary::getInstance();

				$html .= '<div id="community-wrap">';
				$html .= $tool->getHTML();
				$html .= '</div>';
			}
		}

		return $html;
	}

	/** Checks for view override
	 *
	 * @param   String  $viewname       Name of view
	 * @param   String  $layout         Layout name eg order
	 * @param   String  $searchTmpPath  It may be admin or site. it is side(admin/site) where to search override view
	 * @param   String  $useViewpath    It may be admin or site. it is side(admin/site) which VIEW shuld be use IF OVERRIDE IS NOT FOUND
	 *
	 * @return Return path
	 *
	 * @since  1.7
	 */
	public function getViewpath($viewname, $layout = "", $searchTmpPath = 'SITE', $useViewpath = 'SITE')
	{
		$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$app           = Factory::getApplication();

		if (!empty($layout))
		{
			$layoutname = $layout . '.php';
		}
		else
		{
			$layoutname = "default.php";
		}

		$override = $searchTmpPath . '/templates/' . $app->getTemplate() . '/html/com_jgive/' . $viewname . '/' . $layoutname;

		if (File::exists($override))
		{
			return $view = $override;
		}
		else
		{
			return $view = $useViewpath . '/components/com_jgive/views/' . $viewname . '/tmpl/' . $layoutname;
		}
	}

	/**
	 * Declare language constants to use in .js file
	 *
	 * @params  void
	 *
	 * @return  void
	 *
	 * @since   1.7
	 */
	public static function getLanguageConstant()
	{
		Text::script('COM_JGIVE_MINIMUM_DONATION_AMOUNT');
		Text::script('COM_JGIVE_ENTER_DONATION_AMOUNT');
		Text::script('COM_JGIVE_AMOUNT_SHOULD_BE');
		Text::script('COM_JGIVE_DONATION_AMOUNT_SHOULD_BE');
		Text::script('COM_JGIVE_INVESTMENT_AMOUNT_SHOULD_BE');
		Text::script('COM_JGIVE_FILL_MANDATORY_FIELDS_DATA');
		Text::script('COM_JGIVE_ORDER_PLACING_ERROR');
		Text::script('COM_JGIVE_PLEASE_SELECT_DEFAULT_VIDEO');
		Text::script('COM_JGIVE_DELETE_VIDEO_CONFIRM_MSG');
		Text::script('COM_JGIVE_EMAIL_VALIDATION');
		Text::script('COM_JGIVE_VIDEO_DELETED');
		Text::script('COM_JGIVE_VIDEO_SET_DEFAULT');
		Text::script('COM_JGIVE_POST_TEXT_ACTIVITY_REMAINING_TEXT_LIMIT');
		Text::script('COM_JGIVE_FIRST_NAME_VALIDATION');
		Text::script('COM_JGIVE_LAST_NAME_VALIDATION');
		Text::script('COM_JGIVE_EMAIL_VALIDATION');
		Text::script('COM_JGIVE_ZIP_VALIDATION');
		Text::script('COM_JGIVE_PHONE_NUMBER_VALIDATION');
		Text::script('COM_JGIVE_DASHBOARD_CREATE_ACTIVITIES_PROCESSING');
		Text::script('COM_JGIVE_DASHBOARD_CREATE_ACTIVITIES_DONE');
		Text::script('COM_JGIVE_DASHBOARD_CREATE_ACTIVITIES_ERROR');
		Text::script('COM_JGIVE_GALLERY_IMAGE_TEXT');
		Text::script('COM_JGIVE_GALLERY_VIDEO_TEXT');
		Text::script('COM_JGIVE_CAMPAIGN_MAIN_IMAGE_TYPE_VALIDATION');
		Text::script('COM_JGIVE_CAMPAIGN_MAIN_IMAGE_DIMES_INFO');
		Text::script('COM_JGIVE_TOOLBAR_DATABASE_FIX_FIX');
		Text::script('COM_JGIVE_TOOLBAR_DATABASE_FIX_MIGRATEMEDIA');
		Text::script('COM_JGIVE_TOOLBAR_DATABASE_FIX_MIGRATEVENDORDATA');
		Text::script('COM_JGIVE_TOOLBAR_DATABASE_FIX_MIGRATEACTIVITIES');
		Text::script('COM_JGIVE_TOOLBAR_DATABASE_FIX_SUCCESS_MSG');
		Text::script('COM_JGIVE_TOOLBAR_DATABASE_FIX');
		Text::script('COM_JGIVE_MEDIA_UPLOAD_ERROR');
		Text::script('COM_TJMEDIA_VALIDATE_URL');
		Text::script('COM_TJMEDIA_MEDIA_SET_TO_DEFAULT');
		Text::script('COM_TJMEDIA_MEDIA_SET_TO_DEFAULT_ERROR');
		Text::script('COM_JGIVE_CONFIRM_DELETE_MEDIA');
		Text::script('COM_JGIVE_CREATE_CAMPAIGN_VALIDATION_FAIL_MSG');
		Text::script('COM_JGIVE_CREATE_CAMPAIGN_IMAGEVALIDATION_FAIL_MSG');
		Text::script('COM_JGIVE_CREATE_CAMPAIGN_MINDONATION_FAIL_MSG');
		Text::script('COM_JGIVE_CREATE_CAMPAIGN_DATEVALIDATION_FAIL_MSG');
		Text::script('COM_JGIVE_CREATE_CAMPAIGN_GIVEAWAY_FAIL_MSG');
		Text::script('COM_JGIVE_MEDIA_INVALID_FILE_TYPE');
		Text::script('COM_JGIVE_GIVE_NOT_AVIL_FOR_THIS_AMOUNT');
		Text::script('COM_JGIVE_STATE');
		Text::script('COM_JGIVE_CITY');
		Text::script('COM_JGIVE_CHECKOUT_ERROR_LOGIN');
		Text::script('COM_JGIVE_START_DATE_PAST_WARNING');
		Text::script('COM_JGIVE_CREATE_CAMPAIGN_NEGATIVE_NUMBER_ERROR_MSG');
		Text::script('COM_JGIVE_CHECK_TERMS');
		Text::script('COM_JGIVE_IMAGE_UPLOAD_LIMIT_EXCEED');
		Text::script('COM_JGIVE_VIDEO_UPLOAD_LIMIT_EXCEED');
		Text::script('COM_JGIVE_GIVEBACK_DESC');
		Text::script('COM_JGIVE_LOADER_LOADING');
		Text::script('COM_JGIVE_CONFIRM_DELETE_REPORT');
		Text::script('COM_JGIVE_VALIDATE_ROUNDED_AMOUNT');
		Text::script('COM_JGIVE_INVALID_FIELD');
		Text::script('COM_JGIVE_FILE_SIZE_ERROR');
		Text::script('COM_JGIVE_FILE_TYPE_ERROR');
		Text::script('COM_JGIVE_FILE_ERROR');
		Text::script('COM_JGIVE_FILE_UPLOAD_ABORT');
		Text::script('COM_JGIVE_FILE_UPLOAD_CONFIRM_ABORT');
		Text::script('COM_JGIVE_REACHED_MAX_REPORT_ATTACHMENT_LIMIT');
		Text::script('COM_JGIVE_CONFIRM_DELETE_REPORT_ATTACHMENT');
		Text::script('COM_JGIVE_EMAIL_EXISTS');
		Text::script('COM_JGIVE_CONTROLLER_FALSE_RESPONSE');
		Text::script('COM_JGIVE_MATCHING_ORGANIZATIONS');
		Text::script('COM_JGIVE_MATCHING_INDIVIDUALS');
		Text::script('COM_JGIVE_MATCHING_USERS');
		Text::script('COM_JGIVE_SEARCH_NULL');
		Text::script('COM_JGIVE_INVALID_EMAIL');
		Text::script('COM_JGIVE_BLANK_EMAIL_ALERT');
		Text::script('COM_JGIVE_DONATION_DATE_FORMAT');
		Text::script('COM_JGIVE_DATE_FORMAT');
		Text::script('COM_JGIVE_TRANSACTION_DATE_TOOLTIP');
		Text::script('COM_JGIVE_CAMPAIGNFORM_END_DATE_DAYS_LIMIT_INVALID_INPUT');
		Text::script('COM_JGIVE_NO_PAYMENT_GATEWAY');
		Text::script('COM_JGIVE_EDIT');
		Text::script('COM_JGIVE_GIVEBACKS_TOOLTIP');
		Text::script('TJTOOLBAR_NO_SELECT_MSG');
		Text::script('COM_JGIVE_ARE_YOU_SURE_YOU_TO_DELETE_THE_CONTACTS');
		Text::script('COM_JGIVE_CONFIRM_MSG_FOR_SEND_MAIL_WITHOUT_SUB_AND_TEXT');
		Text::script('COM_JGIVE_AMOUNT_SUGGESTION_VALIDATION_FAIL_MESSAGE');
		Text::script('COM_JGIVE_AMOUNT_SUGGESTION_VALIDATION_FAILED_MESSAGE_MIN_AMOUNT');
		Text::script('COM_JGIVE_AMOUNT_SUGGESTION_VALIDATION_FAILED_MESSAGE_MAX_AMOUNT');
		Text::script('COM_JGIVE_PAN_VALIDATION');
		Text::script('COM_JGIVE_DONATION_VALIDATION_WRONG_PAYMENT_METHOD');
		Text::script('PLG_PAN_VERIFICATION_SETU_VERIFICATION_SUCCESS');
		Text::script('PLG_PAN_VERIFICATION_SETU_VERIFICATION_FAIL');
		Text::script('COM_JGIVE_PAN_VERIFICATION_VALIDATION_ERROR');
		Text::script('COM_JGIVE_PAN_VALIDATION');
		Text::script('COM_JGIVE_DONATION_VALIDATION_WRONG_PAYMENT_METHOD');
	}

	/**
	 * Pass js file which are needed to load on selected view.
	 *
	 * @param   array  &$jsFilesArray                  Js file's array.
	 * @param   array  &$firstThingsScriptDeclaration  Javascript to be declared first.
	 *
	 * @return  filled up file array
	 */
	public function getJGiveJsFiles(&$jsFilesArray, &$firstThingsScriptDeclaration)
	{
		$input  = Factory::getApplication()->input;
		$option = $input->get('option', '');
		$view   = $input->get('view', '');
		$layout = $input->get('layout', 'default');
		$params = ComponentHelper::getParams('com_jgive');

		if ($option === 'com_jgive')
		{
			// For frontend
			if (Factory::getApplication()->isClient('site'))
			{
				switch ($layout)
				{
					case 'all':
						$jsFilesArray[] = 'media/com_jgive/vendors/js/masonry.pkgd.min.js';
					break;

					case 'default':

						$jsFilesArray[] = 'media/com_jgive/vendors/js/jquery.magnific-popup.min.js';
					break;
				}
			}
		}

		return $jsFilesArray;
	}

	/**
	 * getLineChartFormattedData
	 *
	 * @param   ARRAY  $data  data
	 *
	 * @return  Chart array
	 */
	public function getLineChartFormattedData($data)
	{
		$app        = Factory::getApplication();
		$backdate   = $app->getUserStateFromRequest('from', 'from', '', 'string');
		$todate     = $app->getUserStateFromRequest('to', 'to', '', 'string');
		$backdate   = !empty($backdate) ? $backdate : (date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days')));
		$todate     = !empty($todate) ? $todate : date('Y-m-d');

		$donationData = "[";
		$ordersData = "[";
		$firstdate  = $backdate;

		// Will be
		$keydate    = "";

		foreach ($data as $key => $donation)
		{
			$keydate = date('Y-m-d', strtotime($key));

			if ($firstdate < $keydate)
			{
				while ($firstdate < $keydate)
				{
					$donationData .= " { period:'" . $firstdate . "', amount:0 },
					";
					$ordersData .= " { period:'" . $firstdate . "', orders:0 },
					";
					$firstdate = $this->add_date($firstdate, 1);
				}
			}

			$donationData .= " { period:'" . $donation->cdate . "', amount:" . $donation->donation_amount . "},
		";
			$ordersData .= " { period:'" . $donation->cdate . "', orders:" . $donation->orders_count . "},
		";
			$firstdate = $keydate;
		}

		// Vm: remaing date to last date
		while ($keydate < $todate)
		{
			$keydate = $this->add_date($keydate, 1);
			$donationData .= " { period:'" . $keydate . "', amount:0 },
		";
			$ordersData .= " { period:'" . $keydate . "', orders:0 },
		";
		}

		$donationData .= '
		]';
		$ordersData .= '
		]';
		$returnArray    = array();
		$returnArray[0] = $donationData;
		$returnArray[1] = $ordersData;

		return $returnArray;
	}

	/**
	 * add_date
	 *
	 * @param   ARRAY  $givendate  givendate
	 * @param   INT    $day        day
	 * @param   INT    $mth        month
	 * @param   INT    $yr         year
	 *
	 * @return  string  html
	 */
	public function add_date($givendate, $day = 0, $mth = 0, $yr = 0)
	{
		$cd      = strtotime($givendate);

		$newdate = date('Y-m-d H:i:s',
					mktime(
						date('H', $cd),
						date('i', $cd), date('s', $cd), date('m', $cd) + $mth, date('d', $cd) + $day, date('Y', $cd) + $yr
						)
					);

		// Convert to y-m-d format
		$newdate = date('Y-m-d H:i:s', strtotime($newdate));

		return $newdate;
	}

	/**
	 * Get sites/administrator default template
	 *
	 * @param   mixed  $client  0 for site and 1 for admin template
	 *
	 * @return  json
	 *
	 * @since   1.5
	 */
	public static function getSiteDefaultTemplate($client = 0)
	{
		try
		{
			$db    = Factory::getDbo();

			// Get current status for Unset previous template from being default
			// For front end => client_id=0
			$query = $db->getQuery(true)
						->select('template')
						->from($db->quoteName('#__template_styles'))
						->where('client_id=' . $client)
						->where('home=1');
			$db->setQuery($query);

			return $db->loadResult();
		}
		catch (Exception $e)
		{
			return '';
		}
	}

	/**
	 * Get campaign details page URL
	 *
	 * @param   INT      $id        campaign id
	 * @param   BOOLEAN  $relative  true for relative and false for absolute
	 * @param   BOOLEAN  $sef       true for sef anf false for non sef
	 *
	 * @return  STRING|BOOLEAN  campaign details page url else false
	 *
	 * @since   1.5
	 */
	public function getCampaignUrl($id, $relative = false, $sef = true)
	{
		if (!empty($id))
		{
			$itemId = $this->getItemId('index.php?option=com_jgive&view=campaign&layout=default');

			if ($relative)
			{
				$campaignUrl = 'index.php?option=com_jgive&view=campaign&layout=default&id=' . $id . '&Itemid=' . $itemId;
			}
			else
			{
				$campaignUrl = Uri::root() . 'index.php?option=com_jgive&view=campaign&layout=default&id=' . $id . '&Itemid=' . $itemId;
			}

			if ($sef)
			{
				$app = Factory::getApplication();

				// Get sef URL for campaign
				$campaignUrl = Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $id . '&Itemid=' . $itemId);

				// If function is called from backend and we want a SEF url
				if ($app->isClient("administrator"))
				{
					$appInstance = Factory::getApplication('site');
					$router = $appInstance->getRouter();
					$uri = $router->build($campaignUrl);
					$campaignUrl = $uri->toString();
					$campaignUrl = substr($campaignUrl, strlen(Uri::base(true)) + 1);
				}
				elseif ($relative)
				{
					$campaignUrl = substr(
						$campaignUrl, strlen(Uri::base(true)) + 1
					);
				}
			}

			return $campaignUrl;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get reports details page URL
	 *
	 * @param   INT      $id        campaign id
	 * @param   BOOLEAN  $relative  true for relative and false for absolute
	 * @param   BOOLEAN  $sef       true for sef anf false for non sef
	 *
	 * @return  STRING|BOOLEAN  campaign details page url else false
	 *
	 * @since   2.2.0
	 */
	public function getReportUrl($id, $relative = false, $sef = true)
	{
		if (!empty($id))
		{
			$itemId = $this->getItemId('index.php?option=com_jgive&view=report');

			if ($relative)
			{
				$reportUrl = 'index.php?option=com_jgive&view=report&layout=default&id=' . $id . '&Itemid=' . $itemId;
			}
			else
			{
				$reportUrl = Uri::root() . 'index.php?option=com_jgive&view=report&layout=default&id=' . $id . '&Itemid=' . $itemId;
			}

			if ($sef)
			{
				if ($relative)
				{
					$reportUrl = substr(
					Route::_('index.php?option=com_jgive&view=report&layout=default&id=' . $id . '&Itemid=' . $itemId), strlen(Uri::base(true)) + 1
					);
				}
				else
				{
					$reportUrl = Route::_('index.php?option=com_jgive&view=report&layout=default&id=' . $id . '&Itemid=' . $itemId);
				}
			}

			return $reportUrl;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to get sort url for columns
	 *
	 * @param   STRING  $currentUrl  page Url
	 * @param   STRING  $column      sort column
	 * @param   STRING  $direction   sort direction
	 *
	 * @return  STRING  Url for sorting table data
	 *
	 * @since   2.1
	 */
	public function getTableSortUrl($currentUrl, $column = 'title', $direction = 'asc')
	{
		$urlParams = array();
		$sortUrl = $currentUrl;

		// Get existing params from the url
		if (strpos($currentUrl, '?') !== false)
		{
			$parsedLink = explode('?', $currentUrl);
			$sortUrl = $parsedLink['0'];

			if (!empty($parsedLink[1]))
			{
				$parsedLink = explode('&', $parsedLink[1]);

				foreach ($parsedLink as $param)
				{
					$tmp = explode('=', $param);

					if (isset($tmp[1]) && $tmp[1] != '')
					{
						$urlParams[$tmp[0]] = $tmp[1];
					}
				}
			}
		}

		// Set sort column and direction
		$urlParams['filter_order_Dir'] = ($direction == 'desc')?'asc':'desc';
		$urlParams['filter_order'] = $column;

		$count = 0;

		//  Construct url from params
		foreach ($urlParams as $filter => $value)
		{
			if ($count == 0)
			{
				$sortUrl .= "?" . $filter . '=' . $value;
			}
			else
			{
				$sortUrl .= "&" . $filter . '=' . $value;
			}

			$count++;
		}

		return $sortUrl;
	}
}
