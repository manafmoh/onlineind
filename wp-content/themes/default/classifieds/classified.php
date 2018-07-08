<?php $record = $wp_query->data['classified']; ?>
<?php get_header(); ?>
<div id="wrap">
<?php $subcat = SubCategory::getSubCategorySlugById($record->subcategory_id);  ?>
<?php $dist_slug = District::getDistrictSlugById($record->district_id); ?>
<?php $cat_slug = RootCategory::getRootCategorySlugById($record->category_id); ?>
<?php require_once('sidebar-classified.php')?>
<div id="detail-content" class="clearfix" style="margin-top:0px; padding-top:0" >
<div class="ads" style="padding-bottom:35px;">

</div>
<?php wp_print_scripts(array('tabs.pack','fancybox')); ?>
<?php Classified::addViewCount($record->id); ?>
 <div id="container-1">
            <ul>
                <li class="tabs-selected"><a href="#fragment-1"><span>Listing</span></a></li>
                <li><a class="contactinfo" href="#fragment-2"><span>Contact Info</span></a></li>
            </ul>
            <div id="fragment-1">
					<div id="overview-details">
					<div class="imgwrap">
					<?php $imggal = unserialize($record->image_gallery); ?>
						<?php if($imggal ){ ?>
						<img alt="" title="" src="<?php echo Site::getThumbUrl($imggal[0],180,180,3); ?>"/></a>
							<a class="showimg thumbimg" rel="img-gal" href="<?php echo Site::getImageUrl($imggal[0]); ?>" rel="imagebox-lights">View larger photo</a>
							<?php } ?>
						</div>
						<div class="view-block">
						<h1 <?php if($imggal ){ ?>style=" width:542px;" <?php } ?>><?php echo $record->title ?></h1>
						<div class="ruler2"></div>
						<p class="viewcount"><?php echo "View Count: ".$record->viewcount ?></p>
						<a href="javascript:;" class="reply">Reply</a>
						<?php if($subcat): ?>
						<a href="/classifieds/<?php echo $dist_slug ?>/<?php echo $cat_slug ?>/<?php echo $subcat->slug?>" class="view-all-cat">View All <?php echo $subcat->name?></a>
						<?php endif; ?>
						<h4><label>Ad Id: </label><?php echo Classified::getPropertyId($record->id) ?></h4>
						<?php $exCategory = array(6,8,11,12); ?>
						<?php if(!in_array($record->category_id, $exCategory)): ?>
						<h4><?php echo ucfirst($record->type); ?></h4>
						<?php endif; ?>
						<?php $price = Site::showPrice($record->price); ?>
						<?php if($price): ?>
						<h4>Rs. <?php echo $price  ?></h4>
						<?php endif; ?>
						 <!--  <div><label>Country: </label><?php echo $record->country ?></div> -->
              
	              
	              <h4><?php echo State::getStateById($record->state_id) ?>, <?php echo District::getDistrictById($record->district_id) ?> <?php if($record->location) echo ", ".$record->location ?></h4>
				  <h4><?php echo RootCategory::getRootCategoryById($record->category_id); ?>, <?php echo $subcat->name?></h4>
	              <h4><label>Date Posted: </label><?php echo $record->updated_date; ?>	</h4>	  
					  <!-- <h4><label>Place: </label><?php echo Place::getPlaceById($record->place_id) ?></h4> -->
						<!-- <ul class="reply">
							<?php if(Customer::isLoggedIn() && Customer::IssavedClassified($record->id)){?><li><a href="javascript:;" onclick="Site.saveClassified(this, <?php echo Site::getCurrentUserId();?>, <?php echo $record->id; ?>);" >Save this Classified</a></li><?php } ?>
						</ul> -->
						<div class="share">
						<div class="tweets-block">
							<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
							<a href="http://twitter.com/share" class="twitter-share-button">Tweet</a>
						</div>
						<div class="fb-block">
							<a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php">Share</a>
							<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
						</div>
						</div>
						<br />
					</div>
					<div class="ruler"></div>
					<div class="description"><?php echo $record->description ?></div>
					<?php if(count($imggal)> 1): ?>
					<div class="ruler"></div>
					<ul class="img-gal">
					
					<?php foreach ($imggal as $imgitem) { ?>
						<li>
						<a class="thumbimg" rel="img-gal" href="<?php echo Site::getImageUrl($imgitem); ?>" rel="imagebox-lights"><img alt="" title="" src="<?php echo Site::getThumbUrl($imgitem,80,80,3); ?>"/></a></li>
						<?php } ?>
					</ul>
					<?php endif; ?>
					</div>
            </div>
            <div id="fragment-2">
            <div class="contactinfo view-block">
             <h2>Reply to the poster of this ad</h2>
             <div class="ruler" style="margin-left:-10px;"></div>
            <h4><label>Ad Id: </label><?php echo Classified::getPropertyId($record->id) ?></h4>
              <h4><label>Name: </label><?php echo $record->fullname ?></h4>
              <h4><label>Phone: </label><?php echo $record->mobile ?></h4>
					<!-- <div><label>Email: </label><a href="mailto:<?php echo $record->email ?>"><?php echo $record->email ?></a></div> -->
            
				 
				  <div class="contactad">
				 
				  <form action="#" method="get" class="reply" id="reply_form" >
				  <h2 class="messagereply"></h2>
				  <table class="formreply" >
							<tr>
								<td width="120">Your Email <span class="mand">*</span></td><td><input type="text" name="email" /></td>
							</tr>
							<tr>
								<td>Your Contact No. </td><td><input type="text" name="telephone" /></td>
							</tr>
							<tr>
								<td>Your Message <span class="mand">*</span></td>
								<td><textarea class="bigarea" name="message"></textarea></td>
							</tr>
							<tr>
								<td >
															</td>
								<td>
								<input class="noborder button" type="submit" value="Submit Reply" style="width:110px;" />	
								<input type="hidden" name="recordid" value="<?php echo $record->id ?>" />
								</td>
							</tr>
					</table>
					<script type="text/javascript" >
					$(function(){
					var validator = $("#reply_form").validate({ 
						rules: {
							email: {
								required:true,
								email:true
							},
							message: {
								required:true
							}		
						},
						messages: {
							email: {
								required: "Please enter email address",
								email: "Error! "
							},
							message: {
								required: "Please enter your message"						
							}
						},
						errorElement: "em",
						success: function(label) {
							label.hide();
						},
						submitHandler: function() {
							var AJAX_URL = '/wp-handler.php?__ajax_request=1';
							var serial = $('#reply_form').serialize();
							var url = AJAX_URL + '&__class=Site&__proc=__replyForm&' + serial;	
							$.post(url, {
								ajax:  "1"},
									function(data){	
										$('.messagereply').html(data);
										$('.formreply').hide();
										} 																		 
							);
						} 
					});
				});
					</script>
					</form>
				  </div>
            </div>
        </div>

 <script type="text/javascript">
$(function() {
	$('#container-1').tabs();
	$('.reply').click(function(){
		$('a.contactinfo').click();
		});
	$('.thumbimg').fancybox();
});
 </script>	
</div>	
</div>
<?php get_footer(); ?>