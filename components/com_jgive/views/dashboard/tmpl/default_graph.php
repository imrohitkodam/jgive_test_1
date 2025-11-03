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

use Joomla\CMS\Language\Text;
?>
<div class="col-xs-12 <?php echo $this->graphColumnClass;?>">
	<div class="row">
		<div class="col-xs-12 col-md-11">
			<div class="row af-mb-15">
				<div class="col-xs-6">
					<h6 class="af-font-bold"><?php echo strtoupper(Text::_('COM_JGIVE_DONATIONS'));?></h6>
				</div>
				<div class="col-xs-6">
					<select id='dashboardcampaignsOption' class="pull-right form-select form-select-sm w-auto d-inline-block">
						<option value = '0'><?php echo Text::_('COM_JGIVE_FILTER_LATEST');?></option>
						<option value = '1'><?php echo Text::_('COM_JGIVE_FILTER_LAST_MONTH');?></option>
						<option value = '2'><?php echo Text::_('COM_JGIVE_FILTER_LAST_YEAR');?></option>
					</select>
				</div>
			</div>
			<canvas id="dashboardCampaign_graph"></canvas>

			<!--Payout-->
			<ul class="list-inline af-mt-15">
				<li>
					<h6 class="af-font-bold"><?php echo strtoupper(Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_PAYOUT'));?></h6>
				</li>
				<li>
					<span class="text-muted"><?php echo Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_RECEIVE_AMOUNT');?></span>
					<?php echo $this->receivedPayoutAmount;?>
				</li>
				<li>
					<span class="text-muted"><?php echo Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_PENDING_AMOUNT');?></span>
					<?php 
					if (!empty($this->pendingPayoutAmount))
					{
						echo $this->pendingPayoutAmount['com_jgive'][$this->currency];
					}
					else
					{
						echo " - ";
					}?>
				</li>
			</ul>
			<!--Payout-->
		</div>
	</div>
</div>
