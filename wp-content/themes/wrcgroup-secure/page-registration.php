<?php
/**
 * Template Name: User Registration
 *
 * This template allows a user to register for an account.
 * @package WRC Secure
 */

 //First check and see if we are already logged in. If so, ship us out of here.
if( is_user_logged_in() ) {
  //Well dang, looks like we loggedin. Lets ship this fool to a landing page.
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

require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/Helpers/FormFieldNames.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/Helpers/UserFormHelper.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/Helpers/UserValidationHelper.php');

$form_fields = \WRCInfrastructure\Users\Helpers\UserFormHelper::generateFormFields(true);

//Actually handle the login
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registration_submit'])) {

  //grab all submitted values from form
  foreach($form_fields as $field_name => $field_values){
    if(!empty($_POST[$field_name])){
      $form_fields[$field_name]['field_value'] = $_POST[$field_name];
    }
  }

  require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserRegistration.php');
  $user = \WRCInfrastructure\Users\UserRegistration::registerUser($form_fields);
  //Check and see if we got an error.
  $errors = false;
  if($user['error']) {
    $errors = true;
    $form_fields = $user['fields'];
  }
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

<div class="registration_wrapper">
    <div class="registration_left_column">
      <h1 class="registration_heading">
        <?php if(!empty($user) && empty($errors)) :?>
          New Agent Account Created
        <?php else : ?>
          Create Accounts For New Agents Only
        <?php endif; ?>
      </h1>
      <p><?php echo get_field('form_subhead', get_the_ID()); ?></p>
      <?php if(!empty($user) && empty($errors)) :?>
        <div class="success_message">
          <p>Thank you for creating an account. Once your account is approved, you will receive an email from WRC with instructions for logging in.</p>
          <a href="<?php echo site_url(); ?>">Back to Login</a>
        </div>
        <?php else : ?>
          <?php if( isset($errors) && $errors ): ?>
            <div class="login-errorbox">
              <?php foreach($user['messages'] as $message) : ?>
                <p><?php echo $message; ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <form method="post" action="">
            <div class="registration_form">
            <div class="form_row">
                <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::FIRST_NAME; ?>
                <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field); ?>

                <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::LAST_NAME; ?>
                <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field); ?>
            </div>
            <div class="form_row">
                <?php
                    $AgenciesQuery = array (
                      'post_type' => 'agency',
                      'posts_per_page' => -1,
                      'orderby' => 'post_title',
                      'order' => 'asc'
                    );
                    //GET ALL THE AGENCIES
                    $agencies = new WP_Query($AgenciesQuery);
                    $agencyData = array();
                    //SORT THEM INTO AN ARRAY WITH THE DATA WE NEED
                    if ( $agencies->have_posts() ) {
                      while ( $agencies->have_posts() ) {
                        $agencies->the_post();
                        $id = get_the_ID();
                        $code = get_field('code');
                        $name = get_the_title();
                        $agencyData[$id] = "[" . $code . "] " . $name;
                      }
                    }
                  ?>
                <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::AGENCY; ?>
                <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createSelectInputGroup($form_fields, $field, $agencyData); ?>
            </div>
            <div class="form_row">
              <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::EMAIL; ?>
              <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field); ?>
            </div>
            <div class="form_row">
              <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::CONFIRM_EMAIL; ?>
              <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field); ?>
            </div>
            <div class="form_row">
              <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::ADDRESS; ?>
              <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field); ?>
            </div>
            <div class="form_row">
              <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::ADDRESS_2; ?>
              <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field); ?>
            </div>
            <div class="form_row">
                <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::CITY; ?>
                <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field); ?>

                <?php $select_values = get_all_states(); ?>
                <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::STATE; ?>
                <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createSelectInputGroup($form_fields, $field, $select_values); ?>

                <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::ZIP; ?>
                <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field); ?>
            </div>
            <div class="form_row">
              <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::PHONE; ?>
              <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field); ?>
            </div>
            <div class="form_row">
              <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::PASSWORD; ?>
              <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field, true, true); ?>
            </div>
            <div class="form_row">
              <?php $field = \WRCInfrastructure\Users\Helpers\FormFieldNames::CONFIRM_PASSWORD; ?>
              <?php \WRCInfrastructure\Users\Helpers\UserFormHelper::createTextInputGroup($form_fields, $field, true); ?>
            </div>
            <div class="form_row">
              <span class="terms_and_conditions_label">Terms and Conditions</span>
              <a id="print_terms_and_conditions" href="#">Print Terms &amp; Conditions</a>
              <div id="terms_and_conditions_container" class="terms_and_conditions_container">
                <?php the_field('terms_and_conditions', 'option'); ?>
              </div>
            </div>
            <div class="form_row">
              <div class="agreement_notification_container">
                <h4>Agreement</h4>
                <label for="terms_agree" class="terms-agreement required-label"><input type="checkbox" name="terms_agree" class="required-input" required/> <?php the_field('agreement', 'option'); ?> <span>*</span></label>
              </div>
            </div>
              <input type="submit" name="registration_submit" class="registration_submit login_submit" value="Submit" />
            </div>
          </form>
      <?php endif; ?>
    </div>
    <div class="registration_right_column">
      <div class="registration_technical_assistance_box">
          <?php get_template_part('template-parts/content', 'login-rightnav'); ?>
      </div>
    </div>
  </div>
</body>

<?php
get_footer();