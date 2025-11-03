<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

jimport('techjoomla.tjmoney.tjmoney');

/**
 * JGive utilities class for common methods.
 *
 * @since  2.5.0
 */
class JGiveUtilities
{
	/**
	 * Method to get city.
	 *
	 * @param   int  $cityId  city id
	 *
	 * @return object
	 *
	 * @since   2.5.0
	 */
	public function getCity($cityId)
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/tables');
		$cityTable = Table::getInstance('City', 'TjfieldsTable');
		$cityTable->load(array('id' => $cityId));

		$cityObj = new stdClass;
		$cityObj->id = $cityTable->id;
		$cityObj->city = $cityTable->city;
		$cityObj->country_id = $cityTable->country_id;

		return $cityObj;
	}

	/**
	 * Method to get country.
	 *
	 * @param   int  $countryId  country id
	 *
	 * @return object
	 *
	 * @since   2.5.0
	 */
	public function getCountry($countryId)
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/tables');
		$countryTable = Table::getInstance('Country', 'TjfieldsTable');
		$countryTable->load(array('id' => $countryId));

		$countryObj = new stdClass;
		$countryObj->id = $countryTable->id;
		$countryObj->country = $countryTable->country;
		$countryObj->country_code = $countryTable->country_code;
		$countryObj->country_3_code = $countryTable->country_3_code;

		return $countryObj;
	}

	/**
	 * Method to get state.
	 *
	 * @param   int  $regionId  region id
	 *
	 * @return object
	 *
	 * @since   2.5.0
	 */
	public function getRegion($regionId)
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/tables');
		$regionTable = Table::getInstance('Region', 'TjfieldsTable');
		$regionTable->load(array('id' => $regionId));

		$regionObj = new stdClass;
		$regionObj->id = $regionTable->id;
		$regionObj->country_id = $regionTable->country_id;
		$regionObj->region = $regionTable->region;

		return $regionObj;
	}

	/**
	 * Format the price  based on the JGive config with the help of the money library
	 *
	 * @param   float  $price  price
	 *
	 * @return  string    formatted like 3$ or $3
	 *
	 * @since  2.5.0
	 */
	public function getFormattedPrice($price)
	{
		$params = JGive::config();

		$currencyCode = $params->get('currency');
		$currencyCodeOrSymbol = $params->get('currency_symbol', 'code');
		$currencyDisplayFormat = $params->get('currency_display_format');

		$config = new Registry;
		$config->CurrencyDisplayFormat = $currencyDisplayFormat;
		$config->CurrencyCodeOrSymbol = $currencyCodeOrSymbol;
		$tjCurrency = new TjMoney($currencyCode);

		return $tjCurrency->displayFormattedValue($price, $config);
	}

	/**
	 * This function formatting the date  based on the JGive configuration
	 *
	 * @param   string  $utcDate  Date
	 *
	 * @return  string    formatted Date
	 *
	 * @since  2.5.0
	 */
	public function getFormattedDate($utcDate)
	{
		$result = '';

		if (!empty($utcDate))
		{
			$params = JGive::config();
			$result = HTMLHelper::_('date', $utcDate, $params->get('date_format', 'j  M  Y'));
		}

		return $result;
	}

	/**
	 * This function will return the Plugin title
	 *
	 * @param   string  $plgName  Plugin Name bycheck, byorder
	 *
	 * @return  string    Plugin Name like if plugin is bycheck will return PayBy Check
	 *
	 * @since  2.5.0
	 */
	public function getPaymentGatewayName($plgName)
	{
		$plugin       = PluginHelper::getPlugin('payment', $plgName);
		$pluginParams = new Registry($plugin->params);
		$pluginTitle  = $pluginParams->get('plugin_name', '', 'STRING') ? $pluginParams->get('plugin_name', '', 'STRING') : $plgName;

		return $pluginTitle;
	}

	/**
	 * Get rounded Amount
	 *
	 * @param   float  $amount  Amount
	 *
	 * @return  float  Rounded  Amount like $12.123 = $12.12
	 * 
	 * @since  2.5.0
	 */
	public function getRoundedAmount($amount)
	{
		$params = JGive::config();
		$currency   = $params->get('currency', '', 'STRING');
		$tjCurrency = new TjMoney($currency);

		// Get rounded output to display directly
		// $roundedAmount = $tjCurrency->getRoundedValue($amount);
		$roundedAmount = round($amount, $tjCurrency->getSubunit());

		return $roundedAmount;
	}

	/**
	 * Generate random number
	 *
	 * @param   INT  $length  length of random no
	 *
	 * @return  string  $random  random number
	 *
	 * @since 2.5.0
	 */
	public function _random($length = 5)
	{
		$salt   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len    = strlen($salt);
		$random = '';
		$stat   = @stat(__FILE__);

		if (empty($stat) || !is_array($stat))
		{
			$stat = array(php_uname());
		}

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i++)
		{
			$random .= $salt[mt_rand(0, $len - 1)];
		}

		return $random;
	}

	/**
	 * Get vendor id
	 *
	 * @param   INT     $userId  user id
	 *
	 * @param   STRING  $client  client
	 * 
	 * @return  integer vendor id
	 *
	 * @since 2.5.0
	 */
	public function getVendorId($userId, $client)
	{
		JLoader::import('components.com_tjvendors.includes.tjvendors', JPATH_SITE);
		$tjVendorClass = TJVendors::vendor();
		$vendorInfo = $tjVendorClass->loadByUserId($userId, $client);

		return $vendorInfo->vendor_id;
	}

	/**
	 * Methods to get countries
	 *
	 * @return  Array  country
	 *
	 * @since   2.5.0
	 */
	public function getCountries()
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$tjGeoHelperObj = new TjGeoHelper;
		$rows = $tjGeoHelperObj->getCountryList('com_jgive');

		return $rows;
	}

	/**
	 * This function return status text as per the status code
	 *
	 * @param   STRING  $statusCode  Order status code
	 *
	 * @return  ARRAY  status text and class
	 *
	 * @since   2.5.0
	 */
	public function getOrderStatusText($statusCode)
	{
		$statusArray = array();

		switch ($statusCode)
		{
			case COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED :
				$statusArray['statusText']  = Text::_('COM_JGIVE_CONFIRMED');
				$statusArray['statusClass'] = 'success';
			break;

			case COM_JGIVE_CONSTANT_ORDER_STATUS_REFUND :
				$statusArray['statusText']  = Text::_('COM_JGIVE_REFUND');
				$statusArray['statusClass'] = 'error';
			break;

			case COM_JGIVE_CONSTANT_ORDER_STATUS_PENDING :
				$statusArray['statusText']  = Text::_('COM_JGIVE_PENDING');
				$statusArray['statusClass'] = 'warning';
			break;

			case COM_JGIVE_CONSTANT_ORDER_STATUS_CANCELED :
				$statusArray['statusText']  = Text::_('COM_JGIVE_CANCELED');
				$statusArray['statusClass'] = 'error';
			break;

			case COM_JGIVE_CONSTANT_ORDER_STATUS_DECLINE :
				$statusArray['statusText']  = Text::_('COM_JGIVE_DENIED');
				$statusArray['statusClass'] = 'error';
			break;
		}

		return $statusArray;
	}

	/**
	 * Method to create vendor is loggeding user is not a vendor
	 *
	 * @return  boolean|integer
	 *
	 * @since   2.5.0
	 */
	public function createVendor()
	{
		$user  = Factory::getUser();
		$param = JGive::config();

		// Auto vendor creation is not allowed
		if ($param->get('silent_vendor') === 0)
		{
			return false;
		}

		$vendorId                          = $this->getVendorId($user->id, 'com_jgive');
		$vendorData                        = array();
		$vendorData['vendor_client']       = "com_jgive";
		$vendorData['user_id']             = $user->id;
		$vendorData['vendor_title']        = $user->name;
		$vendorData['state']               = "1";
		$vendorData['approved']            = "1";
		$paymentDetails                    = array();
		$paymentDetails['payment_gateway'] = '';
		$vendorData['paymentDetails']      = json_encode($paymentDetails);

		$table = Table::getInstance('vendor', 'TJVendorsTable', array());
		$table->load(array('user_id' => $user->id));

		// Check for vendor's id if not adds a vendor
		if (empty($vendorId ) && !empty($table->vendor_id))
		{
			$vendorData['vendor_id'] = $table->vendor_id;
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';

		$tjvendorsHelper = new TjvendorsHelper;
		$vendor_id       = $tjvendorsHelper->addVendor($vendorData);

		return $vendor_id;
	}

	/**
	 * Method return previous from and end date on basis of current from, end date and duration
	 *
	 * @param   array  $previousDatesArray  This array containing current from and end date 
	 * 
	 * @return  array  previousDatesArray
	 *
	 * @since   2.5.3
	 */
	public function getPreviousDates($previousDatesArray)
	{
		$durationFilter = $previousDatesArray['group_by'];

		switch (strtolower($durationFilter))
		{
			case 'month':
				$year = date("Y", strtotime("-1 year"));
				$previousDatesArray['from_date'] = $year . '-01-01 00:00:00';
				$previousDatesArray['end_date']  = $year . '-12-31 23:59:59';
				break;
			case 'week':
				$previousDatesArray['from_date'] = date("Y-m-01 00:00:00", strtotime("-1 month"));
				$previousDatesArray['end_date']  = date("Y-m-t 23:59:59", strtotime("-1 month"));
				break;
			case 'day':
				$date = new DateTime;
				$week = $date->format("W") - 1;
				$year = $date->format("Y");
				$date->setISODate($year, $week);
				$previousDatesArray['from_date'] = $date->format('Y-m-d 00:00:00');
				$date->modify('+6 days');
				$previousDatesArray['end_date'] = $date->format('Y-m-d 23:59:59');
				break;
		}

		return $previousDatesArray;
	}
}
