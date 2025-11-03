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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Response\JsonResponse;

/**
 * Individual controller class.
 *
 * @package  JGive
 * @since    2.3
 */
class JGiveControllerIndividuals extends BaseController
{
	/**
	 * Get all individuals.
	 * 
	 * @return  void
	 *
	 * @since   2.3
	 */
	public function getIndividuals()
	{
		try
		{
			$app    = Factory::getApplication();

			if ($app->isClient('site'))
			{
				echo Text::_("COM_JGIVE_ERROR_IN_DONATION_CONTROLLER");

			return;
			}

			$search   = $app->input->get('search', '', 'STRING');
			$vendorId = $app->input->get('vendorId', 0, 'INTEGER');
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models/');
			$JGiveModelIndividuals = BaseDatabaseModel::getInstance('Individuals', 'JgiveModel');
			$JGiveModelIndividuals->setState("search", $search);
			$JGiveModelIndividuals->setState("filter.vendor_id", $vendorId);
			$users                 = $JGiveModelIndividuals->getIndividuals($search, $vendorId);
			echo new JsonResponse($users);
		}
		catch (Exception $e)
		{
		echo new JsonResponse($e);
		}
	}
}
