<?php
/**
 * Template Name: Password Reset Request
 *
 * This template allows a user to request a password reset.
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

  $errors = false;
  $error_message = null;
  if( isset($_GET['errors'])) {
    $errors = $_GET['errors'];
    switch($errors) {
      case 'empty_username':
        $error_message = 'Please enter an email address.';
        break;
      case 'invalid_combo':
        $error_message = 'We are unable to find that email address. Please try again, or contact technical assistance.';
        break;
      default:
        $error_message = 'We are unable to recover your password. Please contact technical assistance';
    }
  }
?>
</head>
<body <?php body_class(); ?>>
  <div id="page" class="site">
    <div class="login_wrapper">
      <div class="login_left_column">
        <h1 class="login_heading">
          Password Reset Request
        </h1>
        <p>Enter your email and we'll send you a password reset request.</p>
        <?php
          if( isset($errors) && $errors ):
         ?>
         <div class="login-errorbox">
          <p><?php echo $error_message ?></p>
         </div>
        <?php
          //End of error block
          endif;
        ?>
        <form method="post" action="<?php echo wp_lostpassword_url();?>">
          <div class="login_form">
            <div class="input_group">
              <label for="user_login">Email</label>
              <input type="text" name="user_login" id="user_login">
            </div>
            <input type="submit" class="login_submit" value="Submit" />
          </div>
        </form>
      </div>
      <div class="login_right_column">
          <?php  get_template_part('template-parts/content', 'login-rightnav'); ?>
      </div>
    </div>
  </div>

</body>

<?php
get_footer();