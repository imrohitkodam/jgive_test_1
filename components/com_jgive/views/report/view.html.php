<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;

JLoader::import('Report', JPATH_SITE . '/components/com_jgive/models');

/**
 * JgiveViewReport
 *
 * @package     Jgive
 * @subpackage  Jgive report view class
 * @since       2.2.0
 */
class JgiveViewReport extends HtmlView
{
	protected $params;

	protected $item;

	protected $state;

	protected $campaignItemId;

	/**
	 * Display the Report view
	 *
	 * @param   string  $tpl  The name of the layout file to parse.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{

		$this->item            = $this->get('Item');
		$this->state           = $this->get('State');
		$this->params          = $this->state->get('params');
		$offset                = $this->state->get('list.offset');
		$item                  = $this->item;
		$temp                  = clone $this->params;
		$item->params          = $temp;

		if (empty($this->item->id))
		{
			throw new Exception(Text::_('COM_JGIVE_REPORT_NOT_FOUND'), 404);
		}

		$this->item->reportUrl = substr(
			Route::_('index.php?option=com_jgive&view=report&id=' . $this->item->id . '&cid=' . $this->item->campaign_id),
			strlen(Uri::base(true)) + 1
		);

		$this->item->campaignUrl = substr(
			Route::_('index.php?option=com_jgive&view=campaign&layout=default&id=' . $this->item->campaign_id),
			strlen(Uri::base(true)) + 1
		);

		$jgiveFrontendHelper  = new JgiveFrontendHelper;
		$this->campaignItemId = $jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=default');

		PluginHelper::importPlugin('content', 'jlike_jgive');
		$item->event = new stdClass;
		$results = Factory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_jgive.report', &$item, &$item->params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return null
	 *
	 * @throws Exception
	 */
	protected function _prepareDocument()
	{
		$reportTitle = $this->item->title;

		$this->params->def('page_heading', $reportTitle);
		$this->document->setTitle($reportTitle);
	}
}
