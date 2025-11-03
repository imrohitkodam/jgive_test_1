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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
$campaign = (object) $displayData;
$otherData = $displayData['otherData'];
$document = Factory::getDocument();
HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive_bs3.css');

$campaignDetailUrl = Uri::root() . substr(
JRoute::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . (int) $campaign->id . '&Itemid=' . $otherData['allCampaignsItemid']),
strlen(Uri::base(true)) + 1
);
$campImg = Uri::root() . 'media/com_jgive/images/default_campaign.png';

if (!empty($displayData['image']))
{
	if ($displayData['params']['front_campaign_pin_view'] == 'media')
	{
		$campImg = $displayData['image']->media;
	}
	elseif($displayData['params']['front_campaign_pin_view'] == 'media_s')
	{
		$campImg = $displayData['image']->media_s;
	}
	elseif($displayData['params']['front_campaign_pin_view'] == 'media_l')
	{
		$campImg = $displayData['image']->media_l;
	}
	else
	{
		$campImg = $displayData['image']->media_m;
	}
}

$campaign->title = htmlspecialchars($campaign->title, ENT_COMPAT, 'UTF-8');
?>

<div class="pin border-gray af-bg-faded">
	<div class="pin_img border-b">
		<a class="af-d-block af-bg-center af-bg-cover af-bg-repn af-embed-responsive af-responsive-embed-16by9 h-auto" href="<?php echo $campaignDetailUrl;?>"
		title="<?php echo $campaign->title;?>"
		style="background-image:url('<?php echo $campImg;?>');">
		</a>
	</div>
	<?php
	if ($campaign->success_status == 1)
	{
	?>
	<div class="af-relative">
		<span class="pin__rubberStamp af-font-bold af-absolute pin__rubberStampText af-p-5 af-br-10">
			<?php echo Text::_('COM_JGIVE_SUCCESS_STAMP'); ?>
		</span>
	</div>
	<?php
	}?>
	<div class="pin__title af-p-10">
		<h5 class="text-uppercase af-mb-0 af-font-bold af-mt-5">
			<a class="campaign-title" href="<?php echo  $campaignDetailUrl;?>" title="<?php echo $campaign->title;?>">
				<?php echo $campaign->title;?>
				<?php
				if ($campaign->featured == 1)
				{
				?>
					<span><i class="fa fa-star pull-right" aria-hidden="true"></i></span>
				<?php
				}
				?>
			</a>
		</h5>
		<h6 class="af-my-5"><?php echo ($campaign->type == 'donation') ? Text::_('COM_JGIVE_DONATION') : Text::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT');?>
		<span>
			<a href="<?php echo  $campaignDetailUrl;?>" title="<?php echo $campaign->title;?>">
				<i class="fa fa-chevron-right pull-right" aria-hidden="true"></i>
			</a>
		</span>
		</h6>
	</div>
	<div class="af-px-15">
		<ul class="list-unstyled">
			<?php
				$donated_per = 0;

				$goal_amount = (float) $campaign->goal_amount;

				if (!empty($campaign->amount_received) && $goal_amount > 0)
				{
					$donated_per = ($campaign->amount_received / $campaign->goal_amount) * 100;
				}

				$donated_per = number_format((float) $donated_per, 2, '.', '');
			?>
			<li><h4 class="af-font-bold af-mb-10"><?php echo $donated_per;?>%</h4></li>
			<li>
				<div class="progress progress__info bs-none af-mb-10">
					<div class="progress-bar bs-none progress-bar-success  progress__info--color progress-bar-striped progress-bar-animated"
					role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="50" style="width: <?php echo $donated_per . '%';?>">
					</div>
				</div>
			</li>
			<li>
				<h6 class="af-mt-10">
				<?php
					// Calculate days left
						$date_expire = 0;
						$curr_date = Factory::getDate()->Format(Text::_('Y-m-d'));
						$end_date  = Factory::getDate($campaign->end_date)->Format(Text::_('Y-m-d'));

						if ($curr_date > $end_date)
						{
							$date_expire = 1;
						}

						$time_curr_date  = strtotime($curr_date);
						$time_end_date   = strtotime($campaign->end_date);
						$interval        = ($time_end_date - $time_curr_date);

						$days_left = floor($interval / (60 * 60 * 24));

						$days = '';
						$lable = Text::_('COM_JGIVE_DAYS_LEFT');

						if ((int) ($time_curr_date) && (int) ($time_end_date))
						{
							$days = Text::_('COM_JGIVE_DAYS_LEFT');
						}

						if ($date_expire)
						{
							$days = -1;
						}
						elseif((int) ($time_curr_date) && (int) ($time_end_date))
						{
							if ((int) $days_left == 0 )
							{
								// Only one day left
								$days = 1;
							}
							else
							{
								$days = $days_left > 0 ?  $days_left : -1;
							}
						}
				?>
				<?php
				if ($days === "No" || $days === "NA" || $days === -1)
				{
					echo Text::_('COM_JGIVE_CAMPAIGN_CLOSED');
				}
				else
				{
				?>
				<?php echo $days;?>&nbsp;
				<?php echo Text::_('COM_JGIVE_LAYOUT_DAYS'); ?>&nbsp;
				<?php echo Text::_('COM_JGIVE_DAYS__REMAINING');
				}?>
				</h6>
			</li>

			<li>
				<div class="clearfix"></div>
				<h4 class="af-font-bold af-mb-5"><?php echo $displayData['campaignDonorsCount'];?></h4></li>
			<li>
				<h6 class="text-capitalize af-mt-5">
					<?php echo ($campaign->type == 'donation') ? Text::_('COM_JGIVE_NO_OF_DONORS') : Text::_('COM_JGIVE_INVESTORS');?>
				</h6>
			</li>
		</ul>
		<div class="row af-mb-10">
			<div class="col-xs-12">
		<?php
			if ($campaign->donteButtonStatusFlag == 0)
			{
			?>
				<input type="button"
					class="btn btn-default btn-donate pull-right disabled w-100 fw-bold"
					value="<?php echo (($campaign->type == 'donation') ? Text::_('COM_JGIVE_DONATIONS_CLOSED') : Text::_('COM_JGIVE_INVESTMENTS_CLOSED'));?>"/>
			<?php
			}
			elseif ($campaign->donteButtonStatusFlag == -1)
			{
			?>
				<input type="button"
					class="btn btn-default btn-donate disabled pull-right"
					value="<?php echo Text::_("COM_JGIVE_WILL_START_SOON"); ?>"/>
			<?php
			}
			elseif($campaign->donteButtonStatusFlag == 1)
			{
			?>
				<form action="" method="post" name="donationform" id="donationform">
					<input type="hidden" name="cid" id="cid" value="<?php echo (int) $campaign->id;?>">
						<button type="submit" class="btn btn-primary btn-donate pull-right w-100 fw-bold" id="allcampdonate"
							title="<?php echo ($campaign->type == 'donation') ? Text::_('COM_JGIVE_BUTTON_DONATE_TOOLTIP') : Text::_('COM_JGIVE_BUTTON_INVEST_TOOPTIP');?>">
							<?php echo ($campaign->type == 'donation') ? Text::_('COM_JGIVE_BUTTON_DONATE') : Text::_('COM_JGIVE_BUTTON_INVEST');?>
						</button>
					<input type="hidden" name="option" value="com_jgive">
					<input type="hidden" name="task" value="donations.donate">
				</form>
			<?php
			}
			?>
			</div>
		</div>
	</div>
</div>
<div class="clearfix"></div>
