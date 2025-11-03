<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController as BaseAdminController;

/**
 * Individual contact controller class
 *
 * @since  2.5.0
 */
class JgiveControllerIndividuals extends BaseAdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object|boolean The model
	 *
	 * @since   2.5.0
	 */
	public function &getModel($name = 'Individualform', $prefix = 'JGiveModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to take id and redirect to contact us view for sending email
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	public function redirectforEmail()
	{
		$app          = Factory::getApplication();
		$input        = $app->input;
		$contactsId   = $input->get('cid', array(), 'ARRAY');
		$contactsId   = ArrayHelper::toInteger($contactsId);

		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individuals&layout=default');

		if (empty($contactsId))
		{
			$app->redirect(Route::_('index.php?option=com_jgive&view=individuals&Itemid=' . $itemId, false));
		}

		$session = Factory::getSession();
		$session->set('selected_contact_item_ids', $contactsId);
		$contactsDetails = JGive::model('Individuals', array('ignore_request' => true))->getContactsEmail($contactsId);

		if (!empty($contactsDetails))
		{
			foreach ($contactsDetails as $key)
			{
				if (empty($key['email']))
				{
					$app->enqueueMessage(Text::sprintf('COM_JGIVE_INDIVIDUALS_NO_MAIL_FOUND', $key['first_name'], $key['last_name']), 'warning');
				}
			}
		}

		$contactLink = Route::_('index.php?option=com_jgive&view=individuals&layout=contact_us&Itemid=' . $itemId, false);
		$app->redirect($contactLink);
	}

	/**
	 * Method to get email for send mail to contacts
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	public function emailtoSelected()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app   = Factory::getApplication();
		$input = $app->input;
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		// Fetch the selected contacts' list from the session, instead of posted form data
		$session            = Factory::getSession();
		$selectedContactIds = $session->get('selected_contact_item_ids');
		$selectedContactIds = ArrayHelper::toInteger($selectedContactIds);

		if (empty($selectedContactIds))
		{
			$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individuals&layout=default');
			$app->redirect(Route::_('index.php?option=com_jgive&view=individuals&layout=default&Itemid=' . $itemId, false));
		}

		$subject = $input->get('jgive_subject', '', 'STRING');
		$body    = $input->get('jgive_message', '', 'RAW');
		$model   = $this->getModel('individuals');

		$selectedContactEmails = $this->getModel('individuals')->getContactsEmail($selectedContactIds);
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individuals&layout=default');

		if (empty($selectedContactEmails))
		{
			$app->enqueueMessage(Text::sprintf('COM_JGIVE_INDIVIDUALS_NO_MAIL_FOUND', 'warning'));
			$app->redirect(Route::_('index.php?option=com_jgive&view=individuals&layout=default&Itemid=' . $itemId, false));
		}

		foreach ($selectedContactEmails as $key => $value)
		{
			if (!empty($value['email']) && ($model->sendMailToContacts($value['email'], $subject, $body)))
			{
				$app->enqueueMessage(Text::sprintf('COM_JGIVE_EMAIL_SUCCESSFUL'), 'message');
			}
		}

		$contactLink = Route::_('index.php?option=com_jgive&view=individuals&layout=default&Itemid=' . $itemId, false);
		$app->redirect($contactLink);
	}

	/**
	 * Method to Cancel mail
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	public function cancelToMail()
	{
		$app   = Factory::getApplication();
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individuals&layout=default');
		$app->redirect(Route::_('index.php?option=com_jgive&view=individuals&layout=default&Itemid=' . $itemId, false));
	}

	/**
	 * Publish the element
	 *
	 * @return  boolean
	 */
	public function publish()
	{
		parent::publish();

		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individuals&layout=default');

		$this->setRedirect(Route::_('index.php?option=com_jgive&view=individuals&layout=default&Itemid='. $itemId, false));
	}

	/**
	 * delete the element
	 *
	 * @return  boolean
	 */
	public function delete()
	{
		parent::delete();

		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individuals&layout=default');

		$this->setRedirect(Route::_('index.php?option=com_jgive&view=individuals&layout=default&Itemid='. $itemId, false));
	}
}
