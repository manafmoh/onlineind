<?php get_header(); ?>
<?php $location = Site::getLocationCookie(); ?>
	<div id="header-home" class="clearfix" style="position:relative;">
			<form action="classifieds/search/" method="get" id="search">
				<input class="txt"  name="keyword" type="text" alt="search"  />
				<input class="btn" type="image" name="search-btn" value="GO" src="<?php echo STYLEURL ?>/images/search-go.jpg"/>
			</form>
<!--<img style="position:absolute; right:-10px; top:20px; display:none" src="<?php echo STYLEURL ?>/images/n_banner.jpg?v=1" alt="banner"/>-->
		<div style="position:absolute;top:24px; right:0;">
		<!--<form action="http://www.google.com/cse" id="cse-search-box" target="_blank">
		  <div>
		    <input type="hidden" name="cx" value="partner-pub-5853823947578032:8ffte998bsv" />
		    <input type="hidden" name="ie" value="UTF-8" />
		    <input style="width:118px" type="text" name="q" size="31" />
		    <input type="submit" name="sa" value="Search" />
		  </div>
		</form>
		<script type="text/javascript" src="http://www.google.com/cse/brand?form=cse-search-box&amp;lang=en"></script>-->
		<!-- Place this tag in your head or just before your close body tag -->
		<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
		
		<!-- Place this tag where you want the +1 button to render -->
		<g:plusone></g:plusone><!-- Place this tag in your head or just before your close body tag -->
		</div>
		
		</div>
		<div id="home" class="clearfix" style="position:relative">
		<h2>Select a classifieds category to find what you are looking for</h2>
		<?php $rootCat = RootCategory::getRootCategories(); ?>
		<?php foreach($rootCat as $cat): ?>
		<div class="catbox">
			
			<h3><a href="/classifieds/<?php echo $location ?>/<?php echo $cat->slug ?>"><?php echo $cat->name ?></a></h3>
			<?php $subCat = SubCategory::getSubCategories($cat->id); ?>
			<ul>
			<?php foreach($subCat as $sub): ?>
				<li><a href="/classifieds/<?php echo $location ?>/<?php echo $cat->slug ?>/<?php echo $sub->slug ?>"><?php echo $sub->name ?></a></li>
			<?php endforeach ?>
			</ul>
		</div>
		<?php endforeach ?>
		<div class="ads" style="position:absolute; bottom:8px">
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
		</div>
		<div class="recently">
		<h2>Recently posted Ads</h2>
		<?php Site::recentPost(); ?>
		</div>
	<?php get_footer(); ?>
