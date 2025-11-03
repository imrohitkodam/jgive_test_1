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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

jimport('techjoomla.common');

// Load Model
BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjprivacy/models');
BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');

// Load Helper
JLoader::import('tjvendors', JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers');
JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
JLoader::import('donations', JPATH_SITE . '/components/com_jgive/helpers');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');

// Load Table
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjvendors/tables');
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jgive/tables');

/**
 * Donations form model class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveModelDonations extends ListModel
{
	protected $data;

	protected $total = null;

	protected $pagination = null;

	protected $campaignHelper;

	protected $donationsHelper;

	protected $donationModel;

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.8
	 */
	public function __construct($config = array())
	{
		$this->techjoomlacommon = new TechjoomlaCommon;

		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id','o.id',
				'order_id','o.order_id',
				'campaign_id','o.campaign_id',
				'donor_id','o.donor_id',
				'donation_id','o.donation_id',
				'cdate','o.cdate',
				'amount','o.amount',
				'original_amount','o.original_amount',
				'fee','o.fee',
				'status','o.status',
				'processor','o.processor',
				'payment_status','o.payment_status',
				'donor_type','d.donor_type',
				'first_name',
			);

			$this->orderingFields = array_merge(array(''), $config['filter_fields']);
		}

		parent::__construct($config);

		$this->donationsHelper = new DonationsHelper;
		$this->campaignHelper  = new campaignHelper;
		$this->donationModel   = BaseDatabaseModel::getInstance('Donation', 'JgiveModel');
	}

	/**
	 * Method populateState
	 *
	 * @param   String  $ordering   Ordering
	 * @param   String  $direction  Direction
	 *
	 * @return void
	 *
	 * @since	2.2.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();

		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', null, 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

		$list      = $app->getUserStateFromRequest($this->context . '.list', 'list', null, 'array');
		$ordering  = $app->input->get('filter_order', '', 'STRING');
		$direction = $app->input->get('filter_order_Dir', '', 'STRING');

		$contributorId = $app->input->get('filter_contributor_id', 0, 'INTEGER');
		$this->setState('filter.contributor_id', $contributorId);

		$donorType = $app->input->get('filter_donor_type', '', 'STRING');
		$this->setState('filter.donor_type', $donorType);

		if (!empty($ordering) && in_array($ordering, $this->orderingFields))
		{
			$this->setState('list.ordering', $ordering);
		}

		if (!empty($direction) && (!in_array(($direction), array('asc', 'desc'))))
		{
			$direction = 'asc';
		}

		$this->setState('list.direction', $direction);

		$listStart = $app->input->get('limitstart', 0, 'INT');
		$this->setState('list.start', $listStart);

		$listlimit = $app->input->get('limit', 20, 'INT');
		$this->setState('list.limit', $listlimit);

		// Layout
		$this->setState('layout', $app->input->getString('layout'));
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 *
	 * @since	2.2.0
	 */
	public function getListQuery()
	{
		$user  = Factory::getUser();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(array('o.*'));
		$query->select($db->quoteName(array('c.id', 'c.type', 'd.user_id'), array('cid', 'ctype', 'donor_id')));
		$query->select(
			$db->quoteName(
				array(
						'd.first_name', 'd.last_name', 'c.title', 'dona.comment',
						'dona.annonymous_donation','d.donor_type','d.org_name','d.user_id'
					)
				)
			);
		$query->from($db->quoteName('#__jg_orders', 'o'));
		$query->join('LEFT', $db->quoteName('#__jg_campaigns', 'c') .
			' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('o.campaign_id') . ')');
		$query->join('LEFT', $db->quoteName('#__jg_donors', 'd') .
			' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('o.donor_id') . ')');
		$query->join('LEFT', $db->quoteName('#__jg_donations', 'dona') .
			' ON (' . $db->quoteName('dona.id') . ' = ' . $db->quoteName('o.donation_id') . ')');

		$filter_campaign_id = $this->state->get('filter.campaign_id');

		if ($filter_campaign_id)
		{
			$query->where($db->quoteName('o.campaign_id') . ' = ' . (int) $filter_campaign_id);
		}

		$layout = $this->getState('layout');

		if (!empty($user->id))
		{
			if ($layout == 'default')
			{
				$query->where($db->quoteName('d.user_id') . ' = ' . (int) $user->id);
			}
			elseif ($layout == 'all_donations')
			{
				$isroot = $user->authorise('core.admin');

				if ($isroot == false)
				{
					$query->where($db->quoteName('c.creator_id') . ' = ' . (int) $user->id);
				}
			}
		}

		$donor_type = $this->getState('filter.donor_type');

		if ($donor_type)
		{
			$query->where($db->quoteName('d.donor_type') . " = " . $db->quote(trim(strtolower($donor_type))));
		}

		$paymentStatus = $this->getState('filter.status');

		if (($paymentStatus != '-1') && !empty($paymentStatus))
		{
			$query->where($db->quoteName("o.status") . " = " . $db->quote($paymentStatus));
		}

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
			$query->where('( d.first_name LIKE ' . $search .
						'  OR  d.last_name LIKE ' . $search .
						'  OR  o.order_id LIKE ' . $search .
						' OR d.org_name LIKE ' . $search .
						' )'
				);
		}

		$contributorId = $this->getState('filter.contributor_id');

		if ($contributorId)
		{
			$query->where($db->quoteName('d.contributor_id') . " = " . (int) $contributorId);
		}

		$listlimit = $this->getState('list.limit');
		$this->setState('list.limit', $listlimit);

		$orderCol = $this->getState('list.ordering');

		if (empty($orderCol))
		{
			$orderCol = 'o.id';
		}

		$orderDirn = $this->getState('list.direction', 'asc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
     * Get the filter form
     *
     * @param   array    $data      data
     * @param   boolean  $loadData  load current data
     *
     * @return  \Joomla\CMS\Form\Form|null  The Form object or null if the form can't be found
     *
     * @since   3.2
     */
    public function getFilterForm($data = [], $loadData = true)
    {
		$app = Factory::getApplication();
        $form = parent::getFilterForm($data, $loadData);
		if ($app->input->getString('layout') != 'all_donations')
		{
			$form->removeField('campaign_id', 'filter');
			$form->removeField('donor_type', 'filter');
		}

        return $form;
    }

	/**
	 * Method confirmpayment.
	 *
	 * @param   String  $pgPlugin  Plugin name.
	 * @param   Int     $orderId   Order Id.
	 *
	 * @return  boolean
	 *
	 * @since	1.8
	 */
	public function confirmpayment($pgPlugin, $orderId)
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		/* since 2.1.3
		 * Don't remove this
		 *  $post  = $input->post ; */
		$post  = $input->post->getArray(array());
		$commentPresent = array_key_exists('comment', $post);

		if ($commentPresent)
		{
			$this->saveComment($pgPlugin, $orderId, $post['comment']);
		}

		$vars = $this->getPaymentVars($pgPlugin, $orderId);

		if (!empty($post) && !empty($vars))
		{
			PluginHelper::importPlugin('payment', $pgPlugin);

			if ($vars->is_recurring == 1)
			{
				$result = $app->triggerEvent('onTP_ProcessSubmitRecurring', array($post, $vars));
			}
			else
			{
				$result = $app->triggerEvent('onTP_ProcessSubmit', array($post, $vars));
			}
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_SOME_ERROR_OCCURRED'), 'error');
		}

		return true;
	}

	/**
	 * Method getDonations.
	 *
	 * @param   String  $pg_plugin  Plugin name.
	 * @param   Int     $orderId    Order Id.
	 * @param   String  $comment    Comment.
	 *
	 * @return  void
	 *
	 * @since	1.8
	 */
	public function saveComment($pg_plugin, $orderId, $comment)
	{
		if ($orderId)
		{
			$obj   = new stdClass;
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('donation_id');
			$query->from($db->quoteName('#__jg_orders'));
			$query->where($db->quoteName('#__jg_orders.id') . ' = ' . (int) $orderId);
			$db->setQuery($query);
			$obj->id      = $db->loadResult();
			$obj->comment = $comment;

			if ($obj->id)
			{
				try
				{
					$db->updateObject('#__jg_donations', $obj, 'id');
				}
				catch (RuntimeException $e)
				{
					echo $e->getMessage();
				}
			}
		}
	}

	/**
	 * Method setSessionCampaignId.
	 *
	 * @param   Integer  $cid         CID.
	 * @param   Integer  $givebackId  GIVEN BACK ID
	 *
	 * @return  boolean
	 *
	 * @since	1.8
	 */
	public function setSessionCampaignId($cid, $givebackId = '')
	{
		$session = Factory::getSession();
		$this->clearSessionCampaignId();
		$session->set('JGIVE_cid', $cid);

		if (!empty($givebackId))
		{
			$session->set('JGIVE_giveback_id', $givebackId);
		}

		return true;
	}

	/**
	 * Method clearSessionCampaignId.
	 *
	 * @return  boolean
	 *
	 * @since	1.8
	 */
	public function clearSessionCampaignId()
	{
		$session = Factory::getSession();
		$session->set('JGIVE_cid', '');

		return true;
	}

	/**
	 * Method getCampaignId.
	 *
	 * @return  Integer cid.
	 *
	 * @since	1.8
	 */
	public function getCampaignId()
	{
		$input   = Factory::getApplication()->input;
		$session = Factory::getSession();
		$post    = $input->post->getArray();

		if (empty($post['cid']))
		{
			$cid = $session->get('JGIVE_cid');
		}
		else
		{
			$cid = $post['cid'];
		}

		if (empty($cid))
		{
			$cid = $input->get('cid', '', 'INT');
		}

		return (int) $cid;
	}

	/**
	 * This method set donor data in session.
	 *
	 * @param   Object  $post  Post
	 *
	 * @return  boolean
	 *
	 * @since	1.8
	 */
	public function setSessionDonorData($post)
	{
		$params               = ComponentHelper::getParams('com_jgive');
		$feeMode              = $params->get('fee_mode', 'inclusive', 'string');
		$exclusiveFeeOptional = $params->get('exclusive_fee_optional', '0', 'string');

		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$session             = Factory::getSession();
		$session->set('JGIVE_cid', $post->get('cid', '', 'INT'));
		$session->set('JGIVE_first_name', $post->get('first_name', '', 'STRING'));
		$session->set('JGIVE_last_name', $post->get('last_name', '', 'STRING'));
		$session->set('JGIVE_paypal_email', $post->get('paypal_email', '', 'STRING'));
		$session->set('JGIVE_address', $post->get('address', '', 'STRING'));
		$session->set('JGIVE_address2', $post->get('address2', '', 'STRING'));
		$session->set('JGIVE_city', $post->get('city', '', 'INT'));
		$session->set('JGIVE_other_city', $post->get('other_city', '', 'STRING'));
		$session->set('JGIVE_state', $post->get('state', '', 'INT'));
		$session->set('JGIVE_country', $post->get('country', '', 'INT'));
		$session->set('JGIVE_zip', $post->get('zip', '', 'ALNUM'));
		$session->set('JGIVE_phone', $post->get('phone', '', 'STRING'));
		$session->set('JGIVE_taxnumber', $post->get('taxnumber', '', 'STRING'));
		$session->set('JGIVE_pannumber', $post->get('pannumber', '', 'STRING'));

		$platformFee    = $post->get('platform_fee', '', 'FLOAT');
		$donationAmount = $post->get('donation_amount', '', 'FLOAT');

		if ($feeMode == 'exclusive')
		{
			if ((int) $exclusiveFeeOptional === 1)
			{
				if ($post->get('exclusive_platform_fee') && $post->get('exclusive_platform_fee') == 'on')
				{
					// Donor ready to pay platform fee
					$donationAmount = $donationAmount + $platformFee;
				}
				else
				{
					// Donor does not ready to pay platform fee
					$donationAmount = $post->get('donation_amount', '', 'FLOAT');
				}
			}
			else
			{
				$donationAmount = $donationAmount + $platformFee;
			}
		}

		$session->set('JGIVE_donation_amount', $jgiveFrontendHelper->getRoundedAmount($donationAmount));
		$session->set('No_first_donation', 1);
		$session->set('JGIVE_user_first_last_name', $post->get('user_first_last_name', '', 'STRING'));

		return true;
	}

	/**
	 * Method addOrder.
	 *
	 * @param   Object  $post  Post
	 *
	 * @return  boolean|Integer
	 *
	 * @since	1.8
	 */
	public function addOrder($post)
	{
		header('Set-Cookie: ' . session_name() . '=' . $_COOKIE[session_name()] . '; SameSite=None; Secure; HttpOnly');

		$db   = Factory::getDbo();
		$user = Factory::getUser();
		$userid = $user->id;

		// Validate all order data
		$validateDataStatus = $this->validateOrderData($post);

		if (!$validateDataStatus)
		{
			return false;
		}

		// Get params
		$session             = Factory::getSession();
		$cid                 = $post->get('cid', 0, 'INT');
		$params              = ComponentHelper::getParams('com_jgive');
		$sendPaymentsToOwner = $params->get('send_payments_to_owner');
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		// Get the user groupwise commision form params
		$params_usergroup     = $params->get('usergroup');
		$commission_fee       = $params->get('commission_fee', 0, 'INT');
		$fixed_commission_fee = $params->get('fixed_commissionfee', 0, 'INT');

		if ($post->get('donation_amount', '', 'FLOAT') <= 0)
		{
			return false;
		}

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaign');
		$jgiveModelCampaign = BaseDatabaseModel::getInstance('campaign', 'JGiveModel');
		$campaignData       = $jgiveModelCampaign->getItem($cid);

		$vendor_id               = $campaignData['campaign']->vendor_id;
		$vendorCommissionTable   = Table::getInstance('vendorfee', 'TjvendorsTable', array());
		$vendorCommissionTable->load(array('vendor_id' => $vendor_id, 'client' => 'com_jgive'));
		$vendorCommissionFlat    = $vendorCommissionTable->flat_commission;
		$vendorCommissionPercent = $vendorCommissionTable->percent_commission;

		if ($vendorCommissionPercent > 0 || $vendorCommissionFlat > 0)
		{
			$commission_fee = $vendorCommissionPercent;
			$fixed_commission_fee = $vendorCommissionFlat;
		}

		// Get campaign type donation/Investment & its creator
		$camp_details            = $this->campaignHelper->getCampaignType($cid);
		$campaign_creator        = Factory::getUser($camp_details->creator_id);
		$camp_creator_groups_ids = $campaign_creator->groups;

		$this->guest_donation = $params->get('guest_donation');

		if ($this->guest_donation)
		{
			if (!$userid)
			{
				$userid = 0;

				$donor_registration = $post->get('account', '', 'STRING');

				if ($donor_registration == 'register')
				{
					$regdata = array();
					$regdata['user_name']  = $post->get('paypal_email', '', 'STRING');
					$regdata['user_email'] = $post->get('paypal_email', '', 'STRING');

					$cb_params   = ComponentHelper::getParams('com_jgive');
					$integration = $cb_params->get('integration');

					if ($integration == 'cb')
					{
						$regdata['first_name'] = $post->get('first_name', '', 'STRING');
						$regdata['last_name']  = $post->get('last_name', '', 'STRING');
					}

					JLoader::import('registration', JPATH_SITE . '/components/com_jgive/models');
					$jgiveModelregistration = new jgiveModelregistration;
					$mesage                 = $jgiveModelregistration->store($regdata);

					if ($mesage)
					{
						$user   = Factory::getUser();
						$userid = $user->id;
					}
					else
					{
						return -1;
					}
				}
				elseif (!($donor_registration == 'guest'))
				{
					return false;
				}

				$session->set('quick_reg_no_login', '1');
			}
		}

		// Save donor details
		$obj            = new stdClass;
		$obj->id        = '';

		$JGIVE_order_id = $session->get('JGIVE_order_id');

		if (!empty($JGIVE_order_id))
		{
			$query = $db->getQuery(true);
			$query->select('donor_id');
			$query->from($db->qn('#__jg_orders'));
			$query->where($db->qn('#__jg_orders.id') . ' = ' . (int) $JGIVE_order_id);
			$db->setQuery($query);
			$obj->id = $db->loadResult();
		}

		$obj->user_id     = $userid;
		$obj->campaign_id = $cid;
		$obj->email       = $post->get('paypal_email', '', 'STRING');
		$obj->first_name  = $post->get('first_name', '', 'STRING');
		$obj->last_name   = $post->get('last_name', '', 'STRING');

		if (!empty($post->get('user_first_last_name', '', 'STRING')))
		{
			$obj->first_name = $post->get('user_first_last_name', '', 'STRING');
		}

		$obj->address  = $post->get('address', '', '', 'STRING');
		$obj->address2 = $post->get('address2', '', 'STRING');
		$obj->city     = "";

		$other_city_check = $post->get('other_city_check', '', 'STRING');

		if (!empty($other_city_check))
		{
			$obj->city = $post->get('other_city', '', 'STRING');
		}
		elseif ($post->get('city', '', 'STRING'))
		{
			$obj->city = $post->get('city', '', 'STRING');
		}

		$obj->country = $post->get('country', '', 'STRING');
		$obj->state   = $post->get('state', '', 'STRING');
		$obj->zip     = $post->get('zip', '', 'STRING');
		$obj->phone   = $post->get('phone', '', 'STRING');
		$obj->taxnumber = $post->get('taxnumber', '', 'STRING');
		$obj->pannumber = "";
		if($post->get('pannumber', '', 'STRING')) {
			$key = $params->get('pan_encryption_key');
			$obj->pannumber = $this->donationsHelper->encryptData($post->get('pannumber', '', 'STRING'), $key);
		}

		// Validate Donor Information
		$jgiveTableDonor = Table::getInstance('Donor', 'JGiveTable', array());
		$jgiveTableDonor->bind((array) $obj);
		$isValid         = $jgiveTableDonor->check();

		$individualContactObj = $obj;
		$individualContactObj->other_city_check = $post->get('other_city_check', '', 'STRING');

		$individualContactObj->vendor_id = $vendor_id;
		$jgiveModelIndividual = JGive::model('Individualform', array('ignore_request' => true));
		$individualDonorId = $jgiveModelIndividual->addDonorContact($individualContactObj);

		$obj->donor_type     = 'ind';
		$obj->org_name       = '';
		$obj->contributor_id = $individualDonorId;
		unset($obj->other_city_check);
		unset($obj->vendor_id);

		if (!$isValid)
		{
			return false;
		}

		if ($obj->id)
		{
			try
			{
				$db->updateObject('#__jg_donors', $obj, 'id');
			}
			catch (RuntimeException $e)
			{
				echo $e->getMessage();

				return false;
			}
		}
		else
		{
			try
			{
				$db->insertObject('#__jg_donors', $obj, 'id');
			}
			catch (RuntimeException $e)
			{
				echo $e->getMessage();

				return false;
			}
		}

		// Get last insert id
		if ($obj->id)
		{
			$donors_key = $obj->id;
		}
		else
		{
			$donors_key = $db->insertid();
		}

		$obj     = new stdClass;
		$obj->id = '';
		$obj->order_id = 0;
		$obj->comment = "";

		if ($JGIVE_order_id)
		{
			$query = $db->getQuery(true);
			$query->select('donation_id');
			$query->from($db->qn('#__jg_orders'));
			$query->where($db->qn('#__jg_orders.id') . ' = ' . (int) $JGIVE_order_id);
			$db->setQuery($query);
			$obj->id = $db->loadResult();
		}

		$obj->campaign_id         = $cid;
		$obj->donor_id            = $donors_key;
		$obj->is_recurring        = ($post->get('donation_type', 0, 'INT')) ? 1: 0;
		$obj->recurring_frequency = ($post->get('donation_type', 0, 'INT')) ? $post->get('recurring_freq', '', 'STRING') : '';
		$obj->recurring_count     = ($post->get('donation_type', 0, 'INT')) ? $post->get('recurring_count', 0, 'INT') : 0;
		$obj->annonymous_donation = $post->get('annonymousDonation', 0, 'INT');
		$no_giveback              = $post->get('no_giveback', '', 'INT');

		// Check donor not checked no giveback option
		if (!$no_giveback)
		{
			$giveback_id      = $post->get('givebacks', 0, 'INT');
			$ds_amount        = $post->get('donation_amount', '', 'FLOAT');
			$amount_separator = $params->get('amount_separator');

			if (!empty($amount_separator))
			{
				$ds_amount = str_replace($amount_separator, '.', $ds_amount);
			}

			$iscorrect = $this->validateGiveback($JGIVE_order_id, $giveback_id, $ds_amount);

			if (!$iscorrect)
			{
				return false;
			}

			$obj->giveback_id = $giveback_id;
		}
		else
		{
			$obj->giveback_id = 0;
		}

		if ($obj->id)
		{
			try
			{
				$db->updateObject('#__jg_donations', $obj, 'id');
			}
			catch (RuntimeException $e)
			{
				echo $e->getMessage();

				return false;
			}
		}
		else
		{
			try
			{
				$db->insertObject('#__jg_donations', $obj, 'id');
			}
			catch (RuntimeException $e)
			{
				echo $e->getMessage();

				return false;
			}
		}

		$donation_id = ($obj->id) ? $obj->id : $db->insertid();

		// Save order details
		$obj     = new stdClass;
		$obj->id = '';

		if ($JGIVE_order_id)
		{
			$obj->id = $JGIVE_order_id;
		}

		// Lets make a random char for this order
		// Take order prefix set by admin
		$order_prefix       = (string) $params->get('order_prefix');

		// String length should not be more than 5
		$order_prefix       = substr($order_prefix, 0, 5);

		// Take separator set by admin
		$separator          = (string) $params->get('separator');
		$obj->order_id      = $order_prefix . $separator;

		// Check if we have to add random number to order id
		$use_random_orderid = (int) $params->get('random_orderid');

		if ($use_random_orderid)
		{
			$random_numer   = $this->_random(5);
			$obj->order_id .= $random_numer . $separator;

			/*This length shud be such that it matches the column lenth of primary key
			It is used to add pading
			order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number*/
			$len = (23 - 5 - 2 - 5);
		}
		else
		{
			/*This length shud be such that it matches the column lenth of primary key
			It is used to add pading
			order_id_column_field_length - prefix_length - no_of_underscores*/
			$len = (23 - 5 - 2);
		}

		$obj->campaign_id    = $cid;
		$obj->donor_id       = $donors_key;
		$obj->donation_id    = $donation_id;
		$obj->cdate          = Factory::getDate()->Format(Text::_('Y-m-d H:i:s'));
		$obj->mdate          = Factory::getDate()->Format(Text::_('Y-m-d H:i:s'));
		$obj->transaction_id = '';

		$amount_separator     = $params->get('amount_separator');
		$feeMode              = $params->get('fee_mode', 'inclusive', 'string');
		$exclusiveFeeOptional = $params->get('exclusive_fee_optional', '0', 'string');
		$platformFee          = $post->get('platform_fee', '', 'FLOAT');
		$donationAmount       = $post->get('donation_amount', '', 'FLOAT');

		// Inclusive Commission
		$originalDonationAmount = $donationAmount;
		$amount                 = $donationAmount;

		// Exclusive Commission
		if ($feeMode == 'exclusive')
		{
			// Exclusive Commission compulsory
			$originalDonationAmount = $donationAmount + $platformFee;
			$amount                 = $donationAmount;

			// Exclusive Commission optional
			if ((int) $exclusiveFeeOptional === 1)
			{
				$exclusivePlatformFeeCheck = $post->get('exclusive_platform_fee');

				if (isset($exclusivePlatformFeeCheck) && $exclusivePlatformFeeCheck == 'on')
				{
					// Donor has wish to pay platform fee
					$originalDonationAmount = $donationAmount + $platformFee;
					$amount                 = $donationAmount;
				}
				else
				{
					// Donor does not wish to pay platform fee
					$originalDonationAmount = $donationAmount;
					$amount                 = $donationAmount;
				}
			}
		}

		$originalDonationAmount  = $jgiveFrontendHelper->getRoundedAmount($originalDonationAmount);
		$amount                  = $jgiveFrontendHelper->getRoundedAmount($amount);

		if (!empty($amount_separator))
		{
			$originalDonationAmount = str_replace($amount_separator, '.', $originalDonationAmount);
			$amount                 = str_replace($amount_separator, '.', $amount);
		}

		$obj->original_amount = $originalDonationAmount;
		$obj->amount          = $amount;
		$obj->fee             = 0;

		if (!$sendPaymentsToOwner)
		{
			if (!empty($params_usergroup))
			{
				$count = count($params_usergroup);

				for ($l = 0; $l < $count; $l = $l + 3)
				{
					if (in_array($params_usergroup[$l], $camp_creator_groups_ids))
					{
						if ($camp_details->type == 'donation')
						{
							$commission_fee = (int) ($params_usergroup[$l + 1]);
						}
						elseif ($camp_details->type == 'investment')
						{
							$commission_fee = (int) ($params_usergroup[$l + 2]);
						}

						break;
					}
				}
			}

			$obj->fee = JGive::utilities()->getRoundedAmount((($obj->amount * $commission_fee) / 100) + $fixed_commission_fee);

			if ($feeMode == 'exclusive')
			{
				if ($exclusiveFeeOptional == '1')
				{
					if ($post->get('exclusive_platform_fee') && $post->get('exclusive_platform_fee') == 'on')
					{
						$paidPlatformFee = true;
					}
					else
					{
						$obj->fee = 0;
						$paidPlatformFee = false;
					}
				}
				else
				{
					$paidPlatformFee = true;
				}
			}
			else
			{
				$paidPlatformFee = true;
			}

			if ($feeMode == 'exclusive' && $obj->fee != $platformFee)
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_REQUES'));

				return false;
			}

			$commissionConfig = array();
			$commissionConfig['fee_mode'] = $feeMode;
			$commissionConfig['exclusive_fee_optional'] = $exclusiveFeeOptional;
			$commissionConfig['paid_platform_fee'] = $paidPlatformFee;
			$commissionConfig['currency']            = $params->get('currency');
			$commissionConfig['commission_fee']      = $commission_fee;
			$commissionConfig['fixed_commissionfee'] = $fixed_commission_fee;

			$obj->params = json_encode($commissionConfig);
		}

		$obj->fund_holder = 0;
		$obj->vat_number  = $post->get('vat_number', '', 'STRING');

		if ($sendPaymentsToOwner)
		{
			// Money for this order will goto campaign promoters account
			$obj->fund_holder = 1;
		}

		// By default pending status
		$obj->status    = 'P';
		$obj->processor = $post->get('gateways', '', 'STRING');
		$obj->payment_received_date = "0000-00-00 00:00:00";

		// Get the IP Address
		if (!empty($_SERVER['REMOTE_ADDR']))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$ip = 'unknown';
		}

		$obj->ip_address = $ip;

		if ($obj->id)
		{
			try
			{
				$db->updateObject('#__jg_orders', $obj, 'id');
			}
			catch (RuntimeException $e)
			{
				echo $e->getMessage();
			}
		}
		else
		{
			try
			{
				$db->insertObject('#__jg_orders', $obj, 'id');
			}
			catch (RuntimeException $e)
			{
				echo $e->getMessage();
			}
		}

		// Get last insert id
		if ($JGIVE_order_id)
		{
			$ordersKey = $JGIVE_order_id;
		}
		else
		{
			$ordersKey = $db->insertid();
		}

		// Set order id in session
		$session = Factory::getSession();
		$session->set('JGIVE_order_id', $ordersKey);

		$db->setQuery('SELECT order_id FROM #__jg_orders WHERE id=' . $ordersKey);
		$order_id      = (string) $db->loadResult();
		$maxlen        = 23 - strlen($order_id) - strlen($ordersKey);
		$padding_count = (int) $params->get('padding_count');

		// Use padding length set by admin only if it is les than allowed(calculate) length
		if ($padding_count > $maxlen)
		{
			$padding_count = $maxlen;
		}

		if (strlen((string) $ordersKey) <= $len)
		{
			$append = '';

			for ($z = 0; $z < $padding_count; $z++)
			{
				$append .= '0';
			}

			$append = $append . $ordersKey;
		}

		$res      = new stdClass;
		$res->id  = $ordersKey;

		// Imp
		$order_id = $res->order_id = $order_id . $append;

		if (!$db->updateObject('#__jg_orders', $res, 'id'))
		{
		}

		if (!empty($params->get('terms_condition', 0)) && !empty($params->get('payment_terms_article', 0)))
		{
			// Save User Privacy Terms and conditions Data
			$userPrivacyData = array();
			$privacyTermsCondition = $post->get('terms_condition', '', 'STRING');
			$userPrivacyData['client'] = 'com_jgive.donation';
			$userPrivacyData['client_id'] = $res->id;
			$userPrivacyData['user_id'] = $user->id?$user->id:0;
			$userPrivacyData['purpose'] = Text::_('COM_JGIVE_USER_PRIVACY_TERMS_PURPOSE_FOR_DONATION');
			$userPrivacyData['accepted'] = isset($privacyTermsCondition)?1:0;
			$userPrivacyData['date'] = Factory::getDate('now')->toSQL();

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjprivacy/models', 'tjprivacy');
			$tjprivacyModelObj = BaseDatabaseModel::getInstance('tjprivacy', 'TjprivacyModel');
			$tjprivacyModelObj->save($userPrivacyData);
		}

		$donationDetails = $this->donationModel->getItem($ordersKey);

		JLoader::import('components.com_jgive.events.donation', JPATH_SITE);
		$jGiveTriggerDonation = new JGiveTriggerDonation;
		$jGiveTriggerDonation->onDonationAfterSave($donationDetails, true);

		$donationDetails['isadmin'] = 0;
		PluginHelper::importPlugin('jgive');
		PluginHelper::importPlugin('actionlog');
		Factory::getApplication()->triggerEvent('onAfterJGPaymentStatusProcess', array($donationDetails));

		return true;
	}

	/**
	 * Method getReceiverList.
	 *
	 * @param   String   $pg_plugin  PLUGIN Name
	 * @param   Integer  $tid        Order ID Primary Key.
	 *
	 * @return  Object vars.
	 *
	 * @since	1.8
	 */
	public function getPaymentVars($pg_plugin, $tid)
	{
		$vars                = new stdclass;
		$params              = ComponentHelper::getParams('com_jgive');
		$currency_code       = $params->get('currency');
		$sendPaymentsToOwner = $params->get('send_payments_to_owner');
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		require_once JPATH_SITE . "/components/com_jgive/helpers/campaign.php";
		$pass_data      = $this->getdetails($tid);
		$vars->order_id = $pass_data->order_id;

		// Get the campaign promoter paypal email id
		$CampaignPromoterPaypalId = $this->campaignHelper->getCampaignPromoterPaypalId($tid, $pg_plugin);

		$session = Factory::getSession();
		$session->set('order_id', $tid);
		$vars->client = 'com_jgive';

		if ($pg_plugin == 'paypal')
		{
			// Lets set the paypal email if admin is not handling transactions
			if ($sendPaymentsToOwner)
			{
				$vars->business = $this->campaignHelper->getCampaignPromoterPaypalId($tid, $pg_plugin);
			}

			// In case of donations
			if ($pass_data->is_recurring == 1)
			{
				$vars->cmd = '_xclick-subscriptions';
			}
			elseif (($pass_data->type == 'donation'))
			{
				$vars->cmd = '_donations';
			}
		}

		$vars->user_firstname = $pass_data->first_name;
		$vars->user_lastname  = $pass_data->last_name;
		$vars->address        = $pass_data->address;
		$vars->address2       = $pass_data->address2;
		$vars->zipcode        = $pass_data->zip;
		$vars->contactNumber  = $pass_data->phone;
		$vars->countryName    = $jgiveFrontendHelper->getCountryNameFromId($pass_data->country);
		$vars->stateName      = $jgiveFrontendHelper->getRegionNameFromId($pass_data->state, $pass_data->country);
		$vars->cityName       = $pass_data->city;

		if (is_numeric($pass_data->city))
		{
			$vars->cityName = $jgiveFrontendHelper->getCityNameFromId($pass_data->city, $pass_data->country);
		}

		$vars->user_id        = Factory::getUser()->id;
		$vars->user_email     = $pass_data->email;
		$this->guest_donation = $params->get('guest_donation');
		$guest_email          = '';

		if ($this->guest_donation)
		{
			if (!$vars->user_id)
			{
				$vars->user_id = 0;
				$session       = Factory::getSession();
				$session->set('quick_reg_no_login', '1');
				$guest_email = '';
				$guest_email = md5($vars->user_email);
				$session     = Factory::getSession();
				$session->set('guest_email', $guest_email);
			}
		}

		$vars->item_name = $this->campaignHelper->getCampaignTitle($tid);

		// Added for payment description.
		$orderData = Table::getInstance('Orders', 'JGiveTable');
		$orderData->load(array('id' => $tid));
		$cid = $orderData->campaign_id;

		$donationsItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=default');

		$link = '<a href="' . Uri::root() . substr(
		Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $cid), strlen(Uri::base(true)) + 1
		) . '">' . $vars->item_name . '</a>';

		$vars->payment_description = Text::sprintf('COM_JGIVE_PAYMENT_DESCRIPTION', $link);
		$vars->submiturl = Route::_("index.php?option=com_jgive&task=donations.confirmpayment&Itemid=". $donationsItemId ."&processor={$pg_plugin}&order_id=" . $vars->order_id);
		$vars->return = Route::_(
									Uri::root() . "index.php?option=com_jgive&view=donation&Itemid=". $donationsItemId . "&donationid=" .
									$pass_data->id . "&processor=" . $pg_plugin . "&email=" . $guest_email
								);

		$vars->cancel_return = Route::_(
		Uri::root() . "index.php?option=com_jgive&view=donation&Itemid=". $donationsItemId . "&donationid=" .
		$pass_data->id . "&processor=" . $pg_plugin . "&email=" . $guest_email
		);

		$vars->url = Route::_(
		Uri::root() . "index.php?option=com_jgive&task=donations.processPayment&Itemid=". $donationsItemId . "&processor={$pg_plugin}&order_id=" . $vars->order_id
		);

		$vars->notify_url = Route::_(
		Uri::root() . "index.php?option=com_jgive&task=donations.notify&Itemid=". $donationsItemId . "&processor={$pg_plugin}&order_id=" . $vars->order_id
		);

		$vars->campaign_promoter = $CampaignPromoterPaypalId;
		$vars->currency_code     = $currency_code;

		$amount_separator = $params->get('amount_separator');

		if (!empty($amount_separator))
		{
			$donation_amount = $session->get('JGIVE_donation_amount', 0);
			$donation_amount = str_replace($amount_separator, '.', $donation_amount);
			$vars->amount    = $jgiveFrontendHelper->getRoundedAmount($donation_amount);
		}
		else
		{
			$vars->amount    = $jgiveFrontendHelper->getRoundedAmount($session->get('JGIVE_donation_amount', 0));
		}

		// In case amount is not set in session
		if (empty($vars->amount))
		{
			$vars->amount = $jgiveFrontendHelper->getRoundedAmount($pass_data->amount);
		}

		$vars->is_recurring        = $pass_data->is_recurring;
		$vars->recurring_frequency = $pass_data->recurring_frequency;
		$vars->recurring_count     = $pass_data->recurring_count;

		// For language specific paypal
		$user_data          = Factory::getUser();
		$vars->country_code = '';
		$user_language      = $user_data->getParam('language');

		if (!empty($user_language)) // User language available in db
		{
			$user_language      = str_replace('-', '_', $user_language);
			$vars->country_code = $user_language;
		}
		else // Pass location if language not available for user in db
		{
			$vars->country_code = $pass_data->country_code;
		}

		if ($pg_plugin == 'ccavenue')
		{
			$vars->userInfo = [];
			$vars->userInfo['add_line1'] = $vars->address;
			$vars->userInfo['add_line2'] = $vars->address2;
			$vars->userInfo['country_code'] = $vars->countryName;
			$vars->userInfo['state_code'] = $vars->stateName;
			$vars->userInfo['city'] = $vars->cityName;
			$vars->userInfo['zipcode'] = $vars->zipcode;
			$vars->userInfo['phone'] = $vars->contactNumber;
		}

		$vars->adaptiveReceiverList = $this->getReceiverList($vars, $pg_plugin);

		return $vars;
	}

	/**
	 * Method getReceiverList.
	 *
	 * @param   Object  $vars      Vars.
	 * @param   String  $pgPlugin  PG PLUGIN.
	 *
	 * @return  Array receiverList.
	 *
	 * @since	1.8
	 */
	public function getReceiverList($vars, $pgPlugin)
	{
		// GET BUSINESS EMAIL
		$plugin           = PluginHelper::getPlugin('payment', $pgPlugin);
		$pluginParams     = json_decode($plugin->params);
		$businessPayEmial = "";

		if (property_exists($pluginParams, 'business'))
		{
			$businessPayEmial = trim($pluginParams->business);
		}
		else
		{
			return array();
		}

		$params = ComponentHelper::getParams('com_jgive');
		$paymentsToOwnerWithoutApplyCommission = $params->get('send_payments_to_owner', 0);

		if ($pgPlugin == 'adaptive_paypal')
		{
			// Send payment to campaign promoter without charging any commision

			if ($paymentsToOwnerWithoutApplyCommission)
			{
				$AmountToPayToPromoter = $vars->amount;
			}
			else
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->qn('fee'));
				$query->from($db->qn('#__jg_orders'));
				$query->where($db->qn('order_id') . ' = ' . $db->quote($vars->order_id));
				$db->setQuery($query);
				$fee = $db->loadResult();
				$AmountToPayToPromoter = $vars->amount - $fee;
			}

			// Get site admin paypal bussiness account email address
			$plugin                         = PluginHelper::getPlugin('payment', $pgPlugin);
			$pluginParams                   = json_decode($plugin->params);
			$siteAdminBussineessAcountEmail = "";

			if (property_exists($pluginParams, 'business'))
			{
				$siteAdminBussineessAcountEmail = trim($pluginParams->business);
			}

			$receiverList                = array();
			$receiverList[0]             = array();
			$receiverList[1]             = array();

			// Admin has his own products
			$receiverList[0]['receiver'] = $siteAdminBussineessAcountEmail;
			$receiverList[0]['amount']   = $fee;
			$receiverList[0]['primary']  = false;

			// Add other receivers
			$receiverList[1]['receiver'] = $vars->campaign_promoter;
			$receiverList[1]['amount']   = $AmountToPayToPromoter;
			$receiverList[1]['primary']  = true;

			return $receiverList;
		}

		return;
	}

	/**
	 * Method getHTML.
	 *
	 * @param   String   $pgPlugin  PG PLUGIN.
	 * @param   Integer  $tid       Id.
	 *
	 * @return  Array html.
	 *
	 * @since	1.8
	 */
	public function getHTML($pgPlugin, $tid)
	{
		$vars       = $this->getPaymentVars($pgPlugin, $tid);
		PluginHelper::importPlugin('payment', $pgPlugin);
		$html = Factory::getApplication()->triggerEvent('onTP_GetHTML', array($vars));

		return $html;
	}

	/**
	 * This method return donation order details.
	 *
	 * @param   Integer  $orderId  Order Id.
	 *
	 * @return  Object orderdetails.
	 *
	 * @since	1.8
	 */
	public function getdetails($orderId)
	{
		$userId = Factory::getUser()->id;
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$query->select(
			$db->qn(
				array(
					'o.id', 'o.order_id','o.amount', 'o.original_amount', 'd.first_name', 'd.last_name', 'd.address', 'd.address2',
					'd.zip', 'd.email', 'd.phone', 'd.city', 'd.state', 'd.country', 'ds.is_recurring',
					'ds.recurring_frequency', 'ds.recurring_count', 'country.country_code', 'camp.type'
				)
			)
		);
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_donors', 'd') . ' ON (' . $db->qn('d.id') . ' = ' . $db->qn('o.donor_id') . ')');
		$query->join('LEFT', $db->qn('#__jg_donations', 'ds') . ' ON (' . $db->qn('ds.id') . ' = ' . $db->qn('o.donation_id') . ')');
		$query->join('LEFT', $db->qn('#__tj_country', 'country') . ' ON (' . $db->qn('country.country') . ' = ' . $db->qn('d.country') . ')');
		$query->join('LEFT', $db->qn('#__jg_campaigns', 'camp') . ' ON (' . $db->qn('camp.id') . ' = ' . $db->qn('o.campaign_id') . ')');
		$query->where($db->quoteName('o.id') . ' = ' . (int) $orderId);

		if ($userId)
		{
			$query->where($db->quoteName('d.user_id') . ' = ' . (int) $userId);
		}

		$db->setQuery($query);
		$orderDetails = $db->loadObjectlist();

		return $orderDetails['0'];
	}

	/**
	 * Method processPayment.
	 *
	 * @param   Array    $post       Post.
	 * @param   Array    $pg_plugin  Data.
	 * @param   Integer  $order_id   Order Id.
	 *
	 * @return  Array|boolean  payment response, false if payment fails
	 *
	 * @since	1.8
	 */
	public function processPayment($post, $pg_plugin, $order_id)
	{
		// Get id for orders table using order_id
		$order_id_key = $this->donationsHelper->getOrderIdKeyFromOrderId($order_id);

		$return_resp = array();

		if (empty($order_id_key))
		{
			return false;
		}

		$comment_present = array_key_exists('comment', $post);

		if ($comment_present)
		{
			$this->saveComment($pg_plugin, $order_id_key, $post['comment']);
		}

		$db = Factory::getDbo();
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$session             = Factory::getSession();
		$guest_email         = '';
		$guest_email         = $session->get('guest_email');
		$session->clear('guest_email');
		$session->set('order_link_guestemail', $guest_email);

		// Load donations helper

		$app   = Factory::getApplication();
		$input = $app->input;
		$input->set('remote', 1);
		$donationItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations');

		// Authorise Post Data
		if ($post['plugin_payment_method'] == 'onsite')
		{
			$plugin_payment_method = $post['plugin_payment_method'];
		}

		// Trigger payment plugins- onTP_Processpayment
		PluginHelper::importPlugin('payment', $pg_plugin);
		$vars = $this->getPaymentVars($pg_plugin, $order_id_key);

		try
		{
			$data = $app->triggerEvent('onTP_Processpayment', array($post, $vars));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$data = $data[0];

		if ($data)
		{
			try
			{
				// Store log
				$res = @$this->storelog($pg_plugin, $data);

				// Get order id
				if (empty($order_id))
				{
					$order_id = $data['order_id'];
				}

				// Get id for orders table using order_id
				$order_id_key = $this->donationsHelper->getOrderIdKeyFromOrderId($order_id);

				$orderDetails = Table::getInstance('Orders', 'JgiveTable');
				$orderDetails->load(array('id' => $order_id_key));

				// Check is repetative same response from paypal & if yes then don't send email notification
				$duplicate_response = 0;

				if ($orderDetails)
				{
					if (($orderDetails->status == $data['status']) AND ($orderDetails->transaction_id == $data['transaction_id']))
					{
						$duplicate_response = 1;
					}
				}

				// Gateway used
				$data['processor'] = $pg_plugin;

				// Payment status
				$data['status']    = trim($data['status']);

				// Get order amount
				$order_amount = $orderDetails->original_amount;

				// Return url
				$return_resp['return'] = $data['return'];

				// If payment status is confirmed
				if ($data['status'] == 'C' && $order_amount == $data['total_paid_amt'])
				{
					$this->updateOrder($data);

					// Update order status, send email,
					$this->donationsHelper->updatestatus($order_id_key, $data['status'], $comment = '', $notify_chk = 1, $duplicate_response);

					if ($duplicate_response == 0)
					{
						$donationDetails = $this->donationModel->getItem($order_id_key);

						JLoader::import('components.com_jgive.events.donation', JPATH_SITE);
						$jGiveTriggerDonation = new JGiveTriggerDonation;
						$jGiveTriggerDonation->onDonationAfterSave($donationDetails, false);
					}

					if ($data['status'] == 'C')
					{
						$this->donationsHelper->getSoldGivebacks($order_id_key);
					}

					// Trigger plugins
					// onAfterJGivePaymentSuccess
					PluginHelper::importPlugin('system');
					$result = $app->triggerEvent('onAfterJGivePaymentSuccess', array($order_id_key));

					if ($result === false)
					{
					}
					// END plugin triggers

					// Added guest email in url for payment processs on site
					$return_resp['return'] = Uri::root() . substr(
						Route::_("index.php?option=com_jgive&view=donation&donationid=" .
							$order_id_key . "&processor={$pg_plugin}&email=" . $guest_email . "&Itemid=" .
							$donationItemid, false
						), strlen(Uri::base(true)) + 1
					);
					$return_resp['status'] = '1';
				}
				elseif ($order_amount != $data['total_paid_amt'])
				{
					$data['status']        = 'E';
					$return_resp['status'] = '0';
					$return_resp['return'] = Uri::root() . substr(
						Route::_("index.php?option=com_jgive&view=donation&donationid=" .
						$order_id_key . "&processor={$pg_plugin}&email=" . $guest_email .
						"&Itemid=" . $donationItemid, false
						), strlen(Uri::base(true)) + 1
					);
				}
				elseif (!empty($data['status']))
				{
					// Added guest email in url for payment processs on site
					if ($plugin_payment_method && $data['status'] == 'P')
					{
						$return_resp['return'] = Uri::root() . substr(
						Route::_("index.php?option=com_jgive&view=donation&donationid=" .
						$order_id_key . "&processor={$pg_plugin}&email=" . $guest_email .
						"&Itemid=" . $donationItemid, false
						), strlen(Uri::base(true)) + 1
						);
					}

					if ($data['status'] != 'C')
					{
						$this->updateOrder($data);
					}
					elseif ($data['status'] == 'C')
					{
						// Added guest email in url for payment processs on site
						$return_resp['return'] = Uri::root() . substr(
						Route::_(
						"index.php?option=com_jgive&view=donations&layout=cancel&processor={$pg_plugin}&email=" . $guest_email . "&Itemid=" . $chkoutItemid
						), strlen(Uri::base(true)) + 1
						);
					}

					$return_resp['status'] = '0';
					$return_resp['return'] = Uri::root() . substr(
						Route::_("index.php?option=com_jgive&view=donation&donationid=" .
						$order_id_key . "&processor={$pg_plugin}&email=" . $guest_email .
						"&Itemid=" . $donationItemid, false
						), strlen(Uri::base(true)) + 1
					);

					// TODO where is this  used ???
					$res = new stdClass();
					$res->processor        = $data['processor'];
					$return_resp['msg'] = (!empty($data['error'])) ? $data['error']['code'] . " " . $data['error']['desc'] : '';
				}

				$res = trim($return_resp['msg']);

				// Update campaign success status.
				$this->campaignHelper->updateCampaignSuccessStatus($cid = 0, $campaignSuccessStatus = null, (int) $order_id_key);

				// Update campaign processed flag.
				$this->campaignHelper->updateCampaignProcessedFlag($cid = 0, $campaignProcessedFlag = null, (int) $order_id_key);

				$orderData = Table::getInstance('Orders', 'JGiveTable');
				$orderData->load(array('id' => $order_id_key));
				$cid = $orderData->campaign_id;

				$campaignData = Table::getInstance('Campaign', 'JGiveTable');
				$campaignData->load(array('id' => $cid));

				if (!$res && ($pg_plugin == 'bycheck' || $pg_plugin == 'byorder'))
				{
					if ($campaignData->type == 'donation')
					{
						$return_resp['msg'] = Text::_('COM_JGIVE_ORDER_PLACED_NOTIFICATION');
					}
					elseif ($campaignData->type == 'investment')
					{
						$return_resp['msg'] = Text::_('COM_JGIVE_INVESTMENT_ORDER_PLACED_NOTIFICATION');
					}
				}

				if ($cid)
				{
					$donationDetails = $this->donationModel->getItem($order_id_key);

					/* Check if goal amount has reached for first time, then only send an email */
					/* Proceed only if payment status is completed */
					if ($donationDetails['payment']->status === "C")
					{
						/* Check if total amount received(including current order payment) by the campaign is greater than goal amount */
						if ((float) $donationDetails['campaign']->amount_received >= (float) $donationDetails['campaign']->goal_amount)
						{
							$previousReceivedAmount = $donationDetails['campaign']->amount_received - $donationDetails['payment']->amount;

							/* Previous received amount (excluding current) is less than goal amount,
							 * This ensures campaign goal amount has reached for first time.
							 * */
							if ((float) $previousReceivedAmount <= (float) $donationDetails['campaign']->goal_amount)
							{
								$campaignDetails = Table::getInstance('campaign', 'JgiveTable');
								$campaignDetails->load(array('id' => $cid));

								JLoader::import('components.com_jgive.events.campaign', JPATH_SITE);
								$jGiveTriggerCampaign = new JGiveTriggerCampaign;
								$jGiveTriggerCampaign->goalAmountReachedFirstTime($campaignDetails);
							}
						}
					}
				}
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}
		else
		{
			$return_resp['msg'] = Text::_('COM_JGIVE_ORDER_ERROR');
		}

		return $return_resp;
	}

	/**
	 * Method storelog.
	 *
	 * @param   String  $name  Name.
	 * @param   Array   $data  Data.
	 *
	 * @return  Object data.
	 *
	 * @since	1.8
	 */
	public function storelog($name, $data)
	{
		$data1              = array();
		$data1['raw_data']  = $data['raw_data'];
		$data1['JT_CLIENT'] = "com_jgive";
		PluginHelper::importPlugin('payment', $name);
		$data = Factory::getApplication()->triggerEvent('onTP_Storelog', array($data1));
	}

	/**
	 * Method updateOrder.
	 *
	 * @param   Array  $data  Data.
	 *
	 * @return  void
	 *
	 * @since	1.8
	 */
	public function updateOrder($data)
	{
		$db = Factory::getDbo();

		// Get id for orders table using order_id
		$order_id_key = $this->donationsHelper->getOrderIdKeyFromOrderId($data['order_id']);

		// Get donation id
		$query = $db->getQuery(true);
		$query->select('donation_id');
		$query->from($db->qn('#__jg_orders'));
		$query->where($db->qn('#__jg_orders.id') . ' = ' . (int) $order_id_key);
		$db->setQuery($query);
		$donation_id = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('subscr_id');
		$query->select('is_recurring');
		$query->from($db->qn('#__jg_donations'));
		$query->where($db->qn('#__jg_donations.id') . ' = ' . (int) $donation_id);
		$db->setQuery($query);
		$donation_details = $db->loadObject();

		// In case of Paypal the order is autoconfirmed
		// If status is confirmed then auto credit entry in TJVendors
		if ($data['status'] == 'C')
		{
			$orderClass    = JGive::order($order_id_key);
			$campaignId    = $orderClass->getCampaignId();
			$campaignClass = JGive::campaign($campaignId);

			$orderDetails = array();
			$orderDetails['vendor_id']        = $campaignClass->getVendorId();
			$orderDetails['status']           = $data['status'];
			$orderDetails['client']           = "com_jgive";
			$orderDetails['client_name']      = Text::_('COM_JGIVE');
			$orderDetails['order_id']         = $order_id_key;
			$orderDetails['amount']           = $orderClass->getOriginalAmount();
			$orderDetails['customer_note']    = "";
			$orderDetails['fee_amount']       = $orderClass->getFee();
			$orderDetails['transaction_time'] = $orderClass->getCreatedDate();
			$tjvendorFrontHelper = new TjvendorFrontHelper;
			$tjvendorFrontHelper->addEntry($orderDetails);
		}

		// If subscriber id is not exist then it is first response from paypal
		// Hence update donation table insert subsriber id & also update transaction id

		if ($donation_details->is_recurring)
		{
			// For recurring payment
			if ($data['txn_type'] == 'subscr_payment')
			{
				// Insert subscriber id in donation table if not exits (First response of Recurring donations)
				if (empty($donation_details->subscr_id))
				{
					$res            = new stdClass;
					$res->id        = $donation_id;
					$res->subscr_id = $data['subscriber_id'];

					if (!$db->updateObject('#__jg_donations', $res, 'id'))
					{
					}

					// Update First order status & transaction id
					$res                 = new stdClass;
					$res->id             = $order_id_key;
					$res->mdate          = date("Y-m-d H:i:s");
					$res->transaction_id = $data['transaction_id'];
					$res->status         = $data['status'];
					$res->processor      = $data['processor'];
					$res->extra          = json_encode($data['raw_data']);

					if (!$db->updateObject('#__jg_orders', $res, 'id'))
					{
					}
				}
				else // For recurring payment for more responses other than first
				{
					// Check the transaction id is present in the order table
					// Get transaction id
					$query = $db->getQuery(true);
					$query->select('transaction_id');
					$query->from($db->qn('#__jg_orders'));
					$query->where($db->qn('#__jg_orders.donation_id') . ' = ' . (int) $donation_id);
					$db->setQuery($query);
					$transaction_ids = $db->loadColumn();

					// Check is transaction id exist in array
					if ($transaction_ids[0])
					{
						$flag = 0;

						for ($i = 0; $i < count($transaction_ids); $i++)
						{
							// If same transaction id then update the order
							if ($transaction_ids[$i] == $data['transaction_id'])
							{
								// Transaction id already in table
								$flag = 1;
								break;
							}
						}

						// New transaction
						if ($flag == 0)
						{
							// Order_id campaign_id  donor_id donation_id fund_holder cdate mdate
							// Transaction_id transaction_id original_amount amount  fee  status processor ip_address extra
							$this->_addNewRecurringOrder($data, $order_id_key);
						}
						else
						{
							$res                 = new stdClass;
							$res->mdate          = date("Y-m-d H:i:s");
							$res->transaction_id = $data['transaction_id'];
							$res->status         = $data['status'];
							$res->processor      = $data['processor'];
							$res->extra          = json_encode($data['raw_data']);

							if (!$db->updateObject('#__jg_orders', $res, 'transaction_id'))
							{
							}
						}
					}
				}
			}

			// Check that user subscription is expired
			if ($data['processor'] == 'stripe')
			{
				$this->_CheckToCancelSubscriptions($donation_id, $data, $order_id_key);
			}
		}
		else
		{
			$res                 = new stdClass;
			$res->id             = $order_id_key;
			$res->mdate          = date("Y-m-d H:i:s");
			$res->transaction_id = $data['transaction_id'];
			$res->status         = $data['status'];
			$res->processor      = $data['processor'];
			$res->extra          = json_encode($data['raw_data']);

			if (!$db->updateObject('#__jg_orders', $res, 'id'))
			{
			}

			// For adaptive payment add entry in payout report
			if (isset($data['txn_type']) && $data['txn_type'] == 'Adaptive Payment PAY')
			{
				$this->addPayoutEntry($data['order_id'], $data['transaction_id'], $data['status'], $data['processor']);
			}
		}
	}

	/**
	 * Update payout entry
	 *
	 * @param   integer  $orderId   id for jgive order
	 * @param   string   $txnid     txnid
	 * @param   array    $status    status
	 * @param   string   $pgPlugin  name of plugin
	 *
	 * @return  void
	 *
	 * @since   1.8
	 */
	public function addPayoutEntry($orderId, $txnid, $status, $pgPlugin)
	{
		$plugin              = PluginHelper::getPlugin('payment', $pgPlugin);
		$params              = ComponentHelper::getParams('com_jgive');
		$sendPaymentsToOwner = $params->get('send_payments_to_owner');

		if ($pgPlugin == 'adaptive_paypal' || ($pgPlugin == 'paypal' && $sendPaymentsToOwner == 1))
		{
			$donationInfo           = $this->donationModel->getItem($orderId);
			$emailDetails           = $this->campaignHelper->getCampaignPromoterPaypalId((int) $orderId, $pgPlugin);
			$com_params             = ComponentHelper::getParams('com_jgive');
			$currency               = $com_params->get('currency');
			$vendor_id              = $this->campaignHelper->getOrderVendorId($orderId);
			$newPayoutData          = array();
			$newPayoutData['debit'] = $donationInfo['payment']->amount - $donationInfo['payment']->fee;
			$tjvendorsHelper                   = new TjvendorsHelper;
			$payableAmount                     = $tjvendorsHelper->getTotalAmount($vendor_id, $currency, 'com_jgive');
			$newPayoutData['total']            = $payableAmount['total'];
			$newPayoutData['transaction_time'] = Factory::getDate()->toSql();
			$newPayoutData['client']           = 'com_jgive';
			$newPayoutData['currency']         = $currency;
			$transactionClient                 = "CMP";
			$newPayoutData['transaction_id']   = $transactionClient . '-' . $currency . '-' . $vendor_id . '-';
			$newPayoutData['id']               = '';
			$newPayoutData['vendor_id']        = $vendor_id;
			$newPayoutData['status']           = 1;
			$newPayoutData['adaptive_payout']  = 1;
			$newPayoutData['credit']           = '0.00';
			$customerNote = Text::_("COM_JGIVE_DIRECT_PAYMENT_VENDOR_ADAPTIVE_PAYPAL");

			if ($pgPlugin == 'paypal')
			{
				$customerNote = Text::_("COM_JGIVE_DIRECT_PAYMENT_VENDOR_PAYPAL");
			}

			$params                  = array("customer_note" => $customerNote, "entry_status" => "debit_payout");
			$newPayoutData['params'] = json_encode($params);
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'payout');
			$tjvendorsModelPayout    = BaseDatabaseModel::getInstance('Payout', 'TjvendorsModel');
			$tjvendorsModelPayout->save($newPayoutData);
		}
	}

	/**
	 * Method _addNewRecurringOrder.
	 *
	 * @param   Array    $data          Data  .
	 * @param   Integer  $order_id_key  Order Id Key  .
	 *
	 * @return  void|String
	 *
	 * @since	1.8
	 */
	public function _addNewRecurringOrder($data, $order_id_key)
	{
		$donationInfo  = $this->donationModel->getItem($order_id_key);

		/*Order_id campaign_id  donor_id donation_id fund_holder cdate mdate
		Transaction_id transaction_id original_amount amount  fee  status processor ip_address extra
		Save order details*/
		$db              = Factory::getDbo();
		$obj             = new stdClass;
		$obj->id         = '';

		// Lets make a random char for this order
		// Take order prefix set by admin
		$obj->id            = '';
		$params             = ComponentHelper::getParams('com_jgive');
		$order_prefix       = (string) $params->get('order_prefix');

		// String length should not be more than 5
		$order_prefix       = substr($order_prefix, 0, 5);

		// Take separator set by admin
		$separator          = (string) $params->get('separator');
		$obj->order_id      = $order_prefix . $separator;

		// Check if we have to add random number to order id
		$use_random_orderid = (int) $params->get('random_orderid');

		if ($use_random_orderid)
		{
			$random_numer = $this->_random(5);
			$obj->order_id .= $random_numer . $separator;

			/*This length shud be such that it matches the column lenth of primary key
			It is used to add pading
			Order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number*/
			$len = (23 - 5 - 2 - 5);
		}
		else
		{
			/*This length shud be such that it matches the column lenth of primary key
			 It is used to add pading
			 Order_id_column_field_length - prefix_length - no_of_underscores*/
			$len = (23 - 5 - 2);
		}

		$obj->campaign_id     = $donationInfo['campaign']->id;
		$obj->donor_id        = $donationInfo['donor']->id;
		$obj->donation_id     = $donationInfo['payment']->donation_id;
		$obj->cdate           = date("Y-m-d H:i:s");
		$obj->mdate           = date("Y-m-d H:i:s");
		$obj->original_amount = $donationInfo['payment']->original_amount;
		$obj->amount          = $donationInfo['payment']->amount;

		// Need To Modify
		$obj->fee         = $donationInfo['payment']->fee;
		$obj->fund_holder = $donationInfo['payment']->fund_holder;
		$obj->processor   = $donationInfo['payment']->processor;

		// Get the IP Address
		$obj->ip_address     = $donationInfo['payment']->ip_address;
		$obj->transaction_id = $data['transaction_id'];
		$obj->status         = $data['status'];
		$obj->extra          = json_encode($data['raw_data']);

		try
		{
			$db->insertObject('#__jg_orders', $obj, 'id');
		}
		catch (RuntimeException $e)
		{
			echo $e->getMessage();
		}

		$orders_key = $db->insertid();

		if (!$orders_key)
		{
			return 'Error in saving order details';
		}

		$query = $db->getQuery(true);
		$query->select('order_id');
		$query->from($db->qn('#__jg_orders'));
		$query->where($db->qn('#__jg_orders.id') . ' = ' . (int) $orders_key);
		$db->setQuery($query);
		$order_id      = (string) $db->loadResult();
		$maxlen        = 23 - strlen($order_id) - strlen($orders_key);
		$padding_count = (int) $params->get('padding_count');

		// Use padding length set by admin only if it is les than allowed(calculate) length
		if ($padding_count > $maxlen)
		{
			$padding_count = $maxlen;
		}

		$append = '';

		if (strlen((string) $orders_key) <= $len)
		{
			for ($z = 0; $z < $padding_count; $z++)
			{
				$append .= '0';
			}

			$append = $append . $orders_key;
		}

		$res           = new stdClass;
		$res->id       = $orders_key;

		// Imp
		$res->order_id = $order_id . $append;

		if (!$db->updateObject('#__jg_orders', $res, 'id'))
		{
		}
	}

	/**
	 * Method changeOrderStatus.
	 *
	 * @param   Object  $post  JInput Post Object
	 *
	 * @return  boolean|Integer value.
	 *
	 * @since	1.8
	 */
	public function changeOrderStatus($post)
	{
		$returnvaule = 1;
		$data        = $post;
		$user        = Factory::getUser();
		$authorised  = $user->authorise('core.admin');

		$orderClass    = JGive::order($data->get('id', '0', 'INT'));
		$orderId       = $orderClass->getId(); 
		$txnid         = $orderClass->getTransactionId(); 
		$paymentStatus = $orderClass->getStatus();
		$pgPlugin      = $orderClass->getProcessor(); 

		$campaignId    = $orderClass->getCampaignId();
		$campaignClass = JGive::campaign($campaignId);
		$creatorId     = $campaignClass->getCreatorId();

		// Not allowing to the guest user for changing donation/investment status
		if (!$user->id)
		{
			return false;
		}
		// Allows to change the order status only for campaign owner
		elseif ($creatorId == $user->id)
		{
		}
		// Allows to change the order status only for site admin
		elseif (empty($authorised))
		{
			return false;
		}

		// For email
		$notify_chk = $data->get('notify_chk', '', 'STRING')? 1 : 0;
		$comment    = $data->get('comment', '', 'STRING') ? $data->get('comment', '', 'STRING'): '';

		$this->donationsHelper->updatestatus($data->get('id', '0', 'INT'), $data->get('status', '', 'STRING'), $comment, $notify_chk, 0);

		if ($notify_chk == 1)
		{
			$donationDetails = $this->donationModel->getItem($data->get('id', '0', 'INT'));
			JLoader::import('components.com_jgive.events.donation', JPATH_SITE);
			$jGiveTriggerDonation = new JGiveTriggerDonation;
			$jGiveTriggerDonation->onDonationAfterSave($donationDetails, false);
		}

		// Assign order status
		$status = $data->get('status', '', 'STRING');

		if ($data->get('status', '', 'STRING') == 'C')
		{
			$orderDetails = array();
			$orderDetails['vendor_id']        = $campaignClass->getVendorId();
			$orderDetails['status']           = $data->get('status', '', 'STRING');
			$orderDetails['client']           = "com_jgive";
			$orderDetails['client_name']      = Text::_('COM_JGIVE');
			$orderDetails['order_id']         = $orderClass->getOrderId();
			$orderDetails['amount']           = $orderClass->getOriginalAmount();
			$orderDetails['fee_amount']       = $orderClass->getFee();
			$orderDetails['transaction_time'] = $orderClass->getCreatedDate();
			$orderDetails['customer_note']    = "";
			$tjvendorFrontHelper = new TjvendorFrontHelper;
			$tjvendorFrontHelper->addEntry($orderDetails);
			$this->addPayoutEntry($orderId, $txnid, $paymentStatus, $pgPlugin);

		}

		if ($data->get('status', '', 'STRING') == 'C')
		{
			$donationDetails = $this->donationModel->getItem($data->get('id', '0', 'INT'));

			/* Check if goal amount has reached for first time, then only send an email */
			/* Proceed only if payment status is completed */
			if ($donationDetails['payment']->status === "C")
			{
				PluginHelper::importPlugin('jgive');
				PluginHelper::importPlugin('actionlog');
				Factory::getApplication()->triggerEvent('onAfterJGPaymentSuccess', array($donationDetails));

				// OnAfterJGivePaymentSuccess
				PluginHelper::importPlugin('system');
				$order_id_key = $this->donationsHelper->getOrderIdKeyFromOrderId($donationDetails['payment']->order_id);
				Factory::getApplication()->triggerEvent('onAfterJGivePaymentSuccess', array($order_id_key));

				// Check giveback status
				$givebackStatus=$this->donationsHelper->getSoldGivebacks($data->get('id', '0', 'INT'));
				// Trigger the 'onAfterJGiveDonarTakingGiveback' event when giveback status is set
				if(!empty($givebackStatus))
				{
					PluginHelper::importPlugin('system');
					Factory::getApplication()->triggerEvent('onAfterJGiveDonarTakingGiveback',
					array($order_id_key)
					);
				}

				/* Check if total amount received(including current order payment) by the campaign is greater than goal amount */
				if ((float) $donationDetails['campaign']->amount_received >= (float) $donationDetails['campaign']->goal_amount)
				{
					$previousReceivedAmount = $donationDetails['campaign']->amount_received - $donationDetails['payment']->amount;

					/* Previous received amount (excluding current) is less than goal amount,
					 * This ensures campaign goal amount has reached for first time.
					 * */
					if ((float) $previousReceivedAmount <= (float) $donationDetails['campaign']->goal_amount)
					{
						$campaignDetails = Table::getInstance('campaign', 'JgiveTable');
						$campaignDetails->load(array('id' => $campaignId));

						JLoader::import('components.com_jgive.events.campaign', JPATH_SITE);
						$jGiveTriggerCampaign = new JGiveTriggerCampaign;
						$jGiveTriggerCampaign->goalAmountReachedFirstTime($campaignDetails);

						// Trigger the event for campaign goal amount reached.
						PluginHelper::importPlugin('system');
						Factory::getApplication()->triggerEvent('onAfterJGiveCampaignGoalAmountReached',
							array($order_id_key)
						);
					}
				}
			}

			// Update campaign success status.
			$this->campaignHelper->updateCampaignSuccessStatus($cid = 0, $campaignSuccessStatus = null, $data->get('id', '0', 'INT'));
		}
		elseif ($status == 'RF')
		{
			$returnvaule = 3;
		}

		return $returnvaule;
	}

	/**
	 * Method _random.
	 *
	 * @param   Integer  $length  Length  .
	 *
	 * @return  String
	 *
	 * @since	1.8
	 */
	public function _random($length = 17)
	{
		$salt   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len    = strlen($salt);
		$random = '';
		$stat   = @stat(__FILE__);

		if (empty($stat) || !is_array($stat))
		{
			$stat = array(php_uname());
		}

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i++)
		{
			$random .= $salt[mt_rand(0, $len - 1)];
		}

		return $random;
	}

	/**
	 * Method getMinimumAmount.
	 *
	 * @param   Integer  $cid  campaign id  .
	 *
	 * @return  Integer minimum_amount.
	 *
	 * @since	1.8
	 */
	public function getMinimumAmount($cid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.minimum_amount');
		$query->from($db->qn('#__jg_campaigns', 'c'));
		$query->where($db->qn('c.id') . ' = ' . (int) $cid);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method checkMailExists.
	 *
	 * @param   string  $mail  The mail.
	 *
	 * @return  Integer
	 *
	 * @since	1.8
	 */
	public function checkMailExists($mail)
	{
		$mailexist = 0;
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->qn('#__users'));
		$query->where($db->qn('#__users.email') . 'LIKE' . $db->quote($db->escape($mail)));
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result)
		{
			$mailexist = 1;
		}
		else
		{
			$mailexist = 0;
		}

		return $mailexist;
	}

	/**
	 * Method _CheckToCancelSubscriptions.
	 *
	 * @param   integer  $donation_id   The donation id .
	 * @param   array    $data          The data.
	 * @param   integer  $order_id_key  The Order Id Key.
	 *
	 * @return  Array result
	 *
	 * @since	1.8
	 */
	public function _CheckToCancelSubscriptions($donation_id, $data, $order_id_key)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id) as orders_count');
		$query->from($db->qn('#__jg_orders'));
		$query->where($db->qn('#__jg_orders.donation_id') . ' = ' . (int) $donation_id);
		$query->where($db->qn('#__jg_orders.status') . ' = ' . $db->quoteName('C'));
		$query->group($db->qn('#__jg_orders.donation_id'));
		$db->setQuery($query);
		$orders_count = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('recurring_count');
		$query->from($db->qn('#__jg_donations', 'd'));
		$query->where($db->qn('d.id') . ' = ' . (int) $donation_id);
		$db->setQuery($query);
		$recurring_count = $db->loadResult();

		if (($orders_count == $recurring_count) || ($orders_count > $recurring_count))
		{
			$query = $db->getQuery(true);
			$query->select('extra');
			$query->from($db->qn('#__jg_orders'));
			$query->where($db->qn('#__jg_orders.id') . ' = ' . (int) $order_id_key);
			$query->where($db->qn('#__jg_orders.status') . ' = ' . $db->quoteName('C'));
			$db->setQuery($query);
			$extra = $db->loadResult();

			PluginHelper::importPlugin('payment', $pg_plugin);
			Factory::getApplication()->triggerEvent('onTP_cancelSubscription', array($extra));
		}
	}

	/**
	 * Get the donor data if donor is recurring
	 *
	 * @return  Array Donor data
	 *
	 * @since  1.7.3
	 */
	public function getRecurringDonorInfo()
	{
		$user_id = Factory::getUser()->id;

		if (!$user_id)
		{
			return;
		}

		$db          = Factory::getDbo();
		$nestedQuery = $db->getQuery(true);
		$nestedQuery->select('MAX(id)');
		$nestedQuery->from($db->quoteName('#__jg_donors'));
		$nestedQuery->where($db->quoteName('user_id') . ' = ' . $user_id);

		$query = $db->getQuery(true);
		$query->select('*, email as paypal_email, country as country_id');
		$query->from($db->quoteName('#__jg_donors'));
		$query->where($db->quoteName('id') . ' = (' . $nestedQuery . ')');
		$db->setQuery($query);
		$results = $db->loadAssoc();

		return $results;
	}

	/**
	 * Method to validate Giveback against amount entered for donation
	 *
	 * @param   INT  $order_id         Order Id
	 * @param   INT  $giveback_id      Giveback Id
	 * @param   INT  $donation_amount  Donation Amoun
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7
	 */
	public function validateGiveback($order_id = null, $giveback_id = 0, $donation_amount = 0)
	{
		if (!empty($giveback_id))
		{
			try
			{
				// Create a new query object.
				$query = $this->_db->getQuery(true);
				$query->select(array('amount'));
				$query->from('#__jg_campaigns_givebacks');
				$query->where('id = ' . $giveback_id);
				$this->_db->setQuery($query);
				$giveback_minimum_amount = $this->_db->loadResult();

				if (((float) $giveback_minimum_amount) > ((float) $donation_amount))
				{
					return false;
				}
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to validate donation data
	 *
	 * @param   Object|Array  $post  Order data
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.3.0
	 */
	public function validateOrderData($post)
	{
		$return = true;
		$params = ComponentHelper::getParams('com_jgive');

		$user   = Factory::getUser();
		$userId = $user->id;

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaign');
		$jgiveModelCampaign = BaseDatabaseModel::getInstance('campaign', 'JGiveModel');
		$campaignData       = $jgiveModelCampaign->getItem($post->get('cid', '', 'INT'));
		$hideSelectedFields = $params->get('show_selected_fields_on_donation');
		$country            = $post->get('country', '', 'INT');
		$donationfield      = $params->get('donationfield');

		// If campaign does not exist
		if (!$campaignData['campaign']->id)
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_VALIDATION_CAMPAIGN_NOT_PRESENT'));
			$return = false;
		}

		// Donation button status should not be "Close" or "Not yet started"
		if ($campaignData['campaign']->donateBtnShowStatus != 1)
		{
			$msg = ($campaignData['campaign']->donateBtnShowStatus == -1) ? Text::_(
			'COM_JGIVE_DONATION_NOT_STARTED_BUTTON_FLAG_MSG') : Text::_('COM_JGIVE_DONATION_CLOSE_BUTTON_FLAG_MSG');
			$this->setError($msg);
			$return = false;
		}

		// Donation amount should not be Zero or negative
		if ($post->get('donation_amount', '', 'FLOAT') <= 0)
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_NEGATIVE_ZERO_NUMBER_ERROR_MSG'));
			$return = false;
		}

		// Email Validation
		if (!filter_var($post->get('paypal_email', '', 'STRING'), FILTER_VALIDATE_EMAIL))
		{
			$return['error']  = Text::_('COM_JGIVE_USER_INVALID_EMAIL');
			$return = false;
		}

		// Server side validation Invalid Payment Gateways
		if (!in_array($post->get('gateways', '', 'STRING'), $params->get('gateways')))
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_VALIDATION_WRONG_PAYMENT_METHOD'));
			$return = false;
		}

		// User Privacy validation
		if (!empty($params->get('terms_condition', 0)) && !empty($params->get('payment_terms_article', 0)))
		{
			if (empty($post->get('terms_condition', '', 'STRING')) || $post->get('terms_condition', '', 'STRING') != 'on')
			{
				$this->setError(Text::_('COM_JGIVE_CHECK_TERMS'));
				$return = false;
			}
		}

		// If guset donation is off and loggedin user and userId in $post data is different
		if (($params->get('guest_donation') != 1) && (!$userId || $userId != $post->get('userid', '', 'INT')))
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_VALIDATION_INVALID_USER'));
			$return = false;
		}

		if ($post->get('donation_type', '', 'INT'))
		{
			$recurringCount = $post->get('recurring_count', 0, 'INT');
			$recurringFreq = $post->get('recurring_freq', '', 'STRING');

			if (!in_array(strtoupper($recurringFreq), array('D', 'W', 'M', 'Y')) || $recurringCount == 0 || $recurringCount == 1)
			{
				$this->setError(Text::_('COM_JGIVE_DONATION_VALIDATION_INVALID_RECURRING_FREQ'));
				$return = false;
			}
		}

		if (($campaignData['campaign']->minimum_amount > 0) && ($post->get('donation_amount', '', 'FLOAT') < $campaignData['campaign']->minimum_amount))
		{
			$this->setError(Text::_('COM_JGIVE_DONATION_VALIDATION_DONATION_AMOUNT_GREATER_THAN_CAMPAIGN_AMOUNT'));
			$return = false;
		}

		// Max allowed donor/investers validation
		if (isset($campaignData['campaign']->max_donors) && $campaignData['campaign']->max_donors > 0)
		{
			$this->setState("filter.campaign_id", $post->get('cid', '', 'INT'));
			$this->setState("filter.payment_status", "C");
			$donations = (array) $this->getItems();

			if (count($donations) >= $campaignData['campaign']->max_donors)
			{
				$this->setError(Text::_('COM_JGIVE_CAMPAIGN_MAX_ALLOWED_DONOR_REACHED'));
				$return = false;
			}
		}

		$givebacks = $post->get('givebacks', '', 'INT');

		if ($givebacks)
		{
			$giveBacksTable   = Table::getInstance('GiveBacks', 'JGiveTable', array());
			$giveBacksTable->load(array('id' => $post->get('givebacks', '', 'INT')));

			if ($giveBacksTable->amount > $post->get('donation_amount', '', 'FLOAT'))
			{
				$this->setError(Text::_('COM_JGIVE_AMOUNT_SHOULD_BE') . ' ' . $giveBacksTable->amount);
				$return = false;
			}
		}

		// Donor not allow to donate more that goal amount if campaign not allowing exceed donation
		if ($campaignData['campaign']->allow_exceed != 1)
		{
			// Donation amount grater than campaign goal amount
			if ($campaignData['campaign']->goal_amount < $post->get('donation_amount', '', 'FLOAT'))
			{
				$this->setError(Text::sprintf('COM_JGIVE_GOAL_AMOUNT_EXCEED_NOT_ALLOW_MSG', $campaignData['campaign']->goal_amount));
				$return = false;
			}

			if ($campaignData['campaign']->remaining_amount < $post->get('donation_amount', '', 'FLOAT'))
			{
				$this->setError(Text::sprintf('COM_JGIVE_REMAINING_AMOUNT_EXCEED_NOT_ALLOW_MSG', $campaignData['campaign']->remaining_amount));
				$return = false;
			}
		}

		// Country validation
		if ($hideSelectedFields == true)
		{
			if ((empty($donationfield) || !in_array('country', $donationfield)) && $params->get('quick_donation') != 1)
			{
				if ($country == 0 || $country == '')
				{
					$this->setError(Text::_('COM_JGIVE_INVALID_FIELD') . Text::_('COM_JGIVE_DONATION_COUNTRY_VALIDATION'));

					$return = false;
				}
			}
		}
		else
		{
			if ((property_exists($post, 'country')) && ($country == 0 || $country == ''))
			{
				$this->setError(Text::_('COM_JGIVE_INVALID_FIELD') . Text::_('COM_JGIVE_DONATION_COUNTRY_VALIDATION'));

				$return = false;
			}
		}

		return $return;
	}
}
