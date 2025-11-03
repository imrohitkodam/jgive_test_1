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

use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

// Add Table Path
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_jgive/tables');

$helperPath = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

if (!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath);
	JLoader::load('campaignHelper');
}

/**
 * Routing class from com_jgive
 *
 * @subpackage  com_jgive
 *
 * @since       2.0
 */
class JGiveRouter extends RouterBase
{
	private $views = array('campaigns', 'dashboard', 'donations', 'donors', 'reports');

	private $views_needing_campaignId = array('campaign');

	private $views_needing_donationId = array('donations');

	/**
	 * Build the route for the com_content component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   1.7.1
	 */
	public function build(&$query)
	{
		$segments = array();

		// Get a menu item based on Itemid or currently active
		$app = Factory::getApplication();
		$menu = $app->getMenu();
		$params = ComponentHelper::getParams('com_jgive');
		$db = Factory::getDbo();

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
			unset($query['Itemid']);
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_jgive')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		// Check if view is set.
		if (isset($query['view']))
		{
			$view = $query['view'];
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}

		if ($view == 'dashboard' || $view == 'reports' || $view == 'campaignform' || $view == 'registration')
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}
		elseif ($view == 'donors')
		{
			$segments[] = $query['view'];
			unset($query['view']);

			if (isset($query['layout']))
			{
				$segments[] = $query['layout'];
				unset($query['layout']);
			}
		}
		elseif($view == 'campaign')
		{
			if (isset($query['id']))
			{
				$campaignTable = $this->_getCampaignRow($query['id'], 'id');
				$segments[] = $campaignTable->alias;
				unset($query['id']);
			}

			unset($query['view']);
		}
		elseif ($view == 'reportform')
		{
			if (isset($query['cid']))
			{
				$campaignTable = $this->_getCampaignRow($query['cid'], 'id');
				$segments[] = $campaignTable->alias;
				unset($query['cid']);
			}

			if (isset($query['id']))
			{
				$segments[] = 'edit-report';
				unset($query['view']);

				$segments[] = $query['id'];
				unset($query['id']);
			}
			else
			{
				$segments[] = 'add-report';
				unset($query['view']);
			}
		}
		elseif ($view == 'report')
		{
			$segments[] = 'report';
			unset($query['view']);

			if (isset($query['id']))
			{
				$segments[] = $query['id'];
				unset($query['id']);
			}
		}
		elseif ($view == 'donation')
		{
			unset ($query['layout']);

			$segments[] = 'paymentDetails';
			$segments[] = $query['donationid'];
			unset($query['view']);
			unset ($query['donationid']);
		}
		elseif($view == 'donations')
		{
			if (isset($query['layout']))
			{
				if ($query['layout'] == 'payment')
				{
					$segments[] = $query['view'];
					$segments[] = $query['layout'];
					$segments[] = $query['cid'];
					unset($query['view']);
					unset ($query['layout']);
					unset ($query['cid']);
				}
				elseif ($query['layout'] == 'all_donations')
				{
					$segments[] = $query['view'];
					$segments[] = $query['layout'];
					unset($query['view']);
					unset ($query['layout']);
				}
				elseif ($query['layout'] == 'default')
				{
					unset($query['view']);
					unset ($query['layout']);
				}
			}
			else
			{
				unset($query['view']);
			}
		}
		elseif($view == 'campaigns')
		{
			if ($query['layout'] == 'all')
			{
				$segments[] = $query['view'];
				$segments[] = $query['layout'];
			}

			if (!empty($query['filter_campaign_cat']))
			{
				$catId = (int) $query['filter_campaign_cat'];

				if ($catId)
				{
					$campaignHelper = new campaignHelper;
					$alias          = $campaignHelper->getCatalias($query['filter_campaign_cat']);
					$segments[]     = $alias;
					unset($query['filter_campaign_cat']);
					unset($query['view']);
				}
				else
				{
					$segments[] = '';
					unset($query['filter_campaign_cat']);
					unset($query['view']);
				}
			}

			unset($query['view']);
			unset ($query['layout']);
		}
		elseif ($view == 'individuals' || $view == 'organizations')
		{
			$segments[] = $view;
			unset($query['view']);
		}
		elseif ($view == 'individualform' || $view == 'organizationform')
		{
			$segments[] = $view;
			unset($query['view']);

			if ($query['layout'] == 'default')
			{
				$segments[] = $query['layout'];
				unset($query['layout']);
			}
		}

		/* Handle layouts*/
		if (isset($query['layout']) && $query['layout'] == 'default')
		{
			unset($query['layout']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   2.0
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$db = Factory::getDbo();

		// Count route segments
		$count = count($segments);

		if ($count == 1)
		{
			if ($campaignId = $this->_getCampaignRow($segments[0])->id)
			{
				$vars['view'] = 'campaign';
				$vars['layout'] = 'default';
				$vars['id'] = $campaignId;
			}

			if ($segments[0] == 'campaignform')
			{
				$vars['view'] = 'campaignform';
				$vars['layout'] = 'default';
			}

			if ($segments[0] == 'donors')
			{
				$vars['view'] = 'donors';
				$vars['layout'] = 'default';
			}

			if ($segments[0] == 'contact_us')
			{
				$vars['view'] = 'donors';
				$vars['layout'] = $segments[0];
			}

			if ($segments[0] == 'dashboard')
			{
				$vars['view'] = 'dashboard';
				$vars['layout'] = 'default';
			}

			// Remove the segment as its parsed - Required for Joomla 4
			array_shift($segments);
		}
		else
		{
			if ($segments[0] == 'donations')
			{
				$view = array_shift($segments);
				$vars['view'] = $view;

				$layout = array_shift($segments);
				$vars['layout'] = $layout;

				if ($layout == 'details')
				{
					$donationId = array_shift($segments);
					$vars['donationid'] = $donationId;
				}

				if ($layout == 'payment')
				{
					$cid = array_shift($segments);
					$vars['cid'] = $cid;
				}
			}
			elseif ($segments[0] == 'paymentDetails')
			{
				array_shift($segments);
				$vars['view'] = 'donation';

				$donationId = array_shift($segments);

				if (!empty($donationId))
				{
					$vars['donationid'] = $donationId;
				}

				$vars['layout'] = 'default';
			}
			elseif ($segments[0] == 'campaigns')
			{
				$view = array_shift($segments);
				$vars['view']   = $view;

				array_shift($segments);
				$vars['layout'] = 'all';

				$id = array_shift($segments);

				if (isset($id))
				{
					if (JVERSION < '4.0.0')
					{
						Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
						$categoryTable = Table::getInstance('Category', 'CategoriesTable', array('dbo', $db));
					}
					else
					{
						$categoryTable = Table::getInstance('CategoryTable', '\\Joomla\\Component\\Categories\\Administrator\\Table\\');
					}

					$categoryTable->load(array('alias' => $id, 'extension' => 'com_jgive'));

					if ($categoryTable->id)
					{
						$vars['filter_campaign_cat'] = $categoryTable->id;
					}
					else
					{
						$vars['filter_campaign_cat'] = '';
					}
				}
			}
			elseif ($segments[1] == 'add-report')
			{
				$campaignId = array_shift($segments);
				$view = array_shift($segments);

				$vars['view'] = 'reportform';

				if ($campaignId = $this->_getCampaignRow($campaignId)->id)
				{
					$vars['cid'] = $campaignId;
				}

				$vars['layout'] = 'default';
			}
			elseif ($segments[1] == 'edit-report')
			{
				$campaignId = array_shift($segments);
				$view = array_shift($segments);

				if ($campaignId = $this->_getCampaignRow($campaignId)->id)
				{
					$vars['cid'] = $campaignId;
				}

				$vars['view'] = 'reportform';

				$reportId = array_shift($segments);

				if (isset($reportId))
				{
					$vars['id'] = $reportId;
				}

				$vars['layout'] = 'default';
			}
			elseif ($segments[0] == 'report')
			{
				$view = array_shift($segments);
				$vars['view'] = $view;

				$id = array_shift($segments);
				$vars['id'] = $id;

				$vars['layout'] = 'default';
			}
			elseif ($segments[0] == 'donors' || $segments[0] == 'individualform' || $segments[0] == 'organizationform')
			{
				$view = array_shift($segments);
				$vars['view'] = $view;

				$layout = array_shift($segments);
				$vars['layout'] = empty($layout) ? 'default' : $layout;
			}
		}

		return $vars;
	}

	/**
	 * Get campaign row based on alias or id
	 *
	 * @param   mixed   $campaign  The id or alias of the campaign to be loaded
	 * @param   string  $input     The field to match to load the product
	 *
	 * @return  object  The product JTable object
	 */
	private function _getCampaignRow($campaign, $input = 'alias')
	{
		$db = Factory::getDbo();
		$table = Table::getInstance('campaign', 'JgiveTable', array('dbo', $db));
		$table->load(array($input => $campaign));

		return $table;
	}
}
