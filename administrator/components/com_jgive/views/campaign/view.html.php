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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

JLoader::import('filterFields', JPATH_SITE . '/components/com_tjfields');

/**
 * jgive View
 *
 * @since  0.0.1
 */
class JGiveViewCampaign extends HtmlView
{
	use TjfieldsFilterField;

	public $form = null;

	protected $item;

	protected $isAdmin;

	protected $campaignGalleryImage;

	protected $campaignMainImage;

	protected $mediaGalleryObj;

	protected $daysConfig;

	protected $default_country;

	protected $hideFields;

	protected $allowedType;

	protected $imageGallery;

	protected $videoGallery;

	protected $hideShowFields;

	protected $allowedFileExtensions;

	protected $imageUploadLimit;

	protected $videoUploadLimit;

	protected $countries;

	protected $form_extra;

	protected $js_groups;

	protected $integration;

	protected $allowedMediaCount;

	protected $allowedVideoCount;

	/**
	 * Display the campaign view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		// Get the Data

		$app                         = Factory::getApplication();
		$input                       = $app->input;
		$this->form                  = $this->get('Form');
		$this->item                  = $this->get('Item');
		$this->com_params            = ComponentHelper::getParams('com_jgive');
		$this->isAdmin               = $app->isClient("administrator");
		$this->campaignGalleryImage  = $this->com_params->get('front_campaign_gallery_view', 'media_s');
		$this->campaignMainImage     = $this->com_params->get('front_campaign_detail_view', 'media_s');
		$this->mediaGalleryObj       = 0;
		$this->daysConfig            = $this->com_params->get('campaign_period_in_days');
		$this->default_country       = $this->com_params->get('default_country');
		$this->hideFields            = $this->com_params->get('creatorfield', array(), 'ARRAY');
		$this->allowedType           = $this->com_params->get('camp_type');
		$this->imageGallery          = $this->com_params->get('img_gallery');
		$this->videoGallery          = $this->com_params->get('video_gallery');
		$this->hideShowFields        = $this->com_params->get('show_selected_fields');
		$this->allowedFileExtensions = $this->com_params->get('allowedFileExtensions');
		$this->imageUploadLimit      = $this->com_params->get('max_size', '1024');
		$this->videoUploadLimit      = $this->com_params->get('max_video_file_size', '10');
		$this->integration           = $this->com_params->get('integration', 'joomla');
		$this->allowedMediaCount     = $this->com_params->get('max_images', '6');
		$this->allowedVideoCount     = $this->com_params->get('max_videos', '10');

		// Get js group for loggend in user
		$this->js_groups = $this->get('JS_usergroup');

		$jgiveFrontendHelper         = new jgiveFrontendHelper;
		$this->countries             = $jgiveFrontendHelper->getCountries();

		if (!empty($this->item))
		{
			$input->set("content_id", $this->item->id);
			$this->form_extra = array();

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaign');
			$jGiveModelCampaign = BaseDatabaseModel::getInstance('Campaign', 'JGiveModel');
			$this->form_extra = $jGiveModelCampaign->getFormExtra(
				array(
					"category" => $this->item->category_id,
					"clientComponent" => 'com_jgive',
					"client" => 'com_jgive.campaign',
					"view" => 'campaign',
					"layout" => 'edit'
				)
			);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
		$input = Factory::getApplication()->input;

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);

		if ($isNew)
		{
			$title = Text::_('COM_JGIVE') . ": " . Text::_('COM_JGIVE_CREATE_NEW_CAMPAIGN');
		}
		else
		{
			$title = Text::_('COM_JGIVE') . ": " . Text::_('COM_JGIVE_EDIT_CAMPAIGN');
		}

		ToolbarHelper::title($title, 'pencil.png');
		ToolbarHelper::apply('campaign.apply');
		ToolbarHelper::save('campaign.save');
		ToolbarHelper::save2new('campaign.save2new');

		ToolbarHelper::cancel('campaign.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
