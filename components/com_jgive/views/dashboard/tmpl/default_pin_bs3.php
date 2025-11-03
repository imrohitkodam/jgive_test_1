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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$jgiveFrontendHelper = new JgiveFrontendHelper;

foreach ($this->promoterDashboardData['myCampData'] as $campData)
{
?>
	<div class="col-xs-12 col-sm-4 col-md-3">
		<div class="thumbnail af-bg-faded pin p-0">
				<div class="pin__title af-p-5 af-mt-10">
					<h6 class="text-uppercase af-mb-0 af-px-15 af-mt-5 af-text-truncate">
						<?php
							$campaignDetailUrl = Uri::root() . substr(
								Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $campData['id'] . '&Itemid=' .
									$this->promoterDashboardData['otherData']->singleCampItemid
								),
								strlen(Uri::base(true)) + 1
							);
						?>
						<?php
						if ($campData['published'] == '1')
						{
							if (strlen($campData['title']) > 25)
							{?>
								<a href="<?php echo  $campaignDetailUrl;?>" title="<?php echo $campData['title'];?>">
									<strong><?php echo strtoupper(substr($campData['title'], 0, 25) . '...');?></strong>
								</a>
							<?php
							}
							else
							{?>
								<a href="<?php echo  $campaignDetailUrl;?>" title="<?php echo $campData['title'];?>">
									<strong><?php echo strtoupper($campData['title']);?></strong>
								</a>
							<?php
							}
						}
						else
						{
							if (strlen($campData['title']) > 25)
							{?>
								<strong><?php echo strtoupper(substr($campData['title'], 0, 25) . '...');?></strong>
							<?php
							}
							else
							{?>
								<strong><?php echo strtoupper($campData['title']);?></strong>
							<?php
							}
						}?>

						<?php
						if ($campData['featured'])
						{
						?>
							<span><i class="fa fa-star pull-right" aria-hidden="true"></i></span>
						<?php
						}
						?>
					</h6>
						<h6 class="text-uppercase text-aqua af-mb-5 af-px-15">
							<?php
								if ($campData['published'] == 1)
								{
								?>
									<i class="fa fa-check" aria-hidden="true"></i>
								<?php
									echo Text::_('COM_JGIVE_VENDOR_CAMPAIGN_PUBLISHED_STATUS') .
									Factory::getDate($campData['start_date'])->Format(Text::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
								}
								else
								{
								?>
									<i class="fa fa-floppy-o" aria-hidden="true"></i>
								<?php
									echo Text::_('COM_JGIVE_VENDOR_CAMPAIGN_UNPUBLISHED_STATUS');
								}
							?>
							<span>
								<a href="<?php echo  $campaignDetailUrl;?>"
								title="<?php echo $campData['title'];?>">
									<i class="fa fa-chevron-right pull-right af-mt-15" aria-hidden="true"></i>
								</a>
							</span>
						</h6>
						<h6 class="col-xs-offset-1 af-mt-0 af-px-10 af-mb-5">
							<?php echo Text::_('COM_JGIVE_CAMP_TYPE') . ':';?>
							<?php echo ($campData['type'] == 'donation') ? Text::_('COM_JGIVE_DONATION') : Text::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT');?>
						</h6>
				</div>
				<div class="clearfix"></div>
				<div class="pin__info af-px-15 af-mb-10">
					<?php
						// Calculate days left
							$date_expire = 0;
							$curr_date = Factory::getDate()->Format(Text::_('Y-m-d'));
							$end_date  = Factory::getDate($campData['end_date'])->Format(Text::_('Y-m-d'));

							if ($curr_date > $end_date)
							{
								$date_expire = 1;
							}

							$time_curr_date  = strtotime($curr_date);
							$time_end_date   = strtotime($campData['end_date']);
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
							elseif ((int) ($time_curr_date) && (int) ($time_end_date))
							{
								if ((int) $days_left == 0)
								{
									// Only one day left
									$days = 1;
								}
								else
								{
									$days = $days_left > 0 ?  $days_left: -1;
								}
							}
						?>
					<h6 class="col-xs-offset-1">
						<?php
						if ($days === "No" || $days === -1)
						{
							echo Text::_('COM_JGIVE_CAMPAIGN_CLOSED');
						}
						else
						{
						?>
							<?php echo $days;?>&nbsp;
							<?php echo Text::_('COM_JGIVE_LAYOUT_DAYS'); ?>&nbsp;
							<?php echo Text::_('COM_JGIVE_DAYS__REMAINING');
						}
						?>
					</h6>
					<?php
					// Calculate days left
						$date_expire = 0;
						$curr_date = Factory::getDate()->Format(Text::_('Y-m-d'));
						$end_date  = Factory::getDate($campData['end_date'])->Format(Text::_('Y-m-d'));

						if ($curr_date > $end_date)
						{
							$date_expire = 1;
						}

						$time_curr_date  = strtotime($curr_date);
						$time_end_date   = strtotime($campData['end_date']);
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
							$days = Text::_('COM_JGIVE_NA');
						}
						elseif ((int) ($time_curr_date) && (int) ($time_end_date))
						{
							if ((int) $days_left == 0)
							{
								// Only one day left
								$days = 1;
							}
							else
							{
								$days = $days_left > 0 ?  $days_left: Text::_('COM_JGIVE_NA');
							}
						}
					?>
					<div class="clearfix"></div>
					<!-- Total Donation-->

						<h4 class="col-xs-offset-1 af-mb-10">
							<strong>
								<?php echo $jgiveFrontendHelper->getFormattedPrice($campData['amount_received']) . ' / ' . $jgiveFrontendHelper->getFormattedPrice($campData['goal_amount']);?>
							</strong>
						</h4>

						<?php
							$donated_per = 0;

							$goal_amount = (float) $campData['goal_amount'];

							if (!empty($campData['amount_received']) && $goal_amount > 0)
							{
								$donated_per = ($campData['amount_received'] / $campData['goal_amount']) * 100;
							}

							$donated_per = number_format((float) $donated_per, 2, '.', '');
						?>

						<div class="progress af-mb-0 col-xs-offset-1 progress__info">
							<div class="progress-bar progress-bar-success progress__info--color" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="50" style="width: <?php echo $donated_per . '%';?>">
							</div>
						</div>
						<h6 class="col-xs-offset-1 af-mt-10">
							<?php echo ($campData['type'] == 'donation') ? Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_TOTAL_DONATION') . ' / ' . Text::_('COM_JGIVE_GOAL_AMOUNT'): Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_TOTAL_INVESTMENT') . ' / ' . Text::_('COM_JGIVE_GOAL_AMOUNT');?>
						</h6>
					<div class="clearfix"></div>
					<!--Average Donation-->

						<?php
							$jgiveModelDonorsObj = new JgiveModelDonors;
							$campaignDonorsCount = $jgiveModelDonorsObj->getDonorsPerCamp($campData['id']);

							// Average Donation or Investment
							if ($campaignDonorsCount > 0)
							{
								$avg_amount = $campData['amount_received'] / $campaignDonorsCount;
							}
							else
							{
								$avg_amount = 0;
							}
						?>

						<h4 class="col-xs-offset-1 af-mb-5"><strong><?php echo $jgiveFrontendHelper->getFormattedPrice($avg_amount);?></strong></h4>
						<h6 class="col-xs-offset-1 af-mt-5">
							<?php echo ($campData['type'] == 'donation') ? Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_AVERAGE_DONATION') : Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_AVERAGE_INVESTMENT');?>
						</h6>

						<!-- No. of Donors-->
						<div class="clearfix"></div>
							<h4 class="col-xs-offset-1 af-mb-5"><strong><?php echo $campaignDonorsCount;?></strong></h4>
							<h6 class="col-xs-offset-1 af-mt-5"><?php echo ($campData['type'] == 'donation') ? Text::_('COM_JGIVE_DONORS') : Text::_('COM_JGIVE_INVESTORS');?></h6>
						<div class="clearfix"></div>
					</div>
			</div>
	</div>
<?php
}
