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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
$document = Factory::getDocument();
$jgivehelper   = new JgiveFrontendHelper;

// Menu Item Id for redirecting url
$mainframe = Factory::getApplication();
$input = Factory::getApplication()->input;
$itemidForDonor = $input->get('Itemid', '', 'INT');

// Check here logged user have campaigns.
if (!$this->campaignsId )
{?>
	<div class="alert alert-warning">
		<?php echo Text::_('COM_JGIVE_DASHBOARD_NOT_FOUND_ANY_CAMPAIGN');?>
	</div>
<?php
}
else
{
	HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
	HTMLHelper::_('bootstrap.tooltip');
	HTMLHelper::_('behavior.multiselect');

	$user       = Factory::getUser();
	$userId     = $user->get('id');
	$listOrder  = $this->state->get('list.ordering');
	$listDirn   = $this->state->get('list.direction');
	$canCreate  = $user->authorise('core.create', 'com_jgive');
	$canEdit    = $user->authorise('core.edit', 'com_jgive');
	$canCheckin = $user->authorise('core.manage', 'com_jgive');
	$canChange  = $user->authorise('core.edit.state', 'com_jgive');
	$canDelete  = $user->authorise('core.delete', 'com_jgive');

	$siteUrl = Uri::root();
	HTMLHelper::_('script', 'libraries/techjoomla/assets/js/tjexport.js');
	$document->addScriptDeclaration("var csv_export_url='{$this->csv_url}';");
	$document->addScriptDeclaration("var tj_csv_site_root='{$siteUrl}';");
	$document->addScriptDeclaration("var csv_export_success='{$this->messages['success']}';");
	$document->addScriptDeclaration("var csv_export_error='{$this->messages['error']}';");
	$document->addScriptDeclaration("var csv_export_inprogress='{$this->messages['inprogress']}';");
?>
<div class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?> container">
	<div id="jgive_donors" class="row">
		<div class="col-xs-12">
			<div class="page-header">
				<h1>
					<?php echo Text::_('COM_JGIVE_MY_CAMPAIGN_DONORS');?>
				</h1>
			</div>
				<form action="<?php echo Route::_('index.php?option=com_jgive&view=donors'); ?>" method="post" name="adminForm" id="adminForm">
					<div class="row">
						<div class="col-md-12">
							<?php echo $this->toolbarHTML;?>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<?php echo LayoutHelper::render('default_filter_' . JGIVE_LOAD_BOOTSTRAP_VERSION, array('view' => $this), dirname(__FILE__)); ?>
						</div>
					</div>

					<?php if (empty($this->items))
					{?>
						<div class="alert alert-no-items">
							<?php echo Text::_('COM_JGIVE_NO_MATCHING_RESULTS'); ?>
						</div>
					<?php
					}
					else
					{?>
						<div class="row">
							<div class="col-xs-12 table-responsive">
								<div class="no-more-tables">
									<table class="table table-striped table-bordered table-hover table-light border table-hover mt-4" id="donorList">
										<thead class="text-break table-primary text-light">
											<tr>
												<th width="1%" class="">
													<input type="checkbox" name="checkall-toggle" value=""
													title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
													onclick="Joomla.checkAll(this)" />
												</th>
												<th class="" width="5%">
													<?php echo Text::_('COM_JGIVE_NO'); ?>
												</th>
												<th class=''>
													<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DASHBOARD_DONOR_NAME', 'a.first_name', $listDirn, $listOrder); ?>
												</th>
												<th class=''>
													<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DONATION_DONOR_TYPE', 'a.donor_type', $listDirn, $listOrder); ?>

												</th>
												<th class=''>
													<?php echo Text::_('COM_JGIVE_DASHBOARD_DONOR_CONTACT'); ?>
												</th>
												<th>
													<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DONORS_ADDRESS', 'a.address', $listDirn, $listOrder); ?>
												</th>
												<th class=''>
													<?php echo HTMLHelper::_('grid.sort',  'COM_JGIVE_DONORS_DONATION_AMOUNT', 'amount', $listDirn, $listOrder); ?>
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$num = 1;
											foreach ($this->items as $i => $item)
											{
												?>
												<?php $canEdit = $user->authorise('core.edit', 'com_jgive'); ?>
												<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_jgive')): ?>
														<?php $canEdit = Factory::getUser()->id == $item->created_by; ?>
												<?php endif; ?>
												<tr class="row<?php echo $i % 2; ?>">
													<td class="center hidden-sm hidden-xs">
														<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
													</td>
													<td data-title="<?php echo Text::_("COM_JGIVE_NO"); ?>">
														<?php echo $num++; ?>
													</td>
													<td data-title="<?php echo Text::_("COM_JGIVE_DASHBOARD_DONOR_NAME"); ?>">
													<?php
														if (isset($item->checked_out) && $item->checked_out)
														{
															echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'donors.', $canCheckin);
														}

														if ($item->donor_type == 'org' && !empty($item->org_name))
														{
															echo htmlspecialchars($item->org_name, ENT_COMPAT, 'UTF-8');
														}
														else
														{
															echo htmlspecialchars($item->first_name, ENT_COMPAT, 'UTF-8') . " " . htmlspecialchars($item->last_name, ENT_COMPAT, 'UTF-8');
														}
													?>
													</td>
													<td>
													<?php echo ($item->donor_type == 'org')? Text::_('COM_JGIVE_DONATION_ORGANIZATION'): Text::_('COM_JGIVE_DONATION_INDIVIDUAL');?>
													</td>
													<td class="wordbreak" data-title="<?php echo Text::_("COM_JGIVE_DASHBOARD_DONOR_EMAIL"); ?>">

														<?php echo htmlspecialchars($item->email, ENT_COMPAT, 'UTF-8'); ?>
														<br>
														<?php echo $item->phone ? htmlspecialchars($item->phone, ENT_COMPAT, 'UTF-8') : " - "; ?>

													</td>
													<td data-title="<?php echo Text::_("COM_JGIVE_DONORS_ADDRESS"); ?>">
														<?php echo !empty($item->address) ? htmlspecialchars($item->address, ENT_COMPAT, 'UTF-8') . ', ' : ''; ?>
														<?php echo $item->address2 ? htmlspecialchars($item->address2, ENT_COMPAT, 'UTF-8') : " - "; ?>
														<?php
															$city = JGive::utilities()->getCity((int) $item->city);
															echo !empty($city->city) ? htmlspecialchars($city->city, ENT_COMPAT, 'UTF-8') . ', ' : '';
														?>
														<?php
															$state = JGive::utilities()->getRegion((int) $item->state);
															echo !empty($state->region) ? htmlspecialchars($state->region, ENT_COMPAT, 'UTF-8') . ', ' : '';
														?>
														<?php
															$country = JGive::utilities()->getCountry((int) $item->country);
															echo !empty($country->country) ? htmlspecialchars($country->country, ENT_COMPAT, 'UTF-8') . ',</br>' : ''; 
														?>
														<?php echo htmlspecialchars($item->zip, ENT_COMPAT, 'UTF-8'); ?>
													</td>
													<td data-title="<?php echo Text::_("COM_JGIVE_DONORS_DONATION_AMOUNT"); ?>">
														<?php
														$donorType = 'ind';

														if ($item->donor_type == 'org')
														{
															$donorType = 'org';
														}
														?>
														<a href="<?php echo Route::_('index.php?option=com_jgive&view=donations&layout=all_donations&filter_contributor_id=' . (int) $item->contributor_id . '&filter_donor_type=' . $this->escape($donorType) . '&Itemid=' . $this->promoterDonationsItemId); ?>">
															<?php echo $jgivehelper->getFormattedPrice($item->amount); ?>
														</a>
													</td>
												</tr>
											<?php
											}?>
										</tbody>
									</table>
									<div class="col-xs-12">
										<div class="hidden-xs hidden-sm">
											<div <?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?> >
												<div class="pull-right">
													<?php echo $this->pagination->getListFooter(); ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php
					}?>

					<input type="hidden" name="task" id="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
					<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
					<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
		</div>
	</div>
</div>
<?php
}?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if(task == 'donors.redirectforEmail')
		{
			if(document.adminForm.boxchecked.value == 0)
			{
				var msg = "<?php echo Text::_('COM_JGIVE_MSG_FOR_SELECT_USER'); ?>";
				alert(msg);
				return false;
			}
			else
			{
				Joomla.submitform(task);
			}
		}
		else
		{
			Joomla.submitform(task);
		}
	}
</script>
