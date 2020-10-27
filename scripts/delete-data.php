<?php

//this script assumes you have downloaded a copy of the dev and prod databases and set each of them up locally
//it is also asummed that all new users were copied over separately
//to run, copy into src folder and navigate to https://wrc-instance-name/transfer-passwords.php in your browser

// Include WordPress
define('WP_USE_THEMES', false);
require('wp-load.php');

$rebuild_db_user = 'root';
$rebuild_db_pass = 'root';
$rebuild_db_name = 'wrc_secure_rebuild_test';
$rebuild_db_host = 'localhost';
$rebuild_dbi = new wpdb($rebuild_db_user, $rebuild_db_pass, $rebuild_db_name, $rebuild_db_host);
//get users' id, login, as password from our rebuild instance

$prod_db_user = 'root';
$prod_db_pass = 'root';
$prod_db_name = 'wrc_secure_test';
$prod_db_host = 'localhost';
$prod_dbi = new wpdb($prod_db_user, $prod_db_pass, $prod_db_name, $prod_db_host);
//get users' id, login, as password from our prod instance

//delete users
$prod_users = $prod_dbi->get_results("select user_login from wp_users");
$users_array = array();
foreach($prod_users as $user){
  array_push($users_array, $user->user_login);
}
$login_string = implode('\',\'', $users_array);
//var_dump("select id from wp_users where user_login not in ('$login_string')"); die;
$unique_stage_users = $rebuild_dbi->get_results("select id from wp_users where user_login not in ('$login_string')");
$keep_users = array();
foreach($unique_stage_users as $user){
  array_push($keep_users, $user->id);
}
$keep_string = implode(', ', $keep_users);
$delete_users = $wpdb->query("delete from wp_users where id not in ($keep_string);");
$delete_user_meta = $wpdb->query("delete from wp_usermeta where user_id not in ($keep_string);");
var_dump('users', $delete_users, $delete_user_meta);


//delete agencies
$prod_agencies = $prod_dbi->get_results("select post_title from wp_posts where post_type = 'agencies'");
$agencies_array = array();
foreach($prod_agencies as $agency){
  array_push($agencies_array, esc_sql($agency->post_title));
}
$agencies_string = implode('\',\'', $agencies_array);
//var_dump($agencies_string); die;
//var_dump("select id from wp_users where user_login not in ('$login_string')"); die;
$unique_stage_agencies = $rebuild_dbi->get_results("select id from wp_posts where post_type='agency' and post_title not in ('$agencies_string')");
$keep_agencies = array();
foreach($unique_stage_agencies as $agency){
  array_push($keep_agencies, $agency->id);
}
$keep_string = implode(', ', $keep_agencies);
$delete_agent_meta = $wpdb->query("delete from wp_postmeta where post_id in (select id from wp_posts where post_type = 'agency' and post_id not in ($keep_string));");
$delete_agents = $wpdb->query("delete from wp_posts where post_type = 'agency' and id not in ($keep_string);");
var_dump('users', $delete_agent_meta, $delete_agents);



//delete mutuals
$prod_mutuals = $prod_dbi->get_results("select post_title from wp_posts where post_type = 'client_companies'");
$mutuals_array = array();
foreach($prod_mutuals as $mutual){
  array_push($mutuals_array, esc_sql($mutual->post_title));
}
$mutuals_string = implode('\',\'', $mutuals_array);
//var_dump("select id from wp_posts where post_type=‘mutual-client’ and post_title not in ('$mutuals_string')"); die;
$unique_stage_mutuals = $rebuild_dbi->get_results("select id from wp_posts where post_type='mutual-client' and post_title not in ('$mutuals_string')");
//var_dump($unique_stage_mutuals); die;
$keep_mutuals = array();
foreach($unique_stage_mutuals as $mutual){
  array_push($keep_mutuals, $mutual->id);
}
$keep_string = implode(', ', $keep_mutuals);
$delete_mutual_meta = $wpdb->query("delete from wp_postmeta where post_id in (select id from wp_posts where post_type = 'mutual-client' and post_id not in ($keep_string));");
$delete_mutuals = $wpdb->query("delete from wp_posts where post_type = 'mutual-client' and id not in ($keep_string);");
var_dump('users', $delete_mutual_meta, $delete_mutuals);


die;

?>