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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

HTMLHelper::_('bootstrap.renderModal', 'a.modal');

// Creating Object of FrontendHelper class
$jgiveFrontendHelper = new jgiveFrontendHelper;
$campaignHelper = new campaignHelper;

// Load Chart Javascript Files.
HTMLHelper::script('media/com_jgive/vendors/js/Chart.min.js');

echo '<div id="fb-root"></div>';
	$fblike_tweet = HTMLHelper::script('media/com_jgive/javascript/fblike.js');
echo "<script type='text/javascript' src='" . $fblike_tweet . "'></script>";

$browserbar_title = htmlspecialchars($this->cdata['campaign']->title, ENT_COMPAT, 'UTF-8');
$document         = Factory::getDocument();
$document->setTitle($browserbar_title);

// Jomsocial toolbar
if (isset($this->jomsocialToolbarHtml))
{
	echo $this->jomsocialToolbarHtml;
}
?>

<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<!--Campaign Information-->
		<div class="row af-py-15 campaignMain border-b af-bg-faded">
			<div class="col-xs-12 af-mb-15 campaignBack">
				<?php
				if ($this->cdata['campaign']->creator_id == $this->cdata['otherData']->loggedUserId && $this->cdata['otherData']->canEdit)
				{
					$dashboardUrl = Route::_('index.php?option=com_jgive&view=dashboard&Itemid=' . $this->cdata['otherData']->dashboardCampItemid);
				?>
					<a href="<?php echo $dashboardUrl;?>" class="text-muted af-font-bold">
						<i class="fa fa-angle-left af-pr-5" aria-hidden="true"></i>
						<span><?php echo Text::_('COM_JGIVE_SINGLE_BACKE_TO_DASHBOARD');?></span>
					</a>
				<?php
				}
				else
				{
					$allCampUrl = Uri::root() . substr(
					Route::_('index.php?option=com_jgive&view=campaigns&layout=all&Itemid=' . $this->cdata['otherData']->allCampItemid),
					strlen(Uri::base(true)) + 1
					);
				?>
					<a href="<?php echo $allCampUrl;?>" class="text-muted af-font-bold">
						<i class="fa fa-angle-left af-pr-5 af-font-bold" aria-hidden="true"></i>
						<span><?php echo Text::_('COM_JGIVE_SINGLE_BACKE_TO_ALL_CAMP');?></span>
					</a>
				<?php
				}?>
			</div>
			<div class="col-xs-12 col-sm-6">
				<h1 class="af-mt-0 af-font-bold fs-title"><strong><?php echo htmlspecialchars($this->cdata['campaign']->title, ENT_COMPAT, 'UTF-8'); ?></strong></h1>
				<div class="row">
					<div class="col-xs-12 col-sm-11 af-mb-15">
					<?php

					if ($this->cdata['campaign']->creator_id == $this->cdata['otherData']->loggedUserId && $this->cdata['otherData']->canEdit)
					{
					?>
					<div class="row af-mb-15">
						<div class="col-xs-6 af-mt-10">
							<?php

								$goal_amount = $jgiveFrontendHelper->getFormattedPrice($this->cdata['campaign']->goal_amount);
								echo Text::_('COM_JGIVE_GOAL_AMOUNT') . ':'?><?php echo $goal_amount;
							?>
						</div>
						<div class="col-xs-6">
							<select id='campaigns_graph_period' class="pull-right af-br-0 form-select w-auto p-1">
								<option value = '0'><?php echo Text::_('COM_JGIVE_FILTER_LATEST');?></option>
								<option value = '1'><?php echo Text::_('COM_JGIVE_FILTER_LAST_MONTH');?></option>
								<option value = '2'><?php echo Text::_('COM_JGIVE_FILTER_LAST_YEAR');?></option>
							</select>
						</div>
					</div>
						<canvas id="mycampaign_graph"></canvas>
					<?php
					}
					else
					{
					?>
						<div class="campaignImg">
							<?php
							if ($this->cdata['campaign']->video_on_details_page == 1 && !empty ($this->cdata['campaign']->videoPlgParams))
							{
								$videoPlgParams = $this->cdata['campaign']->videoPlgParams;

								if (array_key_exists('type', $videoPlgParams) && ($videoPlgParams['type'] == 'youtube' || $videoPlgParams['type'] == 'vimeo'))
								{
									?>
									<iframe width="100%" height="320" src="<?php echo $videoPlgParams['file'];?>"></iframe>
								<?php
								}
								elseif ($videoPlgParams['plugin'] == 'image')
								{
									$defaultImageUrl = $videoPlgParams[$this->cdata['params']['front_campaign_detail_view']];

									echo "<img class='img-responsive' style='' src='" . $defaultImageUrl . "'/>";
								}
								else
								{
								?>
									<video width="100%" height="320" controls autoplay>
  										<source src="<?php echo $videoPlgParams['file'];?>" type="<?php echo "video/" . $videoPlgParams['type'];?>">
									</video>
									<?php
								}
							}
							else
							{
								$campImg = Uri::root() . 'media/com_jgive/images/default_campaign.png';

								if (!empty($this->cdata['campaign']->image))
								{
									if ($this->cdata['params']['front_campaign_detail_view'] == 'media')
									{
										$campImg = $this->cdata['campaign']->image['media'];
									}
									elseif($this->cdata['params']['front_campaign_detail_view'] == 'media_s')
									{
										$campImg = $this->cdata['campaign']->image['media_s'];
									}
									elseif($this->cdata['params']['front_campaign_detail_view'] == 'media_l')
									{
										$campImg = $this->cdata['campaign']->image['media_l'];
									}
									else
									{
										$campImg = $this->cdata['campaign']->image['media_l'];
									}
								}

								echo "<img class='img-responsive' style='' src='" . $campImg . "'/>";
							}
							?>
						</div>
					<?php
					}
					?>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6">
				<div class="row">
					<div class="col-xs-12">
					<?php
						if ($this->cdata['campaign']->creator_id == $this->cdata['otherData']->loggedUserId && $this->cdata['otherData']->canEdit)
						{
						?>
							<div class="col-xs-12 pull-right d-flex justify-content-end mt-3">
								<?php $url = Uri::root() . substr(Route::_('index.php?option=com_jgive&view=campaignform&layout=default&id=' . $this->cdata['campaign']->id . '&Itemid=' .
								$this->cdata['otherData']->createCampItemid), strlen(Uri::base(true)) + 1);?>
								<a id="donation-edit" class="pull-right btn btn-info btn-sm fw-bold d-flex align-items-center" href="<?php echo $url?>">
									<i class="fa fa-pencil" aria-hidden="true"></i>
									<?php echo Text::_('COM_JGIVE_EDIT_CAMPAIGN_HEADER');?>
								</a>
							</div>
					<?php
						}
						else
						{
							if ($this->cdata['campaign']->donateBtnShowStatus == 0 || $this->cdata['campaign']->donateBtnShowStatus == 1)
							{
							?>
								<div class="row campaignDonar af-mb-25">
									<div class="col-xs-6 col-md-2">
										<h3 class="my-0"><strong><?php echo htmlspecialchars($this->cdata['campaign']->totalNoOfDonors, ENT_COMPAT, 'UTF-8'); ?></strong></h3>
										<small class="text-muted af-font-bold">
											<?php echo ($this->cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_NO_OF_DONORS') : Text::_('COM_JGIVE_INVESTORS');?>
										</small>
									</div>
									<div class="col-xs-6 col-md-4">
										<h3 class="my-0"><strong><?php echo $jgiveFrontendHelper->getFormattedPrice($this->cdata['campaign']->amount_received);?></strong></h3>
										<small class="text-muted af-font-bold">
											<?php echo ($this->cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_SINGLE_DONATED_SO_FAR') : Text::_('COM_JGIVE_SINGLE_INVESTED_SO_FAR');?>
										</small>
									</div>

									<div class="col-xs-6 col-md-2">
										<h3 class="my-0"><strong><?php echo $this->cdata['campaign']->goneDays;?></strong></h3>
										<small class="text-muted af-font-bold">
											<?php echo Text::_('COM_JGIVE_SINGLE_DAYS_TO_GO');?>
										</small>
									</div>
									<div class="col-xs-6 col-md-4">
										<h3 class="my-0"><strong><?php echo $jgiveFrontendHelper->getFormattedPrice($this->cdata['campaign']->goal_amount);?></strong></h3>
										<small class="text-muted af-font-bold">
											<?php echo Text::_('COM_JGIVE_GOAL_AMOUNT_PH');?>
										</small>
									</div>
								</div>
						<?php
							}?>
					<?php
						}
					?>
					</div>
					
					<div class="ticketBookBtn d-flex gap-2 mt-4">
						<?php
							$btn_block = '';
							$class = '';
							$close_btn_style = '';

							if ($this->cdata['campaign']->donateBtnShowStatus == 0)
							{
							?>
								<div class="col-xs-12 col-sm-5 col-md-4">
									<input type="button"
									class="btn btn-basic btn-donate btn-donate-90 af-br-0 btn-md disabled af-font-bold text-uppercase <?php echo $btn_block; ?>"
									style="<?php echo $close_btn_style; ?>"
									value="<?php echo (($this->cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_DONATIONS_CLOSED') : Text::_('COM_JGIVE_INVESTMENTS_CLOSED'));?>"
									/>
								</div>
							<?php
							}
							elseif ($this->cdata['campaign']->donateBtnShowStatus == -1)
							{
							?>
								<div class="col-xs-12 col-sm-5 col-md-4">
									<input type="button"
									class="btn btn-basic btn-md btn-donate btn-donate-90 af-br-0 disabled af-font-bold text-uppercase <?php echo $btn_block; ?>"
									style="<?php echo $close_btn_style; ?>"
									value="<?php echo Text::_("COM_JGIVE_WILL_START_SOON"); ?>"/>
								</div>
							<?php
							}
							elseif($this->cdata['campaign']->donateBtnShowStatus == 1)
							{
							?>
								<div class="ccol-xs-12 col-sm-5 col-md-4 ">
									<!--Working Campaign -->
									<form action="" method="post" name="donationform" id="donationform">
										<input type="hidden" name="cid" id="cid" value="<?php echo $this->cdata['campaign']->id;?>">

										<button type="submit" class="btn btn-primary fw-bold w-100" id="donate-now "
											title="<?php echo ($this->cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_BUTTON_DONATE_TOOLTIP') : Text::_('COM_JGIVE_BUTTON_INVEST_TOOPTIP');?>">
											<?php echo strtoupper(($this->cdata['campaign']->type == 'donation') ? Text::_('COM_JGIVE_BUTTON_DONATE') : Text::_('COM_JGIVE_BUTTON_INVEST'));?>
										</button>
										<input type="hidden" name="option" value="com_jgive">
										<input type="hidden" name="task" value="donations.donate">
										<?php echo HTMLHelper::_('form.token'); ?>
									</form>
								</div>
							<?php
							}

						if ($this->enableReports
							&& $this->cdata['campaign']->creator_id == $this->cdata['otherData']->loggedUserId
							&& $this->cdata['otherData']->canEdit)
						{
						?>
							<div class="col-xs-12 col-sm-5 col-md-4 af-mt-8">
								<form action="" method="post" name="reportform" id="reportform">
									<button type="submit" class="btn btn-primary fw-bold w-100 "
									title="<?php echo Text::_('COM_JGIVE_BUTTON_CREATE_REPORT_TITLE'); ?>" >
										<?php echo strtoupper(Text::_('COM_JGIVE_BUTTON_CREATE_REPORT')); ?>
									</button>
									<input type="hidden" name="cid" id="cid" value="<?php echo $this->cdata['campaign']->id;?>">
									<input type="hidden" name="option" value="com_jgive">
									<input type="hidden" name="task" value="reportform.add">
									<?php echo HTMLHelper::_('form.token'); ?>
								</form>
							</div>
						<?php
						}
						?>
					</div>
				</div>

				<div class="row af-mt-10">
					<?php
					// Generate unique ad url for social sharing
					require_once JPATH_SITE . "/components/com_jgive/helpers/integrations.php";

					$campaign_link = 'index.php?option=com_jgive&view=campaign&layout=default&id=' . $this->cdata['campaign']->id;
					$campaign_link = Uri::root() . substr(Route::_($campaign_link), strlen(Uri::base(true)) + 1);
					$add_this_share = '';
					$pid = $this->cdata['params']['addthis_publishid'];

					if (file_exists(JPATH_SITE . '/' . 'components/com_jlike/helper.php') || File::exists(JPATH_ROOT . '/components/com_invitex/invitex.php'))
					{
						// Check for component
						if (File::exists(JPATH_ROOT . '/components/com_invitex/invitex.php'))
						{
							if (ComponentHelper::isEnabled('com_invitex', true))
							{
								if (PluginHelper::isEnabled('system', 'jgive_invitex_email'))
								{
									$helperPath = JPATH_SITE . '/components/com_invitex/helper.php';

									if (!class_exists('cominvitexHelper'))
									{
										JLoader::register('cominvitexHelper', $helperPath);
										JLoader::load('cominvitexHelper');
									}

									$cominvitexHelper = new cominvitexHelper;
									$invite_type      = $cominvitexHelper->geTypeId_By_InernalName('jgive_email');

									$invite_url = 'index.php?option=com_jgive&view=campaign&layout=default&id=' . $this->cdata['campaign']->id . '&Itemid=' .
									$this->cdata['otherData']->allCampItemid;

									$invite_url = Uri::root() . substr(Route::_($invite_url), strlen(Uri::base(true)) + 1);
									$invite_url = urlencode(base64_encode($invite_url));

									$link = Uri::root(true) . '/index.php?option=com_invitex&view=invites&catch_act=&invite_type=' .
									$invite_type . '&invite_url=' . $invite_url . '&invite_anywhere=1&tag=[name=' . $this->cdata['campaign']->title . '|cid=' .
									$this->cdata['campaign']->id . ']';

									$linkAfterRoute = Uri::root() . substr(Route::_($link), strlen(Uri::base(true)) + 1);
									?>
										<div class="col-xs-3 col-sm-3">
											<a type="button" class="button subbutton btn btn-primary btn-md" href ="<?php echo $linkAfterRoute; ?>" target="_self" rel="" name="invite_anywhere">
												<i class="fa fa-envelope"></i>
												<?php echo Text::_('COM_JGIVE_INVITE_PEOPLE_TO_DONATE'); ?>
											</a>
										</div>
									<?php
								}
							}
						}

						if (file_exists(JPATH_SITE . '/' . 'components/com_jlike/helper.php'))
						{
							$show_comments = -1;
							$show_like_buttons = 1;

							// Update campaign url for action log
							$campaign_url = $jgiveFrontendHelper->getCampaignUrl($this->cdata['campaign']->id, true, false);

							$jlikehtml = $jgiveFrontendHelper->DisplayjlikeButton(
							$campaign_url, $this->cdata['campaign']->id, $this->cdata['campaign']->title, $show_comments, $show_like_buttons
							);

							if ($jlikehtml != null)
							{
							?>
								<div class="col-xs-4 col-sm-6">
									<?php echo $jlikehtml;?>
								</div>
							<?php
							}
						}
					}
					?>
				</div>
				<?php
				if ($this->cdata['campaign']->creator_id == $this->cdata['otherData']->loggedUserId && $this->cdata['otherData']->canEdit)
				{
				?>
					<p class="text-uppercase af-font-bold af-mt-10">
						<?php
						$type = ($this->cdata['campaign']->type == 'donation') ? strtoupper(Text::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION')) : strtoupper(Text::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT'));
						echo '<b>' . strtoupper(Text::_("COM_JGIVE_CAMP_TYPE")) . ": " . $type . '</b>' . " | ";

						if ($this->cdata['campaign']->donateBtnShowStatus == 0)
						{
							echo Text::_("COM_JGIVE_DONATIONS_CLOSED");
						}
						elseif ($this->cdata['campaign']->donateBtnShowStatus == -1)
						{
							echo Text::_("COM_JGIVE_WILL_START_SOON");
						}
						elseif ($this->cdata['campaign']->donateBtnShowStatus == 1)
						{
							echo htmlspecialchars($this->cdata['campaign']->goneDays, ENT_COMPAT, 'UTF-8') . " " . Text::_("COM_JGIVE_SINGLE_DAYS_TO_GO");
						}
						?>
					</p>
				<?php
				}
				else
				{
				?>
					<p class="text-uppercase af-font-bold af-mt-10">
						<?php
							if ($this->cdata['campaign']->org_ind_type == 'non_profit')
							{
								$org_ind_type = Text::_('COM_JGIVE_ORG_NON_PROFIT');
							}
							elseif ($this->cdata['campaign']->org_ind_type == 'self_help')
							{
								$org_ind_type = Text::_('COM_JGIVE_SELF_HELP');
							}
							else
							{
								$org_ind_type = Text::_('COM_JGIVE_SELF_INDIVIDUALS');
							}

							$type = ($this->cdata['campaign']->type == 'donation') ? strtoupper(Text::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION')) : strtoupper(Text::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT'));
						?>
						<?php echo $type . ' ';?> |
						<?php echo ' ' . strtoupper(htmlspecialchars($this->cdata['campaign']->catname, ENT_COMPAT, 'UTF-8')) . '</b>' . ' '; ?> |
						<?php echo ' ' . '<b>' . strtoupper(htmlspecialchars($org_ind_type, ENT_COMPAT, 'UTF-8')) . '</b>' . ' '; ?>
					</p>
				<?php
				}
				?>
				<!--Short and Long description display here-->
				<p class="text-justify text-muted af-mb-20">
					<?php
						$long_desc_char = $this->cdata['params']['pin_short_desc_char'] ? $this->cdata['params']['pin_short_desc_char'] : 500;

						echo $this->cdata['campaign']->short_description . "</br>";

						if (strlen($this->cdata['campaign']->long_description) > $long_desc_char)
						{
							echo substr(strip_tags($this->cdata['campaign']->long_description, '<a>'), 0, $long_desc_char);?>
							<a href="#myModal" data-toggle="modal" data-target="#myModal">
								<?php echo Text::_('COM_JGIVE_CAMPAIGN_READ_MORE');?>
							</a>
						<?php
						}
						else
						{
							echo $this->cdata['campaign']->long_description;
						}
					?>
				</p>
				<div class="modal center fade" id="myModal" role="dialog">
					<div class="modal-dialog">
						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-body height-400 overflow-y-auto af-m-10">
								<p><?php echo htmlspecialchars($this->cdata['campaign']->short_description, ENT_COMPAT, 'UTF-8');?></p>
								<p><?php echo $this->cdata['campaign']->long_description;?></p>
							</div>
							<div class="modal-footer">
								<button type="button" id="campaignCloseModalbtn" class="btn btn-default" data-dismiss="modal">
									<?php echo Text::_('COM_JGIVE_BTN_CLOSE');?></button>
							</div>
						</div>
					</div>
				</div>

				<!--Promoter Details-->
				<h5 class="text-uppercase"><strong><?php echo strtoupper(Text::_('COM_JGIVE_CAMPAIGN_PROMOTER'));?></strong></h5>
				<div class="campaignPromoter">
					<?php
					$promoterAvatar = ($this->cdata['campaign']->creator_avatar) ? $this->cdata['campaign']->creator_avatar : Uri::root(true) . '/media/com_jgive/images/default_avatar.png';
					?>

					<div class="col-xs-2 campaignPromoter__img p-0 center">
						<img src="<?php echo $promoterAvatar; ?>" class="img-circle" width="60px" alt="<?php echo Text::_("COM_JGIVE_PROMOTOR_AVATAR")?>">
					</div>
					<div class="col-xs-10 text-muted af-pl-10 campaignPromoter__info">
						<?php
						echo htmlspecialchars($this->cdata['campaign']->vendor_title, ENT_COMPAT, 'UTF-8') . '<br>';
						?>
					</div>
				</div>
			</div>
		</div>
	<!--Campaign Information end-->
	<?php

	if ($this->cdata['params']['social_sharing'])
	{
	?>
		<div class="row af-my-10">
			<div class="col-xs-12 af-pt-10 socialIcon">
				<?php
				// Set metadata
				$config              = Factory::getConfig();
				$site_name           = $config->get('sitename');
				$campaignMainImgPath = Uri::root() . 'media/com_jgive/images/default_campaign.png';

				if (!empty($this->cdata['campaign']->image))
				{
					$campaignMainImgPath = $this->cdata['campaign']->image['media_l'];
				}

				$campaignTitle       = htmlspecialchars($this->cdata['campaign']->title, ENT_COMPAT, 'UTF-8');
				$campaignDescription = substr(strip_tags($this->cdata['campaign']->long_description), 0, $long_desc_char);

				$document->addCustomTag('<meta property="og:title"       content="' . $campaignTitle . '" />');
				$document->addCustomTag('<meta property="og:image"       content="' . $campaignMainImgPath . '" />');
				$document->addCustomTag('<meta property="og:image:height" content="300" />');
				$document->addCustomTag('<meta property="og:image:width"  content="300" />');
				$document->addCustomTag('<meta property="og:url"         content="' . $campaign_link . '" />');
				$document->addCustomTag('<meta property="og:description" content="' . $campaignDescription . '" />');
				$document->addCustomTag('<meta property="og:site_name"   content="' . $site_name . '" />');
				$document->addCustomTag('<meta property="og:type"        content="website" />');

				if ($this->cdata['params']['social_shring_type'] == 'addthis')
				{
					$add_this_share = '
					<!-- AddThis Button BEGIN -->
					<div class="addthis_toolbox addthis_default_style">

					<a class="addthis_button_facebook_like" fb:like:layout="button_count" class="addthis_button" addthis:url="' . $campaign_link . '"></a>

					<a class="addthis_button_google_plusone" g:plusone:size="medium" class="addthis_button" addthis:url="' . $campaign_link . '"></a>

					<a class="addthis_button_tweet" class="addthis_button" addthis:url="' . $campaign_link . '"></a>

					<a class="addthis_button_pinterest_pinit" class="addthis_button" addthis:url="' . $campaign_link . '"></a>

					<a class="addthis_counter addthis_pill_style" class="addthis_button" addthis:url="' . $campaign_link . '"></a>
					</div>

					<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid="' . $pid . '"></script>
					<!-- AddThis Button END -->';

					$add_this_js = 'https://s7.addthis.com/js/300/addthis_widget.js';
					$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
					$JgiveIntegrationsHelper->loadScriptOnce($add_this_js);

					// Output all social sharing buttons
					echo' <div id="rr" style=""><div class="social_share_container"><div class="social_share_container_inner">' . $add_this_share . '</div></div></div>';
				}
				elseif ($this->cdata['params']['social_shring_type'] == 'addtoany')
				{
					$addToAnyShare = '<div class="a2a_kit a2a_kit_size_32 a2a_default_style">';

					if ($this->cdata['params']['addtoany_universal_button'] == 'before')
					{
						$addToAnyShare .= '<a class="a2a_dd" href="https://www.addtoany.com/share"></a>';
					}

					$addToAnyShare .= $this->cdata['params']['addtoany_share_buttons'];

					if ($this->cdata['params']['addtoany_universal_button'] == 'after')
					{
						$addToAnyShare .= '<a class="a2a_dd" href="https://www.addtoany.com/share"></a>';
					}

					$addToAnyShare .= '</div>';

					$addToAnyShare .= '<script async src="https://static.addtoany.com/menu/page.js"></script>';

					/* Output all social sharing buttons */
					echo' <div id="rr" style="">
							<div class="social_share_container">
								<div class="social_share_container_inner">' .
									$addToAnyShare .
								'</div>
							</div>
						</div>';
				}
				else
				{
					echo '<div class="af-d-flex">';
					echo '<div class="fb-like af-pr-5" data-href="' . $campaign_link . '" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true">
						 </div>';
					echo '<div class="g-plus" data-action="share" data-annotation="bubble" data-href="' . $campaign_link . '">
						</div>';
					echo '<div class="af-pr-5">
							<a href="https://twitter.com/share" class="twitter-share-button"  data-url="' . $campaign_link . '" data-counturl="' . $campaign_link . '">Tweet</a>
						</div>';
					echo '</div>
						<div class="clearfix"></div>';
				}
				?>
			</div>
		</div>
	<?php
	}?>
	<!--Tab -->
	<div class="row">
		<div class="col-md-8">
			<div class="row campaignTab border-t border-b af-pt-10">
				<div class="col-xs-12">
					<ul id="myTab" class="nav nav-tabs text-uppercase campaignTab__ul launchForm__nav af-d-flex">
						<?php
						if ($this->cdata['params']['campaign_activity'] == 1 || !isset($this->cdata['params']['campaign_activity'])):?>
						<li class="active">
							<a data-toggle="tab" href="#camp_activity" class="af-pb-10 af-font-bold">
								<?php echo strtoupper(Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_ACTIVITY'));?>
							</a>
						</li>
						<?php
						endif;

						if (!empty($this->cdata['donors']))
						{
						?>
						<li class="<?php echo $this->cdata['params']['campaign_activity'] == 0 ? 'active' : ''?>">
							<a data-toggle="tab" href="#camp_donors" class="af-pb-10 af-font-bold">
								<?php echo ($this->cdata['campaign']->type == 'donation') ? strtoupper(Text::_('COM_JGIVE_DONORS')) : strtoupper(Text::_('COM_JGIVE_INVESTORS'));?>
								<span class="badge"><?php echo $this->cdata['campaign']->campaignDonorsCount; ?></span>
							</a>
						</li>
						<?php
						}

						if ($this->enableReports && !empty($this->reports))
						{
						?>
							<li>
								<a data-toggle="tab" href="#camp_reports" class="af-pb-10 af-font-bold">
									<?php echo strtoupper(Text::_('COM_JGIVE_REPORTS'));?>
									<span class="badge"><?php echo $this->total_reports; ?></span>
								</a>
							</li>
						<?php
						}

						if (isset($this->item['campaign']->gallery))
						{
							if (!empty($this->cdata['image']) && $this->params->get('img_gallery'))
							{
								?>
									<li>
										<a data-toggle="tab" href="#camp_gallery" class="af-pb-10 af-font-bold">
											<?php echo strtoupper(Text::_('COM_JGIVE_SINGLE_GALLERY'));?>
										</a>
									</li>
							<?php
							}
							elseif (!empty($this->cdata['video']) && $this->params->get('video_gallery'))
							{
								?>
									<li>
										<a data-toggle="tab" href="#camp_gallery" class="af-pb-10 af-font-bold">
											<?php echo strtoupper(Text::_('COM_JGIVE_SINGLE_GALLERY'));?>
										</a>
									</li>
							<?php
							}
						}

						if (count($this->extraData))
						{
						?>
							<li>
								<a data-toggle="tab" href="#additional_info" class="af-pb-10 af-font-bold">
									<?php echo strtoupper(Text::_('COM_JGIVE_EXTA_FIELDS'));?>
								</a>
							</li>
						<?php
						}
						?>
					</ul>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 af-mt-20">
					<!--Tab content started here-->
					<div class="tab-content">
						<!---Tab-Activity---->
						<?php if ($this->cdata['params']['campaign_activity'] == 1 || !isset($this->cdata['params']['campaign_activity'])):?>
						<div id="camp_activity" class="tab-pane active">
							<?php
								echo $this->loadTemplate("activity");
							?>
						</div>
						<?php endif;?>

						<!---Tab-DONORS---->
						<div id="camp_donors" class="tab-pane af-mt-20 <?php echo $this->cdata['params']['campaign_activity'] == 0 ? 'active' : ''?>">
							<?php
							if ($this->cdata['otherData']->loggedUserId == $this->cdata['campaign']->creator_id || $this->cdata['campaign']->allow_view_donations)
							{
								echo $this->loadTemplate("donors");
							}
							else
							{
								if ($this->cdata['campaign']->type == 'donation')
								{
									echo Text::_('COM_JGIVE_DONATIONS_ACCESS_LOCKED');
								}
								else
								{
									echo Text::_('COM_JGIVE_INVESTMENTS_ACCESS_LOCKED');
								}
							}
							?>
						</div>

						<!---Tab-REPORTS---->
						<div id="camp_reports" class="tab-pane af-mt-20">
							<?php
								echo $this->loadTemplate('reports');
							?>
						</div>

						<!---Tab-GALLERY---->
						<div id="camp_gallery" class="tab-pane af-mt-20">
							<?php
								echo $this->loadTemplate("gallary_bs3");
							?>
						</div>

						<!--Additional Information-->
						<div id="additional_info" class="tab-pane">
							<?php
								if ($this->form_extra)
								{
									$count = 0;
									$xmlFieldSets = array();

									if (!empty($this->formXml))
									{
										foreach ($this->formXml as $k => $xmlFieldSet)
										{
											$xmlFieldSets[$count] = $xmlFieldSet;
											$count++;
										}
									}

									$itemData         = new stdClass();
									$itemData->id     = $this->cdata['campaign']->id;
									$itemData->client = 'com_jgive.campaign';
									$itemData->created_by = $this->cdata['campaign']->creator_id;

									// Call the JLayout to render the fields in the details view
									$layout = new FileLayout('campaign.extrafields', JPATH_ROOT . '/components/com_jgive');
									echo $layout->render(array('xmlFormObject' => $xmlFieldSets, 'formObject' => $this->form_extra, 'itemData' => $itemData));
								}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--Giveback-->
		<div class="col-md-4">
			<div class="row border-md-l">
				<?php
				if ((!in_array('give_back', $this->hideFields) && $this->hideShowFields == 1) || $this->hideShowFields == 0)
				{
					if (count($this->cdata['givebacks']) >= 1)
					{
					?>
						<div class="col-xs-12 col-md-offset-1 af-pt-25 af-text-md-left center">
							<strong><?php echo strtoupper(Text::_('COM_JGIVE_SINGLE_GIVEBACK'));?></strong>
							<div class="clearfix">&nbsp;</div>
							<?php
							$i = 0;

							foreach ($this->cdata['givebacks'] as $giveback)
							{
								$sold_givebacks = $giveback->sold_giveback;
								$giveBackFlag = 0;
								$giveback_tooltip = Text::_('COM_JGIVE_BY_GIVEBACK');
								$giveback_id = (int) $giveback->id;

								if ($sold_givebacks == $giveback->total_quantity || $sold_givebacks > $giveback->total_quantity)
								{
									$giveBackFlag   = 1;
									$giveback_tooltip = Text::_('COM_JGIVE_GIVEBACK_SOLD_OUT');
								}

								$url = Uri::root(true) . '/index.php?option=com_jgive&task=donations.donate&cid=' . $this->cdata['campaign']->id . '&giveback_id=' .
								$giveback_id . '&Itemid=' . $this->cdata['otherData']->allCampItemid;

								$onclick = 'onclick="callDonateForm(\'' . $url . '\')"';

								// Disable onlclick
								if ($giveBackFlag == 1)
								{
									$onclick = '';
								}
							?>
								<div class="row">
									<div class="pull-left text-dark text-decoration-none" <?php if ($onclick) echo 'href="' . $url . '"';?> title="<?php echo  $giveback_tooltip; ?>">
										<?php
											if ($giveback->title)
											{
											?>
											<div class="col-xs-12 fw-bold fs-5">
												<a <?php if ($onclick) echo 'href="' . $url . '"';?> title="<?php echo  $giveback_tooltip; ?>" >
													<?php echo htmlspecialchars($giveback->title, ENT_COMPAT, 'UTF-8');?>
												</a>
											</div>
											<?php
											}
										?>
										<div class="col-xs-12 giveback">
											<?php
												if ($giveback->image_path)
												{
												?>
													<img src="<?php echo $giveback->image_path->media_l; ?>">
												<?php
												}
												?>
										</div>
										<div class="col-xs-12">
											<?php echo htmlspecialchars($giveback->description, ENT_COMPAT, 'UTF-8');?>
										</div>
										<div class="col-xs-12">
											<strong title="<?php echo Text::_('COM_JGIVE_GIVEBACK_TOTAL_COUNT');?>">
												<?php
													echo $jgiveFrontendHelper->getFormattedPrice($giveback->amount) . ' | ';
												?>
											</strong>
											<strong title="<?php echo $giveBackFlag ? Text::_('COM_JGIVE_GIVEBACK_SOLD') : Text::_('COM_JGIVE_GIVEBACK_SOLD_COUNT');?>">
													<?php
													if ($giveBackFlag == 1)
													{
														echo Text::_('COM_JGIVE_GIVEBACK_SOLD');
													}
													else
													{
														echo $sold_givebacks;
													}
													?>
											</strong>
										</div>
									</div>
								</div>
								<div class="clearfix">&nbsp;</div>
							<?php
							$i ++;
							}
							?>
						</div>
					<?php
					}
				}
				?>
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="camp_id" name="camp_id" value="<?php echo $this->cdata['campaign']->id; ?>" />
<script type="text/javascript">
	jgive.campaignDetails.init();
</script>
