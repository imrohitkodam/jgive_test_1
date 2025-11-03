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

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;


/**
 * jgiveViewCampaign class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       1.6.7
 */
class JgiveViewCampaign extends HtmlView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$mainframe            = Factory::getApplication();
		$callback             = Factory::getApplication()->input->get('callback', '');
		$this->campaignHelper = new campaignHelper;

		// Get logged in user id
		$user                = Factory::getUser();
		$this->logged_userid = $user->id;

		// Get params
		$params                       = ComponentHelper::getParams('com_jgive');
		$this->currency_code          = $params->get('currency');
		$this->commission_fee         = $params->get('commission_fee');
		$this->send_payments_to_owner = $params->get('send_payments_to_owner');
		$this->default_country        = $params->get('default_country');
		$this->admin_approval         = $params->get('admin_approval');

		// Create is a default layout
		$layout = Factory::getApplication()->input->get('layout', 'create');
		$this->setLayout($layout);

		// Create jgive helper object
		$jgiveFrontendHelper        = new jgiveFrontendHelper;

		if (($this->params->get('integration') == 'jomsocial') && $this->params->get('jomsocial_toolbar'))
		{
			$this->jomsocialToolbarHtml = $jgiveFrontendHelper->jomsocialToolbarHtml();
		}

		// Show a single campaign
		if ($layout == 'default')
		{
			$this->allCampsitemid       = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
			$this->createCampaignItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaignform');
			$this->singleCampaignItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');

			// Get campaign details
			$cdata       = $this->get('Campaign');
			$this->cdata = $cdata;

			// Do not show campaign if it is unpublished and redirect to all campaigns
			if (!$cdata['campaign']->published)
			{
				$itemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
				$link   = Route::_('index.php?option=com_jgive&view=campaigns&layout=all&Itemid=' . $itemid, false);
				$msg    = Text::_('COM_JGIVE_CAMPAIGN_NOT_PUBLISHED');
				$mainframe->enqueueMessage($msg);
				$mainframe->redirect($link);
			}

			// Breadcrumbs
			$app     = Factory::getApplication();
			$pathway = $app->getPathway();
			$pathway->addItem($cdata['campaign']->title, '');

			// Function to get campaign main image, progress percentage, days left etc
			$cdata = $this->campaignHelper->mapData($this->cdata, $this->singleCampaignItemid);

			$mapped_data = Array();
			$mapped_data = $this->cdata;

			// Add component params
			$mapped_data['site_root_link']   = Uri::root();
			$mapped_data['com_jgive_params'] = $params;

			if (!count($mapped_data))
			{
				echo $callback ? $callback . '(' . json_encode(array()) . ')' : json_encode(array());
				jexit();
			}

			echo $callback ? $callback . '(' . json_encode($mapped_data) . ')' : json_encode($mapped_data);
			jexit();
		}

		parent::display($tpl);
	}
}
