<?php
/**
 * @package     JGive
 * @subpackage  mod_jgive_category_progres
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$progresslabel       = $recPer . '%';

if ($recPer > 100)
{
	$recPer = 100;
}
?>

<div id ="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div class="row">
		<div class="col-sm-2 text af-text-center">
			<strong>
				<?php echo Text::_("MOD_JGIVE_CATEGORY_PROGRESS_AMOUNT_RAISED") . "<br/>" . $formattedAmountRaised;?>
			</strong>
		</div>
		<div class="col-sm-8">
			<div class="progress" >
				<div class="progress-bar progress-bar-striped" style="width:<?php echo $recPer;?>%;">
					<strong class="com_jgive_progress_text"><?php echo $progresslabel;?></strong>
				</div>
			</div>
		</div>
		<div class="col-sm-2 text af-text-center">
			<strong>
				<?php echo Text::_("MOD_JGIVE_CATEGORY_PROGRESS_GOAL_AMOUNT") . "<br/>" . $formattedGoalAmount;?>
			</strong>
		</div>
	</div>
</div>
