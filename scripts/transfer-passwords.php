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
$prod_users = $prod_dbi->get_results("select ID, user_login, user_pass from wp_users");

$update_statements = array();
foreach($rebuild_users as $r_user){
  foreach($prod_users as $p_user){
    //if the user logins match and the passwords aren't already the same, add sql statement to update rebuild user
    if($r_user->user_login === $p_user->user_login && $r_user->user_pass !== $p_user->user_pass ){
      $password = $p_user->user_pass;
      $id = $r_user->ID;
      $statement = "UPDATE wp_users SET user_pass = '$password' WHERE ID = $id;";
      array_push($update_statements, $statement);

      break;
    }else{
      //notify if password was already the same
      if($r_user->user_login === $p_user->user_login && $r_user->user_pass === $p_user->user_pass){
        var_dump('passwords identical');
      }
    }
  }
}

//start an update transaction if we have stuff to update
if(count($update_statements)){
  $rebuild_dbi->query( "START TRANSACTION" );
  $success = 0;

  //execute each update statement and add to success tally
  //was just playing around with the tally, should probably have better reporting mechanism 
  foreach($update_statements as $statement){
    var_dump($statement);
    $success += $rebuild_dbi->query($statement);
  }

  var_dump($success);

  if($success){
    var_dump('success');
    $rebuild_dbi->query( "COMMIT" );
  }else{
    var_dump('yeah thats what i thought');
    $rebuild_dbi->query( "ROLLBACK" );
  }
}

var_dump('finished'); die;

?>