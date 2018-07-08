<?php get_header(); ?>
<?php //require_once('includes/insidesearch.php'); ?>
<div id="wrap">
<?php get_sidebar(); ?>
<div id="content" class="clearfix">
<?php $results = $wp_query->data ?>
<?php if($results){ ?>
<p class="breadcrumb"><?php echo $searchtitle ?></p>
	<ul class="listing prop-listing">
	<?php foreach ($results as $row) { ?>
		<li>
		<h1><a href="/classifieds/<?php echo $row->slug ?>"><?php echo $row->name ?></a></h1>
		</li>
		<?php } ?>
	</ul>			
	<?php } else { ?>
	<h4 class="notice-msg">Your search criteria did not match any results. Please check your spelling and try again. You may browse through the recently posted ads below.</h4>
	<?php } ?> ?>
</div>
<?php get_footer(); ?>