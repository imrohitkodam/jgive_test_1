<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * JgiveControllerDonors controller class.
 *
 * @package  JGive
 * @since    1.8.1
 */
class JgiveControllerDonors extends JgiveController
{
	/**
	 * Method &getModel.
	 *
	 * @param   String  $name    Name
	 * @param   String  $prefix  Prefix
	 * @param   Array   $config  Config
	 *
	 * @return model
	 *
	 * @since    DEPLOY_VERSION
	 */
	public function &getModel($name = 'Donors', $prefix = 'JgiveModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to take id and redirect to contact us view
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function redirectforEmail()
	{
		$mainframe = Factory::getApplication();
		$input = $mainframe->input;

		// Donors id
		$donorsId = $input->get('cid', array(), 'ARRAY');
		$donorIds = array();
		$emails   = $this->getModel()->getDonorsEmail($donorsId);

		foreach ($emails as $key)
		{
			if (empty($key['email']))
			{
				Factory::getApplication()->enqueueMessage(Text::sprintf('JGIVE_DONORS_NO_MAIL_FOUND', $key['first_name'], $key['last_name']), 'warning');
			}
		}

		foreach ($donorsId as $donorId)
		{
			if (is_numeric($donorId))
			{
				$donorIds[] = $donorId;
			}
		}

		if (!empty($donorIds))
		{
			$itemid = $input->get('Itemid', '', 'POST', 'INT');
			$session = Factory::getSession();
			$session->set('selected_donor_item_ids', $donorIds);
			$contact_ink = Route::_('index.php?option=com_jgive&view=donors&layout=contact_us&Itemid=' . $itemid, false);
		}
		else
		{
			$jgiveFrontendHelper = new jgiveFrontendHelper;
			$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donors');
			$contact_ink = Route::_('index.php?option=com_jgive&view=donors&Itemid=' . $itemId, false);
		}

		$mainframe->redirect($contact_ink);
	}

	/**
	 * Method to get email for send mail to users
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function emailtoSelected()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Fetch the selected donors' list from the session, instead of posted form data
		$session              = Factory::getSession();
		$selectedDonorItemIds = $session->get('selected_donor_item_ids');
		$selectedEmails       = $this->getModel()->getDonorsEmail($selectedDonorItemIds);

		$mainframe     = Factory::getApplication();
		$input         = $mainframe->input;
		$subject       = $input->get('jgive_subject', '', 'STRING');
		$body          = $input->get('jgive_message', '', 'RAW');
		$itemid        = $input->get('Itemid', '', 'POST', 'INT');
		$img_path      = 'img src="' . Uri::root();

		$res           = new stdClass;
		$res->content  = str_replace('img src="' . Uri::root(), 'img src="', $body);
		$res->content  = str_replace('img src="', $img_path, $res->content);
		$res->content  = str_replace("background: url('" . Uri::root(), "background: url('", $res->content);
		$res->content  = str_replace("background: url('", "background: url('" . Uri::root(), $res->content);

		$model = $this->getModel('donors');

		foreach ($selectedEmails as $key => $value)
		{
			if (!empty($value['email']))
			{
				$email_id = $value['email'];
				$model->emailtoSelected($email_id, $subject, $body, $attachmentPath = '');
			}

			if ($value['email'] == '')
			{
				$mainframe->enqueueMessage(Text::sprintf('JGIVE_DONORS_NO_MAIL_FOUND', $value['first_name'], $value['last_name']), 'warning');
			}
			else
			{
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_JGIVE_EMAIL_SUCCESSFUL'), 'message');
			}
		}

		$contact_ink = Route::_('index.php?option=com_jgive&view=donors&Itemid=' . $itemid, false);
		$mainframe->redirect($contact_ink);
	}

	/**
	 * Method to Cancel mail
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancelToMail()
	{
		$mainframe     = Factory::getApplication();
		$input         = $mainframe->input;
		$itemid        = $input->get('Itemid', '', 'POST', 'INT');

		$contact_ink = Route::_('index.php?option=com_jgive&view=donors&Itemid=' . $itemid, false);
		$mainframe->redirect($contact_ink);
	}
}
