<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);

?>

<div class="row subform-repeatable-group subform-repeatable-group-<?php echo $unique_subform_id; ?>"
	data-base-name="<?php echo $basegroup; ?>"
	data-group="<?php echo $group; ?>">
	<?php if (!empty($buttons)) { ?>
		<div class="btn-toolbar float-end m-2">
			<div class="btn-group">
				<?php
					if (!empty($buttons['add']))
					{
					    ?>
						<button type="button" class="group-add btn btn-sm btn-success me-2"><span class="icon-plus icon-white" aria-hidden="true"></span> </button>
				<?php
					}
				?>
				<?php
					if (!empty($buttons['remove']))
					{
					    ?>
						<button type="button" class="group-remove btn btn-sm btn-danger me-2" ><span class="icon-minus icon-white" aria-hidden="true"></span> </button>
				<?php
					}
				?>
				<?php
					if (!empty($buttons['move']))
					{
					    ?>
						<button type="button" class="group-move btn btn-sm btn-primary" ><span class="icon-arrows-alt icon-white" aria-hidden="true"></span> </button>
				<?php
					}
				?>
			</div>
		</div>
	<?php } ?>
	<div class="col-xs-12">
		<div class="row">
		<!-- All field render -->
		<?php
			foreach ($form->getFieldset() as $field)
			{
				$html = $field->renderField();

				if (strpos($html, 'subform_givebacks_title') !== false)
				{
				?>
					<div class="col-xs-12 col-sm-7">
						<?php echo $html;?>
					</div>
				<?php
				}

				if (strpos($html, 'subform_givebacks_id') !== false)
				{
					echo $html;
				}

				if (strpos($html, 'subform_givebacks_amount') !== false)
				{
				?>
					<div class="col-xs-12 col-sm-7">
						<?php echo $html; ?>
					</div>
				<?php
				}

				if (strpos($html, 'subform_givebacks_total_quantity') !== false)
				{
				?>
					<div class="col-xs-12 col-sm-5">
						<?php echo $html; ?>
					</div>
				<?php
				}

				if (strpos($html, 'subform_givebacks_description') !== false)
				{
				?>
					<div class="col-xs-12">
						<?php echo $html; ?>
					</div>
				<?php
				}

				if (strpos($html, 'subform_givebacks_giveback_image') !== false)
				{
				?>
					<div class="col-xs-12">
						<?php echo $html; ?>
					</div>
				<?php
				}

				if (strpos($html, 'giveback_img') !== false)
				{
				?>
					<div class="col-xs-12">
						<?php echo $html; ?>
					</div>
				<?php
				}
			}
		?>
		</div>
	</div>
</div>
