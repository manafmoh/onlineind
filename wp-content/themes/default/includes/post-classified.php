<?php if(isset($_POST) && $_POST){ 
	$status = true;
	if(empty($_POST['fullname'])) {
		Site::showMessage('Please enter your name');
		$status = false;
	}
	if(empty($_POST['email'])) {
		Site::showMessage('Please enter email address');
		$status = false;
	}
	if(empty($_POST['mobile'])) {
		Site::showMessage('Please enter the Telephone/Mobile Number');
		$status = false;
	}
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
	if(empty($_POST['accept'])) {
		Site::showMessage('Please accept it.');
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
		
	}
	 ?>
<?php if(!$status): ?>
<div id="login" style="width:750px; float:left">
	
	<form name="addform" id="addform" method="post" class="add:the-list: validate" enctype="multipart/form-data" >	
	<?php if($id = (int)trim($_REQUEST['id'])) { 
		$record = Site::getClassified($id); 
		}	
		?>
		<?php $login =  Customer::current();   ?>
			
		<table width="100%" cellspacing="0" cellpadding="5" class="form-table">
		<?php if(!$login): ?>
			<tr class="form-field form-required">
				<th width="150" scope="row" valign="top"><label for="title">Name</label> <span class="mand">*</span></th>
				<td><input type="text" name="fullname"  size="35" value="<?php if(isset($record['fullname']))echo $record['fullname']; ?>" /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="title">Email</label> <span class="mand">*</span></th>
				<td><input type="text" name="email"  size="35" value="<?php if(isset($record['email']))echo $record['email']; ?>" /></td>
			</tr>
			
		<?php else: ?>
			<input type="hidden" name="email" value="<?php echo $login->user_email ?>" />
			<!--<input type="hidden" name="mobile" value="<?php echo $login->mobile ?>" />-->
			<input type="hidden" name="fullname" value="<?php echo $login->display_name ?>" />
		<?php endif; ?>
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="title">Mobile</label>  <span class="mand">*</span></th>
				<td><input type="text" name="mobile"  size="35" value="<?php if(isset($record['mobile']))echo $record['mobile']; ?>" /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="title">Title</label> <span class="mand">*</span></th>
				<td><input type="text" name="title"  size="35" value="<?php if(isset($record['title']))echo $record['title']; ?>" /></td>
			</tr>
			
			<tr class="form-field" style="display:none">
				<th scope="row" valign="top"><label for="summary">Summary</label></th>
				<td><textarea name="summary" cols="33"><?php if(isset($record['summary']))echo $record['summary']; ?></textarea></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="description">Description</label> <span class="mand">*</span></th>
				<td><textarea name="description" class="bigarea tinymce" cols="33" style="width:550px; height:150px"><?php if(isset($record['description']))echo $record['description']; ?></textarea></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="category_id">Category</label> <span class="mand">*</span></th>
				<td><?php echo FormField::select('category_id', ClassifiedManager::__getCategorys(), (isset($record['category_id']))? $record['category_id']:'', 'onchange="loadSubcategory(this.value)"', array(''=>'Category')); ?></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="subcategory_id">Sub Category</label> <span class="mand">*</span></th>
				<td id="subcategoryblock"><?php echo FormField::select('subcategory_id', array(''=>''),  (isset($record['subcategory_id']))? $record['subcategory_id']:'', '', array(''=>'Sub Category')); ?></td>
			</tr>	
			<tr class="form-field">
				<th scope="row" valign="top"><label for="price">Price (Rs)</label> </th>
				<td><input type="text" style="width:80px" name="price" value="<?php  if(isset($record['price']))echo $record['price'] ?>" size="35" onkeypress="return isNumberKey(event)" /></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="type">Type</label> <span class="mand">*</span></th>
				<td class="type-td"><?php echo  FormField::radioList('type',array('offered'=>'Offered','wanted' => 'Wanted'), (isset($record['type']))? $record['type']:'offered','','');?></td>
			</tr>
			<!-- <tr class="form-field" style="display:none">
				<th scope="row" valign="top"><label for="name">Image</label></th>
				<td><input type="file" name="image"  size="35"/><?php if(isset($record['image']) && !empty($record['image'])){?>  <a href="<?php echo SITEURL.'/wp-content/media/image/'.$record['image'] ?>" target="_blank"> <?php  if(isset($record['image']))echo $record['image'];?></a>&nbsp;&nbsp;<input type="checkbox" name="imgremove" />Remove<?php } ?> </td>
		</tr> -->
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
				<a href="<?php echo SITEURL.'/wp-content/media/image/'.$imggal[$i]; ?>" target="_blank"><?php echo $imggal[$i]; ?></a>&nbsp;&nbsp; <input type="checkbox" name="imggalremove[]" value="<?php echo $imggal[$i];?>" /> Remove<br/>
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
				<input type="file" name="image_gallery[]" size="35" />	<br />			
				<!--  <a href="javascript:;" onclick="moreGallery();">more</a> -->
				<div id="more_gallery">
				
				</div>		
			</td>
		</tr>
		
		<tr class="form-field">
				<th scope="row" valign="top"><label for="status">Package</label></th>
				<td><?php echo FormField::select('pakage_id', ClassifiedManager::__getPackages(), (isset($record['package_id']))? $record['package_id']: '8', '', array(''=>'Package')); ?></td>
			</tr>
				
		<!--<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="country_id">Country</label></th>
				<td><?php //echo FormField::select('country_id', array(4=>'India'), (isset($record['country_id']))? $record['country_id']:'', 'Readonly="Readonly"', array('4'=>'India')); ?></td>
			</tr>	-->
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="state_id">State</label> <span class="mand">*</span></th>
				<td><?php echo FormField::select('state_id', ClassifiedManager::__getStates(),  (isset($record['state_id']))? $record['state_id']:'', 'onchange="loadDistrict(this.value)"', array(''=>'State')); ?></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="district_id">City</label> <span class="mand">*</span></th>
				<td id="districtblock"><?php echo FormField::select('district_id', ClassifiedManager::__getDistricts(), (isset($record['district_id']))? $record['district_id']:'', 'onchange="loadPlace(this.value)"', array(''=>'District')); ?></td>
			</tr>	<!--<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="district_id">Place</label></th>
				<td id="placeblock"><?php echo FormField::select('place_id', ClassifiedManager::__getPlaces(),  (isset($record['place_id']))? $record['place_id']:'', '', array(''=>'Place')); ?></td>
			</tr>	-->
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="place">Place</label></th>
				<td><input type="text" name="location" value="<?php  if(isset($record['location']))echo $record['location'] ?>" /></td>
			</tr>
			<tr>
				<th scope="row" valign="top"></th>
				<td>
				<input type="checkbox" name="newsletter" value="<?php echo (isset($record['newsletter']))? $record['newsletter']:'1' ?>"  />
				<label for="newsletter">I would like to receive the newsletter.</label>
			</td>
			</tr>
			<tr>
				<th scope="row" valign="top"></th>
				<td>
				<input type="checkbox" name="accept"  value="1" /><label for="newsletter">I agree to the <a href="/terms-of-use">terms of use</a> by making a post  <span class="mand">*</span></label>
			</td>
			</tr>
		</table>	
		<p class="submit">
		<input type="hidden" name="country_id" value="4" />
		<input type="hidden" name="place_id" value="0" />
		<input type="hidden" name="id" value="<?php  if(isset($record['id']))echo $record['id']; ?>" />
		<?php if( isset($record['id'])): ?>
		<input type="hidden" name="updated_date" value="<?php echo date('Y-m-d'); ?>" />
		<?php else: ?>
		<input type="hidden" name="created_date" value="<?php echo date('Y-m-d'); ?>" />
		<input type="hidden" name="updated_date" value="<?php echo date('Y-m-d'); ?>" />
		<input type="hidden" name="status" value="disabled" />
		<?php endif; ?>
		<input type="submit" class="button " name="add" value="Submit your Ad" style="width:110px" />
		<div class="loading" style="display:none"><img src="<?php echo STYLEURL ?>/images/indicator.gif" alt="" /> Please wait...</div>
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
			}, $.format("Please enter atleast {0} words.")); 
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
					fullname: {
						required:true
					},
					email: {
						required:true,
						email:true
					},
					mobile: {
						required:true,
						phone:true
					},
					title: {
						required:true,
						minlength:10
						},
					
					/*description: {
						required:true,
						minWords:8
						}	, */
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
						},
					accept: {
						required:true
						}		
				},
				messages: {
					fullname: {
						required: "Please enter your name"
					},
					email: {
						required: "Please enter email address",
						email:"Please enter a valid email address"
						
					},
					mobile: {
						required: "Please enter the Telephone/Mobile Number"	
					},
					title: {
						required: "Please enter the Title of the Classified Ad."	
						},
					/*description: {
						required: "Please enter the Description of the Classified Ad."						
						},*/
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
						},
					accept: {
						required:"Please accept"
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
				theme : "advanced",
				theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,bullist,numlist,|,fontselect,fontsizeselect,forecolor",
				//theme_advanced_buttons2 : "forecolor,backcolor,removeformat,sub,sup,|,undo,redo",
				theme_advanced_buttons2 : "",
				theme_advanced_buttons3 : "",
				theme_advanced_buttons4 : "",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "",
				theme_advanced_resizing : false,
		        width : "540",
		        height : "180",
				// Example content CSS (should be your site CSS)
				content_css : "<?php echo STYLEURL ?>/js/tiny_mce/themes/simple/skins/default/content.css"
			});
			$('textarea.tinymce').tinymce().show();
		});
	</script>
</div>
<div style="float:left">

<script type="text/javascript"><!--
google_ad_client = "ca-pub-6093044357039023";
/* Wide Skyscraper */
google_ad_slot = "6051058041";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

</div>
<?php endif; ?>