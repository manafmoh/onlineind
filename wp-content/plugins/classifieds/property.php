<?php
/*
Plugin Name: Property Manager
Plugin URI: http://www.pikaspot.com
Description: Property Manager
Version: 1.0
Author: PikaSpot
Author URI: http://www.pikaspot.com
*/

class PropertyManager {
	protected $_url;
	protected $_webrootUrl;
	protected $_caption;
	protected $_pluginUrl;
	protected $_parent;
	
	public function __construct()	
	{
		global $wpdb;
		
		$this->_pluginUrl = "property/property.php";
		$this->_url = get_settings("siteurl") . "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_webrootUrl = "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_caption = 'Property';
		$this->_parent = "property/agent.php";
		
		$wpdb->property = "{$wpdb->prefix}property";
		$wpdb->comments = "{$wpdb->prefix}comments";
		$wpdb->feedback = "{$wpdb->prefix}feedback";
		$wpdb->users = "{$wpdb->prefix}users";
		$wpdb->saved_property = "{$wpdb->prefix}saved_property";
		$wpdb->email_alert_property = "{$wpdb->prefix}email_alert_property";
		add_action('admin_menu', array(&$this,'adminMenu'));
		//add_action('sidemenu', array(&$this,'setSubmenuOrder'));
		add_filter( 'capabilities_list', array(&$this,'capabilities'));	
		add_action('admin_notices', array(&$this, 'subscriptionNotice'));
		add_action('new_rating', array(&$this, 'setPropertyRating'));
	}
	
	public function setSubmenuOrder() {
		global $submenu, $parent_file;
		if(in_array('agent', array_keys(wp_get_current_user()->data->wp_capabilities))) {
			$menuOrder = array('Property', 'Callback Request','Enquiry', 'Statistics', 'Import Property');
			$subMenu = $submenu["$parent_file"];
			$menu = array();
			foreach($menuOrder as $item) {
				foreach($subMenu as $subMenuItem) {
					if($item == $subMenuItem[0]) {
						$menu[] = $subMenuItem;
					}
				}
			}
			$submenu["$parent_file"] = $menu;
		}
	}
		
	public function adminMenu() {
		if(in_array('agent', array_keys(wp_get_current_user()->data->wp_capabilities))) {
			add_menu_page("My Properties", "My Properties", 1, $this->_pluginUrl, array(&$this, 'controller'));
			$this->_parent = "property/property.php";
		}
		if(current_user_can($this->_caption)) {
			add_submenu_page($this->_parent, "My Properties", "My Listings", 1, $this->_pluginUrl, array(&$this, 'controller'));
		}
	}
	
	public function controller() {
		$action = $_REQUEST['action'];
		switch($action) {
			case 'view':
				$this->_view();
				break;
			case'new':
				if(!$this->_isSubscriptionActive()) {
					break;
				}
				$this->_form();
				break;
			case'add':
				if(!$this->_isSubscriptionActive()) {
					break;
				}
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
			case 'status':
				$this->_status();
				break;
			default:	
				$this->_view();
			}	
	}
	
	public function setPropertyRating($data) {
		global $wpdb, $gRating;
		if($data['module'] == 'property') {
			$propertyId = $data['parent_id'];
			$rating=$gRating->getRating('property', $propertyId);
			$rate=$rating['full'];
			
			$wpdb->query("UPDATE {$wpdb->property} SET rating='$rate' WHERE id='$propertyId';");
		}
	}
	
	public function subscriptionNotice() {
		global $user_ID;
		if(!in_array('agent', array_keys(wp_get_current_user()->data->wp_capabilities))) {
			return;
		}
		$user = $this->_getUserData($user_ID);
		$datediff = $this->_mysqlDateToTimestamp($user->subscription_end) - time();
		$datediffInDays = ceil($datediff/60/60/24);
		$message = '';
		if($datediffInDays<=0) {
			$message = "<li>Your subscription has expired. Please contact <a href='mailto:".get_option("admin_email")."'> administrator </a> to renew your subscription.</li>";
		} elseif($datediffInDays>0 && $datediffInDays<=5){
			switch($datediffInDays) {
				case 1:
					$daysMsg = 'today';
					break;
				case 2:
					$daysMsg = 'tommorow';
					break;
				default:
					$daysMsg = "in $datediffInDays days time";
					break;
			}
			$message = "<li>Your subscription is about to expire $daysMsg. Please contact <a href='mailto:".get_option("admin_email")."'> administrator </a> to renew your subscription.</li>";
		}
		if($user->properties<=0) {
			$message .= "<li>You cannot add any more properties. Please contact <a href='mailto:".get_option("admin_email")."'> administrator </a> to buy more packages.</li>";	
		} elseif($user->properties==1) {
			$message .= "<li>You can add only {$user->properties} more property. Please contact <a href='mailto:".get_option("admin_email")."'> administrator </a> to buy more packages.</li>";
		} elseif($user->properties>0 && $user->properties<=5) {
			$message .= "<li>You can add only {$user->properties} more properties. Please contact <a href='mailto:".get_option("admin_email")."'> administrator </a> to buy more packages.</li>";
		}
		;
		if(!empty($message)) {
			$this->_showMessage("<ul>$message</ul>");
		}
	}
	
	protected function _status() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$option = trim($_REQUEST['option']);
		
		switch($option) {
			case 'enabled':
				$wpdb->query("UPDATE {$wpdb->property} SET status='enabled' WHERE id='$id';");
				break;
			case 'disabled':
				$wpdb->query("UPDATE {$wpdb->property} SET status='disabled' WHERE id='$id';");
				break;
		}
		$this->_showMessage('Successfully '.ucfirst($option).'!');
		$this->_view();
		
	}
	
	public function capabilities($caps) {
		$caps[] = $this->_caption;
		return($caps);
	}
		
	protected function _showMessage($message)
	{
		echo "<div id=\"message\" class=\"updated fade\"><p><strong>$message</strong></p></div>";
	}
	
	protected function _prepareUrl($action)
	{
		return("$this->_url&amp;action=$action");
	}
	
	protected function getdevlopers()
	{
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM wp_development");
		$developer = array();
		if($result)
		{
			foreach ($result as $row)
			{
				$developer[$row->id] = $row->name;
			}
		}
		return $developer;
	}
	
	protected function getarea()
	{
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM wp_area");
		$area = array();
		if($result)
		{
			foreach ($result as $row)
			{
				$area[$row->id] = $row->name;
			}
		}
		return $area;
	}
	
	protected  function gettype()
	{
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM wp_type");
		$type = array();
		if($result)
		{
			foreach ($result as $row)
			{
				$type[$row->id] = $row->name;
			}
		}
		return $type;
	}
	
	protected function _isSubscriptionActive() {
		global $user_ID;
		$user = $this->_getUserData($user_ID);
		$datediff = $this->_mysqlDateToTimestamp($user->subscription_end) - time();
		$datediffInDays = ceil($datediff/60/60/24);
		if($datediffInDays>0 && $user->properties>0) {
			$status = true;
		} else {
			$status = false;
		}
		return($status);
	}
	
	protected function _getUserData($userId) {
		global $wpdb;
		$user = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID='$userId';");
		if($user) {
			$userMeta = $wpdb->get_results("SELECT * FROM {$wpdb->usermeta} WHERE user_id='{$userId}';");
			if($userMeta) {
				foreach($userMeta as $item) {
					$user->{$item->meta_key} = $item->meta_value;
				}
			}
		}
		return($user);
	}
	
	protected function _mysqlDateToTimestamp($date) {
		$parts = explode(' ', $date);
		$dateParts = explode('-', $parts[0]);
		$timeParts = explode(':', $parts[1]);
		return(mktime($timeParts[0], $timeParts[1], $timeParts[2], $dateParts[1], $dateParts[2], $dateParts[0]));
	}
	
	public function showRating($name, $record) {
		global $gRating;
		$rating=$gRating->getRating('property', $record->id);
		$rate=$rating['full'];
		$star = '<img class="rating-star" src="'.STYLEURL.'/images/rank'.$rate.'.gif" border="0" alt="rating" width="69" height="13" />';
		return($star);		
	}
	
	protected function _form($record='')
	{
		global $wpdb;
		$heading="Edit";
		$action="update";
		if(!$record)
		{
			$record = new stdClass();
			$heading = "Add new Property";
			$action ="add";
		}
		
	wp_enqueue_script( 'wp-ajax-response' );
	wp_print_scripts('wp-ajax-response');
?>

		<div class="wrap">
		<h2><?php echo $heading ?></h2>
		<form name="addform" id="Property" method="post" class="add:the-list: validate" enctype="multipart/form-data" >
		<table width="100%" cellspacing="0" cellpadding="5" class="form-table"> 
		<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="name">Development </label></th>
				<td><?php echo  FormField::select('development_id',$this->getdevlopers(),$record->development_id,'',array(''=>'Development')); ?></td>
		</tr>	
		<tr class="form-field ">
				<th scope="row" valign="top"><label for="name">Area</label></th>
				<td><?php echo FormField::select('area_id',$this->getarea(),$record->area_id,'',array(''=>'Area')); ?></td>
		</tr>
		<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="name">Type</label></th>
				<td><?php echo FormField::select('type_id',$this->gettype(),$record->type_id,'',array(''=>'Type')) ?></td>
		</tr>
		<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="name">Title</label></th>
				<td><input type="text" name="title"  size="35" value="<?php echo $record->title; ?>" /></td>
		</tr>
		<tr class="form-field">
				<th scope="row" valign="top"><label for="name">Slug</label></th>
				<td><input type="text" name="slug" size="35" value="<?php echo $record->slug; ?>"  /></td>
		</tr>
		<tr class="form-field">
				<th scope="row" valign="top"><label for="name">Over view</label></th>
				<td><textarea name="overview" cols="33"><?php echo $record->overview; ?></textarea></td>
		</tr>
		<tr class="form-field">
				<th scope="row" valign="top"><label for="ref_no">Referance Number</label></th>
				<td><input type="text" name="ref_no" value="<?php echo $record->ref_no; ?>" size="35" /></td>
		</tr>
		<tr class="form-field">
				<th scope="row" valign="top"><label for="name">Price</label></th>
				<td><input type="text" name="price" value="<?php echo $record->price; ?>" size="35" /></td>
		</tr>
		<tr class="form-field">
				<th scope="row" valign="top"><label for="name">Number of bedrooms</label></th>
				<td><input type="text" name="no_bedrooms" value="<?php echo $record->no_bedrooms; ?>" size="35" /></td>
		</tr>
		<tr class="for-field">
		<th scope="row" valign="top"><label for="area">Area(sq.ft)</label></th>
		<td><input type="text" name="area" value="<?php echo $record->area;?>" size="35" /></td>
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
				<th scope="row" valign="top"><label for="google_map_latitude">Google map latitude</label></th>
				<td><input type="text" name="google_map_latitude" size="35" value="<?php echo $record->google_map_latitude; ?>" /></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="google_map_longitude">Google map longitude</label></th>
			<td><input type="text" name="google_map_longitude" size="35" value="<?php echo $record->google_map_longitude; ?>" /></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="type">Type</label></th>
			<td><?php echo  FormField::select('type',array('buy'=>'For Buy','rent' => 'For Rent'),$record->type,'','');?></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="status">Status</label></th>
			<td><?php echo FormField::select('status',array('enabled'=>'Enabled','disabled'=>'Disabled'),$record->status,'','') ?></td>
		</tr>
		</table>
		<p class="submit">
		<input type="hidden" name="id" value="<?php echo $record->id; ?>" />
		<input type="hidden" name="action" value="<?php echo $action; ?>" >
		<input type="submit" class="button" name="add" value="<?php echo ucfirst($action); ?> <?php echo $this->_caption; ?>" />
		</p>
		</form>	
		</div>
		<?php
	}
	
	public function showStatusAction($name, $record, $command) {
		if($record->status == "enabled") {
			$command = 'Enabled <a class="edit" href="'.DataGrid::getUrl(array('action'=>'status', 'option'=>'disabled', 'id'=>$record->id)).'">(Disable)</a>';
		} else {
			$command = 'Disabled <a class="edit" href="'.DataGrid::getUrl(array('action'=>'status', 'option'=>'enabled', 'id'=>$record->id)).'">(Enable)</a>';
		}
		return($command);
	}
	
 	protected  function _view() { 
		global $wpdb, $user_ID;?>
		<div class="wrap">
			<form id="posts-filter" action="" method="get">
			<h2>Manage <?php echo $this->_caption; ?><?php if($this->_isSubscriptionActive()) { ?> (<a href='<?php echo $this->_prepareUrl('new'); ?>'  class="current">add new</a>)<?php } ?></h2>
			<div class="tablenav">
				<div class="alignleft">
					<form action="" method="get">
						<input type="hidden" name="page" value="<?php echo $this->_pluginUrl; ?>" />
						<input type="text" class="text" name="s" value="<?php if (isset($_REQUEST['s'])) { echo htmlspecialchars($_GET['s']); } ?>" />
						<?php echo  FormField::select('development',$this->getdevlopers(),$_REQUEST['development'],'',array(''=>'Developement')); 
						 echo FormField::select('areasqft',$this->getarea(),$_REQUEST['areasqft'],'',array(''=>'Area')); 
						 echo FormField::select('types',$this->gettype(),$_REQUEST['types'],'',array(''=>'Type'));
						 echo FormField::select('sstatus',array('enabled'=>'Enabled','disabled'=>'Disabled'),$_REQUEST['sstatus'],'',array(''=>'Status'));
						  ?>
						<input type="submit" value="Filter" class="button-secondary" />
					</form>
					<br class="clear" />
				</div>
			</div>
			<br class="clear" />
			<?php
			$search = " agent_id='$user_ID'";
			if($_REQUEST['s']) {
				$search .= " AND (title LIKE '%".$_REQUEST['s']."%') ";
			}
			if($_REQUEST['development']){
				$search .= " AND d.id=".$_REQUEST['development'] ;
			}
			if($_REQUEST['areasqft']){
				$search .= " AND a.id=".$_REQUEST['areasqft'];
			}
			if($_REQUEST['types']){
				$search .=" AND t.id=".$_REQUEST['types'];
			}
			if($_REQUEST['sstatus']){
				$search .= " AND p.status='{$_REQUEST['sstatus']}'";
			}
			$list = new DataGrid($wpdb->property, "id");
			$recordCount = $wpdb->get_var("SELECT count(*) FROM $wpdb->property AS p INNER JOIN wp_development AS d ON p.development_id = d.id INNER JOIN wp_type as t ON t.id = p.type_id LEFT OUTER JOIN wp_area AS a ON a.id = p.area_id WHERE $search");
			$list->setTotalRecords($recordCount);
			$list->addColumn('title', 'Title', '15%', true);
			$list->addColumn('devlpname', 'Developement', '15%', true);
			$list->addColumn('areaname', 'Area', '15%', true);
			$list->addColumn('typename', 'Type', '15%', true);
			$list->addColumn('no_bedrooms', 'Bedrooms', '5%', true);
			$list->addColumn('price', 'Price', '5%', true);
			$list->addColumn('rating', 'Rating', '10%', true, array(&$this, 'showRating'));
			$list->addAction('Status', $list->getUrl(array('action'=>'status', 'id'=>'[id]')), '5%', array(&$this, 'showStatusAction'));
			$list->addAction('Edit',$list->getUrl(array('action'=>'edit', 'id'=>'[id]')), '5%');
			$list->addAction('Delete',$list->getUrl(array('action'=>'delete', 'id'=>'[id]')), '5%', null, 'onclick="return confirm(\'Are you sure?\')"');
			$sql ="SELECT p. * , d.name AS devlpname, a.name AS areaname,t.name AS typename FROM $wpdb->property AS p INNER JOIN wp_development AS d ON p.development_id = d.id INNER JOIN wp_type as t ON t.id = p.type_id LEFT OUTER JOIN wp_area AS a ON a.id = p.area_id WHERE $search ".$list->getSortOrderSql().' '.$list->getPagingSql().';';
			$list->setMasterDataSource($wpdb->get_results($sql));
			$list->render();?>
			</form>
		</div>

	<?php }
	
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
	
	protected function uploadimgegallery(){
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
					$imagename = $this->getimgname(basename($_FILES['image_gallery']['name'][$i]));
					$imgset[$i] = basename($imagename);
					$flg = move_uploaded_file($_FILES['image_gallery']['tmp_name'][$i],$imagename);
				}
			}
		}		
		$imgset = array_merge($imgset, $list);
		$imgstring = serialize($imgset);
		return $imgstring;
	}
	
	protected function getSlug($title,$id= ''){
		global $wpdb;
		$sql = "SELECT slug FROM $wpdb->property";
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
	
	protected function _addNew(){
		global $wpdb;
		$imggal=$this->uploadimgegallery();
		if($_FILES["image"]["name"]!=""){
			$img=$this->getimgname(basename($_FILES["image"]["name"]));
			$flg = move_uploaded_file($_FILES["image"]["tmp_name"],$img);
			$img = basename($img);
		}
		
		//$imggal = serialize($_FILES['image_gallery']);
		$slug=$_REQUEST['slug']!=''?sanitize_title($_REQUEST['slug']):sanitize_title($_REQUEST['title']);
		$slug = $this->getSlug($slug);
		$query="INSERT INTO  $wpdb->property (development_id,agent_id,area_id,title,slug,overview,price,type_id,no_bedrooms,area,ref_no,image,image_gallery,google_map_latitude,google_map_longitude,type,status)VALUES ('".$_REQUEST['development_id']."', '".Agent::current()->ID."', '".$_REQUEST['area_id']."', '".$wpdb->escape($_REQUEST['title'])."', '".$slug."', '".$wpdb->escape($_REQUEST['overview'])."', '".$_REQUEST['price']."', '".$_REQUEST['type_id']."', '".$_REQUEST['no_bedrooms']."','".$_REQUEST['area']."','".$_REQUEST['ref_no']."', '".$img."', '".$imggal."', '".$_REQUEST['google_map_latitude']."', '".$_REQUEST['google_map_longitude']."', '".$_REQUEST['type']."','".$_REQUEST['status']."');";
		$query = $wpdb->prepare($query);
		$result=$wpdb->query($query);
		$this->_decrementPropertyCount();
		$this->_showMessage("Successfully Saved");
		$this->_view();
	}
	
	protected function _decrementPropertyCount() {
		global $user_ID;
		$properties = intval(get_usermeta($user_ID, 'properties'));
		$properties -= 1;
		if(empty($properties)) {
			$properties = '00';
		}
		update_usermeta($user_ID, 'properties', $properties);
		wp_cache_delete($user_ID, 'users');
	}
	
	protected function _incrementPropertyCount() {
		global $user_ID;
		$properties = intval(get_usermeta($user_ID, 'properties'));
		$properties += 1;
		if(empty($properties)) {
			$properties = '00';
		}
		update_usermeta($user_ID, 'properties', $properties);
		wp_cache_delete($user_ID, 'users');
	}
	
	protected  function _delete()
	{
		global $wpdb;
		$id = (int)trim($_REQUEST['id']);
		$query="DELETE FROM $wpdb->property WHERE id ='".$id."'";
		$result = $wpdb->query($query);
		
		$this->_incrementPropertyCount();
		
		if($result>0) {
			$this->_showMessage("Successfully Deleted");
		}
		$this->_view();
	}
	
	protected  function _edit()
	{
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$record = $wpdb->get_row("SELECT * FROM {$wpdb->property} WHERE id='$id';");
		if($record) {
			$this->_form($record);
		} else {
			$this->_view();
		}
	}
	
	protected function _update()
	{
		global $wpdb;
		$forbuy = $_REQUEST['for_buy']=="on"?1:0;
		$forrent = $_REQUEST['for_rent']=="on"?1:0;
		$sts = $_REQUEST['status']=="on"?'Enabled':'Disabled';
		$path = ABSPATH.'wp-content/media/image/';
		if($_REQUEST['imgremove']=="on"){
			$imgname = $wpdb->get_var("SELECT image FROM $wpdb->property WHERE id='".$_REQUEST['id']."'");
			if(file_exists($path.$imgname)){
				unlink($path.$imgname);
				
			}
			$updateimg="UPDATE $wpdb->property SET image = '' WHERE id ='".$_REQUEST['id']."'";
			$wpdb->query($updateimg);
		}
		if($_FILES["image"]["name"]!=""){
			
			$img = $this->getimgname(basename($_FILES["image"]["name"]));
			$flg = move_uploaded_file($_FILES["image"]["tmp_name"],$img);
			$img=basename($img);
			$updateimg="UPDATE $wpdb->property SET image = '".$img."' WHERE id ='".$_REQUEST['id']."'";
			$wpdb->query($updateimg);
		}
		$sql="SELECT image_gallery FROM $wpdb->property WHERE id='".$_REQUEST['id']."'";
		$imgchlist =  $wpdb->get_var($sql);	
		$n = 0;
		if($imgchlist){
			$imggallist = unserialize($imgchlist);
			$n = count($imggallist);
			$imgrlist = $_REQUEST['imggalremove'];
			$m = count($imgrlist);
			for($i=0;$i<$n;$i++){
				for($j=0;$j<$m;$j++){
					if($imggallist[$i] == $imgrlist[$j]){
						if(file_exists($path.$imggallist[$i])){
							unlink($path.$imggallist[$i]);
						}
						//unset($imggallist[$i]);
						//$imggallist=$this->removeElement($i,$);
						for($k=$i;$k<($n-1);$k++){
							$imggallist[$k]=$imggallist[$k+1];
						}
						unset($imggallist[$n-1]);
					}
				}
			}
		}
		
		$tmplist = array();
		$tmplist = $imggallist;
		
		$imggal = $this->uploadimgegallery();
		if($imggal){
			$img_list = unserialize($imggal);
			$n = count($tmplist);
			$m = count($img_list);
			for($i=0;$i<$m;$i++,$n++){
				$tmplist[$n]=$img_list[$i];
			}
		}
		for($i=0;$i<$n;$i++)
		{
			if($tmplist[$i]==''){
				unset($tmplist[$i]);
			}
			
		}
		$imglist=serialize($tmplist);
		$slug=$_REQUEST['slug']!=''?sanitize_title($_REQUEST['slug']):sanitize_title($_REQUEST['title']);
		$slug = $this->getSlug($slug, $_REQUEST['id']);
		$query="UPDATE wp_property SET development_id = '".$_REQUEST['development_id']."',area_id = '".$_REQUEST['area_id']."',title = '".$wpdb->escape($_REQUEST['title'])."',slug = '".$slug."',overview = '".$wpdb->escape($_REQUEST['overview'])."',price = '".$_REQUEST['price']."',type_id = '".$_REQUEST['type_id']."',no_bedrooms = '".$_REQUEST['no_bedrooms']."',ref_no = '".$_REQUEST['ref_no']."',google_map_latitude = '".$_REQUEST['google_map_latitude']."',google_map_longitude = '".$_REQUEST['google_map_longitude']."',type = '".$_REQUEST['type']."',status = '".$_REQUEST['status']."', image_gallery ='".$imglist."', area = '".$_REQUEST['area']."' WHERE id ='".$_REQUEST['id']."'";
		$query = $wpdb->prepare($query);
		$result=$wpdb->query($query);
		$this->_showMessage("Successfully Updated");
		$this->_view();
		
	}
	
    function removeElement($Position, $Array){
			for($Index = $Position; $Index < count($Array) - 1; $Index++){
			$Array[$Index] = $Array[$Index + 1];
			}
			array_pop($Array);
			return $Array;
	} 


}

new PropertyManager();

class Property {
	
	const PAGE_SIZE = 5;
	
	public static function getPermalink($slug) {
		return(get_option('siteurl')."/property/$slug.html");
	}
	
	protected static function _getBaseSql() {
		global $wpdb;
		$sql = "SELECT p.*, a.display_name AS agent_name, a.user_nicename AS agent_slug, a.ID as agent_id , ar.name AS area_name, ar.slug as area_slug,
				ty.name AS type_name, ty.slug AS type_slug, de.name AS development_name, de.slug AS development_slug, ci.name AS city_name,
				ci.slug AS city_slug, co.name AS country_name, co.code AS country_slug FROM {$wpdb->property} AS p INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS ty ON p.type_id=ty.id LEFT OUTER JOIN {$wpdb->area} AS ar ON p.area_id=ar.id
				INNER JOIN {$wpdb->development} AS de ON p.development_id=de.id INNER JOIN {$wpdb->city} AS ci ON de.city_id=ci.id
				INNER JOIN {$wpdb->country} AS co ON ci.country_id=co.id WHERE p.status='enabled'";
		return($sql);	
	}
	
	
	protected static function _getOrderBySql($sort = 'id', $order = 'desc') {
		global $wp_query;
		if($_REQUEST['sort']) {
			$sort = $_REQUEST['sort'];
		}
		if($_REQUEST['order']) {
			$order = $_REQUEST['order'];
		}
		$sql = " ORDER BY p.$sort $order ";
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

	public static function getProeprtyRecord($id){
		global $wpdb;
		$sql = self::_getBaseSql();
		$sql .= "where p.id = {$id}";
		$record = $wpdb->get_row($sql);
		return $record;
	}
	
	public static function loadProperties($slug) {
		global $wpdb;
		$sql = self::_getBaseSql()."";
		$result = $wpdb->get_row($sql);
		return($result);
	}
	
	public static function load($slug) {
		global $wpdb;
		$sql = self::_getBaseSql()." AND p.slug='$slug';";
		$result = $wpdb->get_row($sql);
		return($result);
	}
	
	public static function loadByAgent($slug) {
		global $wpdb;
		$sql = self::_getBaseSql()." AND a.user_nicename='$slug'".self::_getOrderBySql().self::_getPagingSql();
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function loadByCountry($slug ) {
		global $wpdb;
		$sql = self::_getBaseSql()." AND co.slug='$slug'".self::_getOrderBySql().self::_getPagingSql();
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function loadByCity($slug) {
		global $wpdb;
		$sql = self::_getBaseSql()." AND ci.slug='$slug'".self::_getOrderBySql().self::_getPagingSql();
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getCountbyCity($slug) {
		global $wpdb;
		$sql = "SELECT count(*) FROM {$wpdb->property} AS p INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS ty ON p.type_id=ty.id LEFT OUTER JOIN {$wpdb->area} AS ar ON p.area_id=ar.id
				INNER JOIN {$wpdb->development} AS de ON p.development_id=de.id INNER JOIN {$wpdb->city} AS ci ON de.city_id=ci.id
				INNER JOIN {$wpdb->country} AS co ON ci.country_id=co.id WHERE p.status='enabled' AND ci.slug ='{$slug}'";
		return ($wpdb->get_var($sql));
	}
	
	public static function getCountType($slug) {
		global $wpdb;
		$sql = "SELECT count(*) FROM {$wpdb->property} AS p INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS ty ON p.type_id=ty.id LEFT OUTER JOIN {$wpdb->area} AS ar ON p.area_id=ar.id
				INNER JOIN {$wpdb->development} AS de ON p.development_id=de.id INNER JOIN {$wpdb->city} AS ci ON de.city_id=ci.id
				INNER JOIN {$wpdb->country} AS co ON ci.country_id=co.id WHERE p.status='enabled' AND ty.slug ='{$slug}'";
		return ($wpdb->get_var($sql));
	}
	
	public static function loadByDevelopment($slug) {
		global $wpdb;
		$sql = self::_getBaseSql()." AND de.slug='$slug'".self::_getOrderBySql().self::_getPagingSql();
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getCountbyDevelopment($slug) {
		global $wpdb;
		$sql = "SELECT count(*) FROM {$wpdb->property} AS p INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS ty ON p.type_id=ty.id LEFT OUTER JOIN {$wpdb->area} AS ar ON p.area_id=ar.id
				INNER JOIN {$wpdb->development} AS de ON p.development_id=de.id INNER JOIN {$wpdb->city} AS ci ON de.city_id=ci.id
				INNER JOIN {$wpdb->country} AS co ON ci.country_id=co.id WHERE p.status='enabled' AND de.slug ='{$slug}'";
		return ($wpdb->get_var($sql));
		
	}
	
	public static function loadByArea($slug) {
		global $wpdb;
		$sql = self::_getBaseSql()." AND ar.slug='$slug'".self::_getOrderBySql().self::_getPagingSql();
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getCountbyArea($slug) {
		global $wpdb;
		$sql = "SELECT count(*) FROM {$wpdb->property} AS p INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS ty ON p.type_id=ty.id LEFT OUTER JOIN {$wpdb->area} AS ar ON p.area_id=ar.id
				INNER JOIN {$wpdb->development} AS de ON p.development_id=de.id INNER JOIN {$wpdb->city} AS ci ON de.city_id=ci.id
				INNER JOIN {$wpdb->country} AS co ON ci.country_id=co.id WHERE p.status='enabled' AND ar.slug ='{$slug}'";
		return ($wpdb->get_var($sql));
	}
	
	public static function loadByType($slug) {
		global $wpdb;
		$sql = self::_getBaseSql()." AND ty.slug='$slug'".self::_getOrderBySql().self::_getPagingSql();
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getFeaturedCount() {
		global $wpdb;
			$sql = "SELECT count(*) FROM {$wpdb->featured_property} AS fp
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled'";
		$count = $wpdb->get_var($sql);
		return($count);
	}
	
	public static function getFeatured($page = 1) {
		global $wpdb;
		$page = $_REQUEST['page']?$_REQUEST['page']:1;
		$sql = "SELECT p.*, a.display_name AS agent_name, a.user_nicename AS agent_slug, a.ID as agent_id ,t.name as propertytype, t.slug as propertytypeslug FROM {$wpdb->featured_property} AS fp
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ORDER BY fp.sortorder ASC ";
		if($page >0) {
			$sql .= self::_getPagingSql($page, 4);
		}
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getFeaturedwithoutpaging() {
		global $wpdb;
		$page = $_REQUEST['page']?$_REQUEST['page']:1;
		$sql = "SELECT p.*, a.display_name AS agent_name, a.user_nicename AS agent_slug, a.ID as agent_id , t.name as propertytype, t.slug as propertytypeslug FROM {$wpdb->featured_property} AS fp
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ORDER BY fp.sortorder ASC ";
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getSimilarProperty($id,$price,$nobeadroom,$area,$type) {
		global $wpdb;
		$page = $_REQUEST['page']?$_REQUEST['page']:1;
		$budgetrange = Property::getBudgetRangeValue($price);
		$sql = self::_getBaseSql()." and p.id != '{$id}' and p.no_bedrooms='{$nobeadroom}' and p.area='{$area}' and p.type = '{$type}'  ";
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getMostPopularCount() {
		global $wpdb;
		$sql = "SELECT count(*) FROM {$wpdb->featured_property} AS fp			
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ORDER BY p.view_count DESC ";
		
		$count = $wpdb->get_var($sql);
		return($count);
	}
	
	public static function getMostPopular($page =1) {
		global $wpdb;
		$page = $_REQUEST['page']?$_REQUEST['page']:1;
		$sql = "SELECT p.*,a.display_name AS agent_name, a.user_nicename AS agent_slug, a.ID as agent_id, t.name as propertytype, t.slug as propertytypeslug FROM {$wpdb->featured_property} AS fp			
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ORDER BY p.view_count DESC ".self::_getPagingSql($page, 4);
			$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getMostPopularwithOutPaging() {
		global $wpdb;
		$page = $_REQUEST['page']?$_REQUEST['page']:1;
		$sql = "SELECT p.*,a.display_name AS agent_name, a.user_nicename AS agent_slug, a.ID as agent_id, t.name as propertytype, t.slug as propertytypeslug FROM {$wpdb->featured_property} AS fp			
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ORDER BY p.view_count DESC ";
			$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getMostDiscussedCount(){	global $wpdb;
		$sql = "SELECT count(*) FROM {$wpdb->featured_property} AS fp
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ";
		$count = $wpdb->get_var($sql);
		return($count);
	}
	
	public static function getMostDiscussed($page =1 ) {
		global $wpdb;
		$page = $_REQUEST['page']?$_REQUEST['page']:1;
		$sql = "SELECT p.*, a.display_name AS agent_name, a.user_nicename AS agent_slug, a.ID as agent_id, t.name as propertytype, t.slug as propertytypeslug FROM {$wpdb->featured_property} AS fp
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ORDER BY p.comment_count DESC ".self::_getPagingSql($page, 4);
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getMostDiscussedwithOutPaging() {
		global $wpdb;
		$page = $_REQUEST['page']?$_REQUEST['page']:1;
		$sql = "SELECT p.*, a.display_name AS agent_name, a.user_nicename AS agent_slug, a.ID as agent_id, t.name as propertytype FROM {$wpdb->featured_property} AS fp
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ORDER BY p.comment_count DESC ";
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getMostExpensiveCount() {
		global $wpdb;
		$sql = "SELECT count(*) FROM {$wpdb->featured_property} AS fp
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ";
		$count = $wpdb->get_var($sql);
		return($count);
	}
	
	public static function getMostExpensive($page = 1) {
		global $wpdb;
		$page = $_REQUEST['page']?$_REQUEST['page']:1;
		$sql = "SELECT p.*, a.display_name AS agent_name, a.user_nicename AS agent_slug, a.ID as agent_id , t.name as propertytype, t.slug as propertytypeslug FROM {$wpdb->featured_property} AS fp
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ORDER BY p.price DESC ".self::_getPagingSql($page, 4);
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getMostExpensivewithoutPaging () {
		global $wpdb;
		$page = $_REQUEST['page']?$_REQUEST['page']:1;
		$sql = "SELECT p.*, a.display_name AS agent_name, a.user_nicename AS agent_slug, a.ID as agent_id , t.name as propertytype FROM {$wpdb->featured_property} AS fp
				INNER JOIN {$wpdb->property} AS p ON fp.property_id=p.id
				INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
				WHERE p.status='enabled' ORDER BY p.price DESC ";
		$result = $wpdb->get_results($sql);
		return($result);
	}
	
	public static function getBedRooms() {
		global $wpdb;
		$sql = "SELECT distinct(no_bedrooms) FROM $wpdb->property where no_bedrooms >0  ORDER BY no_bedrooms";
		$result = $wpdb->get_results($sql);
		$bedroom = array();
		foreach ($result as $row) {
			$bedroom[$row->no_bedrooms] = $row->no_bedrooms;
		}
		return $bedroom;
	}
	
	public static function getBudgetRange($currency_code = 'AED'){
			$budrange = array();
			$budrange_start = array(
			0 => 0, 1 => 800000 , 2 => 1000000 , 3 => 1300000 , 4 => 1500000 , 5 => 1700000 , 6 => 2000000 , 7 => 3000000, 8 => 5000000 );
			$budrange_end = array(
			 0 => 800000 , 1 => 1000000 , 2 => 1300000 , 3 => 1500000 , 4 => 1700000 , 5 => 2000000 , 6 => 3000000, 7 => 5000000 );
			for($i=0;$i<9;$i++) {
				$sval = Currency::convert($budrange_start[$i], $currency_code);
				if($budrange_end[$i]) {
					$eval = Currency::convert($budrange_end[$i], $currency_code);
				}		
				if($sval == 0 ){
					$budrange[$budrange_start[$i]."-".$budrange_end[$i]] = " Below ".number_format($eval);
				}
				elseif (!$budrange_end[$i]) {
					$budrange[$budrange_start[$i]."-".$budrange_end[$i]] = " Above ".number_format($eval);
				}
				else {
					$budrange[$budrange_start[$i]."-".$budrange_end[$i]] = number_format($sval)." to ".number_format($eval);
				}
			}
			return $budrange;
	}
	
	public static function getBudgetRangeValue($price){
		$range = array();
		if($price < 800000){
			$range[0] = 0;
			$range[1] = 800000;
		}
		elseif(($price > 800000 )&&($price < 1000000 )) {
			$range[0] = 800000;
			$range[1] = 1000000;
		}
		elseif(($price > 1000000 )&&($price < 1300000 )) {
			$range[0] = 1000000;
			$range[1] = 1300000;
		}
		elseif(($price > 1300000 )&&($price < 1500000 )) {
			$range[0] = 1300000;
			$range[1] = 1500000;
		}
		elseif(($price > 1500000 )&&($price < 1700000 )) {
			$range[0] = 1500000;
			$range[1] = 1700000;
		}
		elseif(($price > 1700000 )&&($price < 2000000 )) {
			$range[0] = 1700000;
			$range[1] = 2000000;
		}
		elseif(($price > 2000000 )&&($price < 3000000 )) {
			$range[0] = 2000000;
			$range[1] = 3000000;
		}
		elseif(($price > 3000000 )&&($price < 5000000 )) {
			$range[0] = 3000000;
			$range[1] = 5000000;
		}
		else {
			$range[0] = 5000000;
		}
		return $range;
	}
	
	public static function getOverView($id) {
		global $wpdb;
		$sql = "SELECT * FROM $wpdb->property WHERE id = '{$id}'";
		$record = $wpdb->get_row($sql);
		return $record;
		
	}
	
	public static function getComparison($id){
		global $wpdb;
		$record = $wpdb->get_row("SELECT price,no_bedrooms,area,type FROM $wpdb->property WHERE id = '{$id}'");
		$sql = "SELECT avg(price) FROM $wpdb->property WHERE id !='{$id}' and no_bedrooms='{$record->no_bedrooms}' and area = '{$record->area}' and type ='{$record->type}'";
		$avg = $wpdb->get_var($sql);
		$avg = round($avg,2);
		$pa = array(0 => $record->price, 1 => $avg );
		return $pa;
	}
	
	public static function getCommants($id) {
		global $wpdb;
		$sql = "SELECT c.* FROM $wpdb->comments as c INNER JOIN {$wpdb->users} AS a ON c.user_id=a.ID   WHERE comment_post_ID='{$id}' and comment_approved ='1' and type ='property'";
		$result = $wpdb->get_results($sql);
		return $result;
	}
	
	public static function insertCommants($id,$commant,$user_id) {
		global $wpdb;
		$record = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID='{$user_id}'");
		$sql = "INSERT INTO $wpdb->comments (comment_post_ID ,comment_author ,comment_author_email ,comment_author_url ,comment_author_IP ,comment_date ,comment_date_gmt ,comment_content ,comment_karma ,comment_approved ,comment_agent ,comment_type ,comment_parent ,user_id ,type )VALUES ('{$id}', '{$record->user_nicename}', '{$record->user_email}', '{$record->user_url}', '1', NOW(), NOW(), '{$commant}', '1', '0', '1', '1', '0', '{$user_id}', 'property');";
		$result = $wpdb->query($sql);
		return $result;
	}
	
	public static function getSearchResult($develpment_id,$city_id,$type_id,$area_id,$bedroom,$budget,$type,$page=1,$sortby,$proptypes = '', $bedrooms ='', $sliderfirst = '', $slidersecond = '' ) 	{
		global $wpdb;
		$sql = Property::_getBaseSql();
		if($develpment_id) {
			$sql .= " and p.development_id ='{$develpment_id}'  ";
		}
		if($city_id) {
			$sql .= " and ci.id='{$city_id}' ";
		}
		if($type_id > 0){
			$sql .= " and p.type_id='{$type_id}' ";
		}
		if($area_id) {
			$sql .= " and p.area_id='{$area_id}' ";
		}
		if($bedroom) {
			$sql .= " and p.no_bedrooms = '{$bedroom}'";
		}
		if($budget) {
			$bud  = explode('-', $budget);
			$sql .= " and price >= {$bud[0]} ";
			if($bud[1] > 0){
				$sql .= " and price <= {$bud[1]} ";
			}
		}
		if($type){
			$sql .= " and p.type ='{$type}' ";
		}
		if(!$sortby){
			$sortby="rating";
		}
		$sortorder = 'asc';
		if($sortby == "rating"){
			$sortorder = "desc";
		}
		if($proptypes) {
			$properties = explode('|', $proptypes);
			$n = count($properties);
			$n--;
			for($i=0;$i<$n;$i++) {
				if($i == 0) { $sql .= " and (";} else { $sql .= " or "; }
				$sql .= "  p.type_id ='{$properties[$i]}'";
			}
			$sql .= ")";
		}
		if($bedrooms) {
			$beds = explode("|", $bedrooms);
			$n = count($beds);
			$n--;
			for($i=0;$i<$n;$i++) {
				if($i == 0) { $sql .= " and (";} else { $sql .= " or "; }
				$sql .= "  p.no_bedrooms = '{$beds[$i]}'";
			}
			$sql .= ")";
		}
		if($slidersecond > 0) {
			$sql .= " and p.price between {$sliderfirst} and {$slidersecond} ";
		}
		$sql .= self::_getOrderBySql($sortby, $sortorder).self::_getPagingSql($page, 5);
		$result = $wpdb->get_results($sql);
		return $result;
	}
	
	public static function getMaxPrice($develpment_id, $city_id, $type_id, $area_id, $bedroom, $budget, $type ) {
	 	global $wpdb;
	 	$sql = "SELECT max(p.price) FROM {$wpdb->property} AS p INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS ty ON p.type_id=ty.id LEFT OUTER JOIN {$wpdb->area} AS ar ON p.area_id=ar.id
				INNER JOIN {$wpdb->development} AS de ON p.development_id=de.id INNER JOIN {$wpdb->city} AS ci ON de.city_id=ci.id
				INNER JOIN {$wpdb->country} AS co ON ci.country_id=co.id WHERE p.status='enabled'";
		if($develpment_id) {
			$sql .= " and p.development_id ='{$develpment_id}'  ";
		}
		if($city_id) {
			$sql .= " and ci.id='{$city_id}' ";
		}
		if($type_id > 0){
			$sql .= " and p.type_id='{$type_id}' ";
		}
		if($area_id) {
			$sql .= " and p.area_id='{$area_id}' ";
		}
		if($bedroom) {
			$sql .= " and p.no_bedrooms = '{$bedroom}'";
		}
		if($budget) {
			$bud  = explode('-', $budget);
			$sql .= " and price >= {$bud[0]} ";
			if($bud[1] > 0){
				$sql .= " and price <= {$bud[1]} ";
			}
		}
		if($type){
			$sql .= " and p.type ='{$type}' ";
		}
		if($proptypes) {
			$properties = explode('|', $proptypes);
			$n = count($properties);
			$n--;
			for($i=0;$i<$n;$i++) {
				if($i == 0) { $sql .= " and (";} else { $sql .= " or "; }
				$sql .= "  p.type_id ='{$properties[$i]}'";
			}
			$sql .=")";
		}
		if($bedrooms) {
			$beds = explode("|", $bedrooms);
			$n = count($beds);
			$n--;
			for($i=0;$i<$n;$i++) {
				if($i == 0) { $sql .= " and (";} else { $sql .= " or "; }
				$sql .= "  p.no_bedrooms = '{$beds[$i]}'";
			}
			$sql .=")";
		}
		if($slidersecond > 0) {
			$sql .= " and p.price between {$sliderfirst} and {$slidersecond} ";
		}
		$price = $wpdb->get_var($sql);
		return $price;
	}
	
	public static function getSearchResultwithoutPaging($develpment_id, $city_id, $type_id, $area_id, $bedroom, $budget, $type, $proptypes = '', $bedrooms ='', $sliderfirst = '', $slidersecond = '' ) 	{
		global $wpdb;
		$sql = Property::_getBaseSql();
		if($develpment_id) {
			$sql .= " and p.development_id ='{$develpment_id}'  ";
		}
		if($city_id) {
			$sql .= " and ci.id='{$city_id}' ";
		}
		if($type_id > 0){
			$sql .= " and p.type_id='{$type_id}' ";
		}
		if($area_id) {
			$sql .= " and p.area_id='{$area_id}' ";
		}
		if($bedroom) {
			$sql .= " and p.no_bedrooms = '{$bedroom}'";
		}
		if($budget) {
			$bud  = explode('-', $budget);
			$sql .= " and price >= {$bud[0]} ";
			if($bud[1] > 0){
				$sql .= " and price <= {$bud[1]} ";
			}
		}
		if($type){
			$sql .= " and p.type ='{$type}' ";
		}
		
		if($proptypes) {
			$properties = explode('|', $proptypes);
			$n = count($properties);
			$n--;
			for($i=0;$i<$n;$i++) {
				if($i == 0) { $sql .= " and (";} else { $sql .= " or "; }
				$sql .= "  p.type_id ='{$properties[$i]}'";
			}
			$sql .=")";
		}
		if($bedrooms) {
			$beds = explode("|", $bedrooms);
			$n = count($beds);
			$n--;
			for($i=0;$i<$n;$i++) {
				if($i == 0) { $sql .= " and (";} else { $sql .= " or "; }
				$sql .= "  p.no_bedrooms = '{$beds[$i]}'";
			}
			$sql.=")";
		}
		if($slidersecond > 0) {
			$sql .= " and p.price between {$sliderfirst} and {$slidersecond} ";
		}
		
			$sortby="rating";
			$sortorder = "desc";
		
		$sql .= self::_getOrderBySql($sortby,$sortorder);
		$result = $wpdb->get_results($sql);
		return $result;
	}
	
	public static function getSearchResultCount($develpment_id, $city_id, $type_id, $area_id, $bedroom, $budget, $type, $proptypes = '', $bedrooms ='', $sliderfirst = '', $slidersecond = '' ) {
		global $wpdb;
		$sql = "SELECT count(*) FROM {$wpdb->property} AS p INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS ty ON p.type_id=ty.id LEFT OUTER JOIN {$wpdb->area} AS ar ON p.area_id=ar.id
				INNER JOIN {$wpdb->development} AS de ON p.development_id=de.id INNER JOIN {$wpdb->city} AS ci ON de.city_id=ci.id
				INNER JOIN {$wpdb->country} AS co ON ci.country_id=co.id WHERE p.status='enabled'";
		if($develpment_id) {
			$sql .= " and p.development_id ='{$develpment_id}'  ";
		}
		if($city_id) {
			$sql .= " and ci.id='{$city_id}' ";
		}
		if($type_id > 0){
			$sql .= " and p.type_id='{$type_id}' ";
		}
		if($area_id) {
			$sql .= " and p.area_id='{$area_id}' ";
		}
		if($bedroom) {
			$sql .= " and p.no_bedrooms = '{$bedroom}'";
		}
		if($budget) {
			$bud  = explode('-', $budget);
			$sql .= " and price >= {$bud[0]} ";
			if($bud[1] > 0){
				$sql .= " and price <= {$bud[1]} ";
			}
		}
		if($type){
			$sql .= " and p.type ='{$type}' ";
		}
		if($proptypes) {
			$properties = explode('|', $proptypes);
			$n = count($properties);
			$n--;
			for($i=0;$i<$n;$i++) {
				if($i == 0) { $sql .= " and (";} else { $sql .= " or "; }
				$sql .= "  p.type_id ='{$properties[$i]}'";
			}
			$sql .=")";
		}
		if($bedrooms) {
			$beds = explode("|", $bedrooms);
			$n = count($beds);
			$n--;
			for($i=0;$i<$n;$i++) {
				if($i == 0) { $sql .= " and (";} else { $sql .= " or "; }
				$sql .= "  p.no_bedrooms = '{$beds[$i]}'";
			}
			$sql .=")";
		}
		if($slidersecond > 0) {
			$sql .= " and p.price between {$sliderfirst} and {$slidersecond} ";
		}
		$num = $wpdb->get_var($sql);
		return $num;
	}
	
	public static function getPopertyType(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM $wpdb->type");
		return $result;
		
	}
	
	public static function saveProperty($user_id, $property_id) {
		global $wpdb;
		$result = $wpdb->query("INSERT INTO $wpdb->saved_property (property_id ,user_id)VALUES ('{$property_id}', '{$user_id}');");
		return $result;
	}
	
	public static function sendProperty($user_id, $property_id){
		global $wpdb;
		$result = $wpdb->query( "INSERT INTO $wpdb->email_alert_property (property_id ,user_id)VALUES ('{$property_id}', '{$user_id}');");
		return $result;
	}
	
	public static function getSavedProperty($user_id,$page =1) {
		global $wpdb;
		$result = $wpdb->get_results("SELECT p.*,a.ID as agent_id,  a.display_name  AS agent_name ,a.user_nicename AS agent_slug, t.name as propertytype, t.slug as property_slug FROM $wpdb->property as p 
		INNER JOIN $wpdb->saved_property  as sp on p.id =  sp.property_id 
		INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
		INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
		  WHERE sp.user_id ='{$user_id}'".self::_getOrderBySql('rating').self::_getPagingSql($page, 5)) ;
		return $result;
	}
	
	public static function getSavedPropertyCount($user_id) {
		global $wpdb;
		$num = $wpdb->get_var("SELECT count(*) FROM $wpdb->property as p 
		INNER JOIN $wpdb->saved_property  as sp on p.id =  sp.property_id 
		INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
		INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
		  WHERE sp.user_id ='{$user_id}'".self::_getOrderBySql('rating')) ;
		return $num;
		
	}
	
	
	public static function removeProperty($user_id, $property_id) {
		global $wpdb;
		$result = $wpdb->query("DELETE FROM $wpdb->saved_property WHERE property_id='{$property_id}' and user_id = '{$user_id}' ");
		return $result;
	}
	
	public static function getEmailAlertProperty($user_id, $page =1) {
		global $wpdb;
		$result = $wpdb->get_results("SELECT p.*, a.display_name  AS agent_name ,a.user_nicename AS agent_slug, t.name as propertytype, t.slug as property_slug FROM $wpdb->property as p 
		INNER JOIN $wpdb->email_alert_property  as ep on p.id =  ep.property_id 
		INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
		INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
		  WHERE ep.user_id ='{$user_id}'".self::_getOrderBySql('rating').self::_getPagingSql($page, 5)) ;
		return $result;
	}
	
	public static function getEmailAlertPropertyCount($user_id) {
		global $wpdb;
		$num = $wpdb->get_var("SELECT count(*) FROM $wpdb->property as p 
		INNER JOIN $wpdb->email_alert_property  as ep on p.id =  ep.property_id 
		INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
		INNER JOIN {$wpdb->type} AS t ON p.type_id=t.id
		  WHERE ep.user_id ='{$user_id}'".self::_getOrderBySql('rating')) ;
		return $num;
	}
	
	public static function removeEmailAlert($user_id,$property_id) {
		global $wpdb;
		$result = $wpdb->query("DELETE FROM $wpdb->email_alert_property WHERE property_id ='{$property_id}' and user_id ='{$user_id}' ");
		return $result;
	}
	
	public static function addViewCount($id) {
		global $wpdb;
		$count = $wpdb->get_var("SELECT view_count FROM $wpdb->property WHERE id={$id}");
		$count++;
		$result = $wpdb->query("UPDATE $wpdb->property SET view_count = {$count} WHERE id = $id ");
	}
	
	public static function addCommentCount($id) {
		global $wpdb;
		$count = $wpdb->get_var("SELECT comment_count FROM $wpdb->property WHERE id={$id}");
		$count++;
		$result = $wpdb->query("UPDATE $wpdb->property SET comment_count = {$count} WHERE id = $id ");
	}
	public static function getPropertiesCount() {
		global $wpdb;
		$sql = "SELECT count(*) FROM {$wpdb->property} AS p INNER JOIN {$wpdb->users} AS a ON p.agent_id=a.ID
				INNER JOIN {$wpdb->type} AS ty ON p.type_id=ty.id LEFT OUTER JOIN {$wpdb->area} AS ar ON p.area_id=ar.id
				INNER JOIN {$wpdb->development} AS de ON p.development_id=de.id INNER JOIN {$wpdb->city} AS ci ON de.city_id=ci.id
				INNER JOIN {$wpdb->country} AS co ON ci.country_id=co.id WHERE p.status='enabled'";
		$count = $wpdb->get_var($sql);
		return $count;
	}
	
	public static function getPrice($id) {
		global $wpdb;
		return ($wpdb->get_var("SELECT price FROM $wpdb->property WHERE id = {$id}"));		
		
	}
	
	public static function saveFeedback($name, $email, $phone, $type, $message){ global $wpdb;
		$sql ="	INSERT INTO $wpdb->feedback (name ,email_id ,phone ,comment_type ,message)VALUES ('{$name}', '{$email}', '{$phone}', '{$type}', '{$message}');";
		$result = $wpdb->query($sql);
	}
}
?>