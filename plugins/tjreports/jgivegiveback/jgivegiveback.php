<?php
/**
 * @package    JGive_GiveBack
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009-2020 Techjoomla. All rights reserved.
 * @license    GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Include TJReport Model
JLoader::import('com_tjreports.models.reports', JPATH_SITE . '/components');

/**
 * Give back report plugin of TJReport
 *
 * @since  _DEPLOY_VERSION_
 */
class TjreportsModelJgivegiveback extends TjreportsModelReports
{
	protected $default_order = 'a.title';

	protected $default_order_dir = 'ASC';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   _DEPLOY_VERSION_
	 */
	public function __construct($config = array())
	{
		$lang     = Factory::getLanguage();
		$base_dir = JPATH_SITE . '/administrator';
		$lang->load('com_jgive', $base_dir);

		$this->columns = array(
			'givebacktitle' => array('table_column' => 'a.title', 'title' => 'PLG_TJREPORTS_JGIVE_GIVEBACK_NAME'),
			'campaign_title' => array('table_column' => 'b.title', 'title' => 'PLG_TJREPORTS_JGIVE_GIVEBACK_CAMPAIGN_NAME', 'disable_sorting' => true),
			'amount' => array('table_column' => 'a.amount', 'title' => 'PLG_TJREPORTS_JGIVE_GIVEBACK_MINIMUM_AMOUNT'),
			'total_quantity' => array('table_column' => 'a.total_quantity', 'title' => 'PLG_TJREPORTS_JGIVE_GIVEBACK_TOTAL_QUANTITY'),
			'quantity' => array('table_column' => 'a.quantity', 'title' => 'PLG_TJREPORTS_JGIVE_GIVEBACK_QUANTITY')
		);

		parent::__construct($config);
	}

	/**
	 * Get client of this plugin
	 *
	 * @return array<string,mixed|string> Plugin Details
	 *
	 * @since   _DEPLOY_VERSION_
	 * */
	public function getPluginDetail()
	{
		return $detail = array('client' => 'com_jgive', 'title' => Text::_('PLG_TJREPORTS_JGIVEGIVEBACK'));
	}

	/**
	 * Create an array of filters
	 *
	 * @return    ARRAY Filters used in reports
	 *
	 * @since    _DEPLOY_VERSION_
	 */
	public function displayFilters()
	{
		$dispFilters = array(
			array(
				'givebacktitle' => array(
					'search_type' => 'text', 'searchin' => 'a.title')
			)
		);

		return $dispFilters;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	protected function getListQuery()
	{
		$user = Factory::getUser();
		$isSuperUser = $user->authorise('core.admin');
		$db        = $this->_db;
		$query     = parent::getListQuery();
		$query->select('a.title, a.amount, a.total_quantity, a.quantity, b.creator_id', 'b.title as campaign_title');
		$query->from($db->quoteName('#__jg_campaigns_givebacks', 'a'));
		$query->join('LEFT', $db->quoteName('#__jg_campaigns', 'b') . ' ON (' . $db->quoteName('b.id') . ' = ' . $db->quoteName('a.campaign_id') . ')');

		if (!$isSuperUser)
		{
			$query->where($db->quoteName('b.creator_id') . ' = ' . (int) $user->id);
		}

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function getItems()
	{
		$items = parent::getItems();

		JLoader::import('components.com_jgive.helper', JPATH_SITE);
		$jGiveFrontendHelper = new JgiveFrontendHelper;

		foreach ($items as $key => $item)
		{
			$items[$key]['amount'] = $jGiveFrontendHelper->getFormattedPrice($item['amount']);
		}

		return $items;
	}
}
