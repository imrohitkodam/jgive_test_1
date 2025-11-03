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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;

JLoader::import('report', JPATH_SITE . '/components/com_jgive/models');

/**
 * JgiveViewCampaigns
 *
 * @package     Jgive
 * @subpackage  Jgive controller
 * @since       2.2.0
 */
class JgiveViewReportform extends HtmlView
{
	protected $item;

	protected $form = null;

	protected $script;

	protected $canDo;

	protected $params;

	protected $allowedFileExtensions;

	protected $imageUploadLimit;

	protected $allowedMediaCount;

	protected $isAdmin;

	protected $campaignMainImage;

	protected $allowed_report_attachments;

	protected $max_report_attachment_size;

	protected $max_report_attachments;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the layout file to parse.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		$this->item   = $this->get('Item');
		$this->form   = $this->get('Form');
		$this->script = $this->get('Script');

		// Get the current user id
		$createdByUserId = (int) $this->item->created_by;

		// Get the vendor id
		JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$vendorId            = $tjvendorFrontHelper->checkVendor($createdByUserId, 'com_jgive');

		// Check ownership
		$modelCampaignForm 	 = BaseDatabaseModel::getInstance('CampaignForm', 'JGiveModel');
		$authorised = $modelCampaignForm->checkOwnership($vendorId, 'save');

		if (!$authorised)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->canDo                      = ContentHelper::getActions('com_jgive');
		$this->params                     = ComponentHelper::getParams('com_jgive');
		$this->allowedFileExtensions      = $this->params->get('allowedFileExtensions');
		$this->imageUploadLimit           = $this->params->get('max_size', '1024');
		$this->allowedMediaCount          = $this->params->get('max_images', '6');
		$this->campaignMainImage          = $this->params->get('front_campaign_detail_view', 'media_s');
		$this->allowed_report_attachments = $this->params->get('allowed_report_attachments', 'doc,docx,pdf,ppt,pptx');
		$this->max_report_attachment_size = $this->params->get('max_report_attachment_size', '6');
		$this->max_report_attachments     = $this->params->get('max_report_attachments', '6');

		if (!($this->canDo->get('core.create')))
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return;
		}

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$reportId = $input->get('id', 0, 'INT');

		$this->_prepareDocument($reportId);
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @param   integer  $reportId  id of the report
	 *
	 * @return null
	 *
	 * @throws Exception
	 */
	protected function _prepareDocument($reportId)
	{
		$app = Factory::getApplication();

		if ($reportId == 0)
		{
			$title = Text::sprintf('COM_JGIVE_ADD_REPORT_PAGE_TITLE', $app->get('sitename'));
			$this->params->def('page_heading', $title);
		}
		else
		{
			$title = Text::sprintf('COM_JGIVE_EDIT_REPORT_PAGE_TITLE', $app->get('sitename'));
			$this->params->def('page_heading', $title);
		}

		$this->document->setTitle($title);
	}
}
