<?php
namespace WRCInfrastructure\Users;
/**
* This class is in charge of the User Registration form functionality and adding new users to the DB
**/

class UserAccount {

  //Keep in mind $form_fields is passed by reference and modified by the validation functions so error flags can be set
  public static function updateUser(&$form_fields, $current_user){
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

    // Make sure Email Address and Confirm Email Address match
    \WRCInfrastructure\Users\Helpers\UserValidationHelper::checkMatchingEmail($form_fields, $error_messages);
    \WRCInfrastructure\Users\Helpers\UserValidationHelper::validateEmail($form_fields, $error_messages);
    \WRCInfrastructure\Users\Helpers\UserValidationHelper::checkExistingEmail($form_fields, $error_messages, $current_user->ID);

    //user does not have to update password, but if they do...
    if(!empty($password_field['field_value']) && $password_field['updated']){
      \WRCInfrastructure\Users\Helpers\UserValidationHelper::checkMatchingPasswords($form_fields, $error_messages);
      \WRCInfrastructure\Users\Helpers\UserValidationHelper::validatePassword($form_fields, $error_messages);
      \WRCInfrastructure\Users\Helpers\UserValidationHelper::checkCurrentPasswordReuse($form_fields, $error_messages, $current_user);
      \WRCInfrastructure\Users\Helpers\UserValidationHelper::checkPasswordReuseHistory($form_fields, $error_messages, $current_user);

      //grab old user password before we swap it out
      $old_password = $current_user->user_pass;
    }

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
      'ID' => $current_user->ID,
      'user_pass' => $password_field['field_value'],
      'user_nicename' => strtolower(str_replace('@', '', str_replace('.', '-', ($email_field['field_value'])))),
      'user_email' => $email_field['field_value'],
      'display_name' => $first_name_field['field_value'] . ' ' . $last_name_field['field_value'],
      'first_name' => $first_name_field['field_value'],
      'last_name' => $last_name_field['field_value']
    );

    $user_id = wp_update_user($user_data);
    if($user_id){
      if($password_field['updated'] && !empty($password_field['field_value'])){
        //add password to old passwords array
        add_user_meta($user_id, 'old_password', $old_password);

        //values that we will actually set in the usermeta. these are not connected to acf fields and don't need to be
			  update_user_meta($user_id, 'last_change_password_date', time());
      }

      if($email_field['updated']){
        //can't update user_login with that update user function, have to do it manually
        global $wpdb;
        $wpdb->update($wpdb->users, array('user_login' => $email_field['field_value']), array('ID' => $user_id));
      }
      
      if($address_field['updated']){
        $street_address = $address_field['field_value'] . ($address_2_field['field_value'] == '' ? '' : '; ' . $address_2_field['field_value']);
        update_user_meta($user_id, 'address', $street_address);
        update_user_meta($user_id, '_address', 'field_user_street');
      }
      
      if($city_field['updated']){
        update_user_meta($user_id, 'city', $city_field['field_value']);
        update_user_meta($user_id, '_city', 'field_user_city'); 
      }
      
      if($state_field['updated']){
        update_user_meta($user_id, 'state', $state_field['field_value']);
        update_user_meta($user_id, '_state', 'field_user_state'); 
      }
      
      if($zip_field['updated']){
        update_user_meta($user_id, 'zip', $zip_field['field_value']);
        update_user_meta($user_id, '_zip', 'field_user_zip'); 
      }
      
      if($phone_field['updated']){
        update_user_meta($user_id, 'phone', $phone_field['field_value']);
        update_user_meta($user_id, '_phone', 'field_user_phone'); 
      }

      if($password_field['updated'] || $email_field['updated']){
        $redirect_var = ($password_field['updated'] ? '?reset=1' : '?update=1');
        wp_logout();
        $login_page = get_field('login_page', 'option');
        wp_redirect(get_permalink($login_page->ID) . $redirect_var);
        exit;
      }

      return $user_id;
    }
    
  }

  public static function checkLastPasswordResetDate(){
    if (is_user_logged_in()) {
      $last_pass = get_user_meta(get_current_user_id(), 'last_change_password_date', true);
      $reset_day_limit = intval(get_field('reset_request_day_limit', 'option'));
      //var_dump($reset_day_limit);
      //default to 180 days if limit isn't set in acf
      $reset_limit = 60 * 60 * 24 * (empty($reset_day_limit) ? 180 : $reset_day_limit);
      //var_dump($reset_limit);
      $my_account_page = get_field('my_account_page', 'option');
      //check to see if we're loading the my account page before redirecting
      if ((empty($last_pass) || time() - $last_pass > $reset_limit) && $my_account_page->ID !== get_the_ID()) {
        $my_account_page = get_field('my_account_page', 'option');
        wp_redirect(get_permalink($my_account_page->ID) . '?expired=1');
        exit;
      }
    }
  }

  //fancy error messages handled on the client side, this is just in case something gets through
  private static function validatePassword($password){
    if(strlen($password) < 8 || !preg_match('/\d/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/\W/', $password) || preg_match('/#/', $password)){
      return false;
    }
    return true;
  }


  //This handles checking the password on update from admin so that if an admin updates
  //someone's password, it'll not force them to do it too
  public static function profile_update_check($user_id, $old_user_data){
    //check and see if the current saved password hash is the same as the old data.
    global $wpdb;
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_users WHERE user_pass = %s AND ID = %d", array($old_user_data->user_pass, $user_id)));
    //If we got no hits, then it's not the same, and we can know it was changed.
    if(!count($results)){
      add_user_meta($user_id, 'old_password', $old_user_data->user_pass);
      update_user_meta($user_id, 'last_change_password_date', time());
    }
  }

}