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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;

HTMLHelper::_('bootstrap.renderModal', 'a.modal');
JLoader::import('campaigns', JPATH_ADMINISTRATOR . '/components/com_jgive/helpers');
JLoader::import('integrations', JPATH_SITE . '/components/com_jgive/helpers');

/**
 * Donations view class.
 *
 * @package  JGive
 * @since    1.8
 */
class JgiveViewDonation extends HtmlView
{
	protected $params;

	protected $retryPayment;

	protected $pstatus;

	protected $sstatus;

	protected $donationDetails;

	protected $sidebar;

	protected $layout;

	protected $certificateHtml;

	/**
	 * Function to display.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths
	 *
	 * @return  boolean|null
	 *
	 * @since	1.8
	 */
	public function display($tpl = null)
	{
		$app          = Factory::getApplication();
		$user         = Factory::getUser();
		$this->params = ComponentHelper::getParams('com_jgive');
		$this->layout = $app->input->get('layout', 'default');

		$this->jgiveFrontendHelper = new jgiveFrontendHelper;
		$this->donationsHelper     = new DonationsHelper;

		if (!$user->authorise('core.manage', 'com_jgive'))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_AUTH_ERROR'), 'error');

			return false;
		}

		// Load submenu
		$jgiveHelper = new JgiveHelper;
		$jgiveHelper->addSubmenu('donations');

		// For payments
		$this->retryPayment         = new StdClass;
		$this->retryPayment->status = '';
		$this->retryPayment->msg    = '';

		// Used on donations view for payment status filter
		$donationsHelper = new DonationsHelper;
		$this->pstatus   = $donationsHelper->getSStatusArray();
		$this->sstatus   = $donationsHelper->getSStatusArray();

		// Load language file for component backend
		$lang = Factory::getLanguage();
		$lang->load('com_jgive', JPATH_SITE);

		$this->donationDetails = $this->get('Item');

		$params = ComponentHelper::getParams('com_jgive');
		$key = $this->params->get('pan_encryption_key');
		if (!empty($this->donationDetails['donor']->pannumber))
		{
			try
			{
				JLoader::register('DonationsHelper', JPATH_SITE . '/components/com_jgive/helpers/donations.php');
				$this->donationDetails['donor']->pannumber = DonationsHelper::decryptData($this->donationDetails['donor']->pannumber, $key);
			}
			catch (Exception $e)
			{
				$this->donationDetails['donor']->pannumber = Text::_('COM_JGIVE_INVALID_PAN_FORMAT');
			}
		}

		if (empty($this->donationDetails))
		{
			$app->enqueueMessage(Text::_('COM_JGIVE_NO_DATA'), 'error');

			return false;
		}

		if ($this->layout == 'generate_certificate')
		{
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models', 'receipttemplate');
			$receiptTemplateModel = BaseDatabaseModel::getInstance('receipttemplate', 'JGiveModel');

			$this->certificateHtml = $receiptTemplateModel->generateReceipt($app->input->get('donationid'));
		}

		$this->_setToolBar();
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Function to set tool bar.
	 *
	 * @return void
	 *
	 * @since	1.8
	 */
	public function _setToolBar()
	{
		$donationId = $this->donationDetails['payment']->id;
		$token      = Session::getFormToken();

		$toolbar      = Toolbar::getInstance('toolbar');
		ToolbarHelper::back('COM_JGIVE_BACK', 'index.php?option=com_jgive&view=donations');

		$toolbar->appendButton(
			'Custom',
			'<button onclick="com_jgive.UI.Common.printReceipt()"
			class="btn btn-default btn-small no-print">
			<span class="icon-print icon-fw" aria-hidden="true"></span>' .
			htmlspecialchars(Text::_('COM_JGIVE_DONATION_PRINT')) . '</button>'
		);

		if ($this->donationDetails['campaign']->type == 'donation')
		{
			$toolbar->appendButton(
				'Custom',
				'<button onclick="com_jgive.UI.Donation.generateReceipt(' . $donationId . ',\'' . $token . '\')"
				class="btn btn-default btn-small no-print">
				<span class="icon-file icon-fw" aria-hidden="true"></span>' .
				htmlspecialchars(Text::_('COM_JGIVE_GENERATE_DONATION_RECEIPT')) . '</button>'
			);
		}

		ToolbarHelper::title(Text::_("COM_JGIVE") . ": " . Text::_('COM_JGIVE_DONATIONS'), 'list');
		ToolbarHelper::preferences('com_jgive');
	}
}
