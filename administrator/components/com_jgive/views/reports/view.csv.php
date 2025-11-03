<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

jimport('techjoomla.view.csv');

/**
 * View for donations
 *
 * @package     JGive
 * @subpackage  component
 * @since       2.3.5
 */
class JgiveViewReports extends TjExportCsv
{
	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  Object|Boolean in case of success instance and failure - boolean
	 *
	 * @since   2.3.5
	 */
	public function display($tpl = null)
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$user  = Factory::getUser();

		if (!$user->authorise('core.create', 'com_jgive'))
		{
			// Redirect to the list screen.
			$redirect = Route::_('index.php?option=com_jgive&view=reports', false);
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect($redirect);

			return false;
		}
		else
		{
			if ($input->get('task') == 'download')
			{
				$this->download($input->get('file_name'));
				$app->close();
			}
			else
			{
				parent::display();
			}
		}
	}
}
