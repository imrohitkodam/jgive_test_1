<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;

/**
 * JGive Campaign class
 *
 * @since  2.5.0
 */
class JGiveCampaign extends CMSObject
{
	/**
	 * The auto incremental primary key of the campaign
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $id = 0;

	/**
	 * Category id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $category_id = 0;

	/**
	 * Vendor id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $vendor_id = 0;

	/**
	 * Organization individual type
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $org_ind_type = '';

	/**
	 * Campaign creator id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $creator_id = 0;

	/**
	 * Campaign title
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $title = '';

	/**
	 * Campaign alias
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $alias = '';

	/**
	 * Campaign creation date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $created = '';

	/**
	 * Campaign modified date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $modified = '';

	/**
	 * Campaign type (donation/investment)
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $type = '';

	/**
	 * Campaign max donors
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $max_donors = 0;

	/**
	 * Campaign minimum donation/investment amount accept.
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $minimum_amount = 0;

	/**
	 * Campaign long description
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $long_description = '';

	/**
	 * Campaign goal amount
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	private $goal_amount = 0;

	/**
	 * Campaign promoter first name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $first_name = '';

	/**
	 * Campaign promoter last name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $last_name = '';

	/**
	 * Campaign promoter address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $address = '';

	/**
	 * Campaign promoter address2
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $address2 = '';

	/**
	 * Campaign promoter city id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $city = 0;

	/**
	 * Campaign promoter other city(if not found in city dropdown)
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $other_city = '';

	/**
	 * Campaign promoter state id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $state = 0;

	/**
	 * Campaign promoter country id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $country = 0;

	/**
	 * Campaign promoter zip
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $zip = '';

	/**
	 * Campaign promoter phone number
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $phone = '';

	/**
	 * Group Name
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $group_name = '';

	/**
	 * Website address
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $website_address = '';

	/**
	 * Campaign start date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $start_date = '';

	/**
	 * Campaign end date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $end_date = '';

	/**
	 * Allowed exceed donation flag
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $allow_exceed = 1;

	/**
	 * Allowed to view donations
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $allow_view_donations = 1;

	/**
	 * Campaign state published/unpublished
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $published = 0;

	/**
	 * Featured Campaign
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $featured = 0;

	/**
	 * Jomsocial Group id
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $js_groupid = 0;

	/**
	 * Campaign Status  0 - Ongoing, 1 - Successful, -1 - Failed
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $success_status = 0;

	/**
	 * Default video for campaign
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $video_on_details_page = 0;

	/**
	 * Campaign meta data
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $meta_data = '';

	/**
	 * Campaign meta description
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	private $meta_desc = '';

	/**
	 * Campaign ordering
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	private $ordering = 0;

	/**
	 * Campaign total doantion amount
	 *
	 * @var    float
	 * @since  2.5.0
	 */
	public $totalAmount = 0.0;

	/**
	 * holds the already loaded instances of the campaign
	 *
	 * @var    array
	 * @since   2.5.0
	 */
	private static $campaignObj = array();

	/**
	 * Constructor activating the default information of the campaign
	 *
	 * @param   int  $id  The unique campaign key to load.
	 *
	 * @since   2.5.0
	 */
	public function __construct($id = 0)
	{
		if (!empty($id))
		{
			$this->load($id);
		}
	}

	/**
	 * Returns the global campaign object
	 *
	 * @param   integer  $id  The primary key of the campaign to load (optional).
	 *
	 * @return  JGiveCampaign  The campaign object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JGiveCampaign;
		}

		// Check if the campaign id is already cached.
		if (empty(self::$campaignObj[$id]))
		{
			self::$campaignObj[$id] = new JGiveCampaign($id);
		}

		return self::$campaignObj[$id];
	}

	/**
	 * Method to load a campaign properties
	 *
	 * @param   int  $id  The campaign id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JGive::table("campaign");

		if ($table->load($id))
		{
			$this->id                    = (int) $table->get('id');
			$this->category_id           = (int) $table->get('category_id');
			$this->vendor_id             = (int) $table->get('vendor_id');
			$this->org_ind_type          = $table->get('org_ind_type');
			$this->creator_id            = (int) $table->get('creator_id');
			$this->title                 = $table->get('title');
			$this->alias                 = $table->get('alias');
			$this->created               = $table->get('created');
			$this->modified              = $table->get('modified');
			$this->type                  = $table->get('type');
			$this->max_donors            = (int) $table->get('max_donors');
			$this->minimum_amount        = (float) $table->get('minimum_amount');
			$this->long_description      = $table->get('long_description');
			$this->goal_amount           = (float) $table->get('goal_amount');
			$this->first_name            = $table->get('first_name');
			$this->last_name             = $table->get('last_name');
			$this->address               = $table->get('address');
			$this->address2              = $table->get('address2');
			$this->city                  = (int) $table->get('city');
			$this->other_city            = $table->get('other_city');
			$this->state                 = (int) $table->get('state');
			$this->country               = (int) $table->get('country');
			$this->zip                   = $table->get('zip');
			$this->phone                 = $table->get('phone');
			$this->group_name            = $table->get('group_name');
			$this->website_address       = $table->get('website_address');
			$this->start_date            = $table->get('start_date');
			$this->end_date              = $table->get('end_date');
			$this->allow_exceed          = $table->get('allow_exceed');
			$this->allow_view_donations  = $table->get('allow_view_donations');
			$this->published             = $table->get('published');
			$this->featured              = $table->get('featured');
			$this->js_groupid            = (int) $table->get('js_groupid');
			$this->success_status        = $table->get('success_status');
			$this->video_on_details_page = $table->get('video_on_details_page');
			$this->meta_data             = $table->get('meta_data');
			$this->meta_desc             = $table->get('meta_desc');
			$this->ordering              = (int) $table->get('ordering');

			return true;
		}

		return false;
	}

	/**
	 *Overridden: Returns an associative array of object properties.
	 *
	 * @param   boolean  $public  If true, returns only the public properties.
	 *
	 * @return  array
	 *
	 * @since   2.5.0
	 *
	 * @see     CMSObject::get()
	 */
	public function getProperties($public = true)
	{
		$vars = get_object_vars($this);

		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if ('_' == substr($key, 0, 1))
				{
					unset($vars[$key]);
				}
			}
		}

		return $vars;
	}

	/**
	 * Method to get category
	 *
	 * @return  object
	 *
	 * @since   2.5.0
	 */
	public function getCategory()
	{
		if (JVERSION < '4.0.0')
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
			$categoryTable = Table::getInstance('Category', 'CategoriesTable');
		}
		else
		{
			$categoryTable = Table::getInstance('CategoryTable', '\\Joomla\\Component\\Categories\\Administrator\\Table\\');
		}

		$categoryTable->load(array('id' => $this->category_id));

		$category = new stdClass;
		$category->id = $categoryTable->id;
		$category->title = $categoryTable->title;
		$category->description = $categoryTable->description;

		return $category;
	}

	/**
	 * Method to get total confirmed donation count
	 *
	 * @return  object
	 *
	 * @since   2.5.0
	 */
	public function getDonationsCount()
	{
		$donationsModel = JGive::model('Donations', array('ignore_request' => true));
		$donationsModel->setState('filter.campaign_id', $this->id);
		$donationsModel->setState('filter.payment_status', 'C');

		return $donationsModel->getTotal();
	}

	/**
	 * Method to get total donors count
	 *
	 * @return  int  total donors count
	 *
	 * @since   2.5.0
	 */
	public function getDonorsCount()
	{
		$donorsModel = JGive::model('Donors', array('ignore_request' => true));
		$donorsModel->setState('filter.campaign_id', $this->id);
		$donorsModel->setState('filter.payment_status', 'C');

		return $donorsModel->getTotal();
	}

	/**
	 * Method to get total donation amount
	 *
	 * @param   boolean  $formattedText  If true, returns formatted price.
	 *
	 * @return  integer  return total amount
	 *
	 * @since  2.5.0
	 */
	public function getTotalAmount($formattedText = false)
	{
		$orderModel = JGive::model('orders', array('ignore_request' => true));
		$orderModel->setState('filter.campaign_id', $this->id);
		$orderModel->setState('filter.status', 'C');
		$totalAmount = 0.0;
		$totalAmount = $orderModel->getDonationAmountSum();

		return $formattedText ? JGive::utilities()->getFormattedPrice($totalAmount) :$totalAmount;
	}

	/**
	 * Method to get total amount in percentage
	 *
	 * @return  integer  return total amount percentage
	 *
	 * @since  2.5.0
	 */
	public function getTotalAmountInPercentage()
	{
		$percentage = ($this->getTotalAmount() / $this->goal_amount) * 100;

		return $percentage;
	}

	/*
	public function getDonorsCount()
	{
	}

	public function getTotalAmountPercenatge()
	{
	}
	*/

	/*
	public function getGivebacks()
	{

	}

	public function getActivities()
	{

	}

	public function getMedia()
	{

	}

	public function getReports()
	{
	}
	*/

	/**
	 * Method to get the Campaign id
	 *
	 * @return  integer  return the id.
	 *
	 * @since  2.5.0
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Method to get vendor id
	 *
	 * @return  integer  return the id.
	 *
	 * @since  2.5.0
	 */
	public function getVendorId()
	{
		return $this->vendor_id;
	}

	/**
	 * Method to get creator id
	 *
	 * @return  integer  return the id.
	 *
	 * @since  2.5.0
	 */
	public function getCreatorId()
	{
		return $this->creator_id;
	}

	/**
	 * Method to get campaign title
	 *
	 * @return  string  return title.
	 *
	 * @since  2.5.0
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Method to get alias
	 *
	 * @return  string  return alias.
	 *
	 * @since  2.5.0
	 */
	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * Method to get created date
	 *
	 * @param   boolean  $formattedDate  If true, returns formatted price.
	 *
	 * @return  string   return alias.
	 *
	 * @since  2.5.0
	 */
	public function getCreatedDate($formattedDate = false)
	{
		return $formattedDate ? JGive::utilities()->getFormattedDate($this->created) :$this->created;
	}

	/**
	 * Method to get modified date
	 *
	 * @return  string  return alias.
	 *
	 * @since  2.5.0
	 */
	public function getModifiedDate()
	{
		return $this->modified;
	}

	/**
	 * Method to get campaign type
	 *
	 * @return  string  return alias.
	 *
	 * @since  2.5.0
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Method to get max donors allowed
	 *
	 * @return  integer  return max donor
	 *
	 * @since  2.5.0
	 */
	public function getMaxDonorsAllowed()
	{
		return $this->max_donors;
	}

	/**
	 * Method to get minimum amount
	 *
	 * @param   boolean  $formattedText  If true, returns formatted price.
	 *
	 * @return  integer  return minimum amount
	 *
	 * @since  2.5.0
	 */
	public function getMinimumAmount($formattedText = false)
	{
		return $formattedText ? JGive::utilities()->getFormattedPrice($this->minimum_amount) :$this->minimum_amount;
	}

	/**
	 * Method to get description
	 *
	 * @return  string  return long description
	 *
	 * @since  2.5.0
	 */
	public function getDescription()
	{
		return $this->long_description;
	}

	/**
	 * Method to get goal amount
	 *
	 * @param   boolean  $formattedText  If true, returns formatted price.
	 *
	 * @return  integer  return goal amount
	 *
	 * @since  2.5.0
	 */
	public function getGoalAmount($formattedText = false)
	{
		return $formattedText ? JGive::utilities()->getFormattedPrice($this->goal_amount) :$this->goal_amount;
	}

	/**
	 * Method to get start date
	 *
	 * @param   boolean  $formattedDate  If true, returns formatted Date.
	 *
	 * @return  string  return formatted start date
	 *
	 * @since  2.5.0
	 */
	public function getStartDate($formattedDate = false)
	{
		return $formattedDate ? JGive::utilities()->getFormattedDate($this->start_date) :$this->start_date;
	}

	/**
	 * Method to get end date
	 *
	 * @param   boolean  $formattedDate  If true, returns formatted Date.
	 *
	 * @return  string  return formatted end date
	 *
	 * @since  2.5.0
	 */
	public function getEndDate($formattedDate = false)
	{
		return $formattedDate ? JGive::utilities()->getFormattedDate($this->end_date) :$this->end_date;
	}

	/**
	 * Method to check allow exceed
	 *
	 * @return  interger  return allow exceed
	 *
	 * @since  2.5.0
	 */
	public function isAllowToExceed()
	{
		return $this->allow_exceed;
	}

	/**
	 * Method to check allow view donations
	 *
	 * @return  interger  return allow view donations
	 *
	 * @since  2.5.0
	 */
	public function isAllowToViewDonations()
	{
		return $this->allow_view_donations;
	}

	/**
	 * Method to get campaign status
	 *
	 * @return  interger  return allow view donations
	 *
	 * @since  2.5.0
	 */
	public function getStatus()
	{
		return $this->published;
	}

	/**
	 * Method to check the campaign is featured
	 *
	 * @return  interger  return featured
	 *
	 * @since  2.5.0
	 */
	public function isFeatured()
	{
		return $this->featured;
	}

	/**
	 * Method to get success status
	 *
	 * @return  interger  return success status
	 *
	 * @since  2.5.0
	 */
	public function getSuccessStatus()
	{
		$statusArray = array("0" => "Ongoing", "1" => "Successful", "-1" => "Failed");

		return $statusArray[$this->success_status];
	}

	/**
	 * Method to get meta data
	 *
	 * @return  string  return meta data
	 *
	 * @since  2.5.0
	 */
	public function getMetaData()
	{
		return $this->meta_data;
	}

	/**
	 * Method to get meta description
	 *
	 * @return  string  return meta description
	 *
	 * @since  2.5.0
	 */
	public function getMetaDesc()
	{
		return $this->meta_desc;
	}

	/**
	 * Method to get Organization/Individual type
	 *
	 * @return  string  return Organization/Individual type
	 *
	 * @since  2.5.0
	 */
	public function getOrgIndType()
	{
		return $this->org_ind_type;
	}

	/**
	 * Method to get cover image
	 *
	 * @param   string  $size  The image size
	 *
	 * @return  string  image   campaign cover image path
	 *
	 * @since  2.5.0
	 */
	public function getCoverImage($size = 'media_m')
	{
		$modelMediaXref    = JGive::model('MediaXref');
		$campaignMainImage = $modelMediaXref->getMedia($this->id, 'com_jgive.campaign', 0);

		if (!empty($campaignMainImage))
		{
			$com_params  = JGive::config();
			$storagePath = $com_params->get('jgive_media_upload_path', 'media/com_jgive/campaigns');

			JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
			JLoader::import("/techjoomla/media/tables/files", JPATH_LIBRARIES);
			$filetable = Table::getInstance('Files', 'TJMediaTable');
			$filetable->load($campaignMainImage[0]->media_id);
			$mediaType   = explode(".", $filetable->type);
			$imgPath     = $storagePath . '/' . $mediaType[0] . 's';
			$mediaConfig = array('id' => $campaignMainImage[0]->media_id, 'uploadPath' => $imgPath);
			$image = TJMediaStorageLocal::getInstance($mediaConfig);

			return $image->$size;
		}
	}

	/**
	 * Method to get remaining days to end campaign
	 *
	 * @return  int days
	 *
	 * @since  2.5.0
	 */
	public function getRemainingDays()
	{
		$currentDate = Factory::getDate()->Format('Y-m-d');
		$endDate  = Factory::getDate($this->end_date)->Format('Y-m-d');

		$timeCurrentDate  = strtotime($currentDate);
		$timeEndDate   = strtotime($endDate);
		$interval        = ($timeEndDate - $timeCurrentDate);

		$days_left = floor($interval / (60 * 60 * 24));

		if ((int) $days_left == 0)
		{
			// Only one day left
			$days_left = 1;
		}
		elseif ((int) $days_left < 0)
		{
			$days_left = "NA";
		}

		return $days_left;
	}

	/**
	 * Method to get days to start campaign
	 *
	 * @return  int days
	 *
	 * @since  2.5.0
	 */
	public function getDaysToStart()
	{
		$currentDate = Factory::getDate()->Format('Y-m-d');
		$startDate  = Factory::getDate($this->start_date)->Format('Y-m-d');

		$timeCurrentDate  = strtotime($currentDate);
		$timeStartDate   = strtotime($startDate);
		$interval        = ($timeStartDate - $timeCurrentDate);

		$days_start = floor($interval / (60 * 60 * 24));

		if ((int) $days_start < 0)
		{
			$days_start = "NA";
		}

		return $days_start;
	}
}
