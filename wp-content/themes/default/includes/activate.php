
<?php $key = trim($_REQUEST['key']);
if(Customer::activate($key, $user)) {
	$data = array(
		'[NAME]' => $user->display_name
	);
	Classified::updateClassifiedUserIdByEmail($user);
	Site::sendMail('welcome', $data, $user->user_email, "Welcome!");
	echo '<div class="message"><p class="successfull">Your account is successfully activated. You may now <a href="'.SITEURL.'/sign-in/" style="color:#39abc9">sign in</a> to manage your account.</p></div>';
} else {
	echo '<div class="message"><p class="warning">Invalid activation key.</p></div>';
}
?>
