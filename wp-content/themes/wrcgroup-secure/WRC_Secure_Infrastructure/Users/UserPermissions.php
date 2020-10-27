<?php
namespace WRCInfrastructure\Users;

class UserPermissions 
{
  /**
  * This builds the user permissions array.
  * The static keyword here means that permissions will only be defined once,
  * this allows us to call this as many times as we need in the lifecycle of a request
  * without crushing performance.
  **/
  public static function buildPermissionsArray($id = null)
  {
    static $permissions = NULL;
    if (empty($permissions) ) {
      if(!$id) {
        $userID = \get_current_user_id();
      }
      else {
        $userID = $id;
      }
      //Try to get the transient. If it doesn't exist build a permissions array and save it
      $permissions = get_transient('user_permissions_' . $userID);
      if( !$permissions) {
        $states = \get_field('states', 'user_' . $userID);
        $sites = \get_field('sites', 'user_' . $userID);
        $ca = \get_field('ca', 'user_' . $userID);
        $ft = \get_field('ft', 'user_' . $userID);
        $li = \get_field('li', 'user_' . $userID);
        $pa = \get_field('pa', 'user_' . $userID);
        $um = \get_field('um', 'user_' . $userID);

        $linesOfBusiness = array(
          'CA' => $ca, 
          'FT' => $ft,
          'Liability' => $li,
          'PA' => $pa,
          'UM' => $um
          );

        $permissions = array (
          'states' => $states,
          'sites' => $sites,
          'lob' => $linesOfBusiness
        );
        //Set the permissions into a transient with 24 hours delay
        \set_transient('user_permissions_' . $userID, $permissions, 60 * 60 * 24);
      }
    }
    return $permissions;
  }
}