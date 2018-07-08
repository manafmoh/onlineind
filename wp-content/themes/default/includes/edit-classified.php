<?php $login =  Customer::current();    ?>
<?php if($id = (int)trim($_REQUEST['id'])) { 
	$record = Site::getClassifiedByIDANDUSERID($id, $login->ID);
	}	
	if(isset($record) && $record) :
?>
<?php if(isset($_POST) && $_POST){ 
	$status = true;
	if(empty($_POST['title'])) {
		Site::showMessage('Please enter the Title of the Classified Ad.');
		$status = false;
	}
	if(empty($_POST['description'])) {
		Site::showMessage('Please enter the Description of the Classified Ad.');
		$status = false;
	}
	if(empty($_POST['category_id'])) {
		Site::showMessage('Please select category.');
		$status = false;
	}
	if(empty($_POST['subcategory_id'])) {
		Site::showMessage('Please select subcategory.');
		$status = false;
	}
	if(empty($_POST['state_id'])) {
		Site::showMessage('Please select state.');
		$status = false;
	}
	if(empty($_POST['district_id'])) {
		Site::showMessage('Please select district.');
		$status = false; }
	if(empty($_POST['type'])) {
		Site::showMessage('Please select type.');
		$status = false;
	}

	if($_POST['id']) {
		if($status ) {
			ClassifiedManager::updateFrondend();	
			$status = true;		
		}
	} else {
		if(Classified::checkUnique($_POST['email'], $_POST['title']) != 0) {
			Site::showMessage('Sorry, you have already posted the same Ad. please post new Ad');
			$status = false;
		}
		if($status)	 {		
			ClassifiedManager::addNew();
			$status = true;
			} 
	}
		
	} ?>
<?php if(!$status): ?>
<div id="login">
	<form name="addform" id="addform" method="post" class="add:the-list: validate" enctype="multipart/form-data" >	

		
			
		<table width="100%" cellspacing="0" cellpadding="5" class="form-table">
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="title">Title</label>  <span class="mand">*</span></th>
				<td><input type="text" name="title"  size="35" value="<?php if(isset($record['title']))echo $record['title']; ?>" /></td>
			</tr>
			
			<tr class="form-field" style="display:none">
				<th scope="row" valign="top"><label for="summary">Summary</label>  <span class="mand">*</span></th>
				<td><textarea name="summary" cols="33"><?php if(isset($record['summary']))echo $record['summary']; ?></textarea></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="description">Description</label>  <span class="mand">*</span></th>
				<td><textarea name="description" class="tinymce" cols="33" style="width:550px; height:200px"><?php if(isset($record['description']))echo $record['description']; ?></textarea></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="category_id">Category</label>  <span class="mand">*</span></th>
				<td><?php echo FormField::select('category_id', ClassifiedManager::__getCategorys(), (isset($record['category_id']))? $record['category_id']:'', 'onchange="loadSubcategory(this.value)"', array(''=>'Category')); ?></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="subcategory_id">Sub Category</label>  <span class="mand">*</span></th>
				<td id="subcategoryblock"><?php echo FormField::select('subcategory_id', ClassifiedManager::__getSubCategorys($record['category_id']),  (isset($record['subcategory_id']))? $record['subcategory_id']:'', '', array(''=>'Sub Category')); ?></td>
			</tr>	
			<tr class="form-field">
				<th scope="row" valign="top"><label for="price">Price (Rs)</label></th>
				<td><input style="80px" type="text" name="price" value="<?php  if(isset($record['price']))echo $record['price'] ?>" size="35" onkeypress="return isNumberKey(event)" /></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="type">Type</label></th>
				<td><?php echo  FormField::radioList('type',array('offered'=>'Offered','wanted' => 'Wanted'), (isset($record['type']))? $record['type']:'','','');?></td>
			</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="name">Upload Pictures<br /><small><i>Maximum filesize: 500KB</i></small></label></th>
			<td>
			<?php
			if(isset($record['image_gallery']) && $record['image_gallery']!=''){
			$imggal=unserialize( $record['image_gallery']); 
			
			$n =  count($imggal);
			for($i=0;$i<$n;$i++){
				if($imggal[$i]){	
				?>
				<a href="<?php echo SITEURL.'/wp-content/media/image/'.$imggal[$i]; ?>" target="_blank"><?php echo $imggal[$i]; ?></a>&nbsp;&nbsp; <input type="checkbox" name="imggalremove[]" value="<?php echo $i ?>" /> Remove<br/>
				<?php
				}
				}
			}
			?>
			<script type="text/javascript">
					function moreGallery() {
						var newItem = document.createElement('div');
						newItem.innerHTML = '<input type="file" name="image_gallery[]" size="35" /> <a href="javascript:;" onclick="this.parentNode.parentNode.removeChild(this.parentNode);">remove</a><br />';
						document.getElementById('more_gallery').appendChild(newItem);
					}
				</script>
				<input type="file" name="image_gallery[]" size="35" /><br />
				<input type="file" name="image_gallery[]" size="35" /><br />
				<input type="file" name="image_gallery[]" size="35" /><br />
				<input type="file" name="image_gallery[]" size="35" /><br />
				<input type="file" name="image_gallery[]" size="35" /><br />
<!--  <a href="javascript:;" onclick="moreGallery();">more</a> -->
				<div id="more_gallery">
				
				</div>		
			</td>
		</tr>
		
		<tr class="form-field">
				<th scope="row" valign="top"><label for="status">Package</label></th>
				<td><?php echo FormField::select('pakage_id', ClassifiedManager::__getPackages(), (isset($record['pakage_id']))? $record['pakage_id']: '8', '', array(''=>'Package')); ?></td>
			</tr>
				
			<!--<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="country_id">Country</label></th>
				<td><?php //echo FormField::select('country_id', array(4=>'India'), (isset($record['country_id']))? $record['country_id']:'', 'Readonly="Readonly"', array('4'=>'India')); ?></td>
			</tr>	-->
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="state_id">State</label>  <span class="mand">*</span></th>
				<td><?php echo FormField::select('state_id', ClassifiedManager::__getStates(),  (isset($record['state_id']))? $record['state_id']:'', 'onchange="loadDistrict(this.value)"', array(''=>'State')); ?></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="district_id">City</label>  <span class="mand">*</span></th>
				<td id="districtblock"><?php echo FormField::select('district_id', ClassifiedManager::__getDistricts(), (isset($record['district_id']))? $record['district_id']:'', 'onchange="loadPlace(this.value)"', array(''=>'District')); ?></td>
			</tr>	<tr class="form-field form-required">
				<!--<th scope="row" valign="top"><label for="district_id">Place</label></th>
				<td id="placeblock"><?php echo FormField::select('place_id', ClassifiedManager::__getPlaces(),  (isset($record['place_id']))? $record['place_id']:'', '', array(''=>'Place')); ?></td>
			</tr>	-->
			<!-- <tr>
				<th scope="row" valign="top"><label for="newsletter">Newsletter</label></th>
				<td>
				<input type="checkbox" name="newsletter" value="<?php echo (isset($record['newsletter']))? $record['newsletter']:'1' ?>"  >
			</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="newsletter">Accept</label></th>
				<td>
				<input type="checkbox" name="accept"  value="1" >
			</td>
			</tr> -->
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="place">Place</label></th>
				<td><input type="text" name="location" value="<?php  if(isset($record['location']))echo $record['location'] ?>" /></td>
			</tr>
		</table>	
		<p class="submit">
		<input type="hidden" name="id" value="<?php  if(isset($record['id']))echo $record['id']; ?>" />
		<input type="hidden" name="user_id" value="<?php  if(isset($record['user_id']))echo $record['user_id']; ?>" />
		<input type="hidden" name="updated_date" value="<?php echo date('Y-m-d'); ?>" />
		
		<input type="submit" class="button" name="add" value="Edit Ad" style="width:85px" />
		</p>
		</form>
	<script type="text/javascript">
		function loadSubcategory(catid) {
	
				$('#subcategoryblock').html('<p class="indicator"></p>');
				$.get('/wp-handler.php?__class=Site&__proc=__loadSubcategory',{ajax:  "1", 'catid': catid},
						function(data){ 
							$('#subcategoryblock').html(data);
							}	
					);
				
			}
		function loadDistrict(id) {
				$('#districtblock').html('<p class="indicator"></p>');
				$.get('/wp-handler.php?__class=Site&__proc=__loadDistrict',{ajax:  "1", 'id': id},
						function(data){ 
							$('#districtblock').html(data);
							}	
					);
			}
		function loadPlace(id) { 
				$('#placeblock').html('<p class="indicator"></p>');
				$.get('/wp-handler.php?__class=Site&__proc=__loadPlace',{ajax:  "1", 'id': id},
						function(data){ 
							$('#placeblock').html(data);
							}	
					);
			}
		$(function(){
			jQuery.validator.addMethod("accept", function(value, element, param) {
			  return value.match(new RegExp("." + param + "$"));
			});
			rules: {
			  fileupload: { accept: "(jpe?g|gif|png)" }
			}
			jQuery.validator.addMethod("phone", function(pnumber) {
				var stripped = pnumber.replace(/[\(\)\.\-\ ]/g, '');
				if (isNaN(parseInt(stripped)) || !(stripped.length >= 10 && stripped.length <= 12)) {
					return false;
				}else{
					return true;
				}
			}, "Please specify a valid mobile number");
			
			jQuery.validator.addMethod("minWords", function(value, element, params) { 
			    return this.optional(element) || value.match(/\b\w+\b/g).length >= params; 
			}, $.format("Please enter at least {0} words.")); 
			jQuery.validator.addMethod("integer", function(value, element) {
				return !jQuery.validator.methods.required(value, element) || /^\d+$/i.test(value);
			}
			, "Numbers only please");
			jQuery.validator.addMethod("usernameCheck", function(value, element){
			$.get('/ajax/usernames.php', {q: value}, function(data){
			if(data == "true"){
			return false;
			}
			return true;
			});
			}, "This username has already been registered");
			var validator = $("#addform").validate({ 
				rules: {
					
					title: {
						required:true,
						minlength:10
						},
					
					description: {
						required:true,
						minWords:8
						}	,
					"category_id": {
						required:true
						},
					subcategory_id: {
						required:true
						},
					pakage_id:{
						required:true
						},
					state_id:{
						required:true
						},
					district_id:{
						required:true
						},
					type:{
						required:true
						}		
				},
				messages: {
				
					title: {
						required: "Please enter the Title of the Classified Ad."	
						},
					description: {
						required: "Please enter the Description of the Classified Ad."						
						},
					"category_id": {
						required: "Please select Category"				
						},
					subcategory_id: {
						required: "Please select Sub Category"					
						},
					pakage_id: {
						required: "Please select Package"		
						},
					state_id:{
						required: "Please select state"	
						},
					district_id:{
						required: "Please select city"	
						},
					type:{
						required:"Please select adtype"
						}
				},
				errorElement: "em",
				success: function(label) {
					label.hide();
				}
			});
		});
		function isNumberKey(evt) {
         var charCode = (evt.which) ? evt.which : event.keyCode
         if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;

         return true;
      }
	</script>
	<script src="<?php echo STYLEURL ?>/js/tiny_mce/jquery.tinymce.js" type="text/javascript"></script>
	 <script type="text/javascript">
		$().ready(function() {
			$('textarea.tinymce').tinymce({
				// Location of TinyMCE script
				script_url : '<?php echo STYLEURL ?>/js/tiny_mce/tiny_mce.js',
				mode : "textareas",
		        theme : "simple",
		        width : "540",
		        height : "180",
				// Example content CSS (should be your site CSS)
				content_css : "<?php echo STYLEURL ?>/js/tiny_mce/themes/simple/skins/default/content.css"
			});
			$('textarea.tinymce').tinymce().show();
		});
	</script>
</div>
<?php endif; ?>
<?php endif; ?>
