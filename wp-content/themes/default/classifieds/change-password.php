<div id="change-password">
	<h2 class="headline1">Change Password</h2>
		<?php if($_POST) {
		extract($_POST);
		
		if(wp_check_password($current_password, $wp_query->data->user_pass, $wp_query->data->ID)) {
			$data = array(
				'ID' => $wp_query->data->ID,
				'user_pass' => $password
			);			
			$success = Customer::updateProfile($data); ?>
			<h2>Password updated successfully</h2>
		<?php } else {
			$invalid_password = true;
		}
	} ?>
	<?php if(!$_POST || !$success) { ?>
	<script type="text/javascript">
		$(function(){
			var validator = $("#frm-password").validate({ 
				rules: {
					current_password: {
						required:true
					},
					password: {
						required:true,
						minLength: 6
					},
					repeat_password: {
						required:true,
						equalTo: "#password"
					}
				},
				messages: {
					current_password: {
						required: "Please enter current password"
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
	<form id="frm-password" action="" method="post">
		<table>
			<tr>
				<td width="130" height="50"><label>Current Password:</label> <span class="mand">*</span></td>
				<td><input type="password" name="current_password" /><?php if($invalid_password) { ?><em class="error" style="display: block">Password not correct</em><?php } ?></td>
			</tr>
			<tr>
				<td width="64" height="50"><label>New Password:</label> <span class="mand">*</span></td>
				<td><input type="password" id="password" name="password" /></td>
			</tr>
			<tr>
				<td height="50" width="74"><label>*Confirm Password:</label> <span class="mand">*</span></td>
				<td><input type="password" name="repeat_password" /></td>
			</tr>
			<tr>
				<td  height="40"></td>
				<td><input  type="submit" class="button" value="Change Password" style="width:130px;"  />
				<input type="hidden" name="_buffer" value="1" />
				</td>
			</tr>
		</table>
	</form>
	
	<?php } ?>
</div>