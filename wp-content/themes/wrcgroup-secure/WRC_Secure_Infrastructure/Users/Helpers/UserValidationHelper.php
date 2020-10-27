<?php 
namespace WRCInfrastructure\Users\Helpers;

require_once( __DIR__ . '/FormFieldNames.php');

class UserValidationHelper{

  static $validation_messages = array(
    'required' => 'Required fields are not complete.',
    'confirm_email' => 'Email and Confirm Email must match.',
    'confirm_password' => 'Password and Confirm Password must match.',
    'invalid_email' => 'Email address must be valid.',
    'invalid_password' => 'Invalid password.',
    'existing_email' => 'Email addresses is already taken.',
    'password_reuse' => 'The new password must be different from the old password.',
    'password_reuse_history' => 'You can’t reuse the same password twice.'
  );

  //sanitizes form field values by modifying original form fields array
  public static function sanitizeFormFields(&$form_fields){
    //loop through and sanitize all fields
    foreach($form_fields as $field_name => $field_values){
      $value = sanitize_text_field($field_values['field_value']);
      $form_fields[$field_name]['field_value'] = $value;
    }
  }

  //check for form fields with a required flag and push errors to an existing error array
  public static function checkRequiredFields(&$form_fields, &$error_messages){
    $missing_required = false;
    $has_error = false;
    //loop through and add requirement error if
    foreach($form_fields as $field_name => $field_values){
      if(empty($field_values['field_value']) && $field_values['required']){
        $form_fields[$field_name]['error'] = true;
        $has_error = true;
        //make sure we don't duplicate required fields error message
        if(!$missing_required){
          $missing_required = true;
          array_push($error_messages, self::$validation_messages['required']);
        }
      }
    }

    return $has_error;
  }

  public static function checkMatchingEmail(&$form_fields, &$error_messages){
    $email_field = &$form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::EMAIL['NAME']];
    $confirm_email_field = &$form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::CONFIRM_EMAIL['NAME']];

    // Make sure Email Address and Confirm Email Address match
    if ($email_field['field_value'] !== $confirm_email_field['field_value']) {
      $email_field['error'] = true;
      $confirm_email_field['error'] = true;
      array_push($error_messages, self::$validation_messages['confirm_email']);

      return true;
    }

    return false;
  }

  public static function checkMatchingPasswords(&$form_fields, &$error_messages){
    $password_field = &$form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::PASSWORD['NAME']];
    $confirm_password_field = &$form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::CONFIRM_PASSWORD['NAME']];

    // Make sure Email Address and Confirm Email Address match
    if ($password_field['field_value'] !== $confirm_password_field['field_value']) {
      $password_field['error'] = true;
      $confirm_password_field['error'] = true;
      array_push($error_messages, self::$validation_messages['confirm_password']);

      return true;
    }

    return false;
  }

  public static function validateEmail(&$form_fields, &$error_messages){
    $email_field = &$form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::EMAIL['NAME']];

    //Check to see if the given email addresses are valid email addresses
    if (!is_email($email_field['field_value'])) {
      $email_field['error'] = true;
      array_push($error_messages, self::$validation_messages['invalid_email']);

      return true;
    }

    return false;
  }

  public static function validatePassword(&$form_fields, &$error_messages){
    $password_field = &$form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::PASSWORD['NAME']];

    //fancy error messages handled on the client side, this is just incase something gets through
    if(!self::isPasswordValid($password_field['field_value'])){
        $password_field['error'] = true;
        array_push($error_messages, self::$validation_messages['invalid_password']);
        return false;
    }
    return true;
  }

  public static function isPasswordValid($value){
    if(!preg_match('/\d/', $value) || 
      !preg_match('/[a-z]/', $value) || 
      !preg_match('/[A-Z]/', $value) || 
      !preg_match('/\W/', $value) || 
      preg_match('/#/', $value)){
        return false;
      }

    return true;
  }

  public static function checkExistingEmail(&$form_fields, &$error_messages, $current_user_id = NULL){
    $email_field = &$form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::EMAIL['NAME']];
    //Check to see if a user with given email address already exists.
    $email_exists = email_exists($email_field['field_value']);
    if (!empty($email_exists) && $email_exists !== $current_user_id) {
      $email_field['error'] = true;
      array_push($error_messages, self::$validation_messages['existing_email']);

      return true;
    }

    return false;
  }

  public static function checkCurrentPasswordReuse(&$form_fields, &$error_messages, $current_user){
    $password_field = &$form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::PASSWORD['NAME']];
    if(self::isCurrentPasswordReused($password_field['field_value'], $current_user)){
      $password_field['error'] = true;
      $error_message = self::$validation_messages['password_reuse'];
      array_push($error_messages, $error_message);

      return true;
    }
    return false;
  }

  public static function isCurrentPasswordReused($value, $user){
    if (wp_check_password($value, $user->user_pass, $user->ID) !== false){
      return true;
    }
    return false;
  }

  public static function checkPasswordReuseHistory(&$form_fields, &$error_messages, $current_user){
    $password_field = &$form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::PASSWORD['NAME']];
    if(self::isPasswordOld($password_field['field_value'], $current_user)){
      $password_field['error'] = true;
      array_push($error_messages, self::$validation_messages['password_reuse_history']);
      return true;
    }
    return false;
  }

  public static function isPasswordOld($value, $user){
    $old_passwords = get_user_meta($user->ID, 'old_password');
    if(!empty($old_passwords)){
      foreach($old_passwords as $old_password){
        if(wp_check_password($value, $old_password, $user->ID) !== false){
          return true;
        }
      }
    }
    return false;
  }

}