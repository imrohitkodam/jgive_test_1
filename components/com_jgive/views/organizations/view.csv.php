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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

jimport('techjoomla.view.csv');

/**
 * Csv
 *
 * @package     JGive
 * @subpackage  Csv
 * @since       2.5.0
 */
class JgiveViewOrganizations extends TjExportCsv
{
	/**
	 * The individual object
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
	 * @since   2.5.0
	 */
	public function display($tpl = null)
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$user  = Factory::getUser();
		$userAuthorisedExport = $user->authorise('core.create', 'com_jgive');

		if ($userAuthorisedExport !== true || !$user->id)
		{
			$redirect = Route::_('index.php?option=com_jgive&view=organizations', false);
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
