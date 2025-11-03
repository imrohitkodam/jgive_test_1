<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;


/**
 * Donors list controller class.
 *
 * @since  1.6
 */
class JGiveControllerDonors extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function getModel($name = 'donors', $prefix = 'JGiveModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to redirect for Mass mailing
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function redirectToMassmailing()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$mainframe = Factory::getApplication();
		$redirect  = $mainframe->input->get('view');

		if (!class_exists('JgiveControllerDonations'))
		{
			JLoader::register('JgiveControllerDonations', JPATH_ADMINISTRATOR . '/components/com_jgive/controllers/donations.php');
			JLoader::load('JgiveControllerDonations');
		}

		$jgiveControllerDonations = new JgiveControllerDonations;

		$jgiveControllerDonations->redirectToMassmailing($redirect);
	}
}
