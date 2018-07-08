<?php 	$cur_uid = Site::getCurrentUserId(); $num = Classified::getSavedClassifiedCount($cur_uid); ?>
<?php   $results = Classified::getSavedClassified($cur_uid); global $gRating;?>
<div id="saved-properties">
	<h2><?php the_ttftext("Saved Properties",true,"search"); ?></h2>
	<?php if($results) { ?>
	<p>Remove the properties of your choice from the below list</p>
	<ul class="listing">
	<?php foreach ($results as $row) { ?>
		<li>
			<div class="hme-section">
				<?php $img = "noimage.gif"; if($row->image){ $img = $row->image; } ?>
				<a href="<?php echo Classified::getPermalink($row->slug); ?>"><img src="<?php echo Site::getThumbUrl($img,116,87); ?>" alt="<?php echo $row->title; ?>" title="<?php echo $row->title; ?>" /></a>
				<h3><a href="<?php echo Classified::getPermalink($row->slug); ?>"><?php echo $row->title;?></a></h3>
				<p><a href="<?php echo SubCategory::getPermalink($row->property_slug);?>"><?php echo $row->classifiedtype; ?></a></p>
				<a href="<?php echo Classified::getPermalink($row->slug);?>" class="link">Details</a>
				<a class="link" href="javascript:;" onclick="Site.showDialog('sendenquery-popup<?php echo $row->id;  ?>', 'sendEnquery-pop.php?classified_id=<?php echo $row->id; ?>&agent_id=<?php echo $row->agent_id; ?>&sourcepage = property_detail', 'indicator');">Send Enquiry</a>
				<div style="position:relative"><iframe allowtransparency="true" frameborder = "0" class="popup-enquiry2" id="sendenquery-popup<?php echo $row->id;?>"  scrolling="no" src =""></iframe></div>	
			</div>
			<div class="price-section">
				<h4> <?php echo  Site::showPrice($row->price); if($row->type=="rent"){ echo "/yr"; } ?></h4>
				<?php $rating=$gRating->getRating('classified', $row->id);
							$rate=$rating['full']; 	?>
				<img class="rating-star" src="<?php echo STYLEURL;?>/images/rank<?php echo $rate;?>.gif" border="0" alt="rating" width="54" height="10" />
				<p><span>Added by:</span><a href="<?php echo Agent::getPermalink($row->agent_slug);?>"><?php echo $row->agent_name; ?></a></p>
				<p class="remove-prop"><a href="javascript:;" onclick="Site.deleteClassified(this,<?php echo $cur_uid;?>,<?php echo $row->id; ?>)" >Remove property</a></p>
			</div>
		</li>
	<?php } ?>
	</ul>
	
	<?php if($num >5) { ?>
	<div id="paging">
					<label>Pages:</label><?php Site::showPaging($num,5,$page,'Site.loadSavedClassified');  ?>
		</div>
		<?php } } else { ?>
		<p class="notice-msg">There is no saved properties</p>
		<?php } ?>
</div>
