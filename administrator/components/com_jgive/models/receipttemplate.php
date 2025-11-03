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

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

jimport('techjoomla.tjmail.mail');
jimport('techjoomla.tjmoney.tjmoney');

/**
 * ReceiptTemplate Model class.
 *
 * @since  2.3.0
 */
class JgiveModelReceiptTemplate extends ListModel
{
	/**
	 * Method to save receipt template
	 *
	 * @return  boolean
	 *
	 * @since   2.3.0
	 */
	public function save()
	{
		$app          = Factory::getApplication();
		$input        = $app->input;
		$message_body = $input->get('data', '', 'array');
		$result       = false;
		$template_css = '';

		if (!empty($message_body))
		{
			$file     = JPATH_ADMINISTRATOR . "/components/com_jgive/template/receipt_template.php";

			if (isset($message_body['template_css']))
			{
				$template_css = $message_body['template_css'];
				unset($message_body['template_css']);
			}

			$file_contents = "<?php \n\n";

			// Add email_subject value into array
			$file_contents .= "\$emails_config=array(" . $this->row2text($message_body) . ");\n";
			$file_contents .= "\n?>";

			if (File::write($file, $file_contents))
			{
				$result['msg']     = Text::_('COM_JGIVE_REEIPT_TEMPLATE_SAVE_SUCCESSFULLY');
				$result['success'] = true;
			}
			else
			{
				$result['msg']     = Text::_('COM_JGIVE_REEIPT_TEMPLATE_SAVE_FAILED');
				$result['success'] = false;
			}

			$cssfile = JPATH_SITE . "/media/com_jgive/css/receipt_template.css";
			File::write($cssfile, $template_css);
		}

		return $result;
	}

	/**
	 * Method to get data
	 *
	 * @param   array  $row    row for template
	 * @param   array  $dvars  dvars
	 *
	 * @return  string
	 *
	 * @since   2.3.0
	 */
	public function row2text($row, $dvars = array())
	{
		reset($dvars);

		foreach ($dvars as $idx => $var)
		{
			unset($row[$var]);
		}

		$text = '';
		reset($row);
		$flag = $i = 0;

		foreach ($row as $var => $val)
		{
			if ($flag == 1)
			{
				$text .= ",\n";
			}
			elseif ($flag == 2)
			{
				$text .= ",\n";
			}

			$flag = 1;

			if (is_numeric($var))
			{
				if ($var[0] == '0')
				{
					$text .= "'$var'=>";
				}
				else
				{
					if ($var !== $i)
					{
						$text .= "$var=>";
					}

					$i = $var;
				}
			}
			else
			{
				$text .= "'$var'=>";
			}

			$i++;

			if (is_array($val))
			{
				$text .= "array(" . $this->row2text($val, $dvars) . ")";
				$flag = 2;
			}
			else
			{
				$text .= "\"" . addslashes($val) . "\"";
			}
		}

		return $text;
	}

	/**
	 * Method to generate 80G certificate
	 *
	 * @param   integer  $orderId  Order Id
	 *
	 * @return  array
	 *
	 * @since   2.3.0
	 */
	public function generateReceipt($orderId)
	{
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jgive/models', 'donation');
		$jgiveModelDonation  = BaseDatabaseModel::getInstance('donation', 'JGiveModel');
		$item                = $jgiveModelDonation->getItem($orderId);
		$jgiveFrontendHelper = new jgiveFrontendHelper;

		$params        = ComponentHelper::getParams('com_jgive');
		$tjCurrency    = new TjMoney($params->get('currency'));
		$body          = '';
		$res           = array();
		$res['status'] = true;

		if (empty($item['payment']->status) || ($item['payment']->status != 'C'))
		{
			$body = "<div class='alert alert-primary'>" . Text::_("COM_JGIVE_GENERATE_REEIPT_FAIL_STATUS") . "</div>";

			$res['body']   = $body;
			$res['status'] = false;

			return $res;
		}

		$flag = true;

		switch (true)
		{
			case (empty($item['donor']->phone)):
			$flag = false;
			break;

			case (empty($item['donor']->taxnumber)):
			$flag = false;
			break;

			case (empty($item['donor']->first_name)):
			$flag = false;
			break;

			case (empty($item['donor']->address)):
			$flag = false;
			break;

			case (empty($item['donor']->email)):
			$flag = false;
			break;
		}

		if ($flag === false)
		{
			$body = "<div class='alert alert-primary'>" . Text::_("COM_JGIVE_GENERATE_REEIPT_FAIL_PHONE_TAX") . "</div>";

			$res['body']   = $body;
			$res['status'] = false;

			return $res;
		}

		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');

		// Fetching Country Data
		$countryTable           = Table::getInstance('country', 'TjfieldsTable', array());
		$countryTable->load(array('id' => $item['donor']->country));

		// Fetching State Data
		$regionTable           = Table::getInstance('region', 'TjfieldsTable', array());
		$regionTable->load(array('id' => $item['donor']->state));

		$city = $item['donor']->city;

		// Fetching city Data
		if (is_numeric($item['donor']->city))
		{
			$cityTable           = Table::getInstance('city', 'TjfieldsTable', array());
			$cityTable->load(array('id' => $item['donor']->city));
			$city = (property_exists($cityTable, 'city') ? $cityTable->city : '');
		}

		// Collect Receipt required data
		$receiptData = array();
		$receiptData['order']    = new stdClass;
		$receiptData['donor']    = new stdClass;
		$receiptData['campaign'] = new stdClass;

		$receiptData['order']->receipt_no              = $item['payment']->order_id;
		$receiptData['order']->date_of_payment         = HTMLHelper::_('date', $item['payment']->cdate, $params->get('date_format', 'j  M  Y'));
		$receiptData['order']->payment_received_date   = HTMLHelper::_('date', $item['payment']->payment_received_date, $params->get('date_format', 'j  M  Y'));
		$receiptData['order']->payment_mode            = ucwords($item['payment']->processor);
		$receiptData['order']->original_amount         = $jgiveFrontendHelper->getFormattedPrice($item['payment']->original_amount);
		$receiptData['order']->original_amount_in_word = $tjCurrency->numberToWord($item['payment']->original_amount, $params->get('currency'));
		$receiptData['donor']->name                    = ucwords($item['donor']->first_name . ' ' . $item['donor']->last_name);

		if ($item['donor']->donor_type == 'org' && !empty($item['donor']->org_name))
		{
			$receiptData['donor']->name  = ucwords($item['donor']->org_name);
		}

		$receiptData['donor']->address = $item['donor']->address . ' ' . $item['donor']->address2;
		$receiptData['donor']->country = (property_exists($countryTable, 'country') ? ucwords($countryTable->country) : '');
		$receiptData['donor']->state   = (property_exists($regionTable, 'region') ? ucwords($regionTable->region) : '');
		$receiptData['donor']->city    = ucwords($city);
		$receiptData['donor']->contact = $item['donor']->phone;
		$receiptData['donor']->email   = $item['donor']->email;
		$receiptData['donor']->taxnumber     = $item['donor']->taxnumber;

		$receiptData['campaign']->title = ucwords($item['campaign']->title);

		$emails_config = array();

		// File which contain receipt html
		include_once JPATH_ADMINISTRATOR . "/components/com_jgive/template/receipt_template.php";

		// Fetching Receipt Html here
		$htmldata = $emails_config['message_body'];

		// Pass html & data for replacing tag
		$body = TjMail::TagReplace($htmldata, $receiptData);

		$res['body']   = $body;

		return $res;
	}
}
