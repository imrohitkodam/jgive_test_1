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
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;

jimport('techjoomla.tjtoolbar.button.csvexport');

/**
 * JgiveViewDonors view class.
 *
 * @package  JGive
 * @since    1.8.1
 */
class JgiveViewDonors extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	protected $promoterDonationsItemId;

	/**
	 * Function dispaly
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function display($tpl = null)
	{
		$app                 = Factory::getApplication();
		$input               = $app->input;
		$this->logged_userid = Factory::getUser()->id;

		$input               = Factory::getApplication()->input;
		$layout              = Factory::getApplication()->input->get('layout', 'default');

		$this->messages = array();
		$this->messages['success'] = Text::_("COM_JGIVE_EXPORT_FILE_SUCCESS");
		$this->messages['error'] = Text::_("COM_JGIVE_EXPORT_FILE_ERROR");
		$this->messages['inprogress'] = Text::_("COM_JGIVE_EXPORT_FILE_NOTICE");

		$input = Factory::getApplication()->input;
		$this->csv_url = 'index.php?option=' . $input->get('option') . '&view=' . $input->get('view') . '&format=csv';

		// Check login status
		if (!$this->logged_userid)
		{
			$msg = Text::_('COM_JGIVE_LOGIN_MSG');
			$uri = $input->server->get('REQUEST_URI', '', 'STRING');
			$url = base64_encode($uri);
			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		// Get itemids
		$menu = $app->getMenu();
		$menuItemId = $menu->getActive()->id;
		$this->itemid = $menuItemId;

		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');

		$this->pagination    = $this->get('Pagination');
		$this->params        = $app->getParams('com_jgive');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (!class_exists('JgiveModelDashboard'))
		{
			JLoader::register('JgiveModelDashboard', JPATH_SITE . '/components/com_jgive/models/dashboard.php');
			JLoader::load('JgiveModelDashboard');
		}

		$dashboardModelObject = new JgiveModelDashboard;
		$this->campaignsId = $dashboardModelObject->getCampaignIds();
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$this->promoterDonationsItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=all_donations');

		if ($layout == 'contact_us')
		{
			$session                 = Factory::getSession();
			$selected_donor_item_ids = $session->get('selected_donor_item_ids');
			$this->selected_emails   = $this->getModel()->getDonorsEmail($selected_donor_item_ids);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Setup toolbar
		$this->addTJtoolbar();

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Setup ACL based tjtoolbar
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	protected function addTJtoolbar ()
	{
		$layout    = Factory::getApplication()->input->get('layout', 'default');
		$user      = Factory::getUser();
		$canCreate = $user->authorise('core.create', 'com_jgive');

		// Add toolbar buttons
		jimport('techjoomla.tjtoolbar.toolbar');
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right float-end');

		// Load library language file
		Factory::getLanguage()->load('lib_techjoomla', JPATH_SITE, null, false, true);
		Text::script('LIB_TECHJOOMLA_CSV_EXPORT_ABORT');
		Text::script('LIB_TECHJOOMLA_CSV_EXPORT_UESR_ABORTED');
		Text::script('LIB_TECHJOOMLA_CSV_EXPORT_CONFIRM_ABORT');

		if ($layout == 'contact_us')
		{
			// Send button code on contact us file
			$tjbar->appendButton('donors.emailtoSelected',
								'COM_JGIVE_EMAIL_SEND',
								'glyphicon glyphicon-envelope icon icon-envelope',
								'class="btn btn-primary btn-sm"
								title="' . Text::_("COM_JGIVE_EMAIL_SEND_TOOLTIP") . '"');

			$tjbar->appendButton('donors.cancelToMail',
								'COM_JGIVE_CANCEL_EMAIL',
								'glyphicon glyphicon-remove icon icon-remove',
								'class="btn btn-secondary btn-sm"
								title="' . Text::_("COM_JGIVE_CANCEL_EMAIL") . '"');
		}
		else
		{
			if (count($this->items) > 0 && $canCreate === true && $user->id)
			{
				$tjbar->appendButton('donors.redirectforEmail',
									'COM_JGIVE_EMAIL_TO_DONORS',
									'glyphicon glyphicon-envelope icon icon-envelope',
									'class="btn btn-secondary btn-sm"
									title="' . Text::_("COM_JGIVE_EMAIL_TO_DONORS_TOOLTIP") . '"');

				$tjbar->appendButton('tjtoolbar.custom',
									'COM_JGIVE_CSV_EXPORT',
									'glyphicon glyphicon-download-alt icon-download',
									'onclick="tjexport.exportCsv(0)" class="btn btn-info btn-sm"
									title="' . Text::_("COM_JGIVE_CSV_EXPORT_TOOLTIP") . '"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}

	/**
	 * Function _prepareDocument
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_JGIVE_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

	/**
	 * Function getState
	 *
	 * @param   String  $state  State
	 *
	 * @return  boolean
	 *
	 * @since   1.8.1
	 */
	public function getState($state)
	{
		return isset($this->state->{$state}) ? $this->state->{$state} : false;
	}
}
