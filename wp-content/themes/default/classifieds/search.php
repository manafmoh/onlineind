<?php get_header(); ?>
<?php //require_once('includes/insidesearch.php'); ?>
<div id="wrap">
<?php get_sidebar(); ?>
<div id="content" class="clearfix">
<div class="ads">
	<script type="text/javascript"><!--
	google_ad_client = "ca-pub-5853823947578032";
	/* Onlineind-leaderboard */
	google_ad_slot = "8752889078";
	google_ad_width = 728;
	google_ad_height = 90;
	//-->
	</script>
	<script type="text/javascript"
	src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
	</script>
</div>
<?php 
$keyword = Site::cQ($_REQUEST['keyword']);  
$category = $_REQUEST['category'];
Site::searching($state='','','','','',$keyword);
?>
</div>
<?php get_footer(); ?>
