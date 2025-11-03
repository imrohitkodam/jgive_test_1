<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

// Check if any filter field has been filled
$filters = false;
if (isset($data['view']->filterForm))
{
	$filters = $data['view']->filterForm->getGroup('filter');
}

// Check if there are filters set.
if ($filters !== false)
{
	$filterFields = array_keys($filters);
	$filtered     = false;
	$filled       = false;
	foreach ($filterFields as $filterField)
	{
		$filterField = substr($filterField, 7);
		$filter      = $data['view']->getState('filter.' . $filterField);
		if (!empty($filter))
		{
			$filled = $filter;
		}
		if (!empty($filled))
		{
			$filtered = true;
			break;
		}
	}
}

// Set some basic options
$customOptions = array(
	'filtersHidden'       => isset($data['options']['filtersHidden']) ? $data['options']['filtersHidden'] : empty($data['view']->activeFilters) && !$filtered,
	'defaultLimit'        => isset($data['options']['defaultLimit']) ? $data['options']['defaultLimit'] : Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering'
);

$data['options'] = array_unique(array_merge($customOptions, $data['options']));

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
HTMLHelper::_('searchtools.form', $formSelector, $data['options']);
?>

<div class="js-stools clearfix">
	<div class="clearfix">
		<div class="row">
			<label for="filter_search" class="element-invisible"
				aria-invalid="false"><?php echo Text::_('COM_JGIVE_SEARCH_FILTER_SUBMIT');?>
			</label>

			<div class="col-md-12 col-xs-12 af-mb-10">
				<div class="btn-group">
					<input type="text" name="filter[search]" id="filter_search"
						value="<?php echo htmlspecialchars($filters['filter_search']->value, ENT_COMPAT, 'UTF-8');  ?>"
						class="js-stools-search-string form-control"
						placeholder="Search by email, donor name">
					<button type="submit" class="btn btn-primary hasTooltip btn-md btn__filterDonar" title=""
						data-original-title="<?php echo Text::_('COM_JGIVE_SEARCH_FILTER_SUBMIT'); ?>">
						<i class="fa fa-search"></i>
					</button>

					<button type="button" class="btn btn-secondary hasTooltip js-stools-btn-clear" title=""
						data-original-title="<?php echo Text::_('COM_JGIVE_SEARCH_FILTER_CLEAR'); ?>"
						onclick="javascript:techjoomla.jQuery(this).closest('form').find('input').val('');">
						<i class="fa fa-close"></i>
					</button>
				</div>
			</div>
			<div class="col-md-12 col-xs-12 af-mb-10">
				<div class="btn-toolbar float-end">
					<?php if ($filters) : ?>
						<?php foreach ($filters as $fieldName => $field) : ?>
							<?php if ($fieldName != 'filter_search') : ?>
								<div class="js-stools-field-filter ms-1">
									<?php echo $field->input; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

		</div>
	</div>
	<!-- Filters div -->

</div>
