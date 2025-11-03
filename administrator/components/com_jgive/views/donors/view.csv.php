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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

jimport('techjoomla.view.csv');

/**
 * Csv
 *
 * @package     JGive
 * @subpackage  Csv
 * @since       1.0
 */
class JgiveViewdonors extends TjExportCsv
{
	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  Object|Boolean in case of success instance and failure - boolean
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$user  = Factory::getUser();
		$userAuthorisedExport = $user->authorise('core.create', 'com_jgive');

		if ($userAuthorisedExport !== true || !$user->id)
		{
			// Redirect to the list screen.
			$redirect = Route::_('index.php?option=com_jgive&view=donors', false);
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect($redirect);

			return false;
		}
		else
		{
			if ($input->get('task') == 'download')
			{
				$fileName = $input->get('file_name');
				$this->download($fileName);
				$app->close();
			}
			else
			{
				parent::display();
			}
		}
	}
}
