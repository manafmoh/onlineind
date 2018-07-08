<div id="left" class="clearfix">
<div class="searchbox" style="min-height:280px;">
<h2>Most Viewed [<?php echo $subcat->name?>]</h2> 
<h2>&nbsp;</h2>
<?php $results = Classified::getMostViewedResult($dist_slug,$cat_slug,$subcat->slug);  ?>
<?php if($results):   ?>
<ul class="listing prop-listing">
<?php foreach ($results as $row) { ?>
	<li>
	<a href="<?php echo Classified::getPermalink($row->slug);?>" class="display_img">
	<?php //$imggal = unserialize($row->image_gallery); ?>
		<?php //if($imggal[0] ): ?>
		<!--<img class="list-img" alt="<?php //echo $row->title; ?>" title="<?php //echo $row->title; ?>" src="<?php //echo Site::getThumbUrl($imggal[0],45,45,3); ?>"/>-->
		<?php // else: ?>	
		<!--<img class="list-img" width="45" src="<?php //echo STYLEURL.'/images/no-image.gif' ?>" alt="<?php //echo $row->title; ?>" title="<?php //echo $row->title; ?>" />-->
		<?php //endif; ?>	
		<?php echo $row->title ?>	
						
	</a>
	<?php if($row->updated_date) { ?> [<small><?php echo $row->updated_date; ?></small>]<?php } ?>
	<div class="ruler" style="margin:10px 10px 0 0"></div>
	</li>
	<?php } ?>
</ul>
<?php endif; ?>
</div>
<br />
<div style="padding-left:5px">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-5853823947578032";
/* Small Square (200x200) */
google_ad_slot = "4143375038";
google_ad_width = 200;
google_ad_height = 200;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</div>
</div>


