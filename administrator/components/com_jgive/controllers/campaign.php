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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;

/**
 * JgiveControllerCampaign form controller class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JGiveControllerCampaign extends FormController
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
	 * @since   2.1
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Campaign', 'JGiveModel');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');

		if (empty($data['creator_id']))
		{
			$data['creator_id'] = Factory::getUser()->id;
		}

		// JForm tweak - Save all jform array data in a new array for later reference.
		$allJformData = $data;

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		// Code modified to store end date when days are set in the form.
		$date       = new Date($data['start_date']);
		$com_params = ComponentHelper::getParams('com_jgive');
		$daysLimit  = $com_params->get('campaign_period_in_days', '0', 'INT');

		if ($daysLimit)
		{
			$date->modify("+" . $data['days_limit'] . " day");
			$data['end_date'] = $date->toSql(true);
		}

		// Other city assigned to city
		if (!empty($data['other_city']) && $data['other_city'] == 1)
		{
			$data['city'] = $allJformData['option_city'];
			unset($allJformData['option_city']);
		}
		else
		{
			$data['other_city'] = 0;
		}

		// Galler media as main image/video
		if (isset($data['video_on_details_page']))
		{
			$data['video_on_details_page'] = 1;
		}
		else
		{
			$data['video_on_details_page'] = 0;
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

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
				else
				{
					$app->enqueueMessage($errors[$i], 'error');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_jgive.edit.campaign.data', $allJformData);

			// Tweak *important
			$app->setUserState('com_jgive.edit.campaign.id', $allJformData['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.campaign.id');
			$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaign&layout=edit&id=' . $id, false));

			return false;
		}

		$extraFieldData = array();

		if (!empty($data['id']))
		{
			$extraJformData = array_diff_key($allJformData, $data);
			$filesData = $app->input->files->get('jform', array(), 'ARRAY');

			unset($filesData['image']);
			unset($filesData['gallery_file']);
			unset($filesData['givebacks']);
			unset($extraJformData['terms_condition']);
			unset($extraJformData['option_city']);

			$extraJformData = array_merge_recursive($extraJformData, $filesData);
			$extraFieldData['content_id']  = $data['id'];
			$extraFieldData['client']      = 'com_jgive.campaign';
			$extraFieldData['fieldsvalue'] = $extraJformData;

			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
			$campaignTable = Table::getInstance('campaign', 'JgiveTable');
			$campaignTable->load($data['id']);

			$extraFieldData['created_by'] = $campaignTable->creator_id;
		}

		/*User Privacy Policy Data sanitize here*/
		if (!empty($com_params->get('enable_campaign_terms_conditions', 0)) && !empty($com_params->get('camp_create_terms_article', '0', 'INT')))
		{
			$userPrivacyJformData = array();

			/*User Privacy Policy Data sanitize here*/
			$userPrivacyJformData['privacy_terms_condition'] = Factory::getApplication()->input->get('terms_condition', '', 'STRING');

			if (!empty($userPrivacyJformData['privacy_terms_condition']) && $userPrivacyJformData['privacy_terms_condition'] == 'on')
			{
				$userPrivacyJformData['accepted'] = 1;
				$data['userPrivacyJformData'] = $userPrivacyJformData;
			}
			else
			{
				$app->setUserState('com_jgive.edit.campaign.data', $allJformData);

				// Tweak *important
				$app->setUserState('com_jgive.edit.campaign.id', $allJformData['id']);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_jgive.edit.campaign.id');
				$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaign&layout=edit&id=' . $id, false));

				return false;
			}
		}

		$return = $model->save($data);

		if (!empty($data['id']))
		{
			$model->saveExtraFields($extraFieldData);
		}

		// Check for errors.
		if ($return === false)
		{
			$app->setUserState('com_jgive.edit.campaign.data', $allJformData);

			// Redirect back to the edit screen.
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaign&layout=edit&id=' . $data['id'], false));

			return false;
		}

		$msg   = Text::_('COM_JGIVE_MSG_SUCCESS_SAVE_CAMPAIGN');
		$input = Factory::getApplication()->input;
		$id    = $input->get('id');

		if (empty($id))
		{
			$id = $return;
		}

		$task = $input->get('task');

		if ($task == 'apply')
		{
			$redirect = Route::_('index.php?option=com_jgive&view=campaign&layout=edit&id=' . $id, false);
			$app->enqueueMessage($msg, 'success');
			$app->redirect($redirect);
		}

		if ($task == 'save2new')
		{
			$redirect = Route::_('index.php?option=com_jgive&view=campaign&layout=edit', false);
			$app->enqueueMessage($msg, 'success');
			$app->redirect($redirect);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jgive.edit.campaign.id', null);

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Redirect to the list screen.
		$redirect = Route::_('index.php?option=com_jgive&view=campaigns', false);
		$app->enqueueMessage($msg, 'success');
		$app->redirect($redirect);

		// Flush the data from the session.
		$app->setUserState('com_jgive.edit.campaign.data', null);
	}

	/**
	 * Set Success State
	 *
	 * @return  void
	 */
	public function changeSuccessState()
	{
		$post  = Factory::getApplication()->input->post;
		$model = $this->getModel('campaign');
		$model->setState('request', $post);

		$cid = $post->get('hiddenCid');
		$successStatus = $post->get('hiddenSuccessStatus');
		$successStatus = $successStatus ? $successStatus : null;

		$campaignHelper = new campaignHelper;
		$result = $campaignHelper->updateCampaignSuccessStatus($cid, $successStatus, $orderId = 0);

		if ($result === true)
		{
			$msg = Text::_('COM_JGIVE_MSG_SUCCESS_STATUS_CHANGED_SUCCESS');
		}
		else
		{
			$msg = Text::_('COM_JGIVE_MSG_SUCCESS_STATUS_CHANGED_ERROR');
		}

		$link = 'index.php?option=com_jgive&view=campaigns&layout=default';
		$this->setRedirect($link, $msg);
	}
}
