<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Editor\Editor;

// Fetching Email message body
include_once JPATH_ADMINISTRATOR . "/components/com_jgive/template/receipt_template.php";

HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive-tables.css');
HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.min.css');
HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.css');
HTMLHelper::_('behavior.formvalidator');
?>
<div id="jgiveWrapper" class="j-main-container tjBs3">
	<form action="<?php echo Route::_('index.php?option=com_jgive&view=receipttemplate&layout=default');?>" id="adminForm" name="adminForm" method="post" class="form-validate" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-7">
				<?php
					$emorgdata = $emails_config['message_body'];
					$jconfig = Factory::getConfig();
					$configuredEditor = $jconfig->get('editor');
					$editor = Editor::getInstance($configuredEditor);
					echo $editor->display("data[message_body]", stripslashes($emorgdata), 670, 600, 60, 20, false);?>

			</div>
			<div class="col-md-5">
				<div class="alert alert-info">
					<?php echo Text::_('COM_JGIVE_REEIPT_TEMPLATE_TAGS_DESC');?>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{order.receipt_no}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_RECEIPT_NUMBER');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{order.date_of_payment}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_PAYMENT_DATE');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{order.payment_received_date}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_PAYMENT_RECEIVED_DATE');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{order.payment_mode}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_PAYMENT_METHOD');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{order.original_amount}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_PAYMENT_AMOUNT');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{order.amount_in_word}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_PAYMENT_AMOUNT_IN_WORD');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{donor.name}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_DONOR_NAME');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{donor.country}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_DONOR_COUNTRY');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{donor.state}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_DONOR_STATE');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{donor.city}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_DONOR_REGION');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{donor.address}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_DONOR_ADDRESS');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{donor.contact}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_DONOR_CONTACT');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{donor.email}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_DONOR_EMAIL');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{donor.taxnumber}</strong>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
						<?php echo Text::_('COM_JGIVE_TAGS_DONOR_TAX_NUMBER');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 text-break">
						<strong>{campaign.title}</strong>
					</div>
					<div class="col-md-6 text-break">
						<?php echo Text::_('COM_JGIVE_TAGS_CAMPAIGN_TITLE');?>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token');?>
	</form>
</div>
