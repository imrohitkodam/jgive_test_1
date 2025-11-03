<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('techjoomla.view.csv');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Campaigns Csv Export 
 *
 * @package     JGive
 * @subpackage  Csv
 * @since       2.6.0
 */
class JgiveViewCampaigns extends TjExportCsv
{
	/**
	 * The campaigns object
	 *
	 * @var  \stdClass
	 */
	protected $download;

	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  Object|Boolean in case of success instance and failure - boolean
	 *
	 * @since   2.6.0
	 */
	public function display($tpl = null)
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$user  = Factory::getUser();
		$userAuthorisedExport = $user->authorise('core.create', 'com_jgive');

		if ($userAuthorisedExport !== true || !$user->id) {
			$redirect = 'index.php?option=com_jgive&view=campaigns';
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect($redirect);

			return false;
		} else {
			if ($input->get('task') == 'download') {
				$fileName = $input->get('file_name');
				$this->download($fileName);
				$app->close();
			} else {
				parent::display();
			}
		}
	}
}
