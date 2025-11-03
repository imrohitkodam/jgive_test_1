<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

?>
<div class="row-fluid form-horizontal-desktop">
	<div class="span6">
		<?php
			echo $this->form->renderField('creator_id');
			echo $this->form->renderField('title');
			echo $this->form->renderField('alias');
			echo $this->form->renderField('org_ind_type');
			echo $this->form->renderField('category_id');

			if (in_array('long_desc', $this->hideFields) && $this->hideShowFields == 1)
			{
				?>
				<input type="hidden" name="jform[long_description]" id="jform_long_description" value="" />
				<?php
			}
			else
			{
				?>
				<div class="control-group launchForm__desc">
					<div class="control-label">
						<?php echo $this->form->getLabel('long_description'); ?>
					</div>
					<div class="pull-left clearfix">
						<?php echo $this->form->getInput('long_description'); ?>
					</div>
				</div>
				<?php
			}
		?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('image'); ?>
			</div>
			<div class="pull-left clearfix">
				<?php echo $this->form->getInput('image'); ?>
			</div>
		</div>
		<div class="control-group af-d-none creatcamp--mb-0">
			<div class="pull-left span9 clearfix creatcamp__imgprogress"></div>
		</div>
		<div class="control-group creatcamp--mb-0">
			<div class="span9 alert alert-info">
				<?php
					echo Text::_('COM_JGIVE_MAIN_IMAGE_SIZE') . $this->com_params->get('large_width') .
					Text::_('COM_JGIVE_MAIN_IMAGE_DIMENSIONS_UNIT') . Text::_('COM_JGIVE_MAIN_IMAGE_DIMENSIONS_SIGN')
					. $this->com_params->get('large_height') . Text::_('COM_JGIVE_MAIN_IMAGE_DIMENSIONS_UNIT');

					$mediaId = '';
					$hideDiv = 'hide_jgdiv';
					$this->campaignImage = Uri::root() . 'media/com_jgive/images/default_campaign.png';

					if (isset($this->item->image->id))
					{
						$hideDiv             = '';
						$this->campaignImage = $this->item->image->media_m;
						$mediaId             = $this->item->image->id;
					}
				?>
			</div>
		</div>
		<div class="control-group">
			<div class="pull-left jgive_campaign_upload creatcamp__imgcover--height200">
				<img src="<?php echo $this->campaignImage; ?>" id="uploaded_media" class="creatcamp__img">
				<input type="hidden" name="jform[image][new_image]" id="jform_campaign_image" value="<?php echo $mediaId;?>" />
				<input type="hidden" name="jform[image][old_image]" id="jform_campaign_old_image" value="" />
			</div>
		</div>
	</div>

	<div class="span6">
		<?php
			$campaignType = array();

			// This type casting is needed for converting string to array
			$campaignType = (array) $this->com_params->get('camp_type');

			/* Check if campaign type field is hide */
			if (isset($this->hideShowFields) && !empty($this->hideFields) && in_array('campaign_type', $this->hideFields))
			{
			?>
				<input type="hidden" name="jform[type]" id="jform_type" value="donation" />
			<?php
			}
			elseif(empty($this->hideFields) || !empty($this->hideFields) && !in_array('campaign_type', $this->hideFields))
			{
				if (sizeof($campaignType) == 1)
				{
				?>
					<input type="hidden" name="jform[type]" id="jform_type" value="<?php echo $campaignType[0];?>" />
				<?php
				}
				elseif (sizeof($campaignType) == 2)
				{
					echo $this->form->renderField('type');
				}
			}

			echo $this->form->renderField('published');
			echo $this->form->renderField('start_date');

			if (!$this->daysConfig)
			{
			?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('end_date'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('end_date'); ?></div>
				</div>
				<input type="hidden" name="jform[days_limit]" id="jform_days_limit" value="" />
		<?php
			}
			else
			{
			?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('days_limit'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('days_limit'); ?></div>
				</div>
		<?php
			}

			if (in_array('show_public', $this->hideFields) && $this->hideShowFields == 1)
			{
			?>
					<input type="hidden" name="jform[allow_view_donations]" id="jform_allow_view_donations" value="0" />
		<?php
			}
			else
			{
				echo $this->form->renderField('allow_view_donations');
			}

			if (in_array('allow_exceed', $this->hideFields) && $this->hideShowFields == 1)
			{
			?>
					<input type="hidden" name="jform[allow_exceed]" id="jform_allow_exceed" value="0" />
		<?php
			}
			else
			{
				echo $this->form->renderField('allow_exceed');
			}

			echo $this->form->renderField('goal_amount');

			if (in_array('min_donation', $this->hideFields) && $this->hideShowFields == 1)
			{
		?>
				<input type="hidden" name="jform[minimum_amount]" id="jform_minimum_amount" value="0" />
		<?php
			}
			else
			{
				echo $this->form->renderField('minimum_amount');
			}

			if (in_array('max_donation', $this->hideFields) && $this->hideShowFields == 1)
			{
		?>
				<input type="hidden" name="jform[max_donors]" id="jform_max_donors" value="0" />
		<?php
			}
			else
			{
				echo $this->form->renderField('max_donors');
			}
		?>
		<div class="control-group">
			<?php
				echo $this->form->renderField('meta_data');
				echo $this->form->renderField('meta_desc');
				
				$integration = $this->com_params->get('integration');

				if ($integration == 'jomsocial')
				{
					echo $this->form->renderField('js_groupid');
				}
			?>
		</div>
	</div>
</div>
