<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jgive/models/');

require_once JPATH_SITE . '/components/com_jgive/includes/jgive.php';

$versionClass  = JGive::jgversion();
$version = $versionClass->getMediaVersion();
$options = array("version" => $version);

$lang = Factory::getLanguage();
$lang->load('plg_community_jgive', JPATH_ADMINISTRATOR);

// Load Campaigns helper
$helperPath = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

if (!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath);
	JLoader::load('campaignHelper');
}

/**
 * Campaign plgCommunityjgive class.
 *
 * @package  JGive
 * @since    1.8
 */
class PlgCommunityjgive extends CApplications
{
	/**
	 * Function onProfileDisplay
	 *
	 * @return  String
	 *
	 * @since   1.8
	 */
	public function onProfileDisplay()
	{
		// Payee user
		$user = CFactory::getRequestUser();

		return $this->_getHTML($user->id);
	}

	/**
	 * Function _getHTML
	 *
	 * @param   string  $user_id  User information
	 *
	 * @return  String  html
	 *
	 * @since   1.8
	 */
	public function _getHTML($user_id)
	{
		// Create instance of model class
		$campaignsModel = BaseDatabaseModel::getInstance('campaigns', 'JgiveModel');
		$campaignsModel->setState('displayPins', 1);
		$campaignsModel->setState('filter.creator_id', $user_id);

		$totalCampaigns = count($campaignsModel->getItems());

		$limit = $this->params->get('count');
		$campaignsModel->setState('list.limit', $limit);
		$data = $campaignsModel->getItems();
		$result = '';

		if ($data)
		{
			$params = ComponentHelper::getParams('com_jgive');

			// Load CSS resources.
			$load_bootstrap = (int) $params->get('load_bootstrap');

			if (!empty($load_bootstrap))
			{
				HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.min.css', $options);
			}

			// Load component css
			HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive.css', $options);
			HTMLHelper::_('stylesheet', 'media/com_jgive/css/artificiers.min.css', $options);
			HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive_bs3.css', $options);

			JLoader::import('tjstrapper', JPATH_SITE . '/media/techjoomla_strapper/');
			TjStrapper::loadTjAssets('com_jgive');
			$html = '';
			?>
			<style type="text/css">
				@media only screen and (min-width: 481px){
				.jgive_js_plg_pin_item { width: <?php echo $this->params->get('pin_width', '400') . 'px'; ?> !important;}
				}
			</style>
			<?php
			$html .= '<div class="tjBs3" id="jgiveWrapper">';

				foreach ($data as $displayData)
				{
					$html .= '<div class="col-sm-3 col-xs-12 jgive_js_plg_pin_item af-mb-20">';
					$layout = new FileLayout('pin', $basePath = JPATH_SITE . '/components/com_jgive/layouts/campaigns');
					$html .= $layout->render($displayData);
					$html .= '</div>';
				}

				// Show link if all campaigns are not shown
				if ($totalCampaigns && $totalCampaigns > $this->params->get('count'))
				{
					$viewmorelink = Uri::root() . substr(
						Route::_(
									'index.php?option=com_jgive&view=campaigns&layout=all&user_filter=' . $user_id . '&Itemid=' .
									$data['0']['otherData']['allCampaignsItemid']
								), strlen(Uri::base(true)) + 1
							);

						$html .= '<div class="col-sm-12"><a href="' . $viewmorelink . '">' .
						Text::_('PLG_JGIVE_VIEW_ALL_CAMPAIGNS_FROM_USER') . '(' . $totalCampaigns . ')' . '</a>
						</div>';
				}

			$html .= '</div>';

			$result = $html;
		}

		return $result;
	}
}
