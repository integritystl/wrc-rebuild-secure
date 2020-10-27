<?php
namespace WRCInfrastructure\Bolt;
/**
* This class represents what used to be service.php servicea.php and servicem.php
* The class returns the relevant XML for these old endpoints.
**/
class BoltHandler
{
  public static function returnBoltLink()
  {
    /**
    *
    * This function handles producing the link that "Single Sign On" into bolt.
    * It grabs the hashed user password and then encrypts it with a pem key in the theme root.
    * NOTE: in order for this to work, you need to use the same Hash key for WP that is used on Prod now
    * otherwise your hashed password will be different and it won't sign you into bolt.
    * Effectively this process uses the hashed password in the WP db like a plaintext password.
    *
    **/
    //If no one is logged in, bail
    if(!is_user_logged_in()) {
      return null;
    }
    //If we have emergency maintenance on, return that link
    if(get_field('emergency_maintenance', 'options')) {
      return get_field('emergency_maintenance_page', 'options');
    }
    //If we are in a maintenance window, return the maintenance link
    $Central = new \DateTimeZone('America/Chicago');
    $currentDate = new \DateTime(null, $Central );
    $dayOfWeek = $currentDate->format('l');
    $formattedTime = $currentDate->format('His');

    if(have_rows(strtolower($dayOfWeek), 'options')): while(have_rows(strtolower($dayOfWeek), 'options')): the_row();

      $start_time = get_sub_field('start_time');

      //If we are past the start time and the maintenance window is active, keep checking
      if(($formattedTime > $start_time ) && get_sub_field('active')) {
        $end_time = get_sub_field('end_time');
        //If we are within the end time, return the maintenance page
        if($end_time > $formattedTime) {
          return get_field('maintenance_page', 'options');
        }
      }
    endwhile;
    endif;
    $user = wp_get_current_user();
    if(!$user) {
      return null;
    }
    $user_pass = ($user->data->user_login) . ',' . ($user->data->user_pass);
    $pub_pem = file_get_contents(get_stylesheet_directory() . '/wrcauth_pub.pem');
    openssl_public_encrypt($user_pass, $enc_data2, $pub_pem);
    $enc64 = base64_encode($enc_data2);
    $xlink = '?t=' . urlencode($enc64);

    //Enter stuff in here to figure out if bolt is in maintenance
    $baselink = get_field('bolt_base_link', 'options');
    if(!$baselink) {
      return null;
    }
    $returnLink = $baselink . $xlink;
    return $returnLink;

  }
  public static function returnUser($is_other_service = false)
  {
   error_reporting(0);
    if(!$is_other_service) {
      header('Content-type: application/xml; charset="utf-8"', true);


    }
    $errors = 0;
     //Set our header and get our variables
    $username = urldecode($_GET['u']);
    $password = urldecode($_GET['p']);

    //Log this to our access JSON so that we can debug bolt.
    //Remove this once bolt works.
    $log = array(
      'action' => 'Get User',
      'username' => $username,
      'password' => $password
    );
    file_put_contents('./boltaccesslog.json', json_encode($log), FILE_APPEND);

    //First lets find the user by username
    $user = get_user_by('login', $username);
    //If we didn't get someone return an error
    if(!$user) {
      $errors++;
    }
    //If we did get someone, check the password.
    //Note that this uses the hashed password, not the actual password, so we need to do a raw query against it.
    global $wpdb;
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_users WHERE user_pass = %s", $password));

    if(!count($results)){
      //If that didn't work, try and get the user by plaintext password
      if(!wp_check_password($password, $user->data->user_pass, $user->ID)){
        $errors++;
      }
    }
    //Get all the user parts we will need
    if($user) {

      $user_meta = get_user_meta($user->ID);
      $permissions = build_user_permissions($user->ID);
      $other_permissions = get_field('permissions', 'user_' . $user->ID);
      if(!$other_permissions) {
        $other_permissions = array();
      }

      //Now get the user role id key - this is purely mapped due to bolt
     
      $roleList = $user->roles;
      foreach($roleList as $roleName){
        $role = $roleName;
      }
      
      $user_type = 1;
      
      switch($role) {
        case 'underwriter':
           $user_type = 3;
           break;
        case 'administrator':
        case 'web_administrator':
           $user_type = 4;
           break;
        case 'mutual':
           $user_type = 5;
           break;
        case 'staff':
           $user_type = 6;
           break;
      }
      //Grab all the agencies for this person
      $agencies = array();
      $agencyMessageComponent = '';
      //Get agencies, underwriters get all of them
      if ( $isUnderwriter = (in_array($user->roles[0], array('administrator', 'underwriter', 'web_administrator') ))) {
        $agencies = array_merge($agencies, get_posts(array('post_type'=>'agency', 'posts_per_page' => -1)));
      }
      else {
        //If not an admin, we find agencies attached to them.
        if($agency = get_field('agency', 'user_' . $user->ID)) {
          
          $agency = get_field('agency', 'user_' . $user->ID);
          $agency = (int)$agency;
          //$agency = get_posts(array('post_type' => 'agency', 'posts_per_page' => 1, 'ID' => $agency));
          $agency = get_post($agency);
          //var_dump($agency);
          $agencies[] = $agency;
        }
        //if a mutual, we find all agencies attached to that mutual
        if($client_company = get_field('mutual-client', 'user_' . $user->ID)) {
          $client_companies = get_field('agencies', $client_company);
          if(count($client_companies)) {
            foreach($client_companies as $c) {
              $curAgency = get_post($c['agency']);
              $agencies[] = $curAgency;
            }
          }
        }
      }

      if(count($agencies)) {
        $agenciesInitials = array();
        foreach( $agencies as $i=>$a ) {
          $agencies[$i]->code = get_field('code', $a->ID);

          if(($underwriter = get_field('underwriter', $a->ID)) && !$isUnderwriter) {
            if(($initials = get_field('underwriter_initials', 'user_'.$underwriter['ID'])) && preg_replace('/[^\w]/', '', $initials)) {
               $newAgency = new \stdClass();
               $newAgency->code = $initials;
               $newAgency->post_title = 'Unknown';
               $agenciesInitials[$newAgency->code] = $newAgency;
            }
          }
        }

        $agencies = array_merge($agenciesInitials, $agencies);
        $agencyMessageComponent = '';
        // if there is more than one

        if(count($agencies) > 1) {
           $agencyMessageComponent .= '<agencies_codes>';
           foreach($agencies as $a) {
              
              $agencyMessageComponent .='<agency_code>'.htmlentities($a->code).'</agency_code>';
              
           }
           $agencyMessageComponent .='</agencies_codes>';

           reset($agencies);

           foreach($agencies as $a) {
              $agencyMessageComponent .= '<agency_name agency_code="'.htmlentities($a->code).'">'.htmlentities($a->post_title).'</agency_name>';
           }
        } else {
           foreach($agencies as $a) {
             
               $agencyMessageComponent .='<agency_code>'.htmlentities($a->code).'</agency_code>';
             
              $agencyMessageComponent .='<agency_name agency_code="'.htmlentities($a->code).'">'.htmlentities($a->post_title).'</agency_name>';
           }
        }
      }
    }

    //Start to output the actual message
    if(!$is_other_service){
      $message =    '<?xml version="1.0" encoding="utf-8"?>';
      $message .=  '<response>';
      $message .=  '<errors>' . $errors . '</errors>';
      if($errors) {
        $message .= '<error_message>Your credentials do not match our records</error_message>';
        $message .= '</response>';
        echo $message;
        return;
      }
    }
    else {
      $message = '';
    }

    $message .=  '<user_type>' . $user_type . '</user_type>';
    foreach(array('first_name', 'last_name', 'address', 'city', 'state', 'zip', 'phone') as $v) {
      if(isset($user_meta[$v])){
        $message .= '<' . $v . '>'.htmlentities($user_meta[$v][0]) . '</' . $v . '>';
      } else {
        $message .= '<' . $v . '>None Set</'. $v . '>';
      }
    }
    $message .= '<email>' . htmlentities($user->user_email) . '</email>';
    $message .= '<states_allowed>';
    if(isset($permissions['states'])){
      for( $i = 0 ; $i < count($permissions['states']); $i++) {
        if(isset($permissions['states'][$i])){
          $message .= $permissions['states'][$i];
          if($i < count($permissions['states']) - 1) {
            $message .= ',';
          }
        }
      }
    }
    $message .= '</states_allowed>';
    $message .= '<user_permission>';
    foreach($permissions['lob'] as $key=>$value) {
      if(is_array($value)){
        $message .= '<LOB name="' . $key . '">';
        for($i = 0; $i < count($value); $i++) {
          if(isset($value[$i])){
            $message .= $value[$i];
            if($i < count($value) -1) {
              $message .= ',';
            }
          }
        }
        $message .= '</LOB>';
      }
    }
    $message .= '</user_permission>';
    foreach($other_permissions as $permission) {
      if($permission == 'is_endorsement') {
        $message .= '<seapass_endorsement>1</seapass_endorsement>';
      }
      else {
        $message .= '<' . $permission . '>1</' . $permission . '>';
      }
    }
    $message .=  $agencyMessageComponent;
    if(!$is_other_service){
      $message .=  '</response>';
      //$message = 'hi';
      echo $message;
    }
    else {
      return $message;
    }

    //var_dump($user);
  }

  public static function returnAgencies()
  {
    global $wpdb;
    header('Content-type: application/xml; charset="utf-8"',true);
    //If we didn't get our required input, bail.
    if(!isset($_GET['id'])){
      $message = '<?xml version="1.0" encoding="utf-8" ?>';
      $message .= "<response>";
      $message .= "<errors>1</errors>";
      $message .= "<error_message>No Agency Found</error_message>";
      $message .= "</response>";
      echo $message;
      return;
    }

    //Log this to our access JSON so that we can debug bolt.
    //Remove this once bolt works.
    $log = array(
      'action' => 'Get Agency',
      'id' => $_GET['id']
    );
    file_put_contents('./boltaccesslog.json', json_encode($log), FILE_APPEND);

    //Grab the agency code and query by it
    $errors = 0;
    $agencyCode = $_GET['id'];
    $agencies = get_posts(array( 'post_type'=> 'agency', 'meta_key' => 'code', 'meta_value' => $agencyCode));
    $agency = $agencies[0];
    if(!$agency) { $errors++;};
    $message = '<?xml version="1.0" encoding="utf-8" ?>';
    $message .= "<response>";
    $message .= "<errors>" . $errors . "</errors>";
    if($errors)
    {
      $message .= "<error_messae>This agency could not be found.</errors>";
    }
    else
    {
      $message .= "<code>" . $agencyCode . "</code>";
      $message .= "<title>" . $agency->post_title . "</title>";
      $message .= "<address_1>".get_field('address', $agency->ID) . '</address_1>';
      $message .= '<city>'.get_field('city', $agency->ID).'</city>';
      $message .= '<state>'.get_field('state', $agency->ID).'</state>';
      $message .= '<zip>'.get_field('zip', $agency->ID).'</zip>';
      $message .= '<phone>'.get_field('phone', $agency->ID).'</phone>';
      $message .= '<website>'.get_field('website', $agency->ID).'</website>';

      //Get users attached to this agency
      $users = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'users u INNER JOIN '.$wpdb->prefix.'usermeta um ON u.ID = um.user_id WHERE um.meta_key = \'agency\' AND um.meta_value = \''.$agency->ID.'\'');
      if(count($users)) {
          $message .= '<users>';
          foreach($users as $u) {
             $message .= '<user>';
             $_GET['u'] = $u->user_login;
             $_GET['p'] = $u->user_pass;
             $message .= \WRCInfrastructure\Bolt\BoltHandler::returnUser(true);
             $message .= '</user>';
          }
          $message .= '</users>';
       }

    }
    $message .= "</response>";
    echo $message;
    return;
  }

  public static function returnMutuals()
  {
    if(isset($_GET['agency_code']))
    {
      global $wpdb;
      header('Content-type: application/xml; charset="utf-8"',true);
      $mutualCode = urldecode($_GET['agency_code']);
      $errors = 0;
      $message = "";

      $mutual = get_posts(array('posts_per_page' => 1, 'post_type' => 'mutual-client', 'meta_key' => 'code', 'meta_value' => $mutualCode));
      $mutual = $mutual[0];
      if(!$mutual){$errors++;};
      $message .= '<?xml version="1.0" encoding="utf-8" ?>';
      $message .= '<response>';
      if($errors)
      {
        $message .= '<errors>'.(int)$errors.'</errors>';
        $message .= '<error_message>This agency could not be found.</error_message>';
      }
      else
      {
        $users = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'users u INNER JOIN '.$wpdb->prefix.'usermeta um ON u.ID = um.user_id WHERE um.meta_key = \'mutual-client\' AND um.meta_value = \''.$mutual->ID.'\'');
         if(count($users)) {
            $message .= '<errors>'.(int)$errors.'</errors>';
            $message .= '<mutuals>';
            foreach($users as $u) {
               $message .= '<mutual>';
               $_GET['u'] = $u->user_login;
               $_GET['p'] = $u->user_pass;
               $message .= \WRCInfrastructure\Bolt\BoltHandler::returnUser(true);
               $message .= '</mutual>';
            }
            $message .= '</mutuals>';
         } else {
            $errors++;
            $message .= '<errors>'.(int)$errors.'</errors>';
            $message .= '<error_message>Couldn\'t find any users.</error_message>';
         }
      }
      $message .= '</response>';
      echo $message;
      return;
  }
}
}
