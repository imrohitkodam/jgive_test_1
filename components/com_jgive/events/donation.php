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
class JGiveTriggerDonation
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
	 * Trigger for onDonationAfterSave
	 *
	 * @param   ARRAY|boolean  $donationDetails  Donation / Order Array, false on failure
	 * @param   boolean        $isNew            true when new and false when existing
	 *
	 * @return  void
	 */
	public function onDonationAfterSave($donationDetails, $isNew)
	{
		switch ($isNew)
		{
			case true:
					/* If Donation order is new */
					$amount = $donationDetails['payment']->amount;
					$this->jGiveMailsHelper->newDonationStatus($donationDetails);
					$donationDetails['payment']->amount = $amount;
			break;

			case false:
				/* If Donation order is updated */
				$amount = $donationDetails['payment']->amount;
				$this->jGiveMailsHelper->updateDonationStatus($donationDetails);
				$donationDetails['payment']->amount = $amount;
			break;
		}

		/* If Donation order status is confirmed */
		if ($donationDetails['payment']->status === 'C')
		{
			$this->jGiveMailsHelper->generateReceipt($donationDetails);
		}

		return;
	}
}
