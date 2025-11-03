<?php
/**
 * @package     JGIVE
 * @subpackage  PlgActionlogJgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

/**
 * JGive Actions Logging Plugin.
 *
 * @since  2.2.1
 */
class PlgActionlogJgive extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  2.2.1
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  2.2.1
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  2.2.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Proxy for ActionlogsModelUserlog addLog method
	 *
	 * This method adds a record to #__action_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array   $messages            The contents of the messages to be logged
	 * @param   string  $messageLanguageKey  The language key of the message
	 * @param   string  $context             The context of the content passed to the plugin
	 * @param   int     $userId              ID of user perform the action, usually ID of current logged in user
	 *
	 * @return  void
	 *
	 * @since   2.2.1
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		if (JVERSION >= '4.4.0')
		{
			$model = Factory::getApplication()->bootComponent('com_actionlogs')
            ->getMVCFactory()->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);
		}
		else if (JVERSION >= '4.0')
		{
			$model = new ActionlogModel;
		}
		else
		{
			JLoader::register('ActionlogsModelActionlog', JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php');
			$model = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
		}

		$model->addLog($messages, $messageLanguageKey, $context, $userId);
	}

	/**
	 * On saving campaign data logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   Object   $campaignData  Holds the report data
	 * @param   Boolean  $isNew         True if a new report is stored.
	 *
	 * @return  void
	 *
	 * @since    2.2.1
	 */
	public function onAfterJGCampaignSave($campaignData, $isNew)
	{
		if (!$this->params->get('logActionForCampaignSave', 1))
		{
			return;
		}

		$context = Factory::getApplication()->input->get('option');

		$user = Factory::getUser();

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JGIVE_CAMPAIGN_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JGIVE_CAMPAIGN_UPDATED';
			$action             = 'update';
		}

		$message = array(
			'action'      => $action,
			'id'          => $campaignData->id,
			'title'       => $campaignData->title,
			'type'        => $campaignData->type,
			'itemlink'    => 'index.php?option=com_jgive&task=campaign.edit&id=' . $campaignData->id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On deleting campaign data logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   String  $context  com_jgive
	 * @param   Object  $table    Holds the campaign data.
	 *
	 * @return  void
	 *
	 * @since    2.2.1
	 */
	public function jgOnAfterCampaignDelete($context, $table)
	{
		if (!$this->params->get('logActionForCampaignDelete', 1))
		{
			return;
		}

		$context = Factory::getApplication()->input->get('option');
		$user    = Factory::getUser();

		$messageLanguageKey = 'PLG_ACTIONLOG_JGIVE_CAMPAIGN_DELETED';
		$message = array(
				'action'      => 'delete',
				'id'          => $table->id,
				'title'       => $table->title,
				'type'        => $table->type,
				'userid'      => $user->id,
				'username'    => $user->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On changing state of campaign logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   String  $context  com_jgive
	 * @param   array   $pks      Holds array of primary key.
	 * @param   int     $value    Switch case value.
	 *
	 * @return  void
	 *
	 * @since    2.2.1
	 */
	public function onAfterJGCampaignChangeState($context, $pks, $value)
	{
		if (!$this->params->get('logActionForCampaignStateChange', 1))
		{
			return;
		}

		$jgiveTableCampaign = Table::getInstance('campaign', 'JGiveTable', array());
		$context  = Factory::getApplication()->input->get('option');
		$jUser    = Factory::getUser();
		$userId   = $jUser->id;
		$userName = $jUser->username;

		switch ($value)
		{
			case 0:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_UNPUBLISHED';
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_PUBLISHED';
				$action             = 'publish';
				break;
			case 2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_ARCHIVED';
				$action             = 'archive';
				break;
			case -2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_TRASHED';
				$action             = 'trash';
				break;
			case 3:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_FEATURED';
				$action             = 'featured';
				break;
			case -3:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_UNFEATURED';
				$action             = 'unfeatured';
				break;
			default:
				$messageLanguageKey = '';
				$action             = '';
				break;
		}

		foreach ($pks as $pk)
		{
			$jgiveTableCampaign->load(array('id' => $pk));

			$message = array(
					'action'      => $action,
					'id'          => $jgiveTableCampaign->id,
					'title'       => $jgiveTableCampaign->title,
					'type'        => $jgiveTableCampaign->type,
					'itemlink'    => 'index.php?option=com_jgive&view=campaign&layout=edit&id=' . $jgiveTableCampaign->id,
					'userid'      => $userId,
					'username'    => $userName,
					'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
				);

			$this->addLog(array($message), $messageLanguageKey, $context, $userId);
		}
	}

	/**
	 * On saving report data logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   array    $reportData  Holds the report data
	 * @param   Boolean  $isNew       True if a new report is stored.
	 *
	 * @return  void
	 *
	 * @since    2.2.1
	 */
	public function onAfterJGReportSave($reportData, $isNew)
	{
		if (!$this->params->get('logActionForReportSave', 1))
		{
			return;
		}

		$context = Factory::getApplication()->input->get('option');

		$user = Factory::getUser();

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JGIVE_REPORT_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JGIVE_REPORT_UPDATED';
			$action             = 'update';
		}

		$message = array(
			'action'      => $action,
			'id'          => $reportData['id'],
			'title'       => $reportData['title'],
			'campaignTitle' => $reportData['campaign_title'],
			'campaignlink'  => 'index.php?option=com_jgive&view=campaign&layout=edit&id=' . $reportData['campaign_id'],
			'itemlink'    => 'index.php?option=com_jgive&task=reportform.edit&id=' . $reportData['id'],
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On deleting report data logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   String  $context  com_jgive
	 * @param   Object  $table    Holds the campaign data.
	 *
	 * @return  void
	 *
	 * @since    2.2.1
	 */
	public function onAfterJGReportDelete($context, $table)
	{
		if (!$this->params->get('logActionForReportDelete', 1))
		{
			return;
		}

		$context = Factory::getApplication()->input->get('option');
		$user    = Factory::getUser();

		$messageLanguageKey = 'PLG_ACTIONLOG_JGIVE_REPORT_DELETED';
		$message = array(
				'action'      => 'delete',
				'id'          => $table->id,
				'title'       => $table->title,
				'userid'      => $user->id,
				'username'    => $user->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On payment successful for donation logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   Object  $orderData  Holds the campaign data.
	 *
	 * @return  void
	 *
	 * @since    2.2.1
	 */
	public function onAfterJGPaymentSuccess($orderData)
	{
		if (!$this->params->get('logActionForPaymentSuccess', 1))
		{
			return;
		}

		$context = Factory::getApplication()->input->get('option');

		$user = Factory::getUser();

		if ($orderData['campaign']->type == 'donation')
		{
			$action = 'donation';
			$type   = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_TYPE_DONATION';
		}

		if ($orderData['campaign']->type == 'investment')
		{
			$action = 'investment';
			$type   = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_TYPE_INVESTMENT';
		}

		if ($orderData['donor']->user_id)
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_DONATION_PAYMENT_SUCCESS';

			$message = array(
				'action'      => $action,
				'id'          => $orderData['payment']->id,
				'orderid'     => $orderData['payment']->order_id,
				'campaign'    => $orderData['campaign']->title,
				'type'        => $type,
				'itemlink'    => 'index.php?option=com_jgive&task=campaign.edit&id=' . $orderData['campaign']->id,
				'orderlink'   => 'index.php?option=com_jgive&view=donation&layout=default&donationid=' . $orderData['payment']->id,
				'userid'      => $orderData['donor']->user_id,
				'username'    => Factory::getUser($orderData['donor']->user_id)->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $orderData['donor']->user_id,
			);
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_DONATION_PAYMENT_SUCCESS_BY_GUEST_USER';

			$message = array(
				'action'      => $action,
				'id'          => $orderData['payment']->id,
				'orderid'     => $orderData['payment']->order_id,
				'campaign'    => $orderData['campaign']->title,
				'type'        => $type,
				'itemlink'    => 'index.php?option=com_jgive&task=campaign.edit&id=' . $orderData['campaign']->id,
				'orderlink'   => 'index.php?option=com_jgive&view=donation&layout=default&donationid=' . $orderData['payment']->id,
				'username'    => $orderData['donor']->first_name,
			);
		}

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On donating amount to campaign logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   Object  $orderData  Holds the campaign data.
	 *
	 * @return  void
	 *
	 * @since    2.2.1
	 */
	public function onAfterJGPaymentStatusProcess($orderData)
	{
		if (!$this->params->get('logActionForPaymentStatusProcess', 1))
		{
			return;
		}

		$context = $this->app->input->get('option');

		$user = Factory::getUser();

		if ($orderData['campaign']->type == 'donation')
		{
			$action = 'donation';
			$type   = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_TYPE_DONATION';
		}

		if ($orderData['campaign']->type == 'investment')
		{
			$action = 'investment';
			$type   = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_TYPE_INVESTMENT';
		}

		if ($orderData['donor']->user_id)
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_DONATION_PLACED';

			$message = array(
				'action'      => $action,
				'id'          => $orderData['payment']->id,
				'orderid'     => $orderData['payment']->order_id,
				'campaign'    => $orderData['campaign']->title,
				'type'        => $type,
				'itemlink'    => 'index.php?option=com_jgive&task=campaign.edit&id=' . $orderData['campaign']->id,
				'orderlink'   => 'index.php?option=com_jgive&view=donation&layout=default&donationid=' . $orderData['payment']->id,
				'userid'      => $orderData['donor']->user_id,
				'username'    => Factory::getUser($orderData['donor']->user_id)->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $orderData['donor']->user_id,
			);

			if (!empty($orderData['isadmin']))
			{
				$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_DONATION_PLACED_ADMIN';

				$message = array(
				'action'      => $action,
				'id'          => $orderData['payment']->id,
				'orderid'     => $orderData['payment']->order_id,
				'campaign'    => $orderData['campaign']->title,
				'type'        => $type,
				'adminlink'   => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
				'adminname'   => $user->username,
				'itemlink'    => 'index.php?option=com_jgive&task=campaign.edit&id=' . $orderData['campaign']->id,
				'orderlink'   => 'index.php?option=com_jgive&view=donation&layout=default&donationid=' . $orderData['payment']->id,
				'userid'      => $orderData['donor']->user_id,
				'username'    => Factory::getUser($orderData['donor']->user_id)->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $orderData['donor']->user_id,
				);
			}
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_DONATION_PLACED_BY_GUEST_USER';

			$message = array(
				'action'      => $action,
				'id'          => $orderData['payment']->id,
				'orderid'     => $orderData['payment']->order_id,
				'campaign'    => $orderData['campaign']->title,
				'type'        => $type,
				'itemlink'    => 'index.php?option=com_jgive&task=campaign.edit&id=' . $orderData['campaign']->id,
				'orderlink'   => 'index.php?option=com_jgive&view=donation&layout=default&donationid=' . $orderData['payment']->id,
				'username'    => $orderData['donor']->first_name,
			);

			if (!empty($orderData['isadmin']))
			{
				$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_DONATION_PLACED_BY_GUEST_USER_ADMIN';

							$message = array(
				'action'      => $action,
				'id'          => $orderData['payment']->id,
				'orderid'     => $orderData['payment']->order_id,
				'campaign'    => $orderData['campaign']->title,
				'type'        => $type,
				'adminlink'   => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
				'adminname'   => $user->username,
				'itemlink'    => 'index.php?option=com_jgive&task=campaign.edit&id=' . $orderData['campaign']->id,
				'orderlink'   => 'index.php?option=com_jgive&view=donation&layout=default&donationid=' . $orderData['payment']->id,
				'username'    => $orderData['donor']->first_name,
				);
			}
		}

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On after deleting order data logging method
	 *
	 * Method is called after order data is deleted from  the database.
	 *
	 * @param   array  $orderData  Holds the orders data.
	 *
	 * @return  void
	 *
	 * @since   2.2.1
	 */
	public function onAfterJGOrderDelete($orderData)
	{
		if (!$this->params->get('logActionForDonationDeleted', 1))
		{
			return;
		}

		if ($orderData['campaign']->type == 'donation')
		{
			$type   = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_TYPE_DONATION';
		}

		if ($orderData['campaign']->type == 'investment')
		{
			$type   = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_TYPE_INVESTMENT';
		}

		$context = Factory::getApplication()->input->get('option');
		$user    = Factory::getUser();
		$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_DONATION_DELETE';

		$message = array(
			'action'      => 'delete',
			'type'        => $type,
			'id'          => $orderData['payment']->id,
			'title'       => $orderData['payment']->order_id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&view=user&layout=edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On after payment status change logging method
	 *
	 * Method is called after payment status change.
	 *
	 * @param   array  $orderData  Holds the orders data.
	 *
	 * @return  void
	 *
	 * @since   2.2.1
	 */
	public function onAfterJGivePaymentStatusChange($orderData)
	{
		if (!$this->params->get('logActionForPaymentStatusChange', 1))
		{
			return;
		}

		$context = Factory::getApplication()->input->get('option');
		$user    = Factory::getUser();

		if ($orderData['campaign']->type == 'donation')
		{
			$action = 'donation_change_status';
			$type   = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_TYPE_DONATION';
		}

		if ($orderData['campaign']->type == 'investment')
		{
			$action = 'investment_change_status';
			$type   = 'PLG_ACTIONLOGS_JGIVE_CAMPAIGN_TYPE_INVESTMENT';
		}

		if ($orderData['donor']->user_id)
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_PAYMENT_STATUS_CHANGE';

			$message = array(
				'action'      => $action,
				'id'          => $orderData['payment']->id,
				'orderid'     => $orderData['payment']->order_id,
				'status'      => $orderData['payment']->order_status,
				'type'        => $type,
				'orderlink'   => 'index.php?option=com_jgive&view=donation&layout=default&donationid=' . $orderData['payment']->id,
				'userid'      => $user->id,
				'username'    => $user->username,
				'donorlink'   => 'index.php?option=com_users&task=user.edit&id=' . $orderData['donor']->user_id,
				'donorname'   => Factory::getUser($orderData['donor']->user_id)->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JGIVE_PAYMENT_STATUS_CHANGE_OF_GUEST';
			$message = array(
				'action'      => $action,
				'id'          => $orderData['payment']->id,
				'orderid'     => $orderData['payment']->order_id,
				'status'      => $orderData['payment']->order_status,
				'type'        => $type,
				'orderlink'   => 'index.php?option=com_jgive&view=donation&layout=default&donationid=' . $orderData['payment']->id,
				'userid'      => $user->id,
				'username'    => $user->username,
				'donorname'   => $orderData['donor']->first_name,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);
		}

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}
}
