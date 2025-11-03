<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\ItemModel;

/**
 * Model for order for getting single order details
 *
 * @package     JGive
 * @subpackage  component
 * @since       2.5.0
 */
class JgiveModelOrder extends ItemModel
{
	/**
	 * @var       object  The order details.
	 * @since   2.5.0
	 */
	protected $data;

	/**
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 *
	 * @since  2.5.0
	 */
	public function getTable($type = 'Orders', $prefix = 'JGiveTable', $config = array())
	{
		return JGive::table('orders', array('ignore_request' => true));
	}

	/**
	 * Method to get the order data form data.
	 *
	 * The base form data is loaded and then an event is fired
	 * for users plugins to extend the data.
	 *
	 * @param   int  $pk  Primary Key
	 *
	 * @return  JGiveOrder	Data object on success, false on failure.
	 *
	 * @since   2.5.0
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('order.id');

		if ($this->data === null)
		{
			// Initialise and return data with JGiveOrder obj.
			$jgiveOrder = JGive::order($pk);
			$this->data = $jgiveOrder->getProperties();

			if ($this->data['id'] != 0)
			{
				$this->data['formatted_original_amount']       = $jgiveOrder->getOriginalAmount(true);
				$this->data['formatted_amount']                = $jgiveOrder->getAmount(true);
				$this->data['formatted_cdate']                 = $jgiveOrder->getCreatedDate(true);
				$this->data['formatted_payment_received_date'] = $jgiveOrder->getPaymentReceivedDate(true);
				$this->data['formatted_processor']             = $jgiveOrder->getProcessor(true);
				$this->data['status']                          = $jgiveOrder->getStatus(true);
			}
		}

		return $this->data;
	}

	/**
	 * Generate valid order status
	 *
	 * @param   string  $status       Donation order status
	 *
	 * @param   array   $allStatuses  All Status
	 *
	 * @return  array
	 */
	public function getValidOrderStatus($status, $allStatuses)
	{
		$unsetOrderStatus = array(
				"P"   => array (0 => "RF"),
				"C"   => array (0 => "P",  1 => "D",   2 => "E"),
				"D"   => array (0 => "P",  1 => "C",   2 => "E", 3 => "RF"),
				"E"   => array (0 => "P",  1 => "C",   2 => "D", 3 => "RF"),
				"RF"  => array (0 => "P",  1 => "C",   2 => "D", 3 => "E"),
		);

		foreach ($unsetOrderStatus as $key => $orderStatuses)
		{
			if ($key === $status)
			{
				foreach ($orderStatuses as $orderStatus)
				{
					// Unset the indexes
					unset($allStatuses[$orderStatus]);
				}
			}
		}

		return $allStatuses;
	}

	/**
	 * Method to get the full form of the donation order statuses
	 *
	 * @param   String  $type  The type of status array you want, default is 'fullforms' for key as status and full form of status as Value
	 * 'statuses' is for only status as Value
	 * 'list' is for select list of status.
	 *
	 * @return  array array of the order status full forms and on failed condition boolean false
	 *
	 * @since  2.6.0
	 */
	public function getOrderStatues($type = 'fullforms')
	{
		if (empty($type))
		{
			return array();
		}

		$statues = array(
					COM_JGIVE_CONSTANT_ORDER_STATUS_PENDING,
					COM_JGIVE_CONSTANT_ORDER_STATUS_COMPLETED,
					COM_JGIVE_CONSTANT_ORDER_STATUS_REFUND,
					COM_JGIVE_CONSTANT_ORDER_STATUS_CANCELED,
					COM_JGIVE_CONSTANT_ORDER_STATUS_DECLINE
				);

		$fullForms = array(Text::_('COM_JGIVE_PENDING'),
						Text::_('COM_JGIVE_CONFIRMED'),
						Text::_('COM_JGIVE_REFUND'),
						Text::_('COM_JGIVE_CANCELED'),
						Text::_('COM_JGIVE_DENIED')
					);

		if ($type === 'fullforms')
		{
			// In case status as key and full form of status as value is required.
			return array_combine($statues, $fullForms);
		}
		elseif ($type === 'statuses')
		{
			// In case array with only status as value required.
			return $statues;
		}

		// In case of select list is required. Default return the list
		$selectList = array();

		// Set default select option.
		$selectList[] = HTMLHelper::_('select.option', '-1', Text::_('COM_JGIVE_APPROVAL_STATUS'));

		foreach ($statues as $key => $value)
		{
			$selectList[] = HTMLHelper::_('select.option', $value, $fullForms[$key]);
		}

		return $selectList;
	}
}
