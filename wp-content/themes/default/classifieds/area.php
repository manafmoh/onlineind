<?php get_header(); ?>
<script type="text/javascript">
function submitform() {
  document.frmsort.submit();
}
function changeCurrency(){
	  document.changecurrency.submit();
}
</script>
<?php $current_page = 1; if($_GET['page']>0){$current_page = $_GET['page'];} ?>

<?php if($_REQUEST['action']=='changecurrency') {  $currency = Site::getCurrenctCurrency(); if($_GET['currency']){ $currency = $_GET['currency']; }
 Site::setCurrenctCurrency($currency); }?>
<?php $results = $wp_query->data['area']; $headings = $results[0]->area_name; ?> 
<?php $num = Property::getCountbyArea($results[0]->area_slug); global $gRating;?>

<div id="content">
				<h1><?php the_ttftext($headings,true,"search"); ?></h1>
				<ul class="listing prop-listing">
				<?php foreach ($results as $row) {?>
					<li>
						<div class="hme-section">
							<?php $img = "noimage.gif"; if($row->image){ $img = $row->image; } ?>
							<a href="<?php echo Property::getPermalink($row->slug); ?>" ><img src="<?php echo Site::getThumbUrl($img,116,87); ?>" alt="<?php echo $row->title; ?>" title="<?php echo $row->title; ?>" /></a>
							<h3><a href="<?php echo Property::getPermalink($row->slug); ?>"><?php echo $row->title; ?></a></h3>
							<p><?php if($row->no_bedrooms){ echo  $row->no_bedrooms." Bedroom,";}if($row->area){echo $row->area." sq.ft,"; }?><a href="<?php echo Type::getPermalink($row->type_slug); ?>"><?php echo $row->type_name; ?></a></p>
							<a href="<?php echo Property::getPermalink($row->slug);?>" class="link">Details</a>
							<?php if(Customer::isLoggedIn() && Customer::IssavedProperty($row->id)){?><a href="javascript:;" onclick="Site.saveProperty(this,<?php echo Site::getCurrentUserId();?>,<?php echo $row->id; ?>)" class="link">Save this property</a><?php } ?>
						</div>
						<div class="price-section">
						<?php if($row->price) { ?>
							<h4> <?php echo  Site::showPrice($row->price);if($row->type=="rent"){ echo "(PerYear)";} ?></h4>
						<?php } ?>
							<?php $rating=$gRating->getRating('property', $row->id);
							$rate=$rating['full']; 	?>
							<img class="rating-star" src="<?php echo STYLEURL;?>/images/rank<?php echo $rate;?>.gif" border="0" alt="rating" width="69" height="13" />
							<p><span>Added by:</span><a href="<?php echo Agent::getPermalink($row->agent_slug);?>"><?php echo  $row->agent_name; ?></a></p>
							<p class="call-back"><a href="javascript:;" onclick="Site.showDialog('callback-popup<?php echo $row->id; ?>', 'requestcallback-pop.php?property_id=<?php echo $row->id; ?>&agent_id=<?php echo $row->agent_id;?>', 'indicator');">Request call back</a></p>
						</div>
						<div class="pop"><iframe allowtransparency="true" frameborder = "0" id="callback-popup<?php echo $row->id; ?>" class="popup-listing" scrolling="no" src =""></iframe></div>
					</li>
					<?php } ?>
				</ul>
			
				<?php if($num > 5) { ?>
				<?php Site::paging($num,5,$current_page); } ?>
			<?php require_once TEMPLATEPATH.'/includes/bottom-ad.php'; ?>
		</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
