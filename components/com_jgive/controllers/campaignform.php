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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * Campaign controller class
 *
 * @since  0.0.9
 */
class JGiveControllerCampaignForm extends FormController
{
	/**
	 * Method to save campaign's data.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean|void
	 *
	 * @since  2.1
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$model      = $this->getModel('CampaignForm', 'JGiveModel');
		$app        = Factory::getApplication();
		$data       = $app->input->get('jform', array(), 'array');
		$com_params = ComponentHelper::getParams('com_jgive');

		$allJformData = $data;

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new Exception($model->getError());
		}

		$silentVendor = $com_params->get('silent_vendor');

		if ($silentVendor == 0)
		{
			$TjvendorFrontHelper = new TjvendorFrontHelper;
			$checkVendor         = $TjvendorFrontHelper->checkVendor(Factory::getUser()->id, 'com_jgive');

			// Check if user is a vendor
			if (empty($checkVendor))
			{
				return false;
			}
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

		// Condition to check a valid date. If start date is not a valid date "strrtotime" returns false
		if (!strtotime($data['start_date']))
		{
			$date = Factory::getDate();
		}
		else
		{
			$date = Factory::getDate($data['start_date']);
		}

		$com_params = ComponentHelper::getParams('com_jgive');
		$daysLimit  = $com_params->get('campaign_period_in_days', '0', 'INT');

		if ($daysLimit)
		{
			$date->modify("+" . $data['days_limit'] . " day");
			$data['end_date'] = $date->toSql(true);
		}

		$data['creator_id'] = Factory::getUser()->id;

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

			// Save the data in the session. Tweak
			$app->setUserState('com_jgive.edit.campaignform.data', $allJformData);

			// Tweak *important
			$app->setUserState('com_jgive.edit.campaignform.id', $allJformData['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jgive.edit.campaignform.id');
			$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaignform&layout=default&id=' . $id, false));

			return false;
		}

		$extraFieldData   = array();

		if (!empty($data['id']))
		{
			$extraJformData = array_diff_key($allJformData, $data);
			$filesData      = $app->input->files->get('jform', array(), 'ARRAY');
			unset($filesData['image']);
			unset($filesData['gallery_file']);
			unset($filesData['givebacks']);
			unset($extraJformData['terms_condition']);
			unset($extraJformData['option_city']);

			$extraJformData                = array_merge_recursive($extraJformData, $filesData);
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
			$userPrivacyJformData = array_diff_key($allJformData, $data);

			if (!empty($userPrivacyJformData['terms_condition']) && $userPrivacyJformData['terms_condition'] == 'on')
			{
				$userPrivacyJformData['accepted'] = 1;
				$data['userPrivacyJformData']     = $userPrivacyJformData;
			}
			else
			{
				$app->setUserState('com_jgive.edit.campaignform.data', $allJformData);
				$id = $app->getUserState('com_jgive.edit.campaignform.data.id');
				$this->setMessage(Text::_('COM_JGIVE_SAVE_CAMPAIGN_FAILED_MSG'), 'warning');
				$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaignform&layout=default&id=' . $id, false));

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
			// Save the data in the session.
			$app->setUserState('com_jgive.edit.campaignform.data', $allJformData);

			$id = $app->getUserState('com_jgive.edit.campaignform.data.id');
			$this->setMessage(Text::_('COM_JGIVE_SAVE_CAMPAIGN_FAILED_MSG'), 'warning');

			$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaignform&layout=default&id=' . $id, false));

			return false;
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jgive.edit.campaignform.id', null);

		// Redirect to the list screen.
		$link = 'index.php?option=com_jgive&view=campaigns&layout=my';

		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$itemId              = $jgiveFrontendHelper->getItemId($link);
		$link .= $link . "&Itemid=" . $itemId;

		// Check this function
		$redirect = Route::_($link);
		$msg      = Text::_('COM_JGIVE_MSG_SUCCESS_SAVE_CAMPAIGN');

		$allowVendorToShareCampaign = $com_params->get('allow_vendor_to_share_campaign', 1, 'Integer');
		$socialSharingOptions       = $com_params->get('social_sharing_options', array(), 'Array');

		// Here we check is allows to show social media options to the campaign owner. And the vendor has created a new campaign.
		if ($allowVendorToShareCampaign && !empty($socialSharingOptions) && empty($data['id']))
		{
			$campaignClassObj = JGive::campaign($return);
			$isPublished      = $campaignClassObj->getStatus();

			if ($isPublished)
			{
				$this->setRedirect(Route::_('index.php?option=com_jgive&view=campaignform&layout=social_integration&id=' . $return . '&Itemid=' . $itemId));
			}
			else
			{
				$this->setRedirect($redirect, $msg);
			}
		}
		else
		{
			$this->setRedirect($redirect, $msg);
		}

		// Flush the data from the session.
		$app->setUserState('com_jgive.edit.campaignform.data', null);
	}

	/**
	 * cancel a campaign form
	 *
	 * @param   integer  $key  The key
	 *
	 * @return  boolean|void  Incase of error boolean and in case of success void
	 *
	 * @since   2.3.4
	 */
	public function cancel($key = null)
	{
		$recordId            = $this->input->getInt('id');
		$link                = 'index.php?option=com_jgive&view=campaigns&layout=all';
		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$itemId              = $jgiveFrontendHelper->getItemId($link);
		$link .= $link . "&Itemid=" . $itemId;
		$redirect            = Route::_($link);

		// Attempt to check-in the current record.
		if ($recordId)
		{
			$this->setRedirect($redirect);

			return false;
		}

		// Clean the session data and redirect.
		$this->releaseEditId("com_jgive.edit.campaignform", $recordId);
		Factory::getApplication()->setUserState('com_jgive.edit.campaignform.data', null);

		// Check if there is a return value
		$return = $this->input->get('return', null, 'base64');

		if (!is_null($return) && Uri::isInternal(base64_decode($return)))
		{
			$redirect = base64_decode($return);
		}

		// Redirect to the list screen.
		$this->setRedirect(Route::_($redirect, false));

		return true;
	}

	/**
	 * Method to send campaign announcement emails to past donors.
	 *
	 * This method fetches campaigns for which emails haven't been sent yet,
	 * retrieves donor email addresses based on the campaign's category, and
	 * sends notification emails to those donors. After successfully sending,
	 * it updates the campaign record to mark the email as sent.
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	public function sendCampaignEmails()
	{
		JLoader::register('JGiveMailsHelper', JPATH_SITE . '/components/com_jgive/helpers/mails.php');
		$model = $this->getModel('CampaignForm', 'JGiveModel'); // Adjust model name if needed
		$pendingCampaigns = $model->getPendingEmailCampaigns(); // Get pending campaigns (email_sent = 0)

		if (!empty($pendingCampaigns))
		{
			foreach ($pendingCampaigns as $campaign)
			{
				$emails = $model->getCategoryDonorEmails($campaign['id']); // Fetch past donors emails based on category

				if (!empty($emails))
				{
					foreach ($emails as $email)
					{
						$donorDetails = new stdClass;
						$donorDetails->email = $email;

						$campaignDetails = new stdClass;
						$campaignDetails->id    = $campaign['id'];
						$campaignDetails->name = $campaign['title']; // Assuming title field hai
				
						$mailHelper = new JGiveMailsHelper(); // Create object here
						$mailHelper->newCampaignAddedNotification($campaignDetails, $donorDetails);
					}
				}				

				// Update campaign to mark email as sent
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->update('#__jg_campaigns')
					->set('email_sent = 1')
					->where('id = ' . (int) $campaign['id']);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}
}
