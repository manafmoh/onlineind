<?php
/*
Plugin Name: Classified Manager
Plugin URI: http://www.onlineind.com
Description: Classified Manager
Version: 1.0
Author: OnlineInd
Author URI: http://www.onlineind.com
*/

class ClassifiedManager{
	protected $_url;
	protected $_webrootUrl;
	protected $_caption;
	protected $_pluginUrl;
	protected $_parent;
	public function __construct()	{
		global $wpdb;
		
		$this->_pluginUrl = "classifieds/item.php";
		$this->_url = get_settings("siteurl") . "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_webrootUrl = "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_caption = 'Item';
		$this->_parent = "classifieds/classifiedshome.php";
		
		$wpdb->classified = "{$wpdb->prefix}classified";

		
		add_action('admin_menu', array(&$this,'adminMenu'));
		add_filter( 'capabilities_list', array(&$this,'capabilities'));
	}
		
	public function adminMenu() {
		if(current_user_can($this->_caption)) {
			add_submenu_page($this->_parent, $this->_caption, $this->_caption, 1, $this->_pluginUrl, array(&$this, 'controller'));
		}
	}
	
	public function controller() {
		$action = $_REQUEST['action'];
		switch($action) {
			case 'view':
				$this->_view();
				break;
			case'new':
				$this->_form();
				break;
			case'add':
				$this->_addNew();
				break;	
			case'edit':
				$this->_edit();
				break;	
			case 'update':
				$this->_update();
				break;	
			case 'delete':
				$this->_delete();
				break;		
			default:	
				$this->_view();
			}	
	}
	
	public function capabilities($caps) {
		$caps[] = $this->_caption;
		return($caps);
	}
	
	protected function _showMessage($message) {
		echo "<div id=\"message\" class=\"updated fade\"><p><strong>$message</strong></p></div>";
	}
	
	protected function _prepareUrl($action) {
		return("$this->_url&amp;action=$action");
	}
	
	protected function _getUsers() { 
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->users} ORDER BY display_name ASC;");
		$user = array();
		if($result) {
			foreach($result as $item) {
				$user[$item->ID] = $item->display_name;
			}
		}
		return($user);
	}
	public static function __getUsers() { 
	$obj = new ClassifiedManager();
		return $obj->_getUsers();
	}
	protected function _getCountries() {
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->country} ORDER BY name ASC;");
		$country = array();
		if($result) {
			foreach($result as $item) {
				$country[$item->id] = $item->name;
			}
		}
		return($country);
	}
	public static function __getCountries() { 
		$obj = new ClassifiedManager();
		return $obj->_getCountries();
	}
	protected function _getStates(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM $wpdb->state ORDER BY name");
		$city = array();
		foreach ($result as $row){
			$city[$row->id] = $row->name;
		}
		return $city;
	}
	public static function __getStates() { 
	$obj = new ClassifiedManager();
		return $obj->_getStates();
	}

	protected function _getDistricts() {
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->district} ORDER BY name ASC;");
		$district = array();
		if($result) {
			foreach($result as $item) {
				$district[$item->id] = $item->name;
			}
		}
		return($district);
	}
	public static function __getDistricts() { 
	$obj = new ClassifiedManager();
		return $obj->_getDistricts();
	}
	protected function _getPlaces() {
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->place} ORDER BY name ASC;");
		$place = array();
		if($result) {
			foreach($result as $item) {
				$place[$item->id] = $item->name;
			}
		}
		return($place);
	}
	public static function __getPlaces() { 
	$obj = new ClassifiedManager();
		return $obj->_getPlaces();
	}
	protected function _getPackages() {
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->package} WHERE status='enabled' ORDER BY title ASC;");
		$package = array();
		if($result) {
			foreach($result as $item) {
				$package[$item->id] = $item->title;
			}
		}
		return($package);
	}
	public static function __getPackages() { 
	$obj = new ClassifiedManager();
		return $obj->_getPackages();
	}
	protected function _getCategorys() {
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->category} ORDER BY name ASC;");
		$category = array();
		if($result) {
			foreach($result as $item) {
				$category[$item->id] = $item->name;
			}
		}
		return($category);
	}
	protected function _getCategorysDropdown() {
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->category} ORDER BY name ASC;");
		$category = array();
		if($result) {
			foreach($result as $item) {
				$category[$item->slug] = $item->name;
			}
		}
		return($category);
	}
	public static function __getCategorys() { 
	$obj = new ClassifiedManager();
		return $obj->_getCategorys();
	}
	public static function __getCategorysDropDown() { 
	$obj = new ClassifiedManager();
		return $obj->_getCategorysDropDown();
	}
	protected function _getSubCategorys() {
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->subcategory} ORDER BY name ASC;");
		$category = array();
		if($result) {
			foreach($result as $item) {
				$category[$item->id] = $item->name;
			}
		}
		return($category);
	}
	protected function _getSubCategorysDropDown($catSlug) {
		global $wpdb;
		$category_id = RootCategory::getRootCategoryIDBySlug($catSlug);
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->subcategory} WHERE category_id = $category_id AND status = 'enabled' ORDER BY name ASC;");
		$category = array();
		if($result) {
			foreach($result as $item) {
				$category[$item->slug] = $item->name;
			}
		}
		return($category);
	}
	public static function __getSubCategorys() { 
		$obj = new ClassifiedManager();
		return $obj->_getSubCategorys();
	}
	public static function __getSubCategorysDropDown($catSlug) { 
		$obj = new ClassifiedManager();
		return $obj->_getSubCategorysDropDown($catSlug);
	}
	protected function _edit() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$record = $wpdb->get_row("SELECT * FROM {$wpdb->classified} WHERE id='$id';");
		if($record) {
			$this->_form($id, $record, 'update');
		} else {
			$this->_view();
		}
	}
	
	protected function _update() {
		global $wpdb;
		
		extract($_POST);
		$id = (int)trim($_REQUEST['id']);
		$slug = $slug?sanitize_title($slug):sanitize_title($title);
		$adid = Classified::getPropertyId($id);
		$data = array(
			'[EMAIL]' => $email,
			'[TITLE]' => $title,
			'[ADID]' => $adid ,
			'[SITEURL]' => SITEURL,
			'[STYLEURL]' => STYLEURL,
			'[LINK]'=> SITEURL."/classified/".$slug.".html"
		);
		//if($status != 'enabled')$status = 'disabled';
		if($status == 'enabled') {
			Site::sendMail('ad_active', $data, $email, "Your Ad \"".$title."\" (Ad Id: ".$adid.") has been posted on ".SITEURL); 
		} elseif($status == 'rejected') {
			Site::sendMail('ad_rejected', $data, $email, "Your Ad \"".$title."\" (Ad Id: ".$adid.") has been rejected on ".SITEURL); 
			}
		
		$wpdb->query("UPDATE {$wpdb->classified} SET status='$status' WHERE id='$id';");
		$this->_showMessage('Successfully Updated!');
		$this->_view();
	}
protected function _updateFrondend() {
		global $wpdb;
		extract($_POST);
		$title = Site::cQ($title);
		$summary = Site::cQ($summary);
		$description = Site::cQ($description);
		$price = Site::cQ($price);
		$type = Site::cQ($type);
		$fullname = Site::cQ($fullname);
		//$email = Site::cQ($email);
		$mobile = Site::cQ($mobile);	
		$location = Site::cQ($location);	
		
		$id = (int)trim($_REQUEST['id']);
		$item = Classified:: getClassifiedById($id);
		$image_gallery = "";
		$oldimages = array();
		if($rmChks = $_REQUEST['imggalremove']) {
			$oldimages = unserialize($item->image_gallery);
			foreach($rmChks as $index) {
				unset($oldimages[$index]);
			}
			$oldimages = Site::reIndexArray($oldimages);
			$oldimages = serialize($oldimages);
		} else {
			$oldimages = $item->image_gallery;
			}
		

		if(!empty($_FILES["image_gallery"]["name"][0])){
			//$img=$this->getimgname(basename($_FILES["image"]["name"]));
			//$flg = move_uploaded_file($_FILES["image"]["tmp_name"],$img);
			//$img = basename($img);
			//$image_gallery = serialize($_FILES['image_gallery']);
			$imggal=$this->uploadimgegallery($oldimages);
			$image_gallery = "image_gallery='$imggal',";
		} elseif($oldimages) {
			$image_gallery = "image_gallery='$oldimages',";			
			}
		$keyword = State::getStateById($state_id).",".District::getDistrictById($district_id).','.Place::getPlaceById($place_id).','.RootCategory::getRootCategoryById($category_id).','.SubCategory::getSubCategoryById($subcategory_id).','.$location;
		
		if($status != 'enabled')$status = 'disabled';
		$slug = sanitize_title($title);
		$wpdb->query("UPDATE {$wpdb->classified} SET title='$title',  description='$description', price='$price', type='$type', status='enabled', title='$title', $image_gallery  country_id='$country_id', state_id='$state_id', district_id='$district_id', place_id='$place_id', category_id='$category_id', subcategory_id='$subcategory_id', pakage_id='$pakage_id', newsletter='$newsletter', updated_date='$updated_date',  keyword='$keyword', location='$location' WHERE id='$id';");
		$this->_showMessage('Successfully Updated!');
		
	}	
	
	public static function updateFrondend()	{
			$obj = new ClassifiedManager();
			return $obj->_updateFrondend();
		}
	
	protected function _delete() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$wpdb->query("DELETE FROM {$wpdb->classified} WHERE id='$id';");
		$this->_showMessage('Successfully Deleted!');
		$this->_view();
	}
	
	protected function _addNew()	{
		global $wpdb;
		extract($_POST);
		if(empty($user_id)) {
			$this->_showMessage('User not selected!');
			$this->_view();
			return; 
		}
		
		$imggal=$this->uploadimgegallery();
		if($_FILES["image"]["name"]!=""){
			$img=$this->getimgname(basename($_FILES["image"]["name"]));
			$flg = move_uploaded_file($_FILES["image"]["tmp_name"],$img);
			$img = basename($img);
		}
		$image_gallery = serialize($_FILES['image_gallery']);
		$slug = $_REQUEST['slug'];
		$slug=$slug!=''?sanitize_title($slug):sanitize_title($slug);
		$slug = $this->getSlug($slug);
		$slug =  str_replace('%e2%80%93','-',$slug);
		$query="INSERT INTO  $wpdb->classified  (`title` ,`slug` ,`summary` ,`description` ,`price`,`type` ,`status` ,`image` ,`image_gallery` ,`country_id`,`district_id` ,`state_id` ,`place_id` ,`category_id` ,`subcategory_id` ,`pakage_id` ,`user_id` ,`created_date` ,`updated_date` ,
`newsletter`,`fullname`,`email`,`mobile`) VALUES ('".$title."','".$slug."','".$summary."','".$description."','".$price."','".$type."','".$status."','".$img."','".$imggal."','".$country_id."', '".$district_id."','".$state_id."','".$place_id."','".$category_id."','".$subcategory_id."','".$pakage_id."','".$user_id."','".$created_date."','".$updated_date."','".$newsletter."','".$fullname."','".$email."','".$mobile."');";
		$query = $wpdb->prepare($query);
		$result=$wpdb->query($query);
		$this->_showMessage("Successfully Saved");
		$this->_view();
	}
	protected function _addNewFrondend()	{
		global $wpdb;
		extract($_POST);
		$title = Site::cQ($title);
		$summary = Site::cQ($summary);
		$description = Site::cQ($description);
		$price = Site::cQ($price);
		$type = Site::cQ($type);
		$fullname = Site::cQ($fullname);
		$email = Site::cQ($email);
		$mobile = Site::cQ($mobile);
		$location = Site::cQ($location);
		
		if(empty($user_id)) {
			$user = Customer::getUserByEmail($email);
			if($user->ID) {
				$user_id = $user->ID;
			} else {
				$user_id = 8;
			}
		}
		$img = "";
		$imggal=$this->uploadimgegallery();
		if($_FILES["image_gallery"]["name"]==""){
			//$img=$this->getimgname(basename($_FILES["image"]["name"]));
			//$flg = move_uploaded_file($_FILES["image"]["tmp_name"],$img);
			//$img = basename($img);
			$imggal = "";
		}
		//$image_gallery = serialize($_FILES['image_gallery']);
		$slug=$_REQUEST['slug']!=''?sanitize_title($_REQUEST['slug']):sanitize_title($_REQUEST['title']);
		$slug = $this->getSlug($slug);
		$slug =  str_replace('%e2%80%93','-',$slug);

		$status = "enabled";
		$keyword = Site::cQ(State::getStateById($state_id)).",".Site::cQ(District::getDistrictById($district_id)).','.Site::cQ(Place::getPlaceById($place_id)).','.Site::cQ(RootCategory::getRootCategoryById($category_id)).','.Site::cQ(SubCategory::getSubCategoryById($subcategory_id)).','.$location ;
		$query="INSERT INTO  $wpdb->classified  (`title` ,`slug` ,`summary` ,`description` ,`price`,`type` ,`status` ,`image` ,`image_gallery` ,`country_id`,`district_id` ,`state_id` ,`place_id` ,`category_id` ,`subcategory_id` ,`pakage_id` ,`user_id` ,`created_date` ,`updated_date`,`newsletter`,`fullname`,`email`,`mobile` ,`keyword`, `location`, `remote_addr`) VALUES ('".$title."','".$slug."','".$summary."','".$description."','".$price."','".$type."','".$status."','".$img."','".$imggal."','".$country_id."', '".$district_id."','".$state_id."','".$place_id."','".$category_id."','".$subcategory_id."','".$pakage_id."','".$user_id."','".$created_date."','".$updated_date."','".$newsletter."','".$fullname."','".$email."','".$mobile."','".$keyword."','".$location."','".$_SERVER['REMOTE_ADDR']."');";
		//$query1 = $query;
		//$query = $wpdb->prepare($query);
		$result=$wpdb->query($query);
		
		$adid = Classified::getPropertyId(mysql_insert_id());
		$data = array(
			'[EMAIL]' => $email,
			'[TITLE]' => $title,
			'[ADID]' => $adid ,
			'[SITEURL]' => SITEURL,
			'[STYLEURL]' => STYLEURL,
			'[LINK]'=> SITEURL."/classified/".$slug.".html"
		);
		//if($status != 'enabled')$status = 'disabled';

			Site::sendMail('ad_active', $data, $email, "Your Ad \"".$title."\" (Ad Id: ".$adid.") has been posted on ".SITEURL); 
		
		$this->_showMessage("Thank you for posting your ad on ". get_bloginfo('name').".");
		$this->_showMessage("Your ad would be displayed on the site within the next 24 hrs after undergoing a validation process.");
		$this->_showMessage("You will be notified about the status of your ad through an email from ". get_bloginfo('name').".");
		//mail("manafmoh@gmail.com","New Ad posted on onlineind.com","$title - $query");
	}
	public static function addNew()	{
			$obj = new ClassifiedManager();
			return $obj->_addNewFrondend();
		}
	protected function _form($id='', $record='', $action='add') {
		global $wpdb;
		if(!$record) {
			$record = new stdClass();
		} 
	wp_enqueue_script( 'wp-ajax-response' );
	wp_print_scripts('wp-ajax-response');
?>
		<div class="wrap">
		<h2><?php echo ucfirst($action); ?> <?php echo $this->_caption; ?></h2>
		<form name="addform" id="classified" method="post" class="add:the-list: validate" enctype="multipart/form-data" >	
		<table width="100%" cellspacing="0" cellpadding="5" class="form-table">
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="title">Title</label></th>
				<td><input type="text" name="title"  size="35" value="<?php echo $record->title; ?>" /></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="slug">Slug</label></th>
				<td><input type="text" name="slug" size="35" value="<?php echo $record->slug; ?>"  /></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="summary">Summary</label></th>
				<td><textarea name="summary" cols="33"><?php echo $record->summary; ?></textarea></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="description">Description</label></th>
				<td><textarea name="description" cols="33"><?php echo $record->description; ?></textarea></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="category_id">Category</label></th>
				<td><?php echo FormField::select('category_id', $this->_getCategorys(), $record->category_id, '', array(''=>'Category')); ?></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="subcategory_id">Category</label></th>
				<td><?php echo FormField::select('subcategory_id', $this->_getSubCategorys(), $record->subcategory_id, '', array(''=>'Sub Category')); ?></td>
			</tr>	
			<tr class="form-field">
				<th scope="row" valign="top"><label for="price">Price</label></th>
				<td><input type="text" name="price" value="<?php echo $record->price; ?>" size="35" /></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="type">Type</label></th>
				<td><?php echo  FormField::select('type',array('buy'=>'For Buy','rent' => 'For Rent'),$record->type,'','');?></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="name">Image</label></th>
				<td><input type="file" name="image"  size="35"/><?php if($record->image){?>  <a href="<?php echo '../wp-content/media/image/'.$record->image ?>" target="_blank"> <?php echo $record->image;?></a>&nbsp;&nbsp;<input type="checkbox" name="imgremove" />Remove<?php } ?> </td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="name">Image Gallery</label></th>
			<td>
			<?php
			if($record->image_gallery!=''){
			$imggal=unserialize($record->image_gallery);
			$n =  count($imggal);
			for($i=0;$i<$n;$i++){
				if($imggal[$i]){	
				?>
				<a href="<?php echo '../wp-content/media/image/'.$imggal[$i]; ?>" target="_blank"><?php echo $imggal[$i]; ?></a>&nbsp;&nbsp; <input type="checkbox" name="imggalremove[]" value="<?php echo $imggal[$i];?>" /> Remove<br/>
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
				<input type="file" name="image_gallery[]" size="35" /> <a href="javascript:;" onclick="moreGallery();">more</a>(You can upload image files and zip files)
				<div id="more_gallery">
				
				</div>		
			</td>
		</tr>
		
		<tr class="form-field">
				<th scope="row" valign="top"><label for="status">Package</label></th>
				<td><?php echo FormField::select('pakage_id', $this->_getPackages(),  $record->pakage_id, '', array(''=>'Package')); ?></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="user_id">User</label></th>
				<td><?php echo FormField::select('user_id', $this->_getUsers(), $record->user_id, '', array(''=>'User')); ?></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="country_id">Country</label></th>
				<td><?php echo FormField::select('country_id', $this->_getCountries(), $record->country_id, '', array(''=>'Country')); ?></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="state_id">State</label></th>
				<td><?php echo FormField::select('state_id', $this->_getStates(), $record->state_id, '', array(''=>'State')); ?></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="district_id">District</label></th>
				<td><?php echo FormField::select('district_id', $this->_getDistricts(), $record->district_id, '', array(''=>'District')); ?></td>
			</tr>	<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="district_id">Place</label></th>
				<td><?php echo FormField::select('place_id', $this->_getPlaces(), $record->place_id, '', array(''=>'Place')); ?></td>
			</tr>	
			<tr>
				<th scope="row" valign="top"><label for="newsletter">Newsletter</label></th>
				<td><?php echo  FormField::select('newsletter',array('1'=>'Yes','0' => 'No'),$record->newsletter,'','');?></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="email">Email</label></th>
				<td><?php echo  $record->email ?><input type="hidden" name="email" value="<?php echo  $record->email ?>" /></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="mobile">Mobile</label></th>
				<td><?php echo  $record->mobile ?></td>
			</tr>
			<tr>
			<th scope="row" valign="top"><label for="status">Status</label></th>
			<td><?php echo FormField::select('status',array('enabled'=>'Enabled','disabled'=>'Disabled','rejected'=>'Rejected'),$record->status,'','') ?></td>
		</tr>
		</table>	
		<p class="submit">
		<input type="hidden" name="id" value="<?php echo $record->id; ?>" />
		<?php if( isset($record->id)): ?>
		<input type="hidden" name="updated_date" value="<?php echo date('Y-m-d'); ?>" >
		<?php else: ?>
		<input type="hidden" name="created_date" value="<?php echo date('Y-m-d'); ?>" >
		<input type="hidden" name="updated_date" value="<?php echo date('Y-m-d'); ?>" >
		<?php endif; ?>
		<input type="hidden" name="action" value="<?php echo $action; ?>" >
		<input type="submit" class="button" name="add" value="<?php echo ucfirst($action); ?> <?php echo $this->_caption; ?>" />
		</p>
		</form>
		</div>	
	<?php }
	
	function _view() { 
		global $wpdb;?>
		<div class="wrap">
			<form id="posts-filter" action="" method="get">
			<h2>Manage <?php echo $this->_caption; ?> (<a href='<?php echo $this->_prepareUrl('new'); ?>'  class="current">add new</a>)</h2>
			<div class="tablenav">
				<div class="alignleft">
					<form action="" method="get">
						<input type="hidden" name="page" value="<?php echo $this->_pluginUrl; ?>" />
						<input type="text" class="text" name="s" value="<?php if (isset($_REQUEST['s'])) { echo htmlspecialchars($_GET['s']); } ?>" />
						<?php echo FormField::select('user_id', $this->_getUsers(), $_REQUEST['user_id'],'', array(''=>'User')); ?>
							<?php echo FormField::select('status', array('disabled'=>Disabled, 'enabled'=>'Enabled','rejected'=>'Rejected'), $_REQUEST['status'],'', array(''=>'Status (All)')); ?>
						<input type="submit" value="Filter" class="button-secondary" />
					</form>
					<br class="clear" />
				</div>
			</div>
			<br class="clear" />
			<?php
			$search = " 1=1 ";
			
				if($_REQUEST['s']) {
					$search .= " AND (ci.title LIKE '%".$_REQUEST['s']."%') ";
				}
				/*if($_REQUEST['user_id']) {
					$search .= " AND (ci.user_id='".$_REQUEST['user_id']."') ";
				}
				if($_REQUEST['status']) {
					$search .= " AND (ci.status='".$_REQUEST['status']."') ";
				}*/
			
			$list = new DataGrid($wpdb->classified, "id"); 
			$recordCount = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->classified} WHERE $search");
			$list->setTotalRecords($recordCount);
			$list->addColumn('title', 'Title', '50%', true);
			$list->addColumn('slug', 'Slug', '25%', true);
			$list->addColumn('users', 'User', '20%', true);
			$list->addColumn('status', 'Status', '15%', true);
			$list->addAction('Edit', $list->getUrl(array('action'=>'edit', 'id'=>'[id]')), '5%');
			$list->addAction('Delete', $list->getUrl(array('action'=>'delete', 'id'=>'[id]')), '5%', null, 'onclick="return confirm(\'Are you sure?\')"');
			$sql = "SELECT ci.*, co.display_name AS users FROM {$wpdb->classified} AS ci INNER JOIN {$wpdb->users} AS co ON ci.user_id=co.id WHERE $search ".$list->getSortOrderSql().' '.$list->getPagingSql().';';
			$list->setMasterDataSource($wpdb->get_results($sql));
			$list->render();?>
			</form>
		</div>

	<?php }
	protected function getSlug($title,$id= ''){
		global $wpdb;
		$sql = "SELECT slug FROM $wpdb->classified";
		if($id) {
			$sql .= " where  id <> '{$id}'";
		}
		$slug = $wpdb->get_col($sql);
		$i = 1;
		while (in_array($title, $slug)){
			$title = $title.$i;
			$i++;
		}
		return $title;
	}
	protected function uploadimgegallery($oldimg = ""){
		$maxSize = 500 * 1024;
		$arimg=$_FILES['image_gallery']['name'];
		$n=count($arimg);
		$imgset = array();
		$list = array();
		$tmplist = array();
		for($i=0;$i<$n;$i++)
		{
			$filetype = pathinfo($_FILES['image_gallery']['name'][$i], PATHINFO_EXTENSION);
			$filetype = strtolower($filetype);
			if($filetype == "zip"){
				if(count($list) > 0){
					$tmplist =	$this->uploadzip($i);
					$list = array_merge($list, $tmplist);
				}else{
					$list = $this->uploadzip($i);
				}
			}
			else{ 
				if($_FILES['image_gallery']['name'][$i]){
					if($_FILES["image_gallery"]["size"][$i] <= $maxSize && $_FILES["image_gallery"]["size"][$i]!=0) {
					$imagename = $this->getimgname(basename($_FILES['image_gallery']['name'][$i]));
					$imgset[$i] = basename($imagename);
					$flg = move_uploaded_file($_FILES['image_gallery']['tmp_name'][$i],$imagename);
					}
				}
			}
		}		
		$imgset = array_merge($imgset, $list);
		if(!empty($oldimg)) {
			$oldimg = unserialize($oldimg);
			$imgset = array_merge($imgset,$oldimg);
		}
		$imgstring = serialize($imgset);
		return $imgstring;
	}

	protected function getimgname($imgname){
		if(!$imgname){
			return ;
		}
		$img = ABSPATH.'wp-content/media/image/'.$imgname;
		$i = 1;
		$tmimgname = "";
		$path = ABSPATH.'wp-content/media/image/';
		while(file_exists($img)){
			$tmp = explode('.',$imgname);
			$n = count($tmp);
			$tmimgname = $tmp[0].$i.".".$tmp[$n-1];
			$img = "";
			$img = $path.$tmimgname;
			$i++;
		}	
		return $img;
	}
	protected function uploadzip($index){
		require_once  (ABSPATH . WPINC . '/pclzip.lib.php');
		$folder=ABSPATH.'wp-content/media/zip/';
		$path=ABSPATH.'wp-content/media/image/';
		$flg = move_uploaded_file($_FILES['image_gallery']['tmp_name'][$index],"{$folder}temp.zip");
		$achives = new PclZip("{$folder}temp.zip");
 		$list = $achives->extract(PCLZIP_OPT_PATH,$folder."extract/", PCLZIP_OPT_REMOVE_ALL_PATH);
 		$imglist = array();
 		$i = 0;
 		foreach($list as $data) {
 			if(!$data["folder"]){
 				$file_names = explode("/", $data["filename"]);
 				$n = count($file_names);
 				$img = $this->getimgname($file_names[$n-1]);
 				$flg = copy($data["filename"],$img);
 				$imglist[$i] = basename($img);
 				if(file_exists($data["filename"])) {
 					unlink($data["filename"]);
 				}
 				$i++;
 			}
 		}
 		return $imglist;
	}
}

new ClassifiedManager();

class Classified {
	const PAGE_SIZE = 5;
	public static function getPermalink($slug) {
		return(get_option('siteurl')."/classified/$slug.html");
	}
	protected static function _getOrderBySql($sort = 'id', $order = 'desc') {
		global $wp_query;
		if($_REQUEST['sort']) {
			$sort = $_REQUEST['sort'];
		}
		if(!$sort ){$sort = 'id';}
		if($_REQUEST['order']) {
			$order = $_REQUEST['order'];
		}
		$sql = " ORDER BY c.$sort $order ";
		return($sql);
	}
		
	protected static function _getPagingSql($page = 1, $pageSize = self::PAGE_SIZE) {
		if($_REQUEST['page']) {
			$page = $_REQUEST['page'];
		}
		
		$start = (((int)$page-1) * $pageSize);
		$sql = " LIMIT $start, $pageSize ";
		return($sql);
	}
	public static function getClassified(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM $wpdb->classified ORDER BY name");
		$classified = array();
		foreach ($result as $row){
			$classified[$row->id] = $row->name;
		}
		return $classified;
	}
	public static function getPropertyId($id, $prefix=100){
		return $prefix."$id";
	}
	public static function getClassifiedById($id){
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM $wpdb->classified WHERE id='$id' ");
		return $result;
	}
	public static function checkUnique($email, $title) {
		global $wpdb;
		$num = $wpdb->get_var("SELECT count(*) FROM $wpdb->classified WHERE email='$email' AND title = '$title'"  );
		return $num; 
		}
	public static function getClassifiedSubCategory(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM $wpdb->subcategory");
		return $result;
		
	}
	
	public static function saveClassified($user_id, $classified_id) {
		global $wpdb;
		$result = $wpdb->query("INSERT INTO $wpdb->saved_classified (classified_id ,user_id)VALUES ('{$classified_id}', '{$user_id}');");
		return $result;
	}
	
	public static function sendClassified($user_id, $classified_id){
		global $wpdb;
		$result = $wpdb->query( "INSERT INTO $wpdb->email_alert_classified (classified_id ,user_id)VALUES ('{$classified_id}', '{$user_id}');");
		return $result;
	}
	
	public static function getSavedClassified($user_id,$page =1) {
		global $wpdb;
		$result = $wpdb->get_results("SELECT p.*,a.ID as agent_id,  a.display_name  AS agent_name ,a.user_nicename AS agent_slug, t.name as classifiedtype, t.slug as classified_slug FROM $wpdb->classified as p 
		INNER JOIN $wpdb->saved_classified  as sp on p.id =  sp.classified_id 
		INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
		INNER JOIN {$wpdb->subcategory} AS t ON p.subcategory_id=t.id
		  WHERE sp.user_id ='{$user_id}'".self::_getOrderBySql('rating').self::_getPagingSql($page, 5)) ;
		return $result;
	}
	
	public static function getSavedClassifiedCount($user_id) {
		global $wpdb;
		$num = $wpdb->get_var("SELECT count(*) FROM $wpdb->classified as p 
		INNER JOIN $wpdb->saved_classified  as sp on p.id =  sp.classified_id 
		INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
		INNER JOIN {$wpdb->subcategory} AS t ON p.subcategory_id=t.id
		  WHERE sp.user_id ='{$user_id}'".self::_getOrderBySql('rating')) ;
		return $num;
		
	}
	
	
	public static function removeClassified($user_id, $classified_id) {
		global $wpdb;
		$result = $wpdb->query("DELETE FROM $wpdb->saved_classified WHERE classified_id='{$classified_id}' and user_id = '{$user_id}' ");
		return $result;
	}
	
	public static function getEmailAlertClassified($user_id, $page =1) {
		global $wpdb;
		$result = $wpdb->get_results("SELECT p.*, a.display_name  AS agent_name ,a.user_nicename AS agent_slug, t.name as classifiedtype, t.slug as classified_slug FROM $wpdb->classified as p 
		INNER JOIN $wpdb->email_alert_classified  as ep on p.id =  ep.classified_id 
		INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
		INNER JOIN {$wpdb->subcategory} AS t ON p.subcategory_id=t.id
		  WHERE ep.user_id ='{$user_id}'".self::_getOrderBySql('rating').self::_getPagingSql($page, 5)) ;
		return $result;
	}
	
	public static function getEmailAlertClassifiedCount($user_id) {
		global $wpdb;
		$num = $wpdb->get_var("SELECT count(*) FROM $wpdb->classified as p 
		INNER JOIN $wpdb->email_alert_classified  as ep on p.id =  ep.classified_id 
		INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
		INNER JOIN {$wpdb->subcategory} AS t ON p.subcategory_id=t.id
		  WHERE ep.user_id ='{$user_id}'".self::_getOrderBySql('rating')) ;
		return $num;
	}
	
	public static function removeEmailAlert($user_id,$classified_id) {
		global $wpdb;
		$result = $wpdb->query("DELETE FROM $wpdb->email_alert_classified WHERE classified_id ='{$classified_id}' and user_id ='{$user_id}' ");
		return $result;
	}
	
	public static function addViewCount($id) {
		global $wpdb;
		$count = $wpdb->get_var("SELECT viewcount FROM $wpdb->classified WHERE id={$id}");
		$count++;
		$result = $wpdb->query("UPDATE $wpdb->classified SET viewcount = {$count} WHERE id = $id ");
	}
	
	
	public static function getPrice($id) {
		global $wpdb;
		return ($wpdb->get_var("SELECT price FROM $wpdb->classified WHERE id = {$id}"));		
		
	}
	
	public static function saveFeedback($name, $email, $phone, $type, $message){ global $wpdb;
		$sql ="	INSERT INTO $wpdb->feedback (name ,email_id ,phone ,comment_type ,message)VALUES ('{$name}', '{$email}', '{$phone}', '{$type}', '{$message}');";
		$result = $wpdb->query($sql);
	}
	protected static function _getBaseSql($state='', $district='', $place='', $category='', $subcategory='', $type='',$keyword='') {
		global $wpdb;
		$sql = "SELECT c.* FROM {$wpdb->classified} AS c ";
				
				if($category) {
				$sql .=" INNER JOIN {$wpdb->category} AS cat ON c.category_id=cat.id ";
				}
				if($subcategory) {
				$sql .=" INNER JOIN {$wpdb->subcategory} AS subcat ON c.subcategory_id=subcat.id ";
				}
				if($state) {
				$sql .=" INNER JOIN {$wpdb->state} AS state ON c.state_id=state.id ";
				}
				if($district) {
				$sql .=" INNER JOIN {$wpdb->district} AS district ON c.district_id=district.id";
				}
				if($place) {
				$sql .=" INNER JOIN {$wpdb->place} AS place ON c.place_id=place.id";
				}
				
				$sql .=" WHERE c.status='enabled'";
		return($sql);	
	}
	protected static function _getBaseSqlCount($state='', $district='', $place='', $category='', $subcategory='', $type='',$keyword='') {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$wpdb->classified} AS c ";
				
				if($category) {
				$sql .=" INNER JOIN {$wpdb->category} AS cat ON c.category_id=cat.id ";
				}
				if($subcategory) {
				$sql .=" INNER JOIN {$wpdb->subcategory} AS subcat ON c.subcategory_id=subcat.id ";
				}
				//if($state) {
				//$sql .=" INNER JOIN {$wpdb->country} AS co ON c.country_id=co.id"
				//}
				if($state) {
				$sql .=" INNER JOIN {$wpdb->state} AS state ON c.state_id=state.id ";
				}
				if($district) {
				$sql .=" INNER JOIN {$wpdb->district} AS district ON c.district_id=district.id";
				}
				if($place) {
				$sql .=" INNER JOIN {$wpdb->place} AS place ON c.place_id=place.id";
				}
				
				$sql .=" WHERE c.status='enabled'";
		return($sql);	
	}
	public static function getKeywordSearchResult($keyword,$page) {
		global $wpdb;
		$keyword = Site::cQ($keyword);
		$sql = "SELECT c.* FROM {$wpdb->classified} AS c 
		WHERE c.title LIKE '%{$keyword}%' 
		OR c.description LIKE '%{$keyword}%' 
		OR c.keyword LIKE '%{$keyword}%'	";	
		$sortorder = "desc";
		$sql .= self::_getOrderBySql($sortby, $sortorder).self::_getPagingSql($page, 5);
		$result = $wpdb->get_results($sql);
		return $result;
	}
	public static function getKeywordSearchResultCount($keyword) {
		global $wpdb;
		$keyword = Site::cQ($keyword);
		$sql = "SELECT COUNT(*) FROM {$wpdb->classified} AS c 
		WHERE c.title LIKE '%{$keyword}%' 
		OR c.description LIKE '%{$keyword}%' 
		OR c.keyword LIKE '%{$keyword}%'	";	
		$num = $wpdb->get_var($sql);
		return $num;
	}
	public static function getSearchResult($state, $district, $place, $category, $subcategory, $type,$keyword,$page=1   ) 	{
		global $wpdb;
		$state = Site::cQ($state);
		$district = Site::cQ($district);
		$place = Site::cQ($place);
		$category = Site::cQ($category);
		$subcategory = Site::cQ($subcategory);
		$type = Site::cQ($type);
		$sql = Classified::_getBaseSql($state, $district, $place, $category, $subcategory, $type,$keyword);
		if($category) {
			$sql .= " and cat.slug ='{$category}'  ";
		}
		if($subcategory) {
			$sql .= " and subcat.slug='{$subcategory}' ";
		}
		if($type){
			$sql .= " and c.type='{$type}' ";
		}
		if($state) {
			$sql .= " and state.slug='{$state}' ";
		}
		if($district && $district!='all') {
			$sql .= " and district.slug='{$district}' ";
		}
		if($place) {
			$sql .= " and place.slug='{$place}' ";
		}
		if($price) {
			$sql .= " and price >= {$price} ";
			$sql .= " and price <= {$price} ";
			}
	
		$sortorder = "desc";
		$sql .= self::_getOrderBySql($sortby, $sortorder).self::_getPagingSql($page, 30);
		$result = $wpdb->get_results($sql);
		return $result;
	}
	public static function getRecentPosts() 	{
		global $wpdb;
		$sql = Classified::_getBaseSql();
		$sortorder = "desc";
		$sql .= self::_getOrderBySql($sortby, $sortorder)." LIMIT 0, 30";
		$result = $wpdb->get_results($sql);
		return $result;
	}
	public static function getMostViewedResult( $district,  $category, $subcategory   ) 	{
		global $wpdb;
		$district = Site::cQ($district);
		$category = Site::cQ($category);
		$subcategory = Site::cQ($subcategory);
		$sql = Classified::_getBaseSql('', $district, '', $category, $subcategory);
		if($category) {
			$sql .= " and cat.slug ='{$category}'  ";
		}
		if($subcategory) {
			$sql .= " and subcat.slug='{$subcategory}' ";
		}
		
		if($district && $district!='all') {
			$sql .= " and district.slug='{$district}' ";
		}
		
	
		$sortorder = "desc";
		$sortby = "viewcount";
		//echo $sql;
		$sql .= self::_getOrderBySql($sortby, $sortorder).self::_getPagingSql(1, 15);
		$result = $wpdb->get_results($sql);
		return $result;
	}
	public static function getSearchResultCount($state, $district,$place, $category, $subcategory, $type,$keyword,$page=1 ) {
			global $wpdb;
			$state = Site::cQ($state);
			$district = Site::cQ($district);
			$place = Site::cQ($place);
			$category = Site::cQ($category);
			$subcategory = Site::cQ($subcategory);
			$type = Site::cQ($type);
		$sql = Classified::_getBaseSqlCount($state, $district, $place, $category, $subcategory, $type,$keyword);
		if($category) {
			$sql .= " and cat.slug ='{$category}'  ";
		}
		if($subcategory) {
			$sql .= " and subcat.slug='{$subcategory}' ";
		}
		if($type){
			$sql .= " and c.type='{$type}' ";
		}
		if($state) {
			$sql .= " and state.slug='{$state}' ";
		}
		if($district && $district!='all') {
			$sql .= " and district.slug='{$district}' ";
		}
		if($place) {
			$sql .= " and place.slug='{$place}' ";
		}
		if($price) {
			$sql .= " and price >= {$price} ";
			$sql .= " and price <= {$price} ";
			}
		$num = $wpdb->get_var($sql);
		return $num;
	}
	public static function load($slug) {
		global $wpdb;
		$sql = self::_getBaseSql()." AND c.slug='$slug';";
		$result = $wpdb->get_row($sql);
		return($result);
	}
	public static function getClassifiedByUserID($userid){
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM $wpdb->classified WHERE user_id=$userid AND status='enabled' ORDER BY id DESC");
		return $result;
	}
	public static function getClassifiedDescriptionBySlug($slug){
		global $wpdb;
		$result = $wpdb->get_var("SELECT description FROM $wpdb->classified WHERE slug='$slug'");
		return substr(strip_tags($result),0,500);
	}
	public static function updateClassifiedUserIdByEmail($user) {
		global $wpdb;	
		$wpdb->query("UPDATE {$wpdb->classified} SET user_id='{$user->ID}' WHERE email='{$user->user_email}';");
	}
}