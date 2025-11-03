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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\AdminController;

JLoader::import("/techjoomla/media/storage/local", JPATH_LIBRARIES);
JLoader::import("/techjoomla/media/xref", JPATH_LIBRARIES);

/**
 * Controller class for reports
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       2.2.0
 */
class JgiveControllerReport extends AdminController
{
	/**
	 * Method to get the record form.
	 *
	 * @return  void
	 *
	 * @since   2.2.0
	 */
	public function view()
	{
		$input = Factory::getApplication()->input;
		$reportId   = $input->get('id', '0', 'INT');

		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jgive/tables');
		$reportTable = Table::getInstance('report', 'JgiveTable');
		$reportTable->load(array('id' => $reportId));

		$jgiveFrontendHelper = new JgiveFrontendHelper;
		$campaignItemId      = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default&id=' . $reportTable->campaign_id);

		$redirect = Route::_('index.php?option=com_jgive&view=report&id=' . $reportId . '&Itemid=' . $campaignItemId, false);
		$this->setRedirect($redirect);
	}

	/**
	 * Downloads the file requested by user
	 *
	 * @return  boolean|void
	 *
	 * @since  2.2.0
	 */
	public function downloadAttachment()
	{
		// CSRF token check
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));
		$app      = Factory::getApplication();
		$clientId = $app->input->get('reportId', '', 'INT');
		$mediaId  = $app->input->get('id', '', 'INT');

		$params = ComponentHelper::getParams('com_jgive');

		if (!$mediaId && !$clientId)
		{
			return false;
		}

		$config              = array();
		$config['mediaId']   = $mediaId;

		// Assign client id as Campaign Id or Report Id or Giveback Id
		$config['client_id'] = $clientId;
		$config['client']    = 'com_jgive.reportAttachment';
		$mediaAttachmentData = TJMediaXref::getInstance($config);
		$folderName          = explode('.', $mediaAttachmentData->media->type);

		// Making File Download path For e.g /var/www/html/ttpl36-php71.local/your site/media/com_jgive/campaigns/reports/attachments
		$downloadPath        = JPATH_SITE . '/' . $params->get('report_attachment_upload_path', 'media/com_jgive/campaigns/reports/attachments');

		// Making File Download path For e.g /file mime type + 's'/text.pdf Here mime type like application + s this is folder name
		$downloadPath        = $downloadPath . '/' . $folderName[0] . 's' . '/' . $mediaAttachmentData->media->source;
		$media               = TJMediaStorageLocal::getInstance();
		$media->downloadMedia($downloadPath);
	}
}
