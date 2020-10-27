<?php
namespace WRCInfrastructure\CustomPostTypes;

class SiteCPT{

  public static function setupSites(){
    self::createSitePostType();
    self::createSiteFields();
  }

  private static function createSitePostType(){
    register_post_type( 'site',
      array(
        'labels' => array(
          'name' => __( 'Sites' ),
          'singular_name' => __( 'Site' )
        ),
        'show_ui' => true,
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-admin-site',
        'hierarchical' => true,
        'supports' => array('page-attributes', 'title')
      )
    );
  }

  private static function createSiteFields(){
    if( function_exists('acf_add_local_field_group') ){
      acf_add_local_field_group(array(
        'key' => 'group_sites',
        'title' => 'Site Options',
        'fields' => array (
          array (
            'key' => 'field_site_logo',
            'label' => 'Site Logo',
            'name' => 'site_logo',
            'type' => 'image',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_site_favicon',
            'label' => 'Site Favicon',
            'name' => 'site_favicon',
            'type' => 'image',
            'instructions' => 'This image is used by browsers to add an icon onto the window tab.',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_site_top_menu',
            'label' => 'Top Menu',
            'name' => 'top_menu',
            'type' => 'nav_menu',
          ),
          array (
            'key' => 'field_site_landing_page',
            'label' => 'Landing Page',
            'name' => 'landing_page',
            'post_type' => 'page',
            'allow_archives' => false,
            'type' => 'page_link',
            'wrapper' => array (
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'key' => 'field_site_help_page',
            'label' => 'Help Page',
            'name' => 'help_page',
            'post_type' => 'page',
            'allow_archives' => false,
            'type' => 'page_link',
            'allow_null' => 1,
            'wrapper' => array (
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'key' => 'field_site_events_page',
            'label' => 'Events Page',
            'name' => 'events_page',
            'post_type' => 'page',
            'allow_archives' => false,
            'allow_null' => 1, // WASI doesn't have events
            'type' => 'post_object',
            'wrapper' => array (
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'key' => 'field_site_library_page',
            'label' => 'Learning Library Page',
            'name' => 'library_page',
            'post_type' => 'page',
            'allow_archives' => false,
            'allow_null' => 1,
            'type' => 'post_object',
            'wrapper' => array (
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'key' => 'field_site_favorites_page',
            'label' => 'Learning Favorites Page',
            'name' => 'favorites_page',
            'post_type' => 'page',
            'allow_archives' => false,
            'allow_null' => 1,
            'type' => 'post_object',
            'wrapper' => array (
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'key' => 'field_site_news_page',
            'label' => 'News Page',
            'name' => 'news_page',
            'post_type' => 'page',
            'allow_archives' => false,
            'allow_null' => 1,
            'type' => 'post_object',
            'wrapper' => array (
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'key'=> 'field_options_email_news_page',
            'label' => 'Email News Page',
            'name' => 'email_news_page',
            'type' => 'post_object',
            'post_type' => 'page',
            'return_format' => 'object'
          ),
          array (
            'key' => 'field_site_footer_menu',
            'label' => 'Footer Menu',
            'name' => 'footer_menu',
            'type' => 'nav_menu',
          ),
          array (
            'key' => 'field_display_doc_states',
            'label' => 'Hide Document States',
            'name' => 'hide_doc_states',
            'type' => 'true_false',
            'instructions' => 'When selected this will hide the states column from document listings',
            'default_value' => 0,
          ),
          array (
            'key' => 'field_footer_contact_phone',
            'label' => 'Contact Phone',
            'name' => 'contact_phone',
            'type' => 'text',
            'wrapper' => array (
              'width' => '50',
              'id' => 'custom-phone-input',
            ),
          ),
          array (
            'key' => 'field_footer_contact_fax',
            'label' => 'Fax',
            'name' => 'contact_fax',
            'type' => 'text',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_footer_contact_email',
            'label' => 'Contact Email',
            'name' => 'contact_email',
            'type' => 'email',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_footer_suggestion_email',
            'label' => 'Suggestions Email',
            'name' => 'suggestion_email',
            'type' => 'email',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'site',
            ),
          ),
        ),
      ));
    }
  }
}
