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
 * @since    2.3.0
 */
class JgiveControllerOrganization extends FormController
{
	/**
	 * Save campaign data
	 *
	 * @param   integer  $key     key.
	 *
	 * @param   integer  $urlVar  url
	 *
	 * @return  boolean|string  The arguments to append to the redirect URL.
	 *
	 * @since   2.3.0
	 */
	public function save($key = null, $urlVar = null)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Organization', 'JGiveModel');

		$data = $app->input->get('jform', array(), 'array');

		$allJformData = $data;

		// Validate the posted data.
		$form = $model->getForm();

		// Validate the posted data.
		$data = $model->validate($form, $data);

		$input = $app->input;

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
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_jgive.edit.organization.data', $allJformData);

			// Tweak *important
			$app->setUserState('com_jgive.edit.organization.id', $allJformData['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.organization.id');
			$this->setRedirect(Route::_('index.php?option=com_jgive&view=organization&layout=edit&id=' . $id, false));

			return false;
		}

		// Check existing email
		$task   = $input->get('task');
		$tmpl   = $input->get('tmpl');
		$msg    = Text::_('COM_JGIVE_MSG_SUCCESS_SAVE_ORGANIZATION');
		$id     = $input->get('id');

		$organizationObjClass = JGive::organization();
		$return = $organizationObjClass->addOrganization($data);

		if ($return === false)
		{
			$organizationError = $organizationObjClass->getError();
			$app->setUserState('com_jgive.edit.organization.data', $allJformData);
			$this->setMessage($organizationError, 'warning');

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.organization.id');
			$this->setRedirect(Route::_('index.php?option=com_jgive&view=organization&layout=edit&id=' . $id, false));

			return false;
		}

		if (empty($id))
		{
			$id = $return;
		}

		switch ($task)
		{
			case 'apply':
			$redirect = Route::_('index.php?option=com_jgive&view=organization&layout=edit&id=' . $id, false);
			$app->enqueueMessage($msg, 'success');
			$app->redirect($redirect);
			break;

			case 'save2new':
			$redirect = Route::_('index.php?option=com_jgive&view=organization&layout=edit', false);
			$app->enqueueMessage($msg, 'success');
			$app->redirect($redirect);
			break;

			case 'save':
			if ($tmpl == 'component')
			{
				echo '<script type="text/javascript">
						jQuery(window.parent.document.getElementById("contact_name")).val("' . $data['name'] . '");
						jQuery(window.parent.document.getElementById("jform_contact_id")).val("org.' . $id . '");
						jQuery(window.parent.document.getElementById("contact_id")).val("org.' . $id . '");

						parent.jQuery("#newIndividualOrganization").modal("hide");
					</script>';
					break;
			}
			else
			{
				$redirect = Route::_('index.php?option=com_jgive&view=organizations', false);
				$app->enqueueMessage($msg, 'success');
				$app->redirect($redirect);
				break;
			}
		}
	}
}
