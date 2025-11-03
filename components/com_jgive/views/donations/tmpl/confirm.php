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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$document = Factory::getDocument();
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

if (JVERSION < '4.0.0')
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

$url = "index.php?option=com_jgive&task=donations.gethtml&processor=";
$ajax = "techjoomla.jQuery(document).ready(function()
{
	techjoomla.jQuery('#gateways').change(function()
	{
		var url1='" . $url . "'+document.getElementById('gateways').value;
		techjoomla.jQuery('#html-container').empty().append(
		'<div class=\"com_jgive_ajax_loading\"><div class=\"com_jgive_ajax_loading_text\">" . Text::_('COM_JGIVE_LOADING_PAYMET_FORM_MSG') . "</div><img class=\"com_jgive_ajax_loading_img\" src=\"" . Uri::base() . "media/com_jgive/images/ajax.gif\"></div>');
	 	techjoomla.jQuery.ajax(
	 	{
			url:url1,
			type:'GET',
			dataType:'html',
			success:function(response){
				techjoomla.jQuery('#html-container').removeClass('ajax-loading').html(response);
			}
		});
	});
});";
$document->addScriptDeclaration($ajax);
$cdata = $this->cdata;
?>
<div class="techjoomla-bootstrap">
	<h2 class="componentheading"><?php echo Text::_('COM_JGIVE_MAKE_PAYMENT_CONFIRM');?></h2>
	<hr/>
	<h4><?php echo Text::_('COM_JGIVE_CAMPAIGN_DETAILS');?></h4>

	<div style="width:100%;">
		<table class="table table-bordered table-striped">
			<tr>
				<td style="width:50%;">
					<?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_CAMPAIGN_YOU_ARE_DONTAING_TO') : Text::_('COM_JGIVE_CAMPAIGN_YOU_ARE_INVESTING_IN'));
						$itemid = $this->jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations');
					?>
				</td>
				<td style="width:50%;">
					<a target='_blank' href="<?php echo Uri::root() . substr(Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $cdata['campaign']->id . '&Itemid=' . $itemid), strlen(Uri::base(true)) + 1);?>">
						<?php echo htmlspecialchars($cdata['campaign']->title, ENT_COMPAT, 'UTF-8');?>
					</a>
				</td>
			</tr>

			<tr>
				<td style="width:50%;"><?php echo Text::_('COM_JGIVE_GOAL_AMOUNT');?></td>
				<td style="width:50%;"><?php echo $this->jgiveFrontendHelper->getFormattedPrice($cdata['campaign']->goal_amount);?></td>
			</tr>

			<tr>
				<td style="width:50%;"><?php echo Text::_('COM_JGIVE_AMOUNT_RECEIVED');?></td>
				<td style="width:50%;"><?php echo $this->jgiveFrontendHelper->getFormattedPrice($cdata['campaign']->amount_received);?></td>
			</tr>
			<tr>
				<td style="width:50%;"><?php echo Text::_('COM_JGIVE_REMAINING_AMOUNT');?></td>
				<td style="width:50%;">
					<?php
						if ($cdata['campaign']->amount_received > $cdata['campaign']->goal_amount)
						{
							echo Text::_('COM_JGIVE_NA');
						}
						else
						{
							echo $this->jgiveFrontendHelper->getFormattedPrice($cdata['campaign']->remaining_amount);
						}
					?>
				</td>
			</tr>
		</table>
	</div>

	<div style="width:100%;">
		<h4>
			<?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_CAMPAIGN_DONATION_DETAILS') : Text::_('COM_JGIVE_CAMPAIGN_INVESTMENT_DETAILS'));
			?>
		</h4>
		<table class="table table-bordered table-striped">
			<tr>
				<td style="width:50%;">
					<?php echo (($cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_AMOUNT_YOU_ARE_DONATING') : Text::_('COM_JGIVE_AMOUNT_YOU_ARE_INVESTING'));
					?>
				</td>
				<td style="width:50%;"><?php echo $this->session->get('JGIVE_donation_amount') . ' ' . $this->currencySymbol;?></td>
			</tr>
			<tr>
				<td style="width:50%;"><?php echo Text::_('COM_JGIVE_SEL_GATEWAY');?></td>
				<td style="width:50%;">
					<?php
						$select[] = new stdclass;
						$select[0]->id = 0;
						$select[0]->name = Text::_('COM_JGIVE_SEL_GATEWAY');
						$gateways = array_merge($select, $this->gateways);
						$gateways = array_filter($gateways);

						if (empty($this->gateways))
						{
							echo Text::_('COM_JGIVE_NO_PAYMENT_GATEWAY');
						}
						else
						{
							$pg_list = HTMLHelper::_('select.genericlist', $gateways, 'gateways', 'class="inputbox form-select" id="gateways"', 'id', 'name');
							echo $pg_list;
						}
					?>
				</td>
			</tr>
		</table>
	</div>

	<div id="html-container"></div>

</div>
