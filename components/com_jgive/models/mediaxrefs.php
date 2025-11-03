<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;


/**
 * Tjvendors model.
 *
 * @since  1.6
 */
class JGiveModelMediaXrefs extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'media_id',
				'client_id',
				'client',
				'is_gallery',
				'type',
				'state',
				'storage',
				'created_by',
				'access'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select($db->quoteName('m.*'));
		$query->from($db->quoteName('#__tj_media_files_xref', 'm'));
		$query->join('INNER', $db->quoteName('#__tj_media_files', 'mf') . ' ON (' . $db->quoteName('m.media_id') . ' = ' . $db->quoteName('mf.id') . ')');

		// Get all filters
		$filters = $this->get('filter_fields');

		foreach ($filters as $filter)
		{
			$filterValue = $this->getState('filter.' . $filter);

			if (isset($filterValue))
			{
				$query->where($db->quoteName($filter) . ' = ' . $filterValue);
			}
		}

		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'm.id') . ' ' . $this->getState('list.direction', 'DESC'));

		return $query;
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
	 * @since   2.1
	 */
	protected function populateState($ordering = 'm.id', $direction = 'desc')
	{
		$app = Factory::getApplication();
		$clientId = $app->getUserStateFromRequest('com_jgive.client_id', 'client_id', '');

		if (!empty($clientId))
		{
			$this->setState('filter.client_id', $clientId);
		}

		// List state information.
		parent::populateState($ordering, $direction);
	}
}
