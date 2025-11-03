<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Import Joomla modelitem library

/**
 * JFormFieldModal_Single class.
 *
 * @package  JGive
 * @since    1.8
 */
class JFormFieldModal_Single extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'Modal_Single';
	/**
	 * Method to get the field input markup
	 */

	/**
	 * Method to get the field input markup
	 *
	 * @return  Array
	 *
	 * @since   1.8
	 */
	protected function getInput()
	{
		// Load modal behavior
		if (JVERSION < '4.0.0')
		{
			JHtml::_('behavior.modal', 'a.modal');
		}
		else
		{
			HTMLHelper::_('bootstrap.renderModal', 'a.modal');
		}

		// Build the script
		$script   = array();
		$script[] = '    function jSelectCampaign_' . $this->id . '(id, title, object) {';
		$script[] = '        document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '        document.getElementById("' . $this->id . '_name").value = title;';

		if (JVERSION < '4.0.0')
		{
			$script[] = 'document.querySelector("#campaignModal .close").click() ';
		}
		else
		{
			$script[] = 'document.querySelector("#campaignModal .btn-close").click() ';
		}

		$script[] = '    }';

		// Add to document head
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display
		$html = array();

		$link = 'index.php?option=com_jgive&amp;view=campaigns&amp;layout=modal' . '&amp;tmpl=component&amp;function=jSelectCampaign_' . $this->id;

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title');
		$query->from('#__jg_campaigns');
		$query->where('id=' . (int) $this->value);
		$db->setQuery($query);

		if (!$title = $db->loadResult())
		{
		}

		if (empty($title))
		{
			$title = Text::_('COM_JGIVE_FIELD_SELECT_CAMP');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		$inpuClass    = (JVERSION < '4.0.0') ? '' : 'class="form-control"';

		$html[] = '<span class="input-group">';
		$html[] = '<input type="text" id="' . $this->id . '_name" readonly="readonly" value="' . $title . '" disabled="disabled" ' . $inpuClass . ' size="35" />';

		// The current campaign input field
		if (JVERSION < '4.0.0')
		{
			$html[] = '<button type="button" data-target="#campaignModal" class="btn btn-primary" data-toggle="modal">'
			. '<span class="icon-list icon-white" aria-hidden="true"></span> '
			. Text::_('COM_JGIVE_SELECT_CHANGE') . '</button></span>';
			$html[] = HTMLHelper::_(
				'bootstrap.renderModal',
				'campaignModal',
				array(
					'url'        => $link,
					'title'      => Text::_('COM_MENUS_ITEM_FIELD_TYPE_LABEL'),
					'width'      => '100%',
					'height'     => '100&',
					'modalWidth' => 80,
					'bodyHeight' => 70
				)
			);
		}
		else
		{
			$html[] = '<button type="button" data-bs-target="#campaignModal" class="btn btn-primary" data-bs-toggle="modal">'
			. '<span class="icon-list icon-white" aria-hidden="true"></span> '
			. Text::_('COM_JGIVE_SELECT_CHANGE') . '</button></span>';
			$html[] = HTMLHelper::_(
				'bootstrap.renderModal',
				'campaignModal',
				array(
					'url'        => $link,
					'title'      => Text::_('COM_MENUS_ITEM_FIELD_TYPE_LABEL'),
					'width'      => '800px',
					'height'     => '300px',
					'modalWidth' => 80,
					'bodyHeight' => 70
				)
			);
		}

		// The active campaign id field
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// Class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}
