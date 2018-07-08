<?php global $wp_query; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="google-site-verification" content="e9CSWAsHa4Q73Lfb_0p3ifHkzvqEJ5lk0itnS_ESkDE" />
<?php $title = "Online India Classified Ads - 100% Free"  ?>
<?php $description = get_bloginfo('description') ; ?>
<?php $keyword = "Online India Classified Ads , 100% Free Indian Classifieds, Advertising Listings, Andhra Pradesh, Arunachal Pradesh ,Assam ,Bihar ,Chhattisgarh,Goa,Gujarat,Haryana,Himachal Pradesh,Jammu and Kashmir,Jharkhand
Karnataka,Kerala,Madhya Pradesh,Maharashtra,Manipur,Meghalaya,Mizoram,Nagaland,Orissa,Punjab,Rajasthan,Sikkim,Tamil Nadu,Tripura
Uttarakhand,Uttar Pradesh,West Bengal";  ?>
<?php if (is_home () ) { $title = get_bloginfo('name') ." - Online India Classified Ads - 100% Free";  ?>
<?php } elseif($wp_query->option == 'classifieds') {
	$uri =  parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
	$uri_segments = split('/', Site::drop_multiple_slashes(ltrim($uri ,'/')));
	$level = count($uri_segments);
switch ($level) {
	case 2:?>
		<?php $title = "Classified ads in ". ucfirst(str_replace('-', ' ', $wp_query->slug)) ." - ". ucfirst($wp_query->option) ." - ".get_bloginfo('name');
		$description = ucfirst(str_replace('-', ' ', $wp_query->slug)) ." - ". ucfirst($wp_query->option) ." - ".$description;
		//$keyword = "";
		break;
	case 3:
		$categoryName = RootCategory::getRootCategoryBySlug($uri_segments[2]);
		?>
		<?php $title = "Classified ads in ". ucfirst(str_replace('-', ' ', $wp_query->slug)) ." - ". ucfirst($wp_query->option)." - ". ucfirst($categoryName) ." - ".get_bloginfo('name');
		$meta = Meta::getMetaBySlug($uri_segments[2]); 
		$description = ucfirst(str_replace('-', ' ', $wp_query->slug)) ." - ". ucfirst($wp_query->option)." - ". ucfirst($categoryName) ." - ".preg_replace('/\s+/', ' ',$meta->description);
		$keyword = $meta->keyword;
		break;	
	case 4:
		$categoryName = RootCategory::getRootCategoryBySlug($uri_segments[2]);
		$SubCategoryName = SubCategory::getSubCategoryBySlug($uri_segments[3]);
		?>
		<?php $title = "Classified ads in ". ucfirst(str_replace('-', ' ', $wp_query->slug)) ." - ". ucfirst($wp_query->option)." - ". ucfirst($categoryName) ." - ". ucfirst($SubCategoryName). "-" .get_bloginfo('name');
		$meta = Meta::getMetaBySlug($uri_segments[2]);
		$description = ucfirst(str_replace('-', ' ', $wp_query->slug)) ." - ". ucfirst($wp_query->option)." - ". ucfirst($categoryName) ." - ". ucfirst($SubCategoryName). " - " .preg_replace('/\s+/', ' ',$meta->description);
		$keyword = $meta->keyword;
		
		break;				
	} 
}

elseif($wp_query->option == 'classified') { ?>
<?php $title =  ucfirst(str_replace('-', ' ', $wp_query->slug)) ."- ". ucfirst($wp_query->option) ."-". get_bloginfo('name');
	$description = preg_replace('/\s+/', ' ',Classified::getClassifiedDescriptionBySlug(str_replace('.html','',$wp_query->slug)));
	$keyword = "";
}
elseif (is_single() ) { $title = single_post_title('',false); $description = $title;}
elseif (is_page() ) { $title = get_bloginfo('name'). ' - '. single_post_title('',false); $description = $title;}

else { $title = ucfirst(str_replace('-',' ',$wp_query->slug)) .' - '.$title; $description = $title; } ?>

<title><?php echo $title; ?></title>
<meta name="description" content="<?php echo $description; ?>" />
<meta name="keywords" content="<?php echo $keyword; ?>" />
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="robots" content="All" />
<meta name="copyright" content="<?php bloginfo('name'); ?>" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLEURL.'/style.css?v=1.3' ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLEURL.'/js/jquery.tabs.css' ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLEURL.'/js/fancybox/jquery.fancybox-1.3.1.css' ?>" />
<link rel="shortcut icon" href="<?php echo SITEURL ?>/favicon.ico" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="http://cdn.webrupee.com/font">
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
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-20399520-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body>
	<div id="main" class="clearfix">
		<div id="top" style="position:relative;">
		<h1 class="logo"><a href="/"><?php bloginfo('name'); ?> </a></h1>
		<?php $location = Site::getLocationCookie();  ?>
		<?php $cities = District::getSelectedDistrict(); ?>
		<div class="wrap-city">
		<select class="selectcity"  name="place" onchange="changeLocation(this)"><option value="/">-- Home --</option>
			<?php foreach ($cities as $city=>$value): ?>	
				<option value="<?php echo $city ?>" <?php if($location == $city ):?> selected="selected" <?php endif ?>><?php echo $value ?></option>
			<?php endforeach; ?>			
		</select>
		<script type="text/javascript" >
			function changeLocation(loc) { 
				setLocationCookie(loc.value);
				}
		</script>
		
		<?php if($location): ?><h4 class="loc">
			<?php if(($location) == 'all'): ?>
				<?php echo ucfirst(str_replace('all','<< change city',$location)); ?>
			<?php else: ?>
				- <?php echo ucfirst($location) ?>
			<?php endif; ?>
		</h4><?php endif; ?>
		</div>
		<div class="loginbox">
			<?php $login =  Customer::current();   ?>
			<?php if(!$login): ?>
			<ul>
			<li><a href="/">Home</a></li>
			<li><a href="/register">Register</a></li>
			<li><a href="/sign-in">Sign In</a></li>
			<li class="last"><a href="/forgot-password">Forgot your Password?</a></li>
			</ul>
			<?php else: ?>
			<label class='message'>Welcome <?php echo $login->display_name ?></label>
			<ul>
				<li><a href="/my-account/manage">Manage Account</a></li>    
				<li><a href="/my-account/change-password">Change Password</a></li>
				<li class="last"><a class="logout" href='javascript:logout();' onclick='logout();'>Logout</a></li>
			</ul>
			<?php endif; ?>
			<ul id="special">		  
				<li class="png"><a class="post-ads png" href="/post-classified">Post Ads - FREE !!</a></li>
			</ul>
			</div>
		</div>
		
