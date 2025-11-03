<?php
/**
 * @package    Jgive_Activities
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\HTML\HTMLHelper;

jimport('techjoomla.jsocial');

$lang = Factory::getLanguage();
$lang->load('plg_system_jgiveactivities', JPATH_ADMINISTRATOR);
$lang->load('com_activitystream', JPATH_SITE);

/**
 * jgive_activities
 *
 * @package     Jgive_Activities
 * @subpackage  site
 * @since       1.0
 */
class PlgSystemJgiveActivities extends CMSPlugin
{
	public $plgSystemJgiveActivitiesHelper;
	
	/**
	 * Constructor
	 *
	 * @param   string  &$subject  subject
	 *
	 * @param   string  $config    config
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		require_once dirname(__FILE__) . '/helper.php';

		$this->plgSystemJgiveActivitiesHelper = new PlgSystemJgiveActivitiesHelper;
	}

	/**
	 * Method to include required scripts for activity streams
	 *
	 * @param   string  $theme  Theme used
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function onGetActivityScript($theme)
	{
		JLoader::register('ComActivityStreamHelper', JPATH_SITE . '/components/com_activitystream/helper.php');
		$comActivityStreamHelper = new ComActivityStreamHelper;
		$comActivityStreamHelper->getLanguageConstantForJs();

		$document = Factory::getDocument();
		$document->addScriptDeclaration('var root_url = \'' . Uri::base() . '\'');
		HTMLHelper::_('stylesheet', 'media/com_jgive/themes/' . $theme . '/css/theme.css');

		HTMLHelper::script('media/com_activitystream/scripts/mustache.min.js');
		HTMLHelper::script('media/com_activitystream/scripts/activities.jQuery.js');
	}

	/**
	 * Method to add activity streams for changes done in campaign
	 *
	 * @param   INT     $orderId   order id
	 * @param   STRING  $status    order status
	 * @param   STRING  $comment   order status change comment
	 * @param   INT     $sendMail  send mail flag
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function onAfterJGPaymentStatusChange($orderId, $status, $comment, $sendMail)
	{
		if ($status == 'C')
		{
			JLoader::import('models.donation', JPATH_SITE . '/components/com_jgive');

			$donationModel   = new JgiveModelDonation;
			$donationModel->setState('dataForactivity', 1);
			$donationDetails = $donationModel->getItem($orderId);

			// Activity for campaign complition - start
			if (($donationDetails['campaign']->amount_received >= $donationDetails['campaign']->goal_amount))
			{
				$result = $this->plgSystemJgiveActivitiesHelper->addCampaignCompleteActivity($donationDetails);
			}
			// Activity for campaign complition - end

			// Activity for donation done in campaign - start

			$result = $this->plgSystemJgiveActivitiesHelper->addDonationActivity($donationDetails);

			// Activity for donation done in campaign - end

			return $result;
		}

		return false;
	}

	/**
	 * Function OnAfterCampaignCreate
	 *
	 * @param   Array  $campaignData  Camapgin id, new data, old data
	 *
	 * @return  Boolean
	 *
	 * @since  2.1
	 */
	public function onAfterJGCampaignCreate($campaignData)
	{
		$campaignId = $campaignData['campaignId'];

		if (isset($campaignData['campaignOldData']))
		{
			$oldDetails = $campaignData['campaignOldData'];
		}

		$isNew = true;

		if (!empty($campaignData['id']))
		{
			$isNew = false;
		}

		if (!empty($campaignId))
		{
			if (isset($oldDetails) && $isNew == false)
			{
				// Activity for adding images to the campaign
				$result           = false;
				$newGalleryImages = array();

				for ($i = 0; $i < count($campaignData['gallery_file']['media']); $i++)
				{
					$imageIsNew = 1;

					if (isset($oldDetails->gallery))
					{
						foreach ($oldDetails->gallery as $k => $image)
						{
							if ($campaignData['gallery_file']['media'][$i] == $oldDetails->gallery[$k]->id)
							{
								$imageIsNew = 0;
								break;
							}
							else
							{
								continue;
							}
						}
					}

					if ($imageIsNew)
					{
						$newGalleryImages[] = $campaignData['gallery_file']['media'][$i];
					}
				}

				$newGalleryImages = array_filter($newGalleryImages);

				// Activity for gallery images
				if (!empty($newGalleryImages))
				{
					$result = $this->plgSystemJgiveActivitiesHelper->addMediaActivity($newGalleryImages, $campaignId, $campaignData);
				}

				// Activity for extending campaign date or end date changed- start
				if (!empty($campaignData['end_date']) && !empty($campaignData['campaignOldData']->end_date))
				{
					$newEndDate = $campaignData['end_date'];
					$oldEndDate = $campaignData['campaignOldData']->end_date;

					if ($newEndDate != $oldEndDate)
					{
						$result = $this->plgSystemJgiveActivitiesHelper->endDateChangeActivity($newEndDate, $oldEndDate, $campaignId);
					}
				}

				// Activity for adding give away - start
				foreach ($campaignData['newAddedGiveBacks'] as $giveBack)
				{
						$client         = 'com_jgive.givebacks.' . $giveBack['id'];
						$mediaXrefTable = Table::getInstance('mediaxref', 'JGiveTable', array());
						$mediaXrefTable->load(
							array(
								'client_id' => $campaignId,
								'client' => $client
							)
						);

						$mediaTable = Table::getInstance('media', 'JGiveTable', array());

						if ($mediaXrefTable->media_id)
						{
							$mediaTable->load(
								array(
									'id' => $mediaXrefTable->media_id
								)
							);
						}

						if ($mediaTable->source)
						{
							$giveBackImage['image'] = Uri::root() . 'media/com_jgive/campaigns/images/' . $mediaTable->source;
						}
						else
						{
							$giveBackImage['image'] = "";
						}

						$givebackDetails = array();
						$givebackDetails['giveback_image'] = $giveBackImage['image'];
						$givebackDetails['giveback_id'] = $giveBack['id'];
						$newAddedGivebacks[] = $givebackDetails;
				}

				if (!empty($newAddedGivebacks))
				{
					$result = $this->plgSystemJgiveActivitiesHelper->addGivebackActivity($newAddedGivebacks, $campaignId);
				}
				// Activity for adding give away - end

				// Remove/update activites related to the deleted images
				if (!empty($campaignData['deletedGiveBacks']))
				{
					$this->plgSystemJgiveActivitiesHelper->removeGiveBackActivity($campaignData['deletedGiveBacks'], $campaignId);
				}
			}
			elseif ($isNew == true)
			{
				$result = $this->plgSystemJgiveActivitiesHelper->addCampaignActivity($campaignId, $campaignData);
			}

			return $result;
		}
	}

	/**
	 * Function onAfterJGReportCreate
	 *
	 * @param   Array  $reportData  Report related data
	 *
	 * @return  Boolean
	 *
	 * @since   2.2.0
	 */
	public function onAfterJGReportCreate($reportData)
	{
		$result = $this->plgSystemJgiveActivitiesHelper->addReportActivity($reportData);

		return $result;
	}

	/**
	 * Function onAfterJGReportDelete
	 *
	 * @param   Object  $context  Context object
	 * @param   Object  $table    Table object
	 *
	 * @return  void
	 *
	 * @since   2.2.0
	 */
	public function onAfterJGReportDelete($context, $table)
	{
		$this->plgSystemJgiveActivitiesHelper->deleteReportActivity($table->id);
	}

	/**
	 * Function onPostActivity
	 *
	 * @param   MIXED  $data  data
	 *
	 * @return  Array|Boolean
	 *
	 * @since	1.8
	 */
	public function onPostActivity($data)
	{
		$jinput        = Factory::getApplication()->input;
		$componentName = $jinput->post->get('option');

		if ($componentName == 'com_jgive' && !empty($data))
		{
			$result = $this->plgSystemJgiveActivitiesHelper->onPostActivity($data);

			return $result;
		}
	}

	/**
	 * Function onAfterMediaDelete
	 *
	 * @param   Integer  $mediaId  Media Id
	 *
	 * @return  void
	 *
	 * @since  1.8
	 */
	public function onBeforeJGMediaDelete($mediaId)
	{
		if ($mediaId)
		{
			$this->plgSystemJgiveActivitiesHelper->removeMediaActivity($mediaId);
		}
	}
}
