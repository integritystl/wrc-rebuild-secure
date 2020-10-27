<?php
/**
 * View: Connection Tab
 *
 * External DB connection tab view.
 *
 * @package wsal
 * @subpackage external-db
 * @since 3.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Ext_Plugin' ) ) {
	exit( esc_html__( 'You are not allowed to view this page.', 'wp-security-audit-log' ) );
}

/**
 * External DB connection tab class.
 *
 * @package wsal
 * @subpackage external-db
 */
final class WSAL_Ext_Connections {

	/**
	 * Instance of WSAL.
	 *
	 * @var WpSecurityAuditLog
	 */
	private $wsal;

	/**
	 * Contructor.
	 *
	 * @param WpSecurityAuditLog $wsal – Instance of WSAL.
	 */
	public function __construct( WpSecurityAuditLog $wsal ) {
		$this->wsal     = $wsal;
		$this->base_url = trailingslashit( WSAL_BASE_URL ) . 'extensions/external-db/';
		$this->base_dir = trailingslashit( WSAL_BASE_DIR ) . 'extensions/external-db/';

		add_action( 'wsal_ext_db_header', array( $this, 'enqueue_styles' ) );
		add_action( 'wsal_ext_db_footer', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_wsal_delete_connection', array( $this, 'delete_connection' ), 10 );
		add_action( 'wp_ajax_wsal_connection_test', array( $this, 'test_connection' ), 10 );
		add_action( 'wp_ajax_wsal_wizard_connection_test', array( $this, 'wizard_connection_test' ), 10 );
		add_action( 'admin_init', array( $this, 'save' ) );
	}

	/**
	 * Tab Connections Render.
	 */
	public function render() {
		// @codingStandardsIgnoreStart
		$page       = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
		$tab        = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : false;
		$connection = isset( $_GET['connection'] ) ? sanitize_text_field( wp_unslash( $_GET['connection'] ) ) : false;
		// @codingStandardsIgnoreEnd

		// Check if configuring a connection.
		if ( ! empty( $page ) && ! empty( $tab ) && ! empty( $connection ) && 'wsal-ext-settings' === $page && 'connections' === $tab ) :
			$this->configure_connection( $connection );
		else :
			// Get connections.
			$connections = $this->wsal->GetNotificationsSetting( WSAL_CONN_PREFIX );
			?>
			<?php
			$allowed_tags     = array(
				'a' => array(
					'href'   => true,
					'target' => true,
				),
			);
			$description_text = sprintf(
				/* translators: 1- a string wrapped in a link saying to create or configure databases. */
				__( 'In this section you can %1$s and services connections. These connections can be used as external activity log storage, activity log archive or to mirror the activity log to it. In use connections cannot be deleted.', 'wp-security-audit-log' ),
				sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( 'https://wpactivitylog.com/support/kb/getting-started-external-databases-third-party-services/' ),
					__( 'create and configure databases', 'wp-security-audit-log' )
				)
			);
			?>
			<p class="description"><?php echo wp_kses( $description_text, $allowed_tags ); ?></p>
			<p><button id="wsal-create-connection" class="button button-hero button-primary"><?php esc_html_e( 'Create a Connection', 'wp-security-audit-log' ); ?></button></p>
			<!-- Create a Connection -->
			<h3><?php esc_html_e( 'Connections', 'wp-security-audit-log' ); ?></h3>
			<table id="wsal-external-connections" class="wp-list-table widefat fixed striped logs">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Name', 'wp-security-audit-log' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Type', 'wp-security-audit-log' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Used for', 'wp-security-audit-log' ); ?></th>
						<th scope="col"></th>
						<th scope="col"></th>
						<th scope="col"></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $connections ) : ?>
						<tr class="no-items">
							<td class="colspanchange" colspan="6"><?php esc_html_e( 'No connections so far.', 'wp-security-audit-log' ); ?></td>
						</tr>
						<?php
					else :
						foreach ( $connections as $connection ) :
							$details       = maybe_unserialize( $connection->option_value );
							$conf_args     = array(
								'page'       => 'wsal-ext-settings',
								'tab'        => 'connections',
								'connection' => $details->name,
							);
							$configure_url = add_query_arg( $conf_args, network_admin_url( 'admin.php' ) );
							?>
							<tr>
								<td><?php echo isset( $details->name ) ? esc_html( $details->name ) : false; ?></td>
								<td><?php echo isset( $details->type ) ? esc_html( $details->type ) : false; ?></td>
								<td><?php echo isset( $details->used_for ) ? esc_html( $details->used_for ) : false; ?></td>
								<td><a href="<?php echo esc_url( $configure_url ); ?>" class="button-primary"><?php esc_html_e( 'Configure', 'wp-security-audit-log' ); ?></a></td>
								<!-- Configure -->
								<td>
									<?php

									/*
									 * Sets the text to use for the test button.
									 *
									 * For syslog it's not correct to imply that
									 * a full test was completed since connect
									 * is UDP.
									 */
									if ( 'syslog' === $details->type ) {
										$button_text = __( 'Send a test message', 'wp-security-audit-log' );
									} else {
										$button_text = __( 'Test', 'wp-security-audit-log' );
									}
									?>
									<a href="javascript:;" data-connection="<?php echo esc_attr( $connection->option_name ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( $connection->option_name . '-test' ) ); ?>" class="button button-secondary wsal-conn-test"><?php echo esc_html( $button_text ); ?></a>
								</td>
								<!-- Test -->
								<td><button type="button" data-connection="<?php echo esc_attr( $connection->option_name ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( $connection->option_name . '-delete' ) ); ?>" class="button button-danger wsal-conn-delete" <?php echo ( isset( $details->used_for ) && ! empty( $details->used_for ) ) ? 'disabled' : false; ?>><?php esc_html_e( 'Delete', 'wp-security-audit-log' ); ?></button></td>
								<!-- Delete -->
							</tr>
							<?php
						endforeach;
					endif;
					?>
				</tbody>
			</table>
			<?php
			// Create connection wizard.
			$this->wizard();
		endif;
	}

	/**
	 * Connection Wizard
	 *
	 * @param string $connection – Connection name.
	 */
	private function wizard( $connection = '' ) {
		// Check connection paramter.
		$details = '';
		if ( $connection ) {
			// Get connection setting.
			$details = $this->wsal->wsalCommonClass->GetSettingByName( WSAL_CONN_PREFIX . $connection );
		}
		?>
		<div id="wsal-connection-wizard" class="hidden">
			<div class="wsal-logo"><a href="https://wpactivitylog.com" target="_blank"><img src="<?php echo esc_url( trailingslashit( WSAL_BASE_URL ) ); ?>img/wsal-logo-full.png" alt="<?php esc_attr_e( 'WP Activity Log', 'wp-security-audit-log' ); ?>" /></a></div>
			<ul class="steps">
				<li id="step-1" class="is-active"><?php esc_html_e( 'Step 1', 'wp-security-audit-log' ); ?></li>
				<li id="step-2"><?php esc_html_e( 'Step 2', 'wp-security-audit-log' ); ?></li>
				<li id="step-3"><?php esc_html_e( 'Step 3', 'wp-security-audit-log' ); ?></li>
			</ul>
			<!-- Steps -->
			<div class="content">
				<form method="POST">
					<?php wp_nonce_field( 'wsal-connection-wizard' ); ?>
					<div id="content-step-1">
						<h3><?php esc_html_e( 'Select the type of connection', 'wp-security-audit-log' ); ?></h3>
						<p class="description"><?php esc_html_e( 'Select the type of connection you would like to setup.', 'wp-security-audit-log' ); ?></p>
						<table class="form-table">
							<tbody>
								<tr>
									<th><label for="connection-type"><?php esc_html_e( 'Type of Connection', 'wp-security-audit-log' ); ?></label></th>
									<td>
										<fieldset>
											<select name="connection[type]" id="connection-type">
												<option <?php isset( $details->type ) ? selected( $details->type, 'mysql' ) : false; ?> value="mysql"><?php esc_html_e( 'MySQL Database', 'wp-security-audit-log' ); ?></option>
												<option <?php isset( $details->type ) ? selected( $details->type, 'syslog' ) : false; ?> value="syslog"><?php esc_html_e( 'Syslog Server', 'wp-security-audit-log' ); ?></option>
												<option <?php isset( $details->type ) ? selected( $details->type, 'slack' ) : false; ?> value="slack"><?php esc_html_e( 'Slack', 'wp-security-audit-log' ); ?></option>
												<option <?php isset( $details->type ) ? selected( $details->type, 'papertrail' ) : false; ?> value="papertrail"><?php esc_html_e( 'Papertrail', 'wp-security-audit-log' ); ?></option>
											</select>
										</fieldset>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div id="content-step-2">
						<?php $this->connection_details( $details ); ?>
					</div>
					<div id="content-step-3">
						<h3><?php esc_html_e( 'Name the connection', 'wp-security-audit-log' ); ?></h3>
						<p class="description"><?php esc_html_e( 'Please specify a friendly name for the connection. Connection names can be 25 characters long and can only contain letters, numbers and underscores.', 'wp-security-audit-log' ); ?></p>
						<?php $this->get_connection_field( $details, 'name' ); // Get connection name field. ?>
					</div>
					<div class="content-btns">
						<input type="submit" class="button button-primary" id="wizard-save" value="<?php esc_attr_e( 'Save Connection', 'wp-security-audit-log' ); ?>" name="submit" />
						<input type="button" class="button button-primary" id="wizard-next" value="<?php esc_attr_e( 'Next', 'wp-security-audit-log' ); ?>" />
						<span id="test-spinner" class="spinner" style="display: none; float: none; margin: 0; margin-top: 16px;"></span>
						<input type="button" class="button button-secondary" id="wizard-cancel" value="<?php esc_attr_e( 'Cancel', 'wp-security-audit-log' ); ?>" />
					</div>
					<p class="wizard-error hide"></p>
				</form>
			</div>
		</div>
		<!-- Create Connection Modal -->
		<?php
	}

	/**
	 * Connection Detail Fields.
	 *
	 * @param stdClass $details – Connection details.
	 */
	private function connection_details( $details = '' ) {
		?>
		<h3><?php esc_html_e( 'Configure the connection', 'wp-security-audit-log' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Configure the connection details.', 'wp-security-audit-log' ); ?></p>
		<?php
		$this->get_connection_field( $details, 'mysql' );
		$this->get_connection_field( $details, 'papertrail' );
		$this->get_connection_field( $details, 'syslog' );
		$this->get_connection_field( $details, 'slack' );
	}

	/**
	 * Configure Connection View.
	 *
	 * @param string $conn_name - Connection name.
	 */
	private function configure_connection( $conn_name ) {
		if ( ! $conn_name ) {
			esc_html_e( 'No connection name specified!', 'wp-security-audit-log' );
			return;
		}

		// Get connection setting.
		$connection = $this->wsal->wsalCommonClass->GetSettingByName( WSAL_CONN_PREFIX . $conn_name );
		?>
		<h1><?php echo esc_html__( 'Configure Connection → ', 'wp-security-audit-log' ) . esc_html( $connection->name ); ?></h1>
		<br>
		<form method="POST">
			<?php
			wp_nonce_field( 'wsal-connection-configure' );
			$this->get_connection_field( $connection, 'name' ); // Get connection name field.
			?>
			<h3><?php esc_html_e( 'Configure the connection', 'wp-security-audit-log' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Configure the connection details.', 'wp-security-audit-log' ); ?></p>
			<?php
			// Get connection details fields.
			if ( 'mysql' === $connection->type ) {
				$this->get_connection_field( $connection, 'mysql' );
			} elseif ( 'papertrail' === $connection->type ) {
				$this->get_connection_field( $connection, 'papertrail' );
			} elseif ( 'syslog' === $connection->type ) {
				$this->get_connection_field( $connection, 'syslog' );
			} elseif ( 'slack' === $connection->type ) {
				$this->get_connection_field( $connection, 'slack' );
			}
			?>
			<input type="hidden" name="connection[type]" value="<?php echo esc_attr( $connection->type ); ?>" />
			<input type="hidden" name="connection[update]" value="1" />
			<?php submit_button( 'Save Connection' ); ?>
		</form>
		<?php
	}

	/**
	 * Get Connection Field.
	 *
	 * @param mixed  $connection - Connection details.
	 * @param string $type       - Type of connection field.
	 */
	public function get_connection_field( $connection, $type ) {
		if ( 'name' === $type ) :
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="connection-name"><?php esc_html_e( 'Connection Name', 'wp-security-audit-log' ); ?></label></th>
						<td>
							<fieldset>
								<input type="text" name="connection[name]" id="connection-name" value="<?php echo isset( $connection->name ) ? esc_attr( $connection->name ) : false; ?>" />
								<span class="description error"><?php esc_html_e( '* Invalid Connection Name', 'wp-security-audit-log' ); ?></span>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		elseif ( 'mysql' === $type ) :
			?>
			<div class="details-mysql">
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="db-name"><?php esc_html_e( 'Database Name', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<input type="text" name="connection[mysql][dbName]" id="db-name" data-type="required" value="<?php echo isset( $connection->db_name ) ? esc_attr( $connection->db_name ) : false; ?>" />
									<p class="description"><?php esc_html_e( 'Specify the name of the database where you will store the WordPress activity log.', 'wp-security-audit-log' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th><label for="db-user"><?php esc_html_e( 'Database User', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<input type="text" name="connection[mysql][dbUser]" id="db-user" data-type="required" value="<?php echo isset( $connection->user ) ? esc_attr( $connection->user ) : false; ?>" />
									<p class="description"><?php esc_html_e( 'Specify the username to be used to connect to the database.', 'wp-security-audit-log' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th><label for="db-password"><?php esc_html_e( 'Database Password', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<input type="password" name="connection[mysql][dbPassword]" id="db-password" data-type="required" />
									<p class="description"><?php esc_html_e( 'Specify the password each time you want to submit new changes. For security reasons, the plugin does not store the password in this form.', 'wp-security-audit-log' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th><label for="db-hostname"><?php esc_html_e( 'Database Hostname', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<input type="text" name="connection[mysql][dbHostname]" id="db-hostname" data-type="required" value="<?php echo isset( $connection->hostname ) ? esc_attr( $connection->hostname ) : false; ?>" />
									<p class="description"><?php esc_html_e( 'Specify the hostname or IP address of the database server.', 'wp-security-audit-log' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th><label for="db-base-prefix"><?php esc_html_e( 'Database Base Prefix', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<input type="text"
										name="connection[mysql][dbBasePrefix]"
										id="db-base-prefix"
										data-type="required"
										value="<?php echo isset( $connection->baseprefix ) ? esc_attr( $connection->baseprefix ) : false; ?>"
										<?php echo ( isset( $connection->url_prefix ) && '1' === $connection->url_prefix ) ? 'disabled' : false; ?>
									/>
									<p class="description"><?php esc_html_e( 'Specify a prefix for the database tables of the activity log. Ideally this prefix should be different from the one you use for WordPress so it is not guessable.', 'wp-security-audit-log' ); ?></p>
									<br>
									<label for="db-url-base-prefix">
										<input type="checkbox" name="connection[mysql][dbUrlBasePrefix]" id="db-url-base-prefix" value="1" <?php isset( $connection->url_prefix ) ? checked( $connection->url_prefix ) : false; ?> />
										<?php esc_html_e( 'Use website URL as table prefix', 'wp-security-audit-log' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th><label for="db-ssl"><?php esc_html_e( 'SSL', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<label for="db-ssl">
										<input type="checkbox" name="connection[mysql][dbSSL]" id="db-ssl" value="1" <?php isset( $connection->is_ssl ) ? checked( $connection->is_ssl ) : false; ?> />
										<?php esc_html_e( 'Enable to use SSL to connect with the MySQL server.', 'wp-security-audit-log' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th><label for="db-ssl-cc"><?php esc_html_e( 'Client Certificate', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<label for="db-ssl-cc">
										<input type="checkbox" name="connection[mysql][sslCC]" id="db-ssl-cc" value="1" <?php isset( $connection->is_cc ) ? checked( $connection->is_cc ) : false; ?> />
										<?php esc_html_e( 'Enable to use SSL to connect with the MySQL server.', 'wp-security-audit-log' ); ?>
									</label>
								</fieldset>
								<fieldset>
									<input type="text" name="connection[mysql][sslCA]" id="db-ssl-ca" placeholder="<?php esc_attr_e( 'CA SSL Certificate (--ssl-ca)', 'wp-security-audit-log' ); ?>" value="<?php echo isset( $connection->ssl_ca ) ? esc_attr( $connection->ssl_ca ) : false; ?>" />
								</fieldset>
								<fieldset>
									<input type="text" name="connection[mysql][sslCert]" id="db-ssl-cert" placeholder="<?php esc_attr_e( 'Server SSL Certificate (--ssl-cert)', 'wp-security-audit-log' ); ?>" value="<?php echo isset( $connection->ssl_cert ) ? esc_attr( $connection->ssl_cert ) : false; ?>" />
								</fieldset>
								<fieldset>
									<input type="text" name="connection[mysql][sslKey]" id="db-ssl-key" placeholder="<?php esc_attr_e( 'Client Certificate (--ssl-key)', 'wp-security-audit-log' ); ?>" value="<?php echo isset( $connection->ssl_key ) ? esc_attr( $connection->ssl_key ) : false; ?>" />
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
		elseif ( 'papertrail' === $type ) :
			?>
			<div class="details-papertrail">
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="papertrail-dest"><?php esc_html_e( 'Destination', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<input type="text" name="connection[papertrail][destination]" id="papertrail-dest"  value="<?php echo isset( $connection->destination ) ? esc_attr( $connection->destination ) : false; ?>" />
									<span class="description error"><?php esc_html_e( '* Invalid Papertrail Destination', 'wp-security-audit-log' ); ?></span>
									<p class="description">
										<?php
										echo sprintf(
											/* translators: %s: Log destinations link */
											esc_html__( 'Specify your destination. You can find your Papertrail Destination in the %s section of your Papertrail account page. It should have the following format: logs4.papertrailapp.com:54321', 'wp-security-audit-log' ),
											'<a href="https://papertrailapp.com/account/destinations" target="_blank">' . esc_html__( 'Log Destinations', 'wp-security-audit-log' ) . '</a>'
										);
										?>
									</p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th><label for="papertrail-colorization"><?php esc_html_e( 'Colorization', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<label for="papertrail-colorization">
										<input type="checkbox" name="connection[papertrail][colorization]" id="papertrail-colorization" <?php isset( $connection->colorization ) ? checked( $connection->colorization ) : false; ?> />
										<?php esc_html_e( 'Enable', 'wp-security-audit-log' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
		elseif ( 'syslog' === $type ) :
			?>
			<div class="details-syslog">
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="syslog-location"><?php esc_html_e( 'Syslog Location', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<?php $syslog_location = isset( $connection->location ) ? $connection->location : 'local'; ?>
									<label for="syslog-local">
										<input type="radio" name="connection[syslog][location]" id="syslog-local"  value="local" <?php checked( $syslog_location, 'local' ); ?> />
										<?php esc_html_e( 'Write to local syslog file', 'wp-security-audit-log' ); ?>
									</label>
									<br>
									<label for="syslog-remote">
										<input type="radio" name="connection[syslog][location]" id="syslog-remote"  value="remote" <?php checked( $syslog_location, 'remote' ); ?> />
										<?php esc_html_e( 'Send messages to remote syslog server', 'wp-security-audit-log' ); ?>
									</label>
									<br>
									<label for="syslog-remote-ip">
										<span class="syslog-remote-cover"><?php esc_html_e( 'IP Address / Hostname', 'wp-security-audit-log' ); ?></span>
										<input type="text" name="connection[syslog][remote-ip]" id="syslog-remote-ip" value="<?php echo isset( $connection->remote_ip ) ? esc_attr( $connection->remote_ip ) : false; ?>" />
										<br>
										<span class="description error"><?php esc_html_e( '* Invalid IP/Hostname', 'wp-security-audit-log' ); ?></span>
									</label>
									<br>
									<label for="syslog-remote-port">
										<span class="syslog-remote-cover"><?php esc_html_e( 'Port', 'wp-security-audit-log' ); ?></span>
										<input type="text" name="connection[syslog][remote-port]" id="syslog-remote-port" value="<?php echo isset( $connection->remote_port ) ? esc_attr( $connection->remote_port ) : false; ?>" />
										<br>
										<span class="description error"><?php esc_html_e( '* Invalid Port', 'wp-security-audit-log' ); ?></span>
									</label>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
		elseif ( 'slack' === $type ) :
			?>
			<div class="details-slack">
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="slack-bot"><?php esc_html_e( 'Bot Name', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<input type="text" name="connection[slack][bot-name]" id="slack-bot" value="<?php echo isset( $connection->bot_name ) ? esc_attr( $connection->bot_name ) : false; ?>" />
									<br>
									<span class="description error"><?php esc_html_e( '* Invalid Bot Name', 'wp-security-audit-log' ); ?></span>
									<p class="description"><?php esc_html_e( 'The name to be used in the slack channel for all the WordPress activity log events sent from the plugin.', 'wp-security-audit-log' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th><label for="slack-webhook-url"><?php esc_html_e( 'WebHook URL', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<input type="text" name="connection[slack][webhook-url]" id="slack-webhook-url" value="<?php echo isset( $connection->webhook_url ) ? esc_attr( $connection->webhook_url ) : false; ?>" />
									<br>
									<span class="description error"><?php esc_html_e( '* Invalid WebHook URL', 'wp-security-audit-log' ); ?></span>
									<p class="description">
										<?php
										/* Translators: Slack help HTML link. */
										echo sprintf( esc_html__( 'If you are not familiar with incoming WebHooks for Slack please refer to %s.', 'wp-security-audit-log' ), '<a href="https://get.slack.help/hc/en-us/articles/115005265063-Incoming-WebHooks-for-Slack" target="_blank">' . esc_html__( 'Slack help', 'wp-security-audit-log' ) . '</a>' );
										?>
									</p>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
		endif;
	}

	/**
	 * Enqueue tab scripts.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style(
			'wsal-connections-css',
			$this->base_url . 'css/wsal-ext-wizard.css',
			array(),
			filemtime( $this->base_dir . 'css/wsal-ext-wizard.css' )
		);
	}

	/**
	 * Enqueue tab scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery-ui-dialog' );

		// Connections script file.
		wp_register_script(
			'wsal-connections-js',
			$this->base_url . 'js/wsal-ext-wizard.js',
			array( 'jquery', 'jquery-ui-dialog' ),
			filemtime( $this->base_dir . 'js/wsal-ext-wizard.js' ),
			true
		);

		// @codingStandardsIgnoreStart
		$connection = isset( $_GET['connection'] ) ? sanitize_text_field( wp_unslash( $_GET['connection'] ) ) : false;
		// @codingStandardsIgnoreEnd

		$script_data = array(
			'wpNonce'       => wp_create_nonce( 'wsal-create-connections' ),
			'title'         => __( 'Connections Wizard', 'wp-security-audit-log' ),
			'connTest'      => __( 'Testing...', 'wp-security-audit-log' ),
			'deleting'      => __( 'Deleting...', 'wp-security-audit-log' ),
			'connFailed'    => __( 'Connection Failed!', 'wp-security-audit-log' ),
			'connSuccess'   => __( 'Connected', 'wp-security-audit-log' ),
			'confirm'       => __( 'Are you sure that you want to delete this connection?', 'wp-security-audit-log' ),
			'ajaxURL'       => admin_url( 'admin-ajax.php' ),
			'connection'    => $connection,
			'mirror'        => false,
			'urlBasePrefix' => $this->wsal->wsalCommonClass->get_url_base_prefix(),
			'testContinue'  => __( 'Test connection and continue', 'wp-security-audit-log' ),
			'buttonNext'    => __( 'Next', 'wp-security-audit-log' ),
		);
		wp_localize_script( 'wsal-connections-js', 'scriptData', $script_data );
		wp_enqueue_script( 'wsal-connections-js' );
	}

	/**
	 * Save Connections Form.
	 */
	public function save() {
		global $pagenow;

		// Only run the function on audit log custom page.
		// @codingStandardsIgnoreStart
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // Current page.
		// @codingStandardsIgnoreEnd

		if ( 'admin.php' !== $pagenow ) {
			return;
		} elseif ( 'wsal-ext-settings' !== $page ) { // Page is admin.php, now check auditlog page.
			return; // Return if the current page is not auditlog's.
		}

		// Check if submitting.
		if ( ! isset( $_POST['submit'] ) ) {
			return;
		}

		// Check nonce.
		if ( isset( $_POST['connection']['update'] ) ) {
			check_admin_referer( 'wsal-connection-configure' );
		} else {
			check_admin_referer( 'wsal-connection-wizard' );
		}

		// Get connection details.
		$type      = isset( $_POST['connection']['type'] ) ? sanitize_text_field( wp_unslash( $_POST['connection']['type'] ) ) : false;
		$details   = isset( $_POST['connection'][ $type ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['connection'][ $type ] ) ) : false;
		$conn_name = isset( $_POST['connection']['name'] ) ? sanitize_text_field( wp_unslash( $_POST['connection']['name'] ) ) : false;

		if ( 'mysql' === $type ) {
			$db_name       = isset( $details['dbName'] ) ? sanitize_text_field( wp_unslash( $details['dbName'] ) ) : false;
			$db_user       = isset( $details['dbUser'] ) ? sanitize_text_field( wp_unslash( $details['dbUser'] ) ) : false;
			$db_password   = isset( $details['dbPassword'] ) ? sanitize_text_field( wp_unslash( $details['dbPassword'] ) ) : false;
			$db_password   = $this->wsal->wsalCommonClass->EncryptPassword( $db_password );
			$db_hostname   = isset( $details['dbHostname'] ) ? sanitize_text_field( wp_unslash( $details['dbHostname'] ) ) : false;
			$db_baseprefix = isset( $details['dbBasePrefix'] ) ? sanitize_text_field( wp_unslash( $details['dbBasePrefix'] ) ) : false;
			$db_urlbasepre = isset( $details['dbUrlBasePrefix'] ) ? sanitize_text_field( wp_unslash( $details['dbUrlBasePrefix'] ) ) : false;
			$is_ssl        = isset( $details['dbSSL'] ) ? sanitize_text_field( wp_unslash( $details['dbSSL'] ) ) : false;
			$is_cc         = isset( $details['sslCC'] ) ? sanitize_text_field( wp_unslash( $details['sslCC'] ) ) : false;
			$ssl_ca        = isset( $details['sslCA'] ) ? sanitize_text_field( wp_unslash( $details['sslCA'] ) ) : false;
			$ssl_cert      = isset( $details['sslCert'] ) ? sanitize_text_field( wp_unslash( $details['sslCert'] ) ) : false;
			$ssl_key       = isset( $details['sslKey'] ) ? sanitize_text_field( wp_unslash( $details['sslKey'] ) ) : false;

			if ( ! empty( $db_urlbasepre ) ) {
				$db_baseprefix = $this->wsal->wsalCommonClass->get_url_base_prefix();
			}

			try {
				$result = WSAL_Connector_ConnectorFactory::CheckConfig( $type, $db_user, $db_password, $db_name, $db_hostname, $db_baseprefix, $is_ssl, $is_cc, $ssl_ca, $ssl_cert, $ssl_key );
				if ( true === $result ) {
					// Create the connection object.
					$connection             = new stdClass();
					$connection->name       = $conn_name;
					$connection->type       = $type;
					$connection->user       = $db_user;
					$connection->password   = $db_password;
					$connection->db_name    = $db_name;
					$connection->hostname   = $db_hostname;
					$connection->baseprefix = $db_baseprefix;
					$connection->url_prefix = $db_urlbasepre;
					$connection->is_ssl     = $is_ssl;
					$connection->is_cc      = $is_cc;
					$connection->ssl_ca     = $ssl_ca;
					$connection->ssl_cert   = $ssl_cert;
					$connection->ssl_key    = $ssl_key;

					// Install tables.
					$config = WSAL_Connector_ConnectorFactory::GetConfigArray( $connection->type, $connection->user, $connection->password, $connection->db_name, $connection->hostname, $connection->baseprefix, $connection->is_ssl, $connection->is_cc, $connection->ssl_ca, $connection->ssl_cert, $connection->ssl_key );
					$this->wsal->getConnector( $config )->installAll( true );
				}
			} catch ( Exception $ex ) {
				add_action( 'admin_notices', array( $this, 'connection_failed_notice' ), 10 );
				return;
			}
		} elseif ( 'papertrail' === $type ) {
			$destination  = isset( $details['destination'] ) ? sanitize_text_field( wp_unslash( $details['destination'] ) ) : false;
			$colorization = ( isset( $details['colorization'] ) && 'on' === $details['colorization'] ) ? true : false;

			// Create the connection object.
			$connection               = new stdClass();
			$connection->name         = $conn_name;
			$connection->type         = $type;
			$connection->destination  = $destination;
			$connection->colorization = $colorization;
		} elseif ( 'syslog' === $type ) {
			// Connection details.
			$location    = isset( $details['location'] ) ? sanitize_text_field( wp_unslash( $details['location'] ) ) : false;
			$remote_ip   = isset( $details['remote-ip'] ) ? sanitize_text_field( wp_unslash( $details['remote-ip'] ) ) : false;
			$remote_port = isset( $details['remote-port'] ) ? sanitize_text_field( wp_unslash( $details['remote-port'] ) ) : false;

			// Create the connection object.
			$connection           = new stdClass();
			$connection->name     = $conn_name;
			$connection->type     = $type;
			$connection->location = $location;

			// Remote IP.
			if ( ! empty( $remote_ip ) ) {
				$connection->remote_ip = $remote_ip;
			}

			// Remote Port.
			if ( ! empty( $remote_port ) ) {
				$connection->remote_port = $remote_port;
			}
		} elseif ( 'slack' === $type ) {
			// Connection details.
			$bot_name    = isset( $details['bot-name'] ) ? sanitize_text_field( wp_unslash( $details['bot-name'] ) ) : false;
			$webhook_url = isset( $details['webhook-url'] ) ? sanitize_text_field( wp_unslash( $details['webhook-url'] ) ) : false;

			// Create the connection object.
			$connection              = new stdClass();
			$connection->name        = $conn_name;
			$connection->type        = $type;
			$connection->bot_name    = $bot_name;
			$connection->webhook_url = $webhook_url;
		}

		if ( ! isset( $_POST['connection']['update'] ) ) {
			// Add new option for connection.
			$this->wsal->wsalCommonClass->AddGlobalSetting( WSAL_CONN_PREFIX . $conn_name, $connection );
		} elseif ( isset( $_POST['connection']['update'] ) && isset( $_GET['connection'] ) ) {
			// Get original connection name.
			$ogc_name = sanitize_text_field( wp_unslash( $_GET['connection'] ) );

			// If the option name is changed then delete the previous one.
			if ( $connection->name !== $ogc_name ) {
				$this->wsal->wsalCommonClass->DeleteGlobalSetting( WpSecurityAuditLog::OPT_PRFX . WSAL_CONN_PREFIX . $ogc_name );
			}
			$this->wsal->SetGlobalSetting( WSAL_CONN_PREFIX . $conn_name, $connection );
		}

		if ( isset( $_GET['connection'] ) ) {
			$redirect_args = array(
				'page' => 'wsal-ext-settings',
				'tab'  => 'connections',
			);
			$redirect_url  = add_query_arg( $redirect_args, admin_url( 'admin.php' ) );
			wp_safe_redirect( $redirect_url );
			exit();
		}
	}

	/**
	 * Admin notice for failed connection.
	 */
	public function connection_failed_notice() {
		?>
		<div class="error notice is-dismissible">
			<p><?php esc_html_e( 'Connection failed. Please check the configuration again.', 'wp-security-audit-log' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Delete Connection Hanlder.
	 */
	public function delete_connection() {
		if ( ! $this->wsal->settings()->CurrentUserCan( 'edit' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Access Denied.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		// @codingStandardsIgnoreStart
		$nonce      = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;
		$connection = isset( $_POST['connection'] ) ? sanitize_text_field( wp_unslash( $_POST['connection'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( $nonce && $connection && wp_verify_nonce( $nonce, $connection . '-delete' ) ) {
			$this->wsal->DeleteSettingByName( $connection );

			echo wp_json_encode( array( 'success' => true ) );
		} else {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ),
				)
			);
		}
		exit();
	}

	/**
	 * Test Connection Handler.
	 */
	public function test_connection() {
		if ( ! $this->wsal->settings()->CurrentUserCan( 'edit' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Access Denied.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		// @codingStandardsIgnoreStart
		$nonce      = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;
		$connection = isset( $_POST['connection'] ) ? sanitize_text_field( wp_unslash( $_POST['connection'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( $nonce && $connection && wp_verify_nonce( $nonce, $connection . '-test' ) ) {
			// Get connection details.
			$connection = str_replace( 'wsal-', '', $connection );
			$details    = $this->wsal->wsalCommonClass->GetSettingByName( $connection );

			if ( isset( $details->type ) && 'mysql' === $details->type ) {
				try {
					WSAL_Connector_ConnectorFactory::CheckConfig( $details->type, $details->user, $details->password, $details->db_name, $details->hostname, $details->baseprefix, $details->is_ssl, $details->is_cc, $details->ssl_ca, $details->ssl_cert, $details->ssl_key );

					echo wp_json_encode(
						array(
							'success' => true,
							'message' => esc_html__( 'Successfully connected to database.', 'wp-security-audit-log' ),
						)
					);
				} catch ( Exception $ex ) {
					echo wp_json_encode(
						array(
							'success' => false,
							'message' => $ex->getMessage(),
						)
					);
				}
			} elseif ( isset( $details->type ) && 'papertrail' === $details->type ) {
				// Get papertrail configs.
				$destination = array_combine( array( 'hostname', 'port' ), explode( ':', $details->destination ) );
				$socket      = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );

				if ( socket_connect( $socket, $destination['hostname'], $destination['port'] ) ) {
					// Get website info.
					if ( $this->wsal->IsMultisite() ) {
						$site_id = get_current_blog_id();
						$info    = get_blog_details( $site_id, true );
						$website = ( ! $info ) ? 'Unknown_site_' . $site_id : str_replace( ' ', '_', $info->blogname );
					} else {
						$website = str_replace( ' ', '_', get_bloginfo( 'name' ) );
					}

					$current_date   = date( 'M d H:i:s', time() + $this->wsal->wsalCommonClass->GetTimezone() );
					$syslog_message = '<6>' . $current_date . ' ' . $website . ' Security_Audit_Log:Test message by WP Activity Log plugin';
					if ( socket_sendto( $socket, $syslog_message, strlen( $syslog_message ), 0, $destination['hostname'], $destination['port'] ) ) {
						echo wp_json_encode(
							array(
								'success' => true,
								'message' => esc_html__( 'Successfully connected to Papertrail App.', 'wp-security-audit-log' ),
							)
						);
					} else {
						echo wp_json_encode(
							array(
								'success' => false,
								'message' => esc_html__( 'socket_sendto was unable to send activity log events.', 'wp-security-audit-log' ),
							)
						);
					}
				} else {
					echo wp_json_encode(
						array(
							'success' => false,
							'message' => socket_strerror( socket_last_error( $socket ) ),
						)
					);
				}
				socket_close( $socket );
			} elseif ( isset( $details->type ) && 'syslog' === $details->type ) {
				if ( 'remote' === $details->location ) {
					// Get papertrail configs.
					$destination = array_combine( array( 'hostname', 'port' ), explode( ':', $details->remote_ip . ':' . $details->remote_port ) );
					$socket      = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );

					if ( socket_connect( $socket, $destination['hostname'], $destination['port'] ) ) {
						/*
						 * Connected... Assumed success. Try to send a message.
						 */

						// get the website that's sending.
						if ( $this->wsal->IsMultisite() ) {
							$site_id = get_current_blog_id();
							$info    = get_blog_details( $site_id, true );
							$website = ( ! $info ) ? 'Unknown_site_' . $site_id : str_replace( ' ', '_', $info->blogname );
						} else {
							$website = str_replace( ' ', '_', get_bloginfo( 'name' ) );
						}
						// get the date.
						$current_date = date( 'M d H:i:s', time() );
						// generate a message and send to socket.
						$syslog_message = '<6>' . $current_date . ' ' . $website . ' Security_Audit_Log:Test message by WP Activity Log plugin';
						socket_sendto( $socket, $syslog_message, strlen( $syslog_message ), 0, $destination['hostname'], $destination['port'] );
						echo wp_json_encode(
							array(
								'success'       => true,
								'message'       => esc_html__( 'Successfully connected to Syslog.', 'wp-security-audit-log' ),
								'customMessage' => esc_html__( 'Message sent', 'wp-security-audit-log' ),
							)
						);
					} else {
						echo wp_json_encode(
							array(
								'success' => false,
								'message' => socket_strerror( socket_last_error( $socket ) ),
							)
						);
					}
					socket_close( $socket );
				} else {
					openlog( 'Security_Audit_Log', LOG_NDELAY, LOG_USER );
					$response = syslog( LOG_INFO, 'WP Activity Log connection testing message.' );
					closelog();

					if ( $response ) {
						echo wp_json_encode(
							array(
								'success' => true,
								'message' => esc_html__( 'Successfully connected to Syslog.', 'wp-security-audit-log' ),
							)
						);
					} else {
						echo wp_json_encode(
							array(
								'success' => false,
								'message' => esc_html__( 'Syslog connection testing failed.', 'wp-security-audit-log' ),
							)
						);
					}
				}
			} elseif ( isset( $details->type ) && 'slack' === $details->type ) {
				// Message body.
				$message_body = wp_json_encode(
					array(
						'username' => $details->bot_name,
						'text'     => esc_html__( 'WP Activity Log connection testing message.', 'wp-security-audit-log' ),
					)
				);

				// Slack POST message arguments.
				$message_args = array(
					'headers' => array( 'Content-Type' => 'application/json' ),
					'body'    => str_replace( '\n', 'n', $message_body ),
				);

				// POST the message.
				$response = wp_remote_post( $details->webhook_url, $message_args );

				if ( ! is_wp_error( $response ) ) {
					echo wp_json_encode(
						array(
							'success' => true,
							'message' => esc_html__( 'Successfully connected to Slack.', 'wp-security-audit-log' ),
						)
					);
				} else {
					echo wp_json_encode(
						array(
							'success' => false,
							'message' => $response->get_error_message(),
						)
					);
				}
			} else {
				echo wp_json_encode(
					array(
						'success' => false,
						'message' => esc_html__( 'Unknown connection type.', 'wp-security-audit-log' ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ),
				)
			);
		}
		exit();
	}

	/**
	 * Wizard Connection Test Handler.
	 */
	public function wizard_connection_test() {
		// Check access.
		if ( ! $this->wsal->settings()->CurrentUserCan( 'edit' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Access Denied.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		if ( isset( $_POST['wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpnonce'] ) ), 'wsal-connection-wizard' ) ) {
			$db_name           = isset( $_POST['dbName'] ) ? sanitize_text_field( wp_unslash( $_POST['dbName'] ) ) : false;
			$db_user           = isset( $_POST['dbUser'] ) ? sanitize_text_field( wp_unslash( $_POST['dbUser'] ) ) : false;
			$db_password       = isset( $_POST['dbPassword'] ) ? sanitize_text_field( wp_unslash( $_POST['dbPassword'] ) ) : false;
			$db_password       = $this->wsal->wsalCommonClass->EncryptPassword( $db_password );
			$db_hostname       = isset( $_POST['dbHostname'] ) ? sanitize_text_field( wp_unslash( $_POST['dbHostname'] ) ) : false;
			$db_baseprefix     = isset( $_POST['dbBasePrefix'] ) ? sanitize_text_field( wp_unslash( $_POST['dbBasePrefix'] ) ) : false;
			$db_url_baseprefix = isset( $_POST['dbUrlBasePrefix'] ) ? sanitize_text_field( wp_unslash( $_POST['dbUrlBasePrefix'] ) ) : false;
			$db_ssl            = isset( $_POST['dbSSL'] ) ? sanitize_text_field( wp_unslash( $_POST['dbSSL'] ) ) : false;
			$ssl_cc            = isset( $_POST['sslCC'] ) ? sanitize_text_field( wp_unslash( $_POST['sslCC'] ) ) : false;
			$ssl_ca            = isset( $_POST['sslCA'] ) ? sanitize_text_field( wp_unslash( $_POST['sslCA'] ) ) : false;
			$ssl_cert          = isset( $_POST['sslCert'] ) ? sanitize_text_field( wp_unslash( $_POST['sslCert'] ) ) : false;
			$ssl_key           = isset( $_POST['sslKey'] ) ? sanitize_text_field( wp_unslash( $_POST['sslKey'] ) ) : false;

			// Convert string values to boolean.
			$db_url_baseprefix = ( 'true' === $db_url_baseprefix ) ? true : false;
			$db_ssl            = ( 'true' === $db_ssl ) ? true : false;
			$ssl_cc            = ( 'true' === $ssl_cc ) ? true : false;

			if ( ! empty( $db_url_baseprefix ) ) {
				$db_baseprefix = $this->wsal->wsalCommonClass->get_url_base_prefix();
			}

			try {
				WSAL_Connector_ConnectorFactory::CheckConfig( 'mysql', $db_user, $db_password, $db_name, $db_hostname, $db_baseprefix, $db_ssl, $ssl_cc, $ssl_ca, $ssl_cert, $ssl_key );

				echo wp_json_encode(
					array(
						'success' => true,
						'message' => esc_html__( 'Successfully connected to database.', 'wp-security-audit-log' ),
					)
				);
			} catch ( Exception $ex ) {
				$error = str_replace( "'", '', $ex->getMessage() );

				echo wp_json_encode(
					array(
						'success' => false,
						'message' => esc_html( $error ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ),
				)
			);
		}
		exit();
	}
}
