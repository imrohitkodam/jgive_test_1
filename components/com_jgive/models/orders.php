<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Model for orders for getting orders
 *
 * @package     JGive
 * @subpackage  component
 * @since       2.5.0
 */
class JgiveModelorders extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JController
	 * @since   2.5.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'id', 'o.id',
			'campaign_id', 'o.campaign_id',
			'donor_id','o.donor_id',
			'donation_id', 'o.donation_id',
			'fund_holder', 'o.fund_holder',
			'cdate', 'o.cdate',
			'payment_received_date', 'o.payment_received_date',
			'original_amount', 'o.original_amount',
			'amount', 'o.amount',
			'fee', 'o.fee',
			'status', 'o.status'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Orders order by
	 * @param   string  $direction  Orders order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since  2.5.0
	 */
	protected function populateState($ordering = 'o.id', $direction = 'DESC')
	{
		$app  = Factory::getApplication();

		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', null, 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

		parent::populateState($ordering, $direction);

		$listlimit = $app->input->get('limit', '20', 'INT');

		$this->setState('list.limit', $listlimit);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    2.5.0
	 */
	protected function getListQuery()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('o.*');
		$query->select(
				$db->quoteName(
					array('d.user_id', 'd.contributor_id', 'd.donor_type', 'd.email', 'd.first_name', 'd.last_name', 'd.org_name')
				)
			);
		$query->select($db->quoteName(array('c.title')));
		$query->select($db->quoteName(array('c.creator_id'), array('campaign_creator_id')));
		$query->from($db->quoteName('#__jg_orders', 'o'));
		$query->join('LEFT', $db->quoteName('#__jg_donors', 'd') .
			' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('o.donor_id') . ')');
		$query->join('LEFT', $db->quoteName('#__jg_campaigns', 'c') .
			' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('d.campaign_id') . ')');

		// Filter by order status
		$orderStatus = $this->getState('filter.status');

		if (!empty($orderStatus))
		{
			$query->where($db->quoteName('o.status') . ' = ' . $db->quote(trim($orderStatus)));
		}

		// To get promoter specific campaings orders
		$campaignCreatorId = $this->getState('filter.campaign_creator_id');

		if (!empty($campaignCreatorId))
		{
			$query->where($db->quoteName('c.creator_id') . ' = ' . (int) $campaignCreatorId);
		}

		if (!empty($this->getState('filter.campaign_id')))
		{
			$query->where($db->qn('c.id') . 'IN(' . $this->getState('filter.campaign_id') . ')');
		}

		// Check campaign status 0 - Ongoing, 1 - Successful, -1 - Failed
		$campaignStatus = $this->getState('filter.success_status');

		if (is_numeric($campaignStatus))
		{
			$query->where($db->quoteName('c.success_status') . ' = ' . (int) $campaignStatus);
		}

		// Get orders between two dates
		$orderFromDate = $this->getState('filter.from_date');

		if (!empty($orderFromDate))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' >= ' . $db->quote($orderFromDate));
		}

		$orderEndDate  = $this->getState('filter.end_date');

		if (!empty($orderEndDate))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' <= ' . $db->quote($orderEndDate));
		}

		if (!empty($this->getState('filter.contributor_id')))
		{
			$query->where($db->qn('d.contributor_id') . ' = ' . (int) $this->getState('filter.contributor_id'));
		}

		if (!empty($this->getState('filter.donor_type')))
		{
			$query->where($db->qn('d.donor_type') . ' = ' . $db->q($this->getState('filter.donor_type')));
		}

		// Search filter
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
			$query->where('(d.first_name LIKE ' . $search . ' OR d.last_name LIKE ' . $search .
			' OR d.org_name LIKE' . $search . ' OR c.title LIKE' . $search . ' )');
		}

		$listlimit = $this->getState('list.limit');

		// Default pagination for first page load
		if ($listlimit == '' && $listlimit != '0')
		{
			$listlimit = 20;
		}

		$this->setState('list.limit', $listlimit);

		// Order and Direction filter
		$orderCol  = $this->getState('list.ordering');
		$orderDirn = $this->getState('list.direction');

		if (!in_array($orderDirn ? strtoupper($orderDirn) : '', array('ASC', 'DESC', 'asc', 'desc')))
		{
			$orderDirn = 'ASC';
		}

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'o.id';
		}

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		// Group by filter
		$group = $this->getState('filter.group');

		if ($group)
		{
			$query->group($db->quoteName($group));
		}

		return $query;
	}

	/**
	 * This method will return donations sum on the basis of different filters
	 *
	 * @return float totalDonationAmount
	 *
	 * @since  2.5.0
	 */
	public function getDonationAmountSum()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('SUM(o.original_amount) as total_donation_amount');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->join('LEFT', $db->qn('#__jg_campaigns', 'c') . 'ON (' . $db->qn('c.id') . ' = ' . $db->qn('o.campaign_id') . ')');
		$query->join('LEFT', $db->qn('#__jg_donors', 'd') . 'ON (' . $db->qn('o.donor_id') . ' = ' . $db->qn('d.id') . ')');

		if (!empty($this->getState('filter.contributor_id')))
		{
			$query->where($db->qn('d.contributor_id') . ' = ' . (int) $this->getState('filter.contributor_id'));
		}

		if (!empty($this->getState('filter.donor_type')))
		{
			$query->where($db->qn('d.donor_type') . ' = ' . $db->q($this->getState('filter.donor_type')));
		}

		if (!empty($this->getState('filter.campaign_creator_id')))
		{
			$query->where($db->qn('c.creator_id') . ' = ' . (int) $this->getState('filter.campaign_creator_id'));
		}

		if (!empty($this->getState('filter.campaign_id')))
		{
			$query->where($db->qn('c.id') . 'IN(' . $this->getState('filter.campaign_id') . ')');
		}

		if (!empty($this->getState('filter.campaign_published')))
		{
			$query->where($db->qn('c.published') . '=' . $db->quote($this->getState('filter.campaign_published')));
		}

		if (!empty($this->getState('filter.status')))
		{
			$query->where($db->qn('o.status') . ' = ' . $db->quote($this->getState('filter.status')));
		}

		if (!empty($this->getState('filter.from_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' >= ' . $db->quote($this->getState('filter.from_date')));
		}

		if (!empty($this->getState('filter.end_date')))
		{
			$query->where('DATE(' . $db->quoteName('o.cdate') . ')' . ' <= ' . $db->quote($this->getState('filter.end_date')));
		}

		if ($this->getState('filter.campaign_success_status') != '')
		{
			$query->where($db->quoteName('c.success_status') . ' = ' . (int) $this->getState('filter.campaign_success_status'));
		}

		$db->setQuery($query);
		$totalDonationAmount = $db->loadResult();

		return $totalDonationAmount ? $totalDonationAmount : 0;
	}
}
