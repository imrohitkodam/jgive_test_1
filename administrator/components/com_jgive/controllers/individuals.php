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

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;


/**
 * Individuals controller class.
 *
 * @package  JGive
 * @since    2.3.0
 */
class JgiveControllerIndividuals extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModelLegacy
	 *
	 * @since   2.3.0
	 */
	public function getModel($name = 'Individual', $prefix = 'JGiveModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Get individuals.
	 *
	 * @return void
	 *
	 * @since   2.3.0
	 */

	public function getIndividuals()
	{
		$app    = Factory::getApplication();
		$search = $app->input->get('search', '', 'STRING');

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models');
		$JGiveModelIndividuals = BaseDatabaseModel::getInstance("Individuals", 'JgiveModel', array("ignore_request" => true));

		$app->setUserState("com_jgive.individuals.filter.search", $search);
		$users = $JGiveModelIndividuals->getItems();

		echo json_encode($users);

		jexit();
	}

	/**
	 * Get joomla users.
	 *
	 * @return void
	 *
	 * @since   2.3.0
	 */

	public function getUsers()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$search = $jinput->get('search', '', 'STRING');

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models/');
		$UsersModelIndividuals = BaseDatabaseModel::getInstance('Users', 'UsersModel');
		$toSearch              = "username:" . $search;
		$app->setUserState("com_users.users.default.filter.search", $toSearch);
		$users = $UsersModelIndividuals->getItems();

		echo json_encode($users);

		jexit();
	}

	/**
	 * Delete individuals and organization_contacts.
	 *
	 * @return void
	 *
	 * @since   2.3
	 */
	public function delete()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$model = $this->getModel('individuals');
		$post  = Factory::getApplication()->input->post;

		$individualIdArray = $post->get('cid', '', 'Array');

		if (!empty($individualIdArray))
		{
			if ($model->delete($individualIdArray))
			{
				$msg = Text::_('COM_JGIVE_INDIVIDUAL_DELETED');
			}
			else
			{
				$msg = Text::_('COM_JGIVE_ERR_INDIVIDUAL_DELETED');
			}
		}
		else
		{
			$msg = Text::_('COM_JGIVE_ERR_INDIVIDUAL_DELETED');
		}

		$this->setRedirect(Uri::base() . "index.php?option=com_jgive&view=individuals", $msg);
	}
}
