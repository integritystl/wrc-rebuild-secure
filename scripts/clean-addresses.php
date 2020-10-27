<?php

//this script assumes you have downloaded a copy of the dev and prod databases and set each of them up locally
//it is also asummed that all new users were copied over separately
//to run, copy into src folder and navigate to https://wrc-instance-name/transfer-passwords.php in your browser

// Include WordPress
define('WP_USE_THEMES', false);
require('wp-load.php');

$rebuild_db_user = 'root';
$rebuild_db_pass = 'root';
$rebuild_db_name = 'wrc_secure_rebuild';
$rebuild_db_host = 'localhost';
$rebuild_dbi = new wpdb($rebuild_db_user, $rebuild_db_pass, $rebuild_db_name, $rebuild_db_host);
//get users' id, login, as password from our rebuild instance
$rebuild_addresses = $rebuild_dbi->get_results("select u.id as id, um.meta_value as address from wp_users as u left join wp_usermeta as um on u.id = um.user_id where meta_key = 'address' and meta_value like '%;%'");

$update_statements = "";
foreach($rebuild_addresses as $address){
  $address_parts = explode(';', $address->address);
  if(count($address_parts) > 1){
    $first = trim($address_parts[0]);
    $second = trim($address_parts[1]);
    if($first === $second){
      $statement = "UPDATE wp_usermeta SET meta_value = '$first' WHERE user_id = $address->id and meta_key = 'address'; ";
      $update_statements .= $statement;
    }
  }
}

var_dump($update_statements);
die;

?>