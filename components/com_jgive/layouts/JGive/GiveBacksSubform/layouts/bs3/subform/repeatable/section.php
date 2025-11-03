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
	<?php
	if (!empty($buttons))
	{
	?>
	<div class="col-xs-12 btn-toolbar text-right mt-15">
		<div class="btn-group pull-right">
			<?php
				if (!empty($buttons['add']))
				{
			?>
					<a class="btn btn-sm btn-success fa fa-plus group-add-<?php echo $unique_subform_id; ?>"><span class="icon-plus"></span> </a>
			<?php
				}
			?>
			<?php
				if (!empty($buttons['remove']))
				{
			?>
					<a class="btn-sm btn btn-danger fa fa-trash-o group-remove-<?php echo $unique_subform_id; ?>"><span class="icon-minus"></span> </a>
			<?php
				}
			?>
			<?php
				if (!empty($buttons['move']))
				{
			?>
					<a class="btn btn-sm btn-basic button btn-primary group-move-<?php echo $unique_subform_id; ?>"><span class="icon-move"></span> </a>
			<?php
				}
			?>
		</div>
	</div>
	<?php
	}
	?>
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
