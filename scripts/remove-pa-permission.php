<?php 
echo("Beginning Delete Process\n");
$conn = null;
try{
  if(empty($argv) || count($argv) < 4){
    throw new Exception("Your args are bad and you should feel bad. Command: remove-pa-permission.php username password db_name");
  }

  $db_user = $argv[1];
  $db_pass = $argv[2];
  $db_name = $argv[3];

  $conn = new mysqli('localhost', $db_user, $db_pass, $db_name);

  $users_query = "delete from wp_usermeta where meta_key = 'pa' or meta_key = '_pa'";
  $conn->query($users_query);
  echo("Deleted $conn->affected_rows Rows\n");

  echo("Successfully Completed Delete\n");
}catch(Exception $e){
  echo($e->getMessage() . "\n");
}finally{
  if($conn != null){
    $conn->close();
  }
}


?>