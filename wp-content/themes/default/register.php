<?php /* Template Name: Register */ ?> 
<?php get_header(); ?>
<?php //require_once('includes/insidesearch.php'); ?>
<div id="wrap">
<div id="page-content" class="clearfix">
<h2 class="headline1">Get Started with <?php echo bloginfo('name') ?> for free</h2>
<div class="page-content-left">
<?php wp_print_scripts(array('jquery-validate')); ?>
<?php if($_POST) {
		extract($_POST);
		$username = Site::cQ($username);		
		$email = Site::cQ($email);	
		$password = Site::cQ($password);	
		$first_name = Site::cQ($first_name);	
		$last_name = Site::cQ($last_name);	
		$country = Site::cQ($country);	
		$subscribe = Site::cQ($subscribe);	
		
		$username_exists = false;
		if(Customer::userExists($username)) {$username_exists = true;}
		if(!Customer::emailExists($email) && !$username_exists) {
			$data = array(
				'user_login' => $username,
				'user_pass' => $password,
				'user_nicename' => $first_name.' '.$last_name,
				'user_email' => $email,
				'display_name' => $first_name.' '.$last_name,
				'user_active' => '1',
				'first_name' => $first_name,
				'last_name' => $last_name,
				'country' => $country,
				'subscribe' => $subscribe
			);			
			$success = Customer::register($data, $activataionKey);
			$data = array(
				'[NAME]' => $first_name.' '.$last_name,
				'[ACTIVATION_KEY]' => $activataionKey,
			);
			Site::sendMail('register', $data, $email, "Registration Confirmation"); ?>
			<div class="message"><ul><li class="success">Thank you for registering on <?php bloginfo('name'); ?>. You will receive an email shortly. <br />Click on the activation link in it to activate your account.</li></ul></div>
		<?php } else {
			$email_exists = true;
		}
	} ?>
<?php if(!$_POST || !$success) { ?>
	
	<form id="form-register" class="form" action="" method="post">
		<table>
			<tr>
				<td height="30" width="113">First name: <span class="mand">*</span></td>
				<td><input type="text" name="first_name" value="<?php if(isset($_POST['first_name'])) echo $_POST['first_name'] ?>" /></td>
			</tr>
			<tr>
				<td height="30" width="113">Last name:</td>
				<td><input type="text" name="last_name" value="<?php if(isset($_POST['last_name'])) echo $_POST['last_name'] ?>" /></td>
			</tr>
			<tr>
				<td height="30" width="113">Desired Login name: <span class="mand">*</span></td>
			<td><input type="text" name="username" value="<?php if(isset($_POST['username'])) echo $_POST['username'] ?>" /><?php if($username_exists) { ?><em class="error" style="display: block">Username already exists. Please choose another one.</em><?php } ?><br /><span>eg. john, smith.poul</span></td>
			</tr>
			<tr>
				<td height="30">Email: <span class="mand">*</span></td>
				<td><input type="text" name="email" value="<?php if(isset($_POST['email'])) echo $_POST['email'] ?>" /><?php if($email_exists) { ?><em class="error" style="display: block">Email already exists. Please choose another one.</em><?php } ?></td>
			</tr>
			<tr>
				<td height="30">Password: <span class="mand">*</span></td>
				<td><input type="password" id="password" name="password" /></td>
			</tr>
			<tr>
				<td height="30" width="74">Repeat Password: <span class="mand">*</span></td>
				<td><input type="password" name="repeat_password" /></td>
			</tr>
			<tr>
				<td height="30" width="74">Country of Residence:</td>
				<td><?php echo FormField::countrySelect('country', '', ' style="width: 238px;" ', array(''=>'Select')) ?></td>
			</tr>
		</table>
		<span>
			<input class="chk_box" type="checkbox" checked="checked"  name="terms" /><label>I accept the user <a class="thickbox" href="<?php echo SITEURL; ?>/terms-of-use" target="_blank" >Terms &amp; Condition</a></label><br />
			<input class="chk_box" type="checkbox" name="subscribe" value="1" <?php if(isset($_POST['subscribe'])) echo "checked=checked" ?> /><label>Subscribe to Newsletter</label>
		</span>
		<input class="register button" type="submit" value="Register" />
	</form>
	
	
	
	<script type="text/javascript">
		$(function(){
			var validator = $("#form-register").validate({ 
				rules: {
					first_name: {
						required:true
					},
					username: {
						required:true
					},
					email: {
						required:true,
						email:true
					},
					password: {
						required:true,
						minlength: 6
					},
					repeat_password: {
						required:true,
						equalTo: "#password"
					}		
				},
				messages: {
					first_name: {
						required: "Please enter first name"
					},
					username: {
						required: "Please enter username"
					},
					email: {
						required: "Please enter email address",
						email:"Please enter a valid email address"
						
					},
					password: {
						required: "Please enter password",
						minLength: "Minimum six characters required"
						
					},
					repeat_password: {
						required: "Please repeat your password",
						equalTo: "Passwords not matching"
						
					}
				},
				errorElement: "em",
				success: function(label) {
					label.hide();
				}
			});
			
		});
	</script>
</div>
<div class="page-content-right">
<table cellspacing="0" cellpadding="0" border="0" width="280">
<tr>
<td><h1>Registering allows you to</h1></td>
</tr>
<tr>
<td><ul class="bulletlist">
<li>Post Free Ads</li>
<li>Reserve your own nickname</li>
<li>Manage your Ads &amp; Replies </li>
</ul>
</td>
</tr>
</table>
</div>
<?php } ?>
</div>
</div>
<?php get_footer(); ?>