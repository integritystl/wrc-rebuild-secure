<?php

// Include WordPress
define('WP_USE_THEMES', false);
require('wp-load.php');

$users = get_users();
foreach($users as $user){
  $success = wp_update_user($user);
  var_dump($success);
}

die("ok i guess?");

?>