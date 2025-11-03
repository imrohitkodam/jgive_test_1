<?php
/**
 * @package    JGive
 *
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.renderModal', 'a.modal');
?>

<div id="jgiveWrapper" class="container-fluid">
	<div class="row">
		<?php
		if ($this->certificateHtml['status'])
		{?>
		<div class="col-sm-12">
			<input
				type="button"
				class="btn btn-default btn-xs no-print pull-right"
				onclick="com_jgive.UI.Common.printReceipt()"
				value="<?php echo Text::_('COM_JGIVE_DONATION_PRINT_CERTIFICATE');?>">
		</div>
		<?php
		}?>
	</div>
	<div class="row">
		<div class="col-sm-12" id="printReceipt">
			<?php echo $this->certificateHtml['body'];?>
		</div>
	</div>
</div>

