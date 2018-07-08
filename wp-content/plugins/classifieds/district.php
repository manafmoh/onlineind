<?php
/*
Plugin Name: District Manager
Plugin URI: http://www.onlineind.com
Description: District Manager
Version: 1.0
Author: OnlineInd
Author URI: http://www.onlineind.com
*/

class DistrictManager{
	protected $_url;
	protected $_webrootUrl;
	protected $_caption;
	protected $_pluginUrl;
	protected $_parent;
	
	public function __construct()	{
		global $wpdb;
		
		$this->_pluginUrl = "classifieds/district.php";
		$this->_url = get_settings("siteurl") . "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_webrootUrl = "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_caption = 'District';
		$this->_parent = "classifieds/classifiedshome.php";
		
		$wpdb->district = "{$wpdb->prefix}district";
		
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
	
	protected function _getStates() {
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->state} ORDER BY name ASC;");
		$state = array();
		if($result) {
			foreach($result as $item) {
				$state[$item->id] = $item->name;
			}
		}
		return($state);
	}
	
	protected function _edit() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$record = $wpdb->get_row("SELECT * FROM {$wpdb->district} WHERE id='$id';");
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
		$slug = $slug?sanitize_title($slug):sanitize_title($name);
		$wpdb->query("UPDATE {$wpdb->district} SET name='$name', slug='$slug', state_id='$state_id',`selected` = '".$_REQUEST['selected']."',`status` = '".$_REQUEST['status']."' WHERE id='$id';");
		$this->_showMessage('Successfully Updated!');
		$this->_view();
	}
	
	protected function _delete() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$wpdb->query("DELETE FROM {$wpdb->district} WHERE id='$id';");
		$this->_showMessage('Successfully Deleted!');
		$this->_view();
	}
	
	protected function _addNew()	{
		global $wpdb;
		
			extract($_POST);
		if(empty($state_id)) {
			$this->_showMessage('State not selected!');
			$this->_view();
			return;
		}
		$names = explode("\r\n", trim(strip_tags($name)));	
		if($names) {
			foreach($names as $name)	{
				if(!empty($name)){
				$slug = sanitize_title($name);
				$wpdb->query("INSERT INTO {$wpdb->district}(name, slug, state_id) VALUES('$name', '$slug', '$state_id');");
				}
			}
		}
		$this->_showMessage('Successfully Inserted!');
		$this->_view();
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
		<form name="addform" id="district" method="post" class="add:the-list: validate" >	
		<table width="100%" cellspacing="0" cellpadding="5" class="form-table">
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="name">Name</label></th>
				<td><textarea id="name" name="name" cols="50" rows="5"><?php echo $record->name; ?></textarea></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="slug">Slug</label></th>
				<td><input type="text" id="slug" name="slug" size="35" value="<?php echo $record->slug; ?>" /></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="selected">Selected</label></th>
				<td><?php echo FormField::select('selected', array('1'=>'Selected','0'=>'Deselected'),$record->selected,'',''); ?></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="state_id">State</label></th>
				<td><?php echo FormField::select('state_id', $this->_getStates(), $record->state_id, '', array(''=>'State')); ?></td>
			</tr>	
			<tr>
			<th scope="row" valign="top"><label for="status">Status</label></th>
			<td><?php echo FormField::select('status',array('enabled'=>'Enabled','disabled'=>'Disabled'),$record->status,'','') ?></td>
		</tr>
		</table>	
		<p class="submit">
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
						<?php echo FormField::select('state_id', $this->_getStates(), $_REQUEST['state_id'],'', array(''=>'State')); ?>
						<input type="submit" value="Filter" class="button-secondary" />
					</form>
					<br class="clear" />
				</div>
			</div>
			<br class="clear" />
			<?php
			$search = " 1=1 ";
			if($_REQUEST['s']) {
				$search .= " AND (ci.name LIKE '%".$_REQUEST['s']."%') ";
			}
			if($_REQUEST['state_id']) {
				$search .= " AND (ci.state_id='".$_REQUEST['state_id']."') ";
			}
			$list = new DataGrid($wpdb->district, "id");
			$recordCount = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->district} AS ci INNER JOIN {$wpdb->state} AS co ON ci.state_id=co.id WHERE $search");
			$list->setTotalRecords($recordCount);
			$list->addColumn('name', 'Name', '30%', true);
			$list->addColumn('slug', 'Slug', '30%', true);
			$list->addColumn('state', 'State', '30%', true);
			$list->addColumn('selected', 'Selected', '10%', true);
			$list->addColumn('status', 'Status', '10%', true);
			$list->addAction('Edit', $list->getUrl(array('action'=>'edit', 'id'=>'[id]')), '5%');
			$list->addAction('Delete', $list->getUrl(array('action'=>'delete', 'id'=>'[id]')), '5%', null, 'onclick="return confirm(\'Are you sure?\')"');
			$sql = "SELECT ci.*, co.name AS state FROM {$wpdb->district} AS ci INNER JOIN {$wpdb->state} AS co ON ci.state_id=co.id WHERE $search ".$list->getSortOrderSql().' '.$list->getPagingSql().';';
			$list->setMasterDataSource($wpdb->get_results($sql));
			$list->render();?>
			</form>
		</div>

	<?php }

}

new DistrictManager();

class District {
	public static function getPermalink($slug) {
		return(get_option('siteurl')."/district/$slug/");
	}
	public static function getDistrictDropDown($stateid=''){
		global $wpdb;
		$where = "";
		if($stateid) {
			$where = " WHERE state_id = $stateid ";
			}
		$result = $wpdb->get_results("SELECT * FROM $wpdb->district $where ORDER BY name");
		$district = array();
		foreach ($result as $row){
			$district[$row->slug] = $row->name;
		}
		return $district;
	}
	public static function getDistrict($stateid=''){
		global $wpdb;
		$where = "";
		if($stateid) {
			$where = " WHERE state_id = $stateid ";
			}
		$result = $wpdb->get_results("SELECT * FROM $wpdb->district $where ORDER BY name");
		$district = array();
		foreach ($result as $row){
			$district[$row->id] = $row->name;
		}
		return $district;
	}
	public static function getDistrictById($id){
		global $wpdb;
		$result = $wpdb->get_var("SELECT name FROM $wpdb->district WHERE id=$id AND status='enabled'");	
		return $result;
	}
	public static function getDistrictSlugById($id){
		global $wpdb;
		$result = $wpdb->get_var("SELECT slug FROM $wpdb->district WHERE id=$id AND status='enabled'");	
		return $result;
	}
	public static function getDistrictBySlug($slug){
		global $wpdb;
		$result = $wpdb->get_var("SELECT name FROM $wpdb->district WHERE slug='$slug' AND status='enabled'");	
		return $result;
	}
	public static function getAllDistricts($slug){
		global $wpdb;
		
		$state_id = State::getStateIDBySlug($slug);
		$result = $wpdb->get_results("SELECT name,slug FROM $wpdb->district WHERE state_id='$state_id' AND status='enabled' ORDER BY name");	
		$districts = array();
		foreach ($result as $row){
			$districts[$row->slug] = $row->name;
		}
		return $districts;
	}
	public static function getDistrictIDBySlug($slug){
		global $wpdb;
		$result = $wpdb->get_var("SELECT id FROM $wpdb->district WHERE slug='$slug' AND status='enabled'");	
		return $result;
	}
	public static function getSelectedDistrict(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT slug, name FROM $wpdb->district WHERE selected = 1 AND status='enabled' ORDER BY name");	
		$district = array();
		foreach ($result as $row){
			$district[$row->slug] = $row->name;
		}
		return $district;
	}
	public static function getStateSlugByDistrict($district){
		global $wpdb;
		$sql = "SELECT s.id FROM $wpdb->state AS s INNER JOIN $wpdb->district AS d ON d.state_id=s.id WHERE d.slug='$district'";
		$result = $wpdb->get_var($sql);
		return $result;
		}
}