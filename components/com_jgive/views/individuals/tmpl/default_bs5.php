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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::script('libraries/techjoomla/assets/js/tjexport.js');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$siteUrl  = Uri::root();
$document = Factory::getDocument();
$document->addScriptDeclaration("var csv_export_url='{$this->csv_url}';");
$document->addScriptDeclaration("var tj_csv_site_root='{$siteUrl}';");
$document->addScriptDeclaration("var csv_export_success='{$this->messages['success']}';");
$document->addScriptDeclaration("var csv_export_error='{$this->messages['error']}';");
$document->addScriptDeclaration("var csv_export_inprogress='{$this->messages['inprogress']}';");
?>
<div id="jgiveWrapper" class="<?php echo COM_JGIVE_WRAPPAER_CLASS;?>">
	<div id="jgive_individuals" class="row">
		<div class="col-xs-12">
			<div class="container-fluid">
				<?php
				if ($this->params->get('show_page_heading', 1))
				{
					?>
					<div class="page-header"><h1><?php echo $this->escape($this->params->get('page_heading'));?></h1></div>
					<?php
				}
				?>
			</div>

			<form action="<?php echo Route::_('index.php?option=com_jgive&view=individuals&layout=default&Itemid=' . $this->individualsItemId); ?>" 
			method="post" name="adminForm" id="adminForm" class="">
				<div><?php echo $this->toolbarHTML;?></div>
				<div class="clearfix"></div>
				<hr class="hr-condensed"/>
				<?php echo LayoutHelper::render('joomla.searchtools.default', array ('view' => $this));?>
				<div class="clearfix"></div>
				<hr class="hr-condensed"/>

				<?php
				if (empty($this->items))
				{
					?>
					<div class="alert alert-info" role="alert"><?php echo Text::_('COM_JGIVE_NO_MATCHING_RESULTS');?></div>
					<?php
				}
				else
				{
					?>
					<div class="col-xs-12">
						<div class="no-more-tables table-responsive">
							<table class="table table-striped table-bordered table-hover table-light border mt-4" id="individualcontactList">
								<thead class="text-break table-primary text-light">
									<tr>
										<th width="2%">
											<?php echo HTMLHelper::_('grid.checkall'); ?>
										</th>
										<th width="2%">
											<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder);?>
										</th>
										<th width="10%">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_INDIVIDUALS_NAME', 'a.first_name', $listDirn, $listOrder);?>
										</th>
										<th width="10%">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_JGIVE_INDIVIDUALS_EMAIL', 'a.email', $listDirn, $listOrder);?>
										</th>
										<th width="10%">
											<?php echo Text::_('COM_JGIVE_INDIVIDUALS_PHONE');?>
										</th>
										<th width="10%">
											<?php echo Text::_('COM_JGIVE_INDIVIDUALS_ADDRESS');?>
										</th>
										<th width="10%">
											<?php echo Text::_('COM_JGIVE_INDIVIDUALS_TAXNUMBER');?>
										</th>
										<th>
											<?php echo Text::_('COM_JGIVE_INDIVIDUALS_NUMBER_OF_DONATIONS');?>
										</th>
										<th>
											<?php echo Text::_('COM_JGIVE_INDIVIDUALS_TOTAL_DONATED_AMOUNT');?>
										</th>
										<?php
										if (isset($this->items[0]->customField) && (!empty($this->items[0]->customField)))
										{
											foreach ($this->items[0]->customField as $key => $value)
											{
												?>
													<th width="5%">
														<?php echo ucfirst($key);?>
													</th>
												<?php
											}
										}
										?>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($this->items as $i => $item)
									{
									?>
										<tr>
											<td class="hidden-xs" >
												<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
											</td>
											<td data-title="<?php echo Text::_('JSTATUS');?>">
												<?php
												$publish = HTMLHelper::_('jgrid.published', $item->published, $i, 'individuals.', $this->canChangeState, 'cb');
												echo $publish;
												?>
											</td>
											<td data-title="<?php echo Text::_('COM_JGIVE_INDIVIDUALS_NAME');?>">
												<?php 
												if ($this->canChange)
												{
													?>
													<a href="<?php echo Route::_('index.php?option=com_jgive&view=individualform&layout=default&Itemid= '. $this->individualformFormItemId . '&id=' . (int) $item->id); ?>">
														<?php echo $this->escape($item->first_name) . ' ' . $this->escape($item->last_name); ?>
													</a>
												<?php
												}
												?>
											</td>
											<td data-title="<?php echo Text::_('COM_JGIVE_INDIVIDUALS_EMAIL');?>">
												<?php echo $this->escape($item->email); ?>
											</td>
											<td data-title="<?php echo Text::_('COM_JGIVE_INDIVIDUALS_PHONE');?>">
												<?php echo $this->escape($item->phone); ?>
											</td>
											<td data-title="<?php echo Text::_('COM_JGIVE_INDIVIDUALS_ADDRESS');?>">
												<?php
													if ($item->addr_line_1)
													{
														echo $this->escape($item->addr_line_1) . ', ';
													}

													if ($item->addr_line_2)
													{
														echo $this->escape($item->addr_line_2) . ', ';
													}

													if (($item->other_city_check == 1) && !empty($item->other_city_value))
													{
														echo $this->escape($item->other_city_value) . ', ';
													}
													elseif (!empty($item->city))
													{
														echo $this->escape($item->city) . ', ';
													}

													if ($item->region != null)
													{
														echo $this->escape($item->region) . ', ';
													}

													if ($item->country != null)
													{
														echo $this->escape($item->country);
													}

													if ($item->zip != null)
													{
														echo "<br>" . Text::_("COM_JGIVE_CONTACTS_ZIPCODE") . " - " . $this->escape($item->zip);
													}
												?>
											</td>
											<td data-title="<?php echo Text::_('COM_JGIVE_INDIVIDUALS_TAXNUMBER');?>">
												<?php echo $this->escape($item->taxnumber); ?>
											</td>
											<td data-title="<?php echo Text::_('COM_JGIVE_INDIVIDUALS_NUMBER_OF_DONATIONS');?>">
												<?php 
												if ($item->donations > 0)
												{?>
												<a href="<?php echo Route::_('index.php?option=com_jgive&view=donations&layout=all_donations&filter_contributor_id=' . $item->id . '&filter_donor_type=ind' . '&Itemid=' . $this->allDonations); ?>">
														<?php echo (int) $item->donations; ?>
												</a>
												<?php 
												}
												else
												{
													echo (int) $item->donations;
												}?>
											</td>
											<td data-title="<?php echo Text::_('COM_JGIVE_INDIVIDUALS_TOTAL_DONATED_AMOUNT');?>">
												<?php echo htmlspecialchars($item->amount,  ENT_COMPAT, 'UTF-8'); ?>
											</td>
											<?php
											if (isset($item->customField) && (!empty($item->customField)))
											{
												foreach ($item->customField as $key => $value)
												{
												?>
													<td>
														<?php echo $this->escape($value); ?>
													</td>
												<?php
												}
											}
											?>
										</tr>
									<?php
									}?>
								</tbody>
							</table>
							<div class="col-xs-12 no-more-tables">
								<div class="pull-right">
									<?php echo $this->pagination->getPagesLinks(); ?>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				?>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo HTMLHelper::_('form.token');?>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	Joomla.submitbutton = function(task){jgive.individuals.individualsSubmit(task);}
</script>
