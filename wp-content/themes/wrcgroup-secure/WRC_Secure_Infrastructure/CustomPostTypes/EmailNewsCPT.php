<?php
namespace WRCInfrastructure\CustomPostTypes;

class EmailNewsCPT{

  public static function setupEmailNews(){
    self::createEmailNewsPostType();
    self::createEmailNewsFields();
    self::registerCustomTaxonomy();
  }

  private static function createEmailNewsPostType(){
    register_post_type( 'email_news',
      array(
        'labels' => array(
          'name' => __( 'Email News' ),
          'singular_name' => __( 'Email News' )
        ),
        'show_ui' => true,
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-email-alt',
        'supports' => array('title')
      )
    );
  }

  private static function createEmailNewsFields(){
    if( function_exists('acf_add_local_field_group') ){
      acf_add_local_field_group(array(
        'key' => 'field_group_email_news',
        'title' => 'Email News Options',
        'fields' => array (
          array (
            'key' => 'field_email_news_site',
            'label' => 'Sites',
            'name' => 'site',
            'type' => 'post_object',
            'post_type' => 'site',
            'multiple' => 1
          ),
          array (
            'key' => 'field_email_news_url',
            'label' => 'Email News Link',
            'name' => 'email_news_link',
            'type' => 'url',
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'email_news',
            ),
          ),
        ),
      ));
    }
  }

  public static function registerCustomTaxonomy(){
    $args = array (
      'labels' => array(
        'name' => 'Email News Categories',
        'singular_name' => 'Email News Category',
        'all_items' => 'All Email News Categories',
        'edit_item' => 'Edit Email News Categories',
        'view_item' => 'View Email News Category',
        'update_item' => 'Update Email News Category',
        'add_new_item' => 'Add New Email News Category',
      ),
      'public' => false,
      'show_ui' => true,
      'hierarchical' => true,
      'rewrite' => false,
      'capabilities' => array(
        'manage_terms', 'edit_terms', 'delete_terms', 'assign_terms'
      ),

    );
    register_taxonomy('email_news_category', array('email_news'), $args);
  }
}
