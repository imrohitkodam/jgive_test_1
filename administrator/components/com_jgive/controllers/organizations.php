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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


/**
 * Organizations controller class.
 *
 * @package  JGive
 * @since    2.3.0
 */
class JgiveControllerOrganizations extends AdminController
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
	public function getModel($name = 'Organization', $prefix = 'JGiveModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Get organizations.
	 *
	 * @return void
	 *
	 * @since   2.3
	 */

	public function getOrganizations()
	{
		$app    = Factory::getApplication();
		$search = $app->input->get('search', '', 'STRING');

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models');
		$JGiveModelOrganizations = BaseDatabaseModel::getInstance("Organizations", 'JgiveModel', array("ignore_request" => true));
		$app->setUserState("com_jgive.organizations.filter.search", $search);
		$users = $JGiveModelOrganizations->getItems();

		echo json_encode($users);

		jexit();
	}

	/**
	 * Delete organization and organization contact.
	 *
	 * @return void
	 *
	 * @since   2.3
	 */
	public function delete()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$model = $this->getModel('organizations');
		$post  = Factory::getApplication()->input->post;

		$organizationIdArray = $post->get('cid', '', 'Array');

		if (!empty($organizationIdArray))
		{
			if ($model->delete($organizationIdArray))
			{
				$msg = Text::_('COM_JGIVE_ORGANIZATION_DELETED');
			}
			else
			{
				$msg = Text::_('COM_JGIVE_ERR_ORGANIZATION_DELETED');
			}
		}
		else
		{
			$msg = Text::_('COM_JGIVE_ERR_ORGANIZATION_DELETED');
		}

		$this->setRedirect(Uri::base() . "index.php?option=com_jgive&view=organizations", $msg);
	}
}
