<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

JLoader::import('joomla.application.component.model');
JLoader::import('CampaignHelper', JPATH_SITE . '/components/com_jgive/helpers');

/**
 * JGive Campaigns module helper class.
 *
 * @package  JGive
 * @since    1.8
 */
class ModJGiveHelper
{
	/**
	 * Function For where condition .
	 *
	 * @param   INT  $groupid        Groupid
	 * @param   INT  $group_page     Group_page
	 * @param   INT  $orderby        Orderby
	 * @param   INT  $orderby_dir    Orderby_dir
	 * @param   INT  $count          Count
	 * @param   INT  $image          Image
	 * @param   INT  $featured_camp  Campaign Id
	 *
	 * @return  mixed|Object|Boolean
	 *
	 * @since	1.8
	 */
	public function getData($groupid, $group_page, $orderby = '', $orderby_dir = '', $count = '', $image = '', $featured_camp = '', $categoryId = '', $campaignId ='')
	{
		JLoader::register('JGiveModelMediaXref', JPATH_SITE . '/components/com_jgive/models/mediaxref.php');
		$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');
		JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
		$filetable   = Table::getInstance('Files', 'TJMediaTable');
		$params      = ComponentHelper::getParams('com_jgive');
		$storagePath = $params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$subquery = $db->getQuery(true)
			->select('c.*, 
					SUM(o.amount) AS amount_received, 
					DATEDIFF(c.end_date, NOW()) AS remaining_days, 
					(IFNULL(SUM(o.amount), 0) / c.goal_amount) * 100 AS donation_percentage')
			->from('#__jg_campaigns AS c')
			->leftJoin('#__jg_orders AS o ON c.id = o.campaign_id AND o.status = "C"')
			->group('c.id');

		// Main query
		$query->select('*')
			->from('(' . $subquery . ') AS campaigns')
			->where($db->qn('published') . ' = ' . $db->quote('1'));

		if (($group_page == 1) && (!empty($groupid)))
		{
			$query->where($db->qn('c.js_groupid') . ' = ' . (int) $groupid);
		}

		if ($featured_camp)
		{
			$query->where($db->qn('c.featured') . ' = ' . $db->quote('1'));
		}

		/* Fetch related campaigns from the same category while excluding the current campaign itself from the results.*/
		if ($categoryId)
		{
    		$query->where(
        	$db->qn('campaigns.category_id') . ' = ' . $db->q($categoryId) .
			' AND ' . $db->qn('campaigns.id') . ' != ' . $db->q($campaignId)
    		);

			// Exclude closed campaigns
			$query->where($db->qn('campaigns.end_date') . ' > ' . $db->quote(Factory::getDate()->toSql()));

			// Exclude Not-yet-started campaigns
			$query->where($db->qn('campaigns.start_date') . ' <= ' . $db->quote(Factory::getDate()->toSql()));

			// Sorting logic based on campaign priority
			$query->order('
				CASE
					WHEN remaining_days <= 5 THEN 1
					WHEN featured = 1 THEN 2
					WHEN donation_percentage <= 30 THEN 3
					WHEN donation_percentage >= 80 THEN 4
					WHEN created >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 5
					ELSE 6
				END ASC,
				CASE
					WHEN remaining_days <= 5 THEN remaining_days
					ELSE NULL
				END ASC,
				CASE
					WHEN donation_percentage <= 30 THEN donation_percentage
					ELSE NULL
				END ASC,
				CASE
					WHEN donation_percentage >= 80 THEN donation_percentage
					ELSE NULL
				END DESC,
				start_date DESC
			');
		}

		$query->where($db->qn('campaigns.published') . ' = ' . $db->quote('1'));

		if ($orderby == 'goal_amount' || $orderby == 'start_date')
		{
			$query->order($db->qn('campaigns.' . $orderby) . $orderby_dir);
		}

		$query->setLimit((int) $count);
		$db->setQuery($query);
		$campaignsData = $db->loadobjectlist();

		foreach ($campaignsData as $campaign)
		{
			if ($campaign->id)
			{
				$mediaGallery = $modelMediaXref->getCampaignMedia($campaign->id, 'com_jgive.campaign', 0);

				if (!empty($mediaGallery))
				{
					$filetable->load($mediaGallery[0]->media_id);

					if (!empty($filetable))
					{
						$mediaType       = explode(".", $filetable->type);
						$imgPath         = $storagePath . '/' . $mediaType[0] . 's';
						$mediaConfig     = array('id' => $mediaGallery[0]->media_id, 'uploadPath' => $imgPath);
						$campaign->image = TJMediaStorageLocal::getInstance($mediaConfig);
					}
				}

				$campaignHelper             = new campaignHelper;
				$amounts                    = $campaignHelper->getCampaignAmounts($campaign->id);
				$campaign->amount_received  = $amounts['amount_received'];
				$campaign->remaining_amount = $amounts['remaining_amount'];
				$campaign->donor_count      = $campaignHelper->getCampaignDonorsCount($campaign->id);
			}
			else
			{
				return false;
			}
		}

		return $campaignsData;
	}

	/**
	 * Function For get campaign Amount .
	 *
	 * @param   INT  $cid    Campaign Id
	 * @param   INT  $image  Image
	 *
	 * @return  Query
	 *
	 * @since	1.8
	 */
	public function getCampaignAmounts($cid, $image)
	{
		$db      = Factory::getDbo();
		$amounts = array();
		$query = $db->getQuery(true);
		$query->select('c.max_donors');
		$query->select('COUNT(c.id) AS total');
		$query->select('c.id');
		$query->select('c.goal_amount');
		$query->select('c.type');
		$query->select('c.allow_exceed');
		$query->from($db->qn('#__jg_campaigns', 'c'));
		$query->where($db->qn('c.id') . ' = ' . $db->quote($cid));
		$db->setQuery($query);
		$amounts = $db->loadObject();

		return $amounts;
	}
}
