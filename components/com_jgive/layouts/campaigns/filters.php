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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Filesystem\File;

$campaignHelper = new campaignHelper;
$campaign_type = $campaignHelper->filedToShowOrHide('campaign_type');

if (!empty($displayData['campData']))
{
	$show_org_ind_type_filter = $displayData['campData']['params']['show_org_ind_type_filter'];
}

$document = Factory::getDocument();
$renderer = $document->loadRenderer('module');
$modules  = ModuleHelper::getModules('tj-filters-mod-pos');
$app = Factory::getApplication();
$showFilters = 0;

if ($menu = $app->getMenu()->getActive())
{
	$showFilters = $menu->getParams()->get('show_filters');
}

if ($modules)
{
	$moduleParams = new Registry($modules['0']->params);
	$params   = array();

	if ($showFilters == 1)
	{
		if ($moduleParams->get('client_type') == "com_jgive.campaign")
		{
			foreach ($modules as $module)
			{
				echo $renderer->render($module, $params);
			}
		}
		else
		{
			echo Text::_('COM_JGIVE_CAMP_NOTICE');
		}
	}
}
elseif ($showFilters == 1)
{
	if ($displayData['campData']['params']['show_category_filter'] == 1)
	{
		?>
		<div class="col-xs-12 col-sm-3">
			<form action="" name="campaignFilterform" method="post" id="campaignFilterform">
				<div class="campaignsCatFilterwrapper">
					<h5><strong><?php echo Text::_('COM_JGIVE_CATEGORY');?></strong></h5>
					<?php
					$cat_url = 'index.php?option=com_jgive&view=campaigns&layout=all&filter_campaign_cat=&Itemid=' .
					$displayData['campData']['otherData']['allCampaignsItemid'];
					$cat_url = Uri::root() . substr(Route::_($cat_url), strlen(Uri::base(true)) + 1);

					foreach ($displayData['categories'] as $category)
					{
						$check = "";
						$selected = $category->value;

						$camps_quick = 'index.php?option=com_jgive&view=campaigns&layout=all&filter_campaign_cat='
						. $selected . '&Itemid=' . $displayData['campData']['otherData']['allCampaignsItemid'];
						$camps_quick = Uri::root() . substr(Route::_($camps_quick), strlen(Uri::base(true)) + 1);

						if ($displayData['list']['filter_campaign_cat'] == $selected)
						{
							$class = "active";
							$check = "checked";
						}
						else
						{
							$class = "";
						}
					?>
						<div class="<?php echo $class; ?>">
							<label>
								<input type="radio" class=""
								name="<?php echo 'filter_campaign_cat';?>"
								id="filter_campaign_cat" <?php echo $check;?>
								value="<?php echo $category->value; ?>"
								onclick="document.getElementById('campaignFilterform').submit();" />
								<?php echo $category->text; ?>
							</label>
						</div>
					<?php
					}
					?>
				</div>
			</form>
		</div>
	<?php
	}

	$override = JPATH_SITE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/layouts/com_jgive/corefilters/horizontal/bs3/corefilters.php';
    $basePath = (File::exists($override)) ? JPATH_SITE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/layouts/com_jgive/corefilters/horizontal/bs3' : JPATH_SITE . '/components/com_jgive/layouts/corefilters/horizontal/bs3';
    $layout = new FileLayout('corefilters', $basePath);
	$data = "";
	echo $layout->render($data);
	$selected = null;
	?>
	<div class="clearfix"></div>
		<?php
		$allCampaigns = 'index.php?option=com_jgive&view=campaigns&layout=all';
		$campaignsResetFilterUrl = $allCampaigns . '&Itemid='
		. $displayData['campData']['otherData']['allCampaignsItemid'];

		$campaignsResetFilterUrl = Uri::root() . substr(Route::_($campaignsResetFilterUrl), strlen(Uri::base(true)) + 1);
		?>
		<div class="col-xs-12 col-sm-12">
			<a class="pull-right me-3" onclick='window.location.assign("<?php echo $campaignsResetFilterUrl; ?>&reset_filters=1")' href="javascript:void(0);">
				<i class="fa fa-repeat" aria-hidden="true"></i>
				<?php echo Text::_('COM_JGIVE_REST_FILTERS');?>
			</a>
		</div>
<!--
		<div class="col-xs-12 col-sm-12">
			<button type="button" onclick="document.campaignFilterform.submit();" class="btn btn-mini btn-primary pull-right">
				<?php echo Text::_('COM_JGIVE_DASHBOARD_APPLY_FILTER'); ?>
			</button>
		</div>
-->
	<div class="clearfix"></div>
<?php
}
