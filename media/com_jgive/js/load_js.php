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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

$doc = Factory::getDocument();
$doc->addScriptOptions('jgive', array());

// Load JS files
HTMLHelper::_('script', 'media/com_jgive/js/core/class.js');
HTMLHelper::_('script', 'media/com_jgive/js/com_jgive.js');
HTMLHelper::_('script', 'media/com_jgive/js/core/base.js');
HTMLHelper::_('script', 'media/com_jgive/js/services/common.js');
HTMLHelper::_('script', 'media/com_jgive/js/services/individual.js');
HTMLHelper::_('script', 'media/com_jgive/js/services/organization.js');
HTMLHelper::_('script', 'media/com_jgive/js/services/donation.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/campaign.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/individual.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/organization.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/common.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/donation.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/donations.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/individuals.js');
HTMLHelper::_('script', 'media/com_jgive/js/ui/organizations.js');
