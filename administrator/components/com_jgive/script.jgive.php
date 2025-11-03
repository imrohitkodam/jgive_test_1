<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Access denied');

use Joomla\Data\DataObject;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * JgiveInstallerScript class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.0
 */
class com_jgiveInstallerScript
{
	/** @var array Obsolete files and folders to remove*/
	private $removeFilesAndFolders = array(
		'files'	=> array(
			'administrator/components/com_jgive/admin.jgive.php',
			'administrator/components/com_jgive/views/cp/tmpl/default.php',
			'administrator/components/com_jgive/views/campaigns/tmpl/all_list.php',
			'components/com_jgive/views/campaigns/tmpl/all_list.xml',
			'components/com_jgive/views/donations/tmpl/all.xml',
			'components/com_jgive/views/campaign/tmpl/create_updates.php',
			'components/com_jgive/views/campaigns/tmpl/filters.php',
			'components/com_jgive/views/campaigns/tmpl/pin.php',
			'jgive.log',
			/*Since v2.1*/
			'administrator/components/com_jgive/models/ending_camp.php',
			'administrator/components/com_jgive/models/email_template.php',
			'administrator/components/com_jgive/controllers/ending_camp.php',
			'administrator/components/com_jgive/controllers/email_template.php',
			'administrator/components/com_jgive/helpers/campaign.php',
			'administrator/components/com_jgive/views/campaigns/tmpl/ending_camp.php',
			'administrator/components/com_jgive/views/donors/tmpl/default_filter.php',
			'administrator/components/com_jgive/views/campaign/tmpl/create.php',
			'administrator/components/com_jgive/views/campaign/tmpl/create_extrafields.php',
			'administrator/components/com_jgive/views/campaign/tmpl/create_video_clone.php',
			'administrator/components/com_jgive/views/campaign/tmpl/create_videos.php',
			'administrator/components/com_jgive/views/campaign/tmpl/create_video_uploaded.php',
			'administrator/components/com_jgive/views/reports/tmpl/edit_payout.php',
			'administrator/components/com_jgive/views/reports/tmpl/payouts.php',
			'administrator/components/com_jgive/template/default_email_template.php',
			'media/com_jgive/css/jgive_updates.css',
			'media/com_jgive/css/email_template.css',
			'media/com_jgive/css/tjdashboard.css',
			'media/com_jgive/css/jgive-dashboard.css',
			'media/com_jgive/css/jgive_reports.css',
			'media/com_jgive/vendors/jqueryui/jquery.ui.all.css',
			'media/com_jgive/vendors/jqueryui/jquery.ui.autocomplete.js',
			'media/com_jgive/vendors/jqueryui/jquery.ui.core.js',
			'media/com_jgive/vendors/jqueryui/jquery.ui.position.js',
			'media/com_jgive/vendors/jqueryui/jquery.ui.widget.js',
			'media/com_jgive/vendors/js/jquery-1.8.0.min.js',
			'media/com_jgive/vendors/js/Chart.js',
			'media/com_jgive/javascript/donations_admin.js',
			'media/com_jgive/javascript/jgive_layouts.js',
			'media/com_jgive/javascript/create_camp.js',
			'media/com_jgive/javascript/donations.js',
			'media/com_jgive/javascript/fields_validation.js',
			'media/com_jgive/fields/bssetup.php',
			/*'components/com_jgive/models/reports.php',*/
			'components/com_jgive/views/campaign/create.php',
			'components/com_jgive/views/campaign/tmpl/create.php',
			'components/com_jgive/views/campaign/tmpl/create.xml',
			'components/com_jgive/views/campaign/tmpl/create_extrafields.php',
			'components/com_jgive/views/campaign/tmpl/create_images.php',
			'components/com_jgive/views/campaign/tmpl/create_media.php',
			'components/com_jgive/views/campaign/tmpl/create_otheropt.php',
			'components/com_jgive/views/campaign/tmpl/create_video_clone.php',
			'components/com_jgive/views/campaign/tmpl/create_videos.php',
			'components/com_jgive/views/campaign/tmpl/create_video_uploaded.php',
			'components/com_jgive/views/campaign/tmpl/single.php',
			'components/com_jgive/views/campaign/tmpl/single.xml',
			'components/com_jgive/views/campaign/tmpl/single_activity.php',
			'components/com_jgive/views/campaign/tmpl/single_donors.php',
			'components/com_jgive/views/campaign/tmpl/single_donorslist.php',
			'components/com_jgive/views/campaign/tmpl/single_extrafields.php',
			'components/com_jgive/views/campaign/tmpl/single_gallary.php',
			'components/com_jgive/views/campaign/tmpl/single_image.php',
			'components/com_jgive/views/campaign/tmpl/single_playvideo.php',
			'components/com_jgive/views/campaign/tmpl/single_video.php',
			'components/com_jgive/views/reports/view.html.php',
			'components/com_jgive/views/reports/metadata.xml',
			'components/com_jgive/views/reports/index.html',
			'components/com_jgive/views/reports/tmpl/mypayouts.php',
			'components/com_jgive/views/reports/tmpl/mypayouts.xml',
			'components/com_jgive/views/reports/tmpl/index.html',
			/*Since v2.2.0*/
			'components/com_jgive/views/donations/tmpl/my.xml',
			'components/com_jgive/views/donations/tmpl/my.php',
			'components/com_jgive/views/donations/tmpl/all.php',
			'administrator/components/com_jgive/views/donations/tmpl/all.php',
			'components/com_jgive/models/donatevalidate.php',
			/*Since v2.3.4*/
			'components/com_jgive/models/forms/campaign.xml',
			'components/com_jgive/helpers/jgive.php',
			/*Since v2.5.0*/
			'administrator/components/com_jgive/views/campaign/tmpl/edit_promotor.php',
			'components/com_jgive/views/campaignform/tmpl/default_promotor.php',
			'plugins/api/jgive/jgive/donation.php',
			'components/com_jgive/models/forms/individualform.xml',
			'components/com_jgive/models/forms/organisationform.xml',
			'components/com_jgive/models/organisationform.php',
			'components/com_jgive/includes/organisation.php',
		),
		'folders' => array(
			'administrator/components/com_jgive/assets',
			'components/com_jgive/assets',
			'components/com_jgive/views_bs2',
			/*Since v2.1*/
			'administrator/components/com_jgive/views/ending_camp',
			'administrator/components/com_jgive/views/email_template',
			'components/com_jgive/views/reports',
			'media/com_jgive/classes',
			'media/com_jgive/scripts',
			/*Since v2.1.7*/
			'components/com_jgive/layouts/corefilters/vertical/bs2',
			'components/com_jgive/layouts/corefilters/horizontal/bs2',
			'media/com_activitystream/themes/campaignfeed',
			'media/com_activitystream/themes/dashboardfeed',
			/*Since v2.5.0*/
			'plugins/tjreports/jgivedonororganisation'
		)
	);

	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   string      $type    install, update or discover_update
	 *
	 * @param   JInstaller  $parent  Parent variable
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string      $type    install, update or discover_update
	 *
	 * @param   JInstaller  $parent  Parent variable
	 *
	 * @return  void
	 */
	public function postflight($type, $parent)
	{
		// Remove obsolete files and folders
		$removeFilesAndFolders = $this->removeFilesAndFolders;
		$this->_removeObsoleteFilesAndFolders($removeFilesAndFolders);

		// Add default permissions
		$this->deFaultPermissionsFix();

		// Insert the required data for field manager
		$this->_installSampleFieldsManagerData();

		// Write template file for email template
		$this->_insertTjNotificationTemplates();

		// Add Uncategorised __categories in #__categories table
		$this->addUncategorisedCat();

		// Delete logs file
		$this->deleteLog();

		// Set default layouts to load
		$this->setDefaultLayout($type);

		// Need to verify We have written that code removes duplicate menu entry from table
		$this->menuItemMigration();
	}

	/**
	 * Set default bootstrap layouts to load
	 *
	 * @param   string  $type  install, update or discover_update
	 * 
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function setDefaultLayout($type)
	{
		if ($type == 'install' && JVERSION >= '4.0.0')
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__extensions'));
			$query->where($db->quoteName('type') . ' = ' . $db->quote('component'));
			$query->where($db->quoteName('element') . ' = ' . $db->quote('com_jgive'));
			$db->setQuery($query);
			$data = $db->loadObject();

			$params = json_decode($data->params);

			if (!empty($params) && isset($params->bootstrap_version))
			{
				$query = $db->getQuery(true);
				$params->bootstrap_version = 'bs5';
				$fields = array($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
				$conditions = array($db->quoteName('extension_id') . ' = ' . $data->extension_id);
				$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * Delete Payment plugin logs on installation
	 *
	 * @return void
	 *
	 * @since 2.0.5
	 */
	public function deleteLog()
	{
		// Get log config path
		$config = new JConfig;

		$databasefilepath = Uri::root(true) . '/administrator/components/com_jgive/sql/install.sql';
		file_put_contents($databasefilepath . '/.htaccess', "Deny from all");

		$logsPath = array(
		"cpg__paypal.log",
		"com_jgive_paypal.log",
		"com_jgive_authorizenet.log",
		"com_jgive_amazon.log",
		"com_jgive_2checkout.log",
		"com_jgive_blank.log",
		"com_jgive_adaptive_paypal.log",
		"com_jgive_alphauserpoints.log",
		"com_jgive_bycheck.log",
		"com_jgive_byorder.log",
		"com_jgive_jomsocialpoints.log",
		"com_jgive_linkpoint.log",
		"com_jgive_payu.log",
		"com_jgive_ccavenue.log",
		"com_jgive_easysocialpoints.log",
		"com_jgive_cod.log",
		"com_jgive_code.log",
		"com_jgive_epaydk.log",
		"com_jgive_ewallet.log",
		"com_jgive_eway.log",
		"com_jgive_ewayrapid3.log",
		"com_jgive_ogone.log",
		"com_jgive_pagseguro.log",
		"com_jgive_payfast.log",
		"com_jgive_paymill.log",
		"com_jgive_paypalpro.log",
		"com_jgive_payumoney.log",
		"com_jgive_razorpay.log",
		"com_jgive_transfirst.log");

		foreach ($logsPath as $path)
		{
			// 1. detele log
			if (!File::delete($config->log_path . '/' . $path))
			{
				// 2. if not 1 then clear content
				$output -= file_put_contents($config->log_path . '/' . $path, "");

				// 3. if not 2 then create htaccess
				if ($output == false)
				{
					file_put_contents($config->log_path . '/.htaccess', "Deny from all");
				}
			}
		}

		// Delete log files which was created in Payment gateway plugin folder.
		$oldLogs = array(
			'authorizenet/authorizenet/com_jgive_authorizenet.log',
			'ogone/ogone/ogone_ogone.log',
			'adaptive_paypal/adaptive_paypal/com_jgive_adaptive_paypal.log',
			'adaptive_paypal/adaptive_paypal/logBeforePayment_.log',
			'adaptive_paypal/adaptive_paypal/logBeforePayment_com_jgive.log',
			'authorizenet/authorizenet/com_jgive_authorizenet.log',
			'bycheck/bycheck/com_jgive_bycheck.log',
			'byorder/byorder/com_jgive_byorder.log',
			'jomsocialpoints/jomsocialpoints/com_jgive_jomsocialpoints.log',
			'ogone (copy)/ogone/_old.log',
			'ogone (copy)/ogone/com_jgive_ogone_old.log',
			'ogone (copy)/ogone/com_jgive_ogone.log'
		);

		foreach ($oldLogs as $path)
		{
			$filePath = JPATH_SITE . '/plugins/payment/' . $path;

			// 1. detele log
			if (!File::delete($filePath))
			{
				// 2. if not 1 then clear content
				$output -= file_put_contents($filePath, "");
			}
		}
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array  $removeFilesAndFolders  Contain File and folter which has to remove
	 *
	 * @return void
	 */
	private function _removeObsoleteFilesAndFolders($removeFilesAndFolders)
	{
		// Remove files

		if (!empty($removeFilesAndFolders['files']))
		{
			foreach ($removeFilesAndFolders['files'] as $file)
			{
				$f = JPATH_ROOT . '/' . $file;

				if (!File::exists($f))
				continue;

				File::delete($f);
			}
		}

		// Remove folders

		if (!empty($removeFilesAndFolders['folders']))
		{
			foreach ($removeFilesAndFolders['folders'] as $folder)
			{
				$f = JPATH_ROOT . '/' . $folder;

				if (!file_exists($f))
				continue;

				Folder::delete($f);
			}
		}
	}

	/**
	 * method to install the component
	 *
	 * @param   class  $parent  It is a class
	 *
	 * @return void
	 */
	public function install($parent)
	{
		// $parent is the class calling this method
		$this->setJGiveDefaultBavior();
	}

	/**
	 * method to uninstall the component
	 *
	 * @param   class  $parent  It is a class
	 *
	 * @return void
	 */
	public function uninstall($parent)
	{
		// $parent is the class calling this method
	}

	/**
	 * method to update the component
	 *
	 * @param   class  $parent  It is a class
	 *
	 * @return void
	 */
	public function update($parent)
	{
		// Create core tables
		$this->runSQL($parent, 'install.sql');

		// Since version 1.0.2
		$this->fix_db_on_update();

		// Added this for tag
		$this->setJGiveDefaultBavior();

		$db = Factory::getDbo();
		$config = Factory::getConfig();
		$configdb = $config->get('db');

		// Get dbprefix
		$dbprefix = $config->get('dbprefix');
	}

	/**
	 * method to setJGiveDefaultBavior
	 *
	 * @return void
	 */
	public function setJGiveDefaultBavior()
	{
		$user                    = Factory::getUser();
		$db  = Factory::getDbo();

		// Check if tag exists
		$sql = $db->getQuery(true)->select($db->qn('type_id'))
			->from($db->qn('#__content_types'))
			->where($db->qn('type_title') . ' = ' . $db->q('JGive Category'))
			->where($db->qn('type_alias') . ' = ' . $db->q('com_jgive.category'));
		$db->setQuery($sql);
		$type_id = $db->loadResult();

		// Create tag
		$db                                 = Factory::getDbo();
		$tagobject                          = new stdclass;
		$tagobject->type_id                 = '';
		$tagobject->type_title              = 'JGive Category';
		$tagobject->type_alias              = 'com_jgive.category';
		$tagobject->table                   = '{"special":{"dbtable":"#__categories","key":"id","type":"CategoryTable",'
		. '"prefix":"Joomla\\\\Component\\\\Categories\\\\Administrator\\\\Table\\\\","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"Joomla\\\\CMS\\\\Table\\\\","config":"array()"}}';
		$tagobject->rules                   = '';

		$field_mappings_arr = array(
		'common' => array(
					"core_content_item_id" => "id",
					"core_title" => "title",
					"core_state" => "state",
					"core_alias" => "alias",
					"core_created_time" => "created",
					"core_modified_time" => "modified",
					"core_body" => "description",
					"core_hits" => "null",
					"core_publish_up" => "start_date",
					"core_publish_down" => "end_date",
					"core_access" => "access",
					"core_params" => "params",
					"core_featured" => "featured",
					"core_metadata" => "null",
					"core_language" => "null",
					"core_images" => "image",
					"core_urls" => "null",
					"core_version" => "null",
					"core_ordering" => "ordering",
					"core_metakey" => "metakey",
					"core_metadesc" => "metadesc",
					"core_catid" => "cat_id",
					"core_xreference" => "null",
					"asset_id" => "asset_id"
				),
		'special' => array(
					"parent_id" => "parent_id",
					"lft" => "lft",
					"rgt" => "rgt",
					"level" => "level",
					"path" => "path",
					"path" => "path",
					"extension" => "extension",
					"extension" => "extension",
					"note" => "note"
					)
		);

		$tagobject->field_mappings          = json_encode($field_mappings_arr);
		$tagobject->router                  = 'ContentHelperRoute::getCategoryRoute';

		$content_history_options_arr = '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml","hideFields":["asset_id","checked_out","checked_out_time",'
		. '"version","lft","rgt","level","path","extension"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out","checked_out_time", "version", '
		. '"hits", "path"],"convertToInt":["publish_up", "publish_down"],"displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users", '
		. '"targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id",'
		. '"targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}';

		$tagobject->content_history_options = $content_history_options_arr;

		if (!$type_id)
		{
			if (!$db->insertObject('#__content_types', $tagobject, 'type_id'))
			{
				echo $db->stderr();

				return false;
			}
		}
		else
		{
			$tagobject->type_id = $type_id;

			if (!$db->updateObject('#__content_types', $tagobject, 'type_id'))
			{
				echo $db->stderr();

				return false;
			}
		}

		/** @var JTableContentType $table */
		$table	= Table::getInstance('contenttype');

		if ($table)
		{
			$table->load(array('type_alias' => 'com_jgive.category'));

			if (!$table->type_id)
			{
				$data	= array(
					'type_title'		=> 'JGive Category',
					'type_alias'		=> 'com_jgive.category',
					'table'				=> '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"Joomla\\\\Component\\\\Categories\\\\Administrator\\\\Table\\\\","config":"array()"},'
					. '"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"Joomla\\\\CMS\\\\Table\\\\","config":"array()"}}',
					'rules'				=> '',
					'field_mappings'	=> '
					{"common":{
					"core_content_item_id":"id",
					"core_title":"title",
					"core_state":"published",
					"core_alias":"alias",
					"core_created_time":"created_time",
					"core_modified_time":"modified_time",
					"core_body":"description",
					"core_hits":"hits",
					"core_publish_up":"null",
					"core_publish_down":"null",
					"core_access":"access",
					"core_params":"params", "core_featured":"null",
					"core_metadata":"metadata", "core_language":"language",
					"core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey",
					"core_metadesc":"metadesc", "core_catid":"parent_id",
					"core_xreference":"null", "asset_id":"asset_id"},
					"special": {
					"parent_id":"parent_id",
					"lft":"lft",
					"rgt":"rgt",
					"level":"level",
					"path":"path",
					"extension":"extension",
					"note":"note"
					}
					}',
					'content_history_options' => '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml",
					"hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"],

					"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],

					"convertToInt":["publish_up", "publish_down"],
	"displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},
					{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},
					{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},
					{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}',
				);

				$table->bind($data);

				if ($table->check())
				{
					$table->store();
				}
			}
		}

		// Create default category on installation if not exists
		$sql = $db->getQuery(true)->select($db->quoteName('id'))
			->from($db->quoteName('#__categories'))
			->where($db->quoteName('extension') . ' = ' . $db->quote('com_jgive'));

		$db->setQuery($sql);
		$cat_id = $db->loadResult();

		if (empty($cat_id))
		{
			$catobj        = new stdClass;
			$catobj->title = 'Uncategorised';
			$catobj->alias = 'uncategorised';

			$catobj->extension = "com_jgive";
			$catobj->path      = "uncategorised";
			$catobj->parent_id = 1;
			$catobj->level     = 1;
			$catobj->created_user_id = $user->id;
			$catobj->language        = "*";
			$catobj->description     = '<p>This is a default JGive category</p>';

			$catobj->published = 1;
			$catobj->access    = 1;
			$catobj->created_time = Factory::getDate()->toSql();
			$catobj->modified_time = Factory::getDate()->toSql();

			if (!$db->insertObject('#__categories', $catobj, 'id'))
			{
				echo $db->stderr();

				return false;
			}
		}
	}

	public function runSQL($parent,$sqlfile)
	{
		$db = Factory::getDbo();

		// Obviously you may have to change the path and name if your installation SQL file ;)
		if(method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/backend/sql/' . $sqlfile;
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/' . $sqlfile;
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);

		if ($buffer !== false)
		{
			$queries = \JDatabaseDriver::splitSql($buffer);

			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query[0] != '#')
					{
						$db->setQuery($query);

						if (!$db->execute())
						{
							JError::raiseWarning(1, Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

							return false;
						}
					}
				}
			}
		}
	}
	// End run sql

	// Since version 1.0.2
	public function fix_db_on_update()
	{
		$db = Factory::getDbo();
		$config = Factory::getConfig();
		$dbprefix = $config->get('dbprefix');

		/*Since version 1.0.2
		Check if column - type exists*/
		$query = "SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'type' ";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE  `#__jg_campaigns` ADD  `type` VARCHAR( 50 ) NOT NULL DEFAULT 'donation' AFTER  `modified`";
			$db->setQuery($query);

			if ( !$db->execute() )
			{
				JError::raiseError(500, $db->stderr());
			}
		}

		/*since version 1.0.2
		check if column - max_donors exists*/
		$query = "SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'max_donors' ";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE  `#__jg_campaigns` ADD  `max_donors` INT( 11 ) NOT NULL DEFAULT '0' AFTER  `type`";
			$db->setQuery($query);

			if ( !$db->execute() )
			{
				JError::raiseError( 500, $db->stderr() );
			}
		}

		/*since version 1.0.3
		check if column - type exists*/
		$query = "SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'minimum_amount' ";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE  `#__jg_campaigns` ADD  `minimum_amount` INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'minimum amount for transaction' AFTER  `max_donors`";
			$db->setQuery($query);

			if ( !$db->execute() )
			{
				JError::raiseError(500, $db->stderr());
			}
		}

		// Since version 1.9.5
		// Check if column - start_date exists
		$query = "SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'start_date' ";
		$db->setQuery($query);
		$check = $db->loadResult();

		if ($check)
		{
			$query = "ALTER TABLE  `#__jg_campaigns` MODIFY COLUMN  `start_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00'";
			$db->setQuery($query);

			if (!$db->execute())
			{
				JError::raiseError(500, $db->stderr());
			}
		}

		/* Since version 1.9.5
		Check if column - end_date exists*/
		$query = "SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'end_date' ";
		$db->setQuery($query);
		$check = $db->loadResult();

		if($check)
		{
			$query = "ALTER TABLE  `#__jg_campaigns` MODIFY COLUMN  `end_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00'";
			$db->setQuery($query);

			if (!$db->execute())
			{
				JError::raiseError(500, $db->stderr());
			}
		}

		/*since version 1.0.3
		check if column - order_id exists*/
		$query = "SHOW COLUMNS FROM #__jg_orders WHERE `Field` = 'order_id' ";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE `#__jg_orders` ADD `order_id` VARCHAR( 23 ) NOT NULL AFTER `id`";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		/*since version 1.0.3
		check if column - order_id exists*/
		$query = "SHOW COLUMNS FROM #__jg_orders WHERE `Field` = 'fund_holder'";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE `#__jg_orders` ADD `fund_holder` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT 'To whose account money was originally transferred to: 0-admin, 1-campaign promoter' AFTER `donor_id`";
			$db->setQuery($query);

			if (!$db->execute())
			{
				JError::raiseError(500, $db->stderr());
			}
		}

		//since version 1.5
		//check if column - featured exists
		$query = "SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'featured' ";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE `#__jg_campaigns` ADD `featured` TINYINT( 3 ) NOT NULL default '0' COMMENT 'Set if campaign is Marks as featured'";
			$db->setQuery($query);

			if ( !$db->execute() )
			{
				JError::raiseError( 500, $db->stderr() );
			}
		}

		// since version 1.5
		// check if column - group name exists
		$query = "SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'group_name'  ";
		$db->setQuery($query);
		$check = $db->loadResult();

		if (!$check)
		{
			$query = "ALTER TABLE `#__jg_campaigns` ADD `group_name` varchar(250) NOT NULL AFTER  `phone` ";
			$db->setQuery($query);

			if ( !$db->execute() )
			{
				JError::raiseError(500, $db->stderr());
			}
		}
		//since version 1.5
		//check if column - website_address exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'website_address' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `website_address` varchar(250) NOT NULL AFTER  `group_name` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//since version 1.5.1
		//check if column - category_id exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'category_id' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `category_id` int(11) NOT NULL AFTER  `type` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}
		//since version 1.5.1
		//check if column - organization or individual type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'org_ind_type' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `org_ind_type` varchar(250) NOT NULL AFTER  `category_id` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - recurring_count exists
		$query="SHOW COLUMNS FROM #__jg_donations WHERE `Field` = 'recurring_count' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_donations` ADD `recurring_count` int(11) NOT NULL AFTER  `recurring_frequency` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - subscr_id type exists
		$query="SHOW COLUMNS FROM #__jg_donations WHERE `Field` = 'subscr_id' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_donations` ADD `subscr_id` varchar(100) NOT NULL AFTER  `recurring_frequency` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - donation_id type exists
		$query="SHOW COLUMNS FROM #__jg_orders WHERE `Field` = 'donation_id' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_orders` ADD `donation_id` int(11) NOT NULL AFTER  `donor_id` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - vat_number type exists
		$query="SHOW COLUMNS FROM #__jg_orders WHERE `Field` = 'vat_number' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_orders` ADD `vat_number` varchar(100) NOT NULL AFTER  `fee` ";
			$db->setQuery($query);
			//$db->loadResult();
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//since version 1.6
		//check if column - vat_number type exists
		$query="ALTER TABLE `#__jg_donations` MODIFY `recurring_frequency` varchar(100)";
		$db->setQuery($query);
		//$db->loadResult();
		if ( !$db->execute() ) {
			JError::raiseError( 500, $db->stderr() );
		}

		$query = "SHOW TABLES LIKE '" . $dbprefix . "jg_campaigns_images';";
		$db->setQuery($query);
		$table_exists = $db->loadResult();

		if ($table_exists)
		{
			//since version 1.6
			//check if column - video_provider type exists
			$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'video_provider' ";
			$db->setQuery($query);
			$check=$db->loadResult();
			if(!$check)
			{
				$query="ALTER TABLE `#__jg_campaigns_images` ADD `video_provider` varchar(50) NOT NULL AFTER  `path` ";
				$db->setQuery($query);
				//$db->loadResult();
				if ( !$db->execute() ) {
					JError::raiseError( 500, $db->stderr() );
				}
			}

			//since version 1.6
			//check if column - video_url type exists
			$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'video_url' ";
			$db->setQuery($query);
			$check=$db->loadResult();
			if(!$check)
			{
				$query="ALTER TABLE `#__jg_campaigns_images` ADD `video_url` text NOT NULL AFTER  `video_provider` ";
				$db->setQuery($query);
				//$db->loadResult();
				if ( !$db->execute() ) {
					JError::raiseError( 500, $db->stderr() );
				}
			}

			//since version 1.6
			//check if column - video_url type exists
			$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'video_img' ";
			$db->setQuery($query);
			$check=$db->loadResult();
			if(!$check)
			{
				$query="ALTER TABLE `#__jg_campaigns_images` ADD `video_img` tinyint(1) NOT NULL AFTER  `video_url` ";
				$db->setQuery($query);

				if ( !$db->execute() ) {
					JError::raiseError( 500, $db->stderr() );
				}
			}
		}
		//change transaction_id column size
		$query = "SHOW TABLES LIKE '" . $dbprefix . "jg_payouts';";
		$db->setQuery($query);
		$table_exists = $db->loadResult();

		if ($table_exists)
		{
			$query="SHOW COLUMNS FROM #__jg_payouts WHERE `Field` = 'transaction_id' ";
			$db->setQuery($query);
			$check=$db->loadResult();
			if($check)
			{
				$query="ALTER TABLE `#__jg_payouts` MODIFY `transaction_id` varchar(50) NOT NULL";
				$db->setQuery($query);

				if ( !$db->execute() ) {
					JError::raiseError( 500, $db->stderr() );
				}
			}
		}

		//js_groupid
		//since version 1.6
		//check if column - js_groupid type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'js_groupid' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `js_groupid` int(11) NOT NULL AFTER `featured` ";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//js_groupid
		//since version 1.6
		//check if column - comment is exists
		$query="SHOW COLUMNS FROM #__jg_donations WHERE `Field` = 'comment' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_donations` ADD `comment` text NOT NULL AFTER `recurring_count` ";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//js_groupid
		//since version 1.6
		//check if column - comment is exists
		$query="SHOW COLUMNS FROM #__jg_donations WHERE `Field` = 'comment' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_donations` ADD `comment` text NOT NULL AFTER `recurring_count` ";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//added by sagar for custom project
		$query="SHOW COLUMNS FROM `#__jg_donations` WHERE `Field` = 'giveback_id'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_donations` ADD   `giveback_id` int(11) NOT NULL COMMENT 'id of jg_campaigns_givebacks' AFTER  `order_id`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM `#__jg_campaigns_givebacks` WHERE `Field` = 'quantity' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_campaigns_givebacks` ADD   `quantity` int(11) NOT NULL COMMENT 'quantity of giveback' AFTER  `order`";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM `#__jg_campaigns_givebacks` WHERE `Field` = 'total_quantity' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_campaigns_givebacks` ADD   `total_quantity` int(11) NOT NULL COMMENT 'total quantity of giveback' AFTER  `quantity`";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM `#__jg_campaigns_givebacks` WHERE `Field` = 'image_path' ";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE  `#__jg_campaigns_givebacks` ADD   `image_path` varchar(400) NOT NULL COMMENT 'image_path of giveback' AFTER  `total_quantity`";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//success_status
		//since version 1.6.3
		//check if column - success_status type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'success_status'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `success_status` int(1) NOT NULL DEFAULT '0' COMMENT '0 - Ongoing, 1 - Successful, -1 - Failed' AFTER `js_groupid` ";
			$db->setQuery($query);

			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//processed_flag
		//since version 1.6.3
		//check if column - processed_flag type exists
		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'processed_flag'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `processed_flag` varchar(50) DEFAULT 'NA' COMMENT 'NA - NA, SP - Success Processed, RF - Refunded' AFTER `success_status` ";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query = "SHOW TABLES LIKE '" . $dbprefix . "jg_campaigns_images';";
		$db->setQuery($query);
		$table_exists = $db->loadResult();

		if ($table_exists)
		{
			$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'isvideo' ";
			$db->setQuery($query);
			$check=$db->loadResult();
			if(!$check)
			{
				$query="ALTER TABLE `#__jg_campaigns_images` ADD `isvideo` TINYINT(1) NOT NULL AFTER `path` ";
				$db->setQuery($query);
				if ( !$db->execute() ) {
					JError::raiseError( 500, $db->stderr() );
				}
			}

			$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'upload_option' ";
			$db->setQuery($query);
			$check=$db->loadResult();
			if(!$check)
			{
				$query="ALTER TABLE `#__jg_campaigns_images` ADD `upload_option` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `isvideo` ";
				$db->setQuery($query);
				if ( !$db->execute() ) {
					JError::raiseError( 500, $db->stderr() );
				}
			}

			//gallery
			//since version 1.6
			//check if column - video_url type exists
			$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'gallery_image' ";
			$db->setQuery($query);
			$check=$db->loadResult();
			if($check)
			{
				//gallery
				//since version 1.7
				//check if column - video_url type exists
				$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'gallery' ";
				$db->setQuery($query);
				$check=$db->loadResult();
				if(!$check)
				{
					$query="ALTER TABLE  `#__jg_campaigns_images` CHANGE  `gallery_image`  `gallery` TINYINT( 1 ) NOT NULL";
					$db->setQuery($query);
					if ( !$db->execute() ) {
						JError::raiseError( 500, $db->stderr() );
					}
				}
			}

			//gallery
			//since version 1.7
			//check if column - video_url type exists
			$query="SHOW COLUMNS FROM #__jg_campaigns_images WHERE `Field` = 'gallery'";
			$db->setQuery($query);
			$check=$db->loadResult();
			if(!$check)
			{
				$query="ALTER TABLE `#__jg_campaigns_images` ADD `gallery` tinyint(1) NOT NULL AFTER  `video_img` ";
				$db->setQuery($query);
				if ( !$db->execute() ) {
					JError::raiseError( 500, $db->stderr() );
				}
			}
		}

		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'video_on_details_page'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `video_on_details_page` tinyint(1) NOT NULL AFTER  `processed_flag` ";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'alias'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `alias` VARCHAR(255) NOT NULL AFTER  `title` ";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'meta_data'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `meta_data` text NOT NULL AFTER  `video_on_details_page` ";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		$query="SHOW COLUMNS FROM #__jg_campaigns WHERE `Field` = 'meta_desc'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if(!$check)
		{
			$query="ALTER TABLE `#__jg_campaigns` ADD `meta_desc` text NOT NULL AFTER  `meta_data` ";
			$db->setQuery($query);
			if ( !$db->execute() ) {
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//----------------------Take backup of campaigns table----------------------------------------------------------

		$query = "SHOW TABLES LIKE '".$dbprefix."jg_campaigns_backup';";
		$db->setQuery($query);
		$backup_exists=$db->loadResult();

		if(!$backup_exists)
		{
			$query = "CREATE TABLE IF NOT EXISTS #__jg_campaigns_backup LIKE #__jg_campaigns;";
			$db->setQuery($query);
			if ($db->execute())
			{
				$query = "INSERT INTO  #__jg_campaigns_backup SELECT * FROM #__jg_campaigns";
				$db->setQuery($query);

				if ($db->execute())
				{
					$query="Select id From #__jg_campaigns";
					$db->setQuery($query);
					$campaigns_data=$db->loadObjectlist();

					if ($campaigns_data)
					{
						$query="SHOW COLUMNS FROM #__tj_country WHERE `Field` = 'country_id'";
						$db->setQuery($query);
						$check=$db->loadResult();

						if($check)
						{
							$query = "DROP TABLE #__jg_campaigns_backup";
							$db->setQuery($query);
							$db->execute();

							$app = Factory::getApplication();
							$app->enqueueMessage('Error : Incomplete Installtion, Please Install TJField Component First and then jGIve','error');
							echo "<span id='NewVersion' style='padding-top: 5px; color: red; font-weight: bold; padding-left: 5px;'>". Text::_("Please Install TJField Component First and then jGIve"). '' ."</span>";

							return false;
						}

						foreach($campaigns_data as $campaign )
						{
							if($campaign->country)
							{
								$query="Select id From #__tj_country WHERE country LIKE '".$campaign->country."'";
								$db->setQuery($query);
								$country=$db->loadResult();

								if($country)
								{
									$country_object = new stdClass;
									$country_object->id = $campaign->id;
									$country_object->country = $country;
									if (!$db->updateObject('#__jg_campaigns', $country_object, 'id'))
									{
										return false;
									}

									if($campaign->state && $country_object->country)
									{
										$query="SELECT r.id
										FROM #__tj_region AS r
										LEFT JOIN #__tj_country AS c ON r.country_id = c.id
										WHERE c.id='".$country_object->country."'
										AND r.region='".$campaign->state."'";
										$db->setQuery($query);

										$region_id=$db->loadResult();
										if($region_id)
										{
											$region_object = new stdClass;
											$region_object->id = $campaign->id;
											$region_object->state = $region_id;
											if (!$db->updateObject('#__jg_campaigns', $region_object, 'id'))
											{
												return false;
											}
										}
									}

									if($campaign->city && $country_object->country)
									{
										$query="SELECT c.id
										FROM #__tj_city AS c
										LEFT JOIN #__tj_country AS con ON c.country_id = con.id
										WHERE con.id='".$country_object->country."'
										AND c.city = '".$campaign->city."'";

										$db->setQuery($query);
										$city_id = $db->loadResult();

										if($city_id)
										{
											$city_object = new stdClass;
											$city_object->id = $campaign->id;
											$city_object->city = $city_id;
											if (!$db->updateObject('#__jg_campaigns', $city_object, 'id'))
											{
												return false;
											}
										}
									}
								}
							}
						}
					}
				}
				else
				{
					JError::raiseError( 500, $db->stderr() );
				}
			}
			else
			{
				JError::raiseError( 500, $db->stderr() );
			}
		}

		//----------------------Take backup of donors table----------------------------------------------------------

		$query = "SHOW TABLES LIKE '".$dbprefix."jg_donors_backup';";
		$db->setQuery($query);
		$backup_exists = $db->loadResult();

		if(!$backup_exists)
		{
			$query = "CREATE TABLE IF NOT EXISTS #__jg_donors_backup LIKE #__jg_donors;";
			$db->setQuery($query);
			if ($db->execute())
			{
				$query = "INSERT INTO  #__jg_donors_backup SELECT * FROM #__jg_donors";
				$db->setQuery($query);
				if ($db->execute())
				{
					$query="Select id, country, state, city From #__jg_donors";
					$db->setQuery($query);
					$donors_data=$db->loadObjectlist();

					if ($donors_data)
					{
						$query="SHOW COLUMNS FROM #__tj_country WHERE `Field` = 'country_id'";
						$db->setQuery($query);
						$check=$db->loadResult();

						if($check)
						{
							$app = Factory::getApplication();
							$app->enqueueMessage('Error : Incomplete Installtion, Please Install TJField Component First and then jGIve','error');
							echo "<span id='NewVersion' style='padding-top: 5px; color: red; font-weight: bold; padding-left: 5px;'>". Text::_("Please Install TJField Component First and then jGIve"). '' ."</span>";
							return false;
						}

						foreach($donors_data as $donor )
						{
							if($donor->country)
							{
								$query="Select id From #__tj_country WHERE country LIKE '".$donor->country."'";
								$db->setQuery($query);
								$country=$db->loadResult();

								if($country)
								{
									$country_object = new stdClass;
									$country_object->id = $donor->id;
									$country_object->country = $country;
									if (!$db->updateObject('#__jg_donors', $country_object, 'id'))
									{
										return false;
									}

									if($donor->state && $country_object->country)
									{
										$query="SELECT r.id
										FROM #__tj_region AS r
										LEFT JOIN #__tj_country AS c ON r.country_id = c.id
										WHERE c.id='".$country_object->country."'
										AND r.region='".$donor->state."'";
										$db->setQuery($query);

										$region_id=$db->loadResult();
										if($region_id)
										{
											$region_object = new stdClass;
											$region_object->id = $donor->id;
											$region_object->state = $region_id;
											if (!$db->updateObject('#__jg_donors', $region_object, 'id'))
											{
												return false;
											}
										}
									}

									if($donor->city && $country_object->country)
									{
										$query="SELECT c.id
										FROM #__tj_city AS c
										LEFT JOIN #__tj_country AS con ON c.country_id = con.id
										WHERE con.id='".$country_object->country."'
										AND c.city = '".$donor->city."'";
										$db->setQuery($query);
										$city_id = $db->loadResult();

										if($city_id)
										{
											$city_object = new stdClass;
											$city_object->id = $donor->id;
											$city_object->city = $city_id;
											if (!$db->updateObject('#__jg_donors', $city_object, 'id'))
											{
												return false;
											}
										}
									}
								}
							}
						}
					}
				}
				else
				{
					JError::raiseError( 500, $db->stderr() );
				}
			}

		}

		/**  Video Gallery Migration **/

		$query = "SHOW TABLES LIKE '" . $dbprefix . "jg_campaigns_images';";
		$db->setQuery($query);
		$table_exists = $db->loadResult();

		if ($table_exists)
		{
			//1. Select the video records to move
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id', 'campaign_id', 'video_provider', 'video_url')));
			$query->select($db->quoteName(array('video_img', 'gallery')));
			$query->from($db->quoteName('#__jg_campaigns_images'));
			$query->where($db->quoteName("video_url") . " <>''");
			$db->setQuery($query);
			$videos = $db->loadObjectList();

			foreach($videos as $video)
			{
				//2. Move video url records from images to newly added media table
				$obj             = new stdclass;
				$obj->id         = '';
				$obj->type       = $video->video_provider;
				$obj->display    = 1;
				$obj->url        = $video->video_url;
				$obj->default    = !($video->gallery);
				$obj->content_id = $video->campaign_id;
				$db->insertObject('#__jg_campaigns_media', $obj, 'id');

				// 3. Check if video is set as default, yes then set this default in newly added column in campaign table.
				if( $video->video_img == 1)
				{
					$obj_camp     = new stdclass;
					$obj_camp->id = $video->campaign_id;
					$obj_camp->video_on_details_page = 1;

					$db->updateObject('#__jg_campaigns', $obj_camp, 'id');
				}

				// 4. Remove the video url from image table
				$obj                 = new stdclass;
				$obj->id             = $video->id;
				$obj->video_url      = '';
				$obj->video_provider = '';
				$db->updateObject('#__jg_campaigns_images', $obj, 'id');
			}
		}
	}

	/**
	 * Add default ACL permissions if already set by administrator
	 *
	 * @return  void
	 */
	public function deFaultPermissionsFix()
	{
		$db = Factory::getDbo();
		$query = "SELECT id, rules FROM `#__assets` WHERE `name` = 'com_jgive' ";
		$db->setQuery($query);
		$result = $db->loadobject();

		if(strlen(trim($result->rules))<=3)
		{
			$obj = new Stdclass();
			$obj->id = $result->id;
			$obj->rules = '{"core.admin":[],"core.manage":[],"core.create":{"2":1,"4":1,"5":1},"core.delete":{"2":1,"3":1,"4":1,"5":1},"core.edit":{"2":1,"3":1,"4":1},"core.edit.state":{"2":1,"3":1,"4":1}}';

			if (!$db->updateObject('#__assets', $obj, 'id'))
			{
				$app = Factory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
	}

	/**
	 * Installed Field data
	 *
	 * @return  void
	 */
	public function _installSampleFieldsManagerData()
	{
		// Check if file is present.
		jimport( 'joomla.filesystem.file' );

		$filePath = JPATH_SITE . '/components' . '/com_tjfields' . '/tjfields.php';

		if (!File::exists($filePath))
		{
			return false;
		}

		$db   = Factory::getDbo();
		$user = Factory::getUser();

		// Check if any eventform fields groups exists.
		$query = $db->getQuery(true);
		$query->select('COUNT(tfg.id) AS count');
		$query->from('`#__tjfields_groups` AS tfg');

		$search = $db->Quote('com_jgive.campaign');
		$query->where('tfg.client=' . $search);

		$db->setQuery($query);

		$campaign_field_group = $db->loadResult();

		$queries = array();

		// If campaign field
		if (!$campaign_field_group)
		{
			$queries[] = "INSERT INTO `#__tjfields_groups` (`ordering`, `state`, `created_by`, `name`, `client`, `title`) VALUES(1, 1, ".$user->id.", 'Campaign- Additional Info. fields', 'com_jgive.campaign', 'Campaign Fields');";
		}

		// Execute sql queries.
		if (count($queries) != 0)
		{
			foreach ($queries as $query)
			{
				$query = trim($query);

				if ($query != '')
				{
					$db->setQuery($query);

					if (!$db->execute())
					{
						JError::raiseWarning(1, Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
						return false;
					}
				}
			}
		}
	}

	/**
	 * function for convert row to text
	 *
	 * @return  text
	 */
	public function row2text($row,$dvars=array())
	{
		reset($dvars);
		while(list($idx,$var)=each($dvars))
		unset($row[$var]);
		$text='';
		reset($row);
		$flag=0;
		$i=0;
		while(list($var,$val)=each($row))
		{
			if($flag == 1)
			$text.=",\n";
			elseif($flag == 2)
			$text.=",\n";
			$flag = 1;

			if(is_numeric($var))
			if($var[0]=='0')
			$text.="'$var'=>";
			else
			{
				if($var!==$i)
				$text.="$var=>";
				$i = $var;
			}
			else
			$text.="'$var'=>";
			$i++;

			if (is_array($val))
			{
				$text.= "array(" . $this->row2text($val, $dvars) . ")";
				$flag = 2;
			}
			else
			{
				$text.= "\"" . addslashes($val) . "\"";
			}
		}

		return $text;
	}

	/**
	 * Function Add Uncategorised __categories in #__categories table
	 *
	 * @return  void
	 */
	function addUncategorisedCat()
	{
			$db = Factory::getDbo();
			$query  = 'SELECT `id` FROM `#__categories` WHERE `extension` = \'com_jgive\' AND `title`=\'Uncategorised\'';
			$db->setQuery($query);
			$result = $db->loadResult();

			if (empty($result))
			{
				$catobj = new stdClass;
				$catobj->title = 'Uncategorised';
				$catobj->alias = 'uncategorised';
				$catobj->extension = "com_jgive";
				$catobj->path = " uncategorised";
				$catobj->parent_id = 1;
				$catobj->level = 1;

				$paramdata = array();
				$paramdata['category_layout'] = '';
				$paramdata['image'] = '';
				$catobj->params = json_encode($paramdata);

				// LOGGED user id
				$user = Factory::getUser();
				$catobj->created_user_id = $user->id;
				$catobj->language = "*";
				/*$catobj->description = $category->description;*/

				$catobj->published = 1;
				$catobj->access = 1;

				if (!$db->insertObject('#__categories', $catobj, 'id'))
				{
					echo $db->stderr();

					return false;
				}
			}
	}

	/**
	 * Installed Notifications
	 * method to install default email templates
	 *
	 * @return  void
	 */
	public function _insertTjNotificationTemplates()
	{
		$client = 'com_jgive';
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/tables');
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjnotifications/models');
		$notificationsModel = BaseDatabaseModel::getInstance('Notification', 'TJNotificationsModel');

		$existingKeys = $notificationsModel->getKeys($client);

		$filePath = JPATH_ADMINISTRATOR . '/components/com_jgive/jgiveTemplate.json';
		$str      = file_get_contents($filePath);
		$json     = json_decode($str, true);

		if (count($json) != 0)
		{
			foreach ($json as $template => $array)
			{
				// If template doesn't exist then we add notification template.
				if (!in_array($array['key'], $existingKeys))
				{
					$notificationsModel->createTemplates($array);
				}
				else
				{
					$notificationsModel->updateTemplates($array, $client);
				}

				$replacementTagCount = $notificationsModel->getReplacementTagsCount($array['key'], 'com_jgive');

				// If replacement tags are changed update those
				if (in_array($array['key'], $existingKeys) && isset($array['replacement_tags']) && count($array['replacement_tags']) != $replacementTagCount)
				{
					$notificationsModel->updateReplacementTags($array);
				}
			}
		}
	}

	/**
	 * Remove duplicate menu item
	 *
	 * @return boolean
	 *
	 * @since 2.4.0
	 */
	public function menuItemMigration()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if (JVERSION >= '4.0.0')
		{
			$table   = new \Joomla\Component\Menus\Administrator\Table\MenuTable($db);
		} 
		else 
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');
			$table = Table::getInstance('menu', 'MenusTable', array('dbo', $db));
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('menutype') . ' = ' . $db->quote('main'));
		$query->where($db->quoteName('path') . ' IN ("com-jgive-vendor", "com-jgive-tjnotifications-menu", "com-jgive-tjreports-menu", 
					"com-jgive-campaign-field", "com-jgive-jgive-campaign-fields-group", "com-jgive-title-countries",
					"com-jgive-title-regions", "com-jgive-title-cities"
				)');
		$db->setQuery($query);
		$data = $db->loadObjectList();

		foreach ($data as $key => $menuItem) 
		{
			$table->delete($menuItem->id);
		}

		return true;
	}
}
