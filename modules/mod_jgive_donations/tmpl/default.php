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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;

$lang     = Factory::getLanguage();
$lang->load('mod_jgive_donations', JPATH_ROOT);

// Load component css
HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive.css');

// Load Campaigns helper
$helperPath = JPATH_SITE . '/components/com_jgive/helper.php';

if (!class_exists('jgiveFrontendHelper'))
{
	JLoader::register('jgiveFrontendHelper', $helperPath);
	JLoader::load('jgiveFrontendHelper');
}

$jgiveFrontendHelper = new jgiveFrontendHelper;
?>

<div class="tjBs3 <?php echo $params->get('moduleclass_sfx'); ?>">
<?php
	$module_for = $params->get('module_for');

	if ($module_for == 'my_donations')
	{
		if (!$userid)
		{
			echo Text::_('MOD_JGIVE_DONATION_LOGIN');
			echo '</div>';

			return;
		}
	}
?>

	<table class="table table-hover">
		<?php
		if (count($result) > 0 )
		{
		?>
			<thead>
				<tr>
					<th>
						<?php
							if ($module_for == 'my_donations')
							{
								echo Text::_('MOD_JGIVE_DONATION_ORDER_ID');
							}
							elseif ($module_for == 'last_donations' || $module_for == 'top_donations')
							{
								echo Text::_('MOD_JGIVE_DONATION_DONOR');
							}
						?>
					</th>
					<th>
						<?php echo Text::_('MOD_JGIVE_DONATION_CAMP_NAME');?>
					</th>
					<th>
						<?php echo Text::_('MOD_JGIVE_DONATION_AMOUNT_DONATED');?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$i = 0;
				$total_amount = 0;

				foreach ($result as $row)
				{
					$singleCampaignItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default&id=' . $row->cid);
				?>
					<tr>
						<td class="small">
							<div>
								<?php
									if ($module_for == 'my_donations')
									{
										echo $row->order_id;
									}
									elseif ($module_for == 'last_donations' || $module_for == 'top_donations')
									{
										if (!$row->avatar)
										{
											// If no avatar, use default avatar
											$row->avatar = Uri::root(true) . '/media/com_jgive/images/default_avatar.png';
										}

										if ($row->donor_id != 0)
										{
											$title = $row->first_name;
								?>
											<a href="<?php echo !empty($row->profile_url) ? $row->profile_url : '';  ?>" target="_blank">
												<img class="img-circle" src="<?php echo $row->avatar; ?>" title="<?php echo $title; ?>" width="20" height="20" />
											</a>
								<?php
										}
										else
										{
								?>
											<img class="img-circle" src="<?php echo $row->avatar; ?>" 
											title="<?php echo Text::_('MOD_JGIVE_DONATION_GUEST'); ?>" width="20" height="20"/>
								<?php
										}
									}
								?>
							</div>
						</td>
						<td class="small">
							<div>
							<?php $row->title = htmlspecialchars($row->title, ENT_COMPAT, 'UTF-8');?>
								<a href="<?php echo Uri::root() . substr(
																			Route::_(
																						'index.php?option=com_jgive&view=campaign&layout=default&id=' . $row->cid . '&Itemid=' . $singleCampaignItemid),
																						strlen(Uri::base(true)
																					) + 1
																		);?>">
									<?php 
									if (strlen($row->title) >= 15)
									{
										echo substr($row->title, 0, 10) . '...';
									}
									else
									{
										echo $row->title;
									}
									?>
								</a>
							</div>
						</td>
						<td class = "small" style="word-break: break-word;">
							<div>
							<?php
								$com_jgive_params = ComponentHelper::getParams('com_jgive');
								echo $diplay_amount_with_format = $jgiveFrontendHelper->getFormattedPrice($row->original_amount);
								$total_amount = $total_amount + $row->amount;
							?>
							</div>
						</td>
					</tr>
			<?php
				}?>
			</tbody>
		<?php
		}

		if (count($result) <= 0 )
		{ ?>
			<tbody>
				<tr>
					<td colspan = "3"  >
						<?php echo Text::_("COM_JGIVE_NO_DONATION");?>
					</td>
				</tr>
			</tbody>
		<?php
		}
		elseif ($module_for == 'my_donations')
		{
			if ($totalMyDonationsCount > $params->get('no_of_record_show'))
			{
		?>
				<tbody>
					<tr>
						<td colspan="3" class="small" style="text-align:right;">
							<?php
								$myDonationItemid = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations'); ?>
								<a href="<?php echo Uri::root() . substr(Route::_('index.php?option=com_jgive&view=donations&Itemid='. $myDonationItemid), strlen(Uri::base(true)) + 1)?>">
									<b><?php echo Text::_('MOD_JGIVE_DONATION_ALL');?></b>
								</a>
						</td>
					</tr>
				</tbody>
		<?php
			}
		}?>
	</table>
</div>

