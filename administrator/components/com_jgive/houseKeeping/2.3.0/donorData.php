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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

/**
 * Migration file for JGive
 *
 * @since  2.3.0
 */
class TjHouseKeepingDonorData extends TjModelHouseKeeping
{
	public $title       = "Migrate Donors data";

	public $description = "Add donors/invester information into individual list view";

	/**
	 * This function migrate donors with limit
	 *
	 * @return  array $result
	 *
	 * @since   2.3.0
	 */
	public function migrate()
	{
		try
		{
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');

			// Reading file here for getting limit start and end
			$migrationfilePath   = JPATH_ADMINISTRATOR . '/components/com_jgive/houseKeeping/migrationLimit.txt';

			$fileData   = file($migrationfilePath);
			$limitstart = 0;

			if (strpos($fileData[0], 'limitstart') !== false)
			{
				$limitstart = explode(":", $fileData[0])[1];
			}

			$db = Factory::getDbo();

			// Fetch unique donor records from '#__jg_donors' table
			$query = $db->getQuery(true);
			$query->select(
				$db->qn(
					array(
						'd.first_name', 'd.last_name', 'd.email','d.phone',
						'd.user_id', 'd.country','d.city','d.zip',
						'd.address', 'd.address2', 'd.state', 'd.campaign_id'
					)
				)
			);

			$query->from($db->qn('#__jg_donors', 'd'));
			$query->group($db->qn('d.email'));
			$query->order($db->qn('d.id') . ('DESC'));
			$db->setQuery($query, (int) $limitstart, 20);
			$donorsData = $db->loadObjectList();

			if (!empty($donorsData))
			{
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaign');
				$jgiveModelCampaign = BaseDatabaseModel::getInstance('campaign', 'JGiveModel');

				foreach ($donorsData as $key => $donor)
				{
					$campaignData     = $jgiveModelCampaign->getItem($donor->campaign_id);
					$donor->vendor_id = $campaignData['campaign']->vendor_id;

					$donor->other_city_check = (!is_numeric($donor->city)) ? 'on' : '';
					$jgiveModelIndividual = BaseDatabaseModel::getInstance('Individualform', 'JGiveModel', array('ignore_request' => true));
					$jgiveModelIndividual->addDonorContact($donor);
				}

				// Update limitstart and limitend value
				$filePath = fopen($migrationfilePath, 'w+');

				if ($filePath !== false)
				{
					$newContents = "limitstart:" . ((int) $limitstart + 20);
					fwrite($filePath, $newContents);
					fclose($filePath);
				}
			}

			$result = array();

			if (empty($donorsData))
			{
				// Update limitstart and limitend value
				$filePath = fopen($migrationfilePath, 'w+');

				if ($filePath !== false)
				{
					$newContents = "limitstart:" . ((int) 0);
					fwrite($filePath, $newContents);
					fclose($filePath);
				}

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
