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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

$jgiveFrontendHelper = new jgiveFrontendHelper;
?>
<?php
if (!empty($this->promoterDashboardData['topDonorsData']))
{
	?>
<!----Top Donars----->
<div class="col-xs-12 col-sm-6 col-md-3 donar">
	<h6 class="text-uppercase af-font-bold"><?php echo strtoupper(Text::_("COM_JGIVE_DASHBOARD_TOP_5_DONORS"));?>
		<?php
		if (count($this->promoterDashboardData['topDonorsData']) > 4)
		{
			$all_donors_list_path = JURI::root() . substr(Route::_('index.php?option=com_jgive&view=donors&Itemid=' . $this->promoterDashboardData['otherData']->donorsItemid), strlen(Uri::base(true)) + 1);
		?>
			<span class="pull-right"><a href="<?php echo $all_donors_list_path;?>">View All</a></span>
		<?php
		}
		?>
	</h6>
	<?php
	if (!empty($this->promoterDashboardData['topDonorsData']))
	{
	?>
			<?php
			$i = 1;

			foreach ($this->promoterDashboardData['topDonorsData'] as $donor)
			{
				if (!$donor->avatar)
				{
					// If no avatar, use default avatar
					$donor->avatar = Uri::root(true) . '/media/com_jgive/images/default_avatar.png';
				}

				if ($donor->annonymous_donation)
				{
					$title     = Text::_("COM_JGIVE_DONOR_ANNONYMOUS_NAME") . ' - ' . $jgiveFrontendHelper->getFormattedPrice($donor->donation_amount);
					$donorName = Text::_("COM_JGIVE_DONOR_ANNONYMOUS_NAME");

					// If annonymous_donation, use annonymous avatar, reset url to blank
					$donor->avatar      = Uri::root(true) . '/media/com_jgive/images/annonymous.png';
					$donor->profile_url = '#';
				}
				else
				{
					if ($donor->donor_type == 'org' && !empty($donor->org_name))
					{
						$title     = $donor->org_name . $jgiveFrontendHelper->getFormattedPrice($donor->donation_amount);
						$donorName = (strlen($donor->org_name) > 13) ? substr($donor->org_name, 0, 10) . '...' : $donor->org_name;
					}
					else
					{
						$title     = $donor->first_name . ' ' . $donor->last_name . ' - ' . $jgiveFrontendHelper->getFormattedPrice($donor->donation_amount);
						$donorName = $donor->first_name . ' ' . $donor->last_name;
						$donorName = (strlen($donorName) > 13) ? substr($donorName, 0, 10) . '...' : $donorName;
					}
				}
				?>
				<div class="af-pt-10">
					<div class="media af-my-5 border-b d-flex align-items-center border-bottom pb-2 mb-2 mx-2">
						<div class="media-left media-middle me-1">
							<?php
							if (!empty($donor->profile_url) && $donor->user_id != 0)
							{
							?>
								<a href="<?php echo $donor->profile_url; ?>">
									<img
										src="<?php echo $donor->avatar; ?>"
										class="img-circle donar_img"
										alt="<?php echo $title;?>"
										title="<?php echo $title;?>"
										height="25px"/>
								</a>
							<?php
							}
							else
							{
							?>
								<img
									src="<?php echo $donor->avatar; ?>"
									class="img-circle donar_img"
									alt="<?php echo $title;?>"
									title="<?php echo $title;?>"
									height="25px"/>
							<?php
							}?>
						</div>
						<div class="media-body flex-grow-1 d-flex justify-content-around">
							<?php echo $donorName; ?>
							<span class="pull-right fw-bold"><?php echo $jgiveFrontendHelper->getFormattedPrice($donor->donation_amount)?></span>
						</div>
					</div>
				</div>
				<?php
				// Show only first 4 top donors
				if ($i++ == 5)
				{
					break;
				}
			}
		?>
	<?php
	}?>
</div>
<?php
}
