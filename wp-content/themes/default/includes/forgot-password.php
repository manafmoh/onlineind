<div id="login">
<?php if($_POST) {
	extract($_POST);
	$email = Site::cQ($email);
	$user = Customer::getUserByEmail($email);
	if($user) {
		$activationKey = md5(uniqid(microtime()));
		Customer::setActivationKey($activationKey, $user->ID);
		$data = array(
			'[NAME]' => $user->display_name,
			'[ACTIVATION_KEY]' => $activationKey
		);
		Site::sendMail('reset', $data, $email, "Reset Password"); ?>
		<p>Instructions for resetting password is sent to the provided email address.</p>
	<?php } else {
		$failed = true;
	}
} ?>
<?php if(!$_POST || $failed) { ?>
	<p><?php if($failed) { ?>
		Couldn't find email address in our database.
	<?php } else { ?>
		If you are already a member and forgot your password, please enter below your email. Instructions to reset password will be send to your email address.
	<?php } ?></p>
	<form id="frm-password" action="" method="post">
		<table>
			<tr>
				<td width="113">Email:</td>
				<td><input type="text" name="email" /></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input class="login button" type="submit" src="<?php echo STYLEURL; ?>/images/login.gif" value="Submit"/>
				</td>
			</tr>
		</table>
	</form>
	<script type="text/javascript">
		$(function(){
			var validator = $("#frm-password").validate({ 
				rules: {
					email: {
						required:true,
						email:true
					}		
				},
				messages: {
					email: {
						required: "Please enter email address",
						email:"Please enter a valid email address"
						
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