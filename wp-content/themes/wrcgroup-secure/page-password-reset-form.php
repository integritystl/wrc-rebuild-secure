<?php
/**
 * Template Name: Password Reset Form
 *
 * This template allows a user to reset their password
 * @package WRC Secure
 */
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
  require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/Helpers/UserFormHelper.php');
  $login_url = get_field('login_page', 'options');
  $login_url = $login_url->ID;
  $login_url = get_permalink($login_url);

  if( isset( $_REQUEST['login']) && isset($_REQUEST['key'])) {
    $attributes['login'] = $_REQUEST['login'];
    $attributes['key'] = $_REQUEST['key'];
    // Error messages
    $errors = array();
    if ( isset( $_REQUEST['error'] ) ) {
        $error_codes = explode( ',', $_REQUEST['error'] );

        foreach ( $error_codes as $code ) {
            switch($code) {
              case 'password_reset_empty':
                $message = "Please fill out the form.";
                break;
              case 'password_invalid':
                $message = "Invalid password.";
                break;
              case 'password_reuse':
                $message = "The new password must be different from the old password.";
                break; 
              case 'password_old':
                $message = "You can’t reuse the same password twice.";
                break; 
              default:
                $message = "Passwords do not match";
            }
            $errors []= $message;
        }
    }
    $attributes['errors'] = $errors;
  }
?>
</head>
<body <?php body_class(); ?>>
  <div id="page" class="site">
    <div class="login_wrapper">
      <div class="login_left_column">
        <h1 class="login_heading">
          Password Reset
        </h1>
        <p>Please enter a new password</p>
        <?php if ( isset($attributes['errors']) && count( $attributes['errors'] ) > 0 ) : ?>
         <div class="login-errorbox">

              <?php foreach ( $attributes['errors'] as $error ) : ?>
                  <p>
                      <?php echo $error; ?>
                  </p>
              <?php endforeach; ?>

         </div>
        <?php endif; ?>
        <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::buildPasswordRequirements(); ?>
        <form method="post" action="<?php echo site_url( 'wp-login.php?action=resetpass' ); ?>">
          <input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $attributes['login'] ); ?>" autocomplete="off" />
          <input type="hidden" name="rp_key" value="<?php echo esc_attr( $attributes['key'] ); ?>" />


          <div class="login_form">
            <div class="input_group">
              <label for="pass1">New Password</label>
              <input type="password" name="pass1" id="pass1">
            </div>
            <div class="input_group">
              <label for="pass2">Confirm Password</label>
              <input type="password" name="pass2" id="pass2">
            </div>
            <input type="submit" class="login_submit" value="Save"/>
          </div>
        </form>
      </div>
      <div class="login_right_column">
          <?php get_template_part('template-parts/content', 'login-rightnav'); ?>
      </div>
    </div>
  </div>


</body>

<?php
get_footer();