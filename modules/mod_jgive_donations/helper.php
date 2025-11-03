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

/**
 * Module Donation helper
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class ModJGiveDonationHelper
{
	/**
	 * Get data
	 *
	 * @param   String   $module_for         Param value for showing my donations, recent donations or top donations
	 * @param   Integer  $no_of_record_show  Param value that define How much record shown
	 *
	 * @return  object  result
	 *
	 * @since   1.7
	 */
	public function getData($module_for, $no_of_record_show)
	{
		$userId = Factory::getUser()->id;
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);

		$query->select(
						$db->qn(
								array(
										'i.id','i.order_id','i.original_amount','i.cdate','c.title',
										'u.name','d.first_name','d.last_name'
								)
							)
					);
		$query->select($db->qn('d.user_id', 'donor_id'));
		$query->select($db->qn('c.id', 'cid'));
		$query->from($db->qn('#__jg_orders', 'i'));
		$query->join('LEFT', $db->qn('#__jg_campaigns', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('i.campaign_id') . ')');
		$query->join('LEFT', $db->qn('#__jg_donors', 'd') . ' ON (' . $db->qn('d.id') . ' = ' . $db->qn('i.donor_id') . ')');
		$query->join('LEFT', $db->qn('#__jg_donations', 'don') . ' ON (' . $db->qn('don.id') . ' = ' . $db->qn('i.donation_id') . ')');
		$query->join('LEFT', $db->qn('#__users', 'u') . ' ON (' . $db->qn('u.id') . ' = ' . $db->qn('d.user_id') . ')');
		$query->where($db->qn('i.status') . ' = ' . $db->quote('C'));

		// For top donator
		if ($module_for == 'top_donations')
		{
			$query->order($db->qn('i.original_amount') . ' DESC');
		}
		// For recent donation
		elseif ($module_for == 'last_donations')
		{
			$query->order($db->qn('i.mdate') . ' DESC');
		}
		else
		{
			// For my donation
			$query->where($db->qn('d.user_id') . ' <> 0');
			$query->where($db->qn('d.user_id') . ' = ' . $userId);
		}

		$query->setLimit((int) $no_of_record_show);
		$db->setquery($query);
		$result = $db->loadobjectlist();

		// Get User avatar & profile URL
		$result = $this->_getDonorAvatar($result);

		return $result;
	}

	/**
	 * Get donor avatar
	 *
	 * @param   Array  $donars  Donors
	 *
	 * @return  donors
	 *
	 * @since   1.7
	 */
	public function _getDonorAvatar($donars)
	{
		$JgiveIntegrationsHelperPath = JPATH_SITE . '/components/com_jgive/helpers/integrations.php';

		// Load integrations helper file
		if (!class_exists('JgiveIntegrationsHelper'))
		{
			JLoader::register('JgiveIntegrationsHelper', $JgiveIntegrationsHelperPath);
			JLoader::load('JgiveIntegrationsHelper');
		}

		$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;

		foreach ($donars as $donar)
		{
			$donar->avatar      = $JgiveIntegrationsHelper->getUserAvatar($donar->donor_id);
			$donar->profile_url = $JgiveIntegrationsHelper->getUserProfileUrl($donar->donor_id);
		}

		return $donars;
	}
}
