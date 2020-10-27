<?php
namespace WRCInfrastructure\Users;

/**
 * User Role management
 */
class UserSetup
{

  public static function setupUsers()
  {
    self::removeDefaultRoles();
    self::createRoleAgent();
    self::createRoleWebAdmin();
    self::createRoleMutual();
    self::createRoleUnderwriter();
    self::createRoleStaff();
    self::createRoleMutualManager();
  }

  private static function removeDefaultRoles()
  {
    remove_role('subscriber');
    remove_role('author');
    remove_role('contributor');
    remove_role('editor');

  }

  private static function createRoleAgent()
  {
    $capabilities = array(

    );
    add_role('agent', 'Agent', $capabilities);
  }
  private static function createRoleMutual()
  {
    $capabilities = array(

    );
    add_role('mutual', 'Mutual', $capabilities);
  }

  private static function createRoleUnderwriter()
  {
    $capabilities = array(

    );
    add_role('underwriter', 'Underwriter', $capabilities);
  }

  private static function createRoleWebAdmin()
  {
    //remove_role('web_administrator');
    $capabilities = array(
      'delete_others_pages' => true,
      'delete_others_posts' => true,
      'delete_pages' => true,
      'delete_posts' => true,
      'delete_private_pages' => true,
      'delete_private_posts' => true,
      'delete_published_pages' => true,
      'delete_published_posts' => true,
      'edit_others_pages' => true,
      'edit_others_posts' => true,
      'edit_pages' => true,
      'edit_posts' => true,
      'edit_private_pages' => true,
      'edit_private_posts' => true,
      'edit_published_pages' => true,
      'edit_published_posts' => true,
      'export' => false,
      'import' => false,
      'list_users' => true,
      'manage_categories' => true,
      'manage_links' => true,
      'moderate_comments' => true,
      'promote_users' => true,
      'publish_pages' => true,
      'publish_posts' => true,
      'read_private_pages' => true,
      'read_private_posts' => true,
      'read' => true,
      'remove_users' => true,
      'upload_files' => true,
      'edit_files' => true,
      'edit_users' => true,
      'create_users' => true,
      'delete_users' => true,
      'unfiltered_html' => true,
    );
    add_role('web_administrator', 'Web Administrator', $capabilities);
  }

  private static function createRoleStaff(){
    $capabilities = array(

    );
    add_role('staff', 'Staff', $capabilities);
  }

  private static function createRoleMutualManager(){
    $capabilities = array(

    );
    add_role('mutual_manager', 'Mutual Manager', $capabilities);
  }

}
