<?php 
	require_once(dirname(__FILE__).'/wp-config.php');
	if($_REQUEST['__proc']) {
		$proc = $_REQUEST['__proc'];
		unset($_REQUEST['__proc']);
		if($_REQUEST['__class']) {
			$class=$_REQUEST['__class'];
			unset($_REQUEST['__class']);
			$proc = array($class, $proc);
		}
		if($_REQUEST['__object']) {
			$object=$_REQUEST['__object'];
			$proc = array($$object, $proc);
		}
		$registered_handlers = apply_filters('register_handler', array());
		if(in_array($proc, $registered_handlers)) {
			call_user_func($proc);
		}
	}