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
defined('_JEXEC') or die(';)');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.formvalidator');
$skip_reg = Text::_('COM_SKIP_REGISTRATION_PROCEED_PAYMENT');
$sign_up  = Text::_('COM_JGIVE_BUTTON_SAVE_TEXT_REG');
?>
<script type="text/javascript">
function reg_hideshow(skip_reg,sign_up)
{
	var divstyle=document.getElementById('registration_form').style.display;
	if(divstyle=="none")
	{
		document.getElementById('registration_form').style.display="block";
		document.getElementById('nextbtn').value=sign_up;
	}
	else
	{
		document.getElementById('registration_form').style.display="none";
		document.getElementById('nextbtn').value=skip_reg;
	}

}
</script>

<?php
$js = "Joomla.submitbutton = function(pressbutton){";

	$js .= "var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform(pressbutton);
			return;
		}
		{
			submitform(pressbutton);
			return;
		}
	}

	function submitform(pressbutton){
		 if (pressbutton) {
			document.adminForm.task.value = pressbutton;
		 }
		 if (typeof document.adminForm.onsubmit == 'function') {

		 	document.adminForm.onsubmit();
		 }
		 	document.adminForm.submit();
	}
	";
$document = Factory::getDocument();
$document->addScriptDeclaration($js);
$users = Factory::getuser();?>
<div id="qtc_maindiv">
	<div id="qtc_admin">
		<div class="techjoomla-bootstrap" >
			<form action="" method="post" name="adminForm" class=" form-horizontal form-validate" id="adminForm">
				<div id="editcell"  class="container">
					<!-- Header toolbar -->
					<legend class="componentheading"><?php echo Text::_('COM_JGIVE_SA_REGISTER');?></legend>
					<div id="qtc_chkmthd1"  class="broadcast-expands" >
						<div id="login_button">
							<div>
								<fieldset>
										<a href='<?php
											$msg = Text::_('COM_JGIVE_LOGIN_MSG');
											$uri = 'index.php?option=com_jgive&view=donations&layout=payment';
											$url = base64_encode($uri);
											echo 'index.php?option=com_users&view=login&return=' . $url; ?>'>
											<div>
												<input 
												id="LOGIN" 
												class="btn btn-success btn-sm validate" 
												type="button" 
												value="<?php echo Text::_('COM_JGIVE_BUTTON_LOGIN_TEXT_REG');?>">
											</div>
										</a>
								</fieldset>
							</div>
						</div>
					</div>

					<legend class="componentheading"><?php echo Text::_('COM_JGIVE_OR_REGISTER');?></legend>
					<fieldset>
						<?php
						$user = Factory::getUser();

						if (!$user->id)
						{
							?>
							<div id="qtc_chkmthd1"  class="broadcast-expands" >
								<div class="paddleft">
									<div class="control-group">
										<label for="email1" class="form-label">
											<?php echo HTMLHelper::tooltip(
												Text::_('COM_JGIVE_SEL_CHK_MTHD_TOOLTIP'),
												Text::_('COM_JGIVE_SEL_CHK_MTHD'),
												'',
												Text::_('COM_JGIVE_SEL_CHK_MTHD')
											)?>
										</label>
										<div class="controls">
											<label class="checkbox">
												<input 
												type="checkbox" 
												id="guest_regis" 
												name="guest_regis" 
												value="1" 
												checked="checked"
												class="form-check-input"
												onchange="reg_hideshow('<?php echo $skip_reg;?>','<?php echo $sign_up;?>')">
												<?php echo Text::_('COM_JGIVE_CHK_REGIS'); ?>
											</label>
										</div>
									</div>
								</div>
							</div>
						<?php
						}
						?>
						<div id="registration_form">
							<div class="control-group">
								<label class="form-label"  for="user_name">
										<?php echo HTMLHelper::tooltip(
											Text::_('COM_JGIVE_USER_NAME_TOOLTIP'),
											Text::_('COM_JGIVE_USER_NAME'), '',
											Text::_('COM_JGIVE_USER_NAME')
										);?>
								</label>
								<div class="controls">
									<input class="inputbox form-control validate-name" type="text" name="user_name" id="user_name" size="10" maxlength="50" value=""/>
								</div>
							</div>
							<div class="control-group">
								<label class="form-label"  for="user_email">
									<?php echo HTMLHelper::tooltip(
										Text::_('COM_JGIVE_USER_EMAIL_TOOLTIP'), Text::_('COM_JGIVE_USER_EMAIL'), '',
										Text::_('COM_JGIVE_USER_EMAIL')
									);?>
								</label>
								<div class="controls">
									<input class="inputbox form-control validate-email" type="text" name="user_email" id="user_email" size="20" maxlength="100" value=""/>
								</div>
							</div>
							<div class="control-group">
								<label class="form-label" for="confirm_user_email">
									<?php echo HTMLHelper::tooltip(
										Text::_('COM_JGIVE_CONFIRM_USER_EMAIL_TOOLTIP'),
										Text::_('COM_JGIVE_CONFIRM_USER_EMAIL'),
										'',
										Text::_('COM_JGIVE_CONFIRM_USER_EMAIL')
									);?>
								</label>
								<div class="controls">
									<input class="inputbox form-control validate-email" type="text" name="confirm_user_email" id="confirm_user_email" size="20" maxlength="100" value="" />
								</div>
							</div>
<!--
   </fieldset>
-->
							<div id="nextbtndiv">
								<input 
									id="nextbtn" 
									class="btn btn-success btn-sm validate" 
									type="submit" onclick="save();"  
									value="<?php echo Text::_('COM_JGIVE_BUTTON_SAVE_TEXT_REG');?>">
							</div>
							<div class="clr" ></div>
						</div>
					</fieldset>
					<input type="hidden" name="option" value="com_jgive" />
					<input type="hidden" name="task" value="registration.save" />
					<input type="hidden" name="Itemid" value="<?php echo $this->itemId;?>"/>
					<?php echo HTMLHelper::_('form.token');?>
				</div>
			</form>
		</div>
	</div>
	<?php
	$document   = Factory::getDocument();
	$renderer	= $document->loadRenderer('modules');
	$position	= 'tj_login';
	$options	= array('style' => 'raw');
	echo $renderer->render($position, $options, null);?>
	<div class="clr"></div>
</div>

