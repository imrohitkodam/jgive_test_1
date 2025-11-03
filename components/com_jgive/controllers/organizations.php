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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController as BaseAdminController;

/**
 * Organizations contact controller class
 *
 * @since  2.5.0
 */
class JgiveControllerOrganizations extends BaseAdminController
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
	public function &getModel($name = 'Organizationform', $prefix = 'JGiveModel', $config = array())
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
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=organizations&layout=default');

		if (empty($contactsId))
		{
			$app->redirect(Route::_('index.php?option=com_jgive&view=organizations&Itemid=' . $itemId, false));
		}

		$session = Factory::getSession();
		$session->set('selected_contact_item_ids', $contactsId);
		$contactsDetails = JGive::model('organizations', array('ignore_request' => true))->getContactsEmail($contactsId);

		if (!empty($contactsDetails))
		{
			foreach ($contactsDetails as $key)
			{
				if (empty($key['email']))
				{
					$app->enqueueMessage(Text::sprintf('COM_JGIVE_ORGANIZATION_NO_MAIL_FOUND', $key['name']), 'warning');
				}
			}
		}

		$contactLink = Route::_('index.php?option=com_jgive&view=organizations&layout=contact_us&Itemid=' . $itemId, false);
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

		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=organizations');

		if (empty($selectedContactIds))
		{
			$app->redirect(Route::_('index.php?option=com_jgive&view=organizations&Itemid=' . $itemId, false));
		}

		$subject = $input->get('jgive_subject', '', 'STRING');
		$body    = $input->get('jgive_message', '', 'RAW');
		$model   = $this->getModel('organizations');

		$selectedContactEmails = $this->getModel('organizations')->getContactsEmail($selectedContactIds);

		if (empty($selectedContactEmails))
		{
			$app->enqueueMessage(Text::sprintf('COM_JGIVE_ORGANIZATION_NO_MAIL_FOUND', 'warning'));
			$app->redirect(Route::_('index.php?option=com_jgive&view=organizations&Itemid=' . $itemId, false));
		}

		foreach ($selectedContactEmails as $key => $value)
		{
			if (!empty($value['email']) && ($model->sendMailToContacts($value['email'], $subject, $body)))
			{
				$app->enqueueMessage(Text::sprintf('COM_JGIVE_EMAIL_SUCCESSFUL'), 'message');
			}
		}

		$itemId  = $input->get('Itemid', '', 'POST', 'INT');
		$contactLink = Route::_('index.php?option=com_jgive&view=organizations&layout=default&Itemid=' . $itemId, false);
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
		$app    = Factory::getApplication();
		$itemid = $app->input->get('Itemid', '', 'POST', 'INT');
		$app->redirect(Route::_('index.php?option=com_jgive&view=organizations&Itemid=' . $itemid, false));
	}

	/**
	 * Delete organizations and organization_contacts.
	 *
	 * @return void
	 *
	 * @since   2.5.0
	 */
	public function delete()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app    = Factory::getApplication()->input;
		$itemId = $app->get('Itemid', '', 'POST', 'INT');
		$cid    = $app->get('cid', '', 'Array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseWarning(500, Text::_('COM_JGIVE_ERR_ORGANIZATION_DELETED'));
		}
		else
		{
			$model = $this->getModel('organizations');
			$id    = ArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($id))
			{
				$this->setMessage(Text::_('COM_JGIVE_ORGANIZATION_DELETED'));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}

		$this->setRedirect(Uri::base() . "index.php?option=com_jgive&view=organizations&layout=default&Itemid=" . $itemId);
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
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=organizations&layout=default');

		$this->setRedirect(Route::_('index.php?option=com_jgive&view=organizations&layout=default&Itemid='. $itemId, false));
	}
}
