<div id="myaccount">
<h2 class="headline1">Update My Profile</h2>
	<?php if($_POST) {
		extract($_POST);
		$first_name = Site::cQ($first_name);	
		$last_name = Site::cQ($last_name);	
		$country = Site::cQ($country);	
		$email = Site::cQ($email);	
		if(!Customer::userExists($email, $wp_query->data->ID)) {
			$data = array(
				'ID' => $wp_query->data->ID,
				'user_login' => $email,
				'user_nicename' => $first_name.' '.$last_name,
				'user_email' => $email,
				'display_name' => $first_name.' '.$last_name,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'country' => $country
			);			
			$success = Customer::updateProfile($data); ?>
			<h2>Profile updated successfully</h2>
		<?php } else {
			$email_exists = true;
		}
	} ?>
	<?php if(!$_POST || !$success) { ?>
	<script type="text/javascript">
		$(function(){
			var validator = $("#frm-profile").validate({ 
				rules: {
					first_name: {
						required:true
					},
					email: {
						required:true,
						email:true
					}
				},
				messages: {
					first_name: {
						required: "Please enter first name"
					},
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
	<p>Use the form fields below to edit any detailed related to your account.</p>
	<form id="frm-profile" action="" method="post">
		<table>
			<tr>
				<td height="40" width="120"><label>*First name:</label></td>
				<td><input type="text" name="first_name" value="<?php echo $_POST['first_name']?stripslashes($_POST['first_name']):$wp_query->data->first_name; ?>" /></td>
			</tr>
			<tr>
				<td height="40"><label>*Last name:</label></td>
				<td><input type="text" name="last_name" value="<?php echo $_POST['last_name']?stripslashes($_POST['last_name']):$wp_query->data->last_name; ?>" /></td>
			</tr>
			<tr>
				<td height="40"><label>*Email:</label></td>
				<td><input type="text" name="email" value="<?php echo $_POST['email']?stripslashes($_POST['email']):$wp_query->data->user_email; ?>" /><?php if($email_exists) { ?><em class="error" style="display: block">Email already exists. Please choose another one.</em><?php } ?></td>
			</tr>
			<tr>
				<td height="40"><label>Country of Residence:</label></td>
				<td><?php echo FormField::countrySelect('country', $_POST['country']?stripslashes($_POST['country']):$wp_query->data->country, ' style="width: 236px;" ', array(''=>'Select')) ?></td>
			</tr>
			<tr>
				<td  height="40"></td>
				<td><input class="save button" value="Save" type="submit"  /></td>
			</tr>
		</table>
	</form>
	
	<?php } ?>
</div>