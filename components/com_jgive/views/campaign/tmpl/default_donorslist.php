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

use Joomla\CMS\Language\Text;

// Calling FrontendHelper
$jgiveFrontendHelper = new jgiveFrontendHelper;
$utilitiesObj = JGive::utilities();
?>
<tr>
	<td data-title="<?php echo Text::_("COM_JGIVE_DONORNAME");?>">
		<?php
			if ($this->donor->annonymous_donation)
			{
				echo Text::_("COM_JGIVE_DONOR_ANNONYMOUS_NAME");
			}
			elseif ($this->donor->donor_type == 'org' && !empty($this->donor->org_name))
			{
				echo ucfirst(htmlspecialchars($this->donor->org_name, ENT_COMPAT, 'UTF-8'));
			}
			else
			{
				echo htmlspecialchars($this->donor->first_name, ENT_COMPAT, 'UTF-8') . ' ' . htmlspecialchars($this->donor->last_name, ENT_COMPAT, 'UTF-8');
			}?>
	</td>
	<td data-title="<?php echo Text::_("COM_JGIVE_TOTAL"); ?>">
		<?php echo $jgiveFrontendHelper->getFormattedPrice($this->donor->amount);?>
	</td>
	<td data-title="<?php echo Text::_("COM_JGIVE_SINGLE_DONORS_RECENT_DONATION"); ?>">
		<?php echo $jgiveFrontendHelper->getFormattedPrice($this->donor->recentDonation); ?>
	</td>
	<td data-title="<?php echo Text::_("COM_JGIVE_SINGLE_DONORS_PAYMENT_MODE")?>">
		<?php echo $utilitiesObj->getPaymentGatewayName($this->donor->processor); ?>
	</td>
</tr>
