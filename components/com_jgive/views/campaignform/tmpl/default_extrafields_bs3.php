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

// Code to get TJ-fileds field form - end
?>
<ul class="nav nav-tabs af-mt-10">
<?php
if (!empty($this->form_extra))
{
	$fieldsArray = array();

	foreach ($this->form_extra->getFieldsets() as $fieldsets => $fieldset)
	{
		if (!in_array($fieldset->name, $fieldsArray))
		{
			$fieldsArray[] = $fieldset->name;
		}
	}

	foreach ($fieldsArray as $key => $fieldSetName)
	{
		?>
		<li id="<?php echo str_replace(' ', '', $this->escape($fieldSetName)) . 'id';?>" class="nav-item <?php echo ($key == 0) ? 'active' : '' ?>">
			<a class="nav-link <?php echo ($key == 0) ? 'active' : '' ?>" data-toggle="tab" data-target="#tabId<?php echo str_replace('.','', str_replace(' ', '', $this->escape($fieldSetName)));?>" data-toggle="tab">
				<?php echo str_replace(' ', '', $this->escape($fieldSetName));?></a>
		</li>
		<?php
	}
}
?>
</ul>
<div class="tab-content af-mt-10">
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
		<div class="tab-pane af-mt-10 <?php echo ($key == 0) ? 'active show' : '' ?>" id="tabId<?php echo str_replace('.','', str_replace(' ', '', $this->escape($fieldSetName)));?>">
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
							<div class="form-group row">
								<div class="form-label col-md-4">
									<label title="<?php echo $field->title;?>">
										<?php echo $this->escape($field->getAttribute('label'));

										if (isset($required))
										{
										?>
											<span class="star">&#160;*</span>
										<?php
										}
										?>
									</label>
								</div>
								<div class="col-md-8">
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
}
?>
</div>
