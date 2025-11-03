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

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

// Component Helper

/**
 * Reports Helper.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class ReportsHelper
{
	/**
	 * Getting Total Amount to be paid out
	 *
	 * @param   INT  $userid  UserId
	 *
	 * @return  float TotalAmount2BPaidOut
	 *
	 * @since   1.6
	 **/
	public function getTotalAmount2BPaidOut($userid = 0)
	{
		$db    = Factory::getDbo();
		$where = '';

		$query = "SELECT SUM(o.amount) AS total_amount, SUM(o.fee) AS total_commission
		FROM `#__jg_orders` AS o ";

		if ($userid)
		{
			$query .= " LEFT JOIN `#__jg_campaigns` AS c ON c.id=o.campaign_id ";
			$where = " AND c.creator_id=" . $userid;
		}

		$query .= " WHERE o.status='C'
		AND o.fund_holder=0 " . $where;

		// ONLY consider payments which are directly transferred to admin's account

		$db->setQuery($query);

		$result               = $db->loadObject();
		$TotalAmount2BPaidOut = 0;

		if ($result)
		{
			$TotalAmount2BPaidOut = $result->total_amount - $result->total_commission;
		}

		return $TotalAmount2BPaidOut;
	}

	/**
	 * Getting Total Paid out Amount
	 *
	 * @return  float TotalPaid
	 *
	 * @since   1.6
	 **/
	public function getTotalPaidOutAmount()
	{
		$params   = ComponentHelper::getParams('com_jgive');
		$currency = $params->get('currency', '', 'string');
		JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
		$tjvendorFrontHelper        = new TjvendorFrontHelper;
		$totalpaid = $tjvendorFrontHelper->getPaidAmount('', $currency, 'com_jgive');

		return $totalpaid;
	}

	/**
	 * Getting Total Excluded Amount
	 *
	 * @param   INT  $cid  Campaign Id
	 *
	 * @return  float Excluded Amount
	 *
	 * @since   1.6
	 **/
	public function getTotalAmount2BExcluded($cid)
	{
		$db = Factory::getDbo();

		if (!empty($cid))
		{
			$query = "SELECT SUM(o.amount) AS exclude_amount
			FROM `#__jg_orders` AS o
			WHERE o.status='C'
			AND o.campaign_id=" . $cid . "
			AND o.fund_holder=1 ";

			// ONLY consider payments which are directly transferred to campaign creator's account
			$db->setQuery($query);

			$exclude_amount = $db->loadResult();

			if ($exclude_amount == '')
			{
				$exclude_amount = 0;
			}

			return $exclude_amount;
		}

		$exclude_amount = 0;

		return $exclude_amount;
	}
}
