<?php
namespace WRCInfrastructure\Users;
/**
* This class is in charge of the User Registration form functionality and adding new users to the DB
**/

require_once('Helpers/FormFieldNames.php');
require_once('Helpers/UserValidationHelper.php');

class UserRegistration {

  //Keep in mind $form_fields is passed by reference and modified by the validation functions so error flags can be set
  public static function registerUser(&$form_fields){
    $error_messages = array();

    $first_name_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::FIRST_NAME['NAME']];
    $last_name_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::LAST_NAME['NAME']];
    $email_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::EMAIL['NAME']];
    $confirm_email_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::CONFIRM_EMAIL['NAME']];
    $password_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::PASSWORD['NAME']];
    $confirm_password_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::CONFIRM_PASSWORD['NAME']];
    $address_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::ADDRESS['NAME']];
    $address_2_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::ADDRESS_2['NAME']];
    $city_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::CITY['NAME']];
    $state_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::STATE['NAME']];
    $zip_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::ZIP['NAME']];
    $phone_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::PHONE['NAME']];
    $agency_field = $form_fields[\WRCInfrastructure\Users\Helpers\FormFieldNames::AGENCY['NAME']];

    \WRCInfrastructure\Users\Helpers\UserValidationHelper::sanitizeFormFields($form_fields);
    \WRCInfrastructure\Users\Helpers\UserValidationHelper::checkRequiredFields($form_fields, $error_messages);

    //return missing requirements before moving on to validation
    if(!empty($error_messages) && count($error_messages) > 0){
      return array(
        'error' => true,
        'messages' => $error_messages,
        'fields' => $form_fields
      );
    }

    \WRCInfrastructure\Users\Helpers\UserValidationHelper::checkMatchingEmail($form_fields, $error_messages);
    \WRCInfrastructure\Users\Helpers\UserValidationHelper::checkMatchingPasswords($form_fields, $error_messages);
    \WRCInfrastructure\Users\Helpers\UserValidationHelper::validateEmail($form_fields, $error_messages);
    \WRCInfrastructure\Users\Helpers\UserValidationHelper::validatePassword($form_fields, $error_messages);
    \WRCInfrastructure\Users\Helpers\UserValidationHelper::checkExistingEmail($form_fields, $error_messages);

    //return missing requirements before moving on to validation
    if(!empty($error_messages) && count($error_messages) > 0){
      return array(
        'error' => true,
        'messages' => $error_messages,
        'fields' => $form_fields
      );
    }

    //else create that user!
    $user_data = array(
      'user_login' => $email_field['field_value'],
      'user_pass' => $password_field['field_value'],
      'user_nicename' => strtolower(str_replace('@', '', str_replace('.', '-', ($email_field['field_value'])))),
      'user_email' => $email_field['field_value'],
      'display_name' => $first_name_field['field_value'] . ' ' . $last_name_field['field_value'],
      'first_name' => $first_name_field['field_value'],
      'last_name' => $last_name_field['field_value'],
      'user_registered' => date('Y-m-d H:i:s'),
      'role' => 'agent',
      'rich_editing' => true
    );

    $user_id = wp_insert_user($user_data);
    if($user_id){
      //values that we will actually set in the usermeta. these are not connected to acf fields and don't need to be
			add_user_meta($user_id, 'last_change_password_date', time());
      add_user_meta($user_id, 'agree_with_terms_and_conditions', 'true');

      //acf field schtuff
      add_user_meta($user_id, 'login_attempt_count', 0);
      add_user_meta($user_id, '_login_attempt_count', 'user_login_attempt_count');

      //approval flag used to determine if admin has approved the user
      add_user_meta($user_id, 'approved', 0);
      add_user_meta($user_id, '_approved', 'field_user_approval');

      $street_address = $address_field['field_value'] . ($address_2_field['field_value'] == '' ? '' : '; ' . $address_2_field['field_value']);
      add_user_meta($user_id, 'address', $street_address);
      add_user_meta($user_id, '_address', 'field_user_street');

      add_user_meta($user_id, 'city', $city_field['field_value']);
      add_user_meta($user_id, '_city', 'field_user_city');

      add_user_meta($user_id, 'state', $state_field['field_value']);
      add_user_meta($user_id, '_state', 'field_user_state');

      add_user_meta($user_id, 'zip', $zip_field['field_value']);
      add_user_meta($user_id, '_zip', 'field_user_zip');

      add_user_meta($user_id, 'phone', $phone_field['field_value']);
      add_user_meta($user_id, '_phone', 'field_user_phone');

      add_user_meta($user_id, 'agency', $agency_field['field_value']);
      add_user_meta($user_id, '_agency', 'field_user_agency');

      //tell em what they done
      $agency = get_post($agency_field['field_value']);
      self::sendNewUserEmail($user_id, $user_data, $agency, $phone_field['field_value']);

      return $user_id;
    }

  }

  private static function sendNewUserEmail($user_id, $user_data, $user_agency, $user_phone){
    $public_url = get_field('public_url', 'option');
    $admin_email = get_field('account_creation_email', 'options') ? get_field('account_creation_email', 'options') : 'aaron.rowe@integritystl.com';
    $user_email = $user_data['user_email'];
    $user_agency_name = $user_agency->post_title;
    $user_first_name = $user_data['first_name'];
    $user_last_name = $user_data['last_name'];

    $message = 'Automated message from ' . $public_url . "\r\n\r\n";
    //$message .= "Date:  " . date_i18n('F jS @ g:i a'). "\r\n\r\n";
    $message .= "A website visitor has created a new account:". "\r\n\r\n";
    $message .= "Name: ". $user_first_name . " ". $user_last_name . "\r\n";
    $message .= "Company: ". $user_agency_name . "\r\n";
    $message .= "Email: ". $user_email . "\r\n";
    $message .= "Phone: ". $user_phone . "\r\n";
    $message .= "To approve this user, please log into the administrative section of the website:". "\r\n\r\n";
    $message .= get_admin_url() . "user-edit.php?user_id=" . $user_id . "\r\n\r\n";

    $to = $admin_email;
    $subject = 'Portal Account Creation';
    $headers[] = 'From: WRC Support <webadmin@thewrcgroup.com>';
    wp_mail($to, $subject, $message, $headers);
  }

  public static function sendUserApprovalEmail($user_id){
    $user_data = get_userdata(str_replace("user_", "", $user_id));
    $user_email = $user_data->user_email;
    $user_first_name = $user_data->first_name;
    $user_last_name = $user_data->last_name;
    //$agency = get_post($user_data->caps['agent']);
    $homeURL = get_home_url();
    $public_url = get_field('public_url', 'options');

    $message = 'Automated message from ' . $public_url . "\r\n";
    //$message .= "Date:  " . date_i18n('F jS @ g:i a'). "\r\n\r\n";
    $message .= "Dear " . $user_first_name . " " . $user_last_name . ", ". "\r\n\r\n";
    $message .= "Welcome to WRC Group!". "\r\n\r\n";
    $message .= "Your account has been approved for login:". "\r\n";
    $message .= $homeURL. "\r\n\r\n";
    $message .= "Sincerely,". "\r\n";
    $message .= "The WRC Group". "\r\n\r\n";

    $to = $user_email;
    $subject = 'Your Account is Approved';
    $headers[] = 'From: WRC Support <webadmin@thewrcgroup.com>';

    wp_mail($to, $subject, $message, $headers);
  }

}