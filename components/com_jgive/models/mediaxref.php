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
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;


/**
 * Methods supporting a jgive media integration.
 *
 * @since  2.1.0
 */
class JGiveModelMediaXref extends AdminModel
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	 *
	 * @since	2.0
	 */
	public function getTable($type = 'mediaxref', $prefix = 'JGiveTable', $config = array())
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
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jgive.mediaxref', 'mediaxref', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since  2.0
	 */
	public function save($data)
	{
		if (!$data)
		{
			return false;
		}

		$table    = $this->getTable('mediaxref');
		$table->load(array('media_id' => (int) $data['media_id'], 'client_id' => (int) $data['client_id'], 'client' => $data['client']));

		// Do nothing if the uploaded image has an id
		if ($table->id)
		{
			return;
		}

		if ($returnData = parent::save($data))
		{
			return $returnData;
		}

		return false;
	}

	/**
	 * [DEPRECATED METHOD] Use getMedia() instead
	 *
	 * @param   int  $clientId    campaign id
	 *
	 * @param   int  $clientName  clientName
	 *
	 * @param   int  $isGallery   isGallery
	 *
	 * @deprecated since version 2.2.0
	 *
	 * @return array
	 *
	 * @since	2.0
	 */
	public function getCampaignMedia($clientId, $clientName, $isGallery = 0)
	{
		return 	$this->getMedia($clientId, $clientName, $isGallery, $isMultiple = true);
	}

	/**
	 * Method to get a media files from media xref
	 *
	 * @param   int   $clientId    campaign id
	 *
	 * @param   int   $clientName  clientName
	 *
	 * @param   int   $isGallery   isGallery
	 *
	 * @param   bool  $isMultiple  defaults to true
	 *
	 * @return array
	 *
	 * @since	2.0
	 */
	public function getMedia($clientId, $clientName, $isGallery = 0, $isMultiple = true)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// Generating campaign media data
		$query->select($db->quoteName(array('id', 'media_id', 'client_id')));
		$query->from($db->quoteName('#__tj_media_files_xref'));
		$query->where($db->quoteName('client_id') . '=' . (int) $clientId);
		$query->where($db->quoteName('is_gallery') . '=' . (int) $isGallery);
		$query->where($db->quoteName('client') . '=' . $db->quote($clientName));
		$db->setQuery($query);

		if (!$isMultiple)
		{
			return $db->loadObject();
		}

		return $db->loadObjectList();
	}
}
