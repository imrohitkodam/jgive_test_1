<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


/**
 * Organizations controller class.
 *
 * @package  JGive
 * @since    2.3
 */
class JGiveControllerOrganizations extends BaseController
{
	/**
	 * Get organizations.
	 *
	 * @return void
	 *
	 * @since   2.3.0
	 */

	public function getOrganizations()
	{
		try
		{
			$app    = Factory::getApplication();

			if ($app->isClient('site'))
			{
				echo Text::_("COM_JGIVE_ERROR_IN_DONATION_CONTROLLER");

				return;
			}

			$search = $app->input->get('search', '', 'STRING');
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models');
			$JGiveModelOrganizations = BaseDatabaseModel::getInstance("Organizations", 'JgiveModel', array("ignore_request" => true));
			$JGiveModelOrganizations->setState("search", $search);
			$JGiveModelOrganizations->setState("published", '1');
			$organizations           = $JGiveModelOrganizations->getItems();
			echo new JsonResponse($organizations);
		}

		catch (Exception $e)
		{
			echo new JsonResponse($e);
		}
	}
}
