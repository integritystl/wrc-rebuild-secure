<?php

//this script assumes you have downloaded a copy of the dev and prod databases and set each of them up locally
//it is also asummed that all new users were copied over separately
//to run, copy into src folder and navigate to https://wrc-instance-name/transfer-passwords.php in your browser

// Include WordPress
define('WP_USE_THEMES', false);
require('wp-load.php');

$rebuild_db_user = 'root';
$rebuild_db_pass = 'root';
$rebuild_db_name = 'wrc_secure_rebuild_users';
$rebuild_db_host = 'localhost';
$rebuild_dbi = new wpdb($rebuild_db_user, $rebuild_db_pass, $rebuild_db_name, $rebuild_db_host);
//get users' id, login, as password from our rebuild instance
$rebuild_users = $rebuild_dbi->get_results("select ID, user_login, user_pass from wp_users");

$prod_db_user = 'root';
$prod_db_pass = 'root';
$prod_db_name = 'wrc_secure_test';
$prod_db_host = 'localhost';
$prod_dbi = new wpdb($prod_db_user, $prod_db_pass, $prod_db_name, $prod_db_host);

//get users' id, login, as password from our prod instance
$prod_users = $prod_dbi->get_results("select u.id as id, u.user_login as user_login, um.meta_value as state from wp_users as u left join wp_usermeta as um on u.id = um.user_id where meta_key = 'state'");

$insert_query = "insert into wp_usermeta (user_id, meta_key, meta_value) values ";

foreach($rebuild_users as $r_user){
  foreach($prod_users as $p_user){
    //if the user logins match and the passwords aren't already the same, add sql statement to update rebuild user
    if($r_user->user_login === $p_user->user_login){
      $state_value = empty($p_user->state) ? "''" : "'$p_user->state'";
      $insert_query .= '(' . $r_user->ID . ', \'state\', ' . $state_value . '), ';
      break;
    }
  }
}

var_dump($insert_query); die;


var_dump('finished'); die;

?>