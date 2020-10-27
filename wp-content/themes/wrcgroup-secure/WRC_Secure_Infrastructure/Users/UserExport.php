<?php
namespace WRCInfrastructure\Users;

/**
 * Handle exporting of user data
 */
class UserExport
{

  public static function getExportFilters(){
    $filters = array(
      'is_manager'=>'Manager', 
      'is_principal'=>'Principal', 
      'is_endorsement'=>'Endorsement'
    );

    return $filters;
  }

  public static function export($sites, $states, $roles, $filters, $agencies){
    $data = array(
      array('User ID', 'First Name', 'Last Name', 'Agency', 'Email', 'Address', 'City', 'State', 'Zip', 'Phone', 'Is Manager', 'Is Principal', 'Has Endorsement')
    );

    $query_args = array();
    $meta_query = array();

    if(!empty($roles)){
      $query_args['role__in'] = $roles;
    }

    if(!empty($sites)){
      $subquery = array('relation' => 'OR');
      foreach($sites as $site){
        array_push($subquery, array('key' => 'sites', 'value' => $site, 'compare' => 'LIKE'));
      }
      array_push($meta_query, $subquery);
    }

    if(!empty($states)){
      $subquery = array('relation' => 'OR');
      foreach($states as $state){
        array_push($subquery, array('key' => 'states', 'value' => $state, 'compare' => 'LIKE'));
      }
      array_push($meta_query, $subquery);
    }

    if(!empty($agencies)){
      array_push($meta_query, array('key' => 'agency', 'value' => $agencies, 'compare' => 'IN'));
    }

    if(!empty($filters)){
      $export_filters = self::getExportFilters();
      foreach($export_filters as $key => $filter){
        if(in_array($key, $filters)){
          array_push($meta_query, array('key' => 'permissions', 'value' => $key, 'compare' => 'LIKE'));
        }
      }
    }

    if(count($meta_query) > 0){
      $query_args['meta_query'] = $meta_query;
    }

    $users = get_users($query_args);

    if(!empty($users)){
      foreach($users as $user){
        $meta = get_user_meta($user->ID);
        //will return 'false' if permissions is null
        $permissions = unserialize($meta['permissions'][0]);

        $company_id = empty($meta['agency']) ? (empty($meta['mutual']) ? '' : $meta['mutual'][0]) : $meta['agency'][0];
        $agency_or_mutual = get_post($company_id);
        $user_data = array(
          $user->ID,
          (empty($meta['first_name']) ? '' : $meta['first_name'][0]),
          (empty($meta['last_name']) ? '' : $meta['last_name'][0]),
          (empty($agency_or_mutual) ? '' : $agency_or_mutual->post_title),
          $user->user_email,
          (empty($meta['address']) ? '' : $meta['address'][0]),
          (empty($meta['city']) ? '' : $meta['city'][0]),
          (empty($meta['state']) ? '' : $meta['state'][0]),
          (empty($meta['zip']) ? '' : $meta['zip'][0]),
          (empty($meta['phone']) ? '' : $meta['phone'][0]),
          ($permissions && in_array('is_manager', $permissions) ? '1' : ''),
          ($permissions && in_array('is_principal', $permissions) ? '1' : ''),
          ($permissions && in_array('is_endorsement', $permissions) ? '1' : '')
        );

        array_push($data, $user_data);
      }
    }

    $file = 'user-export-' . date_i18n('Y-m-d') . '.csv';
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=" . basename($file));

    self::outputCSV($data);

    die;

  }

  private static function outputCSV($data){
    $output = fopen("php://output", "w");
    foreach($data as $row){
      fputcsv($output, $row);
    }
    fclose($output);
  }
}