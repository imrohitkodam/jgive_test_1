<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of loggin user created campaigns
 *
 * @since  2.3.4
 */
class JFormFieldMyCampaigns extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 2.3.4
	 */
	protected $type = 'mycampaigns';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since  2.3.4
	 */
	protected function getOptions()
	{
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JGIVE_SEARCH_TOOLS'));
		$user      = Factory::getUser();

		if ($user->id)
		{
			$campaignsModel = JGive::model('campaigns');
			$campaignsModel->setState('filter.creator_id', (int) $user->id);
			$userCampaignsList = $campaignsModel->getItems();

			if (!empty($userCampaignsList))
			{
				foreach ($userCampaignsList as $key => $campaign)
				{
					$options[]  = HTMLHelper::_('select.option', $campaign->id, htmlspecialchars($campaign->title));
				}
			}
		}

		return $options;
	}
}
