<?php
namespace WRCInfrastructure\CustomPostTypes;

class LearningLibraryCPT{
	public static function setupLearningLibrary(){
		self::createLearningLibraryPostType();
		self::createLibraryFields();
		// self::createListFields();
		self::registerLearningLibraryTaxonomy ();
		self::registerLearningLibraryTagTaxonomy();
	}

	private static function createLearningLibraryPostType(){
		register_post_type( 'learning-library',
			array(
		        'labels' => array(
		          'name' => __( 'Learning Library' ),
		          'singular_name' => __( 'Learning Library' )
		        ),
				'public' => true,
				'exclude_from_search' => false,
		        'show_in_menu' => true,
		        'show_in_rest' => true,
		        'has_archive' => true,
		        'menu_icon' => 'dashicons-welcome-learn-more',
		        'hierarchical' => true,
		        'supports' => array('editor', 'title', 'thumbnail', 'revisions', 'page-attributes','excerpt')
	      	)
		);
	}

	private static function createLibraryFields(){
		if( function_exists('acf_add_local_field_group') ):

			acf_add_local_field_group(array(
				'key' => 'group_5d92229621057',
				'title' => 'Library Launch URL',
				'fields' => array(
					array(
						'key' => 'field_5d9222bc7820c',
						'label' => 'Launch URL',
						'name' => 'launch_url',
						'type' => 'text',
						'instructions' => 'Get the URL generate by the A Insert. The URL is in the short code added to the content area after uploading the zip file. Copy everything inside href=\'copy_everything_inside_these_quotes\'. Then paste in this field.',
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
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'learning-library',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));

			acf_add_local_field_group(array(
				'key' => 'group_5c9d15f815f62',
				'title' => 'Learning Library',
				'fields' => array(
					array(
						'key' => 'field_5c9d162b081fa',
						'label' => 'Intro Content',
						'name' => 'intro_content',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 1,
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
						'key' => 'field_5c9d1688081fb',
						'label' => 'Attached Site',
						'name' => 'attached_site',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'site',
						),
						'taxonomy' => '',
						'allow_null' => 0,
						'multiple' => 1,
						'return_format' => 'object',
						'ui' => 1,
					),
					array(
						'key' => 'field_5c9d16b4081fc',
						'label' => 'Related Videos',
						'name' => 'related_videos',
						'type' => 'relationship',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'learning-library',
						),
						'taxonomy' => '',
						'filters' => array(
							0 => 'search',
							1 => 'post_type',
							2 => 'taxonomy',
						),
						'elements' => '',
						'min' => '',
						'max' => 3,
						'return_format' => 'object',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'learning-library',
						),
					),
				),
				'menu_order' => 1,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));

			endif;
	}

	public static function registerLearningLibraryTaxonomy(){
    $args = array (
      'labels' => array(
        'name' => 'Learning Categories',
        'singular_name' => 'Learning Category',
        'all_items' => 'Learning Categories',
        'edit_item' => 'Edit Learning Categories',
        'view_item' => 'View Learning Category',
        'update_item' => 'Update Learning Category',
        'add_new_item' => 'Add Learning Category',
      ),
      'public' => false,
      'show_ui' => true,
      'hierarchical' => true,
      'rewrite' => false,
      'capabilities' => array(
        'manage_terms', 'edit_terms', 'delete_terms', 'assign_terms'
      ),

    );
    register_taxonomy('learning_category', array('learning-library'), $args);
  }

  public static function registerLearningLibraryTagTaxonomy(){
    $args = array (
      'labels' => array(
        'name' => 'Learning Tags',
        'singular_name' => 'Learning Tag',
        'all_items' => 'Learning Tag',
        'edit_item' => 'Edit Learning Tag',
        'view_item' => 'View Learning Tag',
        'update_item' => 'Update Learning Tag',
        'add_new_item' => 'Add Learning Tag',
      ),
      'public' => false,
      'show_ui' => true,
      'hierarchical' => true,
      'rewrite' => false,
      'capabilities' => array(
        'manage_terms', 'edit_terms', 'delete_terms', 'assign_terms'
      ),

    );
    register_taxonomy('learning_tag', array('learning-library'), $args);
  }
}




