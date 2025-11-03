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
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$document = Factory::getDocument();
HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.min.css');
HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.css');
HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/vendors/font-awesome/css/font-awesome.min.css');
HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/vendors/font-awesome/css/font-awesome.css');
HTMLHelper::_('stylesheet', 'media/com_jgive/vendors/css/morris.css');
HTMLHelper::_('stylesheet', 'media/com_jgive/css/tjdashboard-sb-admin.css');
HTMLHelper::_('script', 'media/com_jgive/vendors/js/morris.min.js');
HTMLHelper::_('script', 'media/com_jgive/vendors/js/raphael.min.js');
HTMLHelper::_('script', 'media/com_jgive/javascript/migration.js');

JHtml::script(Uri::root() . 'libraries/techjoomla/assets/js/houseKeeping.js');
$document->addScriptDeclaration("var tjHouseKeepingView='cp';");

$session = Factory::getSession();
$session->set('PeriodicDonationsCount', '');

$i = 0;
$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
$curr_sym = $this->currencySymbol;

$jgivehelper    = new JgiveFrontendHelper;
$dashboard_data = $this->dashBoard;
$AllMonthName   = $this->allMonthName;
$MonthDonation  = $this->monthDonation;

foreach ($this->allMonthName as $AllMonthName)
{
	$AllMonthName_final[$i] = $AllMonthName['month'];
	$curr_MON = $AllMonthName['month'];
	$month_amt_val[$curr_MON] = 0;
	$i++;
}

$barChartData = 0;

foreach ($this->monthDonation as $MonthDonation)
{
	$month_year = '';
	$month_year = $MonthDonation->YEARNAME;
	$month_name = $MonthDonation->MONTHSNAME;

	$month_int = (int) $month_name;
	$timestamp = mktime(0, 0, 0, $month_int);
	$curr_month = date("F", $timestamp);

	foreach ($this->allMonthName as $AllMonthName)
	{
		if (($curr_month == $AllMonthName['month']) and ($MonthDonation->amount) and ($month_year == $AllMonthName['year']))
		{
			$month_amt_val[$curr_month] = str_replace(",", '', $MonthDonation->amount);
		}

		if ($barChartData == 0)
		{
			if ($MonthDonation->amount)
			{
				$barChartData = 1;
			}
		}
	}
}

$month_amt_str  = implode(",", $month_amt_val);
$month_name_str = implode("','", $AllMonthName_final);
$month_name_str = "'" . $month_name_str . "'";
$month_array_name = array();

$js = "
	function refreshViews()
	{
		var curr_sym = \"" . $curr_sym . "\";
		fromDate = document.getElementById('from').value;
		toDate = document.getElementById('to').value;
		fromDate1 = new Date(fromDate.toString());
		toDate1 = new Date(toDate.toString());
		difference = toDate1 - fromDate1;
		days = Math.round(difference/(1000*60*60*24));

		if (parseInt(days)< 0)
		{
			alert(\"" . Text::_('COM_JGIVE_DATELESS') . "\");

			return;
		}

		/*Set Session Variables*/
		var info = {};
		techjoomla.jQuery.ajax({
			type: 'GET',
			url: 'index.php?option=com_jgive&task=cp.SetsessionForGraph&fromDate='+fromDate+'&toDate='+toDate,
			dataType: 'json',
			async:false,
			success: function(data) {
			}
		});

		/*Get periodic data and redraw chart*/
		techjoomla.jQuery.ajax({
			type: 'GET',
			url: 'index.php?option=com_jgive&task=cp.makechart',
			dataType: 'json',
			success: function(data)
			{
				techjoomla.jQuery('#bar_chart_graph').html('' + data.barchart);
				/*Reset hidden field values*/
				document.getElementById('pending_donations').value=data.pending_donations;
				document.getElementById('confirmed_donations').value=data.confirmed_donations;

				document.getElementById('denied_donations').value=data.denied_donations;
				document.getElementById('refunded_donations').value=data.refunded_donations;

				document.getElementById('canceled_donations').value=data.canceled_donations;
				/*Redraw charts*/
				document.getElementById('periodic_donations').innerHTML = curr_sym + ' ' + data.periodicDonationsCount;
				drawPeriodicDonationsChart();
			}
		});
	}";

$document->addScriptDeclaration($js);
?>
	<form name="adminForm" id="adminForm" class="form-validate" method="post">
		<?php
			if (!empty($this->sidebar))
			{
			?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar;?>
				</div>
				<div id="j-main-container" class="span10">
			<?php
			}
			else
			{
			?>
				<div id="j-main-container">
			<?php
			}
			?>
		<!-- TJ Bootstrap3 -->
		<div class="tjBs3">
			<!-- TJ Dashboard -->
			<div class="tjDB">
				<div id="wrapper">
					<div id="page-wrapper">
						<div class="row">
							<div class="col-lg-12">
							<?php
							if (!$this->downloadid)
							{
							?>
								<div class="alert alert-warning">
									<?php echo Text::sprintf(
									'COM_JGIVE_LIVE_UPDATE_DOWNLOAD_ID_MSG', '<a href="https://techjoomla.com/about-tj/faqs/#how-to-get-your-download-id" target="_blank">' .
									Text::_('COM_JGIVE_LIVE_UPDATE_DOWNLOAD_ID_MSG2') . '</a>'
									);
									?>
								</div>
							<?php
							}
							?>
								<div>
									<?php
									$versionHTML = '<span style="font-size: 9pt" class="label label-info">' .
											Text::_('COM_JGIVE_HAVE_INSTALLED_VER') . ': ' . $this->version . '</span>';

									if ($this->latestVersion)
									{
										if ($this->latestVersion->version > $this->version)
										{
											$versionHTML = '<div class="alert alert-error">' .
											'<i class="icon-puzzle install"></i>' .
											Text::_('COM_JGIVE_HAVE_INSTALLED_VER') .
											': ' . $this->version . '<br/>' . '<i class="icon icon-info"></i>' .
											Text::_("COM_JGIVE_NEW_VER_AVAIL") . ': ' .
											$this->latestVersion->version . '<br/>' . '<i class="icon icon-warning"></i>' . '<span class="label label-info">' .
											Text::_("COM_JGIVE_LIVE_UPDATE_BACKUP_WARNING") . '</span>' . '</div>

											<div style="text-align:right">
												<a href="index.php?option=com_installer&view=update" class="btn btn-small btn-primary">' .
												Text::sprintf('COM_JGIVE_LIVE_UPDATE_TEXT', $this->latestVersion->version) . '
												</a>
											</div>';
										}
									}?>
									<?php echo $versionHTML; ?>
								</div>
							</div>
						</div>
					<div class="clearfix">&nbsp;</div>
						<!-- Start - stat boxes -->
						<div class="row">
							<!--Start - campaigns -->
							<div class="col-lg-3 col-md-6">
								<div class="panel panel-green">
									<a href="<?php echo Route::_('index.php?option=com_jgive&view=campaigns&layout=default',    false)?>">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3 ">
													<i class="fa fa-bullhorn fa-4x"></i>
												</div>
												<div class="col-xs-9 af-text-right">
													<div class="huge"><span><?php echo $dashboard_data['campaignInfo']['total_campaigns'];?></span> </div>
													<div><?php echo Text::_('COM_JGIVE_CAMPAIGNS');?></div>
												</div>
											</div>
										</div>
										<div class="panel-footer">
											<span class="pull-left"><?php echo Text::_('COM_JGIVE_VIEW_DETAILS');?></span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>
							</div>
							<!-- /.campaigns -->

							<!--Start - total goal -->
							<div class="col-lg-3 col-md-6">
								<a href="<?php echo Route::_('index.php?option=com_jgive&view=campaigns&layout=default', false)?>">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3 ">
													<i class="fa fa-money fa-4x"></i>
												</div>
												<div class="col-xs-9 af-text-right">
													<div class="huge">
														<span>
															<?php echo $jgivehelper->getFormattedPrice($dashboard_data['campaignInfo']['total_goal_amount']);?>
														</span>
													 </div>
													<div><?php echo Text::_('COM_JGIVE_TOTAL_GOAL_AMOUNT');?></div>
												</div>
											</div>
										</div>
										<div class="panel-footer">
											<span class="pull-left"><?php echo Text::_('COM_JGIVE_VIEW_DETAILS');?></span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</div>
								</a>
							</div>
							<!-- /.total goal -->
							<!--Start - funded amount -->
							<div class="col-lg-3 col-md-6">
								<div class="panel panel-yellow">
									<a href="<?php echo Route::_('index.php?option=com_jgive&view=reports&layout=default', false)?>">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3 ">
													<!--<i class="fa fa-money fa-4x"></i>-->
													<i class="fa fa-dollar fa-4x"></i>
												</div>
												<div class="col-xs-9 af-text-right">
													<div class="huge"><span><?php echo $jgivehelper->getFormattedPrice($dashboard_data['orderInfo']['total_funded_amount']);?></span> </div>
													<div><?php echo Text::_('COM_JGIVE_FUNDED_AMOUNT');?></div>
												</div>
											</div>
										</div>
										<div class="panel-footer">
											<span class="pull-left"><?php echo Text::_('COM_JGIVE_VIEW_DETAILS');?></span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>

							</div>
							<!-- /.funded amount -->
							<!--Start - commision amount -->
							<div class="col-lg-3 col-md-6">
								<div class="panel panel-red">
									<a href="<?php echo Route::_('index.php?option=com_jgive&view=reports&layout=default', false)?>">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3 ">
													<i class="fa fa-money fa-4x"></i>
												</div>
												<div class="col-xs-9 af-text-right">
													<div class="huge"><span><?php echo $jgivehelper->getFormattedPrice($dashboard_data['orderInfo']['commision_amount']);?></span> </div>
													<div><?php echo JTEXT::_('COM_JGIVE_COMMISION_AMOUNT');?></div>
												</div>
											</div>
										</div>
										<div class="panel-footer">
											<span class="pull-left"><?php echo Text::_('COM_JGIVE_VIEW_DETAILS');?></span>
											<span class="pull-right">
												<i class="fa fa-arrow-circle-right"></i>
											</span>
											<div class="clearfix"></div>
										</div>
									</a>
								</div>
							</div>
						</div>

						<div class ="row">
							<div class="col-lg-8">
								<!-- Start - Bar Chart for Monthly Donations for past 12 months -->
								<div class="panel panel-default">
									<div class="panel-heading">
										<i class="fa fa-bar-chart-o fa-fw"></i>
										<?php echo Text::_('COM_JGIVE_MONTHLY_DONATIONS');?>
									</div>
									<div class="panel-body">
										<div id="graph-monthly-donations"></div>
										<hr class="hr hr-condensed"/>
										<div class="center">
											<?php echo Text::_('COM_JGIVE_BAR_CHART_HAXIS_TITLE');?>
										</div>
									</div>
								</div>
								<!-- End - Bar Chart for Monthly Income for past 12 months -->

								<div class="row">
									<div class="col-lg-6">
										<div class="panel panel-default">
											<div class="panel-heading">
												<i class="fa fa-pie-chart fa-fw"></i>
												<?php echo Text::_('COM_JGIVE_PERIODIC_DONATION');?>
											</div>
											<div class="panel-body">
												<!-- CALENDER ND REFRESH BTN  -->
												<div class="clearfix row">
													<div class="col-sm-12 col-lg-12 col-md-12">
														<div>
															<div class="col-md-6 form-inline">
																<label label-default class="control-label"><?php echo Text::_('COM_JGIVE_FROM_DATE');?></label>
																<?php echo HTMLHelper::_('calendar',
																	$backdate, 'fromDate', 'from', '%Y-%m-%d',
																	array('readonly' => 'true' , 'class' => 'jg-bashboard-calender-input form-control')
																); ?>
															</div>

															<div class="col-md-6 form-inline">
																<label label-default class="control-label"><?php echo Text::_('COM_JGIVE_TO_DATE');?></label>
																<?php echo HTMLHelper::_('calendar',
																	date('Y-m-d'), 'toDate', 'to', '%Y-%m-%d',
																	array('readonly' => 'true' , 'class' => 'jg-bashboard-calender-input form-control')
																); ?>
															</div>

															<div class="col-md-12 form-group mt-10">
																<div class="input-group jgive-float-right">
																	<input id="btnRefresh"
																	class="btn btn-primary"
																	type="button"
																	value="<?php echo Text::_('COM_JGIVE_GO'); ?>"
																	title="<?php echo Text::_('COM_JGIVE_DONATIONS_GO_TOOLTIP');?>"
																	style="font-weight: bold;" onclick="refreshViews();"/>
																</div>
															</div>
														</div>
													</div>
												</div>
												<!--END::CALENDER ND REFRESH BTN  -->

												<div class="clearifx">&nbsp;</div>
												<?php

												if (!$this->tot_periodicDonationsCount)
												{
													$this->tot_periodicDonationsCount = 0;
												}
												?>
												<div class="list-group">
													<span class="list-group-item">
														<i class="fa fa-money fa-fw"></i> <?php echo Text::_('COM_JGIVE_PERIODIC_DONATION_AMOUNT');?>

														<span class="text-muted small">
															<strong id="periodic_donations">
																<?php echo $jgivehelper->getFormattedPrice($this->tot_periodicDonationsCount);
																?>
															</strong>
														</span>

													</span>
												</div>
												<!-- Periodic donations - graph start -->
												<div id="graph-periodic-donations"></div>
												<hr class="hr hr-condensed"/>
												<div class="center">
													<strong class="">
														<?php echo Text::_('COM_JGIVE_PERIODIC_DONATION');?>
													</strong>
												</div>
												<!-- Periodic donations - graph end -->
											</div>
											<!-- /.panel-body -->
										</div>
									</div>
									<!-- /.col-lg-6 -->

									<div class="col-lg-6">
										<!-- Start - recent Donation Details -->
										<div class="panel panel-default">
											<div class="panel-heading">
												<i class="fa fa-list fa-fw"></i>
												<?php echo Text::_('COM_JGIVE_RECENTDONATION_DETAILS');?>
											</div>
											<div class="panel-body">
												<?php if (!empty($this->recentDonationDetails))
												{
												?>
													<table class="table table-striped table-hover" >
														<thead>
															<th><?php echo Text::_('COM_JGIVE_ORDER_ID')?></th>
															<th><?php echo Text::_('COM_JGIVE_CAMPAIGN_TITLE')?></th>
															<th><?php echo Text::_('COM_JGIVE_AMOUNT')?></th>
														</thead>
														<tbody>
														<?php
															foreach ($this->recentDonationDetails as $ord)
															{
														?>
															<tr>
																<td><a href="<?php echo Route::_('index.php?option=com_jgive&view=donation&donationid=' . $ord->id, false);?>"
																	title="<?php echo Text::_('COM_JGIVE_TOOLTIP_VIEW_ORDER_MSG');?>">
																	<?php echo $ord->order_id;?>
																	</a>
																</td>
																<td><?php echo ucwords(htmlspecialchars($ord->title, ENT_COMPAT, 'UTF-8'));?></td>
																<td><?php echo $jgivehelper->getFormattedPrice($ord->original_amount);?></td>
															</tr>
														<?php
															}?>
														</tbody>
													</table>
													<a title="<?php echo Text::_('COM_JGIVE_DONATIONS_SHOW_ALL');?>"
														class="btn btn-primary btn-small pull-right"
														href="<?php echo Route::_('index.php?option=com_jgive&view=donations');?>"
														target="_blank" >
															<?php echo Text::_('COM_JGIVE_DONATIONS_SHOW_ALL'); ?>
													</a>
												<?php
}
												else
												{
													?>
													<div class="">
														<?php echo Text::_("COM_JGIVE_NO_STORE_PREVIOUS_DONATIONS");?>
													</div>
													<?php
												} ?>
											</div>

										</div>
										<!-- End - recent Donation Details -->
										<!-- Start - pending payout -->
										<div class="panel panel-default">
											<div class="panel-heading">
												<i class="fa fa-list fa-fw"></i>
												<?php echo Text::_('COM_JGIVE_PENDING_PAYOUTS');?>
											</div>
											<div class="panel-body">
												<?php
													if (!empty($this->pendingPayouts))
													{
															?>
															<table class="table table-striped table-hover">
																<thead>
																	<th><?php echo Text::_('COM_JGIVE_NAME')?></th>
																	<th><?php echo Text::_('COM_JGIVE_REMAINING_AMOUNT')?></th>
																</thead>
															<tbody>
																	<?php
																	$count = 1;

																	foreach ($this->pendingPayouts as $allpay)
																	{
																	?>
																		<tr>
																			<td><?php echo $allpay['name'];?></td>
																			<td><?php echo $jgivehelper->getFormattedPrice($allpay['pendingPayout']); ?></td>
																		</tr>
																			<?php
																			if ($count >= 5)
																			{
																				break;
																			}
																	}
																	?>
																</tbody>
															</table>
															<a title="<?php echo Text::_('COM_JGIVE_DONATIONS_SHOW_ALL');?>"
																class="btn btn-primary btn-small pull-right"
																href="<?php echo Route::_('index.php?option=com_tjvendors&view=payouts&client=com_jgive');?>"
																target="_blank" >
																	<?php echo Text::_('COM_JGIVE_DONATIONS_SHOW_ALL'); ?>
															</a>
													<?php
													}
													else
													{
													?>
													<div class="">
														<?php echo Text::_("COM_JGIVE_DASHBORD_NO_PENDING_PAYOUTS");?>
													</div>
													<?php
													}
													?>
											</div>
										</div>
										<!-- End - pending payout -->
									</div>
									<!-- /.col-lg-6 -->
								</div>
								<div class="row">
									<div class="col-lg-12" title=" Update all campaign success status.">
											<a class="btn btn-primary"
											href="index.php?option=com_jgive&task=cp.updateAllCampaignsSuccessStatus"
											target="_blank">
												<?php echo Text::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_1'); ?>
											</a>
											 <?php echo Text::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_2'); ?>
									</div>
								</div>
								<br>
								<!-- /.row -->
							</div>
							<!-- /.col-lg-8 -->

							<div class="col-lg-4">
								<!--INFO,HELP + ETC START -->
								<div class = "panel panel-default">
									<div class = "panel-heading">
										<i class="fa fa-bullhorn"></i>
										<?php echo Text::_('COM_JGIVE'); ?>
									</div>
									<div class="panel-body">
										<div class = "">
											<blockquote class="blockquote-reverse">
												<p><?php echo Text::_('COM_JGIVE_ABOUT1');?></p>
											</blockquote>
										</div>

										<div class="row">
											<div class = "col-lg-12 col-md-12 col-sm-12">
												<p class = "pull-right"><span class="label label-info"><?php echo Text::_('COM_JGIVE_LINKS'); ?></span></p>
											</div>
										</div>

										<div class = "list-group">
											<a href = "https://techjoomla.com/table/extension-documentation/documentation-for-jgive-formerly-jomgive/ "
											class="list-group-item" target = "_blank">
											<i class="fa fa-file fa-fw i-document"></i>
											<?php echo Text::_('COM_JGIVE_DOCS');?>
											</a>

											<a href="https://techjoomla.com/documentation-for-jgive-formerly-jomgive/jgive-faqs-1" class = "list-group-item" target="_blank">
												<i class = "fa fa-question fa-fw i-question"></i> <?php echo Text::_('COM_JGIVE_FAQS');?>
											</a>
											<a href = "https://techjoomla.com/support/support-tickets" class = "list-group-item" target = "_blank">
												<i class = "fa fa-support fa-fw i-support"></i> <?php echo Text::_('COM_JGIVE_TECHJOOMLA_SUPPORT_CENTER');?>
											</a>

											<a href = "http://extensions.joomla.org/extension/jgive" class = "list-group-item" target = "_blank">
												<i class = "fa fa-bullhorn fa-fw i-horn"></i> <?php echo Text::_('COM_JGIVE_LEAVE_JED_FEEDBACK');?>
											</a>
										</div>

										<div class = "row">
											<div class = "col-lg-12 col-md-12 col-sm-12">
												<p class = "pull-right">
													<span class = "label label-info"><?php echo Text::_('COM_JGIVE_STAY_TUNNED'); ?></span>
												</p>
											</div>
										</div>

										<div class = "list-group">
											<div class = "list-group-item">
												<div class = "pull-left">
													<i class = "fa fa-facebook fa-fw i-facebook"></i>
													<?php echo Text::_('COM_JGIVE_FACEBOOK'); ?>
												</div>
												<div class = "pull-right">
													<!-- facebook button code -->
													<div id = "fb-root"></div>
													<script>
														(function(d, s, id)
															{
																var js, fjs = d.getElementsByTagName(s)[0];

																if (d.getElementById(id)) return;
																js = d.createElement(s); js.id = id;
																js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
																fjs.parentNode.insertBefore(js, fjs);
															}
															(document, 'script', 'facebook-jssdk')
														);
														</script>
													<div class = "fb-like"
														data-href = "https://www.facebook.com/techjoomla"
														data-send = "true" data-layout = "button_count"
														data-width = "250" data-show-faces = "false"
														data-font = "verdana">
													</div>
												</div>
												<div class = "clearfix">&nbsp;</div>
											</div>

											<div class="list-group-item">
												<div class="pull-left">
													<i class="fa fa-twitter fa-fw i-twitter"></i>
													<?php echo Text::_('COM_JGIVE_TWITTER'); ?>
												</div>
												<div class = "pull-right">
													<!-- twitter button code -->
													<a href = "https://twitter.com/techjoomla" class = "twitter-follow-button" data-show-count = "false">Follow @techjoomla</a>
													<script>
														!function(d,s,id)
														{
															var js,fjs = d.getElementsByTagName(s)[0];
															if(!d.getElementById(id))
															{
																js = d.createElement(s);
																js.id = id;
																js.src = "//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);
															}
														}
														(document,"script","twitter-wjs");
													</script>
												</div>
												<div class = "clearfix">&nbsp;</div>
											</div>

											<div class = "list-group-item">
												<div class = "pull-left">
													<i class = "fa fa-google fa-fw i-google"></i>
													<?php echo Text::_('COM_JGIVE_GPLUS'); ?>
												</div>
												<div class = "pull-right">
													<!-- Place this tag where you want the +1 button to render. -->
													<div class = "g-plusone" data-annotation = "inline" data-width = "120" data-href = "https://plus.google.com/102908017252609853905"></div>
													<!-- Place this tag after the last +1 button tag. -->
													<script type = "text/javascript">
													(function() {
													var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
													po.src = 'https://apis.google.com/js/plusone.js';
													var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
													})();
													</script>
												</div>
												<div class = "clearfix">&nbsp;</div>
											</div>
										</div>

										<div class = "row">
											<div class = "col-lg-12 col-md-12 col-sm-12 center">
												<?php
												$logo = '<img src = "' . Uri::root(true) . '/media/com_jgive/images/techjoomla.png" alt = "TechJoomla" class = ""/>';?>
												<span class = "center thumbnail">
													<a href = 'http://techjoomla.com/' target = '_blank'>
														<?php echo $logo;?>
													</a>
												</span>
												<p><?php echo Text::_('COM_JGIVE_COPYRIGHT'); ?></p>
											</div>
										</div>
									<!-- /.panel-body -->
								</div>
								<!-- /.panel -->
							</div>
							<!-- /.col-lg-4 -->
						</div>
						<!-- /.row -->
					</div>
					<!-- /.page-wrapper -->
				</div>
				<!-- /.wrapper -->
			</div>
			<!-- /.tjDB -->
		</div>
		<!-- /.tjBs3 -->

		<?php
		// Get data for periodic orders chart
		$statsforpie = $this->statsForPie;
		$currentmonth = '';

		$pending_donations = $confirmed_donations = $denied_donations = 0;
		$refunded_donations = $canceled_donations = 0;

		if (empty($statsforpie[0][0]) && empty($statsforpie[1][0]) && empty($statsforpie[2][0]) && empty($statsforpie[3][0]) && empty($statsforpie[4][0]))
		{
			$barchart = Text::_('COM_JGIVE_NO_STATS');
			$emptylinechart = 1;
		}
		else
		{
			if (!empty($statsforpie[0]))
			{
				$pending_donations = $statsforpie[0][0]->donations;
			}

			if (!empty($statsforpie[1]))
			{
				$confirmed_donations = $statsforpie[1][0]->donations;
			}

			if (!empty($statsforpie[2]))
			{
				$canceled_donations = $statsforpie[2][0]->donations;
			}

			if (!empty($statsforpie[3]))
			{
				$denied_donations = $statsforpie[3][0]->donations;
			}

			if (!empty($statsforpie[4]))
			{
				$refunded_donations = $statsforpie[4][0]->donations;
			}
		}

		$emptypiechart = 0;

		if (!$pending_donations and !$confirmed_donations and !$denied_donations and !$refunded_donations and !$canceled_donations)
		{
			$emptypiechart = 1;
		}
		?>
		<input type="hidden" name="pending_donations" id="pending_donations"
		value="<?php echo !empty($pending_donations) ? $pending_donations : '0';?>">

		<input type="hidden" name="confirmed_donations" id="confirmed_donations"
		value="<?php echo !empty($confirmed_donations) ? $confirmed_donations : '0';?>">

		<input type="hidden" name="denied_donations" id="denied_donations"
		value="<?php echo !empty($denied_donations) ? $denied_donations : '0';?>">

		<input type="hidden" name="refunded_donations" id="refunded_donations"
		value="<?php echo !empty($refunded_donations) ? $refunded_donations: '0';?>">

		<input type="hidden" name="canceled_donations" id="canceled_donations"
		value="<?php echo !empty($canceled_donations) ? $canceled_donations : '0';?>">

		<?php echo HTMLHelper::_('form.token');?>
</form>
<!--</div>-->

<script type = 'text/javascript'>

Joomla.submitbutton = function(task)
{
	if (task == 'cp.fixDatabase')
	{
		jgMigration.fixDatabase();
	}
}

techjoomla.jQuery(document).ready(function()
{
document.getElementById("pending_donations").value = <?php echo !empty($pending_donations) ? $pending_donations : '0'; ?>;
document.getElementById("confirmed_donations").value = <?php echo !empty($confirmed_donations) ? $confirmed_donations : '0'; ?>;
document.getElementById("denied_donations").value = <?php  echo !empty($denied_donations) ? $denied_donations : '0'; ?>;
document.getElementById("refunded_donations").value = <?php echo !empty($refunded_donations) ? $refunded_donations : '0'; ?>;
document.getElementById("canceled_donations").value = <?php  echo !empty($canceled_donations) ?$canceled_donations : '0'; ?>;

drawPeriodicDonationsChart();
});
<?php
if ($barChartData)
{
?>
		drawBarChart();
		function drawBarChart()
		{
			Morris.Bar({
				element: 'graph-monthly-donations',
				data:
					<?php
						$dataArray = "[";

						for ($i = 0; $i < count($AllMonthName_final); $i++)
						{
							$dataArray .= "{period : '" . $AllMonthName_final[$i] . "', donationTotal : " . $month_amt_val[$AllMonthName_final[$i]] . "},";
						}

						$dataArray .= "]";
					echo $dataArray;

					?>,
				xkey: 'period',
				ykeys: ['donationTotal'],
				labels: ['<?php echo Text::_('COM_JGIVE_BAR_CHART_VAXIS_TITLE');?>'],
				barColors: ['#428bca'],
				barRatio: 0.4,
				xLabelAngle: 35,
				hideHover: 'auto',
				resize:true
			});
		}
	<?php
}
else
{
?>
	techjoomla.jQuery('#graph-monthly-donations').html("<div class = 'center'><?php echo Text::_("COM_JGIVE_NO_STORE_PREVIOUS_ORDERS");?></div>");
<?php
}
?>
	function drawPeriodicDonationsChart()
	{
		techjoomla.jQuery('#graph-periodic-donations').html('');

		var pending_donations = document.getElementById('pending_donations').value;
		var confirmed_donations = document.getElementById('confirmed_donations').value;
		var denied_donations = document.getElementById('denied_donations').value;
		var refunded_donations = document.getElementById('refunded_donations').value;
		var canceled_donations = document.getElementById('canceled_donations').value;

		if (pending_donations > 0 || confirmed_donations > 0 || denied_donations > 0 || refunded_donations > 0 || canceled_donations > 0)
		{
			Morris.Donut({
					element: 'graph-periodic-donations',
					data: [
								{
									label: "<?php echo Text::_("COM_JGIVE_PENDING_DONATIONS");?>",
									value: pending_donations
								},
								{
									label: "<?php echo Text::_("COM_JGIVE_CONFIRMED_DONATIONS");?>",
									value: confirmed_donations
								},
								{
									label: "<?php echo Text::_("COM_JGIVE_DENIED_DONATIONS");?>",
									value: denied_donations
								},
								{
									label: "<?php echo Text::_("COM_JGIVE_REFUNDED_DONATIONS");?>",
									value: refunded_donations
								},
								{
									label: "<?php echo Text::_("COM_JGIVE_CANCELED_DONATIONS");?>",
									value: canceled_donations
								}],
					colors: ["#f0ad4e", "#5cb85c", "#428bca", "#d9534f", "#8A2BE2"],
					resize: true
				});
		}
		else
		{
			techjoomla.jQuery('#graph-periodic-donations').html("<div class = 'center'><?php echo Text::_("COM_JGIVE_NO_STORE_PREVIOUS_ORDERS");?></div>");
		}
	}
</script>
