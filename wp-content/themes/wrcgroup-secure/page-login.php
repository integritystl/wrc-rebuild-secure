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
      <div class="login_right_column">
        <div class="login_technical_assistance_box">
          <?php  get_template_part('template-parts/content', 'login-rightnav'); ?>
        </div>
      </div>
    </div>
  </div>

  <?php get_footer(); ?>
</body>
