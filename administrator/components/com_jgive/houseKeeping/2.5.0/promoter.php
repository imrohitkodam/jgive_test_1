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

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * Migration file for JGive
 *
 * @since  2.5.0
 */
class TjHouseKeepingPromoter extends TjModelHouseKeeping
{
	public $title       = "Migrate campaign promoter data into vendor data";

	public $description = "Add campaign promoter personal data into vendor";

	/**
	 * This function migrate campaign promoter data into vendor data
	 *
	 * @return  array $result
	 *
	 * @since  2.5.0
	 */
	public function migrate()
	{
		$result = array();

		try
		{
			$filePath   = JPATH_ADMINISTRATOR . '/components/com_jgive/houseKeeping/migrationLimit.txt';
			$fileData   = file($filePath);
			$limitstart = 0;
			$lastId     = 0;
			$limit      = 200;

			if (strpos($fileData[0], 'limitstart') !== false)
			{
				$limitstart = explode(":", $fileData[0])[1];
			}

			$db       = Factory::getDbo();
			$subQuery = $db->getQuery(true);
			$subQuery->select('MAX(id)');
			$subQuery->from($db->qn('#__jg_campaigns'));
			$subQuery->where($db->qn('id') . '>' . $limitstart);
			$subQuery->setLimit($limit);
			$subQuery->group($db->qn('vendor_id'));
			$db->setQuery($subQuery);
			$campaignId = $db->loadColumn();

			if (empty($campaignId))
			{
				$result['status']  = true;
				$result['message'] = "Migration is done successfully";

				return $result;
			}

			$query = "SHOW COLUMNS FROM `#__jg_campaigns`";
			$db->setQuery($query);
			$columns = $db->loadobjectlist();

			for ($i = 0; $i < count($columns); $i++)
			{
				$field_array[] = $columns[$i]->Field;
			}

			if (!in_array('first_name', $field_array))
			{
				// We don't need the migration as the newer version is installed than 2.5.0
				$result['status']  = true;
				$result['message'] = "Migration is done successfully";

				return $result;
			}

			$campaignIdStr = implode(",", $campaignId);
			$query = $db->getQuery(true);
			$query->select(
					$db->qn(
							array(
									'id','vendor_id', 'creator_id', 'first_name',
									'last_name', 'address', 'address2', 'country', 'state',
									'city', 'other_city', 'phone', 'website_address', 'zip'
								)
							)
						);
			$query->from($db->qn('#__jg_campaigns'));
			$query->where($db->qn('id') . ' IN (' . $campaignIdStr . ')');
			$db->setQuery($query);
			$promoterArr = $db->loadAssocList();

			foreach ($promoterArr as $key => $promoter)
			{
				$lastId = $promoter['id'];
				$vendor = array();
				$vendorModel               = TJVendors::model('Vendor', array('ignore_request' => true));
				$vendor['vendor_id']       = $promoter['vendor_id'];
				$vendor['vendor_title']    = $promoter['first_name'] . ' ' . $promoter['last_name'];
				$vendor['address']         = $promoter['address'] . ' ' . $promoter['address2'];
				$vendor['zip']             = $promoter['zip'];
				$vendor['website_address'] = $promoter['website_address'];
				$vendor['country']         = $promoter['country'];
				$vendor['region']          = $promoter['state'];

				if (is_numeric($promoter['city']))
				{
					$vendor['city']       = $promoter['city'];
					$vendor['other_city'] = '';
				}
				else
				{
					$vendor['city']       = 'other';
					$vendor['other_city'] = $promoter['city'];
				}

				$vendor['phone_number']  = $promoter['phone'];
				$vendor['user_id']       = $promoter['creator_id'];
				$vendor['approved']      = 1;
				$vendor['vendor_client'] = 'com_jgive';
				$vendorModel->save($vendor);
			}

			$migrateFilePath = fopen($filePath, 'w+');

			if ($migrateFilePath !== false)
			{
				$newContents = "limitstart:" . ((int) $lastId);
				fwrite($migrateFilePath, $newContents);
				fclose($migrateFilePath);
			}

			$result['status']   = "";
			$result['message']  = "Migration is inprogress";
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
