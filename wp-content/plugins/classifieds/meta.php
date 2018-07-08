<?php
/*
Plugin Name: Meta Manager
Plugin URI: http://www.OnlineInd.com
Description: Meta Manager
Version: 1.0
Author: OnlineInd
Author URI: http://www.OnlineInd.com
*/

class MetaManager{
	protected $_url;
	protected $_webrootUrl;
	protected $_caption;
	protected $_pluginUrl;
	protected $_parent;
	
	public function __construct()	{
		global $wpdb;
		
		$this->_pluginUrl = "classifieds/meta.php";
		$this->_url = get_settings("siteurl") . "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_webrootUrl = "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_caption = 'Meta';
		$this->_parent = "classifieds/classifiedshome.php";
		
		$wpdb->meta = "{$wpdb->prefix}meta";
		
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
	
	protected function _edit() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$record = $wpdb->get_row("SELECT * FROM {$wpdb->meta} WHERE id='$id';");
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
		$wpdb->query("UPDATE {$wpdb->meta} SET description='$description', keyword='$keyword', category_id='$category_id',`status` = '".$_REQUEST['status']."' WHERE id='$id';");
		$this->_showMessage('Successfully Updated!');
		$this->_view();
	}
	
	protected function _delete() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$wpdb->query("DELETE FROM {$wpdb->meta} WHERE id='$id';");
		$this->_showMessage('Successfully Deleted!');
		$this->_view();
	}
	
	protected function _addNew()	{
		global $wpdb;
		extract($_POST);
		if(empty($category_id)) {
			$this->_showMessage('Category not selected!');
			$this->_view();
			return;
		}

		$wpdb->query("INSERT INTO {$wpdb->meta}(description, keyword, category_id) VALUES('$description', '$keyword', '$category_id');");
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
		<form name="addform" id="meta" method="post" class="add:the-list: validate" >	
		<table width="100%" cellspacing="0" cellpadding="5" class="form-table">
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="name">Description</label></th>
				<td><textarea id="description" name="description" cols="50" rows="5"><?php echo $record->description; ?></textarea></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="slug">Keyword</label></th>
				<td><input type="text" id="keyword" name="keyword" size="35" value="<?php echo $record->keyword; ?>" /></td>
			</tr>	
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="category_id">Category</label></th>
				<td><?php echo FormField::select('category_id', $this->_getCategorys(), $record->category_id, '', array(''=>'Category')); ?></td>
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
						<?php echo FormField::select('category_id', $this->_getCategorys(), $_REQUEST['category_id'],'', array(''=>'Category')); ?>
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
			if($_REQUEST['category_id']) {
				$search .= " AND (ci.category_id='".$_REQUEST['category_id']."') ";
			}
			$list = new DataGrid($wpdb->meta, "id");
			$recordCount = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->meta} WHERE $search");
			$list->setTotalRecords($recordCount);
			//$list->addColumn('description', 'Description', '30%', true);
			$list->addColumn('category', 'Category', '30%', true);
			$list->addColumn('status', 'Status', '10%', true);
			$list->addAction('Edit', $list->getUrl(array('action'=>'edit', 'id'=>'[id]')), '5%');
			$list->addAction('Delete', $list->getUrl(array('action'=>'delete', 'id'=>'[id]')), '5%', null, 'onclick="return confirm(\'Are you sure?\')"');
			$sql = "SELECT ci.*, co.name AS category FROM {$wpdb->meta} AS ci INNER JOIN {$wpdb->category} AS co ON ci.category_id=co.id WHERE $search ".$list->getSortOrderSql().' '.$list->getPagingSql().';';
			$list->setMasterDataSource($wpdb->get_results($sql));
			$list->render();?>
			</form>
		</div>

	<?php }

}

new MetaManager();

class Meta {
	public static function getPermalink($slug) {
		return(get_option('siteurl')."/meta/$slug/");
	}
	public static function getMeta(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM $wpdb->meta ORDER BY name");
		$city = array();
		foreach ($result as $row){
			$city[$row->id] = $row->name;
		}
		return $city;
	}
	public static function getMetaById($id){
		global $wpdb;
		$result = $wpdb->get_var("SELECT description, keyword FROM $wpdb->meta WHERE id=$id AND status='enabled'");	
		return $result;
	}
	public static function getMetaBySlug($slug){
		global $wpdb;
		$result = $wpdb->get_row("SELECT ci.description, ci.keyword FROM {$wpdb->meta} AS ci INNER JOIN {$wpdb->category} AS co ON ci.category_id=co.id WHERE co.slug='$slug' AND ci.status='enabled'");	
		return $result;
	}
	public static function getMetaIDBySlug($slug){
		global $wpdb;
		$result = $wpdb->get_var("SELECT id FROM $wpdb->meta WHERE slug='$slug' AND status='enabled'");	
		return $result;
	}
}