<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::import('integrations', JPATH_SITE . '/components/com_jgive/helpers');

/**
 * Supports an HTML select list of jomsocial groups
 *
 * @since  2.1
 */
class JFormFieldGroupId extends JFormField
{
	/**
	 * Methods to display jomsocial group
	 *
	 * @return string $html
	 *
	 * @since    2.1
	 */
	public function getInput()
	{
		$JgiveIntegrationsHelper = new JgiveIntegrationsHelper;

		$result = $JgiveIntegrationsHelper->getJS_usergroup('');
?>
		<div class="control-group">
			<label class="control-label" for="js_group">
				<?php echo HTMLHelper::tooltip(Text::_('COM_JGIVE_SELECT_GROUP_TP'), Text::_('COM_JGIVE_SELECT_GROUP'), '', Text::_('COM_JGIVE_SELECT_GROUP'));?>
			</label>
			<div class="controls">
				<select id="jform_js_groupid" name="jform[js_groupid]" class="">
					<option value="0"><?php echo Text::_('COM_JGIVE_SELECT_JS_GROUP'); ?></option>
				<?php
					foreach ($result as $grp)
					{
						$selected = '';

						if ($grp['id'] == $this->value)
						{
							$selected = 'selected="selected"';
						}
					?>
						<option value="<?php echo $grp['id']; ?>"
						<?php echo $selected;?> >
						<?php echo $grp['title'];?></option>
				<?php
					}
					?>
				</select>
			</div>
		</div>
<?php
	}
}
