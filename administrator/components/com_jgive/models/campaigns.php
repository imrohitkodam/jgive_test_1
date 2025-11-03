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
use Joomla\CMS\Table\Table;

/**
 * Methods supporting a list of cities records.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.7
 */
class JgiveModelCampaigns extends ListModel
{
	protected $campaignHelper;

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
				'published', 'a.published',
				'title', 'a.title',
				'start_date', 'a.start_date',
				'end_date', 'a.end_date',
				'goal_amount', 'a.goal_amount',
				'featured', 'a.featured',
				'success_status', 'a.success_status',
				'ordering', 'a.ordering',
			);
		}

		$this->campaignHelper = new campaignHelper;

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
	 * @since   1.7
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.id';
		}

		$this->setState('list.ordering', $orderCol);

		// Load the filter search
		$search = $app->getUserStateFromRequest($this->context . 'filter.filter_search', 'filter_search');
		$this->setState('filter.filter_search', $search);

		// Filter category.
		$category = $app->getUserStateFromRequest($this->context . '.filter.campaign_category', 'campaign_category', '', 'string');
		$this->setState('filter.campaign_category', $category);

		// Load the filter state
		$published = $app->getUserStateFromRequest($this->context . 'filter.publish_states', 'publish_states', '', 'string');
		$this->setState('filter.publish_states', $published);

		// Load the filter campaign type
		$campaign_type = $app->getUserStateFromRequest($this->context . 'filter.campaign_type', 'campaign_type', '', 'string');
		$this->setState('filter.campaign_type', $campaign_type);

		// Load the filter org type
		$campaign_org_ind_type = $app->getUserStateFromRequest($this->context . 'filter.org_ind_type', 'org_ind_type', '', 'string');
		$this->setState('filter.org_ind_type', $campaign_org_ind_type);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jgive');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'desc');
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
	 * @return  string  A store id.
	 *
	 * @since   1.7
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.filter_search');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.7
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();
		$app = Factory::getApplication();

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'));
		$query->from('`#__jg_campaigns` AS a');

		// Join over the categories.
		$query->select('c.title AS category_title')
			->join('LEFT', '#__categories AS c ON c.id = a.category_id');

		// Join over the users
		$query->select('u.name AS campaign_creator')
			->join('LEFT', '#__users AS u ON a.creator_id = u.id');

		// Join over the vendor
		$query->select('v.vendor_title')
			->join('LEFT', '#__tjvendors_vendors AS v ON a.vendor_id = v.vendor_id');

		// Filter by published state.
		$published = ($this->getState('filter.publish_states') != null) ? $this->getState('filter.publish_states'): $this->getState('publishedCampaigns');

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.filter_search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.title LIKE ' . $search . ' )');
			}
		}

		// Filter by search in title
		$search = $this->getState('filter.filter_search');

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
				$query->where('c.lft >= ' . (int) $lft);
				$query->where('c.rgt <= ' . (int) $rgt);
			}
		}

		// Filter campaign type
		$campaign_type = $this->getState('filter.campaign_type');

		if (!empty($campaign_type))
		{
			$query->where('a.type = ' . $db->quote($db->escape($campaign_type)));
		}

		// Filter org ind type
		$org_ind_type = $this->getState('filter.org_ind_type');

		if (!empty($org_ind_type))
		{
			$query->where('a.org_ind_type = ' . $db->quote($db->escape($org_ind_type)));
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
	 * Method to get a list of campaigns.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.7
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $item)
		{
			// Get campaign amounts
			JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');
			$campaignHelper = new campaignHelper;
			$amounts                = $campaignHelper->getCampaignAmounts($item->id);
			$item->amount_received  = $amounts['amount_received'];
			$item->remaining_amount = $amounts['remaining_amount'];
			$item->donor_count      = $campaignHelper->getCampaignDonorsCount($item->id);
			$item->CampaignState    = $this->getCampaignState((array) $item);
		}

		return $items;
	}

	/**
	 * Function getDonteButtonStatusFlag
	 *
	 * @param   Array  $campaignData  All Campaign data
	 *
	 * @return  Integer  DonteButtonStatusFlag
	 *
	 * @since   2.3.5
	 */
	public function getCampaignState($campaignData)
	{
		$camp_startdate = Factory::getDate($campaignData['start_date'])->Format('Y-m-d H:i:s');
		$camp_enddate   = Factory::getDate($campaignData['end_date'])->Format('Y-m-d H:i:s');
		$curr_date      = Factory::getDate()->Format('Y-m-d H:i:s');

		if ($curr_date < $camp_startdate)
		{
			// Campaign Not yet started
			return -1;
		}
		elseif ($curr_date > $camp_enddate)
		{
			// Campaign closed
			return 0;
		}
		else
		{
			// Check Allow donations to exceed goal amount configuration
			$allow_exceed    = $campaignData['allow_exceed']? $campaignData['allow_exceed']: '0';

			// Check max donors or investor if set don't consider is value is 0
			$max_donors      = $campaignData['max_donors']? $campaignData['max_donors']: '0';

			// Check campaign goal amout
			$goal_amount     = $campaignData['goal_amount'];

			// Check campaign received amout
			$amount_received = $campaignData['amount_received'];

			// If not allowed exceed amount than goal amount
			if ($allow_exceed == '0' && $goal_amount <= $amount_received)
			{
				return 0;
			}

			// If max donors count is reached
			if ($max_donors != '0' && ($max_donors <= $campaignData['donor_count']))
			{
				return 0;
			}

			return 1;
		}
	}

	/**
	 * Method to get a list of Active campaigns data.
	 *
	 * @return  Array|Object  An object of data items on success, false on failure.
	 *
	 * @since   2.3.5
	 */
	public function getActiveCampaigns()
	{
		$items = $this->getItems();

		if (!empty($items))
		{
			foreach ($items as $key => $item)
			{
				if ($item->CampaignState == '0' || $item->CampaignState == '-1')
				{
					// Removing closed and not yet started campaigns from object
					unset($items[$key]);
				}
			}
		}

		// Reindex object after removing closed and not yet started campaigns from object
		$updatedItems = array_values($items);

		return $updatedItems;
	}
}
