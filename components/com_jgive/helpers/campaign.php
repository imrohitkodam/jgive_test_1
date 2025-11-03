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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// Component Helper
JLoader::import('vendorclientxref', JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables');
Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/tables');
jimport('techjoomla.tjnotifications.tjnotifications');

/**
 * CampaignHelper class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class CampaignHelper
{
	protected $params;

	protected $app;

	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$this->params = ComponentHelper::getParams('com_jgive');
		$this->app    = Factory::getApplication();
	}

	/**
	 * Get Campaign Campaign Promoter
	 *
	 * @param   Integer  $orderId     Order Id
	 * @param   String   $pluginName  Payment gateway name
	 *
	 * @return  email
	 */
	public function getCampaignPromoterPaypalId($orderId, $pluginName)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('cam.vendor_id'));
		$query->from($db->quoteName('#__jg_campaigns', 'cam'));
		$query->join('LEFT', $db->quoteName('#__jg_orders', 'ord') . 'ON (' . $db->quoteName('ord.campaign_id') . ' = ' . $db->quoteName('cam.id') . ')');
		$query->where($db->quoteName('ord.id') . ' = ' . (int) $orderId);
		$db->setQuery($query);

		$vendorId = $db->loadResult();
		$vendorClientXrefTable = Table::getInstance('vendorclientxref', 'TjvendorsTable', array());
		$vendorClientXrefTable->load(array('vendor_id' => $vendorId, 'client' => 'com_jgive'));

		if ($vendorClientXrefTable->params != null || !empty($vendorClientXrefTable->params))
		{
			$paymentDetails = json_decode($vendorClientXrefTable->params)->payment_gateway;
		}

		if (!empty($paymentDetails))
		{
			foreach ($paymentDetails as $paymentDetail)
			{
				if ($paymentDetail->payment_gateways == $pluginName)
				{
					return $paymentDetail->payment_email_id;
				}
			}
		}
	}

	/**
	 * Get Campaign Title
	 *
	 * @param   INT  $orderid  Order id
	 *
	 * @return  STRING  Title
	 */
	public function getCampaignTitle($orderid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.title');
		$query->from($db->qn('#__jg_campaigns', 'c'));
		$query->join('LEFT', $db->qn('#__jg_orders', 'o') . ' ON (' . $db->qn('o.campaign_id') . ' = ' . $db->qn('c.id') . ')');
		$query->where($db->qn('o.id') . ' = ' . $db->quote($orderid));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get Campaign Title From Cid
	 *
	 * @param   Integer  $cid  Campaign Id
	 *
	 * @return  String  title
	 */
	public function getCampaignTitleFromCid($cid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.title');
		$query->from($db->qn('#__jg_campaigns', 'c'));
		$query->where($db->qn('c.id') . ' = ' . $db->quote($cid));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get Campaign Amounts
	 *
	 * @param   INT  $cid  Campaign Id
	 *
	 * @return mixed
	 */
	public function getCampaignAmounts($cid)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('goal_amount');
		$query->from($db->qn('#__jg_campaigns'));
		$query->where($db->qn('id') . ' = ' . (int) $cid);
		$db->setQuery($query);
		$goal_amount = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('SUM(o.amount) as amount_received');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->where($db->qn('o.campaign_id') . ' = ' . (int) $cid);
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$db->setQuery($query);
		$amounts                    = array();
		$amounts['amount_received'] = $db->loadResult();

		// If no donations, set receved amount as zero
		if ($amounts['amount_received'] == '')
		{
			$amounts['amount_received'] = 0;
		}

		// Calculate remaining amount
		$amounts['remaining_amount'] = ($goal_amount) - ($amounts['amount_received']);

		return $amounts;
	}

	/**
	 * Get Campaign Givebacks
	 *
	 * @param   INT  $cid  Campaign Id
	 *
	 * @return  Givebacks
	 */
	public function getCampaignGivebacks($cid)
	{
		JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
		JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);
		JLoader::register('JGiveModelMediaXref', JPATH_SITE . '/components/com_jgive/models/mediaxref.php');
		$modelMediaXref = BaseDatabaseModel::getInstance('MediaXref', 'JGiveModel');
		JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
		$filetable         = Table::getInstance('Files', 'TJMediaTable');
		$com_params        = ComponentHelper::getParams('com_jgive');
		$storagePath       = $com_params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->qn('#__jg_campaigns_givebacks', 'cg'));
		$query->where($db->qn('cg.campaign_id') . ' = ' . $db->quote($cid));
		$db->setQuery($query);
		$givebacks = $db->loadObjectList();
		$giveback_tooltip = Text::_('COM_JGIVE_BY_GIVEBACK');

		foreach ($givebacks as $giveback)
		{
			$sold_giveback = 0;
			$query = $db->getQuery(true);
			$query->select('COUNT(d.giveback_id) as sold_giveback');
			$query->from($db->qn('#__jg_donations', 'd'));
			$query->join('LEFT', $db->qn('#__jg_campaigns', 'c') . 'ON (' . $db->qn('c.id') . ' = ' . $db->qn('d.campaign_id') . ')');
			$query->join('LEFT', $db->qn('#__jg_orders', 'o') . 'ON (' . $db->qn('o.donation_id') . ' = ' . $db->qn('d.id') . ')');
			$query->where($db->qn('d.campaign_id') . ' = ' . $db->quote($giveback->campaign_id));
			$query->where($db->qn('d.giveback_id') . ' = ' . $db->quote($giveback->id));
			$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
			$db->setQuery($query);

			$giveback->sold_giveback = $sold_giveback = $db->loadResult();

			// Sold out flag
			$giveback->sold_out = 0;

			if ($sold_giveback == $giveback->total_quantity || $sold_giveback > $giveback->total_quantity)
			{
				// Set sold out flag
				$giveback->sold_out = 1;
			}

			/* In media_files_xref table giveback entry like client+givebacks+campaign Id
			For example com_jgive.givebacks.4 */
			$givebackMainImage = $modelMediaXref->getCampaignMedia($giveback->campaign_id, 'com_jgive.givebacks.' . $giveback->id, 0);

			if (!empty($givebackMainImage))
			{
				$filetable->load($givebackMainImage[0]->media_id);
				$mediaType            = explode(".", $filetable->type);
				$imgPath              = $storagePath . '/' . $mediaType[0] . 's';
				$mediaConfig          = array('id' => $givebackMainImage[0]->media_id, 'uploadPath' => $imgPath);
				$giveback->image_path = TJMediaStorageLocal::getInstance($mediaConfig);
			}

			$giveback->amount = JGive::utilities()->getRoundedAmount($giveback->amount);
		}

		return $givebacks;
	}

	/**
	 * Get Campaign Donors
	 *
	 * @param   integer  $cid          Camp Id
	 * @param   integer  $limit_start  Limit start
	 * @param   integer  $limit        Limit
	 * @param   integer  $getCsvData   Flag to get data for csv
	 *
	 * @return  Donors
	 */
	public function getCampaignDonors($cid, $limit_start = 0, $limit = 4, $getCsvData = 0)
	{
		$db    = Factory::getDbo();

		// Added 2 columns for groupby clause because if same user doing donation to same
		// Campaign, one as Anonymous 'yes' and another one is Anonymous 'no', there should be way to differentiate this

		// Get campaign donors
		$query = $db->getQuery(true);
		$query->select(
						$db->qn(
								array(
									'du.id', 'du.user_id', 'du.email', 'du.first_name', 'du.last_name', 'du.address',
									'du.address2', 'du.zip', 'du.donor_type', 'du.org_name',
									'ds.annonymous_donation', 'ds.giveback_id',
									'o.cdate', 'o.fee', 'o.processor'
								)
							)
					);
		$query->select('sum(o.original_amount) as amount');
		$query->select($db->qn('du.city', 'othercity'));
		$query->select($db->qn('gb.description', 'gb_description'));
		$query->select($db->qn('gb.amount', 'giveback_value'));

		$query->from($db->qn('#__jg_donors', 'du'));
		$query->join('LEFT', $db->qn('#__jg_donations', 'ds') . ' ON (' . $db->qn('ds.donor_id') . ' = ' . $db->qn('du.id') . ')');
		$query->join('LEFT', $db->qn('#__jg_orders', 'o') . ' ON (' . $db->qn('o.donation_id') . ' = ' . $db->qn('ds.id') . ')');
		$query->join('LEFT', $db->qn('#__jg_campaigns_givebacks', 'gb') . ' ON (' . $db->qn('gb.id') . ' = ' . $db->qn('ds.giveback_id') . ')');
		$query->join('LEFT', $db->qn('#__tj_city', 'city') . ' ON (' . $db->qn('city.id') . ' = ' . $db->qn('du.city') . ')');
		$query->join('LEFT', $db->qn('#__tj_region', 'region') . ' ON (' . $db->qn('region.id') . ' = ' . $db->qn('du.state') . ')');
		$query->join('LEFT', $db->qn('#__tj_country', 'country') . ' ON (' . $db->qn('country.id') . ' = ' . $db->qn('du.country') . ')');
		$query->where($db->qn('ds.campaign_id') . ' = ' . (int) $cid);
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->group($db->qn('du.email'));
		$query->group($db->qn('ds.annonymous_donation'));
		$query->order($db->qn('o.mdate') . ' DESC');

		// In the case of csv limit should not be applied
		if ($getCsvData == 0)
		{
			$query->setLimit($limit, $limit_start);

			$db->setQuery($query);
			$donors = $db->loadObjectList();
		}
		else
		{
			$db->setQuery($query);
			$donors = $db->loadAssocList();
		}

		foreach ($donors as $key => $donor)
		{
			$donorEmail = $donor->email;
			$query = $db->getQuery(true);

			$query->select('o.original_amount AS donation_amount');
			$query->from($db->qn('#__jg_orders', 'o'));
			$query->join('INNER', $db->qn('#__jg_donors', 'du') . ' ON (' . $db->qn('du.id') . ' = ' . $db->qn('o.donor_id') . ')');
			$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
			$query->where($db->qn('du.email') . ' = ' . $db->quote($donorEmail));
			$query->order($db->qn('o.cdate') . 'DESC');

			$db->setQuery($query);
			$results = $db->loadResult();

			$donors[$key]->recentDonation = $results;
		}

		// In the case of csv this data is not required. Added this condition to reduce overhead.
		if ($getCsvData == 0)
		{
			if (isset($donors))
			{
				foreach ($donors as $donor)
				{
					$helperPath = JPATH_SITE . '/components/com_jgive/helpers/integrations.php';

					if (!class_exists('JgiveIntegrationsHelper'))
					{
						JLoader::register('JgiveIntegrationsHelper', $helperPath);
						JLoader::load('JgiveIntegrationsHelper');
					}

					$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
					$donor->avatar      = $JgiveIntegrationsHelper->getUserAvatar($donor->user_id);
					$donor->profile_url = $JgiveIntegrationsHelper->getUserProfileUrl($donor->user_id);
				}
			}
		}

		return $donors;
	}

	/**
	 * Get Campaign Donors count
	 *
	 * @param   integer  $cid  Camp Id
	 *
	 * @return  Donors count
	 */
	public function getCampaignDonorsCount($cid)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(o.amount) as donors_count');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->where($db->qn('o.campaign_id') . ' = ' . (int) $cid);
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$db->setQuery($query);
		$donors_count = $db->loadResult();

		if ($donors_count == '')
		{
			$donors_count = 0;
		}

		return $donors_count;
	}

	/**
	 * Get Area-Wise Campaign Donors Count
	 *
	 * @param   INT  $cid  Campaign Id
	 *
	 * @return donors
	 */
	public function getCampaignDonorsCountAreaWise($cid)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(d.id) as count');
		$query->select('d.country');
		$query->from($db->qn('#__jg_donors', 'd'));
			$query->join('INNER', $db->qn('#__jg_orders', 'o') . 'ON (' . $db->qn('o.donor_id') . ' = ' . $db->qn('d.id') . ')');
		$query->where($db->qn('d.campaign_id') . ' = ' . (int) $cid);
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->group($db->qn('d.campaign_id'));
		$query->group($db->qn('d.country'));
		$db->setQuery($query);
		$donors_count = $db->loadObjectlist();

		return $donors_count;
	}

	/**
	 * Get Campaign Orders Count
	 *
	 * @param   INT  $cid  Campaign Id
	 *
	 * @return  order record count
	 */
	public function getCampaignOrdersCount($cid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*) as orders_count');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->where($db->qn('o.campaign_id') . ' = ' . (int) $cid);
		$query->where($db->qn('o.status') . ' = ' . $db->quote('C'));
		$query->group($db->qn('o.campaign_id'));

		$db->setQuery($query);

		return $orders_count = $db->loadResult();
	}

	/** Deprecated in 2.6.0
	 * Upload Image
	 *
	 * @param   integer  $camp_id     Campaign id
	 * @param   string   $file_field  File field name
	 * @param   integer  $img_id      Image rec id
	 * @param   integer  $index       Index
	 *
	 * @return  boolean|String
	 */
	public function uploadImage($camp_id, $file_field = 'camp_image', $img_id = 0, $index = 0)
	{
		$db = Factory::getDbo();

		/* save uploaded image
		check the file extension is ok */

		if ($file_field == 'jgive_img_gallery')
		{
			$file_name = $_FILES[$file_field]['name'][$index];
			$file_size = $_FILES[$file_field]['size'][$index];
		}
		else
		{
			$file_name = $_FILES[$file_field]['name'];
			$file_size = $_FILES[$file_field]['size'];
		}

		// Check for max media size allowed for upload
		$JgiveMediaHelper = new JgiveMediaHelper;
		$max_size_exceed = $JgiveMediaHelper->check_max_size($file_size);

		if ($max_size_exceed)
		{
			$max_size = $this->params->get('max_size');

			if (!$max_size)
			{
				// KB
				$max_size = 1024;
			}

			$errorList[] = Text::_('FILE_BIG') . " " . $max_size . "KB<br>";
			$this->app->enqueueMessage(Text::_('COM_JGIVE_MAX_FILE_SIZE') . ' ' . $max_size . 'KB<br>', 'error');

			return false;
		}

		$media_info            = pathinfo($file_name);

		$uploadedFileName      = $media_info['filename'];
		$uploadedFileExtension = $media_info['extension'];
		$validFileExts         = explode(',', 'jpeg,jpg,png,gif');

		// Assume the extension is false until we know its ok
		$extOk = false;

		/* go through every ok extension, if the ok extension matches the file extension (case insensitive)
		then the file extension is ok */
		foreach ($validFileExts as $key => $value)
		{
			if (preg_match("/$value/i", $uploadedFileExtension))
			{
				$extOk = true;
			}
		}

		if ($extOk == false)
		{
			echo Text::_('COM_JGIVE_INVALID_IMAGE_EXTENSION');

			return;
		}

		// The name of the file in PHP's temp directory that we are going to move to our folder
		if ($file_field == 'jgive_img_gallery')
		{
			$file_temp = $_FILES[$file_field]['tmp_name'][$index];
		}
		else
		{
			$file_temp = $_FILES[$file_field]['tmp_name'];
		}

		/* for security purposes, we will also do a getimagesize on the temp file (before we have moved it
		to the folder) to check the MIME type of the file, and whether it has a width and height */
		$image_info     = getimagesize($file_temp);

		/* we are going to define what file extensions/MIMEs are ok, and only let these ones in (whitelisting), rather than try to scan for bad
		types, where we might miss one (whitelisting is always better than blacklisting) */
		$okMIMETypes    = 'image/jpeg,image/pjpeg,image/png,image/x-png,image/gif';
		$validFileTypes = explode(",", $okMIMETypes);

		// If the temp file does not have a width or a height, or it has a non ok MIME, return
		if (!is_int($image_info[0]) || !is_int($image_info[1]) || !in_array($image_info['mime'], $validFileTypes))
		{
			echo Text::_('COM_JGIVE_INVALID_IMAGE_EXTENSION');

			return;
		}

		// Clean up filename to get rid of strange characters like spaces etc
		$file_name = File::makeSafe($uploadedFileName);

		// Lose any special characters in the filename
		$file_name = preg_replace("/[^A-Za-z0-9]/i", "-", $file_name);

		// Use lowercase
		$file_name = strtolower($file_name);

		// Add timestamp to file name
		$timestamp = time();
		$file_name = $file_name . '_' . $timestamp . '.' . $uploadedFileExtension;

		// Always use constants when making file paths, to avoid the possibilty of remote file inclusion
		$upload_path_folder       = JPATH_SITE . '/images/jGive';
		$image_upload_path_for_db = 'images/jGive';

		// If folder is not present create it
		if (!file_exists($upload_path_folder))
		{
			@mkdir($upload_path_folder);
		}

		$upload_path = $upload_path_folder . '/' . $file_name;
		$image_upload_path_for_db .= '/' . $file_name;

		if (!File::upload($file_temp, $upload_path))
		{
			echo Text::_('COM_JGIVE_ERROR_MOVING_FILE');

			return false;
		}
		else
		{
			$obj = new stdClass;

			if ($img_id)
			{
				$obj->id = $img_id;
			}
			else
			{
				$obj->id = '';
			}

			$obj->campaign_id = $camp_id;
			$obj->path        = $image_upload_path_for_db;

			if ($file_field == 'camp_image')
			{
				$obj->gallery = 0;
			}
			else
			{
				$obj->gallery = 1;
			}

			$obj->order = '';

			if ($obj->id)
			{
				if (!$db->updateObject('#__jg_campaigns_images', $obj, 'id'))
				{
					echo $db->stderr();

					return false;
				}

				return $obj->id;
			}
			elseif (!$db->insertObject('#__jg_campaigns_images', $obj, 'id'))
			{
				echo $db->stderr();

				return false;
			}

			return $db->insertid();
		}
	}

	/**
	 * Get All Category Options
	 *
	 * @return  Cat Option
	 */
	public function getAllCategoryOptions()
	{
		/* If not used anywhere then remove 2.1*/
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.category_id');
		$query->select('cat.title');
		$query->from($db->qn('#__jg_campaigns', 'c'));
		$query->join('INNER', $db->qn('#__categories', 'cat') . 'ON (' . $db->qn('cat.id') . ' = ' . $db->qn('c.category_id') . ')');
		$query->order($db->qn('cat.title'));
		$db->setQuery($query);
		$campaigns = $db->loadObjectList();

		return $campaigns;
	}

	/**
	 * Get Campaign Details
	 *
	 * @param   INT  $cid  Campaign ID
	 *
	 * @return  Object
	 */
	public function getCampaignDetails($cid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->qn('#__jg_campaigns'));
		$query->where($db->qn('id') . ' = ' . (int) $cid);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get campaign category filter
	 *
	 * @param   string  $firstOption  Filter options
	 *
	 * @return  Array
	 */
	public function getCampaignsCategories($firstOption = '')
	{
		$app  = Factory::getApplication();
		$lang = Factory::getLanguage();
		$tag  = $lang->gettag();

		$categories  = HTMLHelper::_('category.options', 'com_jgive', $config = array('filter.published' => array(1), 'filter.language' => array('*', $tag)));

		$cat_options = array();
		$cat_options[] = HTMLHelper::_('select.option', 0, Text::_('COM_JGIVE_ALL'));

		if (!empty($categories))
		{
			foreach ($categories as $category)
			{
				if (!empty($category))
				{
					$cat_options[] = HTMLHelper::_('select.option', $category->value, $category->text);
				}
			}
		}

		return $cat_options;
	}

	/**
	 * [getCampaignTypeFilterOptions description]
	 *
	 * @return [type] [description]
	 */
	public function getCampaignTypeFilterOptions()
	{
		$options = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_FILTER_SELECT_TYPE'));
		$options[] = HTMLHelper::_('select.option', 'donation', Text::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION'));
		$options[] = HTMLHelper::_('select.option', 'investment', Text::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT'));

		return $options;
	}

	/**
	 * Check the campaign marks as featured
	 *
	 * @param   INT  $contentId  ContentId
	 *
	 * @return boolean|Integer
	 */
	public function isFeatured($contentId)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('featured');
		$query->from($db->qn('#__jg_campaigns'));
		$query->where($db->qn('id') . ' = ' . (int) $contentId);
		$db->setquery($query);
		$result = $db->loadResult();

		return (empty($result)) ? 0 : $result;
	}

	/**
	 * Get Campaign type Donate/Invest
	 *
	 * @param   INT  $campid  Camp Id
	 *
	 * @return  Object
	 */
	public function getCampaignType($campid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('id', 'creator_id', 'type'));
		$query->from($db->qn('#__jg_campaigns'));
		$query->where($db->qn('id') . ' = ' . (int) $campid);
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Function to get main category name
	 *
	 * @param   INT  $catid  Camp Id
	 *
	 * @return  String
	 */
	public function getCatname($catid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title');
		$query->from($db->qn('#__categories', 'cat'));
		$query->where($db->qn('cat.id') . ' = ' . (int) $catid);
		$query->where($db->qn('extension') . ' = ' . $db->quote('com_jgive'));
		$db->setquery($query);

		return $result = $db->loadResult();
	}

	/**
	 * Function to add the organization/individual type field Individuals
	 *
	 * @return Array
	 */
	public function organization_individual_type()
	{
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SELECT_TYPE_ORG_INDIVIDUALS'));
		$options[] = HTMLHelper::_('select.option', 'non_profit', Text::_('COM_JGIVE_ORG_NON_PROFIT'));
		$options[] = HTMLHelper::_('select.option', 'self_help', Text::_('COM_JGIVE_SELF_HELP'));
		$options[] = HTMLHelper::_('select.option', 'individuals', Text::_('COM_JGIVE_SELF_INDIVIDUALS'));

		return $options;
	}

	/**
	 * Campaigns To ShowOptions
	 *
	 * @return  Array
	 */
	public function campaignsToShowOptions()
	{
		$options = array();
		$app     = Factory::getApplication();

		if ($app->isClient('site') OR JVERSION < 3.0)
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_CAM_TO_SHOW'));
		}

		$options[] = HTMLHelper::_('select.option', 'featured', Text::_('COM_JGIVE_FEATURED_CAMP'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('COM_JGIVE_SUCCESSFUL_CAMP'));
		$options[] = HTMLHelper::_('select.option', '0', Text::_('COM_JGIVE_FILTER_ONGOING'));

		return $options;
	}

	/**
	 * Delet File
	 *
	 * @param   Array  $imgarray  Image file array
	 * @param   INT    $isvideo   Flag
	 *
	 * @return  void
	 */
	public function deletFile($imgarray, $isvideo)
	{
		if (!empty($imgarray))
		{
			foreach ($imgarray as $img)
			{
				$this->deleteFile($img, $isvideo);
			}
		}
	}

	/**
	 * Delete File
	 *
	 * @param   string  $dfinepath  File path to delete
	 * @param   INT     $isvideo    For video flag
	 *
	 * @return void
	 */
	public function deleteFile($dfinepath, $isvideo = 0)
	{
		/* If to delete video file then we will need to specify video file location
		but in-case of image full location we are getting from db itself*/
		if ($isvideo == 1)
		{
			$dfinepath = 'media/com_jgive/videos/' . $dfinepath;
		}

		if (File::exists($dfinepath))
		{
			File::delete($dfinepath);
		}
	}

	/**
	 * Function to idetify passed field hidden or not from component config.
	 *
	 * @param   string  $field_name  Field Name
	 *
	 * @return  boolean
	 */
	public function filedToShowOrHide($field_name)
	{
		$params       = ComponentHelper::getParams('com_jgive');
		$creatorfield = array();
		$creatorfield = $params->get('creatorfield');
		$show_selected_fields = $params->get('show_selected_fields');

		if ($show_selected_fields AND (!empty($creatorfield)))
		{
			// If field is hidden & not to show on form
			if (in_array($field_name, $creatorfield))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Generate Campaign Sucess Status
	 *
	 * @param   INT  $cid  Campaign Id
	 *
	 * @return  Integer  Campaign Sucess Status
	 */
	public function generateCampaignSucessStatus($cid)
	{
		$cdata['campaign'] = new stdclass;

		// Get campaigns details.
		$cdata['campaign'] = $this->getCampaignDetails($cid);

		// Get campaign amounts.
		$amounts                             = $this->getCampaignAmounts($cid);
		$cdata['campaign']->amount_received  = $amounts['amount_received'];
		$cdata['campaign']->remaining_amount = $amounts['remaining_amount'];

		/* 0 - Ongoing.
		 1 - Successful.
		 -1 - Failed.
		*/

		$campaignSuccessStatus = 0;

		if ($cdata['campaign']->amount_received >= $cdata['campaign']->goal_amount)
		{
			/* Changed by deepa
			1 - Successful.*/
			$campaignSuccessStatus = 1;

			/*if (date('Y-m-d') > $cdata['campaign']->end_date)
			{
				$campaignSuccessStatus = 1;
			}
			elseif ($cdata['campaign']->allow_exceed)
			{
				$campaignSuccessStatus = 0;
			}*/
		}
		else
		{
			if (Factory::getDate()->Format('Y-m-d') > Factory::getDate($cdata['campaign']->end_date)->Format('Y-m-d'))
			{
				// -1 - Failed.
				$campaignSuccessStatus = -1;
			}
			else
			{
				// 0 - Ongoing.
				$campaignSuccessStatus = 0;
			}
		}

		return $campaignSuccessStatus;
	}

	/**
	 * Update CampaignSuccessStatus
	 *
	 * @param   Integer  $cid                    Cid
	 * @param   String   $campaignSuccessStatus  Stae
	 * @param   Integer  $orderId                Orderid
	 *
	 * @return boolean
	 */
	public function updateCampaignSuccessStatus($cid = 0, $campaignSuccessStatus = null, $orderId = 0)
	{
		$db = Factory::getDbo();

		// If cid not passed, get cid from orderid.
		if (!$cid && $orderId)
		{
			$orderDetails = Table::getInstance('Orders', 'JgiveTable');
			$orderDetails->load(array('id' => $orderId));
			$cid = $orderDetails->campaign_id;
		}

		// If cid not found, return.
		if (!$cid)
		{
			return false;
		}

		// If campaign success status is not passed.
		if ($campaignSuccessStatus === null)
		{
			// Get campaign success status.
			$campaignSuccessStatus = $this->generateCampaignSucessStatus((int) $cid);
		}

		// Update campaign success status.
		// Create an object.
		$object = new stdClass;

		// Must be a valid primary key value.
		$object->id             = $cid;
		$object->success_status = $campaignSuccessStatus;

		// Update record.
		$result = $db->updateObject('#__jg_campaigns', $object, 'id');

		if (!$result)
		{
			return false;
		}

		// Start - Plugin trigger onAfterJGCampaignSuccessStatusChange.
		PluginHelper::importPlugin('system');
		$result = Factory::getApplication()->triggerEvent('onAfterJGCampaignSuccessStatusChange', array($cid, $campaignSuccessStatus));

		return true;
	}

	/**
	 * Update Campaign Processed Flag
	 *
	 * @param   Integer  $cid                    Cid
	 * @param   String   $campaignProcessedFlag  Flag
	 * @param   Integer  $orderId                Order Id
	 *
	 * @return  Boolean
	 */
	public function updateCampaignProcessedFlag($cid = 0, $campaignProcessedFlag = null, $orderId = 0)
	{
		// If cid not passed, get cid from orderid.
		if (!$cid && $orderId)
		{
			$orderDetails = Table::getInstance('Orders', 'JgiveTable');
			$orderDetails->load(array('id' => $orderId));
			$cid = $orderDetails->campaign_id;
		}

		// If cid not found, return.
		if (!$cid)
		{
			return false;
		}

		// If campaign success status is not passed.
		if ($campaignProcessedFlag === null)
		{
			// Set default campaign success status.
			$campaignProcessedFlag = 'NA';
		}

		$db = Factory::getDbo();

		// Create an object.
		$object = new stdClass;

		// Must be a valid primary key value.
		$object->id             = $cid;
		$object->processed_flag = $campaignProcessedFlag;

		// Update record.
		$result = $db->updateObject('#__jg_campaigns', $object, 'id');

		if ($result)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Get cat alias
	 *
	 * @param   INT  $catid  Cat id
	 *
	 * @return  String
	 */
	public function getCatalias($catid)
	{
		if ($catid)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('alias');
			$query->from($db->qn('#__categories'));
			$query->where($db->qn('id') . ' = ' . (int) $catid);
			$query->where($db->qn('extension') . ' = ' . $db->quote('com_jgive'));
			$db->setQuery($query);

			return $db->loadResult();
		}
	}

	/**
	 * Get date difference in days
	 *
	 * @param   String  $date1  Date 1
	 * @param   Date    $date2  Date 2
	 *
	 * @return  Integer
	 */
	public function getDateDiffInDays($date1, $date2)
	{
		$datetime1 = new DateTime($date1 ? $date1 : '');
		$datetime2 = new DateTime($date2 ? $date2 : '');

		$interval = date_diff($datetime1, $datetime2);

		return $interval->days;
	}

	/**
	 * Get campaign Caculation like - main image path, days left, progress percentage
	 *
	 * @param   Object  $cdata                 Campaign info Object
	 * @param   INT     $singleCampaignItemid  Item Id
	 *
	 * @return  Object  Mapped Data
	 */
	public function mapData($cdata, $singleCampaignItemid)
	{
		// Get Campaign main image
		foreach ($cdata['images'] as $img)
		{
			$path      = 'images/jGive/';
			$fileParts = pathinfo($img->path);

			if ($img->gallery == 0)
			{
				// Get original image name to find it resize images (S,M,L)
				// If loop for old version compatibility (where img resize not available means no L , M ,S before image name)
				if (file_exists($path . $fileParts['basename']))
				{
					$cdata['campaign']->campaign_thumb = Uri::root() . $path . $fileParts['basename'];
					break;
				}
				else
				{
					$cdata['campaign']->campaign_thumb = Uri::root() . $path . 'L_' . $fileParts['basename'];
					break;
				}
			}
		}

		// Calculate days left
		$curr_date                    = Factory::getDate()->Format('Y-m-d');
		$time_curr_date               = strtotime($curr_date);
		$time_end_date                = strtotime($cdata['campaign']->end_date);
		$interval                     = ($time_end_date - $time_curr_date);
		$cdata['campaign']->days_left = floor($interval / (60 * 60 * 24));

		// Calculate Progress Percentage
		$goal_amount = (float) $cdata['campaign']->goal_amount;

		if (!empty($cdata['campaign']->amount_received) && $goal_amount > 0)
		{
			$cdata['campaign']->progress_per = number_format(($cdata['campaign']->amount_received / $cdata['campaign']->goal_amount) * 100, 2) . '%';
		}
		else
		{
			$cdata['campaign']->progress_per = '0.00%';
		}

		$camplink = 'index.php?option=com_jgive&view=campaign&layout=default&id=' . $cdata['campaign']->id . '&Itemid=' . $singleCampaignItemid;
		$cdata['campaign']->link = Uri::root() . substr(Route::_($camplink), strlen(Uri::base(true)) + 1);

		return $cdata;
	}

	/**
	 * Format campaign title for alias
	 *
	 * @param   STRING  $title      Campaign title
	 * @param   INT     $oldCid_id  Campaign id
	 *
	 * @return  Formated campaign title
	 */
	public function formatttedTitle($title, $oldCid_id = '')
	{
		$db          = Factory::getDbo();
		$user        = Factory::getUser();
		$i           = 1;
		$final_title = $title;

		do
		{
			if ($i == 1)
			{
				$status = $this->ckUniqueCampaignTitle($title, $oldCid_id);
			}
			else
			{
				$final_title = $title . $i;
				$status      = $this->ckUniqueCampaignTitle($final_title);
			}

			// Generate new vanity url
			$i++;
		}
		while ($status != 0);

		return $db->escape(trim($final_title), true);
	}

	/**
	 * Check Unique Campaign Title
	 *
	 * @param   String  $title      Campaign title
	 * @param   String  $oldCid_id  Campaign id
	 *
	 * @return  1:IF vanity already exist & 0:IF vanity is ot exist
	 */
	public function ckUniqueCampaignTitle($title, $oldCid_id = '')
	{
		$db      = Factory::getDbo();
		$where   = array();
		$title   = $db->quote($db->escape(trim($title), true));
		$where[] = "`alias`= $title ";

		if (!empty($oldCid_id))
		{
			$where[] = ' `id`!=\'' . $oldCid_id . '\' ';
		}

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$query = 'SELECT `id` FROM `#__jg_campaigns` ' . $where;
		$db->setQuery($query);
		$id = $db->loadResult();

		if (!empty($id))
		{
			// Present title URL
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * This function gives formatted vanity url
	 *
	 * @param   STRING  $vanityurl  Campaign alias
	 * @param   STRING  $title      Campaign title
	 * @param   INT     $oldCid_id  Campaign id
	 *
	 * @return  1:IF vanity already exist & 0:IF vanity is ot exist
	 */
	public function formatttedVanityURL($vanityurl, $title, $oldCid_id = '')
	{
		$user       = Factory::getUser();
		$title = trim($title);

		if (trim($vanityurl) == '')
		{
			$vanityurl = $title;
		}

		$final_vanity = $vanityurl;

		// Remove all space, tab, new line
		$final_vanity = JApplication::stringURLSafe($final_vanity);

		if (trim(str_replace('-', '', $final_vanity)) == '')
		{
			$final_vanity = $user->id . '-' . Factory::getDate()->format('Y-m-d-H-i-s');
		}

		$i = 1;

		do
		{
			if ($i == 1)
			{
				$status = $this->ckUniqueVanityURL($vanityurl, (int) $oldCid_id);
			}
			else
			{
				// Remove userid: from vanity url if exist AS WE R GOING TO APPEND NEXT
				$vanityurl    = preg_replace('/' . $user->id . '-' . '/', '', $vanityurl, 1);
				$final_vanity = $newvanity = $user->id . '-' . $vanityurl . $i;

				// Pattern, replacement, string, limit
				$status = $this->ckUniqueVanityURL($newvanity);
			}

			// Generate new vanity url
			$i++;
		}
		while ($status != 0);

		return $final_vanity;
	}

	/**
	 * Get date difference in days
	 *
	 * @param   String   $alias       Campaign alias
	 * @param   Integer  $oldcamp_id  Campaign id
	 *
	 * @return  Integer 1:IF alias already exist & 0:IF alias is ot exist
	 */
	public function ckUniqueVanityURL($alias, $oldcamp_id = '')
	{
		$db      = Factory::getDbo();
		$where   = array();
		$alias  = $db->quote($db->escape($alias, true));
		$where[] = "`alias`= $alias ";

		if (!empty($oldcamp_id))
		{
			$where[] = ' `id`!=\'' . $oldcamp_id . '\' ';
		}

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$query = 'SELECT `id` FROM `#__jg_campaigns` ' . $where;
		$db->setQuery($query);
		$id = $db->loadResult();

		if (!empty($id))
		{
			// Present alias URL
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Method for formatting time
	 *
	 * @param   array  $post  Array
	 *
	 * @return  Array
	 *
	 * @since   3.0
	 */
	public function getFormattedTime($post)
	{
		$params = ComponentHelper::getParams('com_jgive');
		$campaign_period_in_days = $params->get('campaign_period_in_days');

		// Get all non-jform data for campaign start time fields.
		$campaign_start_time_ampm = strtolower($post->get('campaign_start_time_ampm', '', 'string'));
		$campaign_start_time_hour = $post->get('campaign_start_time_hour', '', 'int');
		$campaign_start_time_min  = $post->get('campaign_start_time_min', '', 'int');
		$campaign_start_time      = $campaign_start_time_hour;

		// Convert hours into 24 hour format.
		if (($campaign_start_time_ampm == 'pm') && ($campaign_start_time_hour != '12'))
		{
			$campaign_start_time = $campaign_start_time_hour + 12;
		}
		elseif (($campaign_start_time_ampm == 'am') && ($campaign_start_time_hour == '12'))
		{
			$campaign_start_time = $campaign_start_time_hour - 12;
		}

		// Get minutes and attach seconds.
		$campaign_start_time .= ":" . $campaign_start_time_min;
		$campaign_start_time .= ":" . '00';

		if (!empty($campaign_period_in_days) || $campaign_period_in_days != 0)
		{
			// If days is set then set default value
			$campaign_end_time_ampm = 'pm';
			$campaign_end_time_hour = 11;
			$campaign_end_time_min  = 59;
			$campaign_end_time      = $campaign_end_time_hour;
		}
		else
		{
			// Get all non-jform data for campaign start time fields.
			$campaign_end_time_ampm = strtolower($post->get('campaign_end_time_ampm', '', 'string'));
			$campaign_end_time_hour = $post->get('campaign_end_time_hour', '', 'int');
			$campaign_end_time_min  = $post->get('campaign_end_time_min', '', 'int');
			$campaign_end_time      = $campaign_end_time_hour;
		}

		// Convert hours into 24 hour format.
		if (($campaign_end_time_ampm == 'pm') && ($campaign_end_time_hour != '12'))
		{
			$campaign_end_time = $campaign_end_time_hour + 12;
		}
		elseif (($campaign_end_time_ampm == 'am') && ($campaign_end_time_hour == '12'))
		{
			$campaign_end_time = $campaign_end_time_hour - 12;
		}

		// Get minutes and attach seconds.
		$campaign_end_time .= ":" . $campaign_end_time_min;
		$campaign_end_time .= ":" . '00';

		$formattedTime = array();

		// Set return values.
		$formattedTime['campaign_start_time'] = $campaign_start_time;
		$formattedTime['campaign_end_time']   = $campaign_end_time;

		return $formattedTime;
	}

/**
 * Get vendor ID for a given order ID.
 *
 * @param   int  $orderId  Order ID.
 *
 * @return  int  Vendor ID.
 *
 * @throws  RuntimeException  If not found.
 *
 * @since   3.0
 */

 public function getOrderVendorId($orderId)
{
    $db = Factory::getDbo();

    // Build the query with a JOIN to get the vendor_id 
    $query = $db->getQuery(true)
        ->select($db->quoteName('c.vendor_id'))
        ->from($db->quoteName('#__jg_orders', 'o'))
        ->join('INNER', $db->quoteName('#__jg_campaigns', 'c') . ' ON c.id = o.campaign_id')
        ->where($db->quoteName('o.id') . ' = ' . (int) $orderId);
    
    $db->setQuery($query);
    $vendorId = $db->loadResult();

    if (!$vendorId)
    {
        throw new RuntimeException(Text::sprintf('COM_JGIVE_VENDOR_NOT_FOUND', $orderId));
    }

    return $vendorId;
}
}