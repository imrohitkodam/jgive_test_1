<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * The Individualform controller
 *
 * @since  2.5.0
 */
class JgiveControllerIndividualform extends FormController
{
	/**
	 * Constructor
	 *
	 * @since  2.5.0
	 */
	public function __construct()
	{
		$this->view_list = 'individuals';
		parent::__construct();
	}

	/**
	 * Method to save a vendor profile data.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean|void
	 *
	 * @since   2.5.0
	 */
	public function save($key = null, $urlVar = null)
	{
		Session::checkToken('post') or jexit(Text::_('JINVALID_TOKEN'));
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individualform&layout=default');

		$app          = Factory::getApplication();
		$model        = $this->getModel('Individualform', 'JGiveModel');
		$input        = $app->input;
		$data         = $input->get('jform', array(), 'array');
		$allJformData = $data;
		$form         = $model->getForm();
		$tmpl         = $input->get('tmpl', '', 'string');

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		$validData   = $model->validate($form, $data);

		if ($validData === false)
		{
			$errors = $model->getErrors();

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
			$app->setUserState('com_jgive.edit.individualform.data', $allJformData);

			// Tweak *important
			$app->setUserState('com_jgive.edit.individualform.id', $allJformData['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.individualform.id');

			if ($tmpl == 'component')
			{
				$this->setRedirect(Route::_('index.php?option=com_jgive&view=individualform&layout=default&&tmpl=component&Itemid=' . $itemId, false));
			}
			else
			{
				$this->setRedirect(Route::_('index.php?option=com_jgive&view=individualform&layout=edit&id=' . $id . '&Itemid=' . $itemId, false));
			}

			return false;
		}

		$return = JGive::individual()->addIndividualDonor($data);

		if ($return === false)
		{
			$app->setUserState('com_jgive.edit.individualform.data', $allJformData);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.individualform.id');
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jgive&view=individualform&layout=default&id=' . $id . '&Itemid=' . $itemId, false));

			return false;
		}

		if ($tmpl == 'component')
		{
			echo '<script type="text/javascript">

					jQuery(window.parent.document.getElementById("contact_name")).val("' . $data['first_name'] . '");
					jQuery(window.parent.document.getElementById("jform_contact_name")).val("' . $data['first_name'] . '");
					jQuery(window.parent.document.getElementById("jform_contact_id")).val("ind.' . $return . '");
					jQuery(window.parent.document.getElementById("contact_id")).val("ind.' . $return . '");
					parent.SqueezeBox.close();
				</script>';
		}
		else
		{
			// Check this function
			$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individuals&layout=default');
			$redirect = Route::_('index.php?option=com_jgive&view=individuals&Itemid=' . $itemId, false);
			$this->setRedirect($redirect, Text::_('COM_JGIVE_INDIVIDUAL_CONTACT_SAVE_SUCCESS_MSG'));

			// Flush the data from the session.
			$app->setUserState('com_jgive.edit.individualform.id', null);
		}
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
		$itemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individuals&layout=default');

		$this->setRedirect(Route::_('index.php?option=com_jgive&view=individuals&layout=default&Itemid=' . $itemId, false));
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
		$individualFormItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individualform&layout=default');

		$individualFormLink = Route::_(
			'index.php?option=com_jgive&view=individualform&layout=default&Itemid=' . $individualFormItemId, false
		);

		$this->setRedirect($individualFormLink);

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
		$individualFormItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individualform&layout=default');

		$individualFormLink = Route::_(
			'index.php?option=com_jgive&view=individualform&layout=default&Itemid=' . $individualFormItemId . '&id=' . $id, false
		);

		$this->setRedirect($individualFormLink);

		return true;
	}
}
