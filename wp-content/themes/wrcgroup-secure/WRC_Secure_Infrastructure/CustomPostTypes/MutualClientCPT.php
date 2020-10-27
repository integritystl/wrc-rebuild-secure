<?php
namespace WRCInfrastructure\CustomPostTypes;

/**
* Mutual Client CPT
**/
class MutualClientCPT
{
  //needs to be called in wordpress init hook
  public static function setupMutualClients()
  {
    self::createMutualClientPostType();
  }

  public static function createMutualClientPostType()
  {
    register_post_type( 'mutual-client',
      array(
        'labels' => array(
          'name' => __( 'Mutual Clients' ),
          'singular_name' => __( 'Mutual Client' )
        ),
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-building',
      )
    );
  }
}