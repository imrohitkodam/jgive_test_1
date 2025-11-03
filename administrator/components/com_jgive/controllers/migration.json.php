<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Installer Database Controller
 *
 * @since  2.0
 */
class JGiveControllerMigration extends BaseController
{
	/**
	 * Function to migrate media data
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function migrateMedia()
	{
		$jgiveMigrationModel = $this->getModel('migration');

		$result = $jgiveMigrationModel->migrateMedia();

		echo new JsonResponse($result);
	}

	/**
	 * Function to migrate vendor data
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	public function migrateVendorData()
	{
		$jgiveMigrationModel = $this->getModel('migration');

		$result = $jgiveMigrationModel->migrateVendorData();

		echo new JsonResponse($result);
	}

	/**
	 * Function to migrate media data
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function migrateActivities()
	{
		$jgiveMigrationModel = $this->getModel('migration');

		$result = $jgiveMigrationModel->migrateActivities();

		echo new JsonResponse($result);
	}
}
