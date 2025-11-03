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

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;

JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
JLoader::import('vendorclientxref', JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');
/**
 * JgiveViewCampaign form view class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JGiveViewCampaign extends HtmlView
{
	protected $isAdmin = 0;

	protected $utilitiesObj;

	/**
	 * Class constructor.
	 *
	 * @param   Array  $config  Config
	 *
	 * @since   1.8
	 */
	public function __construct($config = array())
	{
		$this->app = Factory::getApplication();
		parent::__construct($config = array());
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$user = Factory::getUser();
		$this->logged_userid = $user->id;
		$app = Factory::getApplication();
		$input = $this->app->input;
		$this->utilitiesObj = JGive::utilities();

		if ($app->isClient("administrator"))
		{
			$this->isAdmin = 1;
		}

		$layout = $input->get('layout', 'default', 'STRING');
		$this->setLayout($layout);

		// Get params
		$this->params         = ComponentHelper::getParams('com_jgive');
		$this->hideFields     = $this->params->get('creatorfield', array(), 'ARRAY');
		$this->hideShowFields = $this->params->get('show_selected_fields');
		$this->enableReports  = $this->params->get('enable_reports');

		// Create jgive helper object
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		if (($this->params->get('integration') == 'jomsocial') && $this->params->get('jomsocial_toolbar'))
		{
			$this->jomsocialToolbarHtml = $jgiveFrontendHelper->jomsocialToolbarHtml();
		}

		$this->item = $this->get('Item');
		Factory::getApplication()->triggerEvent('onGetActivityScript', array('campaignfeed'));

		// Get campaign details
		$data  = $this->item;

		// Initialize variable
		$showImageGallery = $showVideoGallery = 0;

		// Check whether video gallery exists then overwrite the variable initialized
		if (isset($data['campaign']->gallery))
		{
			foreach ($data['campaign']->gallery as $gallery)
			{
				// Overwrite video gallery flag as soon as the first video type gallery is hit
				if (strstr($gallery['type'], 'video') && $showVideoGallery != 1)
				{
					$showVideoGallery = 1;
				}

				// Overwrite video gallery flag as soon as the first image type gallery is hit
				if (strstr($gallery['type'], 'image') && $showImageGallery != 1)
				{
					$showImageGallery = 1;
				}
			}
		}

		$data['video'] = $showVideoGallery;
		$data['image'] = $showImageGallery;
		$cdata         = $data;

		// Added here whatever data need for Campaign detail page
		$cdata['otherData']                      = new stdClass;
		$cdata['otherData']->singleCampItemid    = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default');
		$cdata['otherData']->allCampItemid       = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		$cdata['otherData']->createCampItemid    = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaignform');
		$cdata['otherData']->dashboardCampItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=dashboard&layout=default');
		$cdata['otherData']->loggedUserId        = $user->id;
		$cdata['otherData']->canEdit             = $user->authorise('core.edit', 'com_jgive');
		$cdata['params']                         = $this->params;
		$cdata['language']                       = new stdClass;
		$cdata['language']->lang                 = Factory::getLanguage()->getTag();

		$this->cdata = $cdata;
		$this->cdata['campaign']->id = (int) $this->cdata['campaign']->id;

		// Do not show campaign if it is unpublished And redirect to all campaigns
		if (!$cdata['campaign']->published && $layout != 'default_playvideo')
		{
			$itemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
			$link   = Route::_('index.php?option=com_jgive&view=campaigns&layout=all&Itemid=' . $itemid, false);
			$msg    = Text::_('COM_JGIVE_CAMPAIGN_NOT_PUBLISHED');
			$this->app->enqueueMessage($msg);
			$this->app->redirect($link);
		}

		$pathway = $this->app->getPathway();
		$pathway->addItem($cdata['campaign']->title, '');
		$modelCampaign = $this->getModel('campaign');
		$content_id    = $this->cdata['campaign']->id;
		$input->set("content_id", $content_id);

		$campaignformModelObject = JGive::model('campaignform', array('ignore_request' => true));
		$this->form_extra = $campaignformModelObject->getFormExtra(
			array(
				"category" => $this->cdata['campaign']->category_id,
				"clientComponent" => 'com_jgive',
				"client" => 'com_jgive.campaign',
				"view" => 'campaign',
				"layout" => 'default',
				"content_id" => $this->cdata['campaign']->id)
		);

		if (!empty($this->form_extra))
		{
			$xmlFileName = "campaignform_extra" . "." . "xml";

			if (File::exists(JPATH_SITE . "/components/com_jgive/models/forms/" . $xmlFileName))
			{
				$this->formXml = simplexml_load_file(JPATH_SITE . "/components/com_jgive/models/forms/" . $xmlFileName);
			}
		}

		$this->extraData = $modelCampaign->getDataExtra($this->cdata['campaign']->id);

		if ($layout == 'default_playvideo')
		{
			$vid              = $input->get('vid', 0, 'INTEGER');
			$jgiveMediaHelper = new JgiveMediaHelper;

			$this->video_params = array();
			$this->video_params['client'] = $input->get('option', '', 'STRING');
			$this->video_params = $jgiveMediaHelper->getVideoParams($vid);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$this->app->enqueueMessage(Text::_('COM_JGIVE_EXPORT_FILE_ERROR'));

			return false;
		}

		if ($this->enableReports)
		{
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'Reports');
			$modelReports = BaseDatabaseModel::getInstance('Reports', 'JGiveModel');
			$modelReports->setState('filter.campaign_id', $this->cdata['campaign']->id);

			$this->reports        = $modelReports->getItems();
			$this->total_reports = $modelReports->getTotal();
		}

		$this->_prepareDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Function For adding meta tags
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		if (isset($this->cdata['campaign']->meta_data))
		{
			// Get meta_data value
			if ($this->cdata['campaign']->meta_data)
			{
				$this->document->setMetadata('keywords', $this->cdata['campaign']->meta_data);
			}
			elseif (!$this->cdata['campaign']->meta_data && $this->params->get('menu-meta_keywords'))
			{
				// If the meta data is empty get the default menu meta_data value
				$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}
		}

		if (isset($this->cdata['campaign']->meta_desc))
		{
			if ($this->cdata['campaign']->meta_desc)
			{
				$this->document->setDescription($this->cdata['campaign']->meta_desc);
			}
			elseif (!$this->cdata['campaign']->meta_desc && $this->params->get('menu-meta_description'))
			{
				$this->document->setDescription($this->params->get('menu-meta_description'));
			}
		}

		// This robots tag is used for tells to search engine what link is follow
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
