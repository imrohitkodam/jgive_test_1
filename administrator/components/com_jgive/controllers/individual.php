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


use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Individual controller class.
 *
 * @package  JGive
 * @since    2.3.0
 */
class JgiveControllerIndividual extends FormController
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
		Session::checkToken('post') or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Individual', 'JGiveModel');
		$data  = $app->input->get('jform', array(), 'array');
		$tmpl  = $app->input->get('tmpl');

		$all_jform_data = $data;

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
				else
				{
					$app->enqueueMessage($errors[$i], 'error');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_jgive.edit.individual.data', $all_jform_data);

			// Tweak *important
			$app->setUserState('com_jgive.edit.individual.id', $all_jform_data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.individual.id');

			if ($tmpl == 'component')
			{
				$this->setRedirect(Route::_('index.php?option=com_jgive&view=individual&layout=edit&tmpl=component', false));
			}
			else
			{
				$this->setRedirect(Route::_('index.php?option=com_jgive&view=individual&layout=edit&id=' . $id, false));
			}

			return false;
		}

		// Check existing email
		$task          = $input->get('task');
		$msg           = Text::_('COM_JGIVE_MSG_SUCCESS_SAVE_INDIVIDUAL');

		$return = JGive::individual()->addIndividualDonor($data);

		if ($return === false)
		{
			$app->setUserState('com_jgive.edit.individual.data', $all_jform_data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.individual.id');
			$this->setRedirect(Route::_('index.php?option=com_jgive&view=individual&layout=edit&id=' . $id, false));

			return false;
		}

		switch ($task)
		{
			case 'apply':
			$redirect = Route::_('index.php?option=com_jgive&view=individual&layout=edit&id=' . $return, false);
			$app->enqueueMessage($msg, 'success');
			$app->redirect($redirect);
			break;

			case 'save2new':
			$redirect = Route::_('index.php?option=com_jgive&view=individual&layout=edit', false);
			$app->enqueueMessage($msg, 'success');
			$app->redirect($redirect);
			break;

			case 'save':

			if ($tmpl == 'component')
			{
				// Jform_contact_name in case of organization form, contact_name for donation form
				echo '<script type="text/javascript">

						jQuery(window.parent.document.getElementById("contact_name")).val("' . $data['first_name'] . '");
						jQuery(window.parent.document.getElementById("jform_contact_name")).val("' . $data['first_name'] . '");
						jQuery(window.parent.document.getElementById("jform_contact_id")).val("ind.' . $return . '");
						jQuery(window.parent.document.getElementById("contact_id")).val("ind.' . $return . '");

						parent.jQuery("#newIndividualDonor").modal("hide");
					</script>';
				break;
			}
			else
			{
				$redirect = Route::_('index.php?option=com_jgive&view=individuals', false);
				$app->enqueueMessage($msg, 'success');
				$app->redirect($redirect);
				break;
			}
		}
	}
}
