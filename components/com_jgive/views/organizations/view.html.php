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
defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Organizations Contacts view class.
 *
 * @since  2.5.0
 */
class JgiveViewOrganizations extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var  Joomla\CMS\Object\CMSObject
	 */
	protected $state;

	/**
	 * The Organizations object
	 *
	 * @var  \stdClass
	 */
	protected $items;

	/**
	 * JGive Config Parameter
	 */
	protected $params;

	/**
	 * @var  \JPagination
	 *
	 * @since  2.5.0
	 */
	protected $pagination;

	/**
	 * The Page Title String object
	 *
	 * @var
	 */
	protected $PageTitle;

	/**
	 * Function Adding toolbar action on individuals list view
	 */
	protected $addTJtoolbar;

	/**
	 * @var  \JForm
	 *
	 * @since  2.5.0
	 */
	public $filterForm;

	/**
	 * @var  array
	 *
	 * @since  2.5.0
	 */
	public $activeFilters;

	/**
	 * Used for adding ToolBar action as per ACL
	 *
	 * @since  2.5.0
	 */
	public $toolbarHTML;

	protected $user;

	protected $canCreate;

	protected $canChange;

	protected $canChangeState;

	protected $canDelete;

	protected $emails;

	protected $selectedEmails;

	protected $allDonations;

	protected $organizationFormItemId;

	protected $individualformFormItemId;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  2.5.0
	 */
	public function display($tpl = null)
	{
		$app        = Factory::getApplication();
		$this->user = Factory::getUser();

		// Validate user login.
		if (empty($this->user->id))
		{
			$msg = Text::_('COM_JGIVE_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		$this->messages = array();
		$this->messages['success']    = Text::_("COM_JGIVE_EXPORT_FILE_SUCCESS");
		$this->messages['error']      = Text::_("COM_JGIVE_EXPORT_FILE_ERROR");
		$this->messages['inprogress'] = Text::_("COM_JGIVE_EXPORT_FILE_NOTICE");

		$input         = $app->input;
		$this->_layout = $input->get('layout', 'default');
		$this->csv_url = 'index.php?option=' . $input->get('option') . '&view=' . $input->get('view') . '&format=csv';

		if ($this->_layout == 'contact_us')
		{
			$session                = Factory::getSession();
			$selectedContactItemIds = $session->get('selected_contact_item_ids');
			$this->selectedEmails   = $this->getModel('Organizations')->getContactsEmail($selectedContactItemIds);

			foreach ($this->selectedEmails as $key => $value)
			{
				if (empty($value['email']))
				{
					unset($this->selectedEmails[$key]);
				}
			}

			if (empty($this->selectedEmails))
			{
				$this->emails = '';
			}
			else
			{
				$this->emails = implode(", ", array_column($this->selectedEmails, 'email'));
			}
		}

		$authorised    = $this->user->authorise('core.create', 'com_jgive');

		if ($authorised !== true)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->params         = JGive::config();
		$this->state          = $this->get('State');
		$this->items          = $this->get('Items');
		$this->pagination     = $this->get('Pagination');
		$this->filterForm     = $this->get('FilterForm');
		$this->activeFilters  = $this->get('ActiveFilters');
		$this->canChangeState = $this->user->authorise('core.edit.state', 'com_jgive');
		$this->canChange      = $this->user->authorise('core.edit', 'com_jgive');
		$this->canCreate      = $this->user->authorise('core.create', 'com_jgive');
		$this->canDelete      = $this->user->authorise('core.delete', 'com_jgive');

		$utilitiesClassObj = JGive::utilities();
		$this->isVendor = $utilitiesClassObj->getVendorId($this->user->id, 'com_jgive');

		$vendorObj = TJVendors::vendor($this->isVendor);

		if (($this->isVendor && $this->params->get('silent_vendor') == 0) || $this->params->get('silent_vendor') == 1)
		{
			if (!$vendorObj->isApproved())
			{
				?>
				<div class="alert alert-info">
					<?php echo Text::_('COM_JGIVE_ORGANIZATION_VENDOR_NOT_APPROVED_MESSAGE');?>
				</div>
				<?php
				return false;
			}
		}
		else
		{
			?>
			<div class="alert alert-info alert-help-inline">
				<?php echo Text::_('COM_JGIVE_ORGANIZATION_VENDOR_ENFORCEMENT_ERROR');?>
				<?php echo Text::_('COM_JGIVE_ORGANIZATION_VENDOR_ENFORCEMENT_CAMPAIGN_REDIRECT_MESSAGE');?>
			</div>
			<div>
				<a href="<?php echo Route::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=com_jgive');?>" target="_blank" >
					<button class="btn btn-primary">
						<?php echo Text::_('COM_JGIVE_VENDOR_ENFORCEMENT_CAMPAIGN_REDIRECT_LINK'); ?>
					</button>
				</a>
			</div>
			<?php
			return false;
		}

		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$this->allDonations = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=all_donations');
		$this->organizationsItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=organizations&layout=default');
		$this->organizationFormItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=organizations&layout=default');
		$this->individualformFormItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=individualform&layout=default');

		$this->addTJtoolbar();
		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Add the ACL based tjtoolbar.
	 *
	 * @return void
	 *
	 * @since  2.5.0
	 */
	protected function addTJtoolbar()
	{
		// Add toolbar buttons
		jimport('techjoomla.tjtoolbar.toolbar');
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right');
		$smallBtnClassName = (JGIVE_LOAD_BOOTSTRAP_VERSION == 'bs3') ? 'btn-small' : 'btn-sm';

		// Create New individual contact
		if ($this->canCreate && $this->_layout == 'default')
		{
			$tjbar->appendButton('organizationform.add', 'TJTOOLBAR_NEW', '', 'class="btn '. $smallBtnClassName .' btn-success"');
		}

		if (!empty($this->items) && $this->_layout == 'default')
		{
			// Edit individual contact
			if ($this->canChange)
			{
				$tjbar->appendButton('organizationform.edit', 'TJTOOLBAR_EDIT', '', 'class="btn '. $smallBtnClassName .' btn-success"');
			}

			// Edit individual contact state
			if ($this->canChangeState)
			{
				$tjbar->appendButton('organizations.publish', 'TJTOOLBAR_PUBLISH', '', 'class="btn '. $smallBtnClassName .' btn-success"');
				$tjbar->appendButton('organizations.unpublish', 'TJTOOLBAR_UNPUBLISH', '', 'class="btn '. $smallBtnClassName .' btn-warning"');
			}

			if ($this->canDelete)
			{
				$tjbar->appendButton('organizations.delete', 'TJTOOLBAR_DELETE', '', 'class="btn '. $smallBtnClassName .' btn-danger"');
			}

			$tjbar->appendButton('tjtoolbar.custom',
						'COM_JGIVE_EMAIL_TO_DONORS',
						'glyphicon glyphicon-envelope icon icon-envelope',
						'onclick="com_jgive.UI.Organizations.redirectForMail(\'organizations.redirectforEmail\')" class="btn btn-secondary btn-sm"
						title="' . Text::_("COM_JGIVE_INDIVIDUALS_EMAIL_TO_CONTACT_TOOLTIP") . '"');

			$tjbar->appendButton('tjtoolbar.custom',
								'TJTOOLBAR_CSV_EXPORT',
								'glyphicon glyphicon-download-alt icon-download',
								'onclick="tjexport.exportCsv(0)" class="btn btn-info btn-sm"
								title="' . Text::_("TJTOOLBAR_CSV_EXPORT") . '"');
		}
		elseif(!empty($this->items) && $this->_layout == 'contact_us')
		{
			$tjbar->appendButton('tjtoolbar.custom',
								'COM_JGIVE_EMAIL_SEND',
								'glyphicon glyphicon-envelope icon icon-envelope',
								'onclick="com_jgive.UI.Common.sendEmail(\'organizations.emailtoSelected\')" class="btn btn-primary btn-sm"
								title="' . Text::_("COM_JGIVE_EMAIL_TO_DONORS_TOOLTIP") . '"');

			$tjbar->appendButton('organizations.cancelToMail',
								'COM_JGIVE_CANCEL_EMAIL',
								'glyphicon glyphicon-delete icon icon-delete',
								'class="btn btn-secondary btn-sm"
								title="' . Text::_("COM_JGIVE_CANCEL_EMAIL") . '"');
		}

		$this->toolbarHTML = $tjbar->render();
	}

	/**
	 * Method to display Individuals
	 *
	 * @return  void
	 *
	 * @since   @since  2.5.0
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$menu  = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_JGIVE_ORGANIZATIONS_PAGE_HEADING'));
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
}
