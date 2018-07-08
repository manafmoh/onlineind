<?php
/*
Plugin Name: Classifieds Home
Plugin URI: http://www.onlineind.com
Description: Classifieds Home
Version: 1.0
Author: OnlineInd
Author URI: http://www.onlineind.com
*/

class classifiedsHome {
	
	protected $_url;
	protected $_webrootUrl;
	protected $_caption;
	protected $_pluginUrl;
	protected $_parent;
	
	public function __construct()	{
		global $wpdb;
		
		$this->_pluginUrl = "classifieds/classifiedshome.php";
		$this->_url = get_settings("siteurl") . "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_webrootUrl = "/wp-admin/admin.php?page={$this->_pluginUrl}";
		$this->_caption = 'ClassifiedsHome';
		
		$this->_parent = $this->_pluginUrl;	
		add_action('admin_menu', array(&$this,'adminMenu'));
		add_action('sidemenu', array(&$this,'setSubmenuOrder'));
		add_filter('capabilities_list', array(&$this,'capabilities'));
	}
		
	public function adminMenu() {
		if(in_array('administrator', array_keys(wp_get_current_user()->data->wp_capabilities))) {
			add_menu_page($this->_caption, "Classifieds", 1, $this->_pluginUrl, array(&$this, 'controller'));
		}
		if(current_user_can($this->_caption)) {			
			add_submenu_page($this->_parent, $this->_caption, $this->_caption, 1, $this->_pluginUrl, array(&$this, 'controller'));
		}
	}

	public function setSubmenuOrder() {
		global $submenu, $parent_file;
		if($parent_file == $this->_parent) {
			$menuOrder = array('Country', 'City', 'Development', 'Area', 'Type', 'Property','FeaturedProperty', 'Agent', 'Package', 'Customer', 'Feedback', 'Currency');
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
	
	public function controller() {
		echo "<h1>Welcome to Classified Homepage</h1>";
	}

	public function capabilities($caps) {
		$caps[] = $this->_caption;
		return($caps);
	}

	
}

new classifiedsHome();

