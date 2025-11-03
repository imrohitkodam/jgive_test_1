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
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * JgiveControllerCampaign form controller class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JGiveController extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return   JController  This object to support chaining.
	 *
	 * @since  1.7
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/jgive.php';

		$view = Factory::getApplication()->input->getCmd('view', 'cp');

		Factory::getApplication()->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}

	/**
	 * Method For getting jgive version
	 *
	 * @return   void
	 *
	 * @since  1.7
	 */
	public function getVersion()
	{
		echo @file_get_contents('https://techjoomla.com/vc/index.php?key=abcd1234&product=jgive');
		jexit();
	}

	/**
	 * Method for creating previous created campaign
	 *
	 * @return boolean
	 *
	 * @since   2.0
	 */
	public function createActivity()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_jgive/models/migration.php';
		$model = $this->getModel('migration');

		$result = $model->createActivity();

		if ($result)
		{
			echo "<br/>" . Text::_('COM_JGIVE_DASHBOARD_CREATE_ACTIVITIES_DONE');
		}
		else
		{
			echo "<br/>" . Text::_('COM_JGIVE_DASHBOARD_CREATE_ACTIVITIES_ERROR');
		}
	}
}
