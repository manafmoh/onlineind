<?php
/*
Plugin Name: Customer Manager
Plugin URI: http://www.onlineind.com
Description: Customer Manager
Version: 1.0
Author: OnlineInd
Author URI: http://www.onlineind.com
*/

class CustomerManager {

	const ROLENAME = 'customer';
	
	protected $_url;
	protected $_webrootUrl;
	protected $_caption;
	protected $_pluginUrl;
	protected $_parent;
	
	public function __construct()	{
		global $wpdb;
		
		$this->_pluginUrl = "classifieds/customer.php";
		$this->_url = get_settings("siteurl") . "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_webrootUrl = "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_caption = 'Customer';
		$this->_parent = "classifieds/classifiedshome.php";
				
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
			case 'status':
				$this->_status();
				break;
			default:	
				$this->_view();
			}	
	}

	public function capabilities($caps) {
		$caps[] = $this->_caption;
		return($caps);
	}
		
	public function formatDate($name, $record) {
		return(date('jS F Y h:i:s A', $this->_mysqlDateToTimestamp($record->{$name})));
	}
		
	public function showStatusAction($name, $record, $command) {
		if($record->user_status) {
			$command = 'Disabled <a class="edit" href="'.DataGrid::getUrl(array('action'=>'status', 'option'=>'enable', 'id'=>$record->ID)).'">(Enable)</a>';
		} else {
			$command = 'Enabled <a class="edit" href="'.DataGrid::getUrl(array('action'=>'status', 'option'=>'disable', 'id'=>$record->ID)).'">(Disable)</a>';
		}
		return($command);
	}
	
	protected function _mysqlDateToTimestamp($date) {
		$parts = explode(' ', $date);
		$dateParts = explode('-', $parts[0]);
		$timeParts = explode(':', $parts[1]);
		return(mktime($timeParts[0], $timeParts[1], $timeParts[2], $dateParts[1], $dateParts[2], $dateParts[0]));
	}
	
	protected function _showMessage($message) {
		echo "<div id=\"message\" class=\"updated fade\"><p><strong>$message</strong></p></div>";
	}
	
	protected function _prepareUrl($action) {
		return("$this->_url&amp;action=$action");
	}
	
	protected function _getStatus() {
		return array(
			0 => 'Enabled',
			1 => 'Disabled'
		);		
	}
			
	protected function _status() {
		global $wpdb;
		
		$id = (int)trim($_REQUEST['id']);
		$option = trim($_REQUEST['option']);
		switch($option) {
			case 'enable':
				$wpdb->query("UPDATE {$wpdb->users} SET user_status='0' WHERE ID='$id';");
				break;
			case 'disable':
				$wpdb->query("UPDATE {$wpdb->users} SET user_status='1' WHERE ID='$id';");
				break;
		}
		$email = $wpdb->get_var("SELECT user_login FROM {$wpdb->users} WHERE ID='$id';");
		wp_cache_delete($id, 'users');
		wp_cache_delete($email, 'userlogins');
		$this->_showMessage('Successfully '.ucfirst($option).'d!');
		$this->_view();
	}
		
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
						<?php echo FormField::select('status', $this->_getStatus(), ($_REQUEST['status']!='')?$_REQUEST['status']:-1,'', array(''=>'Status')); ?>
						<input type="submit" value="Filter" class="button-secondary" />
					</form>
					<br class="clear" />
				</div>
			</div>
			<br class="clear" />
			<?php
			$search = " 1=1 AND umc.meta_value LIKE '%".self::ROLENAME."%' ";
			if($_REQUEST['s']) {
				$search .= " AND (u.display_name LIKE '%".$_REQUEST['s']."%') ";
			}
			if($_REQUEST['package']) {
				$search .= " AND (ump.meta_value='".$_REQUEST['package']."') ";
			}
			if($_REQUEST['subscription_status'] == '1') {
				$search .= " AND (DATEDIFF(CURDATE(), CAST(umss.meta_value AS DATETIME))<=(ump.meta_value*".self::PACKAGE_PERIOD.")) ";
			} elseif($_REQUEST['subscription_status'] == '0') {
				$search .= " AND (DATEDIFF(CURDATE(), CAST(umss.meta_value AS DATETIME))>(ump.meta_value*".self::PACKAGE_PERIOD.")) ";
			}
			if($_REQUEST['status'] == '0' || $_REQUEST['status'] == '1') {
				$search .= " AND (u.user_status='".$_REQUEST['status']."') ";
			}
			$list = new DataGrid('customer', 'id');
			$recordCount = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users} AS u
			INNER JOIN {$wpdb->usermeta} AS umc ON u.ID=umc.user_id AND umc.meta_key='wp_capabilities'
			WHERE $search");
			$list->setTotalRecords($recordCount);
			$list->addColumn('display_name', 'Name', '15%', true);
			$list->addColumn('user_email', 'Email', '15%', true);
			$list->addAction('Status', $list->getUrl(array('action'=>'status', 'id'=>'[ID]')), '5%', array(&$this, 'showStatusAction'));
			$sql = "SELECT u.* FROM {$wpdb->users} AS u
			INNER JOIN {$wpdb->usermeta} AS umc ON u.ID=umc.user_id AND umc.meta_key='wp_capabilities'
			WHERE $search ".$list->getSortOrderSql().' '.$list->getPagingSql().';';

			$list->setMasterDataSource($wpdb->get_results($sql));
			$list->render();?>
			</form>
		</div>

	<?php }
	
}

new CustomerManager();

class Customer {
	public static function current() {
		return(wp_get_current_user()->data);
	}
	
	public static function isLoggedIn() {
		return(is_user_logged_in());
	}
	
	public static function register($data, &$activataionKey) {
		global $wpdb;
		
		require_once ABSPATH.WPINC.'/registration.php';
		$userId = wp_insert_user($data);
			
		$activataionKey = md5(uniqid(microtime()));
		$wpdb->query("UPDATE {$wpdb->users} SET user_status='1', user_activation_key='$activataionKey' WHERE ID='$userId';");

		update_usermeta($userId, 'country', $data['country']);
		
		wp_cache_delete($userId, 'users');
		wp_cache_delete($email, 'userlogins');
		return(true);
	}
	
	public static function updateProfile($data) {
		global $wpdb;
		
		require_once ABSPATH.WPINC.'/registration.php';
		$userId = wp_update_user($data);
			
		update_usermeta($userId, 'country', $data['country']);
		
		wp_cache_delete($userId, 'users');
		wp_cache_delete($email, 'userlogins');
		return(true);
	}
	
	public static function userExists($username, $excludeId = '') {
		require_once ABSPATH.WPINC.'/registration.php';
		$userId =  username_exists($username);
		return(!is_null($userId) && $userId !=$excludeId);
	}
	public static function emailExists($email, $excludeId = '') {
		require_once ABSPATH.WPINC.'/registration.php';
		$emailId = email_exists($email);
		return(!is_null($emailId) && $emailId !=$excludeId);
	}
	public static function activate($key, &$user) {
		global $wpdb;
		$user = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE user_activation_key='$key';");
		if($user) {
			$wpdb->query("UPDATE {$wpdb->users} SET user_status='0', user_activation_key='' WHERE ID='{$user->ID}';");
			wp_cache_delete($user->ID, 'users');
			wp_cache_delete($user->user_login, 'userlogins');
			return(true);
		}
		return(false);
	}
	
	public static function isActive($username) {
		global $wpdb;
		return(!$wpdb->get_var("SELECT user_status FROM {$wpdb->users} WHERE user_login='$username';"));
	}
	
	public static function login($username, $password, $remember) {
		global $wpdb;
		if(wp_login($username, $password, false)) { 
			wp_setcookie($username, $password, false, '', '', $remember);
			do_action('wp_login', $username);
			return(true);
		}
		return(false);
	}
	
	public static function logout() {
		wp_clearcookie();
		do_action('tm_logout');
	}
	
	public static function getUserByEmail($email) {
		global $wpdb;
		return($wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE user_email='$email';"));
	}
	
	public static function loadProfile() {
		return(wp_get_current_user()->data);
	}
	
	public static function IssavedClassified($cassified_id) {
		global $wpdb;
		$uid = Site::getCurrentUserId();
		$count = $wpdb->get_var("SELECT count(*) FROM $wpdb->saved_cassified WHERE cassified_id = '{$cassified_id}' and user_id = '{$uid}'");
		if($count > 0) {
			return false;
		}
		return true;
	}
	
	public static function IssentClassified($cassified_id) {
		global $wpdb;
		$uid = Site::getCurrentUserId();
		$count = $wpdb->get_var("SELECT count(*) FROM $wpdb->email_alert_cassified WHERE cassified_id = '{$cassified_id}' and user_id = '{$uid}'");
		if($count > 0) {
			return false;
		}
		return true;
	}
	
	public static function setActivationKey($activationKey, $userId) {
		global $wpdb;
		$wpdb->query("UPDATE {$wpdb->users} SET user_activation_key='$activationKey' WHERE ID='$userId';");
	}
	
	public static function setPassword($password, $userId) {
		global $wpdb;
		$wpdb->query("UPDATE {$wpdb->users} SET user_pass='$password', user_activation_key='' WHERE ID='$userId';");
	}
	
	public static function getUserByKey($activationKey) {
		global $wpdb;
		return($wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE user_activation_key='$activationKey';"));
	}
	
}