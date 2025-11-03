<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

/**
 * JgiveViewregistration view class.
 *
 * @package  JGive
 * @since    1.8.1
 */
class JgiveViewregistration extends BaseHtmlView
{
	protected $itemId;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 * 
	 * @since   2.5.4
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if ($user && $user->id)
		{
			$link    = 'index.php?option=com_users&view=profile&layout=edit';
			$jgiveFrontendHelper = new jgiveFrontendHelper;
			$itemId = $jgiveFrontendHelper->getItemId($link);
			$msg    = Text::_('COM_JGIVE_ALREADY_LOGIN_WHILE_REGISTER_MESSAGE');
			$app->enqueueMessage($msg);
			$app->redirect(Route::_($link . '&Itemid=' . $itemId, false));
		}


		$this->itemId = $app->input->get('Itemid');
		parent::display($tpl);
	}
}
