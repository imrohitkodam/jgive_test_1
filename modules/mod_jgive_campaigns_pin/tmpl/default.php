<?php
/**
 * @package    Jgive
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Component\ComponentHelper;


$app = Factory::getApplication();

// Load Tj Strapper
$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (File::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jgive');
}

require_once JPATH_SITE . '/components/com_jgive/includes/jgive.php';

$versionClass  = JGive::jgversion();
$version = $versionClass->getMediaVersion();
$options = array("version" => $version);

// Load component css
HTMLHelper::_('stylesheet', 'media/com_jgive/css/jgive.css', $options);
HTMLHelper::_('stylesheet', 'media/com_jgive/css/artificiers.min.css', $options);

if ($loadbootstrap)
{
	HTMLHelper::stylesheet('media/techjoomla_strapper/bs3/css/bootstrap.min.css', $options);
}

$pin_width       = $params['pin_width']? $params['pin_width']: 240;
$pin_padding     = $params['pin_padding']? $params['pin_padding']: 5;
if (!defined('JGIVE_LOAD_BOOTSTRAP_VERSION'))
{
	$params = ComponentHelper::getParams('com_jgive');
	$bsVersion = $params->get('bootstrap_version', '', 'STRING');

	if (empty($bsVersion))
	{
		$bsVersion = (JVERSION >= '4.0.0') ? 'bs5' : 'bs3';
	}
	define('JGIVE_LOAD_BOOTSTRAP_VERSION', $bsVersion);
}
?>

<div class="tjBs3 <?php echo $params->get('moduleclass_sfx'); ?>" id="jgiveWrapper">
	<div class="col-xs-12">
		<div id="mod_jgive_pin_container<?php echo $module->id;?>" class="<?php echo JGIVE_LOAD_BOOTSTRAP_VERSION == 'bs3' ? '' : 'row'; ?>">
			<?php
				if (empty($data))
				{
				?>
					<div class="alert alert-warning">
						<?php echo Text::_('MOD_JGIVE_PIN_NO_DATA_FOUND');?>
					</div>
				<?php
				}
				else
				{
					?>
					<style type="text/css">
						@media (min-width: 480px){
							.jgive_module_pin_item { width: <?php echo $pin_width . 'px'; ?> !important; margin-right: <?php echo $pin_padding . 'px'; ?> !important; }
						}
					</style>
					<?php

					// Set the basepath as per the availability of override file
					$override = JPATH_SITE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/layouts/com_jgive/campaigns/pin.php';
					$basePath = (File::exists($override)) ? JPATH_SITE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/layouts/com_jgive/campaigns/' : JPATH_SITE . '/components/com_jgive/layouts/campaigns';

					foreach ($data as $displayData)
					{
					?>
						<div class="col-sm-3 col-xs-12 jgive_module_pin_item af-mb-20">
							<?php
								$layout = new FileLayout('pin', $basePath);
								$html = $layout->render($displayData);
								echo $html;
							?>
						</div>
					<?php
					}
				}
			?>
		</div>
	</div>
</div>

<style>
	@media (min-width: 480px){
		#mod_jgive_pin_container<?php echo $module->id;?> .jgive_pin_item { width: <?php echo $pin_width . 'px'; ?> !important; }
	}
</style>
