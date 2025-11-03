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
defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Access\Access;
use Joomla\Utilities\ArrayHelper;
Use Joomla\String\StringHelper;

/**
 * JTable class for Campaign.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.7
 */
class JgiveTablecampaign extends Table
{
	public $alias;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$_db  Database connector object
	 *
	 * @since 1.7
	 */
	public function __construct (&$_db)
	{
		parent::__construct('#__jg_campaigns', 'id', $_db);
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array to bind
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error
	 *
	 * @since   1.7
	 */
	public function bind ($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new Registry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (! Factory::getUser()->authorise('core.admin', 'com_jgive.campaign.' . $array['id']))
		{
			$accessFilePath = JPATH_ADMINISTRATOR . '/components/com_jgive/access.xml';
			$actions = Access::getActionsFromFile($accessFilePath, "/access/section[@name='campaign']/");
			$default_actions = Access::getAssetRules('com_jgive.campaign.' . $array['id'])->getData();

			$array_jaccess = array();

			foreach ($actions as $action)
			{
				$array_jaccess[$action->name] = $default_actions[$action->name];
			}

			$array['rules'] = $this->RulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$this->setRules($array['rules']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * This function convert an array of JAccessRule objects into an rules array.
	 *
	 * @param   type  $jaccessrules  an array of JAccessRule objects.
	 *
	 * @return  mixed  $rules  Set of rules
	 *
	 * @since   1.7
	 */
	private function RulestoArray ($jaccessrules)
	{
		$rules = array();

		foreach ($jaccessrules as $action => $jaccess)
		{
			$actions = array();

			foreach ($jaccess->getData() as $group => $allow)
			{
				$actions[$group] = ((bool) $allow);
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean
	 *
	 * @see     JTable::check
	 *
	 * @since   1.7
	 */
	public function check ()
	{
		$db = Factory::getDbo();
		$this->alias = trim($this->alias);

		if (!$this->alias)
		{
			$this->alias = $this->title;
		}

		if ($this->alias)
		{
			if (Factory::getConfig()->get('unicodeslugs') == 1)
			{
				$this->alias = OutputFilter::stringURLUnicodeSlug($this->alias);
			}
			else
			{
				$this->alias = OutputFilter::stringURLSafe($this->alias);
			}
		}

		// Check if campaign with same alias is present
		$table = Table::getInstance('Campaign', 'JgiveTable', array('dbo', $db));

		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
		{
			$msg = Text::_('COM_JGIVE_SAVE_ALIAS_WARNING');

			while ($table->load(array('alias' => $this->alias)))
			{
				$this->alias = StringHelper::increment($this->alias, 'dash');
			}

			Factory::getApplication()->enqueueMessage($msg, 'warning');
		}
		// If there is an ordering column and this is a new row then get the
		// next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		return parent::check();
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published, 2=archived, -2=trashed]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		if (!empty($pks))
		{
			$where = $k . '=' . implode(' OR ' . $k . '=', $pks);
		}

		// Determine if there is checkin support for the table.
		if (! property_exists($this, 'checked_out') || ! property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		if (is_array($pks))
		{
			$params = ComponentHelper::getParams('com_jgive');
			$admin_approval = (int) $params->get('admin_approval');

			foreach ($pks as $pk)
			{
				$query = $this->_db->getQuery(true);

				// Update the state flag
				$query->update($this->_db->quoteName('#__jg_campaigns'))
					->set($this->_db->quoteName('published') . ' = ' . $state)
					->where($this->_db->quoteName('id') . ' = ' . $pk);

				$this->_db->setQuery($query);

				try
				{
					$this->_db->execute();
				}
				catch (\RuntimeException $e)
				{
					$this->setError($e->getMessage());

					return false;
				}

				if ($state == 1)
				{
					// If admin approval is on for products
					if ($admin_approval === 1)
					{
						$query = $this->_db->getQuery(true);

						$query->select('u.email, u.name, u.username');
						$query->from('#__users As u');

						$query->select(' c.title, c.id, c.vendor_id');
						$query->from('#__jg_campaigns As c');

						$query->where("u.id = c.creator_id AND c.id = " . $pk);

						$this->_db->setQuery($query);
						$camp_info = $this->_db->loadObject();

						JLoader::import('components.com_jgive.events.campaign', JPATH_SITE);
						$jGiveTriggerCampaign = new JGiveTriggerCampaign;
						$jGiveTriggerCampaign->onCampaignStateChange($camp_info, $state);
					}
				}
			}
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin each row.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were. Set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');

		return true;
	}

	/**
	 * Method to toggle the featured setting of products.
	 *
	 * @param   array    $pks    The ids of the items to toggle.
	 * @param   integer  $value  The value to toggle to.
	 *
	 * @return  boolean  True on success.
	 */
	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		ArrayHelper::toInteger($pks);

		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->update($db->quoteName('#__jg_campaigns'))
						->set('featured = ' . (int) $value)
						->where('id IN (' . implode(',', $pks) . ')');
			$query;
			$db->setQuery($query);
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			throw new Exception($e->getMessage());
		}

		return true;
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return string The asset name
	 *
	 * @see JTable::_getAssetName
	 */
	protected function _getAssetName ()
	{
		$k = $this->_tbl_key;

		return 'com_jgive.campaign.' . (int) $this->$k;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID,
	 * which will default to 1 if none exists.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	protected function _getAssetParentId (Table $table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = Table::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_jgive');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}

	/**
	 * Delete a campaign record by id
	 *
	 * @param   mixed  $pk  Primary key value to delete. Optional
	 *
	 * @return bool
	 */
	public function delete($pk = null)
	{
		/* Get list of orders placed for the respective campaign */
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
		$ordersData = Table::getInstance('Orders', 'JGiveTable');
		$ordersData->load(array('campaign_id' => $pk));
		JLoader::register('JGiveModelMediaXrefs', JPATH_SITE . '/components/com_jgive/models/mediaxrefs.php');
		JLoader::register('JGiveModelMedia', JPATH_SITE . '/components/com_jgive/models/media.php');
		JLoader::register('JGiveModelCampaign', JPATH_SITE . '/components/com_jgive/models/campaign.php');
		JLoader::register('JGiveModelGivebacks', JPATH_SITE . '/components/com_jgive/models/givebacks.php');
		JLoader::register('JGiveModelGiveBack', JPATH_SITE . '/components/com_jgive/models/giveback.php');
		JLoader::register('JgiveModelReport', JPATH_SITE . '/components/com_jgive/models/report.php');
		JLoader::register('JgiveModelReports', JPATH_SITE . '/components/com_jgive/models/reports.php');

		/* If no order is placed, then only proceed for deletion*/
		if (empty($ordersData->id))
		{
			// Get all media list
			$mediaXrefsModel = BaseDatabaseModel::getInstance('Mediaxrefs', 'JGiveModel', array('ignore_request' => true));
			$com_params      = ComponentHelper::getParams('com_jgive');
			$storagePath     = $com_params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

			$mediaXrefsModel->setState('filter.client_id', $pk);
			$mediaData = $mediaXrefsModel->getItems();

			foreach ($mediaData as $media)
			{
				// Delete each media record from database and files
				$mediaModel = BaseDatabaseModel::getInstance('Media', 'JGiveModel', array('ignore_request' => true));
				$mediaModel->deleteMedia($media->media_id, $storagePath, $media->client, $media->client_id);
			}

			// Delete extra fields from TJ Fields
			$campaignModel = BaseDatabaseModel::getInstance('Campaign', 'JGiveModel', array('ignore_request' => true));
			$campaignModel->deleteExtraFieldsData($pk, 'com_jgive.campaign');

			// Get all Givebacks
			$givebacksModel = BaseDatabaseModel::getInstance('Givebacks', 'JGiveModel', array('ignore_request' => true));
			$givebackData = $givebacksModel->getItems();

			// Delete all respective givebacks
			foreach ($givebackData as $giveback)
			{
				$givebackModel = BaseDatabaseModel::getInstance('Giveback', 'JGiveModel', array('ignore_request' => true));
				$givebackModel->delete($giveback->id);
			}

			// Get all reports
			$reportsModel = BaseDatabaseModel::getInstance('Reports', 'JgiveModel', array('ignore_request' => true));
			$reportsModel->setState('filter.campaign_id', $pk);
			$reportData = $reportsModel->getItems();

			// Delete all respective record
			foreach ($reportData as $report)
			{
				$reportModel = BaseDatabaseModel::getInstance('Report', 'JgiveModel', array('ignore_request' => true));
				$reportModel->delete($report->id);
			}

			// Campaign deletion
			$this->load($pk);
			$result = parent::delete($pk);

			return $result;
		}

		Factory::getApplication()->enqueueMessage(Text::_('COM_JGIVE_CAMPAIGN_CANNOT_BE_DELETED'), 'error');

		return false;
	}
}
