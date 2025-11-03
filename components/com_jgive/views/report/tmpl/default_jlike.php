<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;

$jgiveFrontendHelper = new jgiveFrontendHelper;
$jlikehtml = null;

if (file_exists(JPATH_SITE . '/' . 'components/com_jlike/helper.php'))
{
	$showComments    = -1;
	$showLikeButtons = 1;

	// Update report url for action log
	$report_url = $jgiveFrontendHelper->getReportUrl($this->item->id, true, false);

	$jlikehtml = (string) $jgiveFrontendHelper->DisplayjlikeButton(
		$report_url,
		$this->item->id,
		$this->item->title,
		$showComments,
		$showLikeButtons,
		'com_jgive.report'
	);
?>
	<div class="col-xs-4 col-sm-6 col-md-12 col-lg-12">
		<?php echo $jlikehtml;?>
	</div>
<?php
}
