<?php get_header(); ?>
<?php  //require_once('includes/insidesearch.php');  ?>
<div id="wrap">
<?php Site::changeSidebar(); ?>
<div id="content" class="clearfix">
	<div id="personal-details">
		
	<!-- 	<p>Member since <?php echo date('F j, Y', Site::mysqlDateToTimestamp($wp_query->data->user_registered)); ?></p> -->
		<?php require_once "{$wp_query->slug}.php"; ?>
	</div>
	<?php //require_once TEMPLATEPATH.'/includes/bottom-ad.php'; ?>
</div>
</div>
<?php get_footer(); ?>

