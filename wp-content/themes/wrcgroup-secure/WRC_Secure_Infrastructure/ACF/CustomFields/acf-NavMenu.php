<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;
// check if class already exists
if( !class_exists('acf_field_Nav_Menu') && class_exists('acf_field')) :
class acf_field_Nav_Menu extends acf_field
{
	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options

	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/

	function __construct()
	{
		// vars
		$this->name = 'nav_menu';
		$this->label = __('Nav Menu', 'acf-NavMenu');
		$this->category = 'WRC';
    $this->defaults = array(
      'font_size' => 14,
    );

    /*
    *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
    *  var message = acf._e('Agency', 'error');
    */
    $this->l10n = array(
      'error' => __('Error! Please enter a higher value', 'acf-NavMenu'),
    );

    // settings
		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0'
		);

		// do not delete!
		parent::__construct();
	}

	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/

	function render_field_settings( $field )
	{
    acf_render_field_setting( $field, array(
      'label'     => __('Font Size','acf-NavMenu'),
      'instructions'  => __('Customise the input font size','acf-NavMenu'),
      'type'      => 'number',
      'name'      => 'font_size',
      'prepend'   => 'px',
    ));

    acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf-NavMenu'),
			'type'		=>	'radio',
			'name'		=> 'save_format',
			'layout'	=>	'horizontal',
			'choices' 	=>	array(
				'object'	=>	__("Nav Menu Object",'acf-NavMenu'),
				'menu'		=>	__("Nav Menu HTML",'acf-NavMenu'),
				'id'		=>	__("Nav Menu ID",'acf-NavMenu')
			)
		));

    acf_render_field_setting( $field, array(
			'label'			=> __('Menu Container','acf'),
			'instructions'	=> __('What to wrap the Menu\'s ul with.<br />Only used when returning HTML','acf-NavMenu'),
			'type'		=>	'select',
			'name'		=> 'container',
			'choices' 	=>	array('div', 'nav')
		));
	}


	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	function render_field( $field )
	{
		// create Field HTML
		echo sprintf( '<select id="%d" class="%s" name="%s">', $field['id'], $field['class'], $field['name']  );

		// null
		if( $field['allow_null'] )
		{
			echo '<option value=""> - Select - </option>';
		}

		// Nav Menus
		$nav_menus = $this->get_nav_menus();

		foreach( $nav_menus as $nav_menu_id => $nav_menu_name ) {
			$selected = selected( $field['value'], $nav_menu_id );
			echo sprintf( '<option value="%1$d" %3$s>%2$s</option>', $nav_menu_id, $nav_menu_name, $selected );
		}

		echo '</select>';
	}

	function get_nav_menus() {
		$navs = get_terms('nav_menu', array( 'hide_empty' => false ) );

		$nav_menus = array();
		foreach( $navs as $nav ) {
			$nav_menus[ $nav->term_id ] = $nav->name;
		}

		return $nav_menus;
	}

	function get_allowed_nav_container_tags() {
		$tags = apply_filters( 'wp_nav_menu_container_allowedtags', array( 'div', 'nav' ) );
		$formatted_tags = array(
			array( '0' => 'None' )
		);
		foreach( $tags as $tag ) {
    		$formatted_tags[0][$tag] = ucfirst( $tag );
		}
		return $formatted_tags;
	}

}

// create field
new acf_field_Nav_Menu();
// class_exists check
endif;
?>
