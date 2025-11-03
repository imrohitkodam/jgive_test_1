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

if ($this->extraData)
{
	if (count($this->extraData))
	{
	?>
		<table class="table table-striped table-bordered table-hover">
			<?php
			foreach ($this->extraData as $f)
			{
			?>
				<tr>
					<td>
						<strong><?php echo $f->label;?></strong>
					</td>
					<td>
						<?php
						if (!is_array($f->value))
						{
						?>
							<?php echo $f->value; ?>
						<?php
						}
						else
						{
						?>
							<?php
							foreach ($f->value as $option)
							{
							?>
								<?php echo $option->options; ?>
								<br/>
							<?php
							}
							?>
						<?php
						}
						?>
					</td>
				</tr>
			<?php
			}
			?>
		</table>
	<?php
	}
}
