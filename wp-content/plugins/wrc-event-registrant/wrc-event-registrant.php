<?php
/*
Plugin Name: WRC Event Registrant
Description: Small plugin to overwrite Event Espresso's additional registrant's email requirement
*/

//If the Event Espresso plugin is active, we're disabling the function that requires additional registrants to enter their email address
// Ref: https://eventespresso.com/topic/allow-use-of-email-addresses-associated-with-user-accounts-without-signing-in/
if (is_plugin_active('event-espresso-core-reg/espresso.php') ) {

  add_filter( 'EED_WP_Users_SPCO__verify_user_access__perform_email_user_match_check', '__return_false' );

}