<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;
use Joomla\CMS\Factory;

require_once JPATH_SITE . '/components/com_jgive/includes/defines.php';

/**
 * JGive factory class.
 *
 * This class perform the helpful operation required to JGive package
 *
 * @since  2.3.3
 */
class JGive
{
	/**
	 * Holds the record of the loaded Jgive classes
	 *
	 * @var    array
	 * @since  2.3.3
	 */
	private static $loadedClass = array();

	/**
	 * Holds the record of the component config
	 *
	 * @var    Joomla\Registry\Registry
	 * @since  2.3.3
	 */
	private static $config = null;

	/**
	 * Retrieves a table from the table folder
	 *
	 * @param   string  $name    The table file name
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table|boolean object or false on failure
	 *
	 * @since   2.3.3
	 **/
	public static function table($name, $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
		$table = Table::getInstance($name, 'JgiveTable', $config);

		return $table;
	}

	/**
	 * Retrieves a model from the model folder
	 *
	 * @param   string  $name    The model name
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  BaseDatabaseModel|boolean object or false on failure
	 *
	 * @since   2.3.3
	 **/
	public static function model($name, $config = array())
	{
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models');
		$model = BaseDatabaseModel::getInstance($name, 'JgiveModel', $config);

		return $model;
	}

	/**
	 * Magic method to create instance of JGive library
	 *
	 * @param   string  $name       The name of the class
	 * @param   mixed   $arguments  Arguments of class
	 *
	 * @return  mixed   return the Object of the respective class if exist OW return false
	 *
	 * @since   2.3.3
	 **/
	public static function __callStatic($name, $arguments)
	{
		self::loadClass($name);

		$className = 'JGive' . StringHelper::ucfirst($name);

		if (class_exists($className))
		{
			if (method_exists($className, 'getInstance'))
			{
				return call_user_func_array(array($className, 'getInstance'), $arguments);
			}

			return new $className;
		}

		return false;
	}

	/**
	 * Load the class library if not loaded
	 *
	 * @param   string  $className  The name of the class which required to load
	 *
	 * @return  boolean True on success
	 *
	 * @since   2.3.3
	 **/
	public static function loadClass($className)
	{
		if (! isset(self::$loadedClass[$className]))
		{
			$className = (string) StringHelper::strtolower($className);

			$path = JPATH_SITE . '/components/com_jgive/includes/' . $className . '.php';

			include_once $path;

			self::$loadedClass[$className] = true;
		}

		return self::$loadedClass[$className];
	}

	/**
	 * Load the component configuration
	 *
	 */
	public static function config()
	{
		if (empty(self::$config))
		{
			self::$config = ComponentHelper::getParams('com_jgive');
		}

		return self::$config;
	}

	/**
	 * Initializes the css, js and necessary dependencies
	 *
	 * @param   string  $location  The location where the assets needs to load
	 *
	 * @return  void
	 *
	 * @since   2.3.3
	 */
	public static function init($location = 'site')
	{
		static $loaded = null;
		$app           = Factory::getApplication();
		$docType       = Factory::getDocument()->getType();
		$versionClass  = self::jgversion();

		if (isset($loaded[$location]) && ($docType != 'html'))
		{
			return;
		}

		if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
		{
			require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
			TjStrapper::loadTjAssets('com_jgive');
		}

		$version = $versionClass->getMediaVersion();
		$options = array("version" => $version);

		// Load JS Files
		HTMLHelper::script('media/com_jgive/javascript/jgive.min.js', $options);

		// Load Css Files
		HTMLHelper::stylesheet('media/techjoomla_strapper/vendors/font-awesome/css/font-awesome.min.css', $options);
		HTMLHelper::stylesheet('media/com_jgive/css/artificiers.min.css', $options);
		HTMLHelper::stylesheet('media/com_jgive/css/jgive-tables.min.css', $options);
		HTMLHelper::stylesheet('media/com_jgive/css/jgive.min.css', $options);

		if (!defined('JGIVE_LOAD_BOOTSTRAP_VERSION'))
		{
			$params = ComponentHelper::getParams('com_jgive');

			if ($app->isClient("administrator"))
			{
				$bsVersion = (JVERSION >= '4.0.0') ? 'bs5' : 'bs3';
			}
			else
			{
				$bsVersion = $params->get('bootstrap_version', '', 'STRING');

				if (empty($bsVersion))
				{
					$bsVersion = (JVERSION >= '4.0.0') ? 'bs5' : 'bs3';
				}
			}

			define('JGIVE_LOAD_BOOTSTRAP_VERSION', $bsVersion);
		}

		if (!defined('COM_JGIVE_WRAPPAER_CLASS'))
		{
			if (JGIVE_LOAD_BOOTSTRAP_VERSION == 'bs3')
			{
				define('COM_JGIVE_WRAPPAER_CLASS', "tjBs3");
			}
			else 
			{
				define('COM_JGIVE_WRAPPAER_CLASS', "tjBs5");
			}
		}

		if (JGIVE_LOAD_BOOTSTRAP_VERSION == 'bs3')
		{
			HTMLHelper::stylesheet('media/techjoomla_strapper/css/bootstrap.j3.min.css', $options);
			HTMLHelper::stylesheet('media/com_jgive/css/jgive_bs3.min.css', $options);
		}
		else
		{
			HTMLHelper::stylesheet('media/com_jgive/css/jgive_bs5.min.css', $options);
		}

		// Load Boostrap Files
		if (self::config()->get('load_bootstrap') == '1')
		{
			if (JGIVE_LOAD_BOOTSTRAP_VERSION == 'bs3')
			{
				HTMLHelper::stylesheet('media/techjoomla_strapper/bs3/css/bootstrap.min.css', $options);
			}
			else
			{
				HTMLHelper::stylesheet('media/vendor/bootstrap/css/bootstrap.min.css', $options);
			}
		}

		$loaded[$location] = true;
	}
}
