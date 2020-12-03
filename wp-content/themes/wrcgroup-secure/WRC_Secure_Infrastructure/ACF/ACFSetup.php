<?php
namespace WRCInfrastructure;

//Just a place to store common ACF variables
require_once( __DIR__ . '/../Services/ACFCommonVariables.php');

//Include all custom fields here and they will load in
require_once( __DIR__ . '/CustomFields/acf-Agency.php');
require_once( __DIR__ . '/CustomFields/acf-MutualClient.php');
require_once( __DIR__ . '/CustomFields/acf-NavMenu.php');

class ACFSetup {

  public static function setupSharedACFFields(){
    if( function_exists('acf_add_options_page') ) {
      self::addOptionsPages();
    }
    self::createGlobalFields();
    self::createTopMenuFields();
    self::createFooterFields();
    self::createSideNavFields();
    self::setupAgencyAndMutualFields();
    self::setupMutualOnlyFields();
    self::setupAgentsOnlyFields();
    self::createUserFields();
    self::excludeFromLeftNav();
    self::addSiteMetaForSearch();
    self::createNewsFields();
    self::createBoltFields();
    self::fullwidthcontent();
  }
  //fields shared between agencies and mutuals
  private static function setupAgencyAndMutualFields(){
    if( function_exists('acf_add_local_field_group') ){
      acf_add_local_field_group(array(
        'key' => 'group_agency_mutual_company_info',
        'title' => 'Company Information',
        'fields' => array (
          array (
            'key' => 'field_506369a20cc74',
            'label' => 'Code',
            'name' => 'code',
            'type' => 'text',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_50636929996799',
            'label' => 'Address',
            'name' => 'address',
            'type' => 'textarea',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_506369299a614',
            'label' => 'City',
            'name' => 'city',
            'type' => 'text',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          //change to state dropdown
          array (
            'key' => 'field_506369299b5b4',
            'label' => 'State',
            'name' => 'state',
            'type' => 'text',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          //change to state dropdown
          array (
            'key' => 'field_5245df6d54e90',
            'label' => 'State of Business',
            'name' => 'state_of_business',
            'type' => 'text',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_506369299c554',
            'label' => 'Zip',
            'name' => 'zip',
            'type' => 'text',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          //research phone number data type
          array (
            'key' => 'field_506369299d4f4',
            'label' => 'Phone',
            'name' => 'phone',
            'type' => 'text',
            'wrapper' => array (
              'width' => '50',
              'id' => 'custom-phone-input',
            ),
          ),
          array (
            'key' => 'field_506369299e494',
            'label' => 'Website',
            'name' => 'website',
            'type' => 'url',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_agency_facebook',
            'label' => 'Facebook',
            'name' => 'facebook',
            'type' => 'url',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_agency_twitter',
            'label' => 'Twitter',
            'name' => 'twitter',
            'type' => 'url',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_agency_youtube',
            'label' => 'YouTube',
            'name' => 'youtube',
            'type' => 'url',
            'wrapper' => array (
              'width' => '33',
            ),
          ),
          array (
            'key' => 'field_agency_linkedin',
            'label' => 'LinkedIn',
            'name' => 'linkedin',
            'type' => 'url',
            'wrapper' => array (
              'width' => '33',
            ),
          ),
          array (
            'key' => 'field_agency_googleplus',
            'label' => 'Google+',
            'name' => 'google_plus',
            'type' => 'url',
            'wrapper' => array (
              'width' => '33',
            ),
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'agency',
            ),
          ),
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'mutual-client'
            )
          )
        ),
        'hide_on_screen' => array (
          0 => 'the_content',
          1 => 'excerpt',
          2 => 'custom_fields',
          3 => 'featured_image',
        )
      ));
      acf_add_local_field_group(array (
        'key' => 'group_mutual_repeater',
        'title' => 'Agencies',
        'fields' => array (
          array (
            'sub_fields' => array (
              array (
                'key' => 'field_mutual_acenty_repeater_item',
                'label' => 'Agency',
                'name' => 'agency',
                'type' => 'Agency',
                'instructions' => '',
              ),
            ),
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => '',
            'collapsed' => '',
            'key' => 'field_2341234123t1',
            'label' => 'Agencies',
            'name' => 'agencies',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
              'width' => '',
              'class' => '',
              'id' => '',
            ),
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'mutual-client',
            ),
          ),
        ),
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => array (
          0 => 'the_content',
          1 => 'excerpt',
          2 => 'custom_fields',
          3 => 'featured_image',
        ),
        'active' => 1,
        'description' => '',
      ));

    }
  }

  private static function setupAgentsOnlyFields(){
    if( function_exists('acf_add_local_field_group') ){
      acf_add_local_field_group(array (
        'key' => 'group_field_50be1696e55dc',
        'title' => 'Underwriter',
        'fields' => array (
            array (
              'key' => 'field_50be1696e55dc',
              'label' => 'Underwriter',
              'name' => 'underwriter',
              'type' => 'user',
              'role' => 'underwriter',
              'required' => 1,
              'allow_null' => 0
            ),
          ),
          'hide_on_screen' => array (
            0 => 'the_content',
            1 => 'excerpt',
            2 => 'custom_fields',
            3 => 'featured_image',
          ),
          'location' => array (
            array (
              array (
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'agency',
              ),
            ),
          ),
        ));
      }
    }
  private static function setupMutualOnlyFields(){
    if( function_exists('acf_add_local_field_group') ){
  		acf_add_local_field_group(array (
  			'key' => 'group_manager_options',
  			'title' => 'Manager',
  			'fields' => array (
  				array (
  					'key' => 'field_manager_name',
  					'label' => 'Manager Name',
  					'name' => 'manager_name',
  					'type' => 'text',
            'wrapper' => array (
              'width' => '50',
            ),
  				),
  				array (
  					'key' => 'field_manager_email',
  					'label' => 'Manager Email',
  					'name' => 'manager_email',
  					'type' => 'text',
            'wrapper' => array (
              'width' => '50',
            ),
  				)
  			),
  			'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'mutual-client',
            ),
          ),
        ),
  		));
  	}
  }

  private static function createTopMenuFields(){
    if( function_exists('acf_add_local_field_group') ){
  		acf_add_local_field_group(array (
  			'key' => 'group_top_menu_options',
  			'title' => 'Top Menu Options',
  			'fields' => array (
  				array (
  					'key' => 'field_top_menu_logo_link',
  					'label' => 'Logo Link',
  					'name' => 'logo_link',
  					'type' => 'url',
  				),
          array (
            'key' => 'field_top_menu_logo_img',
            'label' => 'Logo Image',
            'name' => 'logo_img',
            'type' => 'image',
          ),
  				array (
  					'key' => 'field_top_menu_secure_email',
  					'label' => 'Secure Email Link',
  					'name' => 'secure_email_link',
  					'type' => 'url',
  				)
  			),
  			'location' => array (
  				array (
  					array (
  						'param' => 'options_page',
  						'operator' => '==',
  						'value' => 'top_menu_settings',
  					),
  				),
  			),
  		));
  	}
  }

  private static function createFooterFields(){
    if( function_exists('acf_add_local_field_group') ){
  		acf_add_local_field_group(array (
  			'key' => 'group_footer_options',
  			'title' => 'Footer Options',
  			'fields' => array (
          array (
            'key' => 'field_footer_footer_menu',
            'label' => 'Footer Menu',
            'name' => 'footer_menu',
            'type' => 'nav_menu',
          ),
  				array (
  					'key' => 'field_footer_legal_notice_link',
  					'label' => 'Legal Notice Link',
  					'name' => 'legal_notice_link',
  					'type' => 'url',
  				),
  				array (
  					'key' => 'field_footer_privacy_policy_link',
  					'label' => 'Privacy Policy Link',
  					'name' => 'privacy_policy_link',
  					'type' => 'url',
  				)
  			),
  			'location' => array (
  				array (
  					array (
  						'param' => 'options_page',
  						'operator' => '==',
  						'value' => 'footer_settings',
  					),
  				),
  			),
  		));
  	}
  }

  private static function createSideNavFields(){
    if( function_exists('acf_add_local_field_group') ){
      acf_add_local_field_group(array (
        'key' => 'group_sidenav_options',
        'title' => 'Side Navigation Options',
        'fields' => array (
          array (
            'key' => 'field_side_nav_logo',
            'label' => 'Side Navigation Logo',
            'name' => 'side_nav_logo',
            'type' => 'image',
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'options_page',
              'operator' => '==',
              'value' => 'sidenav_settings',
            ),
          ),
        ),
      ));
    }
  }

  private static function createGlobalFields(){
    if( function_exists('acf_add_local_field_group')){
      acf_add_local_field_group(array (
  			'key' => 'group_1',
  			'title' => 'Global Options',
  			'fields' => array (
          array (
            'key' => 'field_settings_announcement_enabled',
            'label' => 'Announcement Enabled',
            'name' => 'announcements_enabled',
            'type' => 'true_false',
            'instructions' => 'When enabled, this announcement appears in the header of the site until a user dismisses it.',
            'default_value' => 0,
            'wrapper' => array (
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'key' => 'field_settings_announcement_refresh_time',
            'label' => 'Announcement Refresh Time',
            'name' => 'announcement_refresh_time',
            'type' => 'number',
            'instructions' => 'Time (in minutes) before the announcement banner cookie expires. Defaults to 60.',
            'wrapper' => array (
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'key' => 'field_settings_announcement_header',
            'label' => 'Announcement Header',
            'name' => 'announcement_header',
            'type' => 'text',
            'formatting' => 'html',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_settings_announcement_text',
            'label' => 'Announcement Text',
            'name' => 'announcement_text',
            'type' => 'text',
            'formatting' => 'html',
            'maxlength' => '',
            'wrapper' => array (
              'width' => '50',
            ),
          ),

          array (
            'key'=> 'field_options_login_page',
            'label' => 'Login Page',
            'name' => 'login_page',
            'type' => 'post_object',
            'post_type' => 'page',
            'return_format' => 'object',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key'=> 'field_options_login_attempt_max',
            'label' => 'Login Attempt Max',
            'name' => 'login_attempt_max',
            'instructions' => 'The number of times a user can attempt to login before they are locked out of the system.',
            'type' => 'number',
            'default_value' => 5,
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key'=> 'field_options_reset_request_page',
            'label' => 'Password Reset Request Page',
            'name' => 'reset_request_page',
            'type' => 'post_object',
            'post_type' => 'page',
            'return_format' => 'object',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key'=> 'field_options_reset_day_limit_text',
            'label' => 'Password Reset Day Limit Text',
            'name' => 'password_reset_day_limit_text',
            'type' => 'textarea',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key'=> 'field_options_reset_day_limit',
            'label' => 'Days Between Password Resets',
            'name' => 'reset_request_day_limit',
            'type' => 'number',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key'=> 'field_options_reset_form_page',
            'label' => 'Password Reset Form Page',
            'name' => 'password_reset_page',
            'type' => 'post_object',
            'post_type' => 'page',
            'return_format' => 'object',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key'=> 'field_options_registration_page',
            'label' => 'Registration Page',
            'name' => 'registration_page',
            'type' => 'post_object',
            'post_type' => 'page',
            'return_format' => 'object',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key'=> 'field_options_my_account_page',
            'label' => 'My Account Page',
            'name' => 'my_account_page',
            'type' => 'post_object',
            'post_type' => 'page',
            'return_format' => 'object',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_settings_empty_search_text',
            'label' => 'Empty Search Text',
            'description' => 'This text will appear when there are no results to return for the current filter selection',
            'name' => 'empty_search_text',
            'type' => 'text',
            'formatting' => 'html',
            'maxlength' => '',
          ),

          array (
  					'key' => 'field_options_tech_assistance_email',
  					'label' => 'Technical Assistance Email',
  					'name' => 'tech_assistance_email',
  					'type' => 'email',
            'wrapper' => array (
              'width' => '50',
            ),
          ),
          array (
            'key' => 'field_options_tech_assistance_phone',
            'label' => 'Technical Assistance Phone',
            'name' => 'tech_assistance_phone',
            'type' => 'text',
            'wrapper' => array (
              'width' => '50',
              'id' => 'custom-phone-input',
            ),
          ),
          array (
  					'key' => 'field_options_account_creation_email',
  					'label' => 'Account Creation Email',
  					'name' => 'account_creation_email',
  					'type' => 'email',
          ),
          array (
  					'key' => 'field_options_terms_and_conditions',
  					'label' => 'Terms and Conditions Text',
  					'name' => 'terms_and_conditions',
  					'type' => 'textarea',
            'new_lines' => 'br',
          ),
          array (
  					'key' => 'field_options_agreement',
  					'label' => 'Terms and Conditions Agreement Text',
  					'name' => 'agreement',
  					'type' => 'textarea'
  				),
          array (
            'key' => 'field_5cdd83d603466',
            'label' => 'Learning Library Favorites Page',
            'name' => 'favorites_page',
            'type' => 'page_link',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '',
              'class' => '',
              'id' => '',
            ),
            'post_type' => 'page',
            'taxonomy' => '',
            'allow_null' => 1,
            'allow_archives' => 1,
            'multiple' => 0,
          ),
  			),
  			'location' => array (
  				array (
  					array (
  						'param' => 'options_page',
  						'operator' => '==',
  						'value' => 'global_settings',
  					),
  				),
  			),
  		));
    }
  }

  private static function createUserFields()
  {
    if( function_exists('acf_add_local_field_group') ){
      /**
      * User Address Block
      **/
      acf_add_local_field_group(array(
        'key' => 'group_user_contact',
        'title' => 'Contact Info',
        'fields' => array (
          array (
            'key' => 'field_user_street',
            'label' => 'Street Address',
            'name' => 'address',
            'type' => 'textarea',
          ),
          array (
            'key' => 'field_user_city',
            'label' => 'City',
            'name' => 'city',
            'type' => 'text',
          ),
          array (
            'key' => 'field_user_state',
            'label' => 'State',
            'name' => 'state',
            'type' => 'select',
            'choices' => \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectAllStates(),
            'allow_null' => 0,
            'multiple' => 0
          ),
          array (
            'key' => 'field_user_zip',
            'label' => 'Zip',
            'name' => 'zip',
            'type' => 'text'
          ),
          array (
            'key' => 'field_user_phone',
            'label' => 'Phone',
            'name' => 'phone',
            'type' => 'text',
            'wrapper' => array (
              'width' => '',
              'id' => 'custom-phone-input',
            ),
          )
        ),
        'location' => array (
          array (
            array (
              'param' => 'user_role',
              'operator' => '==',
              'value' => 'all',
            ),
          ),
        ),
      ));

      /**
      * Initials for Underwriter
      * This field exists so users with the role of Underwriter can add 3 character initials to be passed to Bolt
      **/
      acf_add_local_field_group(array (
        'key' => 'group_user_underwriter_initials',
        'title' => 'Underwriter Initials',
        'fields' => array (
          array (
            'key' => 'field_user_underwriter_initials',
            'label' => 'Initials',
            'name' => 'underwriter_initials',
            'type' => 'text',
            'maxlength' => '3',
            'required' => 1
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'user_role',
              'operator' => '==',
              'value' => 'underwriter',
            ),
          ),
        ),
      ));

      /**
      * User Permissions Block
      **/
      acf_add_local_field_group(array (
        'key' => 'group_user_permissions',
        'title' => 'Permissions',
        'fields' => array (
          /**
          * @todo Convert this to a post object field
          **/
          array (
            'key' => 'field_user_sites',
            'label' => 'Sites',
            'name' => 'sites',
            'required' => 1,
            'type' => 'post_object',
            'post_type' => 'site',
            'multiple' => 1
          ),
          array (
            'key' => 'field_user_states',
            'label' => 'States',
            'name' => 'states',
            'required' => 1,
            'type' => 'checkbox',
            'choices' => \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectStatesOfBusiness()
          ),
          array (
            'key' => 'field_user_other_permissions',
            'label' => 'Permissions',
            'name' => 'permissions',
            'type' => 'checkbox',
            'choices' => array (
              'is_manager' => 'Manager',
              'is_principal' => 'Principal',
              'is_endorsement' => 'Endorsement',
              'drp_agreement' => 'DRP Agreement',
              'view_inquiry_only' => 'View Inquiry Only'
            )
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'user_role',
              'operator' => '==',
              'value' => 'all',
            ),
          ),
        ),
      ));

      /**
      * User Approval Block
      **/
      acf_add_local_field_group(array (
        'key' => 'group_user_status',
        'title' => 'User Status',
        'fields' => array (
          array (
            'key' => 'field_user_approval',
            'label' => 'Status',
            'name' => 'approved',
            'type' => 'true_false',
            'message' => 'Approved'
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'user_role',
              'operator' => '==',
              'value' => 'all',
            ),
          ),
        ),
      ));


      /**
      * User Login Attempts Block
      * only admins should be able to edit users, so we only have to specify that it's on the user edit view
      **/
      acf_add_local_field_group(array (
        'key' => 'user_login_attempts',
        'title' => 'User Login Attempts',
        'fields' => array (
          array (
            'default_value' => 0,
            'key' => 'user_login_attempt_count',
            'label' => 'Login Attempt Count',
            'name' => 'login_attempt_count',
            'prepend' => 'User locked',
            'instructions' => 'To reset login attempts, set this value to 0 and save the user.',
            'type' => 'number',
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'user_form',
              'operator' => '==',
              'value' => 'edit',
            ),
          ),
          array (
            array (
              'param' => 'user_role',
              'operator' => '==',
              'value' => 'all',
            ),
          ),
        )
      ));


      /**
      * Agent and Mutual Block
      **/
      acf_add_local_field_group(array (
        'key' => 'group_user_agency_information',
        'title' => 'Agency Information',
        'fields' => array (
          array (
            'key' => 'field_user_agency',
            'label' => 'Agency',
            'name' => 'agency',
            'type' => 'Agency',
          ),
          array (
            'key' => 'field_user_mutual',
            'label' => 'Mutual Client Company',
            'name' => 'mutual-client',
            'type' => 'MutualClient',
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'user_role',
              'operator' => '==',
              'value' => 'agent',
            ),
          ),
          array (
            array (
              'param' => 'user_role',
              'operator' => '==',
              'value' => 'mutual'
            )
          ),
        ),
      ));
      /**
      * Mutual Block
      **/
      // acf_add_local_field_group(array (
      //   'key' => 'group_user_mutual_information',
      //   'title' => 'Mutual Information',
      //   'fields' => array (
      //     array (
      //       'key' => 'field_user_mutual',
      //       'label' => 'Mutual Client Company',
      //       'name' => 'mutual-client',
      //       'type' => 'MutualClient',
      //     ),
      //   ),
      //   'location' => array (
      //     array (
      //       array (
      //         'param' => 'user_role',
      //         'operator' => '==',
      //         'value' => 'agent',
      //       ),
      //       array (
      //         'param' => 'user_role',
      //         'operator' => '==',
      //         'value' => 'mutual',
      //       ),
      //     ),
      //   ),
      // ));

      /**
      * Lines of Business Block
      **/
      acf_add_local_field_group(array (
        'key' => 'group_lines_of_business',
        'title' => 'Lines of Business',
        'fields' => array (
          array (
            'key' => 'field_user_commercial_auto',
            'label' => 'CA (Commercial Auto)',
            'name' => 'ca',
            'type' => 'select',
            'choices' => \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectStatesOfBusiness(false),
            'allow_null' => 1,
            'multiple' => 1
          ),
          array (
            'key' => 'field_user_farm_truck',
            'label' => 'FT (Farm Truck)',
            'name' => 'ft',
            'type' => 'select',
            'choices' => \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectStatesOfBusiness(false),
            'allow_null' => 1,
            'multiple' => 1
          ),
          array (
            'key' => 'field_user_liability',
            'label' => 'Liability (Liability)',
            'name' => 'li',
            'type' => 'select',
            'choices' => \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectStatesOfBusiness(false),
            'allow_null' => 1,
            'multiple' => 1
          ),
          array (
            'key' => 'field_user_personal_auto',
            'label' => 'PA (Personal Auto)',
            'name' => 'pa',
            'type' => 'select',
            'choices' => \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectStatesOfBusiness(false),
            'allow_null' => 1,
            'multiple' => 1
          ),
          array (
            'key' => 'field_user_umbrella',
            'label' => 'UM (Umbrella)',
            'name' => 'um',
            'type' => 'select',
            'choices' => \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectStatesOfBusiness(false),
            'allow_null' => 1,
            'multiple' => 1
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'user_role',
              'operator' => '==',
              'value' => 'all'
            )
          ),
        ),
      ));
    }
  }

  private static function createBoltFields() {
    if( function_exists('acf_add_local_field_group') ){
      acf_add_local_field_group(array (
        'key' => 'bolt_settings_global',
        'title' => 'Bolt Settings',
        'fields' => array (
          array (
            'key' => 'field_options_bolt_link',
            'label' => 'Bolt Base Link',
            'name' => 'bolt_base_link',
            'type' => 'text'
          ),
          array (
            'key' => 'field_options_maintenance_page',
            'label' => 'Maintenance Page',
            'name' => 'maintenance_page',
            'type' => 'page_link'
          ),
          array (
            'key' => 'field_options_emergency_maintenance_page',
            'label' => 'Emergency Maintenance Page',
            'name' => 'emergency_maintenance_page',
            'type' => 'page_link'
          ),
          array (
            'key' => 'field_options_policy_help_text',
            'label' => 'Policy Modal Help Text',
            'name' => 'access_policy_help_text',
            'type' => 'text'
          ),
          array (
            'key' => 'field_options_bill_help_text',
            'label' => 'Bill Pay Help Text',
            'name' => 'bill_pay_help_text',
            'type' => 'text'
          ),
          array (
            'key' => 'field_options_ottomation_link',
            'label' => '1st Ottomation Link',
            'name' => 'ottomation_link',
            'type' => 'text'
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'options_page',
              'operator' => '==',
              'value' => 'bolt_settings',
            ),
          ),
        ),
        ));
    }

    if( function_exists('acf_add_local_field_group') ):

      acf_add_local_field_group(array (
        'key' => 'group_59a43c6fa7509',
        'title' => 'Maintenance Settings',
        'fields' => array (
          array (
            'label' => 'Enable Emergency Maintenance',
            'name' => 'emergency_maintenance',
            'type' => 'true_false',
            'key' => 'field_emergency_maintenance'
          ),
          array (
            'sub_fields' => array (
              array (
                'type' => 'true_false',
                'name' => 'active',
                'label' => 'Active',
                'key' => 'field_active_sun_maintenance'
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_start_sun',
                'label' => 'Start Time',
                'name' => 'start_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_end_sun',
                'label' => 'End Time',
                'name' => 'end_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
            ),
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => '',
            'collapsed' => '',
            'key' => 'field_sunday_maintenance',
            'label' => 'Sunday',
            'name' => 'sunday',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
              'width' => '',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'sub_fields' => array (
              array (
                'type' => 'true_false',
                'name' => 'active',
                'label' => 'Active',
                'key' => 'field_active_mon_maintenance'
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_start_mon',
                'label' => 'Start Time',
                'name' => 'start_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_end_mon',
                'label' => 'End Time',
                'name' => 'end_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
            ),
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => '',
            'collapsed' => '',
            'key' => 'field_monday_maintenance',
            'label' => 'Monday',
            'name' => 'monday',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
              'width' => '',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'sub_fields' => array (
              array (
                'type' => 'true_false',
                'name' => 'active',
                'label' => 'Active',
                'key' => 'field_active_tues_maintenance'
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_start_tues',
                'label' => 'Start Time',
                'name' => 'start_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_end_tues',
                'label' => 'End Time',
                'name' => 'end_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
            ),
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => '',
            'collapsed' => '',
            'key' => 'field_tuesday_maintenance',
            'label' => 'Tuesday',
            'name' => 'tuesday',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
              'width' => '',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'sub_fields' => array (
              array (
                'type' => 'true_false',
                'name' => 'active',
                'label' => 'Active',
                'key' => 'field_active_wed_maintenance'
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_start_wed',
                'label' => 'Start Time',
                'name' => 'start_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_end_wed',
                'label' => 'End Time',
                'name' => 'end_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
            ),
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => '',
            'collapsed' => '',
            'key' => 'field_wednesday_maintenance',
            'label' => 'Wednesday',
            'name' => 'wednesday',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
              'width' => '',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'sub_fields' => array (
              array (
                'type' => 'true_false',
                'name' => 'active',
                'label' => 'Active',
                'key' => 'field_active_thurs_maintenance'
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_start_thurs',
                'label' => 'Start Time',
                'name' => 'start_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_end_thurs',
                'label' => 'End Time',
                'name' => 'end_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
            ),
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => '',
            'collapsed' => '',
            'key' => 'field_thursday_maintenance',
            'label' => 'Thursday',
            'name' => 'thursday',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
              'width' => '',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'sub_fields' => array (
              array (
                'type' => 'true_false',
                'name' => 'active',
                'label' => 'Active',
                'key' => 'field_active_fri_maintenance'
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_start_fri',
                'label' => 'Start Time',
                'name' => 'start_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_end_fri',
                'label' => 'End Time',
                'name' => 'end_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
            ),
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => '',
            'collapsed' => '',
            'key' => 'field_friday_maintenance',
            'label' => 'Friday',
            'name' => 'friday',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
              'width' => '',
              'class' => '',
              'id' => '',
            ),
          ),
          array (
            'sub_fields' => array (
              array (
                'type' => 'true_false',
                'name' => 'active',
                'label' => 'Active',
                'key' => 'field_active_sat_maintenance'
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_start_sat',
                'label' => 'Start Time',
                'name' => 'start_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
              array (
                'display_format' => 'g:i a',
                'return_format' => 'His',
                'key' => 'field_end_sat',
                'label' => 'End Time',
                'name' => 'end_time',
                'type' => 'time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
              ),
            ),
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => '',
            'collapsed' => '',
            'key' => 'field_saturday_maintenance',
            'label' => 'Saturday',
            'name' => 'saturday',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
              'width' => '',
              'class' => '',
              'id' => '',
            ),
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'options_page',
              'operator' => '==',
              'value' => 'maintenance_settings',
            ),
          ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => 1,
        'description' => '',
      ));

      endif;

  }

  private static function createNewsFields(){
    if( function_exists('acf_add_local_field_group') ){
      acf_add_local_field_group(array (
        'key' => 'field_group_flex_news_content',
        'name' => 'group_flex_news_content',
        'title' => 'News Content',
        'fields' => array (
          array (
            'layouts' => array (
              array (
                'key' => '5992fc8e2e5a6',
                'name' => 'single_column_content_block',
                'label' => 'Single Column Content Block',
                'display' => 'block',
                'sub_fields' => array (
                  array (
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'default_value' => '',
                    'delay' => 0,
                    'key' => 'field_5992fdb857b61',
                    'label' => 'Content',
                    'name' => 'content',
                    'type' => 'wysiwyg',
                    'instructions' => 'This is for displaying full width, single column content',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array (
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                  ),
                ),
                'min' => '',
                'max' => '',
              ),
              array (
                'key' => '5992fece57b66',
                'name' => 'quote_block',
                'label' => 'Quote Block',
                'display' => 'block',
                'sub_fields' => array (
                  array (
                    'default_value' => '',
                    'new_lines' => 'wpautop',
                    'maxlength' => '',
                    'placeholder' => '',
                    'rows' => '',
                    'key' => 'field_5992fee857b67',
                    'label' => 'Quote',
                    'name' => 'quote',
                    'type' => 'textarea',
                    'instructions' => 'This field holds text you would like to show up as a stylized quote.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array (
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                  ),
                ),
                'min' => '',
                'max' => '',
              ),
              array (
                'key' => '5992ff1257b68',
                'name' => 'embedded_video_block',
                'label' => 'Embedded Video Block',
                'display' => 'block',
                'sub_fields' => array (
                  array (
                    'key' => 'field_5992ff4257b69',
                    'label' => 'embedded_video',
                    'name' => 'embedded_video',
                    'type' => 'oembed',
                    'instructions' => 'This field is used to embed a video in the post.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array (
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                  ),
                ),
                'min' => '',
                'max' => '',
              ),
              array (
                'key' => '5994375e00de1',
                'name' => 'multi_column_image_block',
                'label' => 'Multi-Column Image Block',
                'display' => 'block',
                'sub_fields' => array (
                  array (
                    'sub_fields' => array (
                      array (
                        'return_format' => 'url',
                        'preview_size' => 'thumbnail',
                        'library' => 'all',
                        'min_width' => '',
                        'min_height' => '',
                        'min_size' => '',
                        'max_width' => '',
                        'max_height' => '',
                        'max_size' => '',
                        'mime_types' => '',
                        'key' => 'field_5994379a95d8b',
                        'label' => 'Image',
                        'name' => 'image',
                        'type' => 'image',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                          'width' => '',
                          'class' => '',
                          'id' => '',
                        ),
                      ),
                      array (
                        'default_value' => '',
                        'maxlength' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'key' => 'field_59956997167e0',
                        'label' => 'Title',
                        'name' => 'title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                          'width' => '',
                          'class' => '',
                          'id' => '',
                        ),
                      ),
                    ),
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'table',
                    'button_label' => '',
                    'collapsed' => '',
                    'key' => 'field_5994378795d8a',
                    'label' => 'Images',
                    'name' => 'images',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array (
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                  ),
                ),
                'min' => '',
                'max' => '',
              ),
              array (
                'key' => '5994383cff747',
                'name' => 'single_column_image_block',
                'label' => 'Single Column Image Block',
                'display' => 'block',
                'sub_fields' => array (
                  array (
                    'return_format' => 'url',
                    'preview_size' => 'thumbnail',
                    'key' => 'field_59943846ff748',
                    'label' => 'Image',
                    'name' => 'image',
                    'type' => 'image',
                  ),
                ),
                'min' => '',
                'max' => '',
              ),
              array (
                'key' => '5992ff6857b6a',
                'name' => 'callout_block',
                'label' => 'Callout Block',
                'display' => 'block',
                'sub_fields' => array (
                  array (
                    'default_value' => '',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'key' => 'field_5992ff7257b6b',
                    'label' => 'Callout Heading',
                    'name' => 'callout_heading',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array (
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                  ),
                  array (
                    'default_value' => '',
                    'new_lines' => 'wpautop',
                    'maxlength' => '',
                    'placeholder' => '',
                    'rows' => '',
                    'key' => 'field_5992ff8657b6c',
                    'label' => 'Callout Body Content', //changing just the label per ticket request
                    'name' => 'callout_subhead',
                    'type' => 'textarea',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array (
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                  ),
                  array (
                    'default_value' => '',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'key' => 'field_5992ff9b57b6d',
                    'label' => 'Callout Button Text',
                    'name' => 'callout_button_text',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array (
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                  ),
                  array (
                    'default_value' => '',
                    'placeholder' => '',
                    'key' => 'field_5992ffa857b6e',
                    'label' => 'Callout Button Link',
                    'name' => 'callout_button_link',
                    'type' => 'url',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array (
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                  ),
                ),
                'min' => '',
                'max' => '',
              ),
            ),
            'min' => '',
            'max' => '',
            'button_label' => 'Add Row',
            'key' => 'field_5992fc7d57b60',
            'label' => 'News Flexible Content',
            'name' => 'news_flexible_content',
            'type' => 'flexible_content',
            'instructions' => 'Reorderable mixed content for News Posts',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
              'width' => '',
              'class' => '',
              'id' => '',
            ),
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'post',
            ),
          ),
        ),
        'hide_on_screen' => array (
          0 => 'the_content',
          1 => 'excerpt',
        )
      ));

    }
  }

  private static function addOptionsPages() {
    acf_add_options_page(array(
        'page_title'    => 'Global Settings Page',
        'menu_title'    => 'Global Settings',
        'menu_slug'     => 'global_settings',
        'redirect'      => false,
        'capability'    => 'manage_options'
    ));

    acf_add_options_sub_page(array(
      'title' => 'Footer Settings',
      'slug' => 'footer_settings',
      'parent' => 'global_settings',
      'capability'    => 'manage_options'
    ));

    acf_add_options_sub_page(array(
      'title' => 'Top Menu Settings',
      'slug' => 'top_menu_settings',
      'parent' => 'global_settings',
      'capability' => 'manage_options'
    ));

    acf_add_options_sub_page(array(
      'title' => 'Bolt Settings',
      'slug' => 'bolt_settings',
      'parent' => 'global_settings',
      'capability' => 'manage_options'
    ));

    acf_add_options_sub_page(array(
      'title' => 'Maintenance Times',
      'slug' => 'maintenance_settings',
      'parent' => 'global_settings',
      'capability' => 'manage_options'
    ));

    acf_add_options_sub_page(array(
      'title' => 'Side Navigation Settings',
      'slug' => 'sidenav_settings',
      'parent' => 'global_settings',
        'capability' => 'manage_options'
    ));
  }

  private static function excludeFromLeftNav() {
    acf_add_local_field_group(array (
        'key' => 'group_exclude_from_left_nav',
        'title' => 'Exclude From Left Navigation',
        'fields' => array (
          array(
            'key' => 'field_exclude_from_left_nav',
            'name' => 'exclude_from_left_nav',
            'type' => 'true_false',
            'default_value' => '0', //should be false (not excluded by default)
            'message' => 'Select if you want to exclude this Page from appearing in the Left Navigation menu.',
          ),
        ),
        'position' => 'side',
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'page'
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'default',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-document_table.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-container.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-email-news-template.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-event-listing.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-events.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-login.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-my-account.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-password-reset-form.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-password-reset-request.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-registration.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-site-template.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-staff.php',
            ),
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-news-template.php',
            ),
          ),
        ),
      ));
  }

  private static function addSiteMetaForSearch() {
    acf_add_local_field_group(array (
        'key' => 'group_site_meta_search',
        'title' => 'Attached Sites',
        'fields' => array (
          array (
            'key' => 'field_post_sites',
            'label' => 'Sites',
            'name' => 'site',
            'type' => 'text',
            'readonly' => 1,
            'instructions' => 'This field is set automatically. There is no need to alter it.'
          ),
        ),
        'position' => 'side',
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'page'
            ),
            array(
              'param' => 'page_template',
              'operator' => '!=',
              'value' => 'page-site-template.php'
            )
          ),
        ),
      ));
    acf_add_local_field_group(array (
        'key' => 'group_site_post_search',
        'title' => 'Attached Sites',
        'fields' => array (
          array (
            'key' => 'field_post_site',
            'label' => 'Sites',
            'name' => 'site',
            'type' => 'post_object',
            'post_type' => 'site',
            'instructions' => 'Indicate which sites this post should show up on',
            'multiple' => 1
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'post'
            ),
          ),
        ),
      ));
  }
  private static function fullwidthcontent() {
      if( function_exists('acf_add_local_field_group') ):

        acf_add_local_field_group(array(
          'key' => 'group_5e90ccc68d15e',
          'title' => 'Full Width Page',
          'fields' => array(
            array(
              'key' => 'field_5e90cd46c3c7a',
              'label' => 'Columns',
              'name' => 'columns',
              'type' => 'radio',
              'instructions' => 'How many columns will this page need?',
              'required' => 1,
              'conditional_logic' => 0,
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'choices' => array(
                1 => '1',
                2 => '2',
              ),
              'allow_null' => 1,
              'other_choice' => 0,
              'default_value' => 1,
              'layout' => 'vertical',
              'return_format' => 'value',
              'save_other_choice' => 0,
            ),
            array(
              'key' => 'field_5e90cf5fc3c80',
              'label' => 'Full Width Content',
              'name' => 'full_width_content',
              'type' => 'wysiwyg',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => array(
                array(
                  array(
                    'field' => 'field_5e90cd46c3c7a',
                    'operator' => '==',
                    'value' => '1',
                  ),
                ),
              ),
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'tabs' => 'all',
              'toolbar' => 'full',
              'media_upload' => 1,
              'delay' => 0,
            ),
            array(
              'key' => 'field_5e90ce36c3c7b',
              'label' => 'Left Column',
              'name' => '',
              'type' => 'tab',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => array(
                array(
                  array(
                    'field' => 'field_5e90cd46c3c7a',
                    'operator' => '==',
                    'value' => '2',
                  ),
                ),
              ),
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'placement' => 'top',
              'endpoint' => 0,
            ),
            array(
              'key' => 'field_5e90ce4cc3c7c',
              'label' => 'left Content',
              'name' => 'left_content',
              'type' => 'wysiwyg',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'tabs' => 'all',
              'toolbar' => 'full',
              'media_upload' => 1,
              'delay' => 0,
            ),
            array(
              'key' => 'field_5e90cf0dc3c7e',
              'label' => 'Right Column',
              'name' => '',
              'type' => 'tab',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => array(
                array(
                  array(
                    'field' => 'field_5e90cd46c3c7a',
                    'operator' => '==',
                    'value' => '2',
                  ),
                ),
              ),
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'placement' => 'top',
              'endpoint' => 0,
            ),
            array(
              'key' => 'field_5e90cf41c3c7f',
              'label' => 'Right Content',
              'name' => 'right_content',
              'type' => 'wysiwyg',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'tabs' => 'all',
              'toolbar' => 'full',
              'media_upload' => 1,
              'delay' => 0,
            ),
            array(
              'key' => 'field_5e90ce77c3c7d',
              'label' => 'Form Shortcode',
              'name' => 'form_shortcode',
              'type' => 'wysiwyg',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'placeholder' => '',
              'prepend' => '',
              'append' => '',
              'maxlength' => '',
            ),
          ),
          'location' => array(
            array(
              array(
                'param' => 'post_template',
                'operator' => '==',
                'value' => 'full-width.php',
              ),
            ),
          ),
          'menu_order' => 0,
          'position' => 'acf_after_title',
          'style' => 'default',
          'label_placement' => 'top',
          'instruction_placement' => 'label',
          'hide_on_screen' => array(
            0 => 'the_content',
          ),
          'active' => true,
          'description' => '',
        ));

        endif;
      }

    private static function userDash() {
      
    }

}
