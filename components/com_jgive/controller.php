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

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

/**
 * Jgive Controller
 *
 * @package     Jgive
 * @subpackage  Jgive controller
 * @since       1.0
 */
class JgiveController extends BaseController
{
	/**
	 * refreshDashboard
	 *
	 * @return  json data
	 */
	public function refreshDashboard()
	{
		$jinput = Factory::getApplication()->input;

		$fromDate = $jinput->get('fromDate', '', 'STRING');

		if ($fromDate)
		{
			$fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
		}

		$toDate = $jinput->get('toDate', '', 'STRING');

		if ($fromDate)
		{
			$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		}

		$app = Factory::getApplication();
		$app->setUserState('from', $fromDate);
		$app->setUserState('to', $toDate);

		$status = array();
		$status['status'] = 1;

		echo json_encode($status);
		jexit();
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.7
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view = Factory::getApplication()->input->getCmd('view', 'campaigns');

		Factory::getApplication()->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}
}
