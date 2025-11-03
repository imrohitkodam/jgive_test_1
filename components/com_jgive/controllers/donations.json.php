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

use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;

JLoader::import('vendorfee', JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');

/**
 * The reportform controller
 *
 * @since  2.5.4
 */
class JgiveControllerDonations extends JGiveController
{
	/**
	 * This methods update return platform fee on basis of donation amount
	 *
	 * @return  boolean returns 1 or 0.
	 *
	 * @since   2.5.4
	 */
	public function updatePlatformFee()
	{
		try
		{
			$app                 = Factory::getApplication();
			$input               = $app->input;
			$donationAmount      = $input->get('donationAmount', '', 'FLOAT');
			$campaignId          = $input->get('campaignId', 0, 'INT');
			$params              = JGive::config();
			$feeMode             = $params->get('fee_mode', 'inclusive', 'STRING');
			$sendPaymentsToOwner = $params->get('send_payments_to_owner');
			$platformFee    = 0;

			if (!$sendPaymentsToOwner && $feeMode == 'exclusive' && $donationAmount > 0)
			{
				// Global Commission
				$commissionFee      = $params->get('commission_fee', 0, 'INT');
				$fixedCommissionFee = $params->get('fixed_commissionfee', 0, 'INT');

				$campaignClass         = JGive::campaign($campaignId);
				$vendorId              = $campaignClass->getVendorId();
				$vendorCommissionTable = Table::getInstance('vendorfee', 'TjvendorsTable', array());
				$vendorCommissionTable->load(array('vendor_id' => $vendorId, 'client' => 'com_jgive'));
				$vendorCommissionFlat    = $vendorCommissionTable->flat_commission;
				$vendorCommissionPercent = $vendorCommissionTable->percent_commission;

				if ($vendorCommissionPercent > 0 || $vendorCommissionFlat > 0)
				{
					$commissionFee = $vendorCommissionPercent;
					$fixedCommissionFee = $vendorCommissionFlat;
				}

				$platformFee = (($donationAmount * $commissionFee) / 100) + $fixedCommissionFee;
			}

			$result['platformFee'] = JGive::utilities()->getRoundedAmount($platformFee);

			echo new JsonResponse($result);
		}
		catch (Exception $e)
		{
			echo new JsonResponse($e);
		}
	}
}
