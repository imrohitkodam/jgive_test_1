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
 * JGive GiveBacks Model
 *
 * @since  1.6
 */
class JGiveModelGivebacks extends ListModel
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('g.*'));
		$query->from($db->quoteName('#__jg_campaigns_givebacks', 'g'));

		$app = Factory::getApplication('site');

		$campaign_id = $app->getUserStateFromRequest('com_jgive.client_id', 'client_id', '');

		if ($campaign_id)
		{
			$query->where($db->quoteName('g.campaign_id') . ' = ' . $db->quote($campaign_id));
		}

		return $query;
	}
}
