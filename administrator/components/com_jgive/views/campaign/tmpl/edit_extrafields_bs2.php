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
use Joomla\CMS\HTML\HTMLHelper;

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

	echo HTMLHelper::_('bootstrap.startTabSet', 'extraFieldtype', array('active' => '0extraFieldType'));

		foreach ($fieldSetNames as $key => $fieldSetName)
		{
			$label       = str_replace(' ', '', $this->escape($fieldSetName));
		    echo HTMLHelper::_('bootstrap.addTab', 'extraFieldtype', $key . 'extraFieldType', $label);
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
		   	echo HTMLHelper::_('bootstrap.endTab');
		}
	echo HTMLHelper::_('bootstrap.endTabSet');
}