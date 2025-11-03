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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
JLoader::import('vendorclientxref', JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/models');
include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * JgiveViewCampaign form view class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JGiveViewCampaignForm extends HtmlView
{
	protected $app;

	protected $adaptivePayment;

	protected $allowed;

	protected $allowedFileExtensions;

	protected $allowedType;

	protected $admin_approval;

	protected $cdata;

	protected $campaignGalleryImage;

	protected $countries;

	protected $campaignMainImage;

	protected $checkGatewayDetails;

	protected $checkVendorApproval;

	protected $commission_fee;

	protected $campaignId;

	protected $daysConfig;

	protected $default_country;

	protected $form_extra;

	protected $form = null;

	protected $hideFields;

	protected $hideShowFields;

	protected $imageGallery;

	protected $isAdmin;

	protected $item;

	protected $imageUploadLimit;

	protected $mediaGalleryObj;

	protected $model;

	protected $params;

	protected $send_payments_to_owner;

	protected $silentVendor;

	protected $state;

	protected $videoUploadLimit;

	protected $videoGallery;

	protected $vendorCheck;

	protected $allowedMediaCount;

	protected $allowedVideoCount;

	protected $allCampItemid;

	protected $editVendor;

	protected $vendorProfileStatus;
	
	protected $profile_complete;

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
		$app  = Factory::getApplication();

		// Get the Data
		$this->form                  = $this->get('Form');
		$this->item                  = $this->get('Item');
		$this->state                 = $this->get('State');
		$this->model                 = BaseDatabaseModel::getInstance('campaignForm', 'JGiveModel');
		$this->params                = ComponentHelper::getParams('com_jgive');
		$this->campaignGalleryImage  = $this->params->get('front_campaign_gallery_view', 'media_s');
		$this->campaignMainImage     = $this->params->get('front_campaign_detail_view', 'media_s');
		$this->mediaGalleryObj       = 0;
		$this->daysConfig            = $this->params->get('campaign_period_in_days');
		$this->default_country       = $this->params->get('default_country');
		$this->allowedFileExtensions = $this->params->get('allowedFileExtensions');
		$this->imageUploadLimit      = $this->params->get('max_size', '1024');
		$this->videoUploadLimit      = $this->params->get('max_video_file_size', '10');
		$this->silentVendor          = $this->params->get('silent_vendor');
		$this->allowedMediaCount     = $this->params->get('max_images', '6');
		$this->allowedVideoCount     = $this->params->get('max_videos', '10');

		$jgiveFrontendHelper        = new jgiveFrontendHelper;
		$this->countries            = $jgiveFrontendHelper->getCountries();
		$this->hideFields           = $this->params->get('creatorfield');
		$this->allowedType          = $this->params->get('camp_type');
		$this->imageGallery         = $this->params->get('img_gallery');
		$this->videoGallery         = $this->params->get('video_gallery');
		$this->hideShowFields       = $this->params->get('show_selected_fields');
		$this->adaptivePayment[]    = $this->params->get('gateways');
		$tjvendorFrontHelper        = new TjvendorFrontHelper;
		$this->vendorCheck          = $tjvendorFrontHelper->checkVendor('', 'com_jgive');
		$this->checkGatewayDetails  = $tjvendorFrontHelper->checkGatewayDetails($user->id, 'com_jgive');
		$vendorXrefTable            = Table::getInstance('vendorclientxref', 'TjvendorsTable', array());

		// Check access
		if ($this->item->creator_id != $user->id)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$vendorXrefTable->load(
			array(
				'vendor_id' => $this->vendorCheck,
				'client' => 'com_jgive'
			)
		);

		$this->checkVendorApproval = $vendorXrefTable->approved;

		if (($this->vendorCheck && $this->silentVendor == 0) || $this->silentVendor == 1)
		{
			$this->allowed = 1;
		}
		else
		{
			$this->allowed = 0;
		}

		$layout = $this->app->input->get('layout', 'default', 'STRING');
		$this->setLayout($layout);
		$this->vendorProfileMenuId = $jgiveFrontendHelper->getItemId('index.php?option=com_tjvendors&view=vendor&client=com_jgive');
		$this->editVendor = Uri::root() .
		substr(
				Route::_(
					'index.php?option=com_tjvendors&view=vendor&layout=edit&client=com_jgive&vendor_id=' .
					$this->vendorCheck . '&Itemid=' . $this->vendorProfileMenuId
			), strlen(Uri::base(true)) + 1
		);

		$this->vendorProfileStatus = TJVendors::vendor()->getVendorProfileStatus($user->id, 'com_jgive');
		
		$this->profile_complete       = $this->params->get('profile_complete');
		
		// if user is not admin, and profilestatus < 100 && $this->profile_complete =1 (Force users to complete profile before creating campaign = yes)
		// then redirect vendor to profile edit page
		if( !$app->isClient("administrator") && $this->vendorProfileStatus < 100 && $this->profile_complete){
			
			$app->enqueueMessage(Text::_('COM_JGIVE_USER_VENDOR_INCOMPLETE_PROFILE'), 'error');
			$app->redirect('index.php?option=com_tjvendors&view=vendor&layout=editinfo&client=com_jgive&vendor_id='.$this->vendorCheck. '&Itemid=' . $this->vendorProfileMenuId);
		}

		if (!$user->id && $layout == "default")
		{
			$msg = Text::_('COM_JGIVE_MESSAGE_LOGIN_FIRST_CAMPAIGN_FORM');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		if (!empty($this->item->gallery))
		{
			$this->mediaGalleryObj = json_encode($this->item->gallery);
		}

		if ($app->isClient("administrator"))
		{
			$this->isAdmin = 1;
		}
		else
		{
			$this->isAdmin = 0;
		}
		// Get Campaign data
		$cdata                   = $this->get('item');
		$this->cdata['campaign'] = (object) json_decode(json_encode($cdata), true);
		$this->campaignId        = (int) $this->cdata['campaign']->id;

		// Get params
		$this->params                 = ComponentHelper::getParams('com_jgive');
		$this->commission_fee         = $this->params->get('commission_fee');
		$this->send_payments_to_owner = $this->params->get('send_payments_to_owner');
		$this->default_country        = $this->params->get('default_country');
		$this->admin_approval         = $this->params->get('admin_approval');
		$this->allCampItemid          = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');

		if (!empty($this->item))
		{
			$input = $app->input;
			$input->set("content_id", $this->item->id);
			$this->form_extra = array();

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaignform');
			$jGiveModelCampaign = BaseDatabaseModel::getInstance('Campaignform', 'JGiveModel');

			// The function getFormExtra is defined in Tj-fields filterFields trait.
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

		$authorised = false;

		if (empty($this->item->id))
		{
			$authorised = $user->authorise('core.create', 'com_jgive');
		}
		else
		{
			$authorised_own = $user->authorise('core.edit', 'com_jgive');

			if ($authorised_own)
			{
				$authorised = true;

				// Check if logged in user is event created_by.
				if ($this->item->creator_id != $user->id)
				{
					$authorised = false;
				}
			}
		}

		if ($authorised !== true)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		if ($layout == "social_integration")
		{
			$this->allowVendorToShareCampaign = $this->params->get('allow_vendor_to_share_campaign', 1, 'Integer');
			$this->socialSharingOptions       = $this->params->get('social_sharing_options', array(), 'Array');
			$input                            = Factory::getApplication()->input;
			$this->campaignId                 = $input->get('id', 0, 'INT');
			$this->campaignTitle              = JGive::campaign($this->campaignId)->getTitle();
			$this->campaignLink  = Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $this->campaignId, false);
			$this->campaignLink  = Uri::root() . substr($this->campaignLink, strlen(Uri::base(true)) + 1);
			$this->encodedUrl    = urlencode($this->campaignLink);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('<br />', $errors), 500);
		}

		// Display the template
		parent::display($tpl);
	}
}
