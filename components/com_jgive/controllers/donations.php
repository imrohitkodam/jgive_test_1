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
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Donations form controller class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JgiveControllerDonations extends jgiveController
{
	/**
	 * Save donation order, this function called from backend too
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	public function save()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$model  = $this->getModel('donations');
		$post   = Factory::getApplication()->input->post;
		$model->setState('request', $post);
		$result = $model->changeOrderStatus($post);
		$msg    = Text::_('COM_JGIVE_ERROR_SAVING_MSG');
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		if ($result == 1)
		{
			$msg = Text::_('COM_JGIVE_SAVING_MSG');
		}
		elseif ($result == 3)
		{
			$msg = Text::_('COM_JGIVE_REFUND_SAVING_MSG');
		}

		$link   = 'index.php?option=com_jgive&view=donations&layout=all_donations';
		$itemId = $jgiveFrontendHelper->getItemId($link);
		$link   = Route::_($link . '&Itemid=' . $itemId, false);

		PluginHelper::importPlugin('system');

		// Call the plugin and get the result
		Factory::getApplication()->triggerEvent('onAfterJGivePaymentUpdate', array($post->get('id', '', 'INT')));
		$this->setRedirect($link, $msg);
	}

	/**
	 * Confirm payment
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function confirmpayment()
	{
		$model   = $this->getModel('donations');
		$session = Factory::getSession();
		$input   = Factory::getApplication()->input;
		$orderId = $session->get('JGIVE_order_id');

		// Clear JGIVE_order_id from seesion to place new order after click on donate
		$session->clear('JGIVE_order_id');
		$pgPlugin = $input->get('processor');
		$model->confirmpayment($pgPlugin, $orderId);
	}

	/**
	 * Cancel order & redirect to my donations view
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function cancel()
	{
		$msg  = Text::_('COM_JGIVE_PAYMENT_CANCEL_MSG');
		$link = 'index.php?option=com_jgive&view=donations';
		$this->setRedirect($link, $msg);
	}

	/**
	 * This method redirecting donor on payment page after clicking on donate button
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	public function donate()
	{
		$input               = Factory::getApplication()->input;
		$post                = $input->post;
		$cid                 = $post->get('cid', '', 'INT') ? $post->get('cid', '', 'INT'): $input->get('cid', '', 'INT');
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		// Clear session order id for placing new order
		$session = Factory::getSession();
		$session->clear('JGIVE_order_id');
		$session->clear('JGIVE_giveback_id');

		// Get giveback id
		$giveback_id = $input->get('giveback_id', 0, 'INT');
		$itemId      = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		$itemId      = (!empty($itemId)) ? $itemId : $input->get('Itemid', '', 'INT');

		// If it is giveback donation then get campaign id from url
		if (!empty($cid))
		{
			$model = $this->getModel('donations');
			$model->setSessionCampaignId($cid, $giveback_id);
		}
		else
		{
			$cid = '';
		}

		$redirect = Route::_('index.php?option=com_jgive&view=donations&layout=payment&cid=' . $cid . '&Itemid=' . $itemId, false);
		$this->setRedirect($redirect);
	}

	/**
	 * Save order details, Called when clicked on donate button on payment form frontend
	 *
	 * @return   void
	 *
	 * @since   1.6
	 */
	public function confirm()
	{
		// Check token
		Session::checkToken() or jexit('Invalid Token');

		$msg                 = '';
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$input               = Factory::getApplication()->input;
		$post                = $input->post->getArray();
		$result              = 0;

		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');

		if (!empty($post['cid']))
		{
			// Save donor details in session, so that those can be used in future donations in current session
			$model = $this->getModel('donations');
			$model->setSessionDonorData($post);
			$result = $model->addOrder($post);
		}

		if ((int) $result != -1)
		{
			$redirect = Route::_('index.php?option=com_jgive&view=donations&layout=confirm&Itemid=' . $itemId, false);
		}
		else
		{
			$msg = Text::_('COM_JGIVE_ERR_CONFIG_SAV');

			// Already exist eamil
			if ((int) $result == -1)
			{
				$msg = Text::_('COM_JGIVE_ERR_CONFIG_SAV_LOGIN');
			}

			$redirect = Route::_('index.php?option=com_jgive&view=donations&layout=payment&Itemid=' . $itemId, false);
		}

		if (!empty($msg))
		{
			$this->setRedirect($redirect, $msg);
		}
		else
		{
			$this->setRedirect($redirect);
		}
	}

	/**
	 * Get pament gateway on confirm payment view frontend.
	 *
	 * @param   string   $pgPlugin  Plugin name.
	 * @param   Integer  $orderId   Order id.
	 *
	 * @return   string  Payment gateway HTML.
	 *
	 * @since   1.6
	 */
	public function getHTML($pgPlugin, $orderId = null)
	{
		// Sleep to show animated ajax image
		sleep(1);

		$model    = $this->getModel('donations');
		$session  = Factory::getSession();
		$orderId  = $orderId ? $orderId : $session->get('JGIVE_order_id');
		$html     = $model->getHTML($pgPlugin, $orderId);

		if (!empty($html[0]))
		{
			return $html[0];
		}
	}

	/**
	 * Retry payment gateway on confirm payment view frontend.
	 *
	 * @return  json.
	 *
	 * @since   1.6
	 */
	public function retryPayment()
	{
		$input             = Factory::getApplication()->input;
		$getdata           = $input->get;
		$pgPlugin          = $getdata->get('gateway_name', '', 'STRING');
		$orderId           = $getdata->get('order', '', 'STRING');
		$singleGateway     = $getdata->get('single_gateway', '0', 'INT');
		$paymentGetwayForm = $this->getHTML($pgPlugin, $orderId);

		if ($singleGateway)
		{
			$plugin = PluginHelper::getPlugin('payment', $pgPlugin);

			if (!empty($plugin))
			{
				$pluginParams = new Registry($plugin->params);
				$pluginName   = $pluginParams->get('plugin_name', '', 'STRING');

				if (!empty($pluginName))
				{
					// We cant added this in payment plugins hence need to add this here
					$paymentGetwayForm = "<div class='col-xs-12'><h3>" . $pluginName . "</h3></div><br /><br />" . $paymentGetwayForm;
				}
			}
		}

		echo json_encode($paymentGetwayForm);
		jexit();
	}

	/**
	 * Payment gateways notification URL expect stripe
	 * Collect data given by payment gateway to confrim order payment
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function processPayment()
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$session = Factory::getSession();

		if ($session->has('payment_submitpost'))
		{
			$post = $session->get('payment_submitpost');
			$session->clear('payment_submitpost');
		}
		else
		{
			$post    = $input->post->getArray();
		}

		/**
		 * post = json_decode('{"mc_gross":"15.00","protection_eligibility":"Eligible","address_status":"confirmed",
		 * "payer_id":"XXXXXXXX","tax":"0.00","address_street":"1 Main St","payment_date":"04:16:38 Mar 27,
		 * 2014 PDT","payment_status":"Completed","charset":"windows-1252","address_zip":"XXXXX","first_name":"XXXXX",
		 * "mc_fee":"0.74","address_country_code":"US","address_name":"XXXXX","notify_version":"3.7","custom":"XXXXX-XXXXX",
		 * "payer_status":"verified","business":"XXXXX@XXXXX.XXXXX","address_country":"United States",
		 * "address_city":"San Jose","quantity":"0","verify_sign":"XXXXX",
		 * "payer_email":"XXXXX@XXXXX.XXXXX","txn_id":"XXXXX","payment_type":"instant","last_name":"XXXXX",
		 * "address_state":"CA","receiver_email":"XXXXX@XXXXX.XXXXX","payment_fee":"0.74","receiver_id":"XXXXX",
		 * "txn_type":"web_accept","item_name":"XXXXX","mc_currency":"USD","item_number":"",
		 * "residence_country":"US","test_ipn":"1","transaction_subject":"XXXXX-XXXXX","payment_gross":"15.00","ipn_track_id":"XXXXX",
		 * "main_response":1}',true);
		 *
		 *
		 * Do not delete : Sample stripe response
		 *
		 * $post = json_decode('{"order_id":"XXXXX-XXXXX","client":"com_jgive","payment_type":"recurring",
		 * "user_firstname":"XXXXX","user_id":"XXXXX","user_email":"XXXXX@XXXXX.XXXXX","item_name":"XXXXX",
		 * "payment_description":"XXXXX",
		 * "submiturl":"YOUR_SITE/index.php\/all-campaigns?controller=donations&amp;task=confirmpayment&amp;
		 * processor=stripe&amp;
		 * order_id=XXXXX-XXXXX",
		 * "return":"YOUR_SITE/index.php?option=com_jgive&view=donations&
		 * layout=details&donationid=29&processor=stripe&email=",
		 * "cancel_return":"YOUR_SITE/index.php?option=com_jgive&view=donation
		 * &donationid=XXXXX&processor=stripe&email=",
		 * "notify_url":"YOUR_SITE/index.php?option=com_jgive&controller=donations&task=processPayment
		 * &processor=stripe&order_id=XXXXX-XXXXX",
		 * "url":"YOUR_SITE/index.php?option=com_jgive&controller=donations&task=processPayment
		 * &processor=stripe&order_id=XXXXX-XXXXX",
		 * "campaign_promoter":"XXXXX@XXXXX.XXXXX","currency_code":"USD","amount":"649","is_recurring":"1",
		 * "recurring_frequency":"DAY","recurring_count":"5","country_code":"IN",
		 * "adaptiveReceiverList":null,"plan":{"id":"XXXXX-XXXXX","interval":"day","name":"Recurring donation plan",
		 * "created":1403700952,"amount":64900,"currency":"usd","object":"plan","livemode":false,"interval_count":1,
		 * "trial_period_days":null,"metadata":{"__PHP_Incomplete_Class_Name":"Stripe_AttachedObject"},
		 * "statement_description":null},"subscription":{"id":"XXXXX",
		 * "object":"customer","created":1403700960,"livemode":false,
		 * "description":null,"email":null,"delinquent":false,"metadata":{"__PHP_Incomplete_Class_Name":"Stripe_AttachedObject"},
		 * "subscriptions":{"__PHP_Incomplete_Class_Name":"Stripe_List"},"discount":null,"account_balance":0,"currency":"usd",
		 * "cards":{"__PHP_Incomplete_Class_Name":"Stripe_List"},"default_card":"XXXXX"}}',true);
		*/

		$pgPlugin = $input->get('processor', '', 'STRING');
		$model    = $this->getModel('donations');
		$orderId  = $input->get('order_id', '', 'STRING');

		if ($pgPlugin == '2checkout')
		{
			$orderId = $post['vendor_order_id'];
		}
		elseif ($orderId == '' && (isset($post['order_id'])))
		{
			$orderId = $post['order_id'];
		}

		if (!$orderId)
		{
			$app->redirect(uri::root());
		}

		if (empty($post) || empty($pgPlugin))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_SOME_ERROR_OCCURRED'), 'error');

			return;
		}

		JLoader::import('donations', JPATH_SITE . '/components/com_jgive/helpers');
		$this->donationsHelper = new DonationsHelper;
		$sessionOrderId        = $session->get('order_id');
		$OrderId               = $this->donationsHelper->getOrderIdKeyFromOrderId($orderId);

		if ($sessionOrderId != $OrderId)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$response = $model->processPayment($post, $pgPlugin, $orderId);

		if (!empty($response['msg']))
		{
			$app->enqueueMessage(trim($response['msg']));

			if ($response['status'] == 0 && $pgPlugin == 'ccavenue')
			{
				$app->enqueueMessage(Text::_('COM_JGIVE_SOME_ERROR_OCCURRED'), 'error');	
			}

			$app->redirect($response['return']);
		}
		elseif (empty($response['return']))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_SOME_ERROR_OCCURRED'), 'error');

			return;
		}
		else
		{
			$app->redirect($response['return']);
		}
	}

	/**
	 * Stripe notification URL
	 * Collect data given by stripe to confrim order payment
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function stripeProcessPayment()
	{
		$app      = Factory::getApplication();
		$input    = $app->input;
		$post     = $input->post->getArray();
		$pgPlugin = $input->get('processor', '', 'STRING');
		$model    = $this->getModel('donations');
		$orderId  = $input->get('order_id', '', 'STRING');

		if (empty($orderId) && (isset($post['order_id'])))
		{
			$orderId = $post['order_id'];
		}

		if ($pgPlugin == 'stripe')
		{
			// Retrieve the request's body and parse it as JSON
			$body = @file_get_contents('php://input');

			// Grab the event information
			$post = $event_json = json_decode($body, true);

			if ($event_json['type'] == 'invoice.payment_succeeded')
			{
				// Get plan id as order ID
				if (isset($event_json['data']['object']['lines']['data'][0]['plan']['id']))
				{
					$orderId = $event_json['data']['object']['lines']['data'][0]['plan']['id'];
				}
			}
			elseif (empty($orderId))
			{
				return 'undefined order id';
			}
		}

		if (empty($post) || empty($pgPlugin) )
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_SOME_ERROR_OCCURRED'), 'error');

			return;
		}

		$response = $model->processPayment($post, $pgPlugin, $orderId);

		if (!empty($response['msg']))
		{
			$app->enqueueMessage($response['msg']);
			$app->redirect($response['return']);
		}
		else
		{
			$app->redirect($response['return']);
		}
	}

	/**
	 * Check user entered email already exist in db for new user registration.
	 *
	 * @return  string  Message for user.
	 *
	 * @since   1.6
	 */
	public function chkmail()
	{
		$email  = Factory::getApplication()->input->get('email', '', 'STRING');
		$model  = $this->getModel('donations');
		$status = $model->checkMailExists($email);
		$e[]    = $status;

		if ($status == 1)
		{
			$e[] = Text::_('COM_JGIVE_MAIL_EXISTS');
		}

		echo json_encode($e);
		jexit();
	}

	/**
	 * Validate user login.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	public function login_validate()
	{
		$app                 = Factory::getApplication();
		$user                = Factory::getUser();
		$json                = array();
		$cid                 = $app->input->get('cid');
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		$itemId      = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		$itemId      = (!empty($itemId)) ? $itemId : $app->input->get('Itemid', '', 'INT');
		$redirectUrl = Route::_('index.php?option=com_jgive&view=donations&layout=payment&cid=' . $cid . '&Itemid=' . $itemId, false);

		if ($user->id)
		{
			$json['redirect'] = $redirectUrl;
		}

		if (!$json)
		{
			require_once JPATH_SITE . '/components/com_jgive/helpers/user.php';
			$userHelper = new UserHelper;

			// Login the user.
			if (!$userHelper->login(array('username' => $app->input->getString('email'), 'password' => $app->input->getString('password'))))
			{
				$json['error']['warning'] = Text::_('COM_JGIVE_CHECKOUT_ERROR_LOGIN');
			}
		}

		$json['redirect'] = $redirectUrl;
		echo json_encode($json);
		$app->close();
	}

	/**
	 * Save order information.
	 *
	 * @params  void
	 * @return  string  The seleted payment gateway HTML.
	 *
	 * @since   1.7
	 */
	public function placeOrder()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$redirectUrl = Route::_('index.php?option=com_jgive&view=donations');
		$input       = Factory::getApplication()->input;
		$post        = $input->post;
		$model       = $this->getModel('donations');

		$model->setSessionDonorData($post);
		$res  = $model->addOrder($post);

		if ($res === false)
		{
			$data                 = array();
			$data['success_msg']  = $model->getErrors();
			$data['success']      = 0;
			$data['redirect_uri'] = $redirectUrl;
			echo json_encode($data);
			jexit();
		}

		$session = Factory::getSession();

		if ($session->get('JGIVE_order_id'))
		{
			$payment_plg         = $session->get('payment_plg');
			$itemid              = $input->get('Itemid', 0);
			$orderid             = $session->get('JGIVE_order_id');
			$data['success_msg'] = Text::_('COM_JGIVE_ORDER_CREATED_SUCCESS');
			$data['success']     = 1;
			$data['order_id']    = $orderid;
			$data['orderHTML']   = $this->getorderHTML($orderid);
		}
		else
		{
			$data['success_msg']  = Text::_('COM_JGIVE_ORDER_CREATED_FAILED');
			$data['success']      = 0;
			$data['redirect_uri'] = $redirectUrl;
			echo json_encode($data);
			jexit();
		}

		$gateway             = $post->get('gateways', '', 'STRING');
		$data['gatewayhtml'] = $this->getHTML($gateway);
		$plugin              = PluginHelper::getPlugin('payment', $gateway);

		if (!empty($plugin))
		{
			$pluginParams = new Registry($plugin->params);
			$pluginName   = $pluginParams->get('plugin_name', '', 'STRING');

			if (!empty($pluginName))
			{
				$data['gatewayName'] = $pluginName;
			}
		}

		echo json_encode($data);
		jexit();
	}

	/**
	 * This method return donation order preview before placing it.
	 *
	 * @param   integer  $orderId  Order Id.
	 *
	 * @return  string  The order preview in HTML
	 *
	 * @since   1.6
	 */
	public function getorderHTML($orderId)
	{
		JLoader::import('models.donation', JPATH_SITE . '/components/com_jgive');
		$donationModel          = new JgiveModelDonation;
		$this->donationDetails  = $donationModel->getItem($orderId);

		$params                     = ComponentHelper::getParams('com_jgive');
		$this->currency_code        = $params->get('currency');
		$donationsHelper            = new donationsHelper;
		$this->pstatus              = $donationsHelper->getSStatusArray();
		$this->donations_site       = 1;
		$this->retryPayment_show    = 1;
		$this->retryPayment         = new StdClass;
		$this->retryPayment->status = '';
		$this->retryPayment->msg    = '';

		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$billpath            = $jgiveFrontendHelper->getViewpath('donations', 'details_'.JGIVE_LOAD_BOOTSTRAP_VERSION);

		ob_start();
			include $billpath;
			$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Function to get IPN response from Payment gateway.
	 *
	 * @return  redirects
	 *
	 * @since  2.1
	 */
	public function notify()
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$session = Factory::getSession();

		if ($session->has('payment_submitpost'))
		{
			$post = $session->get('payment_submitpost');
			$session->clear('payment_submitpost');
		}
		else
		{
			$post = $input->post->getArray();
		}

		$pgPlugin = $input->get('processor', '', 'STRING');
		$model    = $this->getModel('donations');
		$orderId  = $input->get('order_id', '', 'STRING');

		if ($pgPlugin == 'razorpay') 
		{
			// Get the JSON payload from Razorpay
			$input = file_get_contents('php://input');
			$event = json_decode($input, true);
			$entity = $event['payload']['payment']['entity'];
			$notes = $event['payload']['payment']['entity']['notes'];

			if ($notes['client'] == 'com_jgive')
			{
				$orderId = $notes['order_id'];
				$post = $entity;
			}
		}

		if (empty($post) || empty($pgPlugin))
		{
			echo new JsonResponse(400, Text::_('COM_JGIVE_SOME_ERROR_OCCURRED'), true);
		}

		try
		{
			$response = $model->processPayment($post, $pgPlugin, $orderId);

			echo new JsonResponse($response['status'], $response['msg'], true);
		}
		catch (Exception $e)
		{
			echo new JsonResponse($e);
		}

		jexit();
	}

	/**
	 * Get Rounded value
	 *
	 * @return JSON
	 *
	 * @since   2.2.0
	 */
	public function getRoundedValue()
	{
		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$amount              = $this->input->get('amount', 'float');
		$roundedValue        = $jgiveFrontendHelper->getRoundedAmount($amount);
		echo new JsonResponse($roundedValue);
		jexit();
	}

	/**
	 * This function is used to verification PAN number entered by user.
	 * 
	 * @param void
	 * 
	 * @return JSON
	 *
	 * @since 4.1.0
	 */
	public function panVerification(){
		$pannumber = $this->input->get('pannumber', 'string');

		$plugins = PluginHelper::getPlugin('panverification');
		if ( count($plugins) > 0) {
			PluginHelper::importPlugin('panverification', 'setu');
			$data = Factory::getApplication()->triggerEvent('onVerifyPan', array($pannumber));
			echo new JsonResponse($data);
			jexit();
		}
		echo new JsonResponse(['status'=>"error", 'message'=> Text::_("COM_JGIVE_PAN_SERVICE_NOT_FOUND")]);
		jexit();
	}

	public function sendAmoutThresholdMail()
	{
		$email = $this->input->get('email', 'string' , '');
		$phone = $this->input->get('phone', 'string' , '');
		$time = $this->input->get('time', 'string' , '');

		if ($email)
		{
			try
			{
				$jgiveparams = ComponentHelper::getParams('com_jgive');
				$siteConfig = Factory::getConfig();

				$adminEmailArray = array();
				$adminEmail      = (!empty($jgiveparams->get('email'))) ? $jgiveparams->get('email') : $siteConfig->get('mailfrom');
				$adminfromname      = (!empty($siteConfig->get('fromname'))) ? $siteConfig->get('fromname') : "Campaign Owner";
				$adminEmailArray = explode(',', $adminEmail);
				$adminRecipients = array(
					'email' => array(
						'to' => $adminEmailArray
					)
				);

				$replacements           = new stdClass;
				$replacements->info     = $this->siteinfo;
				$replacements->user = new stdClass;
				$replacements->user->email = $email;
				$replacements->user->time = $time;
				$replacements->user->phone = $phone;
				$replacements->user->adminname = $adminfromname;
				$siteInfo = new stdClass;
				$siteInfo->sitename = $this->sitename;

				$options = new Registry;
				$options->set('info', $siteInfo);
				$options->set('email', $email);
				$options->set('phone', $phone);
				$options->set('time', $time);

				$tjnotifications = new Tjnotifications;
				// Mail to site admin
				$tjnotifications->send("com_jgive", "donationAmoutLargerThanThreshold", $adminRecipients, $replacements, $options);

				echo new JsonResponse(200, Text::_('COM_JGIVE_HIGH_AMOUNT_EMAIL_SENT_SUCCESS_MESSAGE'), true);
			}
			catch(Exception $e)
			{
				echo new JsonResponse(400, Text::_('COM_JGIVE_HIGH_AMOUNT_EMAIL_SENT_FAIL_MESSAGE'), true);
			}
		}
		else 
		{
			echo new JsonResponse(400, Text::_('COM_JGIVE_HIGH_AMOUNT_EMAIL_SENT_FAIL_MESSAGE'), true);
		}
		
		jexit();
	}
}
