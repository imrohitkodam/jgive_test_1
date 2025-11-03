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

?>
<ul class="nav nav-tabs mt-3">
<?php
if (!empty($this->form_extra))
{
	$fieldSetNames = array();

	foreach ($this->form_extra->getFieldsets() as $fieldsets => $fieldset)
	{
		if (!in_array($fieldset->name, $fieldSetNames))
		{
			$fieldSetNames[] = $fieldset->name;
		}
	}

	foreach ($fieldSetNames as $key => $fieldSetName)
	{
		?>
		<li id="<?php echo str_replace(' ', '', $this->escape($fieldSetName)) . 'id';?>" class="nav-item <?php echo ($key == 0) ? 'active' : '' ?>">
			<a class="nav-link <?php echo ($key == 0) ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tabId<?php echo str_replace('.','', str_replace(' ', '', $this->escape($fieldSetName)));?>" data-bs-toggle="tab">
				<?php echo str_replace(' ', '', $this->escape($fieldSetName));?></a>
			</li>
		<?php
	}
}
?>
</ul>
<?php

if (!empty($this->form_extra))
{
	$fieldSetNames = array();

	foreach ($this->form_extra->getFieldsets() as $fieldsets => $fieldset)
	{
		if (!in_array($fieldset->name, $fieldSetNames))
		{
			$fieldSetNames[] = $fieldset->name;
		}
	}
	?>

	<div class="tab-content mt-3">
	<?php

		foreach ($fieldSetNames as $key => $fieldSetName)
		{
		?>
			<div class="tab-pane <?php echo ($key == 0) ? 'active show' : '' ?>" id="tabId<?php echo str_replace('.','', str_replace(' ', '', $this->escape($fieldSetName)));?>">
			<?php
				foreach ($this->form_extra->getFieldsets() as $fieldsets => $fieldset)
				{
					if ($fieldset->name == $fieldSetName)
					{
						$fieldsArray = array();

						foreach ($this->form_extra->getFieldset($fieldset->name) as $field)
						{
							$fieldsArray[] = $field;
						}

						foreach ($fieldsArray as $field)
						{
							// If the field is hidden, only use the input.
							if ($field->hidden)
							{
								echo $field->input;
							}
							else
							{
							?>
								<div class="control-group">
									<div class="control-label">
										<label title="<?php echo $field->title;?>">
											<?php echo $this->escape($field->getAttribute('label'));

											if ($field->required)
											{
											?>
												<span class="star">&#160;*</span>
											<?php
											}
											?>
										</label>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php
							}
							?>
							<div class="clearfix">&nbsp;</div>
						<?php
						}
					}
				}
			?>
			</div>
			<?php
		}
	?>
	</div>
	<?php
}
