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
defined('_JEXEC') or die(';)');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


/**
 * jgiveModelmasspayment model class.
 *
 * @package  JGive
 * @since    1.8.1
 */
class JgiveModelmasspayment extends BaseDatabaseModel
{
	/**
	 * Method to Perform Mass Payment
	 *
	 * @return string
	 *
	 * @since    1.8.1
	 */
	public function performmasspay()
	{
		$params = ComponentHelper::getParams('com_jgive');
		$log    = Logger('paypal.log');
		$msg    = "<table border=\"0\" width=\"100%\">
					<tr>
						<th align=\"center\">" . Text::_('COM_JGIVE_OWNER') . "</th>
						<th align=\"center\">" . Text::_('COM_JGIVE_PAYOUT_AMT') . "</th>
						<th align=\"center\">" . Text::_('COM_JGIVE_PAYOUT_STATUS') . "</th>
					</tr>";

		$db           = Factory::getDbo();
		$event_owners = $this->getAllCampaignCretor();
		$nvpStr       = "";
		$k            = 0;
		$log->addEntry(array('comment' => '------------------New Masspayment Data-----------'));
		$arrayuniqid = $eventnamearr = $confirm = $arrayuserid = $reason = $payvaluestr = $arraypayval = "";

		for ($i = 0, $n = count($event_owners); $i < $n; $i++)
		{
			$payvalue = 0;
			$log->addEntry(array('comment' => 'Owner Id Being Processed' . $event_owners[$i]->creator_id));
			$rows      = $this->getCampaignData($event_owners[$i]->creator_id);
			$paytotal  = $rows->nprice - $rows->nfee;
			$pusers    = Factory::getuser($event_owners[$i]->creator_id);

			// Get Total Payout
			$sumresult = $this->gettotalpayout($event_owners[$i]->creator_id);
			$payvalue  = $paytotal - $sumresult;
			$log->addEntry(
							array('comment' => 'Amount earned: ' . $paytotal . ' Amount paid: ' .
							$sumresult . ' Balance: ' . $payvalue)
							);

			// Added by sagar to check minimum value for masspayment
			if ($payvalue < $params->get('min_val_masspay'))
			{
				$log->addEntry(array('comment' => Text::sprintf('MIN_AMT_MASSPAY_ERROR', $params->get('min_val_masspay'))));
				continue;
			}

			// Event owner emailid
			$payee_email = $this->getpaypalemail_campaignowner($event_owners[$i]->creator_id, $event_owners[$i]->id);

			if ($payvalue <= 0)
			{
				$log->addEntry(array('comment' => 'Amount is less than zero-' . $payvalue . ' so skip payment'));
				continue;
			}

			$payvaluestr .= $payvalue . "&";
			$arraypayval[$k] = $payvalue;
			$log->addEntry(array('comment' => 'Net Amount Paid-' . $payvalue));
			$paydata['creator']    = $event_owners[$i]->creator_id;
			$paydata['amount']     = $payvalue;
			$paydata['status']     = '0';
			$paydata['payee_name'] = $pusers->name;
			$paydata['email_id']   = $payee_email;
			$paydata['type']       = 'campaign';

			// Insert Payout Data
			$insertid         = $this->insertPayoutData($paydata);
			$arrayuserid[$k]  = $event_owners[$i]->creator_id;
			$eventnamearr[$k] = $event_owners[$i]->id;
			$confirm[$k]      = $event_owners[$i]->confirmedcount;
			$receiverEmail    = urlencode($payee_email);
			$amount           = urlencode($payvalue);
			$uniqid           = urlencode($insertid);
			$arrayuniqid[$k]  = $uniqid;
			$app              = Factory::getApplication();
			$sitename         = $app->getCfg('sitename');
			$note             = Text::sprintf('COM_JGIVE_MASSPAY_NOTE', date('Y-m-d H:i:s'), $sitename);
			$note             = urlencode($note);
			$nvpStr .= "&L_EMAIL$k=$receiverEmail&L_Amt$k=$amount&L_NOTE$k=$note&L_UNIQUEID$k=$uniqid";
			$k++;
		}

		$log->addEntry(array('comment' => 'Paypal Request string-' . $nvpStr));
		$httpParsedResponseAr = $this->PPHttpPost('MassPay', $nvpStr);
		$new_array = array_map(
								create_function('$key, $value', 'return $key."=".$value." & ";'),
								array_keys($httpParsedResponseAr),
								array_values($httpParsedResponseAr)
								);
		$Responsestr          = implode($new_array);
		$log->addEntry(array('comment' => 'Paypal Response string-' . $Responsestr));

		if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
		{
			for ($l = 0; $l < count($arrayuniqid); $l++)
			{
				$obj                 = new stdClass;
				$obj->id             = $arrayuniqid[$l];
				$obj->transaction_id = $httpParsedResponseAr["CORRELATIONID"];
				$obj->status         = '1';
				$resp                = $this->updatePayoutData($obj);

				if ($httpParsedResponseAr["ACK"] == "FAILURE")
				{
					$reason .= urldecode($httpParsedResponseAr['L_SHORTMESSAGE' . $l]) . "&";
				}
				else
				{
					$reason .= '--';
				}
			}
		}

		if (!empty($arrayuserid))
		{
			for ($j = 0; $j < count($arrayuserid); $j++)
			{
				$msg .= "<tr>
									<td align=\"center\">{$arrayuserid[$j]}</td>
									<td align=\"center\">{$arraypayval[$j]}</td>
									<td align=\"center\">" . strtoupper($httpParsedResponseAr["ACK"]) . "</td>

									</tr>";
			}

			$msg .= "</table>";
		}
		else
		{
			$msg = "<table><tr><td>" . Text::_('COM_JGIVE_NO_USERS_PROCESS') . "</td></tr></table>";
		}

		return $msg;
	}

	/**
	 * Method to PPHttpPost Payout Data
	 *
	 * @param   String  $methodName_  Method Name
	 * @param   String  $nvpStr_      NVP String
	 *
	 * @return httpParsedResponseAr
	 *
	 * @since    1.8.1
	 */

	public function PPHttpPost($methodName_, $nvpStr_)
	{
		$api = $this->getApiDetails();

		if (!$api)
		{
			echo JTEXT::_('COM_JGIVE_MASS_PAY_ERR');
		}

		$API_UserName  = $api['apiuser'];
		$API_Password  = $api['apipass'];
		$API_Signature = $api['apisign'];
		$API_Endpoint  = $api['apiend'];
		$version       = $api['apiv'];

		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

		// Get response from the server.
		$httpResponse = curl_exec($ch);

		if (!$httpResponse)
		{
			exit("$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
		}

		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);

		$httpParsedResponseAr = array();

		foreach ($httpResponseAr as $i => $value)
		{
			$tmpAr = explode("=", $value);

			if (sizeof($tmpAr) > 1)
			{
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}

		if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr))
		{
			exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
		}

		return $httpParsedResponseAr;
	}

	/**
	 * Method to Get API Details
	 *
	 * @return array
	 *
	 * @since    1.8.1
	 */
	public function getApiDetails()
	{
		$params = ComponentHelper::getParams('com_jgive');
		$apiend = 'https://api-3t.paypal.com/nvp';

		if ($params->get('sandbox') == 1)
		{
			$apiend = 'https://api-3t.sandbox.paypal.com/nvp';
		}

		$masspay_config = array(
			'apiuser' => $params->get('apiuser'),
			'apipass' => $params->get('apipass'),
			'apisign' => $params->get('apisign'),
			'apiend' => $apiend,
			'apiv' => $params->get('apiv')
		);
		$var            = $masspay_config;

		return $var;
	}

	/**
	 * Method to Get All campaign creator
	 *
	 * @return creator ids
	 *
	 * @since    1.8.1
	 */
	public function getAllCampaignCretor()
	{
		$db =& Factory::getDbo();
		$query = "SELECT camp.id ,camp.creator_id,count(o.campaign_id) as confirmedcount FROM `#__jg_campaigns`as camp
				INNER JOIN `#__jg_orders` as o ON
				o.campaign_id=camp.id
				WHERE o.status='C'
				GROUP BY camp.paypal_email";

		$db->setQuery($query);
		$createds = $db->loadObjectList();

		return $createds;
	}

	/**
	 * Method to Get Total Payout Data
	 *
	 * @param   INT  $creator  Creator is a User Id
	 *
	 * @return Sum of Amount
	 *
	 * @since    1.8.1
	 */
	public function gettotalpayout($creator)
	{
		$db =& Factory::getDbo();
		$query = "SELECT sum(amount) AS nsum
							FROM #__jg_payouts
							WHERE  status='1'
							AND user_id ='" . $creator . "'
							GROUP BY user_id";
		$db->setQuery($query);
		$sumresult = $db->loadResult();

		return $sumresult;
	}

	/**
	 * Method to Insert Payout Data
	 *
	 * @param   Array  $paydata  PayData
	 *
	 * @return boolean
	 *
	 * @since    1.8.1
	 */
	public function insertPayoutData($paydata)
	{
		$db =& Factory::getDbo();
		$res             = new stdClass;
		$res->id         = '';
		$res->user_id    = $paydata['creator'];
		$res->date       = date("Y-m-d H:i:s");
		$res->amount     = $paydata['amount'];
		$res->status     = $paydata['status'];
		$res->payee_name = $paydata['payee_name'];
		$res->email_id   = $paydata['email_id'];
		$res->ip_address = $_SERVER["REMOTE_ADDR"];
		$res->type       = $paydata['type'];

		if (!$db->insertObject('#__jg_payouts', $res, 'id'))
		{
			echo $db->stderr();

			return false;
		}

		return $db->insertid();
	}

	/**
	 * Method to Update Payout Data
	 *
	 * @param   Object  $obj  Object
	 *
	 * @return boolean
	 *
	 * @since    1.8.1
	 */
	public function updatePayoutData($obj)
	{
		$db =& Factory::getDbo();

		if (!$db->updateObject('#__jg_payouts', $obj, 'id'))
		{
			echo $db->stderr();

			return false;
		}

		return true;
	}

	/**
	 * Method to Getting paypal email of campaign owner
	 *
	 * @param   INT  $cusid  Campaign Creator ID
	 * @param   INT  $cid    Campaign ID
	 *
	 * @return result
	 *
	 * @since    1.8.1
	 */
	public function getpaypalemail_campaignowner($cusid, $cid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('vx.params'));
		$query->from($db->quoteName('#__vendor_client_xref', 'vx'));
		$query->join('LEFT', $db->quoteName('#__tjvendors_vendors', 'v') .
		' ON (' . $db->quoteName('v.vendor_id') . ' = ' . $db->quote('vx.vendor_id') . ')');
		$query->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
		$query->where($db->quoteName('client') . ' = ' . $db->quote('com_jgive'));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to Getting campaign Data
	 *
	 * @param   INT  $creator  Campaign Creator ID
	 *
	 * @return void
	 *
	 * @since    1.8.1
	 */
	public function getCampaignData($creator)
	{
		$db =& Factory::getDbo();
		$query = "SELECT camp.id, camp.creator_id, SUM( o.original_amount ) AS nprice, SUM( o.fee ) AS nfee
				FROM  `#__jg_campaigns` AS camp
				INNER JOIN  `#__jg_orders` AS o ON o.campaign_id = camp.id
				WHERE o.status =  'C'
				AND camp.creator_id =$creator
				GROUP BY camp.paypal_email";
		$db->setQuery($query);
		$rows = $db->loadObject();

		return $rows;
	}
}
