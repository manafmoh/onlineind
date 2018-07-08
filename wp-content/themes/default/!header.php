<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php if (is_home () ) { bloginfo('name'); }elseif ( is_category() ) { single_cat_title(); echo ' - ' ; bloginfo('name'); }
elseif (is_single() ) { single_post_title();}
elseif (is_page() ) { bloginfo('name'); echo ': '; single_post_title();}
else { wp_title('',true); } ?></title>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="keywords" content="real estate, online india" />
<meta name="description" content="<?php bloginfo('description'); ?>" />
<meta name="robots" content="All" />
<meta name="copyright" content="<?php bloginfo('name'); ?>" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLEURL.'/style.css' ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLEURL.'/js/jquery.tabs.css' ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLEURL.'/js/fancybox/jquery.fancybox-1.3.1.css' ?>" />
<?php 
if ( !is_admin() ) :
wp_deregister_script( 'jquery' );
wp_register_script( 'jquery', ( STYLEURL.'/js/jquery-1.4.1.min.js' ), false, '1.4.1', false );
wp_enqueue_script( 'jquery' );
endif;
wp_enqueue_script('script', STYLEURL.'/js/script.js');
wp_enqueue_script('tabs.pack', STYLEURL.'/js/jquery.tabs.pack.js');
wp_enqueue_script('jquery-validate', STYLEURL.'/js/jquery.validate.min.js');
wp_enqueue_script('fancybox', STYLEURL.'/js/fancybox/jquery.fancybox-1.3.1.pack.js');
wp_print_scripts(array('jquery', 'script', 'jquery-validate')); ?>

</head>
<body>
	<div id="main" class="clearfix">
		<div id="top">
		<div class="loginbox">
			<?php $login =  Customer::current();   ?>
			<?php if(!$login): ?>
			<form action="#" method="get" class="login" id="login_form" onsubmit="if(this.email.value=='username'){this.email.focus(); return(false);}">
				<input class="btn" tabindex="3" type="image" name="search-btn" src="<?php echo STYLEURL ?>/images/login.jpg"/>
				<input class="txt" tabindex="2"  name="password" type="password"  />
				<input class="txt" tabindex="1"  name="email" type="text" value="username" onfocus="if(this.value=='username') this.value='';" onblur="if(this.value=='') this.value='username';"  />
				<input type="hidden" name="__redirect" id="__redirect" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />	</form>
			<label class="message"></label>
			<?php else: ?>
			<label class='message'>Welcome <?php echo $login->nickname ?></label>
			<a class="logout" href='javascript:logout();' onclick='logout();'>Logout</a>
			<?php endif; ?>
			</div>
			<ul>
				<li class="first"><?php echo date('l, d M Y') ?></li>  
				<?php if(!$login): ?>   
				<li><a href="/register">Create an Account</a></li>    
				<li class="last"><a href="/forgot-password">Forgot your Password?</a></li>
				<?php else: ?>
				<li><a href="/my-account/manage">Manage Account</a></li>    
				<li class="last"><a href="/my-account/change-password">Change Password</a></li>
				<li><a href="/my-account/saved-properties">Saved Ads</a></li> 
				<?php endif; ?>
			</ul>
		</div>
		
