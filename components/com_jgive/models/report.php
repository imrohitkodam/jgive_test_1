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

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);
/**
 * Reports details view model class
 *
 * @package  JGive
 * @since    2.2.0
 */
class JgiveModelReport extends AdminModel
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  boolean|JTable  A JTable object
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
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   2.2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jgive.report', 'report',
			array(
				'control'   => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the media files by report id
	 *
	 * @param   int  $reportId  Report id
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   2.2.0
	 */
	public function getMedia($reportId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('mf.source'));
		$query->from($db->quoteName('#__tj_media_files_xref', 'mfx'));
		$query->join(
			'LEFT',
			$db->quoteName('#__tj_media_files', 'mf') . ' ON (' . $db->quoteName('mfx.media_id') . ' = ' . $db->quoteName('mf.id') . ')'
		);
		$query->where($db->quoteName('mfx.client_id') . '=' . (int) $reportId);
		$query->where($db->quoteName('mfx.client') . '=' . $db->quote('com_jgive.reports'));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get campaign data.
	 *
	 * @param   integer  $pk  The id of the campaign.
	 *
	 * @return  object|boolean|JException  Menu item data object on success, boolean false or JException instance on error
	 */
	public function getItem($pk = null)
	{
		$item = (object) parent::getItem($pk);

		if (!empty($item))
		{
			JLoader::register('JgiveModelReportform', JPATH_SITE . '/components/com_jgive/models/reportform.php');
			$jgiveModelReportform = BaseDatabaseModel::getInstance('reportform', 'JgiveModel');
			$reportData           = $jgiveModelReportform->getItem($item->id);

			// Fetching Report main image
			if (!empty($reportData->mediaData))
			{
				$item->reportImage = $reportData->mediaData;
			}

			// Fetching Report Attachments
			$mediaXrefLib = TJMediaXref::getInstance();
			$mediaXrefAttachmentData = $mediaXrefLib->retrive(
																$data = array(
																				'clientId' => $item->id,
																				'client' => 'com_jgive.reportAttachment',
																				'isGallery' => 0
																			)
															);

			if (!empty ($mediaXrefAttachmentData))
			{
				$item->mediaAttachmentData = $mediaXrefAttachmentData;
			}
		}

		return $item;
	}
}
