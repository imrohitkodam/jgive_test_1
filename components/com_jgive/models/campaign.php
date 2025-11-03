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

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

JLoader::import('filterFields', JPATH_SITE . '/components/com_tjfields');
JLoader::import('tjvendors', JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers');
JLoader::import('fronthelper', JPATH_SITE . '/components/com_tjvendors/helpers');
JLoader::import('integrations', JPATH_SITE . '/components/com_jgive/helpers');
JLoader::import('campaign', JPATH_SITE . '/components/com_jgive/helpers');
JLoader::import('campaignform', JPATH_SITE . '/components/com_jgive/models');
JLoader::import('donors', JPATH_SITE . '/components/com_jgive/models');
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

/**
 * jticketing Model
 *
 * @since  0.0.1
 */
class JGiveModelCampaign extends ItemModel
{
	use TjfieldsFilterField;

	/**
	 * Model Class  constructor.
	 *
	 * @since   2.1
	 */
	public function __construct()
	{
		$this->campaignHelper = new campaignHelper;
		parent::__construct();
	}

	/**
	 * Method to get campaign data.
	 *
	 * @param   integer  $pk  The id of the campaign.
	 *
	 * @return  object|boolean|JException  Menu item data object on success, boolean false or JException instance on error
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) Factory::getApplication()->input->get('id', 0, 'INTEGER');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			$app = Factory::getApplication();
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaignform');
			$jgiveModelCampaignFrom = BaseDatabaseModel::getInstance('campaignform', 'JGiveModel');
			$item                   = $jgiveModelCampaignFrom->getItem($pk);

			try
			{
				$cdata['campaign'] = (object) json_decode(json_encode($item), true);

				// Get default video path
				if ($cdata['campaign']->video_on_details_page)
				{
					$cdata['campaign']->videoPlgParams = $this->getDefaultVideo($cdata['campaign']->id);
				}

				$vendorModelObj = TJVendors::model('vendor');
				$vendorData = $vendorModelObj->getItem($item->vendor_id);
				$cdata['campaign']->vendor_title = $vendorData->vendor_title;
				$cdata['givebacks'] = $this->campaignHelper->getCampaignGivebacks($cdata['campaign']->id);

				// Get orders count
				$cdata['orders_count'] = $this->campaignHelper->getCampaignOrdersCount($cdata['campaign']->id);

				// Get campaign donors
				$params = ComponentHelper::getParams('com_jgive');

				$limit = 5;

				$cdata['donors'] = $this->campaignHelper->getCampaignDonors($cdata['campaign']->id, 0, $limit);
				$cdata['groupedOrdersCount'] = !empty($cdata['orders_count']) ? $cdata['orders_count'] : 0;

				// Get campaign donors count according to country
				$cdata['areawise_donors'] = $this->campaignHelper->getCampaignDonorsCountAreaWise($cdata['campaign']->id);

				$layout = $app->input->get('layout', 'default', 'STRING');

				// Getting total no. of donors per campaign
				$jgiveModelDonorsObj = BaseDatabaseModel::getInstance('Donors', 'JgiveModel');
				$cdata['campaign']->totalNoOfDonors = $cdata['campaign']->campaignDonorsCount = $jgiveModelDonorsObj->getDonorsPerCamp($cdata['campaign']->id);

				if ($layout == 'default')
				{
					// Get campaign amounts
					$amounts                             = $this->campaignHelper->getCampaignAmounts($cdata['campaign']->id);
					$cdata['campaign']->amount_received  = $amounts['amount_received'];
					$cdata['campaign']->remaining_amount = $amounts['remaining_amount'];

					// Get campaign promoter info
					require_once JPATH_SITE . "/components/com_jgive/helpers/integrations.php";
					$JgiveIntegrationsHelper                = new JgiveIntegrationsHelper;
					$cdata['campaign']->creator_avatar      = $JgiveIntegrationsHelper->getUserAvatar($cdata['campaign']->creator_id);
					$cdata['campaign']->creator_profile_url = $JgiveIntegrationsHelper->getUserProfileUrl($cdata['campaign']->creator_id);

					// Get campaign categories
					if (!empty($cdata['campaign']->category_id))
					{
						$cdata['campaign']->catname = $this->campaignHelper->getCatname($cdata['campaign']->category_id);
					}

					JLoader::import('components.com_jgive.models.campaigns', JPATH_SITE);
					$jgiveModelCampaignsObj = BaseDatabaseModel::getInstance('Campaigns', 'JgiveModel');

					$cdata['campaign']->donateBtnShowStatus = $jgiveModelCampaignsObj->getDonateButtonStatusFlag((array) $cdata['campaign']);
				}

				JLoader::import('components.com_jgive.models.campaigns', JPATH_SITE);
				$jgiveModelCampaignsObj = BaseDatabaseModel::getInstance('Campaigns', 'JgiveModel');
				$cdata['campaign']->donateBtnShowStatus = $jgiveModelCampaignsObj->getDonateButtonStatusFlag((array) $cdata['campaign']);

				$cdata['campaign']->goneDays   = $this->campaignHelper->getDateDiffInDays(date("Y-m-d"), $cdata['campaign']->start_date);
				$cdata['campaign']->days_limit = $this->campaignHelper->getDateDiffInDays($cdata['campaign']->start_date, $cdata['campaign']->end_date);

				return $cdata;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_('COM_JGIVE_NO_DATA_FOUND'), 'error');
			}
		}
	}

	/**
	 * Method to get Graph Data
	 *
	 * @param   Integer  $duration  This show the duration for graph data
	 * @param   Integer  $id        This show the camp id or user id
	 *
	 * @return  Json data
	 *
	 * @since  2.0
	 */
	public function getGraphData($duration, $id)
	{
		if ($duration == 0)
		{
			$graphDuration = 7;
		}
		elseif ($duration == 1)
		{
			$graphDuration = 30;
		}

		$todate = Factory::getDate(date('Y-m-d'))->Format(Text::_('Y-m-d'));

		$db      = Factory::getDbo();
		$user    = Factory::getUser();
		$query   = $db->getQuery(true);
		$dbCdate = $db->quoteName('o.cdate');

		if ($duration == 0 || $duration == 1)
		{
			$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $graphDuration . ' days'));

			$query->select('SUM(o.original_amount) AS donation_amount');
			$query->select('DATE(o.cdate) AS cdate');
			$query->select('COUNT(o.id) AS orders_count');
			$query->from($db->quoteName('#__jg_orders', 'o'));
			$query->join('INNER', $db->quoteName('#__jg_campaigns', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('o.campaign_id') . ')');
			$query->where($db->quoteName('c.id') . ' = ' . $db->quote($id) . ' AND ' . $db->quoteName('o.status') . ' = ' . $db->quote('C'));
			$query->where('DATE(' . $dbCdate . ')' . ' >= ' . $db->quote($backdate) . ' AND ' . 'DATE(' . $dbCdate . ')' . ' <= ' . $db->quote($todate));
			$query->group('DATE(' . $db->quoteName('o.cdate') . ')');
			$query->order($db->quoteName('o.cdate') . 'DESC');

			$db->setQuery($query);
			$results = $db->loadObjectList();
		}
		elseif ($duration == 2)
		{
			$curdate    = date('Y-m-d');
			$back_year  = date('Y') - 1;
			$back_month = date('m') + 1;
			$backdate   = $back_year . '-' . $back_month . '-' . '01';

			$query->select('SUM(o.amount) AS donation_amount');
			$query->select('MONTH(o.cdate) AS MONTHSNAME');
			$query->select('YEAR(o.cdate) AS YEARNAME');
			$query->select('COUNT(o.id) AS orders_count');
			$query->from($db->quoteName('#__jg_orders', 'o'));
			$query->join('INNER', $db->quoteName('#__jg_campaigns', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('o.campaign_id') . ')');
			$query->where($db->quoteName('c.id') . ' = ' . $db->quote($id) . ' AND ' . $db->quoteName('o.status') . ' = ' . $db->quote('C'));
			$query->where('DATE(' . $dbCdate . ')' . ' >= ' . $db->quote($backdate) . ' AND ' . 'DATE(' . $dbCdate . ')' . ' <= ' . $db->quote($todate));
			$query->group($db->quote('YEARNAME'));
			$query->group('MONTHSNAME');
			$query->order($db->quote('YEAR( o.cdate )') . 'DESC');
			$query->order($db->quote('MONTH( o.cdate )') . 'DESC');

			$db->setQuery($query);
			$results = $db->loadObjectList();
		}

		return $results;
	}

	/**
	 * Get Default Video
	 *
	 * @param   INT  $cid  Campaign Id
	 *
	 * @return  Default video path
	 */
	public function getDefaultVideo($cid)
	{
		$app = Factory::getApplication();

		// Load media helper
		$helperPath = JPATH_SITE . '/components/com_jgive/helpers/media.php';

		if (!class_exists('jgivemediaHelper'))
		{
			JLoader::register('jgivemediaHelper', $helperPath);
			JLoader::load('jgivemediaHelper');
		}

		if (!empty($cid))
		{
			try
			{
				$video_params = array();

				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'campaignform');
				$jgiveModelCampaignFrom = BaseDatabaseModel::getInstance('campaignform', 'JGiveModel');
				$CampaignData                   = $jgiveModelCampaignFrom->getItem();

				if (isset($CampaignData->gallery))
				{
					foreach ($CampaignData->gallery as $galleryData)
					{
						$defaultData = json_decode($galleryData->params);

						if (!empty ($defaultData) && $defaultData->default == 1)
						{
							$videoDetails = $galleryData;

							if (strpos($videoDetails->type, 'image') !== false)
							{
								$video_params['path']    = $videoDetails->path;
								$video_params['media']   = $videoDetails->media;
								$video_params['media_s'] = $videoDetails->media_s;
								$video_params['media_m'] = $videoDetails->media_m;
								$video_params['media_l'] = $videoDetails->media_l;
								$video_params['source']  = $videoDetails->source;
								$video_params['plugin']  = 'image';
								break;
							}
							else
							{
								if (!empty($videoDetails) && !empty($videoDetails->id))
								{
									$explodedVideoType = explode('.', $videoDetails->type);
									$videoDetails->type = $explodedVideoType[1];
									$video_params['type'] = $videoDetails->type;

									switch ($videoDetails->type)
									{
										// Video provider youtube
										case 'youtube':
										// Get youtube video ID from embed url
										$videoId = JgiveMediaHelper::videoId($videoDetails->type, $videoDetails->source);

										if (!empty($videoId))
										{
											$video_params['file']    = 'https://www.youtube.com/embed/' . $videoId;
											$video_params['videoId'] = $videoId;

											// Plugin to call to pay video
											$video_params['plugin'] = 'jwplayer';
										}
										break;

										// Video provider vimeo
										case 'vimeo':
										$videoId = JgiveMediaHelper::videoId($videoDetails->type, $videoDetails->source);

										if (!empty($videoId))
										{
											// Get youtube video ID from embed url
											$video_params['videoId'] = $videoId;
											$video_params['file'] = $videoDetails->path;

											// Plugin to call to pay video
											$video_params['plugin'] = 'vimeo';
										}
										break;

										// Other video provider than above
										default:
										$video_params['file'] = Uri::root() . $videoDetails->path . "/" . $videoDetails->source;

										// Plugin to call to pay video
										$video_params['plugin'] = 'jwplayer';
										break;
									}
								}
							}
						}
					}
				}
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				throw new Exception($e->getMessage());
			}

			return $video_params;
		}
	}

	/**
	 * Show More donors of the campaign
	 *
	 * @param   integer  $cid          Campaign id
	 * @param   integer  $limit_start  Limit start
	 *
	 * @return   Array  Donor data
	 *
	 * since 2.1
	 */
	public function showMoreDonors($cid, $limit_start)
	{
		$result = array();
		JLoader::import('JgiveFrontendHelper', JPATH_SITE . '/components/com_jgive/helpers');
		$jgiveFrontendHelper  = new jgiveFrontendHelper;
		$utilitiesObj = JGive::utilities();

		$params = ComponentHelper::getParams('com_jgive');
		$this->currency_code = $params->get('currency');
		$donors              = $this->campaignHelper->getCampaignDonors($cid, $limit_start - 1, 10);

		$html = "";

		$donors_html_view = $jgiveFrontendHelper->getViewpath('campaign', 'default_donorslist');

		foreach ($donors as $this->donor)
		{
			ob_start();
			include $donors_html_view;
			$html .= ob_get_contents();
			ob_end_clean();

			$limit_start ++;
		}

		$result = array();
		$result['jgive_index'] = $limit_start;
		$result['records']     = $html;

		return $result;
	}

	/**
	 * Function to get campaign's received amount
	 *
	 * @param   INTEGER  $cid  Campaign id
	 *
	 * @return  INTEGER|BOOLEAN  Total amount received by campaign, boolean false on error
	 *
	 * since 2.2
	 */
	public function getRaisedFunds($cid = 0)
	{
		$cid = (!empty($cid)) ? $cid : (int) $this->getState('campaignid');

		if (empty($cid))
		{
			return false;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('SUM(o.amount) AS amount_received');
		$query->from($db->qn('#__jg_orders', 'o'));
		$query->where($db->qn('o.campaign_id') . ' = ' . (int) $cid);
		$query->where($db->qn('o.status') . ' = ' . $db->q('C'));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get the extra fields information
	 *
	 * @param   array  $item_id  Id of the record
	 *
	 * @return	Boolean|Array  Extra field data
	 *
	 * @since	1.8.5
	 */
	public function getDataExtra($item_id = null)
	{
		if (empty($item_id))
		{
			$input = Factory::getApplication()->input;
			$item_id = $input->get('item_id', '', 'INT');
		}

		if (empty($item_id))
		{
			return false;
		}

		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;
		$data               = array();
		$data['client']     = 'com_jgive.campaign';
		$data['content_id'] = $item_id;

		$extra_fields_data = $tjFieldsHelper->FetchDatavalue($data);

		return $extra_fields_data;
	}
}
