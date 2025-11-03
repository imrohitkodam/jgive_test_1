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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;

/**
 * ReceiptTemplate controller class.
 *
 * @since  2.3.0
 */
class JgiveControllerReceiptTemplate extends FormController
{
	/**
	 * Method to save a receipt template structure.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   2.3.0
	 */
	public function save($key = null, $urlVar = null)
	{
		$model  = $this->getModel('receiptTemplate');
		$result = $model->save();
		$this->setRedirect('index.php?option=com_jgive&view=receipttemplate&layout=default', $result['msg']);
	}

	/**
	 *Function to save
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function cancel($key = null)
	{
		$this->setRedirect('index.php?option=com_jgive', false);
	}
}
