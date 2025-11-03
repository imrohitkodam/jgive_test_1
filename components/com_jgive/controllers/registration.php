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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * JgiveControllerregistration controller class.
 *
 * @package  JGive
 * @since    1.8.1
 */
class JgiveControllerregistration extends FormController
{
	/**
	 * Method For saving data .
	 *
	 * @return void
	 *
	 * @since    1.8.1
	 */
	public function save($key = 'id', $urlVar = 'id')
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$input   = Factory::getApplication()->input;
		$model   = $this->getModel('registration');
		$session = Factory::getSession();
		$post    = $input->post->getArray();
		$itemId  = $input->get('Itemid');
		$message = "";

		// Let the model save it
		if (array_key_exists('guest_regis', $post))
		{
			$result  = $model->store($post);
			$message = Text::_('COM_JGIVE_REGISTRATION_FAILED');
			$link    = 'index.php?option=com_jgive&view=registration&Itemid=';

			if ($result)
			{
				$message = Text::_('COM_JGIVE_REGIS_USER_CREATE_MSG');
				$link    = 'index.php?option=com_users&view=profile&Itemid=';
			}

			$this->setRedirect($link . $itemId, $message);
		}
		else
		{
			$session->set('quick_reg_no_login', '1');
			$link = 'index.php?option=com_jgive&view=donations&layout=payment&Itemid=';
		}

		$this->setRedirect($link . $itemId, $message);
	}

	/**
	 * Method For cancel registration
	 *
	 * @return void
	 *
	 * @since    1.8.1
	 */
	public function cancel($key = null)
	{
		$msg     = Text::_('COM_JGIVE_REGISTRATION_OPERATION_CANCELLED');
		$input   = Factory::getApplication()->input;
		$itemId  = $input->get('Itemid');
		$session = Factory::getSession();
		$session->set('quick_reg_no_login', '1');
		$this->setRedirect('index.php?option=com_jgive&view=donations&layout=payment&Itemid=' . $itemId, $message);
	}
}
