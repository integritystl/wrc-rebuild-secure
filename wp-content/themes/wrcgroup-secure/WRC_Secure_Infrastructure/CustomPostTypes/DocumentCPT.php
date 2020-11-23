<?php
namespace WRCInfrastructure\CustomPostTypes;

//Just a place to store common ACF variables
require_once( __DIR__ . '/../Services/ACFCommonVariables.php');

/**
* Document Custom Post Type
**/
class DocumentCPT
{
  //needs to be called in wordpress init hook
  public static function setupDocuments()
  {
    self::createDocumentPostType();
    self::addDocumentACFFields();
    self::registerCustomTaxonomy();
  }

  public static function createDocumentPostType()
  {
    register_post_type( 'document',
      array(
        'labels' => array(
          'name' => __( 'Documents' ),
          'singular_name' => __( 'Document' )
        ),
        'exclude_from_search' => false,
        'publicly_queryable' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-media-document',
      )
    );
  }

  public static function addDocumentACFFields()
  {
    if( function_exists('acf_add_local_field_group') ){
      acf_add_local_field_group(array(
        'key' => 'group_documents',
        'title' => 'Document Fields',
        'fields' => array (
          /**
          * @todo Turn into Custom Field
          **/
          array (
            'key' => 'field_document_site',
            'label' => 'Sites',
            'name' => 'site',
            'type' => 'post_object',
            'post_type' => 'site',
            'multiple' => 1

          ),
          array (
            'key' => 'field_document_description',
            'label' => 'Description',
            'name' => 'description',
            'type' => 'wysiwyg',
            'new_lines' => 'br'
          ),
          array (
            'key' => 'field_document_state',
            'label' => 'States',
            'name' => 'states',
            'type' => 'checkbox',
            'choices' => \WRCInfrastructure\Services\ACFCommonVariables::ACFSelectStatesOfBusiness()
          ),
          array (
            'key' => 'field_document_files',
            'label' => 'Files',
            'name' => 'files',
            'type' => 'repeater',
            'sub_fields' => array (
                array (
                  'key' => 'field_document_upload',
                  'type' => 'file',
                  'name' => 'file',
                  'label' => 'File',
                  'return_format' => 'array',
                  'library' => 'all'
                )
              )
          )
        ),
        'location' => array (
          array (
            array (
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'document',
            ),
          ),
        ),
        'hide_on_screen' => array (
          0 => 'the_content',
          1 => 'excerpt',
          2 => 'custom_fields',
          3 => 'featured_image',
        )
      ));
    }
  }

  public static function registerCustomTaxonomy()
  {
    $args = array (
      'labels' => array(
        'name' => 'Document Categories',
        'singular_name' => 'Document Category',
        'all_items' => 'All Document Categories',
        'edit_item' => 'Edit Document Categories',
        'view_item' => 'View Document Category',
        'update_item' => 'Update Document Category',
        'add_new_item' => 'Add New Document Category',
      ),
      'public' => false,
      'show_ui' => true,
      'hierarchical' => true,
      'rewrite' => false,
      'capabilities' => array(
        'manage_terms', 'edit_terms', 'delete_terms', 'assign_terms'
      ),

    );
    register_taxonomy('document_category', array('document'), $args);
  }
}
