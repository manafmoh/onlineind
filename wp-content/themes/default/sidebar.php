<div id="left" class="clearfix">
<div class="searchbox">
<?php 
$uri =  parse_url(str_replace('/search', '/all', $_SERVER['REQUEST_URI']),PHP_URL_PATH);
$uri_segments = split('/', Site::drop_multiple_slashes(ltrim($uri ,'/')));
$catSlug = '';
?>
<h2>Refine your search</h2>
<dl>
	<dt>State</dt>
	<?php $stateId = District::getStateSlugByDistrict($uri_segments[1]);  ?>
	<dd><?php echo FormField::select('state_id', ClassifiedManager::__getStates(),  $stateId, 'onchange="loadDistrict(this.value)"', array(''=>'State')); ?></dd>
	<dt>District</dt>
	<dd id="district"><?php echo FormField::select('district_id', District::getDistrictDropDown($stateId), (isset($uri_segments[1]))? $uri_segments[1]:'', 'onchange="loadPlace(this.value)" <?php  if($stateId){disabled="disabled"} ?>', array('all'=>'District')); ?></dd>
	<dt>Category</dt>
	<dd><?php echo FormField::select('category_id', ClassifiedManager::__getCategorysDropdown(), (isset($uri_segments[2]))? $catSlug = $uri_segments[2]:'', 'onchange="doCategorySearch(this.value)"', array(''=>'Category')); ?></dd>
	<dt>Sub Category</dt>
	<dd id="subcategory"><?php echo FormField::select('subcategory_id', ClassifiedManager::__getSubCategorysDropDown($catSlug),  (isset($uri_segments[3]))? $uri_segments[3]:'', 'onchange="doSubCategorySearch(this.value)"', array(''=>'Sub Category')); ?></dd>
	<dt>Ad Type</dt>
	<dd><?php echo  FormField::select('type',array(''=>'All','offered'=>'Offered','wanted' => 'Wanted'), (isset($_REQUEST['type']))? $_REQUEST['type']:'','onchange="doType(this.value)"','');?></dd>
</dl>
</div>
<script type="text/javascript" >
	<?php if($_REQUEST['type']){ $type ="?type=". $_REQUEST['type']; } else { $type ="";} ?>
	function doCategorySearch(item) {
			var loc = getCookie('__location');	
			window.location = "/classifieds/<?php echo $uri_segments[1] ?>/"+item+"<?php echo $type;  ?>";
		}
	function doSubCategorySearch(item) {
			var loc = getCookie('__location');	
			window.location = "/classifieds/<?php echo $uri_segments[1] ?>/<?php echo $uri_segments[2] ?>/"+item+"<?php echo $type;  ?>";
		}
	function loadPlace(item) {
			window.location = "/classifieds/"+item+"/<?php echo $uri_segments[2] ?>/<?php echo $uri_segments[3] ?><?php echo $type;  ?>";
		}
	function doType(item) {
			window.location = "/classifieds/<?php echo $uri_segments[1] ?>/<?php echo $uri_segments[2] ?>/<?php echo $uri_segments[3] ?>?type="+item;
		}
	function loadDistrict(id) {
				$('#district').html('<p class="indicator"></p>');
				$.get('/wp-handler.php?__class=Site&__proc=__loadDistrictDropDown',{ajax:  "1", 'id': id},
						function(data){ 
							$('#district').html(data);
							}	
					);
			}
</script>

	<br />
	<div style="padding:15px">
	<script type="text/javascript"><!--
	google_ad_client = "ca-pub-5853823947578032";
	/* Wide Skyscraper (160x600) */
	google_ad_slot = "5957159675";
	google_ad_width = 160;
	google_ad_height = 600;
	//-->
	</script>
	<script type="text/javascript"
	src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
	</script>
	</div>
</div>


