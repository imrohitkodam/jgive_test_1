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
use Joomla\CMS\Uri\Uri;

$jgiveFrontendHelper = new jgiveFrontendHelper;
$j = 1;
$donor_records_config = 5;

if (count($this->cdata['donors']) > 0)
{
	if ($this->cdata['orders_count'] > $donor_records_config)
	{
	?>
		<div class="col-xs-12 col-sm-12 ">
			<ul class="list-inline pull-right">
				<li>
					<span class="pull-right " id="SearchDonorsinputbox">
						<input type="text" id="donorInput" onkeyup="jgive.campaign.searchDonor()"
						placeholder="<?php echo JTEXT::_('COM_JGIVE_SINGLE_DONORS_SEARCH_PLACEHOLDER');?>"
						title="<?php echo JTEXT::_('COM_JGIVE_SINGLE_DONORS_SEARCH_PLACEHOLDER');?>"
						size="30">
					</span>
				</li>
			</ul>
		</div>
	<?php
	}?>
	<div class="clearfix"></div>
	<div class="row">
		<div class="col-sm-12 no-more-tables">
			<table class="table user-list table-striped table-bordered table-hover" id="singlecampaignDonor">
				<thead>
					<tr>
						<th>
							<span>
							<?php
								if ($this->cdata['campaign']->type == 'donation')
								{
									echo Text::_("COM_JGIVE_DONORNAME");
								}
								elseif ($this->cdata['campaign']->type == 'investment')
								{
									echo Text::_("COM_JGIVE_INVESTORNAME");
								}
							?>
							</span>
						</th>
						<th><span><?php echo Text::_("COM_JGIVE_TOTAL");?></span></th>
						<th><span><?php echo Text::_("COM_JGIVE_SINGLE_DONORS_RECENT_DONATION");?></span></th>
						<th><span><?php echo Text::_("COM_JGIVE_SINGLE_DONORS_PAYMENT_MODE"); ?></span></th>
					</tr>
				</thead>
				<tbody id="jgive_donors_pic">
				<?php
					$j = 1;

					foreach ($this->cdata['donors'] as $this->donor)
					{
						if ($j <= $donor_records_config)
						{
							echo $this->loadTemplate("donorslist");
						}
						else
						{
							break;
						}

						$j++;
					}
				?>
				</tbody>
			</table>
			<div class="alert alert-warning" id="noDataFoundDiv">
				<?php echo Text::_('COM_JGIVE_NO_DATA_FOUND');?>
			</div>
		</div>
	</div>
<?php
}
else
{
	echo ($this->cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_NO_DONATIONS') : Text::_('COM_JGIVE_NO_INVESTMENTS');
	echo "<br/>";
	echo (($this->cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_BE_THE_FIRST_DONOR') : Text::_('COM_JGIVE_BE_THE_FIRST_INVESTOR'));
}
?>
<input type="hidden" id="donors_pro_pic_index" value="<?php echo $j; ?>" />
<input type="hidden" id="camp_id" name="camp_id" value="<?php echo $this->cdata['campaign']->id; ?>" />

<?php
if ($this->cdata['groupedOrdersCount'] > $donor_records_config
	&& $this->cdata['campaign']->allow_view_donations)
{
?>
	<button id="btn_showMorePic" class="btn btn-info btn-md" type="button" onclick="jgive.campaign.showMoreDonors()">
		<?php
			echo Text::_('COM_JGIVE_SHOW_MORE_DONORS');
		?>
	</button>
<?php
} ?>

<script type="text/javascript">
	var gbl_jgive_index = 0 ;
	var jgive_baseurl;
	var orders_count = <?php
							if (!empty($this->cdata['orders_count']))
							{
								echo $this->cdata['orders_count'];
							}
							else
							{
								echo 0;
							}
							?>;

	var gbl_jgive_pro_pic = 0 ;
	jgive_baseurl = "<?php echo Uri::root(); ?>";
</script>
