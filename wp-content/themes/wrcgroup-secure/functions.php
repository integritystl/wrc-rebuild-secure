<?php
/**
 * wrcgroup-secure functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package wrcgroup-secure
 */

require_once( __DIR__ . '/WRC_Secure_Infrastructure/ACF/ACFSetup.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/ACF/ACFTemplateFields.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserSetup.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserPermissions.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserLogin.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserRegistration.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserAccount.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserExport.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/CustomPostTypes/AgencyCPT.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/CustomPostTypes/DocumentCPT.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/CustomPostTypes/SiteCPT.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/CustomPostTypes/MutualClientCPT.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/CustomPostTypes/EmailNewsCPT.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/CustomPostTypes/LearningLibraryCPT.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Services/CommonServices.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/News/NewsHelper.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/News/EmailNewsHelper.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/News/NewsFlexContentHelper.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Events/EventsHelper.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Library/LibraryHelper.php');

require_once( __DIR__ . '/WRC_Secure_Infrastructure/Bolt/BoltHandler.php');

require_once( __DIR__ . '/WRC_Secure_Infrastructure/News/NewsMenuBuilder.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Library/LibraryMenuBuilder.php');

require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/Helpers/UserValidationHelper.php');




//add custom post types and related data during initialization
if( ! function_exists('wrcgroup_secure_add_custom_post_types')){
	function wrcgroup_secure_add_custom_post_types(){
		\WRCInfrastructure\CustomPostTypes\AgencyCPT::setupAgencies();
		\WRCInfrastructure\CustomPostTypes\DocumentCPT::setupDocuments();
		\WRCInfrastructure\CustomPostTypes\SiteCPT::setupSites();
    \WRCInfrastructure\CustomPostTypes\MutualClientCPT::setupMutualClients();
    \WRCInfrastructure\CustomPostTypes\EmailNewsCPT::setupEmailNews();
    \WRCInfrastructure\CustomPostTypes\LearningLibraryCPT::setupLearningLibrary();
	}
}
add_action('init', 'wrcgroup_secure_add_custom_post_types');


if ( ! function_exists( 'wrcgroup_secure_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function wrcgroup_secure_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on wrcgroup-secure, use a find and replace
	 * to change 'wrcgroup-secure' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'wrcgroup-secure', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'menu-1' => esc_html__( 'Primary', 'wrcgroup-secure' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	//run our user setup script
	\WRCInfrastructure\Users\UserSetup::setupUsers();

  //After setting up users, remove the admin bar for most of them.
  /**
  * @todo Set this up properly for all the roles once ready.
  **/
  if(!current_user_can('administrator') && !is_admin()) {
    show_admin_bar(false);
  }

  //Remove certain default WP capabilities from Event Espresso Event Admin role, if it's active
    if (is_plugin_active('event-espresso-core-reg/espresso.php') ) {
        $ee_admin = get_role( 'ee_events_administrator' );

        //Capabilities we wanna yank out
        $caps = array(
          'edit_users',
          'edit_posts',
          'edit_pages',
          'manage_categories',
          'upload_files',
          'export',
          'import',
          'list_users',
          //events
          'ee_delete_published_events',
          'ee_delete_private_events',
          'ee_delete_event',
          'ee_delete_events',
          'ee_delete_others_events',
          //checkins
          'ee_read_others_checkins',
          'ee_read_checkins',
          'ee_edit_checkins',
          'ee_edit_others_checkins',
          'ee_delete_checkins',
          'ee_delete_others_checkins',
          // venues
          'ee_publish_venues',
          'ee_read_venue',
          'ee_read_venues',
          'ee_read_others_venues',
          'ee_read_private_venues',
          'ee_edit_venue',
          'ee_edit_venues',
          'ee_edit_others_venues',
          'ee_edit_published_venues',
          'ee_edit_private_venues',
          'ee_delete_venue',
          'ee_delete_venues',
          'ee_delete_others_venues',
          'ee_delete_private_venues',
          'ee_delete_published_venues',
          // venue categories
          'ee_manage_venue_categories',
          'ee_edit_venue_category',
          'ee_delete_venue_category',
          'ee_assign_venue_category',
          // prices
          'ee_edit_default_price',
          'ee_edit_default_prices',
          'ee_delete_default_price',
          'ee_delete_default_prices',
          'ee_edit_default_price_type',
          'ee_edit_default_price_types',
          'ee_delete_default_price_type',
          'ee_delete_default_price_types',
          'ee_read_default_prices',
          'ee_read_default_price_types',
          // messages
          'ee_read_messages',
          'ee_read_others_messages',
          'ee_read_global_messages',
          'ee_edit_global_messages',
          'ee_edit_messages',
          'ee_edit_others_messages',
          'ee_delete_messages',
          'ee_delete_others_messages',
          'ee_delete_global_messages',
          'ee_send_message',
          // registration form
          'ee_edit_questions',
          'ee_edit_system_questions',
          'ee_read_questions',
          'ee_delete_questions',
          'ee_edit_question_groups',
          'ee_read_question_groups',
          'ee_edit_system_question_groups',
          'ee_delete_question_groups',
        );

        //This saves to the wp_options table under 'wp_user_roles', so if any of these need to come back,
      // would need to run new functionality to get them back
        foreach ( $caps as $cap ) {
            // Remove the capability.
            $ee_admin->remove_cap( $cap );
        }

    }

}
endif;
add_action( 'after_setup_theme', 'wrcgroup_secure_setup' );



if( function_exists('acf_add_local_field_group') ){
	//add shared ACF fields
	\WRCInfrastructure\ACFSetup::setupSharedACFFields();
	\WRCInfrastructure\ACFTemplateFields::setupTemplateFields();
}

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function wrcgroup_secure_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'wrcgroup_secure_content_width', 640 );
}
add_action( 'after_setup_theme', 'wrcgroup_secure_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function wrcgroup_secure_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'wrcgroup-secure' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'wrcgroup-secure' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'wrcgroup_secure_widgets_init' );

// Enqueue login styles
function my_custom_login()
{
echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/style-login.css" />';
}
add_action('login_head', 'my_custom_login');

/**
 * Enqueue scripts and styles.
 */
function wrcgroup_secure_scripts() {
    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', get_template_directory_uri() . '/js/jquery-3.4.1.min.js', array());
    wp_deregister_script( 'jquery-migrate' );
    wp_enqueue_script('jquery', get_template_directory_uri() . '/js/jquery-migrate-3.0.0.min.js', array());

    wp_enqueue_style( 'wrcgroup-secure-style', get_stylesheet_uri(), array(), '1.3' );

	wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '1.1' );

	//wp_enqueue_script('jquery');
	wp_enqueue_script( 'wrcgroup-secure-fastclick', get_template_directory_uri() . '/js/fastclick.js', array('jquery'), '1.1', true);
	wp_enqueue_script( 'wrcgroup-secure-header', get_template_directory_uri() . '/js/header.js', array('jquery'), '1.1', true);
	wp_enqueue_script( 'wrcgroup-secure-side-nav', get_template_directory_uri() . '/js/side-navigation.js', array('jquery'), '1.1');
	wp_enqueue_script( 'common-services', get_template_directory_uri() . '/js/common-services.js', array('jquery'), '1.1');
  wp_enqueue_script('parsley', get_template_directory_uri() . '/js/parsley.min.js', array('jquery'), '1.1', true);
  wp_enqueue_script('sidr', get_template_directory_uri() . '/js/jquery.sidr.min.js', array('jquery'), '1.1', true);
  wp_enqueue_script('bridge-modal', get_template_directory_uri() . '/js/bridge.js', array('jquery'), time(), true);
  $boltLink = \WRCInfrastructure\Bolt\BoltHandler::returnBoltLink();
  $ottomationLink = get_field('ottomation_link', 'options');
  wp_localize_script( 'bridge-modal', 'bolt_link',
      array(
          'bolt_link'   => $boltLink,
          'ottomation_link' => $ottomationLink
      )
    );
	if(is_page_template('page-document_table.php')) {
		wp_enqueue_script( 'react-document-table', get_template_directory_uri() . '/js/react_document_table/dist/react_document_table.js', array(), '1.2', true);
  }

  if(is_page_template('page-registration.php') || is_page_template('page-my-account.php') || is_page_template('page-password-reset-form.php')) {
    wp_enqueue_script( 'registration', get_template_directory_uri() . '/js/registration.js', ('jquery'),array(), '1.1', true);
    wp_enqueue_script( 'input-mask', get_template_directory_uri() . '/js/jquery.maskedinput.min.js', ('jquery'),array(), '1.1', true);
	}

	if(is_page_template('page-news-template.php')){
		wp_enqueue_script( 'news-archive', get_template_directory_uri() . '/js/ajax-news-load.js', ('jquery'), '1.1');

		wp_localize_script( 'news-archive', 'rest_object',
			array(
			    'api_nonce' => wp_create_nonce( 'wp_rest' ),
			    'api_url'   => home_url('/wp-json/rest/v1/')
			)
    );
  }

  if(is_page_template('page-email-news-template.php')){
		wp_enqueue_script( 'email-news-archive', get_template_directory_uri() . '/js/ajax-email-news-load.js', ('jquery'), '1.1');

		wp_localize_script( 'email-news-archive', 'rest_object',
			array(
			    'api_nonce' => wp_create_nonce( 'wp_rest' ),
			    'api_url'   => home_url('/wp-json/rest/v1/')
			)
    );
	}

  if (is_search()){
    wp_enqueue_script( 'search-load-more', get_template_directory_uri() . '/js/search-load-more.js', ('jquery'), '1.2', true );

    wp_localize_script( 'search-load-more', 'rest_object',
      array(
          'api_nonce' => wp_create_nonce( 'wp_rest' ),
          'api_url'   => home_url('/wp-json/rest/v1/'),
          'posts_per_page' => get_option('posts_per_page'),
      )
    );

  }

  //Learning Library setup
    if(is_page_template('learning-library-list-template.php')){
		wp_enqueue_script( 'learning-archive', get_template_directory_uri() . '/js/ajax-library-load.js', ('jquery'), '1.1');

		wp_localize_script( 'learning-archive', 'rest_object',
			array(
			    'api_nonce' => wp_create_nonce( 'wp_rest' ),
			    'api_url'   => home_url('/wp-json/rest/v1/')
			)
    );
	}

}
add_action( 'wp_enqueue_scripts', 'wrcgroup_secure_scripts' );

//Add an input mask to help format phone numbers properly in admin
add_action( 'admin_enqueue_scripts', 'wrcgroup_secure_admin_scripts' );
if(!function_exists('wrcgroup_secure_admin_scripts')){
	function wrcgroup_secure_admin_scripts(){
		wp_enqueue_script( 'wrcgroup_secure_admin-input_mask', get_template_directory_uri() . '/js/jquery.inputmask.js', array('jquery'), '20180126', true );
		wp_enqueue_script( 'wrcgroup_secure_admin-input', get_template_directory_uri() . '/js/admin-custom-input.js', array('jquery'), '20180126', true );
	}
}

/**
* Enqueue custom admin scripts
**/
function wrcgroup_enqueue_admin_styles(){
  wp_enqueue_style( 'wrcgroup-secure-admin-style', get_template_directory_uri() . '/admin-style.css', array(), '1.1' );
}
add_action( 'admin_enqueue_scripts', 'wrcgroup_enqueue_admin_styles' );

/**
*	Permission Functionality
**/
/**
* @todo clean this up and organize these calls.
**/
add_action( 'template_redirect', 'check_user_authentication', 1);
add_action( 'template_redirect', 'build_user_permissions');
add_action( 'template_redirect', 'check_last_password_reset_date');
// //Password reset flow changes
// add_action('lost_password', 'handle_password_reset_request');
// add_action('login_form_login', 'handle_login_form_redirect');
// //Handle actually trying to reset the password
// add_action( 'login_form_rp', 'redirect_to_custom_password_reset' );
// add_action( 'login_form_resetpass','redirect_to_custom_password_reset' );

//WebAdmin Role remove Tools menu items capabilities to avoid Migrate DB Pro settings
function remove_tools_web_administrator(){
	//Really just have to do this once because it was saved in the DB
	$webAdmin = get_role('web_administrator');

	$caps = array(
		'import',
		'export',
	);

	foreach ( $caps as $cap) {
		$webAdmin->remove_cap($cap);
	}

}
add_action( 'init', 'remove_tools_web_administrator' );

//Remove certain extra menu items for roles with Admin access that aren't Administrator
function user_admin_menu_remove(){
	if ( !current_user_can('administrator') ) {
		remove_menu_page( 'tools.php' );
		remove_menu_page('edit-comments.php');
	}
}
add_action('admin_menu', 'user_admin_menu_remove');

//add endpoint for ajax news load
add_action( 'rest_api_init', 'rest_news_load_endpoint' );
function rest_news_load_endpoint(){
	register_rest_route( 'rest/v1', '/load_more_news/', array(
	    'methods'   => 'POST',
	    'callback'  => 'load_more_news',
	    'args'      => array(
	        'category'  => array( 'required' => false ),
			'tag'  => array( 'required' => false ),
			's'		=> array('required' => false),
			'ppp'  => array( 'required' => true ),
			'page'  => array( 'required' => true ),
	    )
  ) );
}

//add endpoint for ajax email news load
add_action( 'rest_api_init', 'rest_email_news_load_endpoint' );
function rest_email_news_load_endpoint(){
	register_rest_route( 'rest/v1', '/load_more_email_news/', array(
	    'methods'   => 'POST',
	    'callback'  => 'load_more_email_news',
	    'args'      => array(
					'ppp'  => array( 'required' => true ),
					'page'  => array( 'required' => true )
	    )
	) );
}

//add endpoint for ajax Library list load
add_action( 'rest_api_init', 'rest_library_load_endpoint' );
function rest_library_load_endpoint(){
	register_rest_route( 'rest/v1', '/load_more_library/', array(
	    'methods'   => 'POST',
	    'callback'  => 'load_more_library',
	    'args'      => array(
	    	'learning_category'  => array( 'required' => false ),
			'learning_tag'  => array( 'required' => false ),
			's'		=> array('required' => false),
			'ppp'  => array( 'required' => true ),
			'page'  => array( 'required' => true )
	    )
	) );
}

function load_more_email_news($request){
	$params = $request->get_params();
	$posts = \WRCInfrastructure\News\EmailNewsHelper::loadMore($params);
	return new \WP_REST_Response( array('posts' => $posts), 200 );
}

function load_more_news($request){
	$params = $request->get_params();
	$posts = \WRCInfrastructure\News\NewsHelper::loadMore($params);
	return new \WP_REST_Response( array('posts' => $posts), 200 );
}

function set_new_news_cookie($new_post_IDs, $site_id){
  \WRCInfrastructure\News\NewsHelper::setNewPostsCookie($new_post_IDs, $site_id);
}

function load_more_library($request){
	$params = $request->get_params();
	$posts = \WRCInfrastructure\Library\LibraryHelper::loadMore($params);
	return new \WP_REST_Response( array('posts' => $posts), 200 );
}

function set_new_library_cookie($new_lib_IDs, $site_id){
  \WRCInfrastructure\Library\LibraryHelper::setNewLibraryCookie($new_lib_IDs, $site_id);
}
function is_new_lib_post($post_id){
	return \WRCInfrastructure\Library\LibraryHelper::isLibNew($post_id);
}
function destroy_lib_cookies(){
	\WRCInfrastructure\Library\LibraryHelper::deleteLibPostsCookie();
}

//Tied to Event Espresso plugin & it's CPT
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (is_plugin_active('event-espresso-core-reg/espresso.php') ) {
	function set_new_events_cookie($new_event_IDs, $site_id){
	  \WRCInfrastructure\Events\EventsHelper::setNewEventsCookie($new_event_IDs, $site_id);
	}
	function is_new_events_post($post_id){
	  return \WRCInfrastructure\Events\EventsHelper::isEventNew($post_id);
	}

	add_action('wp_logout', 'destroyEventCookies');
	if(!function_exists('destroyEventCookies')) {
	  function destroyEventCookies(){
	    //destroy that cookie!
	    \WRCInfrastructure\Events\EventsHelper::deleteEventPostsCookie();
	  }
	}

	// Remove extra Administrator menus from Event Admin because they should only be in there for Events
	function event_admin_menu_remove() {
		if ( current_user_can('ee_events_administrator') ) {
			remove_menu_page( 'edit.php?post_type=site' );
			remove_menu_page('edit.php?post_type=document');
			remove_menu_page('edit.php?post_type=email_news');
			remove_menu_page('edit.php?post_type=learning-library');
			remove_menu_page('edit.php?post_type=mutual-client');
			remove_menu_page('edit.php?post_type=agency');
		}
	}
	add_action('admin_menu', 'event_admin_menu_remove');

	//Remove 'click here to add new state/province' from Event Espresso 'State' field on Registration page
	//https://gist.github.com/joshfeck/7bf112ee2dc9e49f7e3f14b8c2b59611#file-remove_add_state_form-php
	function event_remove_new_state_form() {
	  remove_filter( 'FHEE__EE_SPCO_Reg_Step_Attendee_Information___question_group_reg_form__question_group_reg_form', array( 'EED_Add_New_State', 'display_add_new_state_micro_form' ), 1, 1 );
	  remove_filter( 'FHEE__EE_SPCO_Reg_Step_Payment_Options___get_billing_form_for_payment_method__billing_form', array( 'EED_Add_New_State', 'display_add_new_state_micro_form' ), 1, 1 );
	}
	add_action( 'wp_loaded', 'event_remove_new_state_form' );

} //end Event Espresso is_active specific functions

function get_all_states(){
  return \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectAllStates();
}

function is_new_news_post($post_id){
  return \WRCInfrastructure\News\NewsHelper::isPostNew($post_id);
}

function get_correct_image_path($assumed_path, $display_pdf){
  return \WRCInfrastructure\News\NewsFlexContentHelper::getCorrectImagePath($assumed_path, $display_pdf);
}

function check_user_authentication() {
  \WRCInfrastructure\Users\UserLogin::checkAuthentication();
}

function check_last_password_reset_date() {
  \WRCInfrastructure\Users\UserAccount::checkLastPasswordResetDate();
}

function build_user_permissions($id = null) {
	return \WRCInfrastructure\Users\UserPermissions::buildPermissionsArray($id);
}

// function redirect_to_custom_password_reset() {
//   if ('GET' == $_SERVER['REQUEST_METHOD'] ) {
//     $user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
//     if ( ! $user || is_wp_error( $user ) ) {
//         $redirect_url = get_field('login_page', 'options');
//         $redirect_url = get_the_permalink($redirect_url->ID);
//         $redirect_url = add_query_arg( 'error', 'resetattempt', $redirect_url );
//         wp_redirect($redirect_url);

//         exit;
//     }
//     $redirect_url = get_field('password_reset_page', 'options');
//     $redirect_url = get_the_permalink($redirect_url->ID);
//     $redirect_url = add_query_arg( 'login', esc_attr( $_REQUEST['login'] ), $redirect_url );
//     $redirect_url = add_query_arg( 'key', esc_attr( $_REQUEST['key'] ), $redirect_url );

//     wp_redirect( $redirect_url );
//     exit;
//   }
//   if ('POST' == $_SERVER['REQUEST_METHOD']) {
//     $rp_key = $_REQUEST['rp_key'];
//     $rp_login = $_REQUEST['rp_login'];
//     $user = check_password_reset_key( $rp_key, $rp_login );

//     if ( ! $user || is_wp_error( $user ) ) {
//         $redirect_url = get_field('password_reset_page', 'options');
//         $redirect_url = get_the_permalink($redirect_url->ID);
//         $redirect_url = add_query_arg( 'error', 'resetattempt', $redirect_url );
//         wp_redirect($redirect_url);

//         exit;
//     }

//     if ( isset( $_POST['pass1'] ) ) {
//       $redirect_url = get_field('password_reset_page', 'options');
//       $redirect_url = get_the_permalink($redirect_url->ID);
//       if ( $_POST['pass1'] != $_POST['pass2'] ) {
//           // Passwords don't match
//           $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
//           $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
//           $redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );

//           wp_redirect( $redirect_url );
//           exit;
//       }

//       if ( empty( $_POST['pass1'] ) ) {
//         // Password is empty

//         $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
//         $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
//         $redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );

//         wp_redirect( $redirect_url );
//         exit;
//       }

//       if(!\WRCInfrastructure\Users\Helpers\UserValidationHelper::isPasswordValid($_POST['pass1'])){
//         $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
//         $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
//         $redirect_url = add_query_arg( 'error', 'password_invalid', $redirect_url );

//         wp_redirect( $redirect_url );
//         exit;
//       }

//       if(\WRCInfrastructure\Users\Helpers\UserValidationHelper::isCurrentPasswordReused($_POST['pass1'], $user)){
//         $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
//         $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
//         $redirect_url = add_query_arg( 'error', 'password_reuse', $redirect_url );

//         wp_redirect( $redirect_url );
//         exit;
//       }

//       if(\WRCInfrastructure\Users\Helpers\UserValidationHelper::isPasswordOld($_POST['pass1'], $user)){
//         $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
//         $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
//         $redirect_url = add_query_arg( 'error', 'password_old', $redirect_url );

//         wp_redirect( $redirect_url );
//         exit;
//       }

//         // Parameter checks OK, reset password
//       reset_password( $user, $_POST['pass1'] );
//       $login_url = get_field('login_page', 'options');
//       $login_url = $login_url->ID;
//       $login_url = add_query_arg( 'password', 'changed', $login_url );
//       wp_redirect( $login_url );
//     } else {
//           echo "Invalid request.";
//     }

//     exit;
//   }
// }

// //Handles redirecting the user when they request a password reset
// function handle_login_form_redirect() {
//   if(isset($_GET['checkemail'])) {
//     $redirect_url = get_field('login_page', 'options');
//     $redirect_url = get_the_permalink($redirect_url->ID);
//     $redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
//     wp_redirect($redirect_url);
//     exit;
//   }
// }
// //Handles both outputting the reset form as well as processing it.
// function handle_password_reset_request() {
//   if( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
//     if( !is_user_logged_in() ) {
//       $password_reset_page = get_field('reset_request_page', 'options');
//       wp_redirect(get_the_permalink($password_reset_page->ID));
//     }
//   }
//   else if ( 'POST' == $_SERVER['REQUEST_METHOD']) {
//     $errors = retrieve_password();
//     $redirect_url = get_field('reset_request_page', 'options');
//     $redirect_url = get_the_permalink($redirect_url->ID);
//     if( is_wp_error($errors) ) {
//       $redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
//     }
//     else {
//       var_dump('what');
//       $redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
//     }

//     wp_redirect($redirect_url);
//     exit;
//   }
// }


//custom styles for admin acf fields
add_action('acf/prepare_field/name=login_attempt_count', 'prepare_login_attempt_count');
function prepare_login_attempt_count($field){
    $login_page = get_field('login_page', 'option');
    $login_max_attempts = get_field('login_attempt_max', 'option');

    if(intval($field['value']) >= intval($login_max_attempts)){
      $field['wrapper'] = array('class' => 'admin_max_login_alert');
    }

    return $field;
}

add_action('acf/update_value/name=login_attempt_count', 'send_login_attempt_count_reset_message', 10, 3);
function send_login_attempt_count_reset_message($value, $post_id, $field){
  $login_page = get_field('login_page', 'option');
  $login_max_attempts = get_field('login_attempt_max', 'option');
  $old_attempt_count = get_field('login_attempt_count', $post_id);

  //this is always available on the user edit screen
  global $user_id;
  $user = get_user_by('id', $user_id);

  if(intval($value) < intval($login_max_attempts) && intval($old_attempt_count) >= intval($login_max_attempts)){
    \WRCInfrastructure\Users\UserLogin::sendLoginAttemptCountResetMessage($user);
  }

  return $value;
}

//Wipe the transient whenever the user profile is updated
add_action( 'profile_update', 'wipe_permissions_transient');

function wipe_permissions_transient($user_id) {
	delete_transient('user_permissions_' . $user_id);
}
//When a profile is updated via the admin, check and see if the password was updated.
//If so, we need to clear out the last update and jam the password into old passwords
//array and such. This allows an admin to reset a password for someone if they
//for some reason don't understand how to do it themselves.
add_action( 'profile_update', 'check_password_update', 10, 2);
function check_password_update($user_id, $old_user_data) {
  return \WRCInfrastructure\Users\UserAccount::profile_update_check($user_id, $old_user_data);
}

/**
* Get Site Functionality
**/
add_action('template_redirect', 'get_current_site');
function get_current_site() {
  if(isset($_GET['current_site'])) {
    $current_site_id = $_GET['current_site'];
    $current_site = get_post($current_site_id);
    return $current_site;
  }
	return \WRCInfrastructure\Services\CommonServices::getCurrentSite();
}


/**
* Make sure previous login date is set correctly
**/
add_action('wp_login', 'setPreviousLoginDate', 10, 2);
if(!function_exists('setPreviousLoginDate')) {
  function setPreviousLoginDate($login, $user){
    $last_login = get_user_meta( $user->ID, '_last_login', true );
    if(!empty($last_login)){
      update_user_meta( $user->ID, '_prev_last_login', $last_login );
    }
    update_user_meta( $user->ID, '_last_login', time() );
  }
}


add_action('wp_logout', 'destroyCookies');
if(!function_exists('destroyCookies')) {
  function destroyCookies(){
    //destroy that cookie!
    \WRCInfrastructure\News\NewsHelper::deleteNewPostsCookie();
  }
}

if(!function_exists('output_news_navigation')) {
  function output_news_navigation($permissions, $current_site, $isEmailNews){
    \WRCInfrastructure\News\NewsMenuBuilder::buildMenu($permissions, $current_site, $isEmailNews);
  }
}

if(!function_exists('output_library_navigation')) {
  function output_library_navigation($permissions, $current_site){
    \WRCInfrastructure\Library\LibraryMenuBuilder::buildMenu($permissions, $current_site);
  }
}



/** -----------------------------------------------------------

	TEMPLATE FUNCTIONS. SHOULD EVENTUALLY BE MOVED TO A MORE
	ORGANIZED LOCATION. LIKELY IN INFRASTRUCTURE

-----------------------------------------------------------**/

/**
* Outputs the left navigation menu.
**/
if(!function_exists(('output_left_navigation'))) {
  function output_left_navigation($pageID, $permissions) {
    /**
          * Outputs the menu on the left, if relevant
          * this needs to grab the user's state permissions, then grab all pages 2 levels down (Site ->
          * Section -> Target Pages), then filter so we only have the ones that are flagged for states
          * we have permissions on, then output them nicely (including their children.)
          *
          * This function exists as a wrapper to walk up and grab the ancestors.
          **/

					//Query for any posts that have exclude_from_left_nav set to true
					// get the ids to put in exclude param

          //Grab an array of ancestor page IDs
          $ancestors = get_ancestors($pageID, 'page');
          //Get the second to last Ancestor. Last is the "site" - top level. Next one down is the
          //container
          $containingAncestor = $ancestors[count($ancestors) - 1];
          //Calls this function to actually output the markup.
          return_left_nav_markup($containingAncestor, $permissions, $pageID);
  }
}

/**
* Returns the actual markup for the left nav. this is done so we can recursively
* call this.
**/
if(!function_exists('return_left_nav_markup')) {
  function return_left_nav_markup($pageID, $permissions, $currentPageID) {
    $stateMetaQuery = array('relation' => 'OR');
          if($permissions['states']){
            foreach($permissions['states'] as $state) {
              $stateQuery = array(
                'key' => 'states',
                'value' => $state,
                'compare' => 'LIKE'
              );
              array_push($stateMetaQuery, $stateQuery);
            }
          }
					//Check the ACF field to Exclude Page from Left Nav,
					// We only include ones set to False (0 in the DB), which is the default
					//exclude_from_left_nav
					$excludeFromNav = array(
						'relation' => 'OR',
						array(
							'key' => 'exclude_from_left_nav',
							'value' => 0,
							'compare' => '='
						),
						array(
							'key' => 'exclude_from_left_nav',
							'compare' => 'NOT EXISTS'
						),
					);


          $pageArgs = array (
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_type' => 'page',
            'post_parent' => $pageID,
            'hierarchical' => 1,
            'meta_query' => array($excludeFromNav, $stateMetaQuery),
						//'meta_query' => $stateMetaQuery,
            'posts_per_page' => -1

          );
          $pages = get_posts($pageArgs);
					// echo '<pre>';
          // var_dump($pageArgs);
					// //var_dump($excludeFromNav);
					// echo '</pre>';
          foreach($pages as $page) {
            //Set up the classes, if on current page add that
            $classes = "page_item page_item-" . $page->ID . " ";
            if($currentPageID === $page->ID) {
              $classes .= " current_page_item";
            }
						//make sure we're only checking for Pages as children because the default includes attachments :\
						$gotKidsArgs = array(
							'post_parent' => $page->ID,
							'post_type' => 'page',
						);
						$gotPageKids = get_children($gotKidsArgs);
            if( count( $gotPageKids ) ) {
              $classes .= " page_item_has_children ";
            }
            //Output the actual LI item
            ?>
            <li class="<?php echo $classes;?>">
              <a href="<?php echo get_permalink($page->ID);?>"><?php echo $page->post_title; ?></a>
            <?php
              //If we have children, recursively call this function to output them
              if( count( $gotPageKids ) ){
                echo "<ul class='children'>";
                  return_left_nav_markup($page->ID, $permissions, $currentPageID);
                echo "</ul>";
              }
              echo "</li>";
          }
          //And reset the query
          wp_reset_query();
  }
}

/**
* Functions used to output header markup
**/
function siteMenuSort($a, $b) {
	if($a->menu_order < $b->menu_order){
		return -1;
	}
	else return 1;
}
if(!function_exists('get_sites_dropdown_markup')){
  function get_sites_dropdown_markup($permissions, $site_pages, $current_site){
    $markup = '';

    usort($site_pages, 'siteMenuSort');

    //var_dump($site_pages);
    if($permissions["sites"] && count($permissions["sites"]) > 1) :
      $markup .= '<div class="companies-wrapper">';
        $markup .= '<a class="companies-link header-link dropdown-toggle" href="#">Companies<i class="fa fa-caret-down" aria-hidden="true"></i></a>';
        $markup .= '<div class="companies-dropdown dropdown-target">';
        $markup .= '<ul>';
        foreach ($site_pages as $page) :
          $company = get_field('site', $page->ID);
          foreach ($permissions['sites'] as $site) :
            if(isset($current_site) && $company->ID === $site->ID && $company->ID && $current_site->ID){
              $markup .= '<li><a href="';
              $markup .= get_the_permalink($page->ID);
							$markup .= '"';
							if(isset($current_site) && $company->ID === $current_site->ID) {
								$markup .= 'class="current-site';
						  }
              $markup .= '">';
              $markup .= $page->post_title;
              $markup .= '</a></li>';
            }
            //This catches pages that don't have a "current site" such as the 404 page
            else if (!isset($current_site) && $company->ID === $site->ID) {
              $markup .= '<li><a href="';
              $markup .= get_the_permalink($page->ID);
              $markup .= '">';
              $markup .= $page->post_title;
              $markup .= '</a></li>';
            }
          endforeach;
        endforeach;
        $markup .= '</ul>';
        $markup .= '</div>';
      $markup .= '</div>';
    endif;

    return $markup;
  }
}

if(!function_exists('get_user_account_nav_item_markup')){
  function get_user_account_nav_item_markup($current_site){
    $current_user = wp_get_current_user();
    $my_account_page = get_field('my_account_page', 'options');
    $login_page = get_field('login_page', 'options');
    $markup = '';

    $markup .= '<li class="user-links__account-item">';
    $markup .= '<a href="#" class="user-link header-link dropdown-toggle">';
    $markup .= '<i class="fa fa-user" aria-hidden="true"></i>';
    if($current_user instanceof WP_User) :
      $markup .= '<span>' . $current_user->display_name . '</span>';
    endif;
    $markup .= '<i class="fa fa-caret-down" aria-hidden="true"></i>';
    $markup .= '</a>';
    $markup .= '<div class="user-dropdown dropdown-target">';

		//Avoid errors on Events page template where there's no Current Site
		if ( isset($current_site) ) {
			$markup .= '<a href="' . ( empty($my_account_page) ? '#' : get_the_permalink($my_account_page->ID) . '?current_site=' . $current_site->ID ) . ' ">My Account</a>';
		} else {
			$markup .= '<a href="' . ( empty($my_account_page) ? '#' : get_the_permalink($my_account_page->ID) ). ' ">My Account</a>';
		}
		$markup .= '<a href="' . wp_logout_url(get_the_permalink($login_page->ID)) . '" class="btn-link">Logout</a>';
    $markup .= '</div>';
    $markup .= '</li>';

    return $markup;
  }
}

if(!function_exists('get_secure_email_nav_item_markup')){
  function get_secure_email_nav_item_markup(){
    $markup = '';

    $markup .= '<li class="navigation__secure-email-item"><a class="header-link" href="';
    $markup .= get_field('secure_email_link', 'option');
    $markup .= '" target="_blank">Secure Email</a></li>';

    return $markup;
  }
}

//Excerpt truncation function so our excerpts in toggles aren't giant
function excerpt($limit) {
  $excerpt = explode(' ', get_the_excerpt(), $limit);
  if (count($excerpt)>=$limit) {
    array_pop($excerpt);
    $excerpt = implode(" ",$excerpt).'...';
  } else {
    $excerpt = implode(" ",$excerpt);
  }
  $excerpt = preg_replace('`[[^]]*]`','',$excerpt);
  return $excerpt;
}

function wrc_excerpt_length( $length ) {
	return 75;
}
add_filter( 'excerpt_length', 'wrc_excerpt_length', 999 );

//only what to generate this once because dbs calls is expensive
//return markup instead of outputting it. that way we can just assign it to a veriable and output it multiple times in header
if(!function_exists('get_news_nav_item_markup')){
  function get_news_nav_item_markup($news_query, $news_page, $current_site){
    $markup = '';

    $markup .= '<li class="navigation__news-item">';
    $markup .=  '<a class="news-link header-link dropdown-toggle" href="#">News<span class="new-count"></span></a>';
    $markup .= '<div class="recent-news-container dropdown-target">';

    $post_count = 0;
    if(!empty($news_query) && $news_query->have_posts()) :
        $markup .= '<ul class="recent-news-list">';
        while($news_query->have_posts()) : $news_query->the_post();
            $cats = get_the_category(get_the_id());
            $cat_names = array();
						//get the Categories ACF field from the site's News page so we only get categories for our Current Site for Posts
						$siteNewsCategories = get_field('news_categories', $news_page->ID);
            foreach ($cats as $cat) {
              if($cat->category_parent !== 0 && in_array($cat->term_id,$siteNewsCategories)){
                array_push($cat_names, $cat->name);
              }
            }
            $cat_list = implode(", ", $cat_names);
            $markup .= '<li data-id="' . get_the_ID() . '" class="news-item ' . (is_new_news_post(get_the_ID()) ? ' new-item' : '') . '">';
            $markup .= '<a class="news-item-link" data-id="' . get_the_ID() . '"" href="' . get_permalink(get_the_ID()) . '?current_site=' . $current_site->ID . '">' . get_the_title() . '</a>';
            $markup .= '<span>' . get_the_date() . '</span><span class="news-item-cat"><p>' . $cat_list .'</p></span>';
            $markup .= '<p>' . excerpt(15) . '</p>';
            $markup .= '<a class="mark-as-read" data-id="' . get_the_ID() . '" href="#">Mark as read</a>';
            $markup .= '</li>';
          endwhile;
        $markup .= '</ul>';
      endif;

      $news_link = (empty($news_page) ? '#' : get_the_permalink($news_page->ID));
      $markup .= '<a href="' . $news_link . '" class="btn-link">View All News</a>';
    $markup .= '</div>';
    $markup .= '</li>';

    return $markup;
  }
}

//only want to generate this once because dbs calls is expensive
//return markup instead of outputting it. that way we can just assign it to a veriable and output it multiple times in header
if(!function_exists('get_learning_nav_item_markup')){
  function get_learning_nav_item_markup($library_query, $library_page, $current_site){
    $markup = '';

    $markup .= '<li class="navigation__library-item">';
    $markup .=  '<a class="library-link header-link dropdown-toggle" href="#">Learning Library<span class="new-count"></span></a>';
    $markup .= '<div class="recent-lib-container dropdown-target">';

    $post_count = 0;
    if(!empty($library_query) && $library_query->have_posts()) :
        $markup .= '<ul class="recent-lib-list">';
        while($library_query->have_posts() && $post_count < 3){ //3 is max posts for dropdown
          $library_query->the_post();
          if(learning_post_sites_contain_id(get_the_id(), $current_site->ID)){
            $libCategories = get_the_terms( get_the_id(), 'learning_category' );
            $libCategory_entries = array();
            if(!empty($libCategories)){
              foreach($libCategories as $category){
                if($category->parent !== 0){
                  array_push($libCategory_entries, $category->name);
                }
              }
            }
            $cat_list = implode(", ", $libCategory_entries);
            $markup .= '<li data-id="' . get_the_ID() . '" class="library-item ' . (is_new_lib_post(get_the_ID()) ? ' new-item' : '') . '">';
            $markup .= '<a class="library-item-link" data-id="' . get_the_ID() . '"" href="' . get_permalink(get_the_ID()) . '?current_site=' . $current_site->ID . '">' . get_the_title() . '</a>';
            $markup .= '<span>' . get_the_date() . '</span><span class="news-item-cat"><p>' . $cat_list .'</p></span>';
            $markup .= '<p>' . excerpt(15) . '</p>';
            $markup .= '<a class="mark-as-read" data-id="' . get_the_ID() . '" href="#">Mark as read</a>';
            $markup .= '</li>';

            $post_count++;
          }
        } 
          
        $markup .= '</ul>';
      endif;

      	$learning_link = (empty($library_page) ? '#' : get_the_permalink($library_page->ID));
      	$markup .= '<a href="' . $learning_link . '" class="btn-link">View Library</a>';
    	$markup .= '</div>';
    	$markup .= '</li>';

    return $markup;
  }
}

if(!function_exists('learning_post_sites_contain_id')){
  function learning_post_sites_contain_id($learning_post_id, $site_id){
    $post_site_array = get_field('attached_site', $learning_post_id);
    if(!empty($post_site_array)){
      foreach($post_site_array as $post_obj){
        if($post_obj->ID == $site_id){
          return true;
        }
      }
    }

    return false;
  }
}

//will probably need to be the same as news
if(!function_exists('get_events_nav_item_markup')){
  function get_events_nav_item_markup($event_query, $events_page, $current_site){
    $markup = '';

    $markup .= '<li class="navigation__events-item">';
    $markup .= '<a class="events-link header-link dropdown-toggle" href="#">Events<span class="new-count"></span></a>';
    $markup .= '<div class="recent-events-container dropdown-target">';

    // Run a query for Event Espresso events, ordered by start date
    // Ref: https://gist.github.com/joshfeck/e3c9540cd4ccc734e755
    if (!empty($event_query) && $event_query->have_posts()) :
			$markup .= '<ul class="recent-events-list">';
				while ($event_query->have_posts()) : $event_query->the_post();
	      //Find event categories and output them as comma separate text instead of text + link
	      $eventCats = get_the_terms( get_the_ID(), 'espresso_event_categories' );
	      $eventCat_names = array();
				if (!empty($eventCats)) {
					foreach ($eventCats as $eventCat) {
						if($eventCat->category_parent !== 0) {
							array_push($eventCat_names, $eventCat->name);
						}
					}
				}

	      $eventCat_list = implode(", ", $eventCat_names);

	      $markup .= '<li class="event-item ' . (is_new_events_post(get_the_ID()) ? ' new-item' : '') . '">';
				//$markup .= '<li class="event-item">';
	     // $markup .= '<div>';
	      $markup .= '<a class="event-item-link" data-id="' . get_the_ID() . '" href="' . get_permalink(get_the_ID()) . '?current_site=' . $current_site->ID .'">';
	      $markup .= get_the_title();
	      $markup .= '</a>';
	      $markup .= '<div class="event-item_meta"><span>' . espresso_event_date('', '', false, false) . '</span><span>' . $eventCat_list . '</span></div>';
	      $markup .= '<p>' . excerpt(15) . '</p>';
				$markup .= '<a class="mark-as-read" data-id="' . get_the_ID() . '" href="#">Mark as read</a>';
	   //   $markup .= '</div>';
	      $markup .= '</li>';

	      endwhile;
			$markup .= '</ul>';
		else:
			$markup .= '<p>No upcoming events.</p>';
    endif;
    wp_reset_query();
    wp_reset_postdata();

    $events_link = (empty($events_page) ? '#' : get_the_permalink($events_page->ID));
		if (!empty($event_query) && $event_query->have_posts()) :
    	$markup .= '<a href="' . $events_link . '" class="btn-link">View All Events</a>';
		endif;
    $markup .= '</div>';
    $markup .= '</li>';

    return $markup;
  }
}

//Recirect service.php and servicea.php
function servicephpOverride() {
  global $wp_query;
  $urlParts = explode('?', $_SERVER['REQUEST_URI'], 2);
  if($urlParts[0] == '/service.php'){
    //var_dump($_GET['p']);
    //die();
    $wp_query->is_404 = false;
    wp_redirect( home_url() . '/wp-admin/admin-post.php?action=bolt_user&u=' . urlencode($_GET['u']) . '&p=' . urlencode($_GET['p']));
  }
  if($urlParts[0] == '/servicea.php') {
    wp_redirect( home_url() . '/wp-admin/admin-post.php?action=bolt_agency&id=' . $_GET['id']);
  }
}
add_filter('template_redirect', 'servicephpOverride');



if(!function_exists(('boltGetUser'))){
  function boltGetUser()
  {
   return \WRCInfrastructure\Bolt\BoltHandler::returnUser();
  }
}
if(!function_exists('boltGetAgency')) {
  function boltGetAgency()
  {
    return \WRCInfrastructure\Bolt\BoltHandler::returnAgencies();
  }
}
if(!function_exists('boltGetMutual')) {
  function boltGetMutual()
  {
    return \WRCInfrastructure\Bolt\BoltHandler::returnMutuals();
  }
}


add_action ('admin_post_bolt_user', 'boltGetUser');
add_action ('admin_post_nopriv_bolt_user', 'boltGetUser');
add_action ('admin_post_bolt_agency', 'boltGetAgency');
add_action ('admin_post_nopriv_bolt_agency', 'boltGetAgency');
add_action ('admin_post_bolt_mutual', 'boltGetMutual');
add_action ('admin_post_nopriv_bolt_mutual', 'boltGetMutual');

/**
 * Implement the Custom Header feature.
 */
//require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
//require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
//require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
//require get_template_directory() . '/inc/jetpack.php';


/**
*
* Automatically add site meta to posts and pages as they are saved.
* we do this so that we can query by it when searching.
*
**/
if(!function_exists('add_site_meta_for_search')) {
  function add_site_meta_for_search($post_id)
  {
    $post_type = get_post_type($post_id);
    if($post_type == 'page') {
      if($post_type == 'page') {
        $ancestors = get_ancestors($post_id, 'page');
       //var_dump($ancestors);
        if(!count($ancestors)) return;
        //var_dump($post_id);
        $top_ancestor = $ancestors[count($ancestors) -1 ];

        $site = get_field('site', $top_ancestor);
        //var_dump($site);
        //If the top level has no site - bail
        if(!$site) return;
        //$site = get_post($site);
        //Otherwise set that same site on the page.
        //We need to remove and re-add the action so that it doesn't infinite loop
        remove_action('save_post', 'add_site_meta_for_search');
        update_post_meta($post_id, 'site', $site->ID);
        add_action( 'save_post', 'add_site_meta_for_search');

      }
    }
  }
}

add_action( 'save_post', 'add_site_meta_for_search');

//Now we need to hook into relevanssi making it's query and update the meta query so we
//only get stuff that's on the current site.
if(!function_exists('add_site_query_to_relevanssi_search')){
  function add_site_query_to_relevanssi_search($query)
  {
    if(!isset($query->query_vars['current_site'])) return $query;
    $site = $query->query_vars['current_site'];
    $siteMetaQuery = array(
      
      array(
        'relation' => 'OR',
        array(
          'key' => 'site',
          'value' => $site,
          'compare' => 'LIKE'
        ),
        array(
          'key' => 'attached_site',
          'value' => $site,
          'compare' => 'LIKE'
        )
      )
    );
    $query->set('meta_query', $siteMetaQuery);
    
    return $query;
  }
}

add_filter('relevanssi_modify_wp_query', 'add_site_query_to_relevanssi_search');

add_filter('query_vars', 'rlv_add_qv');
if(!function_exists('rlv_add_qv')){
  function rlv_add_qv($qv) {
      $qv[] = 'current_site';
      return $qv;
    }
  }


/* add unapproved user submenu item and relevant sorting for user.php, user export menu item */
add_action('admin_menu', 'add_pending_user_submenu_item');

if(!function_exists('build_user_export_page')){
  function build_user_export_page(){
    include(get_stylesheet_directory() . '/admin-export_users.php');
  }
}

if(!function_exists('add_pending_user_submenu_item')){
  function add_pending_user_submenu_item(){
    add_submenu_page('users.php', 'Pending Users', 'Pending Users', 'manage_options', 'users.php?pending=1');
  }
}

add_filter( 'pre_get_users', 'filter_pending_users' );

if(!function_exists('filter_pending_users')){
  function filter_pending_users( $query ) {
    global $pagenow;

    if(is_admin() && 'users.php' == $pagenow && !empty($_GET['pending'])){
        $meta_query = array(
            array(
                'key'   => 'approved',
                'value' => 0
            )
        );
        $query->set( 'meta_key', 'approved' );
        $query->set( 'meta_query', $meta_query );
    }
  }
}


/*****************************************
************ User Export Code ************
******************************************/

//Also might be good to organize within WRCInfrastructure or something

add_action('admin_menu', 'add_user_export_submenu_item');

if(!function_exists('add_user_export_submenu_item')){
  function add_user_export_submenu_item(){
    //this function returns the load page hook suffix that we will use to handle the POST to our custom form
    add_submenu_page('users.php', 'Export Users', 'Export Users', 'manage_options', 'export_users', 'build_user_export_page');
  }
}

//Redirect from sites pages to their landing page
function redirect_site_pages() {
  if(is_page_template('page-site-template.php')) {
    $site = get_field('site');
    $siteID = $site->ID;
    //var_dump($siteID);
    $landingPage = get_field('landing_page', $siteID);
    //var_dump($landingPage);
    wp_redirect($landingPage, 302);
    exit;
  }
  else if (is_page_template('page-container-.php')){
    $redirect = get_field('redirect');
    wp_redirect($redirect, 302);
  }
}
add_action('template_redirect', 'redirect_site_pages');

//Search Load More
function load_more_search_results($request){
  $params = $request->get_params();

  $currentSearch = $params["search"];
  $current_site = $params["current_site"];

  if ( is_plugin_active( 'relevanssi/relevanssi.php' ) || is_plugin_active('relevanssi-premium/relevanssi.php') ) {
    $searchQuery = new WP_Query($searchArgs);
        $searchQuery->query_vars['s'] = $currentSearch;
        $searchQuery->query_vars['posts_per_page'] = $params["ppp"];
        $searchQuery->query_vars['paged'] = $params["page"];
        relevanssi_do_query($searchQuery);
        echo "This is loading";
  } else {
    $searchArgs = array(
      's' => $currentSearch,
      'posts_per_page' => $params["ppp"],
      'paged' => $params["page"],
      'post_status' => 'publish',
    );
    $searchQuery = new WP_Query($searchArgs);
  }

  if( $searchQuery->have_posts() ):
  while( $searchQuery->have_posts() ): $searchQuery->the_post();
      include(locate_template('template-parts/content-search.php'));
    endwhile;
  else:
    if ($params["page"] != 1 ) {
      echo "<p class='load-more-no-posts'>No more posts.</p>";
    } else {
      echo "<header class='page-header'>
        <h1 class='page-title'>No Results for: <span>" . $currentSearch . "</span></h1>
      </header>";
      echo "<p class='search-no-results'>Please try another search.</p>";
      get_search_form();
    }

  endif;

die();
}

//add Learning Library to WP Search Query
add_filter( 'pre_get_posts', 'wll_in_search' );
/**
 * This function modifies the main WordPress query to include an array of 
 * post types instead of the default 'post' post type.
 *
 * @param object $query  The original query.
 * @return object $query The amended query.
 */
function wll_in_search( $query ) {
	
    if ( $query->is_search ) {
	    $query->set( 'post_type', array( 'post', 'learning-library' ) );
    }
    
    return $query;
    
}

//Add endpoint for Load More button on Search Results
add_action( 'rest_api_init', 'rest_results_load_endpoint' );
function rest_results_load_endpoint(){
  register_rest_route( 'rest/v1', '/load_more_results/', array(
      'methods'   => 'POST',
      'callback'  => 'load_more_search_results',
      'args'      => array(
          'category'  => array( 'required' => false ),
          'tag'  => array( 'required' => false ),
          'ppp'  => array( 'required' => true, 'default' => get_option('posts_per_page') ),
          'page'  => array( 'required' => true ),
          'search' => array( 'required' => true, 'default' => get_search_query() ),
          'current_site' => array('required' => true),
          'post_type' => array( 'required' => true, 'default' => array( 'post', 'learning-library' ) )
      )
  ) );
}

//must send csv download headers outside of our submenu template
//the add_submenu_page call that creates our menu item determines the name of this load hook
//format: load-(parent_slug)_page_(subpage_slug)
add_action('load-users_page_export_users', 'check_for_user_export_csv');
if(!function_exists('check_for_user_export_csv')){
  function check_for_user_export_csv(){
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_export_users'])) {
      create_user_export_csv();
    }
  }
}

if(!function_exists('create_user_export_csv')){
  function create_user_export_csv(){
    $export_filters = \WRCInfrastructure\Users\UserExport::getExportFilters();

    $cleanSites = empty($_POST['sites']) ? array() : array_map('sanitize_text_field', $_POST['sites']);
    $cleanStates = empty($_POST['states']) ? array() : array_map('sanitize_text_field', $_POST['states']);
    $cleanRoles = empty($_POST['roles']) ? array() : array_map('sanitize_text_field', $_POST['roles']);
    $cleanAgencies = empty($_POST['agencies']) ? array() : array_map('sanitize_text_field', $_POST['agencies']);

    $cleanFilters = array();
    foreach($export_filters as $filter_key => $filter_value){
      if(!empty($_POST[$filter_key])){
        array_push($cleanFilters, sanitize_text_field($_POST[$filter_key]));
      }
    }

    \WRCInfrastructure\Users\UserExport::export($cleanSites, $cleanStates, $cleanRoles, $cleanFilters, $cleanAgencies);
  }
}

/*****************************************
************ End User Export Code ********
******************************************/

// if the user custom field is switched to approved then send email
function wrc_acf_update_value( $value, $post_id, $field  ) {
  $old_value = get_field('approved', $post_id);
  $new_value = $_POST['acf']['field_user_approval'];
  if( $old_value != $new_value && $new_value == 1) {
    $user_data = get_userdata(str_replace("user_", "", $post_id));
    $user_id = $post_id;
      \WRCInfrastructure\Users\UserRegistration::sendUserApprovalEmail($user_id);
  }
  return $value;
}
add_filter('acf/update_value/name=approved', 'wrc_acf_update_value', 10, 3);

//filter to order User Roles alphabetically in the Admin
add_filter( 'editable_roles', 'wrc_sort_editable_roles' );
function wrc_sort_editable_roles ($roles){
  // sort alphabetically (ignores case)
  uasort($roles, function($a, $b){
    return strcasecmp($b["name"], $a["name"]);
  });
  return $roles;
};


//change how user is displayed in acf user dropdown (to show underwriter name only)
function change_user_acf_result( $result, $user, $field, $post_id ) {
  $result = '';
  if( $user->first_name ) {
    $result = $user->first_name;
    if( $user->last_name ) {
        $result .= ' ' . $user->last_name;
    }
  }
  return $result;
}
add_filter( 'acf/fields/user/result', 'change_user_acf_result', 10, 4 );


function tax_search_join( $join )
{
    global $wpdb;
    if( is_search() )
    {
        $join .= "
        INNER JOIN
          {$wpdb->term_relationships} ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id
        INNER JOIN
          {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
        INNER JOIN
          {$wpdb->terms} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
      ";
    }
    return $join;
}
add_filter('posts_join', 'tax_search_join');

function tax_search_where( $where )
{
    global $wpdb;
    if( is_search() )
    {
        // add the search term to the query
        $where .= " OR
    (
      {$wpdb->term_taxonomy}.taxonomy LIKE 'learning_tag'
      AND
      {$wpdb->terms}.name LIKE ('%".$wpdb->escape( get_query_var('s') )."%')
    ) ";
    }
    return $where;
}
add_filter('posts_where', 'tax_search_where');

function tax_search_groupby( $groupby )
{
    global $wpdb;
    if( is_search() )
    {
        $groupby = "{$wpdb->posts}.ID";
    }
    return $groupby;
}
add_filter('posts_groupby', 'tax_search_groupby');

//Disable autocomplete on login
function disable_autocomplete_login_form() {
    echo <<<html
<script>
    document.getElementById( "user_login" ).autocomplete = "off";
    document.getElementById( "user_pass" ).autocomplete = "off";
</script>
html;
}

add_action( 'login_form', 'disable_autocomplete_login_form' );

function include_custom_post_types( $query ) {
    $custom_post_type = get_query_var( 'post_type' );

    if ( is_archive() ) {
        if ( empty( $custom_post_type ) ) $query->set( 'post_type' , get_post_types() );
    }

    if ( is_search() ) {
        if ( empty( $custom_post_type ) ) {
            $query->set( 'post_type' , array(
                'post', 'page', 'learning-library'
                )
            );
        }
    }

    return $query;
}
add_filter( 'pre_get_posts' , 'include_custom_post_types' );



//restrict user access

function v_getUrl() {
  $url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
  $url .= '://' . $_SERVER['SERVER_NAME'];
  $url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
  $url .= $_SERVER['REQUEST_URI'];
  return $url;
}
function v_forcelogin() {
  if( !is_user_logged_in() ) {
    $url = v_getUrl();
    $whitelist = apply_filters('v_forcelogin_whitelist', array());
    $redirect_url = apply_filters('v_forcelogin_redirect', $url);
    if( preg_replace('/\?.*/', '', $url) != preg_replace('/\?.*/', '', wp_login_url()) && !in_array($url, $whitelist) ) {
      wp_safe_redirect( wp_login_url( $redirect_url ), 302 ); exit();
    }
  }
}
add_action('init', 'v_forcelogin');

function disable_autofill_password($safe_text, $text) {
    if($safe_text === 'user_pass') {
        $safe_text .= '" autocomplete="new-password';
    }
    return $safe_text;
}
add_filter('attribute_escape', 'disable_autofill_password', 10, 2);

