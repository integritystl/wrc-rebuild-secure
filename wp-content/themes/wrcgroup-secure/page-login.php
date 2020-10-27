<?php
/**
 * Template Name: Login Template
 *
 * This template serves as the login page to the site for agents/mutuals/staff etc.
 * @package WRC Secure
 */
//First check and see if we are already logged in. If so, ship us out of here.
if( is_user_logged_in() ) {
  //Well dang, looks like we loggedin. Lets ship this fool to a landing page.
  //first check if we should send to specific landing page
  if(isset($_GET['site'])) {
    //this will redirect if site var matches one of the user's permitted sites
    \WRCInfrastructure\Users\UserLogin::checkLandingPage(sanitize_text_field($_GET['site']));
  }
  $user = wp_get_current_user();
  $sites = get_field('sites', 'user_' . $user->ID);
  $landingPage = get_field('landing_page', $sites[0]->ID);
  /**
  *
  * @todo Handle lack of sites or lack of landing page
  *
  **/
  wp_redirect($landingPage);
}
//Actually handle the login
if(isset($_POST['email']) && isset($_POST['password'])) {

  require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserLogin.php');
  $user = \WRCInfrastructure\Users\UserLogin::doLogin($_POST['email'], $_POST['password']);
  //Check and see if we got an error.
  $errors = false;
  if($user['error']) {
    $errors = true;
  }
}

if(isset($_GET['checkemail']) && $_GET['checkemail'] == 'confirm') {
  $errors = true;
  $user = array(
    'message' => 'A password reset request has been sent to your email'
  );
}

if(isset($_GET['error'])) {
  $errors = true;
  if( $_GET['error'] == 'resetattempt') {
    $user = array(
      'message' => 'Your reset token is expired or invalid. Please request a password reset again.'
    );
  }
}

if(isset($_GET['reset'])) {
  $errors = true;
  $user = array(
    'message' => 'Your password has been reset. Please log back in to fully access the site.'
  );
}

if(isset($_GET['update'])) {
  $errors = true;
  $user = array(
    'message' => 'Your username has been updated. Please log back in to fully access the site.'
  );
}

if(isset($_GET['password'])) {
  if($_GET['password'] == 'changed') {
    $errors = true;
    $user = array(
      'message' => 'Your password has been reset.'
    );
  }
}

//Check if the marketing site passed a site to log into
if(isset($_GET['site'])) {
  $siteLogin = $_GET['site'];
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/wrc-building-favicon.png">
<?php
  wp_head();

?>
</head>
<body <?php body_class(); ?>>
  <div id="page" class="site">
    <div class="login_wrapper">
      <div class="login_left_column">
        <h1 class="login_heading">
          Sign in for Agents and
          Mutual Clients Only
        </h1>
        <div class="login_form">
          <?php
            if( isset($errors) && $errors ):
           ?>
           <div class="login-errorbox">
            <p><?php echo $user['message']; ?></p>
           </div>
          <?php
            //End of error block
            endif;
          ?>
          <form method="post">
            <div class="input_group">
              <label for="email">Email Address</label>
              <input id="user_login" type="text" name="email"/>
            </div>
            <div class="input_group">
              <label for="password">Password</label>
              <input id="user_pass" type="password" name="password"/>
            </div>
            <?php if(isset($siteLogin)): ?>
              <input type="hidden" name="site" value="<?php echo $siteLogin;?>" />
            <?php endif; ?>
            <input type="submit" class="login_submit" value="Login"/>
          </form>
        </div>
        <div class="login_self_service_links">
          <a href="<?php echo wp_lostpassword_url(); ?>">Lost Password?</a>
          <?php $registration_form = get_field('registration_page', 'option');?>
          <a href="<?php echo(empty($registration_form) ? '#' : get_the_permalink($registration_form->ID)); ?>">Register for new account</a>
        </div>
      </div>
      <div class="login_right_column">
        <div class="login_technical_assistance_box">
          <?php  get_template_part('template-parts/content', 'login-rightnav'); ?>
        </div>
      </div>
    </div>
  </div>

  <?php get_footer(); ?>
</body>
