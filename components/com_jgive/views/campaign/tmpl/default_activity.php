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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Creating Object of FrontendHelper class
$jgiveFrontendHelper = new jgiveFrontendHelper;

$videoTheme = $imageTheme = $givebackTheme = "";

if ($this->cdata['params']['video_gallery'])
{
	$videoTheme = "," . "'campaign.addvideo'";
}

if ($this->cdata['params']['img_gallery'])
{
	$imageTheme = "," . "'campaign.addimage'";
}

if (isset($this->cdata['params']['show_selected_fields']) && is_array($this->cdata['params']['creatorfield']) && count($this->cdata['params']['creatorfield']) && !in_array("give_back", $this->cdata['params']['creatorfield']))
{
	$givebackTheme = "," . "'campaign.addgiveback'";
}
else
{
	$givebackTheme = "," . "'campaign.addgiveback'";
}

$user = Factory::getUser();
$limit = 10;
?>
<div class="col-xs-12 col-sm-12 col-md-12 af-pt-25">
	<form name="post-activity" method="post">
	<div class="row">
	<?php
		if ($user->id == $this->cdata['campaign']->creator_id)
		{
		?>
		<div class="feed-item-cover campaigns todays-activity">
			<div class="date col-xs-3 col-sm-2">
				<?php echo Text::_("COM_JGIVE_ACTIVITY_TODAY");?>
				</br>
				<?php echo HTMLHelper::Date('now', 'd, M');?>
			</div>
			<div class="feed-item col-xs-9 col-sm-10">
				<div class="feed-item-inner">
					<div class="form-group">
						<input
							class="form-control input-lg"
							id="activity-post-text"
							name="activity-post-text"
							autocomplete="off"
							placeholder="<?php echo Text::_("COM_JGIVE_ACTIVITY_TODAY_TEXT");?>"
							maxlength="300">
						</input>
						<div id="activity-post-text-length" class="pull-right clearfix"></div>
					</div>
					<div class="form-group af-pt-5">
						<button
							type="submit"
							id="postactivity"
							class="btn btn-primary pull-right clearfix">
							<?php echo Text::_("COM_JGIVE_TEXT_ACTIVITY_POST");?>
						</button>
					</div>
				</div>
			</div>
		</div>
	<?php
		}
		?>
		<div id="tj-activitystream" tj-activitystream-widget
		tj-activitystream-theme="campaignfeed" tj-activitystream-client="com_jgive" tj-activitystream-bs="<?php echo JGIVE_LOAD_BOOTSTRAP_VERSION;?>"
		tj-activitystream-type= "'campaign.extended','jgive.donation','campaign.completed','jgive.addcampaign','campaign.addreport','jgive.textpost'<?php echo $videoTheme . $imageTheme . $givebackTheme;?>"
		tj-activitystream-target-id="<?php echo $this->cdata['campaign']->id;?>" tj-activitystream-limit="<?php echo $limit;?>" tj-activitystream-language="<?php echo $this->cdata['language']->lang;?>">
		</div>
		<input type="hidden" name="option" value="com_jgive"></input>
		<input type="hidden" name="task" value="campaign.addPostedActivity"></input>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	</form>
</div>
