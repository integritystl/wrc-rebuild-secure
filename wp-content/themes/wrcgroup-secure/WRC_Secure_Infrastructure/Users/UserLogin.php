<?php
namespace WRCInfrastructure\Users;
/**
*
* This class handles the front-end login form for users.
**/
class UserLogin
{

  public static function doLogin($email, $password)
  {
    //Sanitize the inputs
    $email = sanitize_text_field($email);
    $password = sanitize_text_field($password);

    //See if a user with this email exists
    if( get_user_by('login', $email) ) {
      $loggingUser = get_user_by('login', $email);
    }
    else if( get_user_by('email', $email)) {
      $loggingUser = get_user_by('email', $email);
    }
    else {
      //User with this email does not exist. Throw an error
      $error = array (
        'error' => true,
        'message' => "Your credentials are incorrect. Please try again."
      );
      // RETURN THE ERROR AND BAIL
      return $error;
    }
    //Check if the user is approved
    $approved = get_field('approved', 'user_' . $loggingUser->ID);
    if(!$approved) {
      //User isn't approved, make an error and bail.
      $error = array (
        'error' => true,
        'message' => "Your account has not yet been approved."
      );
      return $error;
    //}


    //
    // if($login_attempt_count >= $login_attempt_max ){
    //   //current site automatically sends reset message when attempt limit is reached
    //   self::sendPasswordResetMessage($loggingUser);

    //   //Too many login attempts
    //   $error = array (
    //     'error' => true,
    //     'message' => "Your account has been locked due to too many failed login attempts. Please contact WRC support for assistance"
    //   );
    //   return $error;
    }

    //Ok, we exist. We are approved. Lets try and log this b in.
    $signonAttempt = wp_signon($credentials = array('user_login' => $loggingUser->user_login, 'user_password' => $password), is_ssl());


    $login_attempt_count = intval(get_field('login_attempt_count', 'user_' . $loggingUser->ID)) + 1;
    $login_attempt_max = intval(get_field('login_attempt_max', 'option'));

    //Lets see if that worked
    if( is_wp_error($signonAttempt) ) {
      //Ok we are a failure. Kick this kid an error message
      //warn user if there is only one attempt left
      if($login_attempt_count === $login_attempt_max - 1){
        $error = array (
          'error' => true,
          'message' => 'Please click "Lost your Password" below. You have one attempt remaining before you will be locked out of your account.'
        );
      }elseif($login_attempt_count >= $login_attempt_max){
        self::sendLoginLockedEmail($loggingUser->ID);
        $error = array (
            'error' => true,
            'message' => "Your account has been locked due to too many failed login attempts. Please contact WRC support for assistance: <a href='mailto:webadmin@thewrcgroup.com'>webadmin@thewrcgroup.com</a>."
          );
      }else{
        $error = array (
          'error' => true,
          'message' => "Your credentials are incorrect. Please try again. Login# " . $login_attempt_count
        );
      }

      //update login attempt count
      update_field('login_attempt_count', $login_attempt_count, 'user_' . $loggingUser->ID);

      return $error;
    }

    // //Well dang, looks like we loggedin. Lets ship this fool to a landing page.
    // //update login attempt count first, though
    // update_field('login_attempt_count', 0, 'user_' . $loggingUser->ID);

    // $sites = get_field('sites', 'user_' . $signonAttempt->ID);
    // if(isset($_POST['site'])) {
    //   $siteSlug = $_POST['site'];
    //   $args = array (
    //     'name' => $siteSlug,
    //     'post_type' => 'site'
    //   );
    //   $query = new \WP_Query($args);
    //   $matchedSiteID = null;
    //   if($query->have_posts()): while($query->have_posts()): $query->the_post();

    //     $matchedSiteID = get_the_ID();
    //   endwhile; endif;
    //   $inPerms = false;
    //   foreach($sites as $site) {
    //     if($site->ID == $matchedSiteID) {
    //       $inPerms = true;
    //     }
    //   }
    //   if(isset($matchedSiteID) && $inPerms) {
    //     $landingPage = get_field('landing_page', $matchedSiteID);
    //   }
    // }
    // if(! isset($landingPage)){
    //   $landingPage = get_field('landing_page', $sites[0]->ID);
    // }
    // /**
    // *
    // * @todo Handle lack of sites or lack of landing page
    // *
    // **/
    // wp_redirect($landingPage);
  }

  // public static function checkLandingPage($siteSlug) {
  //   $landingPage = null;
  //   $user = wp_get_current_user();
  //   $sites = get_field('sites', 'user_' . $user->ID);
  //   if(!empty($sites)){
  //     $args = array (
  //       'name' => $siteSlug,
  //       'post_type' => 'site'
  //     );
  //     $query = new \WP_Query($args);
  //     //var_dump($query->posts); die;
  //     if($query->have_posts()){
  //       $matchedSiteID = $query->posts[0]->ID;
  //       foreach($sites as $site) {
  //         if($site->ID == $matchedSiteID) {
  //           $landingPage = get_field('landing_page', $matchedSiteID);
  //         }
  //       }
  //     }
  //   }

  //   if(empty($landingPage)){
  //     $landingPage = get_field('landing_page', $sites[0]->ID);
  //   }

  //   wp_redirect($landingPage);
  //   die;
  // }

  public static function checkAuthentication() {
    if( ! is_user_logged_in() && ! wp_doing_ajax() ) {
      $login_page = get_field('login_page', 'options');
      $password_reset_request_page = get_field('reset_request_page', 'options');
      $password_reset_form = get_field('password_reset_page', 'options');
      $registration_page = get_field('registration_page', 'options');

      if(! is_page($login_page->ID) && ! is_page($password_reset_request_page->ID) && ! is_page($password_reset_form->ID) && ! is_page($registration_page->ID)) {
        wp_redirect(get_the_permalink($login_page->ID));
        die;
      }
    }
  }

  //not working
  public static function sendPasswordResetMessage($user){
    $password_reset_page = get_field('password_reset_page', 'options');

    if(!empty($password_reset_page)){
      $key = self::generatePasswordResetKey($user);

      $redirect_url = $password_reset_page->guid;
      $redirect_url = add_query_arg( 'login', esc_attr( $user->user_login ), $redirect_url );
      $redirect_url = add_query_arg( 'key', esc_attr( $key ), $redirect_url );

      $message = 'Automated message from ' . get_field('public_url', 'option') . "\r\n";
      $message .= date('n-j-Y') . "\r\n\r\n";
      $message .= 'Someone has requested that your password be reset for the following website:' . "\r\n\r\n";
      $message .= get_field('public_url', 'option') . "\r\n\r\n";
      $message .= 'If this was a mistake, please ignore this email and nothing will happen.' . "\r\n\r\n";
      $message .= 'To reset your password, visit the following address:' . "\r\n\r\n";
      $message .= $redirect_url . "\r\n";

      return wp_mail($user->user_email, 'Password Reset Request', $message);
      //return wp_mail($to, $subject, $message, $headers);
    }
  }

  public static function sendLoginLockedEmail($user_id){
    $user_data = get_userdata(str_replace("user_", "", $user_id));
    $user_email = $user_data->user_email;
    $public_url = get_field('public_url', 'option');
    $redirect_url = get_field('login_page', 'option');
    $redirect_url = $redirect_url->guid;

    $message = 'Automated message from ' . $public_url . "\r\n\r\n";
    //$message .= date('n-j-Y') . "\r\n\r\n";
    $message .= 'Your account has been locked due to too many failed login attempts. Please contact WRC support for assistance: webadmin@thewrcgroup.com.' . "\r\n\r\n";
    $message .= $public_url . "\r\n\r\n";
    $message .= 'To login visit:' . "\r\n\r\n";
    $message .= $redirect_url . "\r\n\r\n";

    $to = $user_email;
    $subject = 'Your Account is Locked';
    $headers[] = 'From: WRC Support <webadmin@thewrcgroup.com>';

    return wp_mail($to, $subject, $message, $headers);
  }

  public static function sendLoginAttemptCountResetMessage($user){
    $public_url = get_field('public_url', 'option');
    $redirect_url = get_field('login_page', 'option');
    $redirect_url = $redirect_url->guid;

    $message = 'Automated message from ' . $public_url . "\r\n\r\n";
    //$message .= date('n-j-Y') . "\r\n\r\n";
    $message .= 'An administrator has reset your login attempt count for the following website:' . "\r\n\r\n";
    $message .= $public_url . "\r\n\r\n";
    $message .= 'To login visit:' . "\r\n\r\n";
    $message .= $redirect_url . "\r\n\r\n";

    $to = $user->user_email;
    $subject = 'Your Account is Unlocked';
    $headers[] = 'From: WRC Support <webadmin@thewrcgroup.com>';

    return wp_mail($to, $subject, $message, $headers);
  }

  private static function generatePasswordResetKey($user){
    global $wpdb, $wp_hasher;

    //taken from wp_login.php
    $key = wp_generate_password( 20, false );
    do_action( 'retrieve_password_key', $user->user_login, $key );

    //need to hash before insert since check_password_reset_key() checks against hashed key
    $hashed = wp_hash_password( $key );
    $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

    return $key;
  }
}
