<?php 	$cur_uid = Site::getCurrentUserId(); $num = Property::getEmailAlertPropertyCount($cur_uid); ?>
<?php   $results = Property::getEmailAlertProperty($cur_uid); global $gRating; ?>
<div id="email-alerts">
	<h2><?php the_ttftext("Email Alerts", true, "search"); ?></h2>
	<?php if($results) { ?>
	<p>Remove the properties of your choice from the below list</p>
	<ul class="listing">
	<?php foreach ($results as $row) { ?>
		<li>
			<div class="hme-section">
				<?php $img = "noimage.gif"; if($row->image){ $img = $row->image; } ?>
				<a href="<?php echo Property::getPermalink($row->slug); ?>"><img src="<?php echo Site::getThumbUrl($img,116,87); ?>" alt="<?php echo $row->title; ?>" title="<?php echo $row->title; ?>" /></a>
				<h3><a href="<?php echo Property::getPermalink($row->slug); ?>"><?php echo $row->title;?></a></h3>
				<p><?php if($row->no_bedrooms){ echo  $row->no_bedrooms." Bedroom,";}if($row->area){echo $row->area." sq.ft,"; } ?><a href="<?php echo Type::getPermalink($row->property_slug); ?>" > <?php echo $row->propertytype; ?></p>
				<a href="<?php echo Property::getPermalink($row->slug);?>" class="link">Details</a>
				<?php if(Customer::IssavedProperty($row->id)){?><a href="javascript:;" onclick="Site.saveProperty(this,<?php echo $cur_uid;?>,<?php echo $row->id; ?>)" class="link">Save this property</a><?php } ?>
			</div>
			<div class="price-section">
				<h4><?php echo  Site::showPrice($row->price); if($row->type=="rent"){ echo "/yr"; } ?></h4>
				<?php $rating=$gRating->getRating('property', $row->id);
							$rate=$rating['full']; 	?>
				<img class="rating-star" src="<?php echo STYLEURL;?>/images/rank<?php echo $rate;?>.gif" border="0" alt="rating" width="54" height="10" />
				<p><span>Added by:</span><a href="<?php echo $row->agent_slug;?>"><?php echo $row->agent_name;  ?></a></p>
				<p class="remove"><a href="javascript:;" onclick="Site.deleteEmailAlert(this,<?php echo $cur_uid;?>,<?php echo $row->id; ?>)">Remove alert</a></p>
			</div>
		</li>
		<?php } ?>
	</ul>
	<?php if($num > 5) { ?>
	<div id="paging">
					<label>Pages:</label><?php Site::showPaging($num,5,1,'Site.loademailAlertProperty');  ?>
		</div>
		<?php } }else { ?>
		<p class="notice-msg">There is no email alerts properties</p>
		<?php } ?>
</div>