<?php get_header(); ?>
<?php //require_once('includes/insidesearch.php'); ?>
<div id="wrap">
<?php get_sidebar(); ?>
<div id="content" class="clearfix">
<div class="ads">
	<script type="text/javascript"><!--
google_ad_client = "ca-pub-6093044357039023";
/* Leaderboard */
google_ad_slot = "6627631566";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</div>
<?php 
$uri =  parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$uri_segments = split('/', Site::drop_multiple_slashes(ltrim($uri ,'/')));
?>
	<?php 
	$level = count($uri_segments);
		switch ($level) {
			case 2:
				Site::searching($state='',$uri_segments[1]);
				break;
			case 3:
				Site::searching($state='',$uri_segments[1],$uri_segments[2]);
				break;	
			case 4:
				Site::searching($state='',$uri_segments[1],$uri_segments[2],$uri_segments[3]);
				break;				
		} ?>
</div>
<?php get_footer(); ?>
