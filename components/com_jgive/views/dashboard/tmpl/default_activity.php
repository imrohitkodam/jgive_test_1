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

$limit = 3;

$videoTheme = $imageTheme = "";
$givebackTheme = "," . "'campaign.addgiveback'";

if ($this->promoterDashboardData['params']['video_gallery'])
{
	// If video uploading configuration on then only show the video activities
	$videoTheme = "," . "'campaign.addvideo'";
}

if ($this->promoterDashboardData['params']['img_gallery'])
{
	// If image uploading configuration on then only show the image activities
	$imageTheme = "," . "'campaign.addimage'";
}

if ($this->promoterDashboardData['params']['show_selected_fields'] == 1)
{
	if (!empty($this->promoterDashboardData['params']['creatorfield']) && in_array("give_back", $this->promoterDashboardData['params']['creatorfield']))
	{
		$givebackTheme = "";
	}
}

foreach ($this->promoterDashboardData['activityData'] as $activity)
{
	$campIds[] = "'" . $activity['id'] . "'";
}

if (isset($campIds))
{
$campIdstr = implode(',', $campIds);
?>
<div class="col-xs-12 col-sm-6 col-md-3 activity af-mb-10">
	<h6 class="af-font-bold text-uppercase"><strong><?php echo strtoupper(Text::_("COM_JGIVE_VENDOR_CAMPAIGNS_ACTIVITY"));?></strong></h6>
	<div id="tj-activitystream" tj-activitystream-widget tj-activitystream-client="com_jgive" tj-activitystream-theme="dashboardfeed" tj-activitystream-bs="bs3"
	tj-activitystream-type="'campaign.extended','jgive.donation','campaign.completed','jgive.addcampaign','campaign.addreport','jgive.textpost'<?php echo $givebackTheme . $videoTheme . $imageTheme;?>"
	tj-activitystream-target-id="<?php echo $campIdstr;?>" tj-activitystream-limit="<?php echo $limit;?>" tj-activitystream-language="<?php echo $this->promoterDashboardData['language']->lang;?>" class="py-2 px-3">
	</div>
</div>
<?php
}?>
