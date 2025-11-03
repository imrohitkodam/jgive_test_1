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

use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

/**
 * Migration file for JGive
 *
 * @since  2.3.0
 */
class TjHouseKeepingOrderData extends TjModelHouseKeeping
{
	public $title       = "Update Order data";

	public $description = "Set Contributor_id, donor_type to all donor records in donations";

	/**
	 * This function migrate orders with limit
	 *
	 * @return  array $result
	 *
	 * @since   2.3.0
	 */
	public function migrate()
	{
		try
		{
			// Reading file here for getting limit start and end
			$filePath   = JPATH_ADMINISTRATOR . '/components/com_jgive/houseKeeping/migrationLimit.txt';
			$fileData   = file($filePath);
			$limitstart = 0;

			if (strpos($fileData[0], 'limitstart') !== false)
			{
				$limitstart = explode(":", $fileData[0])[1];
			}

			Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jgive/tables');
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');

			$db = Factory::getDbo();

			// Fetch unique orders records from '#__jg_orders' table
			$query = $db->getQuery(true);
			$query->select($db->qn(array('o.id', 'o.cdate', 'o.donor_id')));
			$query->from($db->qn('#__jg_orders', 'o'));
			$db->setQuery($query, (int) $limitstart, 20);
			$ordersData = $db->loadObjectList();

			if (!empty($ordersData))
			{
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaign');
				$jgiveModelCampaign = BaseDatabaseModel::getInstance('campaign', 'JGiveModel');

				foreach ($ordersData as $key => $order)
				{
					// Set donor_type as 'ind' (individual) jg_donors table for old data
					$donorTable = Table::getInstance('Donor', 'JgiveTable', array());
					$donorTable->load(array('id' => (int) $order->donor_id));
					$donorTable->donor_type = 'ind';
					$donorTable->org_name = '';

					$campaignData     = $jgiveModelCampaign->getItem($donorTable->campaign_id);
					$donorTable->vendor_id = $campaignData['campaign']->vendor_id;

					$jgiveModelIndividual = BaseDatabaseModel::getInstance('Individualform', 'JGiveModel', array('ignore_request' => true));
					$donorTable->other_city_check = (!is_numeric($donorTable->city)) ? 'on' : '';
					$donorTable->contributor_id = $jgiveModelIndividual->addDonorContact($donorTable);
					unset($donorTable->vendor_id);
					unset($donorTable->other_city_check);

					$donorTable->save($donorTable);
				}

				// Update limitstart and limitend value
				$migrateFilePath = fopen($filePath, 'w+');

				if ($migrateFilePath !== false)
				{
					$newContents = "limitstart:" . ((int) $limitstart + 20);
					fwrite($migrateFilePath, $newContents);
					fclose($migrateFilePath);
				}
			}

			$result = array();

			if (empty($ordersData))
			{
				$result['status']   = true;
				$result['message']  = "Migration is done successfully";
			}
			else
			{
				$result['status']   = "inprogress";
				$result['message']  = "Migration is inprogress";
			}
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
