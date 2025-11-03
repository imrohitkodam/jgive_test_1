<?php
/**
 * @package    Jgive
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Factory;

echo '<div id="fb-root"></div>';

$fblike_tweet = Uri::root(true) . '/media/com_jgive/javascript/fblike.js';
echo "<script type='text/javascript' src='" . $fblike_tweet . "'></script>";

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$core_js = Uri::root() . 'media/system/js/core.js';

$flg = 0;
$campaignHelper = new campaignHelper;

// Set Title by campaign name
$document = Factory::getDocument();

foreach ($document->_scripts as $name => $ar)
{
	if ($name == $core_js )
	{
		$flg = 1;
	}
}

if ($flg == 0)
{
	echo "<script type='text/javascript' src='" . $core_js . "'></script>";
}

$show_field = 0;
$max_donation_cnf = 0;
$goal_amount      = 0;
$show_selected_fields = $this->params->get('show_selected_fields');

if ($show_selected_fields)
{
	$creatorfield = $this->params->get('creatorfield');

	if (isset($creatorfield))
	{
		foreach ($creatorfield as $tmp)
		{
			switch ($tmp)
			{
				case 'max_donation':
					$max_donation_cnf = 1;
				break;

				case 'long_desc':
					$long_desc_cnf = 1;
				break;

				case 'goal_amount':
					$goal_amount = 1;
				break;
			}
		}
	}
}
else
{
	$show_field = 1;
}
?>
<div class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>" id="all">
	<div class="container-fluid" id="jgiveWrapper">
		<div class="row">
			<div class="col-xs-12 col-md-4">
				<ul class="list-inline">
					<li><h1 class="fs-title af-mt-10"><strong><?php echo strtoupper(Text::_('COM_JGIVE_ALL_CAMPAIGNS'));?></strong></h1></li>
				</ul>
			</div>
			<div class="col-xs-12 col-md-8 campaignBolgFilters ">
				<form action="" method="GET" name="adminForm4" id="adminForm4">
					<input type="hidden" name="option" value="com_jgive" />
					<input type="hidden" name="view" value="campaigns" />
					<input type="hidden" name="layout" value="all" />
					<?php echo HTMLHelper::_('form.token'); ?>
					<ul class="pull-right list-inline campaignsform">
						<?php
							$launch_camp_url = Uri::root() . substr(
							Route::_('index.php?option=com_jgive&view=campaignform&layout=default&Itemid=' . $this->otherData['createCampItemid']),
							strlen(Uri::base(true)) + 1
							);

							if ($this->canCreate)
							{
							?>
								<li class="campaign__launch list__separation">
									<a href="<?php echo $launch_camp_url;?>" title="<?php echo Text::_('COM_JGIVE_CREATE_NEW_CAMPAIGN')?>">
										<i class="fa fa-paper-plane-o" aria-hidden="true"></i>
										<span class="hidden-xs"><?php echo Text::_('COM_JGIVE_VENDOR_CAMPAIGNS_LAUNCH_CAMPAIGN');?></span>
									</a>
								</li>
							<?php
							}

							if ($this->params->get('show_filters'))
							{
								if ($this->params->get('show_sorting_options'))
								{
								?>
								<li class="list__separation">
									<div class="dropdown af-d-flex">
										<a onclick="jgive.jgShowSortFilter();" title="<?php echo Text::_('COM_JGIVE_ORDERING_OPTIONS');?>"><i class="fa fa-sort af-mr-5" aria-hidden="true"></i></a>
										<?php
											echo HTMLHelper::_('select.genericlist', $this->ordering_options, "filter_order", ' size="1"
											onchange="jgiveCommon.filters.submitFilters(\'adminForm4\');"
											class="form-control collapse" name="filter_order"',"value", "text", $this->lists['filter_order']);
										?>
									</div>
								</li>
							<?php
								}
								?>
								<li class="list__separation">
									<a id="displayFilter" href="javascript:void(0)" onclick="jgive.toggleDiv('displayFilterText');" title="<?php echo Text::_('COM_JGIVE_FILTER_CAMPAIGN');?>">
										<i class="fa fa-filter"></i>
									</a>
								</li>
								<?php
							}

							if ($this->params->get('show_search_filter'))
							{?>
								<li class="campaign__search">
									<a id="searchCampBtn" href="javascript:void(0)" onclick="jgive.toggleSearch('SearchFilterInputBox');" title="<?php echo Text::_('COM_JGIVE_ENTER_CAMPAIGN_NAME')?>">
										<i class="fa fa-search" ></i>
									</a>
									<span class="pull-left search__campaign hide" id="SearchFilterInputBox">
										<input type="text"
											placeholder="<?php echo Text::_('COM_JGIVE_ENTER_CAMPAIGN_NAME'); ?>"
											name="filter_search"
											id="filter_search"
											value="<?php echo $this->lists['filter_search']; ?>"
											class="form-control col-xs-5"
											onchange="jgiveCommon.filters.submitFilters('adminForm4');"
											onkeypress="return jgive.searchCampaigns(event);"
											/>
											<button
												onclick="jgive.campaigns.campaignSearchClear()"
												type="button"
												class="btn campaign__search--clear af-absolute"
												data-original-title="Clear"
												title="<?php echo Text::_('COM_JGIVE_CLEAR_TOOLTIP');?>">
													<i class="fa fa-close"></i>
											</button>
									</span>
								</li>
							<?php
							}
							?>
						<li><?php echo $this->pagination->getLimitBox(); ?></li>
					</ul>
				</form>
			</div>
			<!-- Filters Div-->
			<div class="col-xs-12 campaign__filter hide" id="displayFilterText">
				<a id="displayToggleFilter" href="javascript:void(0)" onclick="jgive.toggleDiv('displayFilterText');" title="<?php echo Text::_('COM_JGIVE_FILTER_CAMPAIGN');?>" class="visible-xs pull-right">
					<i class="fa fa-remove" aria-hidden="true"></i>
				</a>
				<?php
					$displayData = array();

					if (!empty($this->data))
					{
						$displayData['campData'] = $this->data[0];
					}
					else
					{
						$campData = array();
						$campData['params'] = $this->params;
						$campData['otherData'] = $this->otherData;
						$displayData['campData'] = $campData;
					}

					$displayData['pagination'] = $this->pagination;
					$displayData['campTypeOption'] = $this->campaign_type_filter_options;
					$displayData['categories'] = $this->cat_options;
					$displayData['org_ind_type'] = $this->filter_org_ind_type;
					$displayData['sort_by'] = $this->ordering_options;
					$displayData['orderDir'] = $this->ordering_direction_options;
					$displayData['list'] = $this->lists;

					$html = "";
					$layout = new FileLayout('filters', $basePath = JPATH_SITE . '/components/com_jgive/layouts/campaigns');
					$html .= $layout->render($displayData);
					$result = $html;
					echo $result;
				?>
				<div class="clearfix">&nbsp;</div>
			</div>

			<div class="col-xs-12">
				<!--Form code here-->
				<div class="row">
					<div class="col-xs-12">
					<?php
						if (empty($this->data))
						{
						?>
							<div class="alert alert-warning">
								<?php echo Text::_('COM_JGIVE_NO_CAMPAIGN_FOUND');?>
							</div>
					<?php
						}
						else
						{
							foreach ($this->data as $cdata)
							{
								$cdata['id']               = (int) $cdata['id'];
								$amounts                   = $campaignHelper->getCampaignAmounts($cdata['id']);
								$cdata['amount_received']  = $amounts['amount_received'];
								$cdata['remaining_amount'] = $amounts['remaining_amount'];

								// Count donors(donations)
								$cdata['donor_count']           = $campaignHelper->getCampaignDonorsCount($cdata['id']);
								$cdata['donteButtonStatusFlag'] = $this->model->getDonateButtonStatusFlag($cdata);
						?>
								<div class="com_jgive_border">
									<div class='com_jgive_campaign_title'>
										<h2>
										<?php
											// Show the star to know the campaigns marked as Featured
											$title = Text::_('COM_JGIVE_FEATURED');
											$result = $campaignHelper->isFeatured($cdata['id']);
											echo $result ? $imgpath = '<img src="' . Uri::root(true) . '/media/com_jgive/images/featured.png"  title="' . $title . '">':'';
										?>
											<a target="_blank" href="<?php echo Uri::root() . substr( Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $cdata['id'] . '&Itemid=' . $this->singleCampaignItemid), strlen(Uri::base(true)) + 1);?>">
												<?php echo htmlspecialchars($cdata['title'], ENT_COMPAT, 'UTF-8'); ?>
											</a>
										</h2>

									<?php
										// Generate unique ad url for social sharing
										require_once JPATH_SITE . "/components/com_jgive/helpers/integrations.php";
										$ad_url = 'index.php?option=com_jgive&view=campaign&layout=default&id=' . $cdata['id'];

										// Integration with Jlike
										if (file_exists(JPATH_SITE . '/' . 'components/com_jlike/helper.php'))
										{
											$show_comments = -1;
											$show_like_buttons = 1;
											$jlikehtml = $this->jgiveFrontendHelper->DisplayjlikeButton(
											$ad_url, $cdata['id'], htmlspecialchars($cdata['title'], ENT_COMPAT, 'UTF-8'), $show_comments, $show_like_buttons
											);

											if ($jlikehtml)
											{
												echo $jlikehtml;
											}
										}

										// Integration with Jlike
										$ad_url = Uri::root() . substr(Route::_($ad_url), strlen(Uri::base(true)) + 1);
										$add_this_share = '';
										$pid = $this->params->get('addthis_publishid', 'GET', 'STRING');

										if ($this->params->get('social_sharing'))
										{
											if ($this->params->get('social_shring_type') == 'addthis')
											{
												$add_this_share = '
												<!-- AddThis Button BEGIN -->
												<div class="addthis_toolbox addthis_default_style">

												<a class="addthis_button_facebook_like" fb:like:layout="button_count" class="addthis_button" addthis:url="' . $ad_url . '"></a>
												<a class="addthis_button_google_plusone" g:plusone:size="medium" class="addthis_button" addthis:url="' . $ad_url . '"></a>
												<a class="addthis_button_tweet" class="addthis_button" addthis:url="' . $ad_url . '"></a>
												<a class="addthis_button_pinterest_pinit" class="addthis_button" addthis:url="' . $ad_url . '"></a>
												<a class="addthis_counter addthis_pill_style" class="addthis_button" addthis:url="' . $ad_url . '"></a>
												</div>
												<script type="text/javascript">
													var addthis_config ={ pubid: "' . $pid . '"};
												</script>
												<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid="' . $pid . '"></script>
												<!-- AddThis Button END -->';

												$add_this_js = 'http://s7.addthis.com/js/300/addthis_widget.js';
												$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;
												$JgiveIntegrationsHelper->loadScriptOnce($add_this_js);
										?>

												<div id="rr" style="">
													<div class="social_share_container">
														<meta property="og:title" content="The Rock"/>
														<div class="social_share_container_inner" onmouseover="onmouseoverfn(\' <?php $cdata['id']?> \',\'<?php $cdata['title']?>\',\'<?php $cdata['image']->media_m;?>\' )">
															<?php $add_this_share ?>
														</div>
													</div>
												</div>
										<?php
											}
											else
											{
											?>
												<div class="com_jgive_horizontal_social_buttons">
													<div class="com_jgive_float_left">
														<div class="fb-like" data-href=" <?php  $ad_url ?>" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true">
														</div>
													</div>
													<div class="com_jgive_float_left">
															&nbsp; <div class="g-plus" data-action="share" data-annotation="bubble" data-href="<?php $ad_url ?>"></div>
													</div>
													<div class="com_jgive_float_left">
															&nbsp; <a href="https://twitter.com/share" class="twitter-share-button" data-url=" <?php $ad_url ?>" data-counturl="<?php
															$ad_url ?>" data-lang="en">Tweet</a>
													</div>
												</div>
												<div class="com_jgive_clear_both"></div>
										<?php
											}
// Check if this is not needed
/*
											echo' <div id="rr" style="">
												<div class="social_share_container">
												<meta property="og:title" content="The Rock"/>
												<div class="social_share_container_inner" onmouseover="onmouseoverfn(\'' . $cdata['campaign']->id . '\',\'' .
												htmlspecialchars($cdata['campaign']->title, ENT_COMPAT, 'UTF-8') . '\',\'' . Uri::base() . $image_linnk . '\' )">' . $add_this_share . '</div></div></div>';
*/
										}
											?>
								</div>

								<div class="row">
									<?php
									$campImg = Uri::root() . 'media/com_jgive/images/default_campaign.png';

									if (!empty($cdata['image']))
									{
										$campImg = $cdata['image']->media_m;
									}
									?>
									<div class="col-lg-2 col-sm-3 col-xs-12 com_jgive_campaign_image_block">
										<?php echo "<img class='img-thumbnail com_jgive_img_96_96' src='" . $campImg . "' />";?>
									</div>

									<?php if (isset($cdata['short_description']))
									{?>
										<div class="col-lg-10 col-sm-9 col-xs-12 com_jgive_justify">
											<?php echo nl2br($cdata['short_description']);?>
										</div>
									<?php
									}?>
								</div>

									<div class="row">
										<div class="col-sm-6 col-xs-12">
											<table class="table table-bordered">
												<tr>
													<!--if condition added by Sneha, to goal amount-->
												<?php
													if ($show_field == 1 OR $goal_amount == 0 ):
													?>
														<td width="25%" class="com_jgive_td_amt com_jgive_td_center">
															<?php echo Text::_('COM_JGIVE_GOAL_AMOUNT');?>
														</td>

														<td width="25%" class="com_jgive_td_amt_right com_jgive_td_center">
															<?php
															$diplay_amount_with_format = $this->jgiveFrontendHelper->getFormattedPrice($cdata['goal_amount']);
															echo $diplay_amount_with_format;
															?>
														</td>
												<?php
													endif;
														$css_start_date = '';
														$css_end_date = '';
														$css_max_donors = '';

														// Check if exeeding goal amount is allowed
														// If not check for received amount to decide about hiding donate button
														$flag = 0;
														$date_expire = 0;

														if ($cdata['allow_exceed'] == 0)
														{
															if ($cdata['amount_received'] >= $cdata['goal_amount'])
															{
																$flag = 1;
															}
														}

														if ($cdata['max_donors'] > 0)
														{
															if ($cdata['donor_count'] >= $cdata['max_donors'])
															{
																$flag = 1;
																$css_max_donors = "class='text-error'";
															}
														}

														// If both start date, and end date are present
														$curr_date = '';

														if ((int) $cdata['start_date'] && (int) $cdata['end_date'])
														{
															$start_date = Factory::getDate($cdata['start_date'])->Format(Text::_('Y-m-d'));
															$end_date = Factory::getDate($cdata['end_date'])->Format(Text::_('Y-m-d'));
															$curr_date = Factory::getDate()->Format(Text::_('Y-m-d'));

															// If current date is less than start date, don't show donate button
															if ($curr_date < $start_date)
															{
																$flag = 1;
																$css_start_date = "class='text-error'";
															}

															// If current date is more than end date, don't show donate button
															if ($curr_date > $end_date)
															{
																	$flag = 1;
																$date_expire = 1;
																$css_end_date = "class='text-error'";
															}
														}


														// Calculate progress progress-progress-bar data

														$recPer = 0;
														if (!empty($cdata['amount_received']))
														{
															$recPer = intval((100 * $cdata['amount_received']) / $cdata['goal_amount']);
														}

														if ($recPer > 100)
														{
															$recPer = 100;
															$progresslabel = Text::_('COM_JGIVE_MORE_THAN_HUNDRED') . ' %';
														}
														else
														{
															$progresslabel = $recPer . '%';
														}
														?>
												</tr>

												<tr>
													<td width="25%" class="com_jgive_td_amt com_jgive_td_center">
														<?php echo Text::_('COM_JGIVE_AMOUNT_RECEIVED');?>
													</td>
													<td width="25%" class="com_jgive_td_amt_right com_jgive_td_center">
														<?php

														$diplay_amount_with_format = $this->jgiveFrontendHelper->getFormattedPrice($cdata['amount_received']);
														echo $diplay_amount_with_format;
														?>
													</td>
												</tr>

												<tr>
													<td width="25%" class="com_jgive_td_amt com_jgive_td_center">
														<?php echo Text::_('COM_JGIVE_REMAINING_AMOUNT');?>
													</td>
													<td width="25%" class="com_jgive_td_amt_right com_jgive_td_center">
														<?php
														if ($cdata['amount_received'] > $cdata['goal_amount'])
														{
															echo Text::_('COM_JGIVE_NA');
														}
														else
														{
															$diplay_amount_with_format = $this->jgiveFrontendHelper->getFormattedPrice($cdata['remaining_amount']);

															echo $diplay_amount_with_format;
														}
														?>
													</td>
												</tr>
												<tr>
													<td width="25%" class="com_jgive_td_amt com_jgive_td_center">
														<?php
															$time_curr_date = strtotime($curr_date);
															$time_end_date  = strtotime($cdata['end_date']);
															$interval       = $time_end_date - $time_curr_date;
															$days_left      = floor($interval / (60 * 60 * 24));

															if ((int) ($time_curr_date) && (int) ($time_end_date))
															{
																echo Text::_('COM_JGIVE_DAYS_LEFT');
															}
														?>
													</td>
													<td width="25%" class="com_jgive_td_amt_right com_jgive_td_center">
														<?php
															if ($date_expire)
															{
																echo Text::_('COM_JGIVE_NA');
															}
															elseif ((int) ($time_curr_date) && (int) ($time_end_date))
															{
																echo $days_left > 0 ? $days_left : Text::_('COM_JGIVE_NA');
															}
														?>
													</td>
												</tr>
												<tr>
													<td width="25%" class="com_jgive_td_amt com_jgive_td_center" colspan="2">
														<?php
														echo Text::_('COM_JGIVE_TOTAL') . (($cdata['type'] == 'donation') ? Text::_('COM_JGIVE_DONATIONS') : Text::_('COM_JGIVE_INVESTMENTS'));

														if ($show_field == 1 OR $max_donation_cnf == 0 )
															echo ' / ' . Text::_('COM_JGIVE_MAX_ALLOWED') . (($cdata['type'] == 'donation') ? Text::_('COM_JGIVE_DONATIONS') : Text::_('COM_JGIVE_INVESTMENTS'));
														?>
														<br/>
														<span <?php echo $css_max_donors;?>>
															<?php
															echo $cdata['donor_count'];

															if ($show_field == 1 OR $max_donation_cnf == 0 )
															{
																if ($cdata['max_donors'] > 0)
																{
																	echo ' / ' . $cdata['max_donors'];
																}
																else
																{
																	echo ' / ' . Text::_('COM_JGIVE_NA');
																}
															}
															?>
														</span>
													</td>
												</tr>
											</table>
										</div>

										<div class="col-sm-6 col-xs-12 campaignBlogProgress">
											<div class="row">
												<div class="form-group">
													<label class="col-lg-3 col-md-4 col-sm-5 col-xs-6 control-label"><span <?php echo $css_start_date;?> ><?php echo Text::_('COM_JGIVE_START_DATE');?>:</span></label>

													<div class="col-lg-9 col-md-8 col-sm-7 col-xs-6">
														<?php echo HTMLHelper::_('date', $cdata['start_date'], $this->params->get('date_format', 'j  M  Y'));?>
													</div>
												</div>
												<div class="clearfix"></div>
												<div class="form-group">
													<label class="col-lg-3 col-md-4 col-sm-5 col-xs-6 control-label">
														<span <?php echo $css_end_date;?>>
															<?php echo Text::_('COM_JGIVE_END_DATE');?>:
														</span>
													</label>

													<div class="col-lg-9 col-md-8 col-sm-7 col-xs-6">
														<?php echo HTMLHelper::_('date', $cdata['end_date'], $this->params->get('date_format', 'j  M  Y'));?>
													</div>
												</div>
											</div>
										<?php
											if ($show_field == 1 OR $goal_amount == 0 )
											{
											?>
												<div class="progress" >
													<div class="progress-bar progress-bar-success " style="width:<?php echo $recPer;?>%; min-width: 2em;" >
														<?php echo $progresslabel;?>
													</div>
												</div>
										<?php
											}

											if ($cdata['donteButtonStatusFlag'] == 0)
											{?>
												<div class="row">
													<div class="col-xs-6">
														<input type="button" class="btn disabled com_jgive_button af-w-100" value="<?php echo (($cdata['type'] == 'donation') ? Text::_('COM_JGIVE_DONATIONS_CLOSED') : Text::_('COM_JGIVE_INVESTMENTS_CLOSED')); ?>"/>
													</div>
													<div class="col-xs-6">
														<a class="btn btn-primary com_jgive_button af-w-100"
														href="<?php echo Uri::root() . substr(
														Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' .
														$cdata['id'] . '&Itemid=' . $this->singleCampaignItemid),
														strlen(Uri::base(true)) + 1 );?>">
															<?php echo Text::_('COM_JGIVE_READ_MORE');?>
														</a>
													</div>
												</div>
											<?php
											}
											elseif($cdata['donteButtonStatusFlag'] == -1)
											{?>
												<div class="row">
													<div class="col-xs-6">
														<input type="button"
															class="btn btn-default disabled com_jgive_button af-w-100"
															value="<?php echo Text::_("COM_JGIVE_WILL_START_SOON"); ?>"/>
													</div>
													<div class="col-xs-6">
														<a class="btn btn-primary com_jgive_button af-w-100"
														href="<?php echo Uri::root() . substr(
														Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' .
														$cdata['id'] . '&Itemid=' . $this->singleCampaignItemid),
														strlen(Uri::base(true)) + 1 );?>">
															<?php echo Text::_('COM_JGIVE_READ_MORE');?>
														</a>
													</div>
												</div>
											<?php
											}
											elseif($cdata['donteButtonStatusFlag'] == 1)
											{?>
												<div class="row">
													<form action="" method="post" name="adminForm2" id="adminForm2">
														<input type="hidden" name="cid" id="cid" value="<?php echo (int) $cdata['id'];?>">
														<div class="form-group">
															<div class="col-xs-6">
																<button class="btn btn-success form-control" type="submit">
																	<?php
																	echo (($cdata['type'] == 'donation') ? Text::_('COM_JGIVE_BUTTON_DONATE') : Text::_('COM_JGIVE_BUTTON_INVEST'));
																	?>
																</button>
															</div>
															<div class="col-xs-6">
																	<a
																	target="_blank"
																	class="btn btn-primary form-control"
																	href="<?php echo Uri::root() . substr(
																	Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $cdata['id'] . '&Itemid=' .
																	$this->singleCampaignItemid
																	), strlen(Uri::base(true)) + 1
																	);?>">
																		<?php echo Text::_('COM_JGIVE_READ_MORE');?>
																	</a>
															</div>
														</div>
														<input type="hidden" name="option" value="com_jgive" />
														<input type="hidden" name="task" value="donations.donate" />
														<?php echo HTMLHelper::_('form.token'); ?>
													</form>
												</div>
											<?php
											}
											?>
										</div>
									</div>
								</div>
							<?php
							}
						}
					?>
					</div>
				</div>
			</div>

			<form action="" method="post" name="adminForm" id="adminForm">
				<div class="row">
					<div class="col-xs-12">
						<?php $class_pagination = ''; ?>
						<div class="<?php echo $class_pagination; ?> com_jgive_align_center pull-right">
							<?php echo $this->pagination->getListFooter(); ?>
						</div>
					</div>
				</div>
				<input type="hidden" name="option" value="com_jgive" />
				<input type="hidden" name="view" value="campaigns" />
				<input type="hidden" name="layout" value="all" />
				<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>
<script>
	var tjListFilters = [];

	jQuery(document).ready(function(){
		if(localStorage.getItem("flag") == 1 || localStorage.getItem("flag") != null)
		{
			jQuery(".campaign__filter").toggleClass( "hide active" );
		}

		if(localStorage.getItem("filter") == 0)
		{
			jQuery(".search__campaign").toggleClass( "hide active" );
		}

		if(localStorage.getItem("dropdown")==1)
		{
			jQuery("#filter_order").toggle();
		}

		jQuery("#limit").attr('onchange', 'jgiveCommon.filters.submitFilters(\'adminForm4\')');

		<?php
		foreach ($this->availableFilters as $availableFilter)
		{
			?>
			tjListFilters.push('<?php echo $availableFilter; ?>');
			<?php
		}
		?>
	});

	var jgive_baseurl = "<?php echo Uri::root(); ?>";
	var menuItemId = "<?php echo $this->otherData['allCampaignsItemid']; ?>";
</script>

