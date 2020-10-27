<?php
namespace WRCInfrastructure;

//Just a place to store common ACF variables
require_once( __DIR__ . '/../Services/ACFCommonVariables.php');

class ACFTemplateFields
{
  public static function setupTemplateFields()
  {
    self::siteTemplateFields();
  }

  private static function siteTemplateFields()
  {
    if( function_exists('acf_add_local_field_group') ){
      //Site Template
      acf_add_local_field_group(array(
        'key' => 'group_site_template',
        'title' => 'Site Template Info',
        'fields' => array (
          array (
            'key' => 'field_site_template_site',
            'label' => 'Company Selection',
            'name' => 'site',
            'type' => 'post_object',
            'post_type' => 'site'
          )
        ),
        'location' => array(
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-site-template.php'
            )
          ),
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-news-template.php'
            )
          )
        ),
        'hide_on_screen' =>  array (
          0 => 'the_content',
          1 => 'excerpt',
          2 => 'custom_fields',
          3 => 'featured_image'
        )
      ));
    //Document Table Template
    acf_add_local_field_group(array(
        'key' => 'group_document_template',
        'title' => 'Document Table Info',
        'fields' => array (
          array (
            'key' => 'field_5ab92c0721d25',
            'label' => 'Reverse Sort',
            'name' => 'reverse_sort',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '',
              'class' => '',
              'id' => '',
            ),
            'message' => '',
            'default_value' => 0,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
          ),
          array (
            'key' => 'field_document_template_category',
            'label' => 'Document Categories',
            'name' => 'document_categories',
            'type' => 'taxonomy',
            'taxonomy' => 'document_category',
            'field_type' => 'checkbox',
            'return_format' => 'id'
          ),
          array (
            'key' => 'field_document_table_additional_info',
            'label' => 'Document Table Additional Info',
            'name' => 'document_table_additional_info',
            'type' => 'wysiwyg',
            'instructions' => 'This field contains additional markup that will be rendered below the document table.'
          )
        ),
        'location' => array(
          array(
            array(
              'param' => 'page_template',
              'operator' => '==',
              'value' => 'page-document_table.php'
            )
          )
        ),
        'hide_on_screen' =>  array (
          0 => 'excerpt',
          1 => 'custom_fields',
          2 => 'featured_image'
        )
      ));

      //Container Template
      acf_add_local_field_group(array(
          'key' => 'group_container_template',
          'title' => 'Container Template Info',
          'fields' => array (
            array (
              'key' => 'field_container_template_redirect',
              'label' => 'Redirect',
              'name' => 'redirect',
              'type' => 'page_link',
              'post_type' => 'page'
            )
          ),
          'location' => array(
            array(
              array(
                'param' => 'page_template',
                'operator' => '==',
                'value' => 'page-container.php'
              )
            )
          ),
          'hide_on_screen' =>  array (
            0 => 'the_content',
            1 => 'excerpt',
            2 => 'custom_fields',
            3 => 'featured_image'
          )
        ));
      //All Pages
        acf_add_local_field_group(array(
          'key' => 'group_page_states',
          'title' => 'Page States',
          'fields' => array (
            array (
              'key' => 'field_page_states',
              'label' => 'States',
              'name' => 'states',
              'type' => 'checkbox',
              'choices' => \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectStatesOfBusiness()
            )
          ),
          'location' => array(
            array(
              array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'page'
              )
            )
          ),
        ));

        //staff template fields
        acf_add_local_field_group(array(
          'key' => 'group_staff_template',
          'title' => 'Staff Info',
          'fields' => array (
            array (
              'key' => 'field_division_repeater',
              'label' => 'Divisions',
              'name' => 'divisions',
              'type' => 'repeater',
              'layout' => 'row',
              'sub_fields' => array(
                array(
                  'key' => 'field_division_name',
                  'label' => 'Division Name',
                  'name' => 'division_name',
                  'type' => 'text'
                ),
                array(
                  'key' => 'field_division_staff',
                  'label' => 'Division Staff',
                  'name' => 'division_staff',
                  'type' => 'repeater',
                  'sub_fields' => array(
                    array(
                      'key' => 'field_division_staff_image',
                      'label' => 'Staff Image',
                      'name' => 'staff_image',
                      'max_size' => '0.5',
                      'type' => 'image',
                      'return_format' => 'array',
                      'preview_size' => 'small',
                      'required' => true,
                      'wrapper' => array (
                        'width' => '10',
                        'class' => '',
                        'id' => '',
                      ),
                    ),
                    array(
                      'key' => 'field_division_staff_name',
                      'label' => 'Staff Name',
                      'name' => 'staff_name',
                      'type' => 'text',
                      'required' => true
                    ),
                    array(
                      'key' => 'field_division_staff_title',
                      'label' => 'Staff Title',
                      'name' => 'staff_title',
                      'type' => 'text',
                      'required' => true
                    ),
                    array(
                      'key' => 'field_division_staff_phone',
                      'label' => 'Staff Phone',
                      'name' => 'staff_phone',
                      'type' => 'text',
                      'required' => true,
                      //format phone number field in admin
                      'wrapper' => array (
                        'id' => 'custom-phone-input',
                      ),
                    ),
                    array(
                      'key' => 'field_division_staff_fax',
                      'label' => 'Staff Fax',
                      'name' => 'staff_fax',
                      'type' => 'text',
                      'required' => true,
                      'wrapper' => array (
                        'id' => 'custom-phone-input',
                      ),
                    ),
                    array(
                      'key' => 'field_division_staff_email',
                      'label' => 'Staff Email',
                      'name' => 'staff_email',
                      'type' => 'email',
                      'required' => true
                    ),
                    array(
                      'key' => 'field_division_staff_years',
                      'label' => 'Employee Since',
                      'name' => 'staff_years',
                      'type' => 'text'
                    ),
                  )
                )
              )
            )
          ),
          'location' => array(
            array(
              array(
                'param' => 'page_template',
                'operator' => '==',
                'value' => 'page-staff.php'
              )
            )
          ),
        ));

        //news archive template fields
        acf_add_local_field_group(array(
          'key' => 'group_news_template',
          'title' => 'News Options',
          'fields' => array (
            array (
              'key' => 'field_site_news_categories',
              'label' => 'News Categories',
              'name' => 'news_categories',
              'type' => 'taxonomy',
              'taxonomy' => 'category',
            ),
          ),
          'location' => array(
            array(
              array(
                'param' => 'page_template',
                'operator' => '==',
                'value' => 'page-news-template.php'
              )
            )
          ),
        ));

        //news archive template fields
        acf_add_local_field_group(array(
          'key' => 'group_email_news_template',
          'title' => 'Email News Options',
          'fields' => array (
            array (
              'key' => 'field_site_email_news_categories',
              'label' => 'Additional Email News Categories',
              'instructions' => 'These categories will be listed in the News side menu underneath Email News',
              'name' => 'email_news_categories',
              'type' => 'taxonomy',
              'taxonomy' => 'email_news_category',
            ),
          ),
          'location' => array(
            array(
              array(
                'param' => 'page_template',
                'operator' => '==',
                'value' => 'page-email-news-template.php'
              )
            )
          ),
        ));

        //Learning archive template fields
        acf_add_local_field_group(array(
          'key' => 'group_learning_template',
          'title' => 'Learning Options',
          'fields' => array (
            array (
              'key' => 'field_site_learning_categories',
              'label' => 'Learning Categories',
              'name' => 'learning_categories',
              'type' => 'taxonomy',
              'taxonomy' => 'learning_category',
            ),
          ),
          'location' => array(
            array(
              array(
                'param' => 'page_template',
                'operator' => '==',
                'value' => 'learning-library-list-template.php'
              )
            )
          ),
        ));
        //Learning Favorites archive template fields
        acf_add_local_field_group(array(
          'key' => 'group_learning_favorites_template',
          'title' => 'Favorites Options',
          'fields' => array (
            array (
              'key' => 'field_site_learning_cats_favorites',
              'label' => 'Learning Categories',
              'name' => 'learning_cats_favorites',
              'type' => 'taxonomy',
              'taxonomy' => 'learning_category',
              'return_format' => 'object'
            ),
          ),
          'location' => array(
            array(
              array(
                'param' => 'page_template',
                'operator' => '==',
                'value' => 'page-favorites.php'
              )
            )
          ),
        ));

        //registration template fields
        acf_add_local_field_group(array(
          'key' => 'group_registration_template',
          'title' => 'Form Options',
          'fields' => array (
            array (
              'key' => 'field_site_form_subhead',
              'label' => 'Form Subhead',
              'name' => 'form_subhead',
              'type' => 'textarea',
            ),
          ),
          'location' => array(
            array(
              array(
                'param' => 'page_template',
                'operator' => '==',
                'value' => 'page-registration.php'
              )
              ),
              array(
                array(
                  'param' => 'page_template',
                  'operator' => '==',
                  'value' => 'page-my-account.php'
                )
              )
          ),
        ));

    }
  }
}
