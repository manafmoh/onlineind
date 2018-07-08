<?php get_header(); ?>
<?php //require_once('includes/insidesearch.php'); ?>
<?php global $query?>
<div id="wrap">
<?php get_sidebar(); ?>
<div id="content" class="clearfix">
<?php $results = $wp_query->data['state'];  ?>
<?php 
$uri =  parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$uri_segments = split('/', Site::drop_multiple_slashes(ltrim($uri ,'/')));
?>
<?php if($results){ ?>
<h1><?php  echo ucfirst(str_replace('.html','',$uri_segments[1])); ?></h1>
	<ul class="listing prop-listing">
	<?php foreach ($results as $row => $value) { ?>
		<li>
		<h1><a href="/classifieds/<?php echo $row ?>"><?php echo $value ?></a></h1>
		</li>
		<?php } ?>
	</ul>			
	<?php } else { ?>
	<h4 class="notice-msg">Your search criteria did not match any results. Please check your spelling and try again. You may browse through the recently posted ads below.</h4>
	<?php } ?> 
</div>
<?php get_footer(); ?>