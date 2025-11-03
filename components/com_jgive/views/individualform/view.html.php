<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

include_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

/**
 * JgiveViewIndividual form view class.
 *
 * @package     JGive
 * @subpackage  com_jgive
 * @since       2.5.0
 */
class JGiveViewIndividualForm extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var  Joomla\CMS\Object\CMSObject
	 */
	protected $state;

	/**
	 * The individual contact object
	 *
	 * @var  \stdClass
	 */
	protected $item;

	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The user object
	 *
	 * @var  \JUser|null
	 */
	protected $user;

	protected $countries;

	protected $default;

	protected $options;

	protected $isVendor;

	protected $params;

	protected $tmpl;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  An optional associative array.
	 *
	 * @return  mixed
	 *
	 * @since  2.5.0
	 */
	public function display($tpl = null)
	{
		$app         = Factory::getApplication();
		$this->user  = Factory::getUser();
		$this->tmpl  = $app->input->get('tmpl', '', 'STRING');

		// Validate user login.
		if (!$this->user->id)
		{
			$msg = Text::_('COM_JGIVE_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		$this->params = JGive::config();
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');

		// Check is vendor
		$utilitiesClassObj = JGive::utilities();
		$this->isVendor = $utilitiesClassObj->getVendorId($this->user->id, 'com_jgive');

		$vendorObj = TJVendors::vendor($this->isVendor);

		if (($this->isVendor && $this->params->get('silent_vendor') == 0) || $this->params->get('silent_vendor') == 1)
		{
			if (!$vendorObj->isApproved())
			{
				?>
				<div class="alert alert-info">
					<?php echo Text::_('COM_JGIVE_VENDOR_NOT_APPROVED_MESSAGE');?>
				</div>
				<?php
				return false;
			}
		}
		else
		{
			?>
			<div class="alert alert-info alert-help-inline">
				<?php echo Text::_('COM_JGIVE_INDIVIDUAL_VENDOR_ENFORCEMENT_ERROR');?>
				<?php echo Text::_('COM_JGIVE_INDIVIDUAL_VENDOR_ENFORCEMENT_REDIRECT_MESSAGE');?>
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

		/* Checking create access else for edit*/
		$authorised = (empty($this->item->id && $this->user->authorise('core.create', 'com_jgive'))) ||
		($this->user->authorise('core.edit', 'com_jgive') && (($this->item->vendor_id == $this->isVendor) ||
		($this->user->authorise('core.admin', 'com_jgive'))));

		if ($authorised !== true)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
			$app->setHeader('status', 403, true);

			return;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('<br />', $errors), 500);
		}

		$utilitiesObj = JGive::utilities();
		$this->countries = $utilitiesObj->getCountries();

		$this->default = null;

		if (isset($this->item->country))
		{
			$this->default = $this->item->country;
		}

		if (empty($this->item->region))
		{
			$this->item->region = '';
			$this->item->city = '';
		}

		$this->options = array();
		$this->options[] = HTMLHelper::_('select.option', 0, Text::_('COM_JGIVE_INDIVIDUAL_FORM_DESC_COUNTRY'));

		foreach ($this->countries as $key => $value)
		{
			$country = $this->countries[$key];
			$id      = $country['id'];
			$value   = $country['country'];
			$this->options[] = HTMLHelper::_('select.option', $id, $value);
		}

		parent::display($tpl);
	}
}
