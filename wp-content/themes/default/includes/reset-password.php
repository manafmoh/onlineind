<?php $key = trim($_REQUEST['key']);
$user = Customer::getUserByKey($key);
if($user) {
	$plainTextPassword = substr(md5(uniqid(microtime())), 0, 6);
	$password = wp_hash_password($plainTextPassword);
	Customer::setPassword($password, $user->ID);
	$data = array(
		'[EMAIL]' => $user->user_login,
		'[PASSWORD]' => $plainTextPassword
	);
	Site::sendMail('password', $data, $user->user_email, "New Password"); ?>
	<p class="success-msg">Successfully updated password and access details sent to your email address.</p>
<?php } else {
	echo '<p class="error-msg">Invalid activation key.</p>';
}