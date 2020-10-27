<?php
namespace WRCInfrastructure\CustomPostTypes;

/**
* Agency Custom Post Type
**/
class AgencyCPT
{
  //needs to be called in wordpress init hook
  public static function setupAgencies()
  {
    self::createAgencyPostType();
  }

  public static function createAgencyPostType()
  {
    register_post_type( 'agency',
      array(
        'labels' => array(
          'name' => __( 'Agencies' ),
          'singular_name' => __( 'Agency' )
        ),
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-shield-alt',
      )
    );
  }

}
