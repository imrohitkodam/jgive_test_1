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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\AdminController;


/**
 * Controller class for reports
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       2.2.0
 */
class JgiveControllerReports extends AdminController
{
	/**
	 * Function to show the next set of reports rows when a user clicks on show more button
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   2.2.0
	 */
	public function showMoreReports()
	{
		$displayData   = array();
		$params        = ComponentHelper::getParams('com_jgive');
		$enableReports = $params->get('enable_reports');

		$user         = Factory::getUser();
		$loggedUserId = $user->id;
		$canUserEdit  = $user->authorise('core.edit', 'com_jgive');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jgive/models', 'Reports');
		$modelReports = BaseDatabaseModel::getInstance('Reports', 'JGiveModel', array('ignore_request' => true));
		$jinput       = Factory::getApplication()->input;
		$campaignId   = $jinput->get('campaign_id', 0, 'INT');
		$listStart    = $jinput->get('limit_offset', 0, 'INT');

		$modelReports->setState('filter.campaign_id', $campaignId);

		$modelCampaign       = BaseDatabaseModel::getInstance('Campaign', 'JGiveModel', array('ignore_request' => true));
		$displayCampaignData = $modelCampaign->getItem($campaignId);

		if ($listStart > 0)
		{
			$modelReports->setState('list.start', $listStart);
			$modelReports->setState('list.limit', 5);
		}

		$modelReports->setState('list.ordering', 'jgr.id');
		$modelReports->setState('list.direction', 'DESC');

		$displayData['data']  = $modelReports->getItems();
		$displayData['count'] = $modelReports->getTotal();

		if (!empty($displayData))
		{
			$displayData['extra_data']['campaign_creator_id'] = $displayCampaignData['campaign']->creator_id;
			$displayData['extra_data']['can_user_edit']       = $canUserEdit;
			$displayData['extra_data']['loggedin_user_id']    = $loggedUserId;
			$displayData['extra_data']['is_report_enabled']   = $enableReports;
			$displayData['extra_data']['get_date_format']     = $params->get('date_format', 'j  M  Y');
		}

		$layout = new FileLayout('append_reports', $basePath = JPATH_SITE . '/components/com_jgive/layouts/reports');
		$response['layout'] = $layout->render($displayData);
		$response['count']  = $displayData['count'];

		echo new JsonResponse($response);
		jexit();
	}
}
