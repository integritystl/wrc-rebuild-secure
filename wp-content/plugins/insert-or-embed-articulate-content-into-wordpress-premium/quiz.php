<?php
/*
Plugin Name: Insert or Embed Articulate Content into WordPress Premium
Plugin URI: https://www.elearningfreak.com
Description: Quickly embed or insert e-learning content into a post or page.  Supports Articulate, Captivate, iSpring, and more.  Uses the e-Learning block in WordPress 5.0
Version: 5.88
Author: Brian Batt
Author URI: https://www.elearningfreak.com
*/ 

add_action('plugins_loaded', 'articulate_ioeac_init');
function articulate_ioeac_init(){
	global $articulate_premium_license;
	$articulate_disable_premium;
	
	load_plugin_textdomain( 'quiz', false, dirname(__FILE__)."/"."languages/" );

	if( !function_exists('is_plugin_active') ) {
			
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			
		}
	if(defined('articulate_WP_QUIZ_EMBEDER_PLUGIN_DIR') || is_plugin_active('insert-or-embed-adobe-captivate-content-into-wp/cap.php') || is_plugin_active('insert-or-embed-ispring-content-into-wp/ispring.php') || is_plugin_active('insert-or-embed-articulate-content-into-wordpress/quiz.php')){
	
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins('insert-or-embed-articulate-content-into-wordpress/quiz.php'); 	
		deactivate_plugins('insert-or-embed-adobe-captivate-content-into-wp/cap.php'); 
		deactivate_plugins('insert-or-embed-ispring-content-into-wp/ispring.php'); 
			wp_redirect( admin_url('plugins.php?articulate_disable_premium=1'));   	
			exit;
	}else{
		define ( 'articulate_WP_QUIZ_EMBEDER_PLUGIN_DIR_BASENAME',plugin_basename( __FILE__ ));
		define ( 'articulate_WP_QUIZ_EMBEDER_PLUGIN_DIR', dirname(__FILE__)); // Plugin Directory
		define ( 'articulate_WP_QUIZ_EMBEDER_PLUGIN_URL', plugin_dir_url(__FILE__)); // Plugin URL (for http requests)
			
	
	/* Start License */
	add_filter('articulate_quiz_embeder_count', 'articulate_combined_quiz_embeder_count_premium' );
	add_action('articulate_license', "License",  "License", "manage_options", 'articulate-license','combined_articulate_settings_page');	
	add_action("admin_enqueue_scripts", 'combined_articulate_premium_enqueue_scripts');
	add_action("admin_init", 'combined_articulate_deactivate_old_premium');
	register_activation_hook( __FILE__, 'combined_articulate_deactivate_old_premium' );
	add_action( 'admin_notices', 'combined_articulate_deactivate_old_premium_notice' );
	register_deactivation_hook( __FILE__, 'combined_articulate_deactivate' );
	
	define("ARTICULATE_PREMIUM_VER", '5.88');
	define('ARTICULATE_PREMIUM_ITEM_NAME','Insert or Embed Articulate Content into WordPress Premium');
	define('ARTICULATE_PREMIUM_SHORT_NAME','articulate_premium');
	define('ARTICULATE_PREMIUM_AUTHOR','Brian Batt');
	define('ARTICULATE_PREMIUM_URL','https://www.elearningfreak.com');

	if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
		include( dirname( __FILE__ ) . '/classes/EDD_SL_Plugin_Updater.php' );
	}
			
	if( ! class_exists( 'articulate_license_manager' ) ){
		include( dirname( __FILE__ ) . '/classes/articulate_license_manager.php' );
	}
	
	$articulate_premium_license = new articulate_license_manager( __FILE__,
								ARTICULATE_PREMIUM_ITEM_NAME,
								ARTICULATE_PREMIUM_VER,
								ARTICULATE_PREMIUM_AUTHOR,
								ARTICULATE_PREMIUM_SHORT_NAME,
								ARTICULATE_PREMIUM_URL
							 );
	

	function combined_articulate_deactivate_old_premium(){

	if (is_plugin_active('insert-or-embed-articulate-content-into-wordpress/quiz.php')) {

    deactivate_plugins(array('insert-or-embed-articulate-content-into-wordpress/quiz.php')); 	
  	wp_redirect( admin_url('plugins.php?articulate_disable_premium=1'));   	
	}
	if (is_plugin_active('insert-or-embed-adobe-captivate-content-into-wp/cap.php')) {

    deactivate_plugins(array('insert-or-embed-adobe-captivate-content-into-wp/cap.php')); 	
  	wp_redirect( admin_url('plugins.php?articulate_disable_premium=1'));   	
	}
	if (is_plugin_active('insert-or-embed-ispring-content-into-wp/ispring.php')) {

    deactivate_plugins(array('insert-or-embed-ispring-content-into-wp/ispring.php')); 	
  	wp_redirect( admin_url('plugins.php?articulate_disable_premium=1'));   	
	}
	
	}
	function combined_articulate_deactivate_old_premium_notice(){
		global $nonce;
		$nonce = wp_create_nonce('wp_rest');
		if ( wp_verify_nonce( $nonce, 'wp_rest' ) ){
			if(@$_GET['articulate_disable_premium'] == 1){
			echo '<div class="notice notice-success is-dismissible">
			<p>'.__('We noticed you were running older versions of the Insert or Embed plugin, we deactivated those since they are no longer needed!', 'quiz').'</p>
		</div>';
			}
		}
	}
	function combined_articulate_premium_enqueue_scripts(){
	
	global $nonce;
	$nonce = wp_create_nonce('wp_rest');
	if ( wp_verify_nonce( $nonce, 'wp_rest' ) ){
		$page = isset($_GET['page']) ? $_GET['page'] : '';
		if($page == 'articulate-license'){
		wp_enqueue_style('materialize-css', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'css/materialize.css?v=587');
		wp_enqueue_script('materializejs', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/materialize.js?v=587' );
		wp_enqueue_script('jshelpers', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/jshelpers.js?v=587' );
		}
	}
		
	}
	function combined_articulate_deactivate(){
	do_action('articulate_deactivate');	
	}
	
	
	function articulate_combined_quiz_embeder_count_premium($count){
		global $articulate_premium_license;
		
		if( $articulate_premium_license->check_license() == 'valid'){	
			$count = 100 * 100;
		}
		
		return $count;
		
	}
	
	
global $wpdb;
require_once "settings_file.php" ;
require_once "include/class-custom-fs-functions.php";
require_once "class-quiz-unzip.php";
require_once "functions.php" ;
function articulate_gutenberg_load() {
	if ( function_exists( 'register_block_type' ) ) {
		include_once( articulate_WP_QUIZ_EMBEDER_PLUGIN_DIR . "/gutenberg/gutenberg.php");
	}
};

add_action( 'plugins_loaded', 'articulate_gutenberg_load', 999 );
include_once( articulate_WP_QUIZ_EMBEDER_PLUGIN_DIR . "/include/shortcode.php");
register_activation_hook(__FILE__,'articulate_quiz_embeder_install'); 
/*add_action( 'admin_notices', 'quiz_embeder_banner');*/
register_deactivation_hook( __FILE__, 'articulate_quiz_embeder_remove' );
function articulate_quiz_embeder_count(){
$count = 2;
return apply_filters('articulate_quiz_embeder_count', $count);
}
function articulate_quiz_embeder_install() {
articulate_quiz_embeder_create_protection_files( true );//this function will create the upload directory also.
}
function articulate_quiz_embeder_remove() {
}
add_action( 'wp_ajax_quiz_upload', 'wp_ajax_quiz_upload' );
add_action( 'wp_ajax_articulate_del_dir', 'articulate_wp_ajax_del_dir' );
add_action( 'wp_ajax_rename_dir', 'articulate_wp_ajax_rename_dir' );
function articulate_wp_myplugin_media_button() {
$wp_myplugin_media_button_image = articulate_getPluginUrl() . 'quiz.png';
$siteurl = get_admin_url();
echo '<a href="'.$siteurl.'media-upload.php?type=articulate-upload&TB_iframe=true&tab=articulate-upload" class="thickbox quiz_btn" style="vertical-align: middle;height:15px;width:15px;display:inline-block;">
<img src="'.$wp_myplugin_media_button_image.'" width="15" height="15" /></a>';
 
}
function articulate_media_upload_quiz_form()
{	
wp_enqueue_style('materialize-css', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'css/materialize.css?v=587');
wp_enqueue_script('materializejs', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/materialize.js?v=587' );
wp_enqueue_script('jshelpers', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/jshelpers.js?v=587' );
articulate_print_tabs();
echo '<div class="wrap" style="margin-left:20px;  margin-bottom:50px;">';
echo '<div id="icon-upload" class="icon32"><br></div><h2 class="header">'.__('Upload File', 'quiz').'</h2>';
articulate_print_upload();
echo "</div>";
}
function articulate_media_upload_quiz_content()
{
wp_enqueue_style('materialize-css', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'css/materialize.css?v=587');
wp_enqueue_script('materializejs', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/materialize.js?v=587' );
wp_enqueue_script('jshelpers', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/jshelpers.js?v=587' );
articulate_print_tabs();
echo '<div class="wrap" style="margin-left:20px;  margin-bottom:50px;">';
echo '<div id="icon-upload" class="icon32"><br></div><h2 class="header">'.__('Content Library', 'quiz').'</h2>';
articulate_printInsertForm();
echo "</div>";
}
function articulate_media_upload_quiz()
{	
wp_enqueue_style('materialize-css', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'css/materialize.css?v=587');
wp_enqueue_script('materializejs', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/materialize.js?v=587' );
wp_enqueue_script('jshelpers', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/jshelpers.js?v=587' );
wp_iframe( "articulate_media_upload_quiz_content" );
}
function articulate_media_upload_upload()
{
	global $nonce;
		$nonce = wp_create_nonce('wp_rest');
		if ( wp_verify_nonce( $nonce, 'wp_rest' ) ){
			if ( isset( $_REQUEST[ 'tab' ] ) && strstr( $_REQUEST[ 'tab' ], 'articulate-quiz') ) {
				wp_iframe( "articulate_media_upload_quiz_content" );
			}
			else
			{
				wp_iframe( "articulate_media_upload_quiz_form" );
			}
	}
}
function articulate_print_tabs()
{
function articulate_quiz_tabs($tabs)
{
$newtab1 = array('articulate-upload' => __('Upload File', 'quiz'));
$newtab2 = array('articulate-quiz' => __('Content Library', 'quiz'));
return array_merge($newtab1,$newtab2);
}
add_filter('media_upload_tabs', 'articulate_quiz_tabs' );
media_upload_header();
}
if ( ! function_exists ( 'quiz_embeder_register_plugin_links' ) ) {
function quiz_embeder_register_plugin_links( $links, $file ) {
$base = plugin_basename(__FILE__);
if ( $file == $base ) {
if ( ! is_network_admin() )
$links[] = '<a href="https://www.youtube.com/watch?v=AwcIsxpkvM4" target="_blank">' . __( 'How to Use','quiz' ) . '</a>';
$links[] = '<a href="admin.php?page=articulate-settings-lightbox">' . __( 'Lightbox Settings','quiz' ) . '</a>';
$links[] = '<a href="admin.php?page=articulate-settings-button">' . __( 'Custom Buttons','quiz' ) . '</a>';
$links[] = '<a href="https://www.elearningfreak.com/contact-us/" target="_blank">' . __( 'Support','quiz' ) . '</a>';
}
return $links;
}
}
add_action('media_upload_articulate-upload', 'articulate_media_upload_upload' );
add_action('media_upload_articulate-quiz', 'articulate_media_upload_quiz' );
add_action( 'media_buttons', 'articulate_wp_myplugin_media_button',100);
add_action('wp_footer', 'articulate_quiz_embeder_wp_head' );
add_filter( 'plugin_row_meta', 'quiz_embeder_register_plugin_links', 10, 2 );
function articulate_quiz_embeder_enqueue_script() {
	global $nonce;
	$nonce = wp_create_nonce('wp_rest');
		if ( wp_verify_nonce( $nonce, 'wp_rest' ) ){
			wp_enqueue_script('jquery');
			if( isset( $_GET['et_fb'] ) && $_GET['et_fb'] == 1 )//if DIVI theme is activated and current page is DIVI visual builder
			{
				wp_enqueue_script( 'media-upload' );
				wp_enqueue_script('thickbox');
				wp_enqueue_style( 'thickbox' );
			}
	}
}    
add_action('wp_enqueue_scripts', 'articulate_quiz_embeder_enqueue_script' );
function quiz_modify_coursepress_element_editor_args( $args, $editor_name, $editor_id ){
    $args['media_buttons'] = true;
    return $args;
}
add_filter( 'coursepress_element_editor_args', 'quiz_modify_coursepress_element_editor_args', 100, 3 );

function quiz_admin_footer(){
?>
    <style type="text/css">
        /* additional CSS for coursepress plugin. Fix the style 'Short Overview' label */
        #course-setup-steps .step-content label.drop-line{
            margin-bottom: 40px;
        }
    </style>
    <script type="text/javascript"> jQuery(document).on('contextmenu','.quiz_btn', function(e) { return false; }); </script>
<?php 
}
add_action( 'admin_footer', 'quiz_admin_footer');

function quiz_admin_footer_fix_with_fusion_builder(){
	if( defined( 'FUSION_BUILDER_PLUGIN_DIR' ) )
	{?>
		<div id="quiz_embeder_button_copy" style="display:none;"><?php articulate_wp_myplugin_media_button() ?></div>
		<script type="text/javascript">
			(function( $ ){
				//See /fusion-builder/assets/js/wpeditor/wp-editor.js
				$(document).on('fusionButtons', function( event , current_id ){
					if( $("#wp-"+current_id+"-media-buttons" ).find(".quiz_btn").length == 0 )
					{
						console.log("adding quiz button");
						$("#wp-"+current_id+"-media-buttons" ).append( $("#quiz_embeder_button_copy").html());
					}
				});
			})( jQuery )
		</script>
	<?php 
	}
}
add_action( 'admin_footer', 'quiz_admin_footer_fix_with_fusion_builder');

include_once( articulate_WP_QUIZ_EMBEDER_PLUGIN_DIR . "/admin_page.php");

if( is_admin() )
{
   include_once( articulate_WP_QUIZ_EMBEDER_PLUGIN_DIR . "/include/five_star_wp_rate_notice.php"); 
} 
	}
}
