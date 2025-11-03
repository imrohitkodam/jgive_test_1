<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2022 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * Migration file for JGive
 *
 * @since  2.3.0
 */
class TjHouseKeepingRecurringFrequency extends TjModelHouseKeeping
{
	public $title       = "Update Recurring frequencies";

	public $description = "Update recurring frequency values in #_jg_donations table";

	/**
	 * This function migrate donors with limit
	 *
	 * @return  array $result
	 *
	 * @since   2.3.0
	 */
	public function migrate()
	{
		$result = array();

		try
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true);

			// Update value of recurring_frequency column from WEEK to W - Changes as per PayPal
			$fields = array($db->quoteName('recurring_frequency') . ' = ' . $db->quote('W'));
			$conditions = array($db->quoteName('recurring_frequency') . ' = ' . $db->quote('WEEK'));
			$query->update($db->quoteName('#__jg_donations'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);

			// Update value of recurring_frequency column from MONTH to M - Changes as per PayPal
			$fields = array($db->quoteName('recurring_frequency') . ' = ' . $db->quote('M'));
			$conditions = array($db->quoteName('recurring_frequency') . ' = ' . $db->quote('MONTH'));
			$query->update($db->quoteName('#__jg_donations'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);

			// Update value of recurring_frequency column from YEAR to Y - Changes as per PayPal
			$fields = array($db->quoteName('recurring_frequency') . ' = ' . $db->quote('Y'));
			$conditions = array($db->quoteName('recurring_frequency') . ' = ' . $db->quote('YEAR'));
			$query->update($db->quoteName('#__jg_donations'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);

			// Update value of recurring_frequency column from DAY to D - Changes as per PayPal
			$fields = array($db->quoteName('recurring_frequency') . ' = ' . $db->quote('D'));
			$conditions = array($db->quoteName('recurring_frequency') . ' = ' . $db->quote('DAY'));
			$query->update($db->quoteName('#__jg_donations'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();

			$result['status']  = true;
			$result['message'] = "Migration is done successfully";
		}
		catch (Exception $e)
		{
			$result['err_code'] = '';
			$result['status']   = false;
			$result['message']  = $e->getMessage();
		}

		return $result;
	}
}
