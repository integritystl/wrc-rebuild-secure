<?php

// Include WordPress
define('WP_USE_THEMES', false);
require('wp-load.php');

$file_path = '/Users/aaronrowe/Desktop/test.json';

$db_user = 'root';
$db_pass = 'root';
$db_name = 'wrc_secure_rebuild';
$db_host = 'localhost';
$dbi = new wpdb($db_user, $db_pass, $db_name, $db_host);

$users_map = array();
$agencies_map = array();
$mutuals_map = array();


//input data assumed to be json encoded nested acssociative arrays
//each entry in the array has it's meta data flattened into the row with all meta table keys taking the format 'meta_{column_name}'
//ex. {"agencies":{"ID":2, ...,  "meta_address":"asdf"}, "users":{...}, "mutuals":{...}}
$input_json = file_get_contents($file_path);
$input_data = json_decode($input_json, true);

//get the id of the next post and user so we can build the id maps
$next_post_id = $dbi->get_results("SELECT Auto_increment FROM information_schema.tables WHERE table_name='wp_posts' AND table_schema='$db_name'");
$next_user_id = $dbi->get_results("SELECT Auto_increment FROM information_schema.tables WHERE table_name='wp_users' AND table_schema='$db_name'");

//we will need to add the acf field mappings along with post and user meta data
//grab all acf field keys for users, agencies, and mutuals
$acf_user_field_names_map = $dbi->get_results("select meta_key, meta_value from wp_users as u left join wp_usermeta as um on u.id = um.user_id where um.meta_key like '\_%' and um.meta_value like 'field\_%' group by meta_key;");
$acf_agency_field_names_map = $dbi->get_results("select meta_key, meta_value from wp_posts as p left join wp_postmeta as pm on p.id = pm.post_id where p.post_type = 'agency' and pm.meta_key like '\_%' and pm.meta_value like 'field\_%' group by meta_key;");
$acf_mutuals_field_names_map = $dbi->get_results("select meta_key, meta_value from wp_posts as p left join wp_postmeta as pm on p.id = pm.post_id where p.post_type = 'mutual-client' and pm.meta_key like '\_%' and pm.meta_value like 'field\_%' group by meta_key;");

//some fields in the old db aren't named the same thing in the new db
//hardcode maps of those values
$user_field_name_replacement_map = array('other_permissions' => 'permissions');

//function description below
$users_map = buildObjectIDMap($input_data['users'], intval($next_user_id[0]->Auto_increment));
$agencies_map = buildObjectIDMap($input_data['agencies'], intval($next_post_id[0]->Auto_increment));
$last_map_entry = end($agencies_map); //var_dump($agencies_map); 
$mutuals_map = buildObjectIDMap($input_data['mutuals'], ($last_map_entry + 1));
//var_dump($mutuals_map); die;

$post_type_map = array('agencies' => 'agency', 'client_companies' => 'mutual-client');

//map from old site's state ids to state abbr
$states_map = array(408 => 'AR', 409 => 'IA', 410 => 'IL', 411 => 'MO', 412 => 'SD', 413 => 'WI');
//map from old site's site ids to new site's site ids
$sites_map = array(414 => 5, 415 => 72, 416 => 75, 417 => 77);

//function description below
$agency_queries = buildObjectInsertStatements(
  $input_data['agencies'],
  'wp_posts',
  'wp_postmeta',
  'post_id',
  array('self' => $agencies_map, 'meta_underwriter' => $users_map),
  array(),
  $acf_agency_field_names_map,
  $post_type_map
);

$mutuals_map_data_temp = array('self' => $mutuals_map);
$temp_agencies_maps = array();
//when entries are added to the agency repeater they take the form 'agencies_#_agency'
//there are 30 unique agency keys for mutuals currently in production. lay off me this is a script jeez.
for($i=0; $i<30; $i++){
  $key_name = 'meta_agencies_' . $i . '_agency';
  $temp_agencies_maps[$key_name] = $agencies_map;
}

$mutuals_map_data = array_merge($mutuals_map_data_temp, $temp_agencies_maps);
/*var_dump($agencies_map);
var_dump(count($agencies_map));
var_dump('break');
var_dump($mutuals_map); die;*/

$mutual_queries = buildObjectInsertStatements(
  $input_data['mutuals'],
  'wp_posts',
  'wp_postmeta',
  'post_id',
  $mutuals_map_data,
  array(),
  $acf_mutuals_field_names_map,
  $post_type_map
);

//var_dump(rtrim($mutual_queries['insert_meta_query'], ", ")); die;

$users_queries = buildObjectInsertStatements(
  $input_data['users'],
  'wp_users',
  'wp_usermeta',
  'user_id',
  array(
    'self' => $users_map, 
    'meta_agency' => $agencies_map, 
    'meta_mutual' => $mutuals_map,
    'meta_sites' => array('map' => $sites_map, 'serialized' => true),
    'meta_states' => array('map' => $states_map, 'serialized' => true),
    'meta_li' => array('map' => $states_map, 'serialized' => true),
    'meta_ft' => array('map' => $states_map, 'serialized' => true),
    'meta_ca' => array('map' => $states_map, 'serialized' => true),
    'meta_um' => array('map' => $states_map, 'serialized' => true),
    'meta_pa' => array('map' => $states_map, 'serialized' => true),
  ),
  array('other_permissions' => 'permissions'),
  $acf_user_field_names_map,
  $post_type_map
);
//var_dump($input_data['users']);
//var_dump(rtrim($agency_queries['insert_meta_query'], ", "));
//die;

$dbi->query( "START TRANSACTION" );
$success = true;
$success = $dbi->query(rtrim($users_queries['insert_query'], ", "));
if(!$success){var_dump('problem with users', rtrim($users_queries['insert_meta_query'], ", ")); die;}
$success = $success && $dbi->query(rtrim($users_queries['insert_meta_query'], ", "));
if(!$success){var_dump('problem with users meta', rtrim($users_queries['insert_meta_query'], ", ")); die;}
$success = $success && $dbi->query(rtrim($agency_queries['insert_query'], ", "));
if(!$success){var_dump('problem with agencies', rtrim($agency_queries['insert_query'], ", ")); die;}
$success = $success && $dbi->query(rtrim($agency_queries['insert_meta_query'], ", "));
if(!$success){var_dump('problem with agency meta', rtrim($agency_queries['insert_meta_query'], ", ")); die;}
$success = $success && $dbi->query(rtrim($mutual_queries['insert_query'], ", "));
if(!$success){var_dump('problem with mutuals', rtrim($mutual_queries['insert_query'], ", ")); die;}
$success = $success && $dbi->query(rtrim($mutual_queries['insert_meta_query'], ", "));
if(!$success){var_dump('problem with mutual', rtrim($mutual_queries['insert_meta_query'], ", ")); die;}
if($success){
  var_dump('success');
  $dbi->query( "COMMIT" );
}else{
  var_dump('yeah thats what i thought');
  $dbi->query( "ROLLBACK" );
}

var_dump('holy crap we did it');
exit;

// Creates an array that maps OLD_OBJECT_ID => NEW_OBJECT_ID
// Used for mapping objects to one another in the new db
function buildObjectIDMap($input_array, $initial_offset){
  $object_map = array();
  foreach($input_array as $object){
    $object_map[$object['ID']] = $initial_offset;
    $initial_offset++;
  }

  return $object_map;
}

//build insert column names for non-meta table columns
function buildQueryColumns($column_data){
  $table_cols = array();

  foreach($column_data as $key => $value){
    if(strpos($key, 'meta_') === false && $key !== 'ID'){
      array_push($table_cols, $key);
    }
  }

  return $table_cols;
}

/**
* The big daddy of this script. uses id maps to create insert statements for the appropriate table/meta table combo
* $object_data - array of data pulled from json decoded input
* $table_name - name of base table we'll be inserting into
* $meta_table_name - name of meta table we'll be inserting into
* $meta_table_foreign_key - name of meta table column that relates base table and meta table (ex. 'post_id' for wp_posts => wp_postmeta)
* $meta_table_reference_data - associative array that maps meta column names to the correct data id map (ex. array('meta_agencies' => $agencies_map))
*                            - use the key 'self' to specify the map for the current object. if you're building the agencies insert statments 'self' would be $agencies_map
* $meta_table_column_name_maps - associative array that maps column names from the old db structure to the new name in the new structure
* $meta_acf_map - associative array that maps column names to their acf field names
* $post_type_map - associative array that maps column names to their acf field names
**/
function buildObjectInsertStatements($object_data, $table_name, $meta_table_name, $meta_table_foreign_key, $meta_table_reference_data, $meta_table_column_name_maps, $meta_acf_map, $post_type_map){
  $old_site_path = '//secure.thewrcgroup.com/';
  $new_site_path = '//wrc-secure-rebuild.loc/';

  $insert_query = "insert into $table_name ";
  $insert_meta_query = "insert into $meta_table_name ";
  
  //assumes meta keys are all grouped together after non-meta keys
  $object_query_columns = buildQueryColumns($object_data[0]);
  
  $insert_query .= "(" . implode(", ", $object_query_columns) . ") values ";
  $insert_meta_query .= "(" . $meta_table_foreign_key . ", meta_key, meta_value) values ";
  
  //assumes post_id comes first in the meta info
  $agencies_count = 0;
  $start_agencies_count = false;
  foreach($object_data as $object){
    $insert_values = array();
    
    //grab old ID for reference
    $object_id = $object["ID"];
    foreach($object as $key => $value){
      //we don't want to import the old IDs
      if($key !== 'ID'){
        //if we're not a meta value, just add the value directly to the base table insert values array
        if(strpos($key, 'meta_') === false && !array_key_exists($key, $meta_table_reference_data)){
            if($key === 'post_type'){
              if(array_key_exists($value, $post_type_map)){
                $value = $post_type_map[$value];
              }
            }

            if(!ctype_digit(strval($value)) && !is_bool($value)){
              $value = str_replace($old_site_path, $new_site_path, $value);
              $value = stringify(esc_sql($value));
            }
            array_push($insert_values, $value);
        }else{
          //if we're a non-empty meta value, and yes some are imported as "null", blargh
          if(!empty($value) && $value !== "null"){
            $temp_meta = array();
            $temp_acf_meta = array();
            //grab the new object id from our map and add set it as the reference column value
            array_push($temp_meta, $meta_table_reference_data['self'][$object_id]);
            //strip 'meta_' from the key and add it as our meta key (5 is the length of the string 'meta_')
            $meta_key = substr($key, strpos($key, 'meta_') + 5);

            $acf_field_entry_name = '_' . $meta_key;
            //create acf field key data if it exists
            //var_dump($acf_field_entry_name);
            foreach($meta_acf_map as $map_entry){
              if($map_entry->meta_key === $acf_field_entry_name){
                array_push($temp_acf_meta, $meta_table_reference_data['self'][$object_id]);
                array_push($temp_acf_meta, stringify($acf_field_entry_name));
                array_push($temp_acf_meta, stringify($map_entry->meta_value));

                break;
              }
            }
            //grab the new key name from the map if it exists
            if(array_key_exists($meta_key, $meta_table_column_name_maps)){
              array_push($temp_meta, stringify($meta_table_column_name_maps[$meta_key]));
            }else{
              array_push($temp_meta, stringify($meta_key));
            }
            //check our reference data to see if a map exists for the current field
            if(array_key_exists($key, $meta_table_reference_data)){
              //check to see if the meta value is a serialized array
              if(array_key_exists('serialized', $meta_table_reference_data[$key])){
                $unserialized_array = unserialize($value);
                $replacement_data = array();
                foreach($unserialized_array as $array_entry){
                  if(array_key_exists($array_entry, $meta_table_reference_data[$key]['map'])){
                    //add the new ID as our meta value
                    array_push($replacement_data, esc_sql($meta_table_reference_data[$key]['map'][$array_entry]));
                  }else{
                    //bad data, for some reason we don't have a mapped value
                    //var_dump('serialization problem');
                    array_push($replacement_data, -1);
                  }
                }
                array_push($temp_meta, stringify(serialize($replacement_data)));
              }else{
                //check the map for an entry mapping the meta value (old ID) to one of our new IDs
                if(array_key_exists($value, $meta_table_reference_data[$key])){
                  //add the new ID as our meta value
                  if(!ctype_digit(strval($meta_table_reference_data[$key][$value])) && !is_bool($meta_table_reference_data[$key][$value])){
                    $value = str_replace($old_site_path, $new_site_path, $meta_table_reference_data[$key][$value]);
                    array_push($temp_meta, stringify(esc_sql($value)));
                  }else{
                    array_push($temp_meta, esc_sql($meta_table_reference_data[$key][$value]));
                  }
                }else{
                  //bad data, for some reason we don't have a mapped value
                  array_push($temp_meta, -1);
                }
              }
            }else{
              //just add the data if a map does not exist

              //old bug in user registration caused address to be duplicated, fix that if we come across it
              if($meta_key === 'address'){
                $address_parts = explode(';', $value);
                if(count($address_parts) > 1){
                  $first = trim($address_parts[0]);
                  $second = trim($address_parts[1]);
                  if($first === $second){
                    $value = $first;
                  }
                }
              }

              if(!ctype_digit(strval($value)) && !is_bool($value)){
                $value = str_replace($old_site_path, $new_site_path, $value);
                $value = stringify(esc_sql($value));
              }
              array_push($temp_meta, $value);
            }
            
            //we want separate Value groups for each meta value since they will be inserted into the meta table separately
            $insert_meta_query .= "(" . implode(", ", $temp_meta) . "), ";

            if(count($temp_acf_meta) > 0){
              $insert_meta_query .= "(" . implode(", ", $temp_acf_meta) . "), ";
            }
          }
        }
      }
    }
    //only one Value group per base table
    $insert_query .= "(" . implode(", ", $insert_values) . "), ";
  }

  return array('insert_query' => $insert_query, 'insert_meta_query' => $insert_meta_query);

}

function stringify($data){
  return "'" . $data . "'";
}

?>