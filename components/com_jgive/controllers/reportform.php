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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\FormController;

JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);

/**
 * The reportform controller
 *
 * @since  2.2.0
 */
class JgiveControllerReportform extends FormController
{
	/**
	 * Function to handle the form request when the user clicks the cancel button
	 *
	 * @param   string  $key  An alphanumeric key
	 *
	 * @return void
	 *
	 * @since  2.2.0
	 */
	public function cancel($key = null)
	{
		parent::cancel($key);
		$jgiveFrontendHelper = new JgiveFrontendHelper;

		$input          = Factory::getApplication()->input;
		$campaignId     = $input->get('cid', 0, 'INT');
		$campaignItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default&id=' . $campaignId);
		$redirect       = Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $campaignId . '&Itemid=' . $campaignItemId, false);

		$this->setRedirect($redirect);
	}

	/**
	 * Function handing the save for adding a new report record
	 *
	 * @param   string  $key     An alphanumeric key
	 *
	 * @param   string  $urlVar  The URL
	 *
	 * @return  Boolean
	 *
	 * @since  2.2.0
	 */
	public function save($key = null, $urlVar = null)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app        = Factory::getApplication();
		$input      = $app->input;
		$campaignId = $input->get('cid', 0, 'INT');
		$reportId   = $input->get('id', 0, 'INT');
		$model      = $this->getModel('reportform');

		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$campaignItemId      = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default&id=' . $campaignId);

		$redirect = Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $campaignId . '&Itemid=' . $campaignItemId, false);

		if (!Factory::getUser()->authorise('core.create', 'com_jgive'))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return;
		}

		JLoader::register('JGiveModelCampaignForm', JPATH_SITE . '/components/com_jgive/models/campaignform.php');
		$modelCampaignForm      = BaseDatabaseModel::getInstance('CampaignForm', 'JGiveModel');
		$campaignData           = $modelCampaignForm->getItem($campaignId);

		if ($campaignData->creator_id != Factory::getUser()->get('id', 0))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return;
		}

		$data    = $input->get('jform', array(), 'array');
		$context = "$this->option.edit.$this->context";
		$form    = $model->getForm($data, false);
		$attachments = array_filter($data['attachmentsvalue']);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			$redirect = (string) Uri::getInstance();
			$errors     = $model->getErrors();

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

			$app->setUserState($context . '.data', $data);

			$this->setRedirect($redirect);

			return false;
		}

		if ($reportId > 0)
		{
			$validData['id']  = $reportId;
		}

		$validData['created_by']  = Factory::getUser()->get('id', 0);
		$validData['created']     = Factory::getDate()->toSQL();
		$validData['campaign_id'] = $campaignId;
		$validData['attachments'] = $attachments;

		if (!$model->save($validData))
		{
			$app->setUserState($context . '.data', $validData);
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');

			$this->setRedirect($redirect);

			return false;
		}

		$app->setUserState($context . '.data', null);

		$this->setRedirect($redirect, Text::_('COM_JGIVE_REPORT_CREATE_REPORT_SUCCESS'));

		return true;
	}

	/**
	 * Function to redirect the user to the create report form
	 *
	 * @return  void
	 *
	 * @since  2.2.0
	 */
	public function add()
	{
		$input = Factory::getApplication()->input;
		$post  = $input->post;
		$cid   = $post->get('cid', 0, 'INT');

		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$campaignItemId      = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default&id=' . $cid);

		$redirect = Route::_('index.php?option=com_jgive&view=reportform&id=0&cid=' . $cid . '&Itemid=' . $campaignItemId, false);
		$this->setRedirect($redirect);
	}

	/**
	 * Function to delete the report
	 *
	 * @return  void
	 *
	 * @since  2.2.0
	 */
	public function delete()
	{
		// Prevent CSRF attack
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		$app        = Factory::getApplication();
		$jinput     = $app->input;
		$campaignId = $jinput->get('cid', 0, 'INT');
		$reportId   = $jinput->get('id', 0, 'INT');

		// Check ownership
		$modelCampaignForm = BaseDatabaseModel::getInstance('CampaignForm', 'JGiveModel');

		$reportTable = Table::getInstance('report', 'JgiveTable');
		$reportTable->load(array('id' => $reportId));

		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jgive/tables');
		$campaignTable = Table::getInstance('campaign', 'JgiveTable');
		$campaignTable->load(array('id' => $reportTable->campaign_id));

		$vendorId   = $campaignTable->vendor_id;
		$authorised = $modelCampaignForm->checkOwnership($vendorId, 'delete');

		if (!$authorised)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$modelMedia          = BaseDatabaseModel::getInstance('Media', 'JGiveModel');
		$mediaXrefLib        = TJMediaXref::getInstance();
		$mediaData           = $mediaXrefLib->retrive($data = array('clientId' => $reportId, 'client' => 'com_jgive.reports', 'isGallery' => 0, false));
		$mediaAttachmentData = $mediaXrefLib->retrive($data = array('clientId' => $reportId, 'client' => 'com_jgive.reportAttachment', 'isGallery' => 0));

		if (isset($mediaData[0]->media_id) || !empty($mediaAttachmentData))
		{
			// Delete corresponding media
			$params      = ComponentHelper::getParams('com_jgive');
			$storagePath = $params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');
			$deleteMedia = $modelMedia->deleteMedia($mediaData[0]->media_id, $storagePath, 'com_jgive.reports', $reportId);
			$flag        = true;

			// Delete corresponding attachments
			if (!empty($mediaAttachmentData))
			{
				foreach ($mediaAttachmentData as $attachment)
				{
					$deleteReportAttachment = $this->getModel('reportform')->deleteAttachment($campaignId, $reportId, $attachment->media_id);

					if ($deleteReportAttachment['success'] == false)
					{
						$flag = false;
						break;
					}
				}
			}

			if ($deleteMedia || $flag === true)
			{
				// Delete reports now if media is deleted successfully
				if ($this->getModel('reportform')->delete($reportId))
				{
					$reportId = (int) $reportId;
					Factory::getApplication()->triggerEvent('onAfterJgiveReportDelete', array($reportId));
					$app->enqueueMessage(Text::_('COM_JGIVE_REPORTS_SUCCESSFUL_DELETION'));
				}
			}
			else
			{
				$app->enqueueMessage(Text::_('COM_JGIVE_REPORTS_DELETION_FAILED'), 'error');
			}
		}
		else
		{
			// Delete reports even if there is no media associated with it
			if ($this->getModel('reportform')->delete($reportId))
			{
				$reportId = (int) $reportId;
				Factory::getApplication()->triggerEvent('onAfterJgiveReportDelete', array($reportId));
				$app->enqueueMessage(Text::_('COM_JGIVE_REPORTS_SUCCESSFUL_DELETION'));
			}
		}

		// Get the campaign item id
		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$campaignItemId      = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default&id=' . $campaignId);

		$redirect = Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $campaignId . '&Itemid=' . $campaignItemId, false);
		$this->setRedirect($redirect);
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
	public function edit($key = 'report.id', $urlVar = 'id')
	{
		parent::edit($key, $urlVar);
		$jgiveFrontendHelper = new JgiveFrontendHelper;

		$app            = Factory::getApplication();
		$input          = $app->input;
		$campaignId     = $input->get('cid', 0, 'INT');
		$reportId       = $input->get('id', 0, 'INT');
		$campaignItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default&id=' . $campaignId);

		$editReportLink = Route::_(
			'index.php?option=com_jgive&view=reportform&id=' . $reportId . '&cid=' . $campaignId . '&Itemid=' . $campaignItemId, false
		);

		$this->setRedirect($editReportLink);
	}
}
