<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * PaymentMode API class
 *
 * @since  2.5.4
 */
class JgiveApiResourceConfig extends ApiResource
{
	/**
	 * Get list of payment gateways data which is configured on site.
	 *
	 * @return  json payment gateways information
	 *
	 * @since   2.5.4
	 */
	public function get()
	{
		$resultArr = new stdclass;
		$user      = Factory::getUser();

		if (empty($user->id))
		{
			ApiError::raiseError(400, Text::_("PLG_API_JGIVE_INVALID_USER"));
		}

		$params          = Jgive::config();
		$paymentgateways = $params->get('gateways');
		$paymentmodes    = array();

		if (!empty($paymentgateways))
		{
			foreach ($paymentgateways as $paymentgateway)
			{
				$paymentmodes[$paymentgateway] = Jgive::utilities()->getPaymentGatewayName($paymentgateway);
			}
		}

		$resultArr->results['paymentgateways'] = $paymentmodes;
		$this->plugin->setResponse($resultArr);
	}
}
