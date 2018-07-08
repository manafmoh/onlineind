<?php
/*
Plugin Name: Package Manager
Plugin URI: http://www.onlineind.com
Description: Package Manager
Version: 1.0
Author: OnlineInd
Author URI: http://www.onlineind.com
*/

class PackageManager{
	
	protected $_url;
	protected $_webrootUrl;
	protected $_caption;
	protected $_pluginUrl;
	protected $_parent;
	
	public function __construct()	{
		global $wpdb;
		
		$this->_pluginUrl = "classifieds/package.php";
		$this->_url = get_settings("siteurl") . "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_webrootUrl = "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_caption = 'Package';
		$this->_parent = "classifieds/classifiedshome.php";
		
		$wpdb->package = "{$wpdb->prefix}package";
		
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
	
	public function showNumericString($name, $record) {
		$map = array(self::UNLIMITED => 'unlimited');
		return(isset($map[$record->{$name}])?$map[$record->{$name}]:$record->{$name});
	}
	
	protected function _getStatus() {
		return array(
			'enabled' => 'Enabled',
			'disabled' => 'Disabled'
		);
	}
	
	protected function _getNumericArray($start, $end) {
		$items = array();
		for($i=$start;$i<=$end;$i++) {
			$items[$i] = $i;
		}
		$items[self::UNLIMITED] = 'unlimited';
		return($items);
	}
	
	protected function _showMessage($message) {
		echo "<div id=\"message\" class=\"updated fade\"><p><strong>$message</strong></p></div>";
	}
	
	protected function _prepareUrl($action) {
		return("$this->_url&amp;action=$action");
	}
		
	protected function _edit() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$record = $wpdb->get_row("SELECT * FROM {$wpdb->package} WHERE id='$id';");
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
		$wpdb->query("UPDATE {$wpdb->package} SET title='$title', days='$days', field1='$field1', status='$status' WHERE id='$id';");
		$this->_showMessage('Successfully Updated!');
		$this->_view();
	}
	
	protected function _delete() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$wpdb->query("DELETE FROM {$wpdb->package} WHERE id='$id';");
		$this->_showMessage('Successfully Deleted!');
		$this->_view();
	}
	
	protected function _addNew()	{
		global $wpdb;
		
		extract($_POST);
		$slug = $slug?sanitize_title($slug):sanitize_title($name);
		$wpdb->query("INSERT INTO {$wpdb->package}(title, days, field1, status) VALUES('$title', '$days', '$field1', '$status');");
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
		<form name="addform" id="package" method="post" class="add:the-list: validate" >	
		<table width="100%" cellspacing="0" cellpadding="5" class="form-table">
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="title">Title</label></th>
				<td><input type="text" id="title" name="title" size="35" value="<?php echo $record->title; ?>" aria-required="true" /></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="days">Days</label></th>
				<td><input type="text" id="days" name="days" size="15" value="<?php echo $record->days; ?>" aria-required="true" /></td>
			</tr>
			<!-- <tr class="form-field">
				<th scope="row" valign="top"><label for="field1">Field1</label></th>
				<td><?php //echo FormField::select('field1', $this->_getNumericArray(0, self::MAX_PROPERTIES), $record->field1); ?></td>
			</tr> -->
			<tr class="form-field">
				<th scope="row" valign="top"><label for="status">Status</label></th>
				<td><?php echo FormField::select('status', $this->_getStatus(), $record->status?$record->status:'enabled'); ?></td>
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
						<input type="submit" value="Filter" class="button-secondary" />
					</form>
					<br class="clear" />
				</div>
			</div>
			<br class="clear" />
			<?php
			$search = " 1=1 ";
			if($_REQUEST['s']) {
				$search .= " AND (title LIKE '%".$_REQUEST['s']."%') ";
			}
			$list = new DataGrid($wpdb->package, "id");
			$recordCount = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->package} WHERE $search");
			$list->setTotalRecords($recordCount);
			$list->addColumn('title', 'Title', '40%', true);
			$list->addColumn('days', 'Days', '25%', true);
			//$list->addColumn('field1', 'Field1', '25%', true, array(&$this, 'showNumericString'));
			$list->addAction('Edit', $list->getUrl(array('action'=>'edit', 'id'=>'[id]')), '5%');
			$list->addAction('Delete', $list->getUrl(array('action'=>'delete', 'id'=>'[id]')), '5%', null, 'onclick="return confirm(\'Are you sure?\')"');
			$sql = "SELECT * FROM {$wpdb->package} WHERE $search ".$list->getSortOrderSql().' '.$list->getPagingSql().';';
			$list->setMasterDataSource($wpdb->get_results($sql));
			$list->render();?>
			</form>
		</div>

	<?php }

}

new PackageManager();

class Package {
	
}