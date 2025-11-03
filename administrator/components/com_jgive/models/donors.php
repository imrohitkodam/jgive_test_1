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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of Donors records.
 *
 * @since  1.8.9
 */
class JGiveModelDonors extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.7
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'user_id', 'a.user_id',
				'campaign_id', 'a.campaign_id',
				'email', 'a.email',
				'first_name', 'a.first_name',
				'last_name', 'a.last_name',
				'address', 'a.address',
				'address2', 'a.address2',
				'city', 'a.city',
				'state', 'a.state',
				'country', 'a.country',
				'zip', 'a.zip',
				'phone', 'a.phone',
				'donor_type', 'a.donor_type',
				'giveback_id', 'g.id',
				'donation_amount', 'o.original_amount',
				'cdate', 'o.cdate',
				'campaigns_title', 'c.campaigns_title',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = 'a.id', $direction = 'DESC')
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Pre-fill the limits for pagination via search tools
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$this->setState('list.limit', $limit);

		$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int');
		$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
		$this->setState('list.start', $limitstart);

		// Set ordering.
		$ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		if (!in_array($ordering, $this->filter_fields))
		{
			$ordering = 'a.id';
		}

		$this->setState('list.ordering', $ordering);

		// Set ordering direction.
		$direction = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir');

		if (!in_array($direction ? strtoupper($direction) : '', array('ASC', 'DESC', '')))
		{
			$direction = 'desc';
		}

		$this->setState('list.direction', $direction);

		// Load the filter search
		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the filter campaign
		$campaign_id = $app->getUserStateFromRequest($this->context . 'filter.campaign_id', 'filter_campaign_id', '', 'string');
		$this->setState('filter.campaign_id', $campaign_id);

		// Load the filter donor type
		$donor_type = $app->getUserStateFromRequest($this->context . 'filter.donor_type', 'donor_type', '', 'string');
		$this->setState('filter.donor_type', $donor_type);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jgive');
		$this->setState('params', $params);

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
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.8.9
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'DISTINCT a.*'));
		$query->from('`#__jg_donors` AS a');

		// Join over the foreign key 'campaign_id'
		$query->select('c.title AS campaigns_title');
		$query->select('g.id AS gid');
		$query->select('g.description AS gdesc');
		$query->select('o.original_amount AS donation_amount');
		$query->select('o.cdate AS cdate');
		$query->select('o.payment_received_date AS payment_received_date');
		$query->select('cou.country');
		$query->select('reg.region AS state');
		$query->join('LEFT', '#__jg_campaigns AS c ON c.id = a.campaign_id');
		$query->join('LEFT', '#__jg_donations AS d ON d.donor_id = a.id');
		$query->join('LEFT', '#__jg_campaigns_givebacks AS g ON g.id = d.giveback_id');
		$query->join('LEFT', '#__jg_orders AS o ON o.donor_id = a.id');
		$query->join('LEFT', '#__tj_country AS cou ON cou.id = a.country');
		$query->join('LEFT', '#__tj_region AS reg ON reg.id = a.state');
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				// Search record
				$search = $db->Quote('%' . $db->escape(trim($search), true) . '%');
				$query->where(
					'(a.first_name LIKE ' . $search .
					' OR a.last_name LIKE' . $search .
					' OR  a.email LIKE ' . $search .
					' OR  a.org_name LIKE ' . $search . ')'
				);
			}
		}

		// Filtering campaign_id
		$filter_campaign_id = $this->state->get("filter.campaign_id");

		if ($filter_campaign_id)
		{
			// Code for filter record
			$query->where("a.campaign_id = '" . $db->escape($filter_campaign_id) . "'");
		}

		// Filtering by donor type
		$filter_donor_type = $this->state->get("filter.donor_type");

		if ($filter_donor_type)
		{
			// Code for filter record
			$query->where("a.donor_type = '" . $db->escape($filter_donor_type) . "'");
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$db = Factory::getDbo();

		foreach ($items as $item)
		{
			if (isset($item->city) && $item->city != '')
			{
				if (is_object($item->city))
				{
					$item->city = ArrayHelper::fromObject($item->city);
				}

				$values = (is_array($item->city)) ? $item->city : explode(',', $item->city);
				$textValue = array();

				foreach ($values as $value)
				{
					// Fetch city by id
					$query = $db->getQuery(true);
					$query->select($db->quoteName('city'))
							->from('`#__tj_city`')
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->city;
					}
				}

				$item->city = !empty($textValue) ? implode(', ', $textValue) : $item->city;
			}
		}

		return $items;
	}
}
