<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
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

class JgiveModelReports extends ListModel
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
				'title', 'c.title',
				'goal_amount', 'c.goal_amount',
				'vendor_title', 'v.vendor_title',
				'donations_count', 'donations_count',
				'total_amount', 'total_amount',
				'total_commission', 'total_commission',
				'campaign_type',
				'campaign',
				'org_ind_type_report'
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
	protected function populateState($ordering = 'c.goal_amount', $direction = 'DESC')
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
		$query->select($db->quoteName(array('v.vendor_title', 'v.vendor_id', 'u.username', 'c.title', 'c.creator_id', 'c.goal_amount')));
		$query->select($db->quoteName(array('c.id'), array('cid')));
		$query->select('SUM(o.original_amount) as total_amount');
		$query->select('SUM(o.fee) as total_commission');
		$query->select('COUNT(o.id) as donations_count');
		$query->from($db->quoteName('#__jg_orders', 'o'));
		$query->join('LEFT', $db->quoteName('#__jg_campaigns', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('o.campaign_id') . ')');
		$query->join('LEFT', $db->quoteName('#__categories', 'cat') . ' ON (' . $db->quoteName('c.category_id') . ' = ' . $db->quoteName('cat.id') . ')');
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('c.creator_id') . ')');
		$query->join('LEFT', $db->quoteName('#__tjvendors_vendors', 'v') . ' ON (' . $db->quoteName('v.user_id') . ' = ' . $db->quoteName('u.id') . ')');

		if ($this->state->get('filter.campaign_type') != '')
		{
			$query->where($db->quoteName('c.type') . ' = ' . $db->quote(trim($this->state->get('filter.campaign_type'))));
		}

		if ($this->state->get('filter.campaign'))
		{
			$query->where($db->quoteName('c.id') . ' = ' . (int) $this->state->get("filter.campaign"));
		}

		if ($this->state->get('filter.org_ind_type_report') != '')
		{
			$query->where($db->quoteName('c.org_ind_type') . ' = ' . $db->quote(trim($this->state->get('filter.org_ind_type_report'))));
		}

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape(trim($search), true) . '%');
			$query->where('(c.title LIKE ' . $search . ' OR v.vendor_title LIKE ' . $search . ' )');
		}

		$query->where($db->quoteName('o.status') . ' = ' . $db->quote('C'));
		$query->group($db->quoteName('o.campaign_id'));

		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Method to get a list of campaign wise data.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure
	 *
	 * @since   2.5.0
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$reportsHelper = new reportsHelper;

		// Get Joomla Custom field data
		if (!empty($items))
		{
			foreach ($items as $key => $item)
			{
				$item->exclude_amount = $reportsHelper->getTotalAmount2BExcluded($item->cid);
			}
		}

		return $items;
	}
}
