<div id="login">
<?php if($_POST) {
	extract($_POST);
	$email = Site::cQ($email);
	$password = Site::cQ($password);
	$remember = Site::cQ($remember);
	$isActive = Customer::isActive($email);
	if($isActive) {
		if(Customer::login($email, $password, $remember)) {
			$redirect = SITEURL.'/my-account/manage/';
			if($_ru) {
				$redirect = SITEURL.$_ru;
			}
			wp_redirect($redirect);
			exit();
		} else {
			$loginFailed = true;
		}
	} else {
		$loginFailed = true;
	}
} ?>
<?php if(!$_POST || $loginFailed) { ?>
	<?php if($loginFailed) { ?>
		<p class="error-msg"><?php if(!$isActive) { ?>
		Your account is not active. If you have just registered, click on the activation link in the confirmation mail to activate your account, otherwise; contact site administrator.
		<?php } else { ?>
		Login failed!
		<?php } ?>
	<?php } else { ?>
		<p>If you are already a member, please enter below your login details:
	<?php } ?></p>
	<form id="form-login" action="" method="post">
		<table>
			<tr>
				<td height="30" width="113">Username: <span class="mand">*</span></td>
				<td><input type="text" name="email" /></td>
			</tr>
			<tr>
				<td height="30">Password: <span class="mand">*</span></td>
				<td><input type="password" name="password" /></td>
			</tr>
			<tr>
				<td></td>
				<td><input class="chk_box" type="checkbox" name="remember" /><label>Remember me</label></td>
			</tr>
			<tr>
				<td></td>
				<td><p style="padding-top:3px">Forgot your password? <a href="<?php echo SITEURL; ?>/forgot-password/">Password reminder.</a></p>
					<input class="login button" type="submit" value="Sign In" />
					<input type="hidden" name="_buffer" value="1" />
					<input type="hidden" name="_ru" value="<?php echo $_REQUEST['_ru']; ?>" />
				</td>
			</tr>
		</table>
	</form>
	<script type="text/javascript">
		$(function(){
			var validator = $("#form-login").validate({ 
				rules: {
					email: {
						required:true
					},
					password: {
						required:true
					}		
				},
				messages: {
					email: {
						required: "Please enter your username"
						
					},
					password: {
						required: "Please enter your password"						
					}
				},
				errorElement: "em",
				success: function(label) {
					label.hide();
				}
			});
			
		});
	</script>
<?php } ?>	
</div>