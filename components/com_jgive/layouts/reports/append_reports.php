<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (empty($displayData))
{
	return false;
}

$token            = Session::getFormToken();
$params           = ComponentHelper::getParams('com_jgive');
$reportTotalChars = $params->get('report_total_chars');

$jgiveFrontendHelper = new jgiveFrontendHelper;
$campaignItemId 	 = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default');

foreach ($displayData['data'] as $report)
{
	$reportImage = 'media/com_jgive/images/logo.png';

	$detailsReportLink = Route::_(
		'index.php?option=com_jgive&task=report.view&id='
		. $report->id
		. '&Itemid='
		. $campaignItemId
	);

	// Remove HTML tags and white spaces from the start and end of the report description
	$reportDescription = strip_tags(trim($report->description));

	// Crop the report description if its length is greater than that specified in the config
	if (strlen($reportDescription) > $reportTotalChars)
	{
		$reportDescription = substr($reportDescription, 0, $reportTotalChars) . '...';
	}

	if ($report->source != null)
	{
		$reportImage = $report->source;
	}
	?>
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12">
				<a href="<?php echo $detailsReportLink; ?>">
					<b><?php echo htmlspecialchars($report->title, ENT_COMPAT, 'UTF-8'); ?></b>
				</a>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-12">
				<div class="row af-mb-20">
					<div class="col-xs-12 col-sm-12 col-md-3">
						<img src="<?php echo Uri::root() . $reportImage ?>" class="img-responsive center-block">
					</div>
					<div class="col-xs-12 col-sm-12 col-md-9">
						<div class="row">
							<div class="col-xs-10 col-sm-10 col-md-10">
								<?php
									echo HTMLHelper::date($report->created_on, $params->get('date_format', 'j  M  Y'), true);
								?>
							</div>
							<div class="col-xs-2 col-sm-2 col-md-2 af-text-right">
								<?php
								if ($displayData['extra_data']['is_report_enabled']
									&& $displayData['extra_data']['campaign_creator_id'] == $displayData['extra_data']['loggedin_user_id']
									&& $displayData['extra_data']['can_user_edit'])
								{
									$editReportLink = Route::_(
										'index.php?option=com_jgive&view=reportform&task=reportform.edit&id=' .
										$report->id .
										'&cid=' .
										$report->campaign_id .
										'&Itemid='
										. $campaignItemId
									);
									?>

									<a class="report__link--nounderline" href="<?php echo $editReportLink; ?>" title="<?php echo Text::_('COM_JGIVE_REPORT_EDIT') ?>">
										<i class="fa fa-edit"></i>
									</a>
									<a class="report__link--nounderline" href="Javascript: void(0);" title="<?php echo Text::_('COM_JGIVE_REPORT_DELETE') ?>">
										<i class="fa fa-trash" data-rid="<?php echo $report->id ?>" data-cid="<?php echo $report->campaign_id ?>"
										onclick="jgive.report.delete('reportform.delete', this, '<?php echo $token ?>')"></i>
									</a>

								    <?php
								}
								?>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 reports__content--wordwrap">
								<span><?php echo $reportDescription; ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
    <?php
}
