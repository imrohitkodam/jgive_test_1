<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Factory;

/**
 * Individual contact controller class
 *
 * @since  2.5.0
 */
class JgiveControllerIndividuals extends JGiveController
{
	/**
	 * Get all individuals.
	 * 
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	public function getIndividuals()
	{
		try
		{
			$app    = Factory::getApplication();
			$search = $app->input->get('search', '', 'STRING');
			$vendorId = $app->input->get('vendorId', 0, 'INTEGER');
			$JGiveModelIndividuals = JGive::model('Individuals', array('ignore_request' => true));
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
