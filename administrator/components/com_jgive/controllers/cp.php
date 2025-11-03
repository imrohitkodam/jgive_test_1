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
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

JLoader::register('TjControllerHouseKeeping', JPATH_SITE . "/libraries/techjoomla/controller/houseKeeping.php");

/**
 * Dashboard form controller class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveControllerCp extends FormController
{
	use TjControllerHouseKeeping;

	/**
	 * Method for Set session for graph
	 *
	 * @return  Json Statsforpie chart data
	 *
	 * @since   1.8
	 */
	public function SetsessionForGraph()
	{
		$input = Factory::getApplication()->input;
		$fromDate = $input->get('fromDate');
		$toDate = $input->get('toDate');
		$session = Factory::getSession();
		$session->set('jgive_graph_from_date', $fromDate);
		$session->set('jgive_socialads_end_date', $toDate);

		$model = $this->getModel('cp');

		$statsforpie = $model->statsforpie();

		$periodicDonationsCount = $model->getPeriodicDonationsCount();
		$session->set('statsforpie', $statsforpie);

		$session->set('periodicDonationsCount', $periodicDonationsCount);

		header('Content-type: application/json');
		echo json_encode(array("statsforpie" => $statsforpie));
		jexit();
	}

	/**
	 * Method makechart
	 *
	 * @return  Json data
	 *
	 * @since   1.8
	 */
	public function makechart()
	{
		$session = Factory::getSession();
		$session->get('jgive_graph_from_date', '');
		$session->get('jgive_socialads_end_date', '');
		$statsforpie = $session->get('statsforpie', '');
		$model = $this->getModel('cp');
		$statsforpie = $model->statsforpie();
		$periodicDonationsCount = $session->get('periodicDonationsCount');

		if ($periodicDonationsCount == null)
		{
			$periodicDonationsCount = "0.00";
		}

		$emptylinechart = 0;
		$session->get('jgive_graph_from_date', '');
		$session->get('jgive_socialads_end_date', '');
		$pending_donations = $confirmed_donations = 0;
		$denied_donations = $refunded_donations = $canceled_donations = 0;

		if (empty($statsforpie[0][0]) && empty($statsforpie[1][0]) && empty($statsforpie[2][0])
			&& empty($statsforpie[3][0]) && empty($statsforpie[4][0]))
		{
				$emptylinechart = 1;
		}
		else
		{
			if (!empty($statsforpie[0]))
			{
				$pending_donations = $statsforpie[0][0]->donations;
			}

			if (!empty($statsforpie[1]))
			{
				$confirmed_donations = $statsforpie[1][0]->donations;
			}

			if (!empty($statsforpie[2]))
			{
				$canceled_donations = $statsforpie[2][0]->donations;
			}

			if (!empty($statsforpie[3]))
			{
				$denied_donations = $statsforpie[3][0]->donations;
			}

			if (!empty($statsforpie[4]))
			{
				$refunded_donations = $statsforpie[4][0]->donations;
			}
		}

		header('Content-type: application/json');
		echo json_encode(
		array (
			"pending_donations" => $pending_donations,
			"confirmed_donations" => $confirmed_donations,
			"denied_donations" => $denied_donations,
			"refunded_donations" => $refunded_donations,
			"canceled_donations" => $canceled_donations,
			"periodicDonationsCount" => $periodicDonationsCount,
			"emptylinechart" => $emptylinechart
		)
		);
		jexit();
	}

	/**
	 * Manual Setup related chages: For now - 1. for overring the bs-2 view
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function setup()
	{
		$jinput = Factory::getApplication()->input;
		$takeBackUp = $jinput->get("takeBackUp", 1);

		$client = 0;
		$defTemplate = JgiveFrontendHelper::getSiteDefaultTemplate($client);

		$templatePath = JPATH_SITE . '/templates/' . $defTemplate . '/html/';

		$statusMsg = array();
		$statusMsg["component"] = array();

		// 1. Override component view
		$siteBs2views = JPATH_ROOT . "/components/com_jgive/views_bs2/site";

		// Check for com_jgive folder in template override location
		$compOverrideFolder  = $templatePath . "com_jgive";

		if (Folder::exists($compOverrideFolder))
		{
			if ($takeBackUp)
			{
				// Rename
				$backupPath = $compOverrideFolder . '_' . date("Ymd_H_i_s");
				Folder::move($compOverrideFolder, $backupPath);
				$statusMsg["component"][] = Text::_('COM_JGIVE_TAKEN_BACKUP_OF_OVERRIDE_FOLDER') . $backupPath;
			}
			else
			{
				Folder::delete($compOverrideFolder);
			}
		}

		// Copy
		Folder::copy($siteBs2views, $compOverrideFolder);
		$statusMsg["component"][] = Text::_('COM_JGIVE_OVERRIDE_DONE') . $compOverrideFolder;

		// 2. Modules override
		$modules = Folder::folders(JPATH_ROOT . "/components/com_jgive/views_bs2/modules/");
		$statusMsg["modules"] = array();

		foreach ($modules as $modName)
		{
			$this->overrideModule($templatePath, $modName, $statusMsg, $takeBackUp);
		}

		$this->displaySetup($statusMsg);
		jexit();
	}

	/**
	 * Override the Modules
	 *
	 * @param   String  $templatePath  TemplatePath eg JPATH_SITE . '/templates/protostar/html/'
	 * @param   String  $modName       Module name
	 * @param   Array   &$statusMsg    The array of config values.
	 * @param   int     $takeBackUp    Take the backup
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function overrideModule($templatePath, $modName, &$statusMsg, $takeBackUp)
	{

		$bs2ModulePath = JPATH_ROOT . "/components/com_jgive/views_bs2/modules/" . $modName;
		$overrideBs2ModulePath = $templatePath . $modName;

		$statusMsg["modules"][] = Text::sprintf('COM_JGIVE_OVERRIDING_THE_MODULE', $modName);

		if (Folder::exists($overrideBs2ModulePath))
		{
			if ($takeBackUp)
			{
				// Rename
				$backupPath = $overrideBs2ModulePath . '_' . date("Ymd_H_i_s");
				Folder::move($overrideBs2ModulePath, $backupPath);
				$statusMsg["modules"][] = Text::sprintf('COM_JGIVE_TAKEN_OF_MODULE_ND_BACKUP_PATH',  $modName, $backupPath);
			}
			else
			{
				Folder::delete($overrideBs2ModulePath);
			}
		}

		// Copy
		Folder::copy($bs2ModulePath, $overrideBs2ModulePath);
		$statusMsg["modules"][] = Text::sprintf('COM_JGIVE_COMPLETED_MODULE_OVERRIDE', "<b>" . $modName . "</b>");
	}

	/**
	 * Override the Modules
	 *
	 * @param   array  $statusMsg  The array of config values.
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function displaySetup($statusMsg)
	{
		echo "<br/> =================================================================================";
		echo "<br/> " . Text::_("COM_JGIVE_BS2_OVERRIDE_PROCESS_START");
		echo "<br/> =================================================================================";

		foreach ($statusMsg as $key => $extStatus)
		{
			echo "<br/> <br/><br/>*****************  " . JText::_("COM_JGIVE_BS2_OVERRIDING_FOR") . " <strong>" . $key . "</strong> ****************<br/>";

			foreach ($extStatus as $k => $status)
			{
				$index = $k ++;
				echo $index . ") " . $status . "<br/> ";
			}
		}

		echo "<br/> " . Text::_("COM_JGIVE_BS2_OVERRIDING_DONE");
	}

	/**
	 * Update campaign statuses
	 *
	 * @return   void
	 *
	 * @since  1.7
	 */
	public function updateAllCampaignsSuccessStatus()
	{
		$db   = Factory::getDbo();
		$query = "SELECT id
		FROM #__jg_campaigns";
		$db->setQuery($query);
		$campaigns = $db->loadColumn();

		$helperPath = JPATH_SITE . '/components/com_jgive/helpers/campaign.php';

		if (!class_exists('campaignHelper'))
		{
			JLoader::register('campaignHelper', $helperPath);
			JLoader::load('campaignHelper');
		}

		$campaignHelper = new campaignHelper;

		foreach ($campaigns as $cid)
		{
			echo "<br/>" . Text::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_3') . $cid;
			$campaignHelper->updateCampaignSuccessStatus($cid, $campaignSuccessStatus = null, $orderId = 0);
		}

		echo "<br/>" . Text::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_4');
	}
}
