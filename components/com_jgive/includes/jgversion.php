<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;

/**
 * Version information class for the JGive.
 *
 * @since  2.3.3
 */
class JGiveJgversion
{
	/**
	 * Product name.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const PRODUCT = 'JGive!';

	/**
	 * Major release version.
	 *
	 * @var    integer
	 * @since  2.3.3
	 */
	const MAJOR_VERSION = 4;

	/**
	 * Minor release version.
	 *
	 * @var    integer
	 * @since  2.3.3
	 */
	const MINOR_VERSION = 1;

	/**
	 * Patch release version.
	 *
	 * @var    integer
	 * @since  2.3.3
	 */
	const PATCH_VERSION = 1;

	/**
	 * Release version.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const RELEASE = '4.1';

	/**
	 * Maintenance version.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const DEV_LEVEL = '1';

	/**
	 * Development status.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const DEV_STATUS = 'Stable';

	/**
	 * Build number.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const BUILD = '';

	/**
	 * Code name.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const CODENAME = 'TechJoomla';

	/**
	 * Release date.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const RELDATE = '18-August-2025';

	/**
	 * Release time.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const RELTIME = '15:51';

	/**
	 * Release timezone.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const RELTZ = 'GMT';

	/**
	 * Copyright Notice.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const COPYRIGHT = 'Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.';

	/**
	 * Link text.
	 *
	 * @var    string
	 * @since  2.3.3
	 */
	const URL = '<a href="https://www.techjoomla.com">TechJoomla!</a> is Joomla product dev.';

	/**
	 * Gets a "PHP standardized" version string for the current JGive.
	 *
	 * @return  string  Version string.
	 *
	 * @since   2.3.3
	 */
	public function getShortVersion()
	{
		return self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION;
	}

	/**
	 * Gets a version string for the current JGive with all release information.
	 *
	 * @return  string  Complete version string.
	 *
	 * @since   2.3.3
	 */
	public function getLongVersion()
	{
		return self::PRODUCT . ' ' . $this->getShortVersion() . ' ' . self::RELDATE;
	}

	/**
	 * Generate a media version string for assets
	 * Public to allow third party developers to use it
	 *
	 * @return  string
	 *
	 * @since   2.3.3
	 */
	public function generateMediaVersion()
	{
		return md5($this->getLongVersion() . Factory::getConfig()->get('secret'));
	}

	/**
	 * Gets a media version which is used to append to JGive core media files.
	 *
	 * This media version is used to append to JGive core media in order to trick browsers into
	 * reloading the CSS and JavaScript, because they think the files are renewed.
	 * The media version is renewed after JGive core update, install, discover_install and uninstallation.
	 *
	 * @return  string  The media version.
	 *
	 * @since   2.3.3
	 */
	public function getMediaVersion()
	{
		return $this->generateMediaVersion();
	}
}
