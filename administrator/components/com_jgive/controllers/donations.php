<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

JLoader::import('joomla.application.component.model');

/**
 * Donations controller class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveControllerDonations extends JGiveController
{
	/**
	 * Method &getModel.
	 *
	 * @param   String  $name    Name
	 * @param   String  $prefix  Prefix
	 * @param   Array   $config  Config
	 *
	 * @return object|boolean The model
	 *
	 * @since    DEPLOY_VERSION
	 */
	public function &getModel($name = 'Donations', $prefix = 'JgiveModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method Save whatever status site admin set
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function save()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		JLoader::import('donations', JPATH_SITE . '/components/com_jgive/models');
		$jgiveModelDonorsObj = BaseDatabaseModel::getInstance('Donations', 'JgiveModel');
		$post                = Factory::getApplication()->input->post;
		$result              = $jgiveModelDonorsObj->changeOrderStatus($post);
		$msg                 = Text::_('COM_JGIVE_ERROR_SAVING_MSG');

		if ($result == 1)
		{
			$msg = Text::_('COM_JGIVE_SAVING_MSG');
		}
		elseif ($result == 3)
		{
			$msg = Text::_('COM_JGIVE_REFUND_SAVING_MSG');
		}

		$link       = 'index.php?option=com_jgive&view=donations';
		PluginHelper::importPlugin('system');
		Factory::getApplication()->triggerEvent('onAfterJGivePaymentUpdate', array($post->get('id', '', 'INT')));
		$this->setRedirect($link, $msg);
	}

	/**
	 * Method cancel
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function cancel()
	{
		$msg = Text::_('COM_JGIVE_CANCEL_MSG');
		$this->setRedirect('index.php?option=com_jgive', $msg);
	}

	/**
	 * Method loadprofiledata
	 *
	 * @return  json
	 *
	 * @since   1.8.1
	 */
	public function loadprofiledata()
	{
		$compaignUserid = Factory::getApplication()->input->get('compaignuserid');
		$path           = JPATH_ADMINISTRATOR . '/components/com_jgive/helpers/integrations.php';

		if (!class_exists('JgiveIntegrationsHelper_backend'))
		{
			JLoader::register('JgiveIntegrationsHelper_backend', $path);
			JLoader::load('JgiveIntegrationsHelper_backend');
		}

		$params        = ComponentHelper::getParams('com_jgive');
		$profileImport = $params->get('profile_import');

		// If profile import is on the call profile import function
		if ($profileImport)
		{
			$integrationsBackendHelper = new JgiveIntegrationsHelper_backend;
			$profiledata   = $integrationsBackendHelper->profileImport(1, $compaignUserid);

			if (!empty($profiledata))
			{
				unset($profiledata['campaign']);
				echo json_encode($profiledata);
				jexit();
			}
		}
	}

	/**
	 * Method placeOrder used for submit donation
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function placeOrder()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$path = JPATH_ADMINISTRATOR . '/components/com_jgive/helpers/donations.php';

		if (!class_exists('donations_backendHelper'))
		{
			JLoader::register('donations_backendHelper', $path);
			JLoader::load('donations_backendHelper');
		}

		$redirectUrl            = Route::_('index.php?option=com_jgive&view=donations');
		$input                  = Factory::getApplication()->input;
		$post                   = $input->post;
		$donationsBackendHelper = new donations_backendHelper;
		$result                 = $donationsBackendHelper->addOrder($post);
		$data                   = array();

		if ($result === false)
		{
			$data['success']      = 0;
			$data['redirect_uri'] = $redirectUrl;
			$link = 'index.php?option=com_jgive&view=donations';
		}
		else
		{
			$session = Factory::getSession();

			if ($session->get('JGIVE_order_id'))
			{
				$orderid             = $session->get('JGIVE_order_id');
				$data['success_msg'] = Text::_('COM_JGIVE_ORDER_CREATED_SUCCESS');
				$data['success']     = 1;
				$data['order_id']    = $orderid;
			}
			else
			{
				$data['success_msg']  = Text::_('COM_JGIVE_ORDER_CREATED_FAILED');
				$data['success']      = 0;
				$data['redirect_uri'] = $redirectUrl;
			}

			$link = 'index.php?option=com_jgive&view=donations';
		}

		$this->setRedirect($link, $data['success_msg']);
	}

	/**
	 * Method For Adding new donation Redirect to Donation Form
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function addNewDonation()
	{
		$link = 'index.php?option=com_jgive&view=donations&layout=paymentform';
		$this->setRedirect($link);
	}

	/**
	 * Method For Cancel Order
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function cancelorder()
	{
		$link = 'index.php?option=com_jgive&view=donations';
		$this->setRedirect($link);
	}

	/**
	 * Method For Getting GiveBack against campaign
	 *
	 * @return  json
	 *
	 * @since   1.8.1
	 */
	public function getGiveBackAgainstCampaign()
	{
		$cid = Factory::getApplication()->input->post->get('cid', '', 'INT');

		JLoader::import('CampaignHelper', JPATH_SITE . '/components/com_jgive/helpers');
		$campaignHelper = new campaignHelper;
		$result         = $campaignHelper->getCampaignGivebacks($cid);
		echo json_encode($result);
		jexit();
	}

	/**
	 * Method For Delete Donation
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function deleteDonations()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$model           = $this->getModel('donations');
		$donationIdArray = Factory::getApplication()->input->post->get('cid', '', 'Array');
		$msg             = Text::_('COM_JGIVE_ERR_DONATION_DELETED');

		if (!empty($donationIdArray))
		{
			$msg = Text::_('COM_JGIVE_ERR_DONATION_DELETED');

			if ($model->deleteDonations($donationIdArray))
			{
				$msg = Text::_('COM_JGIVE_DONATION_DELETED');
			}
		}

		$this->setRedirect(Uri::base() . "index.php?option=com_jgive&view=donations", $msg);
	}

	/**
	 * Method For redirecting on mass mailing layout
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function redirectToMassmailing()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app       = Factory::getApplication();
		$input     = $app->input;
		$redirect  = $input->get('view');
		$cids      = $input->get('cid', '', 'POST', 'ARRAY');
		$emaildata = $this->getModel()->getDonorsEmailByDonationId($cids);

		foreach ($emaildata as $key => $value)
		{
			if (empty($value['email']))
			{
				$app->enqueueMessage(Text::sprintf('JGIVE_DONORS_NO_MAIL_FOUND', $value['first_name'], $value['last_name']), 'warning');
			}
		}

		$session = Factory::getSession();
		$session->set('selected_donations_ids', $cids);
		$contactLink = Route::_(Uri::base() . 'index.php?option=com_jgive&view=donations&layout=mass_mailing&redirect=donations');

		if ($redirect == 'donors')
		{
			$contactLink = Route::_(Uri::base() . 'index.php?option=com_jgive&view=donations&layout=mass_mailing&redirect=donors');
		}

		$app->redirect($contactLink);
	}

	/**
	 * Method For sending email to selected email id
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function emailToSelected()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app             = Factory::getApplication();
		$input           = $app->input;
		$selected_emails = $input->get('selected_emails', '', 'POST', 'STRING');
		$subject         = $input->get('jgive_subject', '', 'POST', 'STRING');
		$body            = $input->get('jgive_message', '', 'RAW');
		$img_path        = 'img src="' . Uri::root();
		$model           = $this->getModel('donations');
		$result          = false;
		$redirect        = $input->get('redirect');

		$res             = new stdClass;
		$res->content    = str_replace('img src="' . Uri::root(), 'img src="', $body);
		$res->content    = str_replace('img src="', $img_path, $res->content);
		$res->content    = str_replace("background: url('" . Uri::root(), "background: url('", $res->content);
		$res->content    = str_replace("background: url('", "background: url('" . Uri::root(), $res->content);
		$emails          = explode(",", $selected_emails);
		$email_id        = array_unique($emails);

		JLoader::import('donors', JPATH_SITE . '/components/com_jgive/models');
		$jgiveModelDonorsObj = BaseDatabaseModel::getInstance('Donors', 'JgiveModel');

		if (is_array($email_id) && count($email_id))
		{
			foreach ($email_id AS $email)
			{
				// If donor is deleted dont send reminder
				if (!$email)
				{
					continue;
				}

				$result = $jgiveModelDonorsObj->sendMailToDonors($email, $subject, $body);
			}
		}
		else
		{
			$result = false;
		}

		if ($result == false)
		{
			$msg = $model->getError();
			$app->enqueueMessage($msg, 'error');
		}
		else
		{
			$msg = Text::_('COM_JGIVE_EMAIL_SUCCESSFUL');
			$app->enqueueMessage($msg, 'success');
		}

		$contactLink = Route::_(Uri::base() . 'index.php?option=com_jgive&view=donors');

		if ($redirect == 'donations')
		{
			$contactLink = Route::_(Uri::base() . 'index.php?option=com_jgive&view=donations');
		}

		$app->redirect($contactLink);
	}

	/**
	 * Method For Cancel email sending redirect to donation list view all layout
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function cancelEmail()
	{
		$app  = Factory::getApplication();
		$link = Route::_(Uri::base() . 'index.php?option=com_jgive&view=donors');

		if ($app->input->get('redirect') == 'donations')
		{
			$link = Route::_(Uri::base() . 'index.php?option=com_jgive&view=donations');
		}

		$this->setRedirect($link);
	}

	/**
	 * Get Rounded value(Goal Amount, donation Amount, Giveback Amount)
	 *
	 * @return JSON
	 *
	 * @since   2.2.0
	 */
	public function getRoundedValue()
	{
		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$value               = $this->input->get('amount', 'float');
		$roundedValue        = $jgiveFrontendHelper->getRoundedAmount($value);
		echo new JsonResponse($roundedValue);
		jexit();
	}
}
