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

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>
<form action="index.php?option=com_jgive&view=organizations" name="adminForm" id="adminForm" class="form-validate" method="post">
	<div id="jgiveWrapper" class="j-main-container row">
	<div class="col-md-12">
	<?php
	// Searching Options
	echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));

	if (empty($this->items))
	{
		?>
		<div class="clearfix">&nbsp;</div>
		<div class="alert alert-no-items">
			<?php echo Text::_('COM_JGIVE_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php
	}
	else
	{
	?>
		<table class='table'>
			<thead>
				<tr>
					<th width="2%">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th  width="5%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_ORGANIZATION_NAME', 'name', $listDirn, $listOrder);?>
					</th>
					<th width="10%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_INDIVIDUALS_VENDOR_NAME', 'vendor_id', $listDirn, $listOrder);?>
					</th>
					<th  width="15%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_CONTACT_PERSON', 'contact_name', $listDirn, $listOrder);?>
					</th>
					<th  width="15%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_ORGANIZATION_EMAIL', 'email', $listDirn, $listOrder);?>
					</th>
					<th  width="15%">
						<?php echo Text::_('COM_JGIVE_ORGANIZATION_PHONE');?>
					</th>
					<th  width="15%">
						<?php echo Text::_('COM_JGIVE_WEBSITE');?>
					</th>
					<!--<th width="20%">
					<?php //echo Text::_('COM_JGIVE_ADDRESS');?>
					</th>-->

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
					<th width="2%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_JGIVE_ORGANIZATION_ID', 'id', $listDirn, $listOrder)?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if (!empty($this->items))
				{
					foreach ($this->items as $i => $row)
					{
						$link = Route::_('index.php?option=com_jgive&task=organization.edit&id=' . $row->id);
					?>
						<tr>
							<td>
								<?php echo HTMLHelper::_('grid.id', $i, $row->id); ?>
							</td>
							<td>
								<a href="<?php echo $link; ?>" >
									<?php echo htmlspecialchars($row->name ? $row->name : '',  ENT_COMPAT, 'UTF-8'); ?>
								</a>
							</td>
							<td>
								<?php echo (!empty($row->vendorTitle)) ? htmlspecialchars($row->vendorTitle ? $row->vendorTitle : '',  ENT_COMPAT, 'UTF-8') : ' - '; ?>
							</td>
							<td class="text-break">
								<?php echo htmlspecialchars($row->contact_firstname ? $row->contact_firstname : '', ENT_COMPAT, 'UTF-8'); ?>
								<?php echo htmlspecialchars($row->contact_lastname ? $row->contact_lastname : '', ENT_COMPAT, 'UTF-8'); ?>
								<br>
								<?php echo htmlspecialchars($row->contact_email ? $row->contact_email : '', ENT_COMPAT, 'UTF-8'); ?>
								<br>
								<?php echo htmlspecialchars($row->contact_phone ? $row->contact_phone : '', ENT_COMPAT, 'UTF-8'); ?>
							</td>
							<td>
								<?php echo htmlspecialchars($row->email ? $row->email : '',  ENT_COMPAT, 'UTF-8'); ?>
							</td>
							<td>
								<?php echo htmlspecialchars($row->phone ? $row->phone : '',  ENT_COMPAT, 'UTF-8'); ?>
							</td>
							<td>
								<?php echo htmlspecialchars($row->website ? $row->website : '',  ENT_COMPAT, 'UTF-8'); ?>
							</td>
							<!--<td>
								<?php
									if ($row->addr_line_1 != "")
									{
										//echo htmlspecialchars($row->addr_line_1,  ENT_COMPAT, 'UTF-8') . ',';
									}
								?>
								<?php
									if ($row->addr_line_2 != "")
									{
										//echo htmlspecialchars($row->addr_line_2,  ENT_COMPAT, 'UTF-8') . ',';
									}
								?>
								<?php
									if ($row->other_city_check == 1)
									{
										//echo htmlspecialchars($row->other_city_value,  ENT_COMPAT, 'UTF-8');
									}
									else
									{
										//echo htmlspecialchars($row->city,  ENT_COMPAT, 'UTF-8');
									}
								?>
								<?php
									if ($row->zip != null)
									{
										//echo '- ' . htmlspecialchars($row->zip,  ENT_COMPAT, 'UTF-8') . ',';
									}
								?>
								<?php
									if ($row->region != null)
									{
										//echo htmlspecialchars($row->region, ENT_COMPAT, 'UTF-8') . ',';
									}
								?>
								<?php
									if ($row->country != null)
									{
										//echo htmlspecialchars($row->country,  ENT_COMPAT, 'UTF-8');
									}
								?>
							</td>-->
							<?php
							if (isset($row->customField) && (!empty($row->customField)))
							{
								foreach ($row->customField as $key => $value)
								{
								?>
									<td>
										<?php echo htmlspecialchars($value ? $value : ''); ?>
									</td>
								<?php
								}
							}
							?>
							<td>
								<a href="<?php echo $link; ?>">
									<?php echo htmlspecialchars($row->id,  ENT_COMPAT, 'UTF-8'); ?>
								</a>
							</td>
						</tr>
					<?php
					}
				}
				?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>
		<?php
	}?>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
<?php echo HTMLHelper::_('form.token'); ?>
</div>
</form>
<script type="text/javascript">

Joomla.submitbutton = function(action)
{
	var form = document.adminForm;

	// Show confirmation message before delete organization
	if (action == 'organizations.delete')
	{
		// Show confirmation message
		if (confirm('<?php echo Text::_("COM_JGIVE_DELETE_ORGANIZATIONS")?>'))
		{
			Joomla.submitform(action );
		}

		return false;
	}
	else
	{
		Joomla.submitform(action );
	}
}
</script>
