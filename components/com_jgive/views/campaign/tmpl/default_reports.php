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

use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$i = 1;

if (!empty($this->reports))
{
	$token            = Session::getFormToken();
	$campaignId       = $this->cdata['campaign']->id;
	$reportTotalChars = $this->params->get('report_total_chars');
?>
	<div class="clearfix"></div>
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 no-more-tables" id="reports_list">
			<?php
			foreach ($this->reports as $report)
			{
				$reportId = $report->id;
				$mediaId  = $report->media_id;

				$detailsReportLink = Route::_(
					'index.php?option=com_jgive&task=report.view&id='
					. $report->id
					. '&Itemid='
					. $this->cdata['otherData']->singleCampItemid
				);

				// Remove HTML tags and white spaces from the start and end of the report description
				$reportDescription = trim(strip_tags($report->description));

				// Crop the report description if its length is greater than that specified in the config
				if (strlen($reportDescription) > $reportTotalChars)
				{
					$reportDescription = substr($reportDescription, 0, $reportTotalChars) . '...';
				}

				$reportImage = Uri::root() . 'media/com_jgive/images/logo.png';

				if ($report->source != null)
				{
					$reportImage = $report->source->media_s;
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
								<img src="<?php echo $reportImage;?>" class="img-responsive center-block">
							</div>
							<div class="col-xs-12 col-sm-12 col-md-9">
								<div class="row">
									<div class="col-xs-10 col-sm-10 col-md-10">
										<?php
											echo HTMLHelper::date($report->created_on, $this->params->get('date_format', 'j  M  Y'), true);
										?>
									</div>
									<div class="col-xs-2 col-sm-2 col-md-2 af-text-right">
										<?php
										if ($this->enableReports
											&& $this->cdata['campaign']->creator_id == $this->cdata['otherData']->loggedUserId
											&& $this->cdata['otherData']->canEdit)
										{
											$editReportLink = Route::_('index.php?option=com_jgive&task=reportform.edit&id='
												. $report->id .
												'&cid=' .
												$this->cdata['campaign']->id
											);
										?>
											<a class="report__link--nounderline" href="<?php echo $editReportLink; ?>" title="<?php echo Text::_('COM_JGIVE_REPORT_EDIT') ?>">
												<i class="fa fa-edit"></i>
											</a>
											<a class="report__link--nounderline" href="Javascript: void(0);" title="<?php echo Text::_('COM_JGIVE_REPORT_DELETE') ?>">
												<i class="fa fa-trash" data-rid="<?php echo $reportId ?>" data-cid="<?php echo $campaignId ?>"
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
				if ($i >= 5)
				{
					break;
				}

				$i++;
			}
			?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php
}
else
{
	echo JTEXT::_('COM_JGIVE_NO_REPORTS');
}
?>
<input type="hidden" id="campaign_id" name="campaign_id" value="<?php echo $this->cdata['campaign']->id; ?>" />
<input type="hidden" id="limit_offset" name="limit_offset" value="<?php echo $i; ?>" />
<?php
if (!empty($this->reports) && $this->total_reports > 5)
{
?>
	<button id="btn_showMorePic" class="btn btn-info btn-md" type="button" onclick="jgive.report.showMoreReports()">
		<?php
			echo Text::_('COM_JGIVE_SHOW_MORE_DONORS');
		?>
	</button>
<?php
}
?>

<script type="text/javascript">
	var jgive_baseurl = "<?php echo Uri::root(); ?>";
	const isAdmin     = <?php echo $this->isAdmin; ?>;
</script>
