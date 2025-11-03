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
defined('_JEXEC') or die('FFF');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

JLoader::import('campaigns', JPATH_ADMINISTRATOR . '/components/com_jgive/models');
JLoader::import('tjvendors', JPATH_SITE . '/components/com_tjvendors/helpers');

/**
 * Cp form model class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveModelCp extends BaseDatabaseModel
{
	/**
	 * Constructor.
	 *
	 * @see     JController
	 * @since   1.8
	 */
	public function __construct()
	{
		// Get download id
		$params           = ComponentHelper::getParams('com_jgive');
		$this->downloadid = $params->get('downloadid');

		// JGive
		$this->extensionsDetails                   = new stdClass;
		$this->extensionsDetails->extension        = 'com_jgive';
		$this->extensionsDetails->extensionElement = 'pkg_jgive';
		$this->extensionsDetails->extensionType    = 'package';
		$this->extensionsDetails->updateStreamName = 'JGive';
		$this->extensionsDetails->updateStreamType = 'extension';
		$this->extensionsDetails->updateStreamUrl  = 'https://techjoomla.com/updates/stream/jgive.xml?format=xml';
		$this->extensionsDetails->downloadidParam  = 'downloadid';

		// Call the parents constructor
		parent::__construct();
	}

	/**
	 * Method for onRefreshUpdateSite
	 *
	 * @return  updated version of jgive
	 *
	 * @since   1.8
	 */
	public function onRefreshUpdateSite()
	{
		// Trigger plugin
		PluginHelper::importPlugin('system', 'tjupdates');
		Factory::getApplication()->triggerEvent('onRefreshUpdateSite', array($this->extensionsDetails));
	}

	/**
	 * Method for get Latest version
	 *
	 * @return  updated version of jgive
	 *
	 * @since   1.8
	 */
	public function onGetLatestVersion()
	{
		// Trigger plugin
		PluginHelper::importPlugin('system', 'tjupdates');
		$latestVersion = Factory::getApplication()->triggerEvent('onGetLatestVersion', array($this->extensionsDetails));

		return (isset($latestVersion[0]) ? $latestVersion[0] : false);
	}

	/**
	 * Method to get a array record.
	 *
	 * @return  array  $result.
	 *
	 * @since	1.8
	 */
	public function getDashboardData()
	{
		$db = Factory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select('COUNT(*) as total_campaigns');
		$query->select('SUM(goal_amount) as total_goal_amount');
		$query->from($db->qn('#__jg_campaigns'));

		$db->setQuery($query);
		$campaignInfo = $db->loadAssoc();

		$query = $db->getQuery(true);
		$query->select('SUM(amount) as total_funded_amount');
		$query->select('SUM(fee) as commision_amount');
		$query->from($db->qn('#__jg_orders'));
		$query->where($db->qn('#__jg_orders.status') . ' = ' . $db->quote('C'));

		$db->setQuery($query);
		$orderInfo = $db->loadAssoc();

		$result = array();
		$result['campaignInfo'] = $campaignInfo;
		$result['orderInfo'] = $orderInfo;

		return $result;
	}

	/**
	 * Function to get a array All months.
	 *
	 * @return  array  $months.
	 *
	 * @since	1.8
	 */
	public function getAllMonths()
	{
		$date2      = date('Y-m-d');

		// Get one year back date
		$date1 = date('Y-m-d', strtotime(date("Y-m-d", time()) . " - 365 day"));

		// Convert dates to UNIX timestamp
		$time1 = strtotime($date1);
		$time2 = strtotime($date2);
		$tmp = date('mY', $time2);
		$year = date('Y', $time1);

		while ($time1 < $time2)
		{
			$month31 = array(1,3,5,7,8,10,12);
			$month30 = array(4,6,9,11);

			$month = date('m', $time1);

			if (array_search($month, $month31))
			{
				$time1 = strtotime(date('Y-m-d', $time1) . ' +31 days');
			}
			elseif (array_search($month, $month30))
			{
				$time1 = strtotime(date('Y-m-d', $time1) . ' +30 days');
			}
			else
			{
				if (((0 == $year % 4) && (0 != $year % 100)) || (0 == $year % 400))
				{
					$time1 = strtotime(date('Y-m-d', $time1) . ' +29 days');
				}
				else
				{
					$time1 = strtotime(date('Y-m-d', $time1) . ' +28 days');
				}
			}

			if (date('mY', $time1) != $tmp && ($time1 < $time2))
			{
				$months[] = array(
					"month" => date('F', $time1),
					"year" => date('Y', $time1)
				);
			}
		}

		$months[] = array("month" => date('F', $time2),"year" => date('Y', $time2));

		return $months;
	}

	/**
	 * Function for to  get a All months donation.
	 *
	 * @return  Object List $donation.
	 *
	 * @since	1.8
	 */
	public function getMonthDonation()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$curdate    = date('Y-m-d');
		$back_year  = date('Y') - 1;
		$back_month = date('m') + 1;
		$backdate   = $back_year . '-' . $back_month . '-' . '01';

		$query->select('FORMAT( SUM(amount) , 2) AS amount');
		$query->select('MONTH(cdate) AS MONTHSNAME');
		$query->select('YEAR(cdate) AS YEARNAME');
		$query->from($db->qn('#__jg_orders', 'o'));

		$query->where(
						'DATE(' . $db->qn('o.cdate') . ')' . ' Between ' . $db->quote($backdate) . ' AND ' .
						$db->quote($curdate) . ' AND ' . $db->qn('o.status') . ' = ' . $db->quote('C')
						);

		$query->group($db->quote('YEARNAME'));
		$query->group('MONTHSNAME');

		$query->order($db->quote('YEAR( o.cdate )') . 'ASC');
		$query->order($db->quote('MONTH( o.cdate )') . 'ASC');

		$db->setQuery($query);
		$donation = $db->loadObjectList();

		return $donation;
	}

	/**
	 * Function for Recent periodic donation
	 *
	 * @return  Object List of Recent periodic donation
	 *
	 * @since   1.8
	 */
	public function getRecentDonationDetails()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('a.id', 'a.order_id', 'a.original_amount', 'b.title')))
			->from($db->quoteName('#__jg_orders', 'a'))
			->join('INNER', $db->quoteName('#__jg_campaigns', 'b') . ' ON (' . $db->quoteName('a.campaign_id') . ' = ' . $db->   quoteName('b.id') . ')')
			->where($db->quoteName('a.status') . ' = ' . "'C'")
			->order($db->quoteName('a.mdate') . ' DESC');

		$query->setLimit(2);

		$db->setQuery($query);

		$recentDonations = $db->loadObjectList();

		return $recentDonations;
	}

	/**
	 * Function for Periodic donation count
	 *
	 * @return  record of periodic donation count
	 *
	 * @since   1.8
	 */
	public function getPeriodicDonationsCount()
	{
		$db      = Factory::getDbo();
		$session = Factory::getSession();

		$query = $db->getQuery(true);

		$jgive_graph_from_date = $session->get('jgive_graph_from_date');
		$jgive_socialads_end_date  = $session->get('jgive_socialads_end_date');
		$where               = '';
		$groupby             = '';

		if ($jgive_graph_from_date)
		{
			$where = ' AND DATE(' . $db->qn('#__jg_orders.mdate') . ') BETWEEN DATE(' .
			$db->quote($jgive_graph_from_date) . ' ) AND DATE (' . $db->quote($jgive_socialads_end_date) . ')';
		}
		else
		{
			$jgive_graph_from_date = date('Y-m-d');
			$backdate            = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));

			$where = ' AND DATE(' . $db->qn('#__jg_orders.mdate') . ') BETWEEN DATE(' .
			$db->quote($backdate) . ' ) AND DATE (' . $db->quote($jgive_graph_from_date) . ')';

			$groupby             = "";
		}

		$query->select('FORMAT(SUM(amount),2)');
		$query->from('#__jg_orders');
		$query->where($db->qn('#__jg_orders.status') . ' = ' . $db->quote('C') . $where);

		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();

		return $result;
	}

	/**
	 * Function for pie chart
	 *
	 * @return  array  Get data for pie chart
	 *
	 * @since   1.8
	 */
	public function statsforpie()
	{
		$db                  = Factory::getDbo();
		$session             = Factory::getSession();

		$query = $db->getQuery(true);

		$jgive_graph_from_date = $session->get('jgive_graph_from_date');
		$jgive_socialads_end_date  = $session->get('jgive_socialads_end_date');
		$where               = '';

		if ($jgive_graph_from_date)
		{
			// For graph
			$where .= " AND DATE(mdate) BETWEEN DATE('" . $jgive_graph_from_date . "') AND DATE('" . $jgive_socialads_end_date . "')";
		}
		else
		{
			$day         = date('d');
			$month       = date('m');
			$year        = date('Y');
			$statsforpie = array();

			$backdate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 30 days'));
			$groupby  = "";
		}

		// Pending donations
		$query = " SELECT COUNT(id) AS donations FROM #__jg_orders WHERE status= 'P'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Confirmed donations
		$query = " SELECT COUNT(id) AS donations FROM #__jg_orders WHERE status= 'C'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Canceled donations
		$query = " SELECT COUNT(id) AS donations FROM #__jg_orders WHERE status= 'E'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Denied donations
		$query = " SELECT COUNT(id) AS donations FROM #__jg_orders WHERE status= 'D'" . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Refunded donations
		$query = " SELECT COUNT(id) AS donations FROM #__jg_orders WHERE status= 'RF' " . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		return $statsforpie;
	}

	/**
	 * Get pending payouts of all vendors
	 *
	 * @return  array  Vendor Payable Amount
	 *
	 * @since   2.1
	 */
	public function getPendingPayouts()
	{
		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');
		$table = Table::getInstance('vendor', 'TJVendorsTable', array());

		JLoader::import('vendor', JPATH_SITE . '/components/com_tjvendors/models');
		$tjvendorsModelVendor = new TjvendorsModelVendor;
		$params               = ComponentHelper::getParams('com_jgive');
		$currency             = $params->get('currency', '');
		$payoutData           = array();
		$db                   = Factory::getDbo();
		$query                = $db->getQuery(true);
		$query->select('DISTINCT vendor_id');
		$query->from($db->qn('#__jg_campaigns'));
		$db->setQuery($query);
		$jgiveVendors = $db->loadColumn();

		foreach ($jgiveVendors as $vendorId)
		{
			$table->load(array('vendor_id' => $vendorId));
			$pendingPayout = $tjvendorsModelVendor->getPayableAmount($vendorId, 'com_jgive', $currency);

			if (!empty($pendingPayout))
			{
				$payoutData[$vendorId]['name'] = $table->vendor_title;
				$payoutData[$vendorId]['pendingPayout'] = $pendingPayout['com_jgive'][$currency];
			}
		}

		return $payoutData;
	}
}
