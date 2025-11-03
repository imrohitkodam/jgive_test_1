<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Campaign controller class
 *
 * @since  0.0.9
 */
class JGiveControllerCampaign extends JGiveController
{
	/**
	 * Method to save campaign's data.
	 *
	 * @return  void
	 *
	 * @since  2.1
	 */
	public function save()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$model          = $this->getModel('Campaign', 'JGiveModel');
		$app            = Factory::getApplication();
		$data           = Factory::getApplication()->input->get('jform', array(), 'array');
		$all_jform_data = $data;

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
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

		if (!empty($form))
		{
			$data = $model->validate($form, $data);
		}

		$date       = new Date($data['start_date']);
		$com_params = ComponentHelper::getParams('com_jgive');
		$daysLimit  = $com_params->get('campaign_period_in_days', '0', 'INT');

		if ($daysLimit)
		{
			$date->modify("+" . $data['days_limit'] . " day");
			$data['end_date'] = $date->toSql(true);
		}

		$silentVendor = $com_params->get('silent_vendor');

		if ($silentVendor == 0)
		{
			$TjvendorFrontHelper = new TjvendorFrontHelper;
			$data['vendor_id']   = $TjvendorFrontHelper->checkVendor('', 'com_jgive');
		}

		$data['creator_id'] = Factory::getUser()->id;

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
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
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session. Tweak
			$app->setUserState('com_jgive.edit.campaign.data', $data);

			// Tweak *important
			$app->setUserState('com_jgive.edit.campaign.id', $data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.campaign.id');
			$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $id, false));

			return false;
		}

		$extra_jform_data = array_diff_key($all_jform_data, $data);

		// Check if form file is present.
		$filePath = JPATH_SITE . '/components/com_jgive/models/forms/campaignform_extra.xml';

		if (File::exists($filePath))
		{
			$category  = $data['category_id'];
			$client    = 'com_jgive.campaign';
			$extraData = array(
				"category" => $category,
				"clientComponent" => 'com_jgive',
				"client" => $client,
				"view" => 'campaign',
				"layout" => 'default'
			);

			// Validate the posted data.
			$formExtra = $model->getFormExtra($extraData);

			if (!$formExtra)
			{
				JError::raiseWarning(500, $model->getError());

				return false;
			}

			$formExtra = array_filter($formExtra);

			if (!empty($formExtra))
			{
				if (!empty($formExtra[0]))
				{
					// Validate the posted extra data.
					$extra_jform_data = $model->validateExtra($formExtra[0], $extra_jform_data);
				}
				else
				{
					// Validate the posted extra data.
					$extra_jform_data = $model->validateExtra($formExtra[1], $extra_jform_data);
				}
			}

			// Check for errors.
			if ($extra_jform_data === false)
			{
				// Get the validation messages.
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
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				// Save the data in the session.
				// Tweak.
				$app->setUserState('com_jgive.edit.campaign.data', $all_jform_data);

				// Tweak *important
				$app->setUserState('com_jgive.edit.campaign.id', $all_jform_data['id']);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_jgive.edit.campaign.id');
				$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $id, false));

				return false;
			}
		}

		$data['extra_jform_data'] = $extra_jform_data;
		$return                   = $model->save($data);

		// Clear the profile id from the session.
		$app->setUserState('com_jgive.edit.campaign.id', null);

		// Redirect to the list screen.
		$link = 'index.php?option=com_jgive&view=campaigns&layout=my';

		// Check this function
		$redirect = Route::_($link, false);
		$msg      = Text::_('COM_JGIVE_MSG_SUCCESS_SAVE_CAMPAIGN');

		$this->setRedirect($redirect, $msg);

		// Flush the data from the session.
		$app->setUserState('com_jgive.edit.campaign.data', null);
	}

	/**
	 * Function to save text activity
	 *
	 * @return  object  activities
	 *
	 * @since   1.6
	 */
	public function addPostedActivity()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$input = Factory::getApplication()->input;
		$activityData = array();
		$postText = $input->get('activity-post-text', '', 'STRING');
		$cid = $input->get('id', '0', 'INT');
		$activityData['postData'] = $postText;
		$activityData['type'] = 'text';
		$activityData['cid'] = $cid;

		$uri = Uri::getInstance();
		$url = $uri->toString();
		$redirect = Route::_($url, false);

		if (!empty($activityData['postData']))
		{
			// Trigger jgiveactivity plugin to add test activity
			PluginHelper::importPlugin('system');

			$result = Factory::getApplication()->triggerEvent('onPostActivity', array($activityData));

			if ($result[0]['error'])
			{
				$this->setRedirect($redirect, $result[0]['error'], 'error');
			}
			else
			{
				$msg = Text::_("COM_JGIVE_TEXT_ACTIVITY_POST_SUCCESS_MSG");
				$this->setRedirect($redirect, $msg);
			}
		}
	}

	/* Delete a campaign based on the provided campaign ID. */
    public function delete()
    {
        // Get the application instance
        $app = Factory::getApplication();

        // Retrieve the campaign ID from the request input
        $input = $app->input;
        $campaignId = $input->getInt("campaignId", 0);

        // Prepare the response structure
        $response = array(
        'success' => false,
        'message' => ''
		);
		// Check if a campaign ID is provided
        if ($campaignId)
        {
            // Get the table instance
            Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
            $table = Table::getInstance('Campaign', 'JGiveTable');
            // Load the campaign record by ID
            if ($table->load($campaignId))
            {
                // Delete the campaign
                if ($table->delete($campaignId))
                {
                    $response['success'] = true;
                    $response['message'] = Text::_('COM_JGIVE_CAMPAIGN_DELETED_SUCCESSFULLY');
                }
                else
                {
                    $response['message'] = Text::_('COM_JGIVE_CAMPAIGN_CANNOT_BE_DELETED');
                }
            }
        }
        // Output the JSON response
        echo json_encode($response);
        $app->close();
    }
}
