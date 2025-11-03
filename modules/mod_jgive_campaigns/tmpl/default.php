<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::stylesheet('modules/mod_jgive_campaigns/css/jgive_campaign.css');

if ($loadbootstrap)
{
	HTMLHelper::stylesheet('media/techjoomla_strapper/bs3/css/bootstrap.min.css');
}

// Load helpers
JLoader::import('CampaignHelper', JPATH_SITE . '/components/com_jgive/helpers');
JLoader::import('JgiveFrontendHelper', JPATH_SITE . '/components/com_jgive');

$jgiveFrontendHelper = new jgiveFrontendHelper;
$campaignHelper      = new campaignHelper;

$allCampaignsItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
$itemId             = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default');

if (empty($itemId))
{
	$itemId = $allCampaignsItemid;
}

if ($params->get('campaigns_sort_by') == 'amount_received' || $params->get('campaigns_sort_by') == 'remaining_amount')
{
	// JGive main helper function.
	$path = JPATH_SITE . '/components/com_jgive/helper.php';

	if (!class_exists('JgiveFrontendHelper'))
	{
		JLoader::register('JgiveFrontendHelper', $path);
		JLoader::load('JgiveFrontendHelper');
	}

	$jgiveFrontendHelper = new jgiveFrontendHelper;
	$campaignsData = $jgiveFrontendHelper->multiDimensionalSort($campaignsData, $params->get('campaigns_sort_by'), $orderby_dir);
}
?>

<div class="tjBs3 <?php echo $params->get('moduleclass_sfx'); ?>">
	<div class="row">
		<?php
		if (empty($campaignsData))
		{
			?>
				<div class="alert alert-warning">
					<?php echo Text::_('COM_JGIVE_NO_DATA_FOUND');?>
				</div>
			<?php
		}
		else
		{
			$arraycnt = count($campaignsData);
			$arraycnt < $count ? $count = $arraycnt:$count;

			for ($i = 0; $i < $count; $i++)
			{
				$campaignTitle = htmlspecialchars($campaignsData[$i]->title, ENT_COMPAT, 'UTF-8');

				if (strlen($campaignsData[$i]->title) > 20)
				{
					$campaignTitle = substr(htmlspecialchars($campaignsData[$i]->title, ENT_COMPAT, 'UTF-8'), 0, 15) . '...';
				}

				$featured = $campaignsData[$i]->featured ? $imgpath = '<img src="' . Uri::root() . 'media/com_jgive/images/featured.png"  style="display:inherit" title="' . $title . '">':'';
			?>
				<div class="mt-3 jgive_module_campaign">
					<div class="card-box shadow-sm">
						<div class="card-body mod_camp border-gray af-bg-faded  p-2 m-0  d-flex flex-column h-100">
							<?php
							if ($params->get('image'))
							{
								$campImg = Uri::root() . 'media/com_jgive/images/default_campaign.png';

								if (!empty($campaignsData[$i]->image))
								{
									$campImg = $campaignsData[$i]->image->media_m;
								}
							?>
								<div class="text-center mb-1 col-sm-12 col-md-12 col-lg-12">
									<a target="_blank"
									href="<?php echo Uri::root() . substr(
									Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . (int) $campaignsData[$i]->id . '&Itemid=' . $itemId),
									strlen(Uri::base(true)) + 1
									);?>" class="d-block">
										<div class="ratio ratio-16x9">
											<img class="img-fluid w-100" src="<?php echo $campImg; ?>" alt="Campaign Image">
										</div>
									</a>
								</div>

								<div class="card-header af-p-10">
									<?php $title = Text::_('MOD_JGIVE_FEATURED');?>
									<h5 class="card-title text-uppercase af-mb-0 af-font-bold af-mt-5">
										<a class="text-info"
										target="_blank"
										href="<?php echo Uri::root() . substr(Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $campaignsData[$i]->id . '&Itemid=' . $itemId), strlen(Uri::base(true)) + 1);?>">
											<b><?php echo $featured . ' ' . $campaignTitle;?></b>
										</a>
									</h5>
								</div>
							<?php
							}

							// Check if exeeding goal amount is allowed
							$flag = 0;

							if (($campaignsData[$i]->allow_exceed == 0 && ($campaignsData[$i]->amount_received >= $campaignsData[$i]->goal_amount)) || ($campaignsData[$i]->max_donors > 0 && ($campaignsData[$i]->donor_count >= $campaignsData[$i]->max_donors)))
							{
								$flag = 1;
							}

							/* If both start date, and end date are present
							The(int) typecasting is important*/
							if ((int) $campaignsData[$i]->start_date && (int) $campaignsData[$i]->end_date)
							{
								$campaignsData[$i]->start_date;
								$campaignsData[$i]->end_date;

								$start_date = Factory::getDate($campaignsData[$i]->start_date)->Format('Y-m-d H:i:s');
								$curr_date  = Factory::getDate()->Format('Y-m-d H:i:s');
								$end_date   = Factory::getDate($campaignsData[$i]->end_date)->Format('Y-m-d H:i:s');

								if ($curr_date > $end_date)
								{
									// Camp closed
									$flag = 0;
								}
								elseif ($curr_date < $start_date)
								{
									// Campaign Not yet started
									$flag = -1;
								}
								else
								{
									$flag = 1;
								}
							}

							// Calculate progress bar data
							$recPer = 0;
							$goal_amount = (float) $campaignsData[$i]->goal_amount;

							if (!empty($campaignsData[$i]->amount_received) && $goal_amount > 0)
							{
								$recPer = intval((100 * $campaignsData[$i]->amount_received) / $campaignsData[$i]->goal_amount);
							}

							$progresslabel = $recPer . '%';

							if ($recPer > 100)
							{
								$recPer = 100;
								$progresslabel = Text::_('MOD_JGIVE_MORE_THAN_HUNDRED') . ' %';
							}
							?>
							<div class="col-sm-12 col-md-12 col-lg-12 mt-1">
								<div class="progress mb-2" >
									<div class="progress-bar bg-primary" style="width:<?php echo $recPer;?>%;min-width: 2em;">
										<!-- <b class="com_jgive_progress_text text-light"><?php echo $progresslabel;?></b> -->
									</div>
								</div>
							</div>
							<?php

							if ($campaignsData[$i]->success_status == 1)
							{
							?>
								<div class="mod_rubber_stamp">
									<span class="mod_campaign_rubber_stamp_text">
										<?php echo Text::_('COM_JGIVE_SUCCESS_STAMP'); ?>
									</span>
								</div>
							<?php
							}

							if ($params->get('show_goal_remaining') != 0 || $params->get('show_received'))
							{
							?>
								<table class="table table-sm table-hover">
								<?php
									// Conditions added by Sneha
									if ($params->get('show_goal_remaining'))
									{
									?>
										<tr>
											<td class="af-text-start fw-bold">
												<?php echo Text::_('MOD_JGIVE_GOAL_AMOUNT');?>
											</td>
											<td class="af-text-center text-info fw-bold">
												<?php
												echo $jgiveFrontendHelper->getFormattedPrice($campaignsData[$i]->goal_amount);
												?>
											</td>
										</tr>
									<?php
									}

									if ($params->get('show_received'))
									{
									?>
										<tr>
											<td class="af-text-start fw-bold">
												<?php echo Text::_('MOD_JGIVE_RECEIVED_AMOUNT');?>
											</td>
											<td class="af-text-center text-info fw-bold">
												<?php echo $jgiveFrontendHelper->getFormattedPrice($campaignsData[$i]->amount_received);
												?>
											</td>
										</tr>
									<?php
									}

									if ($params->get('show_goal_remaining'))
									{
									?>
										<tr>
											<td class="af-text-start fw-bold">
												<?php echo Text::_('MOD_JGIVE_REMAINING_AMOUNT');?>
											</td>
											<td class="af-text-center text-info fw-bold">
												<?php
													if ($campaignsData[$i]->amount_received > $campaignsData[$i]->goal_amount)
													{
														echo Text::_('COM_JGIVE_GOAL_ACHIEVED');
													}
													else
													{
														echo $jgiveFrontendHelper->getFormattedPrice($campaignsData[$i]->remaining_amount);
													}?>
											</td>
										</tr>
								<?php
									}?>
								</table>
							<?php
							}?>

							<div class="text-center">
								<?php
								if ($flag == 1)
								{
								?>
									<form action="" method="post" name="campaignForm" id="campaignForm" class="w-100">
										<input type="hidden" name="cid" value="<?php echo $campaignsData[$i]->id;?>">
										<button class="btn btn-sm jgive-mt-1 btn-primary w-100 p-1 fw-bold" type="submit">
											<?php $campaignsData[$i]->type == "donation" ? $donate = Text::_('MOD_JGIVE_DONATE') :$donate = Text::_('MOD_JGIVE_INVEST');
											echo $donate;	?>
										</button>
										<input type="hidden" name="option" value="com_jgive">
										<input type="hidden" name="task" value="donations.donate">
									</form>
								<?php
								}
								elseif ($flag == -1)
								{
								?>
									<input type="button" class="btn btn-sm jgive-mt-1 fw-bold disabled w-100" value="<?php echo Text::_('COM_JGIVE_WILL_START_SOON');?>" />
								<?php
								}
								elseif ($flag == 0)
								{
								?>
									<input type="button" class=" btn btn-sm jgive-mt-1 p-1 fw-bold disabled w-100" value="<?php echo Text::_('MOD_JGIVE_DONATIONS_CLOSED');?>" />
								<?php
								}?>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
			<?php
			}
		}?>
	</div>
	<?php
	if (!$params->get('show_related_campaigns'))
	{
    ?>
    <div class="row">
        <div class="pull-right col-sm-6 col-md-6 col-lg-6">
            <a href="<?php echo Uri::root() . substr(
            Route::_('index.php?option=com_jgive&view=campaigns&layout=all&Itemid=' . $allCampaignsItemid),
			strlen(Uri::base(true)) + 1
            ); ?>">
            <b><?php echo Text::_('MOD_JGIVE_CAMPAIGNS_ALL_PROJECT'); ?></b>
            </a>
        </div>
		<div class="clearfix"></div>
    </div>
    <?php
	}
	?>
</div>
