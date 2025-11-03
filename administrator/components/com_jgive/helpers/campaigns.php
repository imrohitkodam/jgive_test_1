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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Component Helper

/**
 * CampaignHelper
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       2.1
 */
class CampaignsHelper
{
	/**
	 * Used in backend - reports view
	 *
	 * @return  Object List as campaign data
	 */
	public function getAllCampaignOptions()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.id');
		$query->select('c.title');
		$query->from($db->qn('#__jg_campaigns', 'c'));
		$query->order($db->qn('c.title'));
		$db->setQuery($query);
		$campaigns = $db->loadObjectList();

		return $campaigns;
	}

	/**
	 * Get campaign category filter
	 *
	 * @return  Array
	 */
	public function getCampaignsCategories()
	{
		$app         = Factory::getApplication();
		$categories  = HTMLHelper::_('category.options', 'com_jgive', array('filter.published' => array(1)));
		$cat_options = array();

		if ($app->isClient('site') OR JVERSION < 3.0)
		{
			$cat_options[] = HTMLHelper::_('select.option', '0', Text::_('COM_JGIVE_CAMPAIGN_CATEGORIES'));
		}

		if (!empty($categories))
		{
			foreach ($categories as $category)
			{
				if (!empty($category))
				{
					$cat_options[] = HTMLHelper::_('select.option', $category->value, $category->text);
				}
			}
		}

		return $cat_options;
	}

	/**
	 * Get Campaign Success Status Array
	 *
	 * @return  Array
	 */
	public function getCampaignSuccessStatusArray()
	{
		$campaignSuccessStatus   = array();
		$campaignSuccessStatus[] = HTMLHelper::_('select.option', 0, Text::_('COM_JGIVE_SUCCESS_STATUS_ONGOING'));
		$campaignSuccessStatus[] = HTMLHelper::_('select.option', 1, Text::_('COM_JGIVE_SUCCESS_STATUS_SUCCESSFUL'));
		$campaignSuccessStatus[] = HTMLHelper::_('select.option', -1, Text::_('COM_JGIVE_SUCCESS_STATUS_FAILED'));

		return $campaignSuccessStatus;
	}
}
