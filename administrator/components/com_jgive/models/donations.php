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
defined("_JEXEC") or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * JGive backend Donations list model.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       2.2.0
 */
class JgiveModelDonations extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.2.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'order_id',
				'campaign_id',
				'donor_id',
				'donation_id',
				'cdate',
				'amount',
				'status',
				'processor',
				'fund_holder',
				'fee',
				'd.user_id',
				'original_amount'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   2.2.0
	 */
	protected function populateState($ordering = 'id', $direction = 'desc')
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Filter - campaign type
		$campaign_type = $app->getUserStateFromRequest($this->context . 'filter.campaign_type', 'campaign_type', '', 'STRING');

		if (in_array(($campaign_type), array('donation', 'investment')))
		{
			$this->setState('filter.campaign_type', $campaign_type);
		}

		// Filter - campaign
		$campaign = $app->getUserStateFromRequest($this->context . 'filter.campaign', 'campaign', '', 'INT');
		$this->setState('filter.campaign', $campaign);

		// Filter - campaign category.
		$category = $app->getUserStateFromRequest($this->context . '.filter.campaign_category', 'campaign_category', '', 'STRING');
		$this->setState('filter.campaign_category', $category);

		// Filter - Payment Status.
		$payment_status = $app->getUserStateFromRequest($this->context . 'filter.payment_status', 'payment_status', '', 'STRING');

		if (in_array(($campaign_type), array('P', 'C', 'RF', 'E', 'D')))
		{
			$this->setState('filter.payment_status', $payment_status);
		}

		// Load the filter search
		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Filter - Donor Type.
		$donor_type = $app->getUserStateFromRequest($this->context . 'filter.donor_type', 'donor_type', '', 'STRING');

		if (in_array(($donor_type), array('org', 'ind')))
		{
			$this->setState('filter.donor_type', $donor_type);
		}

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list donations data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   2.2.0
	 */
	protected function getListQuery()
	{
		$jinput = Factory::getApplication()->input;
		$cid    = $jinput->get('cid', 0, 'INT');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('o.id','o.order_id','o.campaign_id','o.donor_id','o.donation_id','o.fund_holder')));
		$query->select($db->quoteName(array('o.cdate','o.mdate','o.payment_received_date','o.transaction_id','o.original_amount')));
		$query->select($db->quoteName(array('o.amount','o.fee','o.vat_number','o.status','o.processor','o.ip_address')));
		$query->select($db->quoteName(array('c.id', 'c.type', 'd.user_id'), array('cid', 'ctype', 'donor_id')));
		$query->select(
			$db->quoteName(
				array(
					'd.first_name', 'd.last_name', 'd.donor_type', 'd.org_name',
					'c.title', 'dona.comment', 'dona.annonymous_donation'
				)
			)
		);

		$query->from($db->quoteName('#__jg_orders', 'o'));
		$query->join('LEFT', $db->quoteName('#__jg_campaigns', 'c') .
			' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('o.campaign_id') . ')');
		$query->join('LEFT', $db->quoteName('#__categories', 'cat') .
			' ON (' . $db->quoteName('cat.id') . ' = ' . $db->quoteName('c.category_id') . ')');
		$query->join('LEFT', $db->quoteName('#__jg_donors', 'd') .
			' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('o.donor_id') . ')');
		$query->join('LEFT', $db->quoteName('#__jg_donations', 'dona') .
			' ON (' . $db->quoteName('dona.id') . ' = ' . $db->quoteName('o.donation_id') . ')');

		// Filter campaign type
		$campaign_type = $this->getState('filter.campaign_type');

		if (!empty($campaign_type))
		{
			$query->where($db->quoteName('c.type') . ' = ' . $db->quote($db->escape($campaign_type)));
		}

		// Filter campaign
		$campaign = $this->getState('filter.campaign') ? $this->getState('filter.campaign') : $cid;

		if (!empty($campaign))
		{
			$query->where($db->quoteName('c.id') . ' = ' . (int) $campaign);
		}

		// Filter by category
		$campaign_category = $this->getState('filter.campaign_category');

		if (!empty($campaign_category))
		{
			if (is_numeric($campaign_category))
			{
				if (JVERSION < '4.0.0')
				{
					Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
					$cat_tbl = Table::getInstance('Category', 'CategoriesTable');
				}
				else
				{
					$cat_tbl = Table::getInstance('CategoryTable', '\\Joomla\\Component\\Categories\\Administrator\\Table\\');
				}

				$cat_tbl->load($campaign_category);
				$rgt = $cat_tbl->rgt;
				$lft = $cat_tbl->lft;
				$baselevel = (int) $cat_tbl->level;
				$query->where($db->quoteName('cat.lft') . ' >= ' . (int) $lft);
				$query->where($db->quoteName('cat.rgt') . '<= ' . (int) $rgt);
			}
		}

		// Filter by payment status
		$payment_status = $this->getState('filter.payment_status');

		if ($payment_status != '-1' && !empty($payment_status))
		{
			$query->where($db->quoteName('o.status') . ' = ' . $db->quote($db->escape($payment_status)));
		}

		// Filter by search in title
		$search = $this->getState('filter.search') ? trim($this->getState('filter.search')) : '';

		if (!empty($search))
		{
			// Search record
			if (is_numeric($search))
			{
				$query->where($db->quoteName('o.id') . ' = ' . (int) $search);
			}
			else
			{
				$query->where($db->quoteName('o.order_id') . ' = ' . $db->quote($db->escape($search)));
			}
		}

		// Filter by payment status
		$donor_type = $this->getState('filter.donor_type');

		if (!empty($donor_type))
		{
			$query->where($db->quoteName('d.donor_type') . ' = ' . $db->quote($db->escape($donor_type)));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get the donors email id of selected donation records
	 *
	 * @param   Array  $donation_ids  Donation id
	 *
	 * @return  Array Donor data
	 *
	 * @since  2.2.0
	 */
	public function getDonorsEmailByDonationId($donation_ids)
	{
		$db = Factory::getDbo();
		$email_array = array();
		$modifiedDonorIds = implode(",", $donation_ids);

		if (!empty($modifiedDonorIds))
		{
				$query = $db->getQuery(true);
				$query->select('DISTINCT d.email');
				$query->select('d.first_name');
				$query->select('d.last_name');
				$query->from($db->qn('#__jg_donors', 'd'));
				$query->where($db->qn('d.id') . ' IN(' . $modifiedDonorIds . ')');
				$db->setQuery($query);
				$email_array = $db->loadAssocList();
		}

		return $email_array;
	}

	/**
	 * Method deleteDonations.
	 *
	 * @param   Countable|Array  $orderId  order id  .
	 *
	 * @return  boolean
	 *
	 * @since	1.8
	 */
	public function deleteDonations($orderId)
	{
		$db           = Factory::getDbo();
		$orderIdCount = count($orderId);

		// Check which orders is recurring
		for ($i = 0; $i < $orderIdCount; $i++)
		{
			$query = $db->getQuery(true);
			$query->select('donation_id');
			$query->from($db->qn('#__jg_orders'));
			$query->where($db->qn('#__jg_orders.id') . ' = ' . (int) $orderId[$i]);
			$db->setQuery($query);
			$donationId = $db->loadResult();

			if ($donationId)
			{
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');
				$donationModel = BaseDatabaseModel::getInstance('Donation', 'JgiveModel');
				$donationData  = $donationModel->getItem($donationId);

				$query = $db->getQuery(true);
				$query->select('is_recurring');
				$query->from($db->qn('#__jg_donations'));
				$query->where($db->qn('#__jg_donations.id') . ' = ' . (int) $donationId);
				$db->setQuery($query);
				$isRecurring = $db->loadResult();

				if ($isRecurring) // For recurring donations delete only order entry
				{
					// Delete from  orders
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__jg_orders'));
					$query->where($db->quoteName('#__jg_orders.id') . ' = ' . (int) $orderId[$i]);
					$db->setQuery($query);

					if (!$db->execute())
					{
						$this->setError($db->getErrorMsg());

						return false;
					}
				}
				else // Delete donor, Order, donations
				{
					// Delete from donors
					$query = $db->getQuery(true);
					$query->select('donor_id');
					$query->from($db->qn('#__jg_donations'));
					$query->where($db->qn('#__jg_donations.id') . ' = ' . (int) $donationId);
					$db->setQuery($query);
					$donorId = $db->loadResult();

					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__jg_donors'));
					$query->where($db->quoteName('#__jg_donors.id') . ' = ' . (int) $donorId);
					$db->setQuery($query);

					if (!$db->execute())
					{
						$this->setError($db->getErrorMsg());

						return false;
					}

					// Delete from  orders
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__jg_orders'));
					$query->where($db->quoteName('#__jg_orders.id') . ' = ' . (int) $orderId[$i]);
					$db->setQuery($query);

					if (!$db->execute())
					{
						$this->setError($db->getErrorMsg());

						return false;
					}

					// Delete from donations
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__jg_donations'));
					$query->where($db->quoteName('#__jg_donations.id') . ' = ' . (int) $donationId);
					$db->setQuery($query);

					if (!$db->execute())
					{
						$this->setError($db->getErrorMsg());

						return false;
					}
				}

				PluginHelper::importPlugin('jgive');
				PluginHelper::importPlugin('actionlog');
				Factory::getApplication()->triggerEvent('onAfterJGOrderDelete', array($donationData));
			}
			else // Support the version of jGive before 1.6 (Recurring payment & One page checkout) release
			{
				// Delete from donors
				$query = $db->getQuery(true);
				$query->select('donor_id');
				$query->from($db->qn('#__jg_orders'));
				$query->where($db->qn('#__jg_orders.id') . ' = ' . (int) $orderId[$i]);
				$db->setQuery($query);
				$donorId = $db->loadResult();

				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jg_donors'));
				$query->where($db->quoteName('#__jg_donors.id') . ' = ' . (int) $donorId);
				$db->setQuery($query);

				if (!$db->execute())
				{
					$this->setError($db->getErrorMsg());

					return false;
				}

				// Delete from  orders
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jg_orders'));
				$query->where($db->quoteName('#__jg_orders.id') . ' = ' . (int) $orderId[$i]);
				$db->setQuery($query);

				if (!$db->execute())
				{
					$this->setError($db->getErrorMsg());

					return false;
				}

				// Delete from donations
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jg_donations'));
				$query->where($db->quoteName('#__jg_donations.order_id') . ' = ' . (int) $orderId[$i]);
				$db->setQuery($query);

				if (!$db->execute())
				{
					$this->setError($db->getErrorMsg());

					return false;
				}
			}
		}

		return true;
	}
}
