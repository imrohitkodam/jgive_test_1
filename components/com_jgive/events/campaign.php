<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

JLoader::import('components.com_jgive.helpers.mails', JPATH_SITE);
JLoader::import('components.com_jgive.helpers.donations', JPATH_SITE);

/**
 * Jgive triggers class for campaign.
 *
 * @since  1.6
 */
class JGiveTriggerCampaign
{
	/**
	 * Method acts as a consturctor
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		$app                    = Factory::getApplication();
		$this->menu             = $app->getMenu();
		$this->jgiveparams      = ComponentHelper::getParams('com_jgive');
		$this->siteConfig       = Factory::getConfig();
		$this->sitename         = $this->siteConfig->get('sitename');
		$this->user             = Factory::getUser();
		$this->tjnotifications  = new Tjnotifications;
		$this->jGiveMailsHelper = new JGiveMailsHelper;
	}

	/**
	 * Trigger for Campaign after save
	 *
	 * @param   int  $campaignDetails  Campaign Details
	 * @param   int  $isNew            isNew = true / !isNew = false
	 *
	 * @return  void
	 */
	public function onAfterCampaignSave($campaignDetails, $isNew)
	{
		switch ($isNew)
		{
			case true:
					/* Send mail on campaign create */
					$this->jGiveMailsHelper->onAfterCreateCampaign($campaignDetails);
				break;

			case false:
					/* Send mail on campaign edit */
					$this->jGiveMailsHelper->onAfterCampaignEdit($campaignDetails);
				break;
		}

		return;
	}

	/**
	 * Trigger for Campaign state change
	 *
	 * @param   int  $campaignDetails  Campaign Details
	 * @param   int  $isPublished      isPublished = 1 / !isPublished = 0
	 *
	 * @return  void
	 */
	public function onCampaignStateChange($campaignDetails, $isPublished)
	{
		switch ($isPublished)
		{
			case 1:
				/* Send mail on campaign publish */
				$this->jGiveMailsHelper->onAfterCampaignStateChange($campaignDetails);
				break;
		}

		return;
	}

	/**
	 * Trigger for campaign's Goal amount has reached for first time
	 *
	 * @param   int  $campaignDetails  campaignDetails Array
	 *
	 * @return  void
	 */
	public function goalAmountReachedFirstTime($campaignDetails)
	{
		return $this->jGiveMailsHelper->MailOnExceedingGoalAmount($campaignDetails);
	}
}
