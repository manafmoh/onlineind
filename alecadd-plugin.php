<?php
/*
Plugin Name:  WAlecadd Plugin
Plugin URI:   https://developer.wordpress.org/plugins/the-basics/
Description:  Basic WordPress Plugin Header Comment
Version:      20160911
Author:       WordPress.org
Author URI:   https://developer.wordpress.org/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  Alecad Plugin
Domain Path:  /languages
*/

defined('ABSPATH') or die('you can\'t access'); 
class alecaddPlugin
{
	function __construct() {
		//$this->create_custom_post();
	}
	
	public static function create() {
		add_action( 'init', array('alecaddPlugin', 'custom_post_type' ));
		self::register();
		add_action('admin_menu', array('alecaddPlugin','myBookAdminMenu'));
	}
	
	public static function myBookAdminMenu() {
		add_submenu_page('edit.php?post_type=book', 'Register Book', 'Register', 'manage_options', $menu_slug='edit.php?post_type=book&page=register');
	}
	
	public static function register() {
		add_action('admin_enqueue_scripts', array('alecaddPlugin', 'enqueue_style')); 
	}
	
	function activate() {
		//echo "Header Already Sent";
		flush_rewrite_rules();
	}	
	
	function decativate () {
		//echo "Dea Header Already Sent";
		flush_rewrite_rules();
	} 
	
	function uninstall() {
		
	}
	
	public function custom_post_type() {
		register_post_type('book', ['public' => true, 'labels' => ['name' => 'My Book'], 'supports' => ['title', 'editor', 'custom-fields', 'thumbnail' ], 
		//'rewrite' => array( 'slug' => 'mybook', 'with_front' => true ),
		'has_archive' => true,
		'taxonomies'=>array('category'),
		//'show_in_menu' => 'edit.php?post_type=book'
		]);
		register_taxonomy('book_tag', 'book', array(
			'label' => 'Book Tag',
			'show_admin_column' => true,
			'rewrite' => array(
				'slug' => 'booktag'
			)
		));
	}
	
	
	
	public function enqueue_style() {
		wp_enqueue_style('mybookstyle',plugins_url('/assets/style.css', __FILE__)); 
		wp_enqueue_script('mybookscript',plugins_url('/assets/script.js', __FILE__));
	}
}

if(class_exists('alecaddPlugin'))
	//$alecaddPlugin = new alecaddPlugin;
	//$alecaddPlugin->register();
	alecaddPlugin::create();

//activation
//register_activation_hook( __FILE__,  array($alecaddPlugin, 'activate') );

//deactivation
//register_deactivation_hook( __FILE__,  array($alecaddPlugin, 'de activate') );
