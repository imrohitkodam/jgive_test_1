<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

?>
<div class="col-xs-12 col-md-5">
	<div class="row">
		<div class="col-xs-12 col-sm-6">
			<div class="form-group">
				<?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 af-pr-10">
			<div class="form-group">
				<?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?>
			</div>
		</div>
	</div>

	<!--Date & days-->
	<div class="row launchForm__date">
		<div class="col-xs-8 col-sm-6">
			<div class="form-group">
				<i class="fa fa-calendar af-pr-5 text-primary af-font-bold" aria-hidden="true"></i><?php echo $this->form->getLabel('start_date'); ?>
				<?php echo $this->form->getInput('start_date'); ?>
			</div>
		</div>
		<?php
			if (!$this->daysConfig)
			{
			?>
				<div class="col-xs-8 col-sm-6">
					<div class="form-group">
						<i class="fa fa-calendar af-pr-5 text-primary af-font-bold" aria-hidden="true"></i>
						<?php echo $this->form->getLabel('end_date'); ?>
						<?php echo $this->form->getInput('end_date'); ?>
					</div>
				</div>
				<input type="hidden" name="jform[days_limit]" id="jform_days_limit" value="" />
		<?php
			}
			else
			{
			?>
				<div class="col-xs-8 col-sm-6">
					<div class="form-group">
						<?php echo $this->form->getLabel('days_limit'); ?>
						<?php echo $this->form->getInput('days_limit'); ?>
					</div>
				</div>
		<?php
			}
			?>
	</div>

	<?php
		$campaignType = array();

		// This type casting is needed for converting string to array
		$campaignType = (array) $this->params->get('camp_type');?>

		<div class="row af-mt-10">
			<div class="col-xs-12">
				<div class="form-group  launchForm__radio">
					<?php
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
						elseif(sizeof($campaignType) == 2)
						{
							echo $this->form->getLabel('type');
							echo $this->form->getInput('type');
						}
					}
					?>
				</div>
			</div>
		</div>

	<div class="row af-mt-10 launchForm__radio">
		<?php
			if (!empty($this->hideFields) && in_array('show_public', $this->hideFields) && $this->hideShowFields == 1)
			{
			?>
				<input type="hidden" name="jform[allow_view_donations]" id="jform_allow_view_donations" value="0" />
		<?php
			}
			else
			{
			?>
			<div class="col-xs-12 form-group">
				<?php
					echo $this->form->getLabel('allow_view_donations');
					echo $this->form->getInput('allow_view_donations');
				?>
			</div>
		<?php
			}

			if ($this->admin_approval == 1)
			{
			?>
				<input type="hidden" name="jform[published]" id="jform_published" value="0" />
			<?php
			}
			else
			{
			?>
			<div class="col-xs-12">
				<div class="form-group">
					<?php echo $this->form->getLabel('published');?>
					<?php echo $this->form->getInput('published'); ?>
				</div>
			</div>
			<?php
			}
			?>
	</div>

	<!--Dropdown Buttons-->
	<div class="row af-mt-10 launchForm__type">
		<div class="col-xs-12 col-sm-6">
			<div class="form-group">
				<label>
					<?php echo $this->form->getLabel('org_ind_type'); ?>
				</label>
				<?php echo $this->form->getInput('org_ind_type'); ?>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6">
			<div class="form-group">
				<label>
					<?php echo $this->form->getLabel('category_id'); ?>
				</label>
				<?php echo $this->form->getInput('category_id'); ?>
			</div>
		</div>
	</div>

	<!--USD input-->
	<div class="row">
		<div class="col-xs-8 col-sm-6 col-md-7 launchForm__goal af-mt-10">
			<div class="form-group">
				<?php echo $this->form->getLabel('goal_amount'); ?>
				<?php echo $this->form->getInput('goal_amount'); ?>
			</div>
		</div>
		<div class="col-xs-12 af-mt-10 launchForm__radio">
			<div class="form-group">
				<?php
				if (!empty($this->hideFields) && in_array('allow_exceed', $this->hideFields) && $this->hideShowFields == 1)
				{
				?>
					<input type="hidden" name="jform[allow_exceed]" id="jform_allow_exceed" value="0" />
				<?php
				}
				else
				{
				?>
				<?php
					echo $this->form->getLabel('allow_exceed');
					echo $this->form->getInput('allow_exceed');
				}?>
			</div>
		</div>
	</div>
	<!--Donation min/max-->
	<div class="row af-mt-10">
		<div class="col-xs-12 col-sm-6">
			<div class="form-group">
				<?php
					if (!empty($this->hideFields) && in_array('min_donation', $this->hideFields) && $this->hideShowFields == 1)
					{
					?>
						<input type="hidden" name="jform[minimum_amount]" id="jform_minimum_amount" value="0" />
				<?php
					}
					else
					{
					?>
						<label>
							<?php echo $this->form->getLabel('minimum_amount'); ?>
						</label>
				<?php
					echo $this->form->getInput('minimum_amount');
					}
					?>
			</div>
		</div>

		<div class="col-xs-12 col-sm-6">
			<div class="form-group">
				<?php
					if (!empty($this->hideFields) && in_array('max_donation', $this->hideFields) && $this->hideShowFields == 1)
					{
					?>
						<input type="hidden" name="jform[max_donors]" id="jform_max_donors" value="0" />
				<?php
					}
					else
					{
					?>
						<label>
							<?php echo $this->form->getLabel('max_donors'); ?>
						</label>
						<?php echo $this->form->getInput('max_donors');
					}
					?>
			</div>
		</div>
	</div>

	<div class="col-xs-12">
		<div class="form-group">
			<?php
				$integration = $this->params->get('integration');

				if ($integration == 'jomsocial')
				{
					echo $this->form->getInput('js_groupid');
				}
				?>
		</div>
	</div>
</div>
<div class="col-xs-12 col-md-5 col-md-offset-2">
	<div class="row">
		<!--Image Upload-->
		<?php
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
		<div class="progress af-d-none" id="campaign-progress-bar">
			<div class="progress-bar progress-bar-striped active af-w-100" role="progressbar"
			aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
				100%
			</div>
		</div>
		<div class="col-sm-4 col-md-6 col-xs-12">
			<div class="form-group jgive_campaign_upload">
				<input type="hidden" name="jform[image][new_image]" id="jform_campaign_image" value="<?php echo $mediaId;?>" />
				<input type="hidden" name="jform[image][old_image]" id="jform_campaign_old_image" value="" />
				<img src="<?php echo $this->campaignImage; ?>" id="uploaded_media" class="img-responsive">
			</div>
		</div>

		<div class="col-xs-12 col-sm-6">
			<div class="form-group">
				<label for="image">
					<?php echo $this->form->getLabel('image');?>
				</label>
				<?php echo $this->form->getInput('image'); ?>
			</div>
		</div>

		<div class="col-xs-12">
			<div class="alert alert-info">
				<?php
					echo Text::_('COM_JGIVE_MAIN_IMAGE_SIZE') .
					$this->params->get('large_width') . Text::_('COM_JGIVE_MAIN_IMAGE_DIMENSIONS_UNIT') .
					Text::_('COM_JGIVE_MAIN_IMAGE_DIMENSIONS_SIGN') .
					$this->params->get('large_height') . Text::_('COM_JGIVE_MAIN_IMAGE_DIMENSIONS_UNIT');
				?>
				<p><?php echo Text::sprintf('COM_JGIVE_ALLOWED_ATTACHMENTS_FILE_TYPE', 'jpeg,png,jpg');?></p>
			</div>
		</div>
	</div>
	<div class="row">
		<!--Description-->
		<div class="col-xs-12">
			<div class="launchForm__desc form-group">
				<?php
					if (!empty($this->hideFields) && in_array('long_desc', $this->hideFields) && $this->hideShowFields == 1)
					{
					?>
						<input type="hidden" name="jform[long_description]" id="jform_long_description" value="" />
				<?php
					}
					else
					{
					?>
						<label for="description">
							<?php echo $this->form->getLabel('long_description');?>
						</label>
				<?php
						echo $this->form->getInput('long_description');
					}
					?>
			</div>
		</div>

	<div class="row">
		<!--Meta Ddata-->
		<div class="col-xs-12">
			<div class="form-group">
				<label for="metadata">
					<?php echo $this->form->getLabel('meta_data');?>
				</label>
				<?php echo $this->form->getInput('meta_data'); ?>
			</div>
		</div>
	</div>
		<!--Meta Description-->
			<div class="row">
		<div class="col-xs-12">
			<div class="form-group">
				<label for="meta_desc">
					<?php echo $this->form->getLabel('meta_desc');?>
				</label>
				<?php echo $this->form->getInput('meta_desc'); ?>
			</div>
		</div>
		</div>
	</div>
</div>
