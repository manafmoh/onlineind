<?php
/*
Plugin Name: Country
Plugin URI: http://www.onlineind.com
Description: Country Manager
Version: 1.0
Author: OnlineInd
Author URI: http://www.onlineind.com
*/
class CountryManager
{
	protected $_url;
	protected $_webrootUrl;
	protected $_caption;
	protected $_pluginUrl;
	protected $_parent;
	
	public function __construct()	
	{
		global $wpdb;
		
		$this->_pluginUrl = "classifieds/country.php";
		$this->_url = get_settings("siteurl") . "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_webrootUrl = "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_caption = 'Country';
		$this->_parent = "classifieds/classifiedshome.php";
		
		$wpdb->country = "{$wpdb->prefix}country";
		
		add_action('admin_menu', array(&$this,'adminMenu'));
		add_filter( 'capabilities_list', array(&$this,'capabilities'));	
	}
	
	public function adminMenu() 
	{
		if(current_user_can($this->_caption)) {
			add_submenu_page($this->_parent, $this->_caption, $this->_caption,1 , $this->_pluginUrl, array(&$this, 'controller'));
			
		}
	}
	public function controller() 
	{
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
	
	protected function _showMessage($message)
	{
		echo "<div id=\"message\" class=\"updated fade\"><p><strong>$message</strong></p></div>";
	}
	
	protected function _prepareUrl($action)
	{
		return("$this->_url&amp;action=$action");
	}
	
	protected function _form($record='')
	{
		global $wpdb;
		$heading="Edit";
		$action="update";
		if(!$record)
		{
			$record = new stdClass();
			$heading = "Add new ";
			$action ="add";
		}
		wp_enqueue_script('admin-forms');
		wp_print_scripts('admin-forms');
	
		wp_enqueue_script( 'wp-ajax-response' );
		wp_print_scripts('wp-ajax-response');

		?>
		<div class="wrap">
		<h2><?php echo $heading ?></h2>
		<form name="addform" id="country" method="post" class="add:the-list: validate" >
		<table width="100%" cellspacing="0" cellpadding="5" class="form-table">
		<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="name">Code</label></th>
				<td><input type="text" id="code" name="code" size="35" value="<?php echo $record->code; ?>" aria-required="true" /></td>
		</tr>	
		<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="name">Name</label></th>
				<td><input type="text" id="name" name="name" size="35" value="<?php echo $record->name; ?>" aria-required="true" /></td>
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
	
 	protected  function _view() { 
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
				$search .= " AND (name LIKE '%".$_REQUEST['s']."%') ";
			}
			$list = new DataGrid($wpdb->city, "id");
			$recordCount = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->country} WHERE $search");
			$list->setTotalRecords($recordCount);
			$list->addColumn('name', 'Name', '45%', true);
			$list->addColumn('code', 'Code', '45%', true);
			$list->addAction('Edit', $list->getUrl(array('action'=>'edit', 'id'=>'[id]')), '5%');
			$list->addAction('Delete', $list->getUrl(array('action'=>'delete', 'id'=>'[id]')), '5%', null, 'onclick="return confirm(\'Are you sure?\')"');
			$sql="SELECT * FROM $wpdb->country WHERE $search";
			$list->setMasterDataSource($wpdb->get_results($sql));
			$list->render();?>
			</form>
		</div>

	<?php }
	
	protected function _addNew()
	{
		global $wpdb;
		$query="INSERT INTO  $wpdb->country (code ,name )VALUES ('".$_REQUEST['code']."', '".$_REQUEST['name']."');";
		$result=$wpdb->query($query);
		if($result > 0)
		{
			$this->_showMessage("Successfully Saved");
		}
		$this->_view();
	}
	
	protected  function _delete()
	{
		global $wpdb;
		$id = (int)trim($_REQUEST['id']);
		$query="DELETE FROM $wpdb->country WHERE id ='".$id."'";
		$result = $wpdb->query($query);
		$this->_view();
	}
	
	protected  function _edit()
	{
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$record = $wpdb->get_row("SELECT * FROM {$wpdb->country} WHERE id='$id';");
		if($record) {
			$this->_form($record);
		} else {
			$this->_view();
		}
	}
	
	protected function _update()
	{
		global $wpdb;
		$query="UPDATE {$wpdb->country} SET code = '".$_REQUEST['code']."',`name` = '".$_REQUEST['name']."' WHERE id ='".$_REQUEST['id']."'";
		$result=$wpdb->query($query);
		if($result > 0)
		{
			$this->_showMessage("Successfully Updated");
		}
		$this->_view();
	}
	
}

new CountryManager();

class Country {
	public static function getPermalink($slug) {
		return(get_option('siteurl')."/country/$slug/");
	}
}
?>