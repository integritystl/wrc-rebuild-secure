<?php

class articulate_license_manager
{
	public $file;
	public $item_name;
	public $item_shortname;
	public $version;
	public $author;
	public $api_url;
	public $license_option_name;
	public $license_status_option_name;
	public $license_page_slug;

	function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null ) {

		$this->file           = $_file;
		$this->item_name      = $_item_name;
		$this->item_shortname =  $_optname;
		$this->version        = $_version;
		$this->author         = $_author;
		$this->api_url        =$_api_url;

		$this->license_option_name =  $_optname;
		$this->license_status_option_name =  $_optname."_license_status";
		
		$this->license_page_slug = 'articulate-license';
		$this->setting_name = 'articulate_license';

		add_action( 'admin_init', array( $this, 'plugin_updater') , 0 );
		add_action('iea_admin_menu', array( $this, 'license_menu' ) );
		add_action('admin_init', array( $this, 'deactivate_license' ) ) ;
		add_action('admin_init', array( $this, 'register_option') );
		add_action('admin_init', array( $this, 'activate_license') );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_notices', array( $this, 'license_expired_notice' ) );	
		add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );
		add_filter("articulate_shortcode_output", array( $this, 'add_powered_by_text_for_expired_license' ) , 100, 1 );
		
	}//end function 

	public function plugin_updater(){

		// retrieve our license key from the DB
		$license_key = trim( get_option( $this->license_option_name ) );

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( $this->api_url, $this->file, array(
				'version' 	=> $this->version, 				// current version number
				'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
				'item_name' => $this->item_name, 	// name of this plugin
				'author' 	=> $this->author,  // author of this plugin
				'beta'		=> false
			)
		);

	}
	

	/************************************
	* the code below is just a standard
	* options page. Substitute with
	* your own.
	*************************************/

	public function license_menu(){
		add_submenu_page ( 'articulate_content', __('License', 'quiz'),  __('License', 'quiz'), "manage_options", $this->license_page_slug, array( $this, 'license_page') );
	}
	

	public function license_page(){
		$license = get_option( $this->license_option_name );
		
		if( $license != "" )
		{
			$this->check_license();
		}
		
		$status  = get_option( $this->license_status_option_name );

		?>
		<div class="Xwrap">
			<h2 class="header"><?php _e('License', 'quiz'); ?></h2>
			<p>Insert or Embed Articulate Content into WordPress Premium</p>
			<form method="post" action="options.php">

				<?php settings_fields( $this->setting_name ); ?>

				<table class="wp-list-table widefat fixed posts">
					<tbody>
						<tr valign="top">
							<th scope="row" valign="top" width="200">
								<?php _e('License Status', 'quiz'); ?>
							</th>
							<td>

								<?php 
								if( $status != "" )
								{
									switch($status)
									{
										case "expired":
										{
											if($license==""){echo '<span style="color:red;">'.__('Empty', 'quiz').'</span>';}else{echo '<span style="color:red;">'.__('Your license has expired. Please renew your license.', 'quiz').'</span>';}
										break;
										}
										case "revoked":
										{
										echo '<span style="color:red;">'.__('Your license key has been disabled.  Please contact support.', 'quiz').'</span>';
										break;
										}
										case "inactive":
										{
											echo '<span style="color:red;">'.__('Inactive', 'quiz').'</span>';
										break;
										}
																		
										case "valid":
										{
											echo '<span style="color:green;">'.__('Valid', 'quiz').'</span>';
										break;
										}

										case 'invalid':
										{
											echo '<span style="color:red;">'.__('Invalid', 'quiz').'</span>';
										break;
										}
										case 'no_activations_left':
										{
										echo '<span style="color:red;">'.__('Your license key has reached its activation limit. Please purchase additional licenses.', 'quiz').'</span>';
										break;
										}								
										case 'disabled':
										{
											echo '<span style="color:red;">'.__('Disabled', 'quiz').'</span>';
										break;
										}
										case "site_inactive":
										{
											echo '<span style="color:red;">'.__('Your license is not active for this URL.', 'quiz').'</span>';
										break;
										}
										default:
										{
											echo '<span style="color:red;">'.__('Unknown error occured.  Please try again or contact support.', 'quiz').'</span>';
											break;
										}								
																	
									} 
								}
								else
								{
									if( $license == "")
									{
										echo __('No license key found', 'quiz');
									}
									else
									{
										echo __('License key has not been applied yet', 'quiz');
									}
								}
								?>
								
							</td>
						</tr>

						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('License Key', 'quiz'); ?>
							</th>
							<td>
								<input id="<?php echo $this->license_option_name ?>" name="<?php echo $this->license_option_name ?>" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" <?php if( $status !== false && $status == 'valid' ) { ?> readonly <?php } ?> />
								<label class="description" for="<?php echo $this->license_option_name ?>"><?php _e('Enter your license key', 'quiz'); ?></label>
							</td>
						</tr>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php _e('Activate License', 'quiz'); ?>
								</th>
								<td>
									<?php if( $status !== false && $status == 'valid' ) { ?>
										<?php wp_nonce_field( 'articulate_license_nonce', 'articulate_license_nonce' ); ?>
										<input type="submit" class="waves-effect btn" name="articulate_license_deactivate" value="<?php echo esc_attr(__('Deactivate License', 'quiz')); ?>"/>
									<?php } else {
										wp_nonce_field( 'articulate_license_nonce', 'articulate_license_nonce' ); ?>
										<input type="submit" class="waves-effect btn" name="articulate_license_activate" value="<?php echo esc_attr(__('Activate License', 'quiz')); ?>"/>
									<?php } ?>
								</td>
							</tr>
					</tbody>
				</table>
				<!-- 
				<p class="submit">
					<input name="submit" id="submit" class="waves-effect btn" value="Save Changes" type="submit">
				</p>
				-->

			</form>
		</div>	
		<?php
	}

	public function register_option() {
		// creates our settings in the options table
		register_setting($this->setting_name, $this->license_option_name, array( $this, 'sanitize_license' ) );
	}
	

	public function sanitize_license( $new ) {
		$old = get_option( $this->license_option_name );
		if( $old && $old != $new ) {
			delete_option( $this->license_status_option_name ); // new license has been entered, so must reactivate
		}
		return $new;
	}



	/************************************
	* this illustrates how to activate
	* a license key
	*************************************/

	public function activate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['articulate_license_activate'] ) ) {

			// run a quick security check
		 	if( ! check_admin_referer( 'articulate_license_nonce', 'articulate_license_nonce' ) )
				return; // get out if we didn't click the Activate button

			//save submitted license key first
			if( isset( $_POST[$this->license_option_name] ) )
			{
				$new_key = $this->sanitize_license( $_POST[$this->license_option_name] );
				update_option( $this->license_option_name , $new_key );
			}

			// retrieve the license from the database
			$license = trim( get_option( $this->license_option_name ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'quiz' );
				}

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					switch( $license_data->error ) {

						case 'expired' :

							$message = sprintf(
								__( 'Your license key expired on %s.', 'quiz'),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'revoked' :

							$message = __( 'Your license key has been disabled.', 'quiz' );
							break;

						case 'missing' :

							$message = __( 'Invalid license.', 'quiz' );
							break;

						case 'invalid' :
						case 'site_inactive' :

							$message = __( 'Your license is not active for this URL.', 'quiz' );
							break;

						case 'item_name_mismatch' :

							$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'quiz' ), $this->item_name );
							break;

						case 'no_activations_left':

							$message = __( 'Your license key has reached its activation limit.', 'quiz' );
							break;

						default :

							$message = __( 'An error occurred, please try again.', 'quiz' );
							break;
					}

				}

			}

			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				$base_url = admin_url( 'admin.php?page=' . $this->license_page_slug );
				$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

				wp_redirect( $redirect );
				exit();
			}

			// $license_data->license will be either "valid" or "invalid"

			update_option( $this->license_status_option_name, $license_data->license );
			wp_redirect( admin_url( 'admin.php?page=' . $this->license_page_slug ) );
			exit();
		}
	}
	


	/***********************************************
	* Illustrates how to deactivate a license key.
	* This will decrease the site count
	***********************************************/

	public function deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['articulate_license_deactivate'] ) ) {

			// run a quick security check
		 	if( ! check_admin_referer( 'articulate_license_nonce', 'articulate_license_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( $this->license_option_name ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'quiz' );
				}

				$base_url = admin_url( 'admin.php?page=' . $this->license_page_slug );
				$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

				wp_redirect( $redirect );
				exit();
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				delete_option( $this->license_status_option_name );
			}

			wp_redirect( admin_url( 'admin.php?page=' . $this->license_page_slug ) );
			exit();

		}
	}
	


	/************************************
	* this illustrates how to check if
	* a license key is still valid
	* the updater does this for you,
	* so this is only needed if you
	* want to do something custom
	*************************************/

	public function check_license(){

		global $wp_version;

		$license = trim( get_option( $this->license_option_name ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $license,
			'item_name' => urlencode( $this->item_name ),
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		update_option( $this->license_status_option_name, $license_data->license );

		return $license_data->license;
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 */
	public function admin_notices() {
		if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

			switch( $_GET['sl_activation'] ) {

				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="error">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;

				case 'true':
				default:
					// Developers can put a custom success message here for when activation is successful if they way.
					break;

			}
		}
	}

	public function get_license_status()
	{
		return get_option( $this->license_status_option_name );
	}

	public function plugin_row_license_missing( $plugin_data, $version_info ){
		$license_status = $this->get_license_status();
		if( 'expired' == $license_status || 'disabled' == $license_status ) {
			echo '&nbsp;'.__('Automatic update is unavailable for this plugin.', 'quiz').'&nbsp;<strong><a href="' . esc_url( admin_url( 'admin.php?page=articulate-license' ) ) . '">'.__('Enter valid license key for automatic updates.', 'quiz').'</a></strong>';
		}

	}

	public function license_expired_notice()
	{
		$license_status = $this->get_license_status();
		if( 'expired' == $license_status || 'disabled' == $license_status ) 
		{
		?>
		<div class="error">
			<p><?php echo sprintf(__('Your license for %s has expired. Automatic updates and access to our support system have been disabled. Please %s to renew your license. Until your license is renewed, you will see <strong>"Powered by elearningfreak.com"</strong> underneath your content.', 'quiz'), '<strong>"Insert or Embed Articulate Content into WordPress Premium"</strong>', '<a href="https://www.elearningfreak.com/contact-us" target="_blank" />'.__('Contact Support', 'quiz').'</a>' )?></p>
		</div>
		<?php 
		}
	}

	public function add_powered_by_text_for_expired_license( $shortcode_output )
	{
		$license_status = $this->get_license_status();
		if( 'expired' == $license_status || 'disabled' == $license_status)
		{
			$shortcode_output = $shortcode_output.'<div style="margin-top:5px; margin-bottom:5px;"><a href="https://www.elearningfreak.com" target="_blank">Powered by elearningfreak.com</a></div>';
		}
		return $shortcode_output;
	}
	

}//end class