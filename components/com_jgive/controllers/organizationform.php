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

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * Organization controller class.
 *
 * @package  JGive
 * @since    2.5.0
 */
class JgiveControllerOrganizationform extends FormController
{
	/**
	 * Save organization data
	 *
	 * @param   integer  $key     key.
	 *
	 * @param   integer  $urlVar  url
	 *
	 * @return  boolean|string  The arguments to append to the redirect URL.
	 *
	 * @since   2.5.0
	 */
	public function save($key = null, $urlVar = null)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app          = Factory::getApplication();
		$model        = $this->getModel('Organizationform', 'JGiveModel');
		$data         = $app->input->get('jform', array(), 'array');
		$allJformData = $data;
		$form         = $model->getForm();

		if (!$form)
		{
			throw new Exception($model->getError());
		}

		$data  = $model->validate($form, $data);

		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Check for errors.
			if (!empty($errors))
			{
				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'error');
					}
				}

				// Save the data in the session.
				$app->setUserState('com_jgive.edit.organizationform.data', $allJformData);

				// Tweak *important
				$app->setUserState('com_jgive.edit.organizationform.id', $allJformData['id']);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_jgive.edit.organizationform.id');
				$this->setRedirect(Route::_('index.php?option=com_jgive&view=organizationform&layout=default&id=' . $id, false));

				return false;
			}
		}

		// Other city assigned
		if (!empty($data['other_city_value']) && $data['other_city_check'] == 1)
		{
			$data['city'] = 0;
		}

		if ($data['city'] != 0)
		{
			$data['other_city_value'] = '';
			$data['other_city_check'] = 0;
		}

		// Check existing email
		$msg = Text::_('COM_JGIVE_MSG_SUCCESS_SAVE_ORGANIZATION');

		$organizationObjClass = JGive::organization();
		$return = $organizationObjClass->addOrganization($data);

		if ($return === false)
		{
			$organizationError = $organizationObjClass->getError();

			$app->setUserState('com_jgive.edit.organizationform.data', $allJformData);
			$this->setMessage($organizationError, 'warning');

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.organizationform.id');
			$this->setRedirect(Route::_('index.php?option=com_jgive&view=organizationform&layout=default&id=' . $id, false));

			return false;
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jgive.edit.organizationform.id', null);
		$app->setUserState('com_jgive.edit.organizationform.data', null);

		// Redirect to the list screen.
		$link = 'index.php?option=com_jgive&view=organizations&layout=default';

		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$itemId = $jgiveFrontendHelper->getItemId($link);
		$link = $link . "&Itemid=" . $itemId;

		// Check this function
		$redirect = Route::_($link);

		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Cancel description
	 *
	 * @param   integer  $key  The key
	 *
	 * @return void
	 */
	public function cancel($key=null)
	{
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=organizations&layout=default');

		$this->setRedirect(Route::_('index.php?option=com_jgive&view=organizations&layout=default&Itemid=' . $itemId, false));
	}

	/**
	 * Method to add a new record.
	 *
	 * @return  mixed  True if the record can be added, an error object if not.
	 *
	 * @since   1.6
	 */
	public function add()
	{
		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$organizationformItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=organizationform&layout=default');

		$organizationformLink = Route::_(
			'index.php?option=com_jgive&view=organizationform&layout=default&Itemid=' . $organizationformItemId, false
		);

		$this->setRedirect($organizationformLink);

		return true;
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   2.2.0
	 */
	public function edit($key = 'id', $urlVar = 'id')
	{
		$jgiveFrontendHelper = new JgiveFrontendHelper;

		$app            = Factory::getApplication();
		$input          = $app->input;
		$cid = $input->get('cid', array(), 'post', 'array');

		if (!count($cid))
		{
			return false;
		}

		$id = $cid[0];
		$organizationformItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=organizationform&layout=default');

		$organizationformLink = Route::_(
			'index.php?option=com_jgive&view=organizationform&layout=default&Itemid=' . $organizationformItemId . '&id=' . $id, false
		);

		$this->setRedirect($organizationformLink);

		return true;
	}
}
