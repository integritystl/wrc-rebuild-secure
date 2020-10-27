<?php
/**
 * Template Name: User Account
 *
 * This template allows a user to update their account.
 * @package WRC Secure
 */
 require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/Helpers/FormFieldNames.php');
 require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/Helpers/UserFormHelper.php');
 require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/Helpers/UserValidationHelper.php');

$current_user = wp_get_current_user();

$agency_required = false;
if(in_array('agent', $current_user->roles) || in_array('mutual', $current_user->roles)){
  $agency_required = true;
}

$form_fields = \WRCInfrastructure\Users\Helpers\UserFormHelper::generateFormFields(false);

//move out to separate function
$form_fields['first_name']['field_value'] = $current_user->user_firstname;
$form_fields['last_name']['field_value'] = $current_user->user_lastname;
$form_fields['email']['field_value'] = $current_user->user_email;
$form_fields['confirm_email']['field_value'] = $current_user->user_email;

$full_address = get_field('address', 'user_' . $current_user->ID);
if(!empty($full_address)){
  $address_fields = explode(";", $full_address);
  $form_fields['address']['field_value'] = $address_fields[0];
  if(count($address_fields) > 1){
    $form_fields['address_2']['field_value'] = $address_fields[1];
  }
}

$agency_id = get_field('agency', 'user_' . $current_user->ID);
$agency_post = get_post($agency_id);
if(!empty($agency_post)){
  $form_fields['agency']['field_value'] = '[' . get_field('code', $agency_post->ID) . '] ' . $agency_post->post_title;
}

$city = get_field('city', 'user_' . $current_user->ID);
if(!empty($city)){
  $form_fields['city']['field_value'] = $city;
}

$state = get_field('state', 'user_' . $current_user->ID);
if(!empty($state)){
  $form_fields['state']['field_value'] = $state;
}

$zip = get_field('zip', 'user_' . $current_user->ID);
if(!empty($zip)){
  $form_fields['zip']['field_value'] = $zip;
}

$phone = get_field('phone', 'user_' . $current_user->ID);
if(!empty($phone)){
  $form_fields['phone']['field_value'] = $phone;
}

if(isset($_GET['expired'])) {
  $errors = true;
  $user = array(
    'messages' => array('Please reset your password. Upon completion, you will be redirected to the login page to sign back in to complete the reset process.')
  );
}

//Actually handle the login
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['my_account_submit'])) {

  //grab all submitted values from form
  foreach($form_fields as $field_name => $field_values){
    if(!empty($_POST[$field_name]) && $_POST[$field_name] !== $form_fields[$field_name]['field_value']){
      $form_fields[$field_name]['field_value'] = $_POST[$field_name];
      $form_fields[$field_name]['updated'] = true;
    }
  }

  require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserAccount.php');
  $user = \WRCInfrastructure\Users\UserAccount::updateUser($form_fields, $current_user);
  //Check and see if we got an error.
  $errors = false;
  if($user['error']) {
    $errors = true;
  }
}

  get_header();
?>

<div class="registration_wrapper">
    <div class="registration_left_column">
      <h1 class="registration_heading">
        My Account
      </h1>
      <p>Edit Account for Agents and Mutual Clients Only</p>
      <p><?php echo get_field('form_subhead', get_the_ID()); ?></p>
      <?php if(!empty($user) && empty($errors)) :?>
        <div class="success_message">
          <p>Your account has been updated</p>
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
          <?php if($agency_required) : ?>
            <div class="form_row">
                <?php $agency_value = empty($form_fields['agency']['field_value']) ? '' : $form_fields['agency']['field_value'] ;?>
                <div class="input_group <?= $form_fields['agency']['error'] ? 'has-error' : '' ?>">
                  <label class="required-label" for="agency">Agency</label>
                  <span><?php echo $agency_value; ?></span>
                </div>
            </div>
          <?php endif; ?>
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
            <input type="submit" name="my_account_submit" class="my_account_submit login_submit" value="Save"/>
          </div>
        </form>
        <?php endif; ?>
      </div>
    <div class="registration_right_column">
      <div class="registration_technical_assistance_box">
          <?php  get_template_part('template-parts/content', 'login-rightnav'); ?>
      </div>
    </div>
  </div>

<?php
get_footer();