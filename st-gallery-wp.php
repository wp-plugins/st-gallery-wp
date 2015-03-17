<?php
/*
Plugin Name: ST Galleria
Plugin URI: http://beautiful-templates.com
Description: Create gallery from your image post with Galleria library & Skitter.
Version: 1.0.8
Author: Beautiful Templates
Author URI: http://beautiful-templates.com
License:  GPL2
*/
require_once 'st-gallery.php';
class StGalleryWp extends StGallery {

	function __construct() {
		parent::__construct();
		add_action('admin_menu', array($this, 'add_plugin_page'));
		add_action('admin_init', array($this, 'register_st_setting'));
		add_action('plugins_loaded', array($this, 'st_init'));
		add_action('admin_init', array($this, 'st_shortcode_button'));
        add_action('admin_footer', array($this, 'st_get_shortcodes'));
		
		add_filter('widget_text', 'do_shortcode');
		if (is_admin()) {
			add_action('admin_enqueue_scripts', array($this, 'st_load_admin_style' ));
		} else {
			add_action('wp_footer', array($this, 'st_load_style' ));
			add_action('wp_footer', array($this, 'st_load_themes' ));
			add_action('wp_footer', array($this, 'st_load_skitter' ));
		}
	}


	/*
	 *  Add button get gallery to post editor
	 */
    public function st_shortcode_button(){
        if( current_user_can('edit_posts') &&  current_user_can('edit_pages') ){
            add_filter( 'mce_external_plugins', array($this, 'st_add_buttons' ));
            add_filter( 'mce_buttons', array($this, 'st_register_buttons' ));
        }
    }

    public function st_add_buttons( $plugin_array ){
        $plugin_array['st_button_get_gallery'] = plugin_dir_url( __FILE__ ) . '/admin/js/addbutton.js';
        return $plugin_array;
    }

    public function st_register_buttons( $buttons ){
        array_push( $buttons, 'st_button_get_gallery' );
        return $buttons;
    }

    public function st_get_shortcodes(){
        echo '<script type="text/javascript">
        var list_gallery_id = new Array();
        var list_gallery_name = new Array();';
		$count = 0;
		if (!empty($this->options)){
			foreach ($this->options as $key => $value) {
				echo "list_gallery_id[{$count}] = '{$key}';";
				echo "list_gallery_name[{$count}] = '{$value['name']}';";
				$count++;
			}
		}
        echo '</script>';
    }
	/*
	 *  End: Add button get gallery to post editor
	 */


	/**
	 * Add plugin page menu
	 */
	function add_plugin_page() {
		add_menu_page( 'ST Gallery WP' , 'ST Gallery WP' , 'manage_options', 'st_gallery', array($this, 'st_router'), 'dashicons-images-alt2');
		add_submenu_page('st_gallery', 'ST Gallery WP' , __('All Gallery', 'st-gallery' ), 'manage_options', 'st_gallery', array($this, 'st_router'));
		add_submenu_page('st_gallery', __('Add New Gallery', 'st-gallery' ) , __('Add New', 'st-gallery' ), 'manage_options', 'st_gallery&action=add', array($this, 'st_gallery'));
	}

	function st_router() {
		if (isset($_GET['action'])) {
			$action = $_GET['action'];
			switch ($action) {
				case 'add': 	$this -> galleryEditor($action);
					break;
				case 'edit': 	$this -> galleryEditor($action);
					break;
				default: 		$this -> allGallery();
					break;
			}
		}else{
			$this -> allGallery();
		}
	}

	/**
	 * Register settings
	 */
	function register_st_setting() {
		register_setting('st_option_group', 'st_gallery_wp', array($this, 'sanitize'));
	}

	function sanitize($input) {
		return $input;
	}

	/**
	 * ST Gallery WP locale
	 * Add textdomain
	 */
	function st_init() {
		$plugin_dir = basename(dirname(__FILE__)).'/languages/';
		load_plugin_textdomain('st-gallery', false, $plugin_dir);
	}

	/**
	 * Add css/js for admin settings
	 * Add l18n for script.js file
	 */
	function st_load_admin_style() {
		wp_enqueue_style('st-admin-style', plugins_url('/admin/css/style.css', __FILE__));
		wp_enqueue_script('st-admin-jquery-validate', plugins_url('/admin/js/jquery.validate.js', __FILE__));
		wp_enqueue_script('st-admin-script', plugins_url('/admin/js/script.js', __FILE__), array('jquery-ui-dialog','jquery-ui-accordion', 'thickbox', 'wp-color-picker'));
		wp_enqueue_style('wp-color-picker');
		$translation_array = array(
			'remove' 			=> __('Remove' , 'st-gallery'), 
			'title' 			=> __('Title' , 'st-gallery'), 
			'caption' 			=> __('Caption' , 'st-gallery'), 
			'url' 				=> __('Image URL' , 'st-gallery'),
			'gallery_removed' 	=> __('Gallery Removed.' , 'st-gallery'),
			'note' 				=> __('Drag & drop to sort', 'st-gallery')
		);
		wp_localize_script('st-admin-script', 'st', $translation_array);
 		wp_enqueue_script('st-admin-tooltipsy', plugins_url('/admin/js/tooltipsy.min.js', __FILE__));
		wp_enqueue_script('st-iris-color-picker', plugins_url('/admin/js/iris.js', __FILE__));
	}


	/*
	 * Load Js & Css in Home
	 */
	function st_load_style() {
		wp_enqueue_style('st-style', plugins_url('/css/style.css', __FILE__));
		wp_enqueue_style('st-style-classic', plugins_url('/themes/classic/galleria.classic.css', __FILE__));
		wp_enqueue_style('st-style-v2', plugins_url('/themes/v2/theme.css', __FILE__));
		wp_enqueue_script('st-script-galleria', plugins_url('/js/galleria-1.4.2.js', __FILE__));
		wp_enqueue_script('st-script', plugins_url('/js/script.js', __FILE__));
	}

	function st_load_skitter(){
		wp_enqueue_style('st-skitter-style', plugins_url('/css/skitter.styles.css', __FILE__));
		wp_enqueue_script('st-skitter-jquery', plugins_url('/js/jquery.skitter.js', __FILE__));
		wp_enqueue_script('st-skitter-easing', plugins_url('/js/jquery.easing.1.3.js', __FILE__));
	}

	function st_load_themes(){
		echo "<script type=\"text/javascript\">";
		echo "(function($){";
		echo "$(document).ready(function(){";
		echo "Galleria.loadTheme('".plugins_url('/themes/classic/galleria.classic.js', __FILE__)."');";
		echo "Galleria.loadTheme('".plugins_url('/themes/v2/theme.js', __FILE__)."');";
		echo "});";
		echo "})(jQuery);";
		echo "</script>";
	}

}
new StGalleryWp();
?>