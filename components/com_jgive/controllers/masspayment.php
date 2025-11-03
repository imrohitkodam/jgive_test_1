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

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once JPATH_COMPONENT . '/controller.php';


/**
 * JgiveControllermasspayment controller class.
 *
 * @package  JGive
 * @since    1.8.1
 */
class JgiveControllermasspayment extends BaseController
{
	/**
	 * Method performmasspay .
	 *
	 * @return boolean
	 *
	 * @since    1.8.1
	 */
	public function performmasspay()
	{
		// Get Params
		$params = ComponentHelper::getParams('com_jgive');
		$send_payments_to_owner = $params->get('send_payments_to_owner');

		if (!$send_payments_to_owner)
		{
			$input = Factory::getApplication()->input;
			$pkey = $input->get('pkey', '');
			$params->get('private_key_cronjob');

			if ($pkey != $params->get('private_key_cronjob'))
			{
				echo Text::_('COM_JGIVE_SECRET_KEY_ERROR');

				return false;
			}

			if ($params->get('commission_fee') == 0)
			{
				echo '<b>' . Text::_('COM_JGIVE_COMMISSION_ZERO_ERROR') . '</b>';

				return false;
			}

			$model = $this->getModel('masspayment');
			$msg   = $model->performmasspay();
			echo $msg;
		}
	}
}
