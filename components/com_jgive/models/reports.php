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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);

/**
 * Reports list class
 *
 * @package  JGive
 * @since    2.2.0
 */
class JgiveModelReports extends ListModel
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   2.2.0
	 */
	public function getTable($type = 'Report', $prefix = 'JGiveTable', $config = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient("administrator"))
		{
			return Table::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');

			return Table::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get a object for retrieving the data set from a database
	 *
	 * @return  Object
	 *
	 * @since   2.2.0
	 */
	public function getListQuery()
	{
		$listStart  = $this->getState('list.start');
		$listLimit  = $this->getState('list.limit');
		$campaignId = $this->getState('filter.campaign_id');
		$ordering   = $this->getState('list.ordering');
		$direction  = $this->getState('list.direction');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array(
					'jgr.id',
					'jgr.campaign_id',
					'jgr.title',
					'jgr.description',
					'jgr.status',
					'jgr.created_by',
					'jgr.created_on',
					'mfx.media_id'
				)
			)
		);
		$query->from($db->quoteName('#__jg_reports', 'jgr'));
		$query->join(
						'LEFT', $db->quoteName('#__tj_media_files_xref', 'mfx') .
						' ON (' . $db->quoteName('jgr.id') . ' = ' . $db->quoteName('mfx.client_id') . ')' . ' AND ' .
						$db->quoteName('mfx.client') . '=' . $db->quote('com_jgive.reports')
				);
		$query->where($db->quoteName('jgr.campaign_id') . '=' . (int) $campaignId);
		$query->order($ordering . ' ' . $direction);

		if (!empty($ordering) && !empty($direction))
		{
			$query->order($ordering . ' ' . $direction);
		}

		if ($listStart > 0)
		{
			$query->setLimit($listLimit, $listStart);
		}

		return $query;
	}

	/**
	 * Method populateState
	 *
	 * @param   string  $ordering   Ordering
	 * @param   string  $direction  Direction
	 *
	 * @return  void
	 *
	 * @since	2.2.0
	 */
	public function populateState($ordering = 'jgr.id', $direction = 'DESC')
	{
		$this->setState('list.start', 0);
		$this->setState('list.limit', 5);

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   2.2.0
	 */
	public function getItems()
	{
		$getItems = parent::getItems();

		if (!empty($getItems))
		{
			$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');
			JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
			$jgiveParams     = ComponentHelper::getParams('com_jgive');
			$reportImagePath = $jgiveParams->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

			foreach ($getItems as &$report)
			{
				$mediaXrefData  = (object) $modelMediaXref->getMedia($report->id, 'com_jgive.reports', 0, false);
				$report->source = null;

				if (isset($mediaXrefData->media_id))
				{
					$filetable       = Table::getInstance('Files', 'TJMediaTable');
					$filetable->load($mediaXrefData->media_id);
					$mediaType       = explode(".", $filetable->type);
					$imgPath         = $reportImagePath . '/' . $mediaType[0] . 's';
					$mediaConfig     = array('id' => $mediaXrefData->media_id, 'uploadPath' => $imgPath);
					$report->source  = TJMediaStorageLocal::getInstance($mediaConfig);
				}
			}
		}

		return $getItems;
	}
}
