<?php
/**
* Custom admin page for user export
**/

require_once( __DIR__ . '/WRC_Secure_Infrastructure/Users/UserExport.php');
require_once( __DIR__ . '/WRC_Secure_Infrastructure/Services/ACFCommonVariables.php');

$export_filters = \WRCInfrastructure\Users\UserExport::getExportFilters();

?>

<div class="wrap">
   <div class="icon32" id="icon-edit"><br></div><h2>Export Users</h2>
   
   <p>Select options for which users you'd like to export. Selecting no options will export all users.</p>
   
   <form action="" method="post">
      <div class="admin-form-row">
        <fieldset>
          <legend>Sites</legend>
          <?php
            $sites = get_posts(array(
              'numberposts' => -1,
              'post_type' => 'site',
              'orderby' => 'title',
              'order' => 'ASC'
            ));

            foreach($sites as $site){
              echo '<input type="checkbox" name="sites[]" id="site_' . $site->ID . '" value="' . $site->ID . '" />';
              echo '<label for="site_' . $site->ID . '">';
              echo $site->post_title;
              echo '</label>';
            }
          ?>
        </fieldset>
      </div>

      <div class="admin-form-row">
        <fieldset>
          <legend>States</legend>
          <?php
            $states = \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectStatesOfBusiness();

            foreach($states as $abbr => $state){
              echo '<input type="checkbox" name="states[]" id="state_' . $abbr . '" value="' . $abbr . '" />';
              echo '<label for="state_' . $abbr . '">';
              echo $state;
              echo '</label>';
            }
          ?>
        </fieldset>
      </div>

      <div class="admin-form-row">
        <fieldset>
          <legend>User Type</legend>
          <?php
            $roles = get_editable_roles();
            foreach($roles as $key => $role){
              echo '<input type="checkbox" name="roles[]" id="role_' . $key . '" value="' . $key . '" />';
              echo '<label for="role_' . $key . '">';
              echo $role['name'];
              echo '</label>';
            }
          ?>
        </fieldset>
      </div>


      <div class="admin-form-row">
        <fieldset>
          <legend>Other Filters</legend>
          <?php
            foreach($export_filters as $key => $filter){
              echo '<input type="checkbox" name="' . $key . '" id="filter_' . $key . '" value="' . $key . '" />';
              echo '<label for="filter_' . $key . '">';
              echo $filter;
              echo '</label>';
            }
          ?>
        </fieldset>
      </div>

      <div class="admin-form-row">
        <fieldset>
          <legend>Agencies</legend>
          <select name="agencies[]" id="agencies" multiple="multiple">
            <?php
              $agencies = get_posts(array(
                'numberposts' => -1,
                'post_type' => 'agency',
                'orderby' => 'title',
                'order' => 'ASC'
              ));

              foreach($agencies as $agency){
                echo '<option value="' . $agency->ID . '">' . $agency->post_title . '</option>';
              }
            ?>
          </select>
        </fieldset>
      </div>

      <div class="admin-form-row">
        <input type="submit" value="Export Users" class="button-primary" name="submit_export_users" />
      </div>  
   </form>
   
</div>