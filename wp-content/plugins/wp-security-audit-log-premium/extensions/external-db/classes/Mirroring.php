<?php
/**
 * View: Mirroring Tab
 *
 * External db mirroring tab view.
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
 * External db mirroring tab class.
 *
 * @package wsal
 * @subpackage external-db
 */
final class WSAL_Ext_Mirroring {

	/**
	 * Instance of WSAL.
	 *
	 * @var WpSecurityAuditLog
	 */
	private $wsal;

	/**
	 * Base URL Path.
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Base Directory Path.
	 *
	 * @var string
	 */
	private $base_dir;

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
		add_action( 'admin_init', array( $this, 'save' ) );
	}

	/**
	 * Tab Mirroring Render.
	 */
	public function render() {
		// @codingStandardsIgnoreStart
		$page   = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
		$tab    = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : false;
		$mirror = isset( $_GET['mirror'] ) ? sanitize_text_field( wp_unslash( $_GET['mirror'] ) ) : false;
		// @codingStandardsIgnoreEnd

		// Check if configuring a connection.
		if ( ! empty( $page ) && ! empty( $tab ) && ! empty( $mirror ) && 'wsal-ext-settings' === $page && 'mirroring' === $tab ) :
			$this->configure_mirror( $mirror );
		else :
			// Get mirrors.
			$mirrors = $this->wsal->GetNotificationsSetting( WSAL_MIRROR_PREFIX );

			// Mirror counter for loop.
			$mirror_counter = 0;

			$allowed_tags = array(
				'a' => array(
					'href'   => true,
					'target' => true,
				),
			);
			// TODO: Maybe this would be better as a loop?
			$help_link    = sprintf(
				'Read more about mirroring logs to an %1$s, %2$s, %3$s, and %4$s.',
				sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( 'https://wpactivitylog.com/support/kb/store-wordpress-activity-log-external-database/' ),
					__( 'external database', 'wp-security-audit-log' )
				),
				sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( 'https://wpactivitylog.com/support/kb/mirror-wordpress-activity-logs-slack-channel/' ),
					__( 'Slack', 'wp-security-audit-log' )
				),
				sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( 'https://wpactivitylog.com/support/kb/mirror-wordpress-activity-log-papertrail/' ),
					__( 'Papertrail', 'wp-security-audit-log' )
				),
				sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( 'https://wpactivitylog.com/support/kb/mirror-wordpress-activity-log-syslog/' ),
					__( 'Syslog', 'wp-security-audit-log' )
				)
			);
			?>
			<p class="description"><?php esc_html_e( 'In this section you can configure the mirroring of the WordPress to external databases, services and servers. You can configure multiple mirroring rules.', 'wp-security-audit-log' ); ?> <?php echo wp_kses( $help_link, $allowed_tags ); ?></p>
			<p><button id="wsal-create-mirror" class="button button-hero button-primary"><?php esc_html_e( 'Setup an Activity Log Mirror', 'wp-security-audit-log' ); ?></button></p>
			<!-- Create a Connection -->
			<h3><?php esc_html_e( 'The WordPress activity log is currently being mirrored to:', 'wp-security-audit-log' ); ?></h3>
			<table id="wsal-external-connections" class="wp-list-table widefat fixed striped logs">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Name', 'wp-security-audit-log' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Type', 'wp-security-audit-log' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Frequency', 'wp-security-audit-log' ); ?></th>
						<th scope="col"></th>
						<th scope="col"></th>
						<th scope="col"></th>
						<th scope="col"></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $mirrors ) : ?>
						<tr class="no-items">
							<td class="colspanchange" colspan="6"><?php esc_html_e( 'No mirrors so far.', 'wp-security-audit-log' ); ?></td>
						</tr>
					<?php
					else :
						foreach ( $mirrors as $mirror ) :
							$details = maybe_unserialize( $mirror->option_value );

							// Check for mirror details object.
							if ( ! $details instanceof stdClass ) {
								continue;
							} else {
								$mirror_counter++;
							}

							$conf_args     = array(
								'page'   => 'wsal-ext-settings',
								'tab'    => 'mirroring',
								'mirror' => $details->name,
							);
							$configure_url = add_query_arg( $conf_args, network_admin_url( 'admin.php' ) );

							// Get mirror connection.
							$connection = $this->wsal->wsalCommonClass->GetSettingByName( WSAL_CONN_PREFIX . $details->connection );

							// Mirror frequency.
							$frequency = '';
							if ( isset( $details->frequency ) ) {
								switch ( $details->frequency ) {
									case 'fifteenminutes':
										$frequency = __( '15 minutes', 'wp-security-audit-log' );
										break;
									case 'hourly':
										$frequency = __( '1 hour', 'wp-security-audit-log' );
										break;
									case 'sixhours':
										$frequency = __( '6 hours', 'wp-security-audit-log' );
										break;
									case 'twicedaily':
										$frequency = __( '12 hours', 'wp-security-audit-log' );
										break;
									case 'daily':
										$frequency = __( '24 hours', 'wp-security-audit-log' );
										break;
									default:
										break;
								}
							}

							// Mirror state.
							$state     = 'disabled';
							$state_btn = __( 'Enable', 'wp-security-audit-log' );
							if ( isset( $details->state ) && true === $details->state ) {
								$state     = 'enabled';
								$state_btn = __( 'Disable', 'wp-security-audit-log' );
							}
							?>
							<tr>
								<td><?php echo isset( $details->name ) ? esc_html( $details->name ) : false; ?></td>
								<!-- Mirror Name -->
								<td><?php echo isset( $connection->type ) ? esc_html( $connection->type ) : false; ?></td>
								<!-- Mirror Type -->
								<td><?php echo ! empty( $frequency ) ? esc_html( $frequency ) : false; ?></td>
								<!-- Mirror Frequency -->
								<td><a href="<?php echo esc_url( $configure_url ); ?>" class="button-primary"><?php esc_html_e( 'Configure', 'wp-security-audit-log' ); ?></a></td>
								<!-- Configure Mirror -->
								<td><button type="button" data-mirror="<?php echo esc_attr( $details->name ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( $details->name . '-run' ) ); ?>" class="button button-primary wsal-mirror-run-now"><?php esc_html_e( 'Run Now', 'wp-security-audit-log' ); ?></button></td>
								<!-- Run Mirror -->
								<td><a href="javascript:;" data-mirror="<?php echo esc_attr( $details->name ); ?>" data-state="<?php echo esc_attr( $state ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( $details->name . '-toggle' ) ); ?>" class="button button-secondary wsal-mirror-toggle"><?php echo esc_html( $state_btn ); ?></a></td>
								<!-- Mirror State -->
								<td><a href="javascript:;" data-mirror="<?php echo esc_attr( $details->name ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( $details->name . '-delete' ) ); ?>" class="button button-danger wsal-mirror-delete"><?php esc_html_e( 'Delete', 'wp-security-audit-log' ); ?></a></td>
								<!-- Delete Mirror -->
							</tr>
							<?php
						endforeach;

						if ( 0 === $mirror_counter ) :
							?>
							<tr class="no-items">
								<td class="colspanchange" colspan="6"><?php esc_html_e( 'No mirrors so far.', 'wp-security-audit-log' ); ?></td>
							</tr>
							<?php
						endif;
					endif;
					?>
				</tbody>
			</table>
			<?php
			// Create mirror wizard.
			$this->wizard();
		endif;
	}

	/**
	 * Mirror Setup Wizard.
	 *
	 * @param string $mirror – Mirror name.
	 */
	private function wizard( $mirror = '' ) {
		// Check mirror paramter.
		$details = '';
		if ( $mirror ) {
			// Get mirror setting.
			$details = $this->wsal->wsalCommonClass->get_ext_mirror( $mirror );
		}

		// Get available alert catogories.
		$alerts = $this->wsal->alerts->GetCategorizedAlerts();

		$wsal_alerts = array();
		foreach ( $alerts as $cname => $group ) {
			foreach ( $group as $subname => $entries ) {
				$wsal_alerts[ $subname ] = $entries;
			}
		}
		?>
		<div id="wsal-mirroring-wizard" class="hidden">
			<ul class="steps">
				<li id="step-1" class="is-active"><?php esc_html_e( 'Step 1', 'wp-security-audit-log' ); ?></li>
				<li id="step-2"><?php esc_html_e( 'Step 2', 'wp-security-audit-log' ); ?></li>
				<li id="step-3"><?php esc_html_e( 'Step 3', 'wp-security-audit-log' ); ?></li>
			</ul>
			<!-- Steps -->

			<div class="content">
				<form method="POST">
					<?php wp_nonce_field( 'wsal-mirroring-wizard' ); ?>
					<div id="content-step-1">
						<h3><?php esc_html_e( 'Select the connection where to mirror to the activity log', 'wp-security-audit-log' ); ?></h3>
						<p class="description"><?php esc_html_e( 'Please specify a friendly name for the mirroring connection. Connection names can be 25 characters long, and can only contain letters, numbers and underscores.', 'wp-security-audit-log' ); ?></p>
						<?php $this->get_mirror_field( $details, 'name' ); ?>
						<p class="description"><?php esc_html_e( 'Select one of the connections you have configured to where you want to mirror the activity log.', 'wp-security-audit-log' ); ?></p>
						<?php $this->get_mirror_field( $details, 'connections' ); ?>
					</div>
					<div id="content-step-2">
						<h3><?php esc_html_e( 'Configure the frequency', 'wp-security-audit-log' ); ?></h3>
						<p class="description"><?php esc_html_e( 'How often do you want to run the mirroring process?', 'wp-security-audit-log' ); ?></p>
						<?php $this->get_mirror_field( $details, 'frequency' ); ?>

						<h3><?php esc_html_e( 'Start once configured?', 'wp-security-audit-log' ); ?></h3>
						<p class="description">
							<input type="checkbox" name="mirror[state]" <?php echo ( isset( $details->state ) ) ? checked( $details, true, false ) : 'checked'; ?> value="1" />
							<?php esc_html_e( 'Tick this checkbox to enable the mirror and start sending data once set up.', 'wp-security-audit-log' ); ?>
						</p>
					</div>
					<div id="content-step-3">
						<h3><?php esc_html_e( 'Configure Filtering', 'wp-security-audit-log' ); ?></h3>
						<p class="description"><?php $this->get_mirror_field( $details, 'events' ); ?><?php esc_html_e( 'Configure any filtering you’d like to apply to this mirroring connection:', 'wp-security-audit-log' ); ?></p>
					</div>
					<div class="content-btns">
						<input type="hidden" name="mirror[last_created]" value="<?php echo isset( $details->last_created ) ? esc_attr( $details->last_created ) : false; ?>" />
						<input type="submit" class="button button-primary" id="wizard-save" value="<?php esc_attr_e( 'Save Mirror', 'wp-security-audit-log' ); ?>" name="submit" />
						<input type="button" class="button button-primary" id="wizard-next" value="<?php esc_attr_e( 'Next', 'wp-security-audit-log' ); ?>" />
						<input type="button" class="button button-secondary" id="wizard-cancel" value="<?php esc_attr_e( 'Cancel', 'wp-security-audit-log' ); ?>" />
					</div>
				</form>
			</div>
		</div>
		<!-- Create Connection Modal -->
		<?php
	}

	/**
	 * Configure Mirror View.
	 *
	 * @param stdClass $mirror_name - Mirroring object.
	 */
	private function configure_mirror( $mirror_name ) {
		// Check if mirror name is empty.
		if ( ! $mirror_name ) {
			esc_html_e( 'No mirror name specified!', 'wp-security-audit-log' );
			return;
		}

		// Get mirror setting.
		$mirror = $this->wsal->wsalCommonClass->get_ext_mirror( $mirror_name );

		if ( ! empty( $mirror ) && $mirror instanceof stdClass ) :
			?>
			<h1><?php echo esc_html__( 'Configure Mirror → ', 'wp-security-audit-log' ) . esc_html( $mirror->name ); ?></h1>
			<br>
			<form method="POST">
				<input type="hidden" name="mirror[update]" value="1" />
				<input type="hidden" name="mirror[state]" value="<?php echo isset( $mirror->state ) ? esc_attr( $mirror->state ) : false; ?>" />
				<input type="hidden" name="mirror[last_created]" value="<?php echo isset( $mirror->last_created ) ? esc_attr( $mirror->last_created ) : false; ?>" />
				<?php
				wp_nonce_field( 'wsal-mirror-configure' );
				$this->get_mirror_field( $mirror, 'name' ); // Get mirror name field.
				?>
				<p class="description"><?php esc_html_e( 'Please specify a friendly name for the mirroring connection. Connection names can be 25 characters long, and can only contain letters, numbers and underscores.', 'wp-security-audit-log' ); ?></p>
				<h3><?php esc_html_e( 'Configure the mirror', 'wp-security-audit-log' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Configure the mirror details.', 'wp-security-audit-log' ); ?></p>
				<?php
				$this->get_mirror_field( $mirror, 'connections' );
				$this->get_mirror_field( $mirror, 'frequency' );
				$this->get_mirror_field( $mirror, 'events' );
				submit_button( 'Save Mirror' );
				?>
			</form>
			<?php
		endif;
	}

	/**
	 * Get Mirror Field.
	 *
	 * @param mixed  $mirror - Mirror details.
	 * @param string $type   - Type of mirror field.
	 */
	private function get_mirror_field( $mirror, $type ) {
		if ( 'name' === $type ) :
			?>
			<table class="form-table">
				<tr>
					<th><label for="mirror-name"><?php esc_html_e( 'Mirror Name', 'wp-security-audit-log' ); ?></label></th>
					<td>
						<fieldset>
							<input type="text" name="mirror[name]" id="mirror-name" data-type="required" value="<?php echo isset( $mirror->name ) ? esc_attr( $mirror->name ) : false; ?>" />
							<span class="description error"><?php esc_html_e( '* Invalid Mirror Name', 'wp-security-audit-log' ); ?></span>
						</fieldset>
					</td>
				</tr>
			</table>
			<?php
		elseif ( 'connections' === $type ) :
			?>
			<table class="form-table">
				<tr>
					<th><label for="mirror-connection"><?php esc_html_e( 'Connection', 'wp-security-audit-log' ); ?></label></th>
					<td>
						<fieldset>
							<?php
							// Get connections.
							$connections = $this->wsal->GetNotificationsSetting( WSAL_CONN_PREFIX );

							// Get selected connection.
							$selected = isset( $mirror->connection ) ? $this->wsal->wsalCommonClass->get_ext_connection( $mirror->connection ) : false;
							?>
							<select name="mirror[connection]" id="mirror-connection" data-type="required">
								<option value="0" disabled selected><?php esc_html_e( 'Select a connection', 'wp-security-audit-log' ); ?></option>
								<?php
								if ( ! empty( $connections ) ) :
									foreach ( $connections as $connection ) :
										$conn_details = maybe_unserialize( $connection->option_value );
										if ( $conn_details instanceof stdClass ) :
											?>
											<option value="<?php echo esc_attr( $conn_details->name ); ?>" <?php isset( $selected->name ) ? selected( $conn_details->name, $selected->name ) : false; ?>><?php echo esc_html( $conn_details->name ); ?></option>
											<?php
										endif;
									endforeach;
								endif;
								?>
							</select>
						</fieldset>
					</td>
				</tr>
			</table>
			<?php
		elseif ( 'frequency' === $type ) :
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="mirror-frequency"><?php esc_html_e( 'Frequency', 'wp-security-audit-log' ); ?></label></th>
						<td>
							<fieldset>
								<select name="mirror[frequency]" id="mirror-frequency">
									<option <?php isset( $mirror->frequency ) ? selected( $mirror->frequency, 'fifteenminutes' ) : false; ?> value="fifteenminutes"><?php esc_html_e( '15 minutes', 'wp-security-audit-log' ); ?></option>
									<option <?php isset( $mirror->frequency ) ? selected( $mirror->frequency, 'hourly' ) : false; ?> value="hourly"><?php esc_html_e( '1 hour', 'wp-security-audit-log' ); ?></option>
									<option <?php isset( $mirror->frequency ) ? selected( $mirror->frequency, 'sixhours' ) : false; ?> value="sixhours"><?php esc_html_e( '6 hours', 'wp-security-audit-log' ); ?></option>
									<option <?php isset( $mirror->frequency ) ? selected( $mirror->frequency, 'twicedaily' ) : false; ?> value="twicedaily"><?php esc_html_e( '12 hours', 'wp-security-audit-log' ); ?></option>
									<option <?php isset( $mirror->frequency ) ? selected( $mirror->frequency, 'daily' ) : false; ?> value="daily"><?php esc_html_e( '24 hours', 'wp-security-audit-log' ); ?></option>
								</select>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		elseif ( 'events' === $type ) :
			// @codingStandardsIgnoreStart
			$is_mirror_set = ( isset( $_GET['mirror'] ) && ! empty( $_GET['mirror'] ) ) ? true : false;
			// @codingStandardsIgnoreEnd

			// Get available alert catogories.
			$alerts = $this->wsal->alerts->GetCategorizedAlerts();

			$wsal_alerts = array();
			foreach ( $alerts as $cname => $group ) {
				foreach ( $group as $subname => $entries ) {
					$wsal_alerts[ $subname ] = $entries;
				}
			}
			?>
			<table class="form-table">
				<tr>
					<?php if ( $is_mirror_set ) : ?>
						<th><label for="mirror-filter-all"><?php esc_html_e( 'Mirror Events', 'wp-security-audit-log' ); ?></label></th>
					<?php endif; ?>
					<td>
						<fieldset>
							<label for="mirror-filter-all">
								<input type="radio" name="mirror[filter]" id="mirror-filter-all" value="all"
								<?php
								// If user is configuring then check the incoming mirror filter value.
								if ( $is_mirror_set ) {
									isset( $mirror->filter ) ? checked( $mirror->filter, 'all' ) : false;
								} else {
									// Otherwise select this option by default.
									checked( 'all', 'all' );
								}
								?>
								/>
								<?php esc_html_e( 'Send all events', 'wp-security-audit-log' ); ?>
							</label>
							<br>
							<label for="mirror-filter-event-codes">
								<input type="radio" name="mirror[filter]" id="mirror-filter-event-codes" value="event-codes" <?php isset( $mirror->filter ) ? checked( $mirror->filter, 'event-codes' ) : false; ?> />
								<?php esc_html_e( 'Only send events with these IDs:', 'wp-security-audit-log' ); ?>
								<br>
								<select name="event-codes[]" id="mirror-select-event-codes" multiple="multiple">
									<?php
									foreach ( $wsal_alerts as $category => $sub_alerts ) :
										if ( __( 'Pages', 'wp-security-audit-log' ) === $category || __( 'Custom Post Types', 'wp-security-audit-log' ) === $category ) {
											continue;
										}
										?>
										<optgroup label="<?php echo esc_attr( $category ); ?>">
											<?php
											foreach ( $sub_alerts as $index => $alert ) :
												if ( isset( $mirror->event_codes ) && is_array( $mirror->event_codes ) ) :
													$event_codes = array_map( 'intval', $mirror->event_codes ); // Convert string codes to int type.
													?>
													<option value="<?php echo esc_attr( $alert->type ); ?>" <?php echo in_array( $alert->type, $event_codes, true ) ? 'selected' : false; ?>><?php echo esc_html( $alert->type . ' — ' . $alert->desc ); ?></option>
													<?php
												else :
													?>
													<option value="<?php echo esc_attr( $alert->type ); ?>"><?php echo esc_html( $alert->type . ' — ' . $alert->desc ); ?></option>
													<?php
												endif;
											endforeach;
											?>
										</optgroup>
										<?php
									endforeach;
									?>
								</select>
							</label>
							<br>
							<label for="mirror-filter-except-codes">
								<input type="radio" name="mirror[filter]" id="mirror-filter-except-codes" value="except-codes" <?php isset( $mirror->filter ) ? checked( $mirror->filter, 'except-codes' ) : false; ?> />
								<?php esc_html_e( 'Send all events BUT those with these IDs:', 'wp-security-audit-log' ); ?>
								<br>
								<select name="except-codes[]" id="mirror-select-except-codes" multiple="multiple">
									<?php
									foreach ( $wsal_alerts as $category => $sub_alerts ) :
										if ( __( 'Pages', 'wp-security-audit-log' ) === $category || __( 'Custom Post Types', 'wp-security-audit-log' ) === $category ) {
											continue;
										}
										?>
										<optgroup label="<?php echo esc_attr( $category ); ?>">
											<?php
											foreach ( $sub_alerts as $index => $alert ) :
												if ( isset( $mirror->exception_codes ) && is_array( $mirror->exception_codes ) ) :
													$exception_codes = array_map( 'intval', $mirror->exception_codes );
													?>
													<option value="<?php echo esc_attr( $alert->type ); ?>" <?php echo in_array( $alert->type, $exception_codes, true ) ? 'selected' : false; ?>><?php echo esc_html( $alert->type . ' — ' . $alert->desc ); ?></option>
													<?php
												else :
													?>
													<option value="<?php echo esc_attr( $alert->type ); ?>"><?php echo esc_html( $alert->type . ' — ' . $alert->desc ); ?></option>
													<?php
												endif;
											endforeach;
											?>
										</optgroup>
										<?php
									endforeach;
									?>
								</select>
							</label>
							<p class="description">
								<?php
								/* Translators: Events and Event IDs hyperlink. */
								echo sprintf( esc_html__( 'Refer to the %s for more information.', 'wp-security-audit-log' ), '<a href="https://wpactivitylog.com/support/kb/list-wordpress-activity-log-event-ids/" target="_blank">' . esc_html__( 'list of events and events IDs', 'wp-security-audit-log' ) . '</a>' );
								?>
							</p>
						</fieldset>
					</td>
				</tr>
			</table>
			<?php
		endif;
	}

	/**
	 * Enqueue tab scripts.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		wp_enqueue_style(
			'wsal-rep-select2-css',
			trailingslashit( WSAL_BASE_URL ) . 'js/select2/select2.css',
			array(),
			'3.5.1'
		);

		wp_enqueue_style(
			'wsal-rep-select2-bootstrap-css',
			trailingslashit( WSAL_BASE_URL ) . 'js/select2/select2-bootstrap.css',
			array(),
			'3.5.1'
		);

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

		wp_enqueue_script(
			'wsal-ext-select2-js',
			trailingslashit( WSAL_BASE_URL ) . 'js/select2/select2.min.js',
			array( 'jquery' ),
			'3.5.1',
			true
		);

		// Connections script file.
		wp_register_script(
			'wsal-connections-js',
			$this->base_url . 'js/wsal-ext-wizard.js',
			array( 'jquery', 'jquery-ui-dialog', 'wsal-ext-select2-js' ),
			filemtime( $this->base_dir . 'js/wsal-ext-wizard.js' ),
			true
		);

		// @codingStandardsIgnoreStart
		$mirror = isset( $_GET['mirror'] ) ? sanitize_text_field( wp_unslash( $_GET['mirror'] ) ) : false;
		// @codingStandardsIgnoreEnd

		$script_data = array(
			'wpNonce'           => wp_create_nonce( 'wsal-create-connections' ),
			'title'             => __( 'Connections Wizard', 'wp-security-audit-log' ),
			'mirrorTitle'       => __( 'Mirroring Wizard', 'wp-security-audit-log' ),
			'connTest'          => __( 'Testing...', 'wp-security-audit-log' ),
			'deleting'          => __( 'Deleting...', 'wp-security-audit-log' ),
			'enabling'          => __( 'Enabling...', 'wp-security-audit-log' ),
			'disabling'         => __( 'Disabling...', 'wp-security-audit-log' ),
			'connFailed'        => __( 'Connection Failed!', 'wp-security-audit-log' ),
			'connSuccess'       => __( 'Connected', 'wp-security-audit-log' ),
			'mirrorInProgress'  => __( 'Running...', 'wp-security-audit-log' ),
			'mirrorComplete'    => __( 'Mirror Complete!', 'wp-security-audit-log' ),
			'mirrorFailed'      => __( 'Failed!', 'wp-security-audit-log' ),
			'confirm'           => __( 'Are you sure that you want to delete this connection?', 'wp-security-audit-log' ),
			'confirmDelMirror'  => __( 'Are you sure that you want to delete this mirror?', 'wp-security-audit-log' ),
			'eventsPlaceholder' => __( 'Select Event Code(s)', 'wp-security-audit-log' ),
			'testContinue'      => __( 'Test connection and continue', 'wp-security-audit-log' ),
			'buttonNext'        => __( 'Next', 'wp-security-audit-log' ),
			'ajaxURL'           => admin_url( 'admin-ajax.php' ),
			'connection'        => false,
			'mirror'            => $mirror,
			'urlBasePrefix'     => $this->wsal->wsalCommonClass->get_url_base_prefix(),
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

		// Verify nonce.
		if ( isset( $_POST['mirror']['update'] ) ) {
			check_admin_referer( 'wsal-mirror-configure' );
		} else {
			check_admin_referer( 'wsal-mirroring-wizard' );
		}

		// Get mirror details.
		$details      = isset( $_POST['mirror'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mirror'] ) ) : false;
		$event_codes  = isset( $_POST['event-codes'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event-codes'] ) ) : false;
		$except_codes = isset( $_POST['except-codes'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['except-codes'] ) ) : false;

		// Create the mirror object.
		$mirror                  = new stdClass();
		$mirror->name            = isset( $details['name'] ) ? $details['name'] : false;
		$mirror->connection      = isset( $details['connection'] ) ? $details['connection'] : false;
		$mirror->frequency       = isset( $details['frequency'] ) ? $details['frequency'] : false;
		$mirror->filter          = isset( $details['filter'] ) ? $details['filter'] : false;
		$mirror->state           = isset( $details['state'] ) ? (bool) $details['state'] : false;
		$mirror->last_created    = isset( $details['last_created'] ) ? $details['last_created'] : false;
		$mirror->event_codes     = $event_codes;
		$mirror->exception_codes = $except_codes;

		// Get connection details and set used for attribute.
		$conn           = $this->wsal->wsalCommonClass->get_ext_connection( $mirror->connection );
		$conn->used_for = __( 'Mirroring', 'wp-security-audit-log' );
		$this->wsal->wsalCommonClass->set_ext_connection( $conn );

		if ( ! isset( $_POST['mirror']['update'] ) ) {
			// Add new option for mirror.
			$this->wsal->wsalCommonClass->set_ext_mirror( $mirror );
		} elseif ( isset( $_POST['mirror']['update'] ) && isset( $_GET['mirror'] ) ) {
			// Get original mirror name.
			$ogm_name = sanitize_text_field( wp_unslash( $_GET['mirror'] ) );

			// If the option name is changed then delete the previous one.
			if ( $mirror->name !== $ogm_name ) {
				$this->wsal->wsalCommonClass->DeleteGlobalSetting( WpSecurityAuditLog::OPT_PRFX . WSAL_MIRROR_PREFIX . $ogm_name );
				$mirror_args = array( $ogm_name );
				wp_clear_scheduled_hook( WSAL_Ext_Plugin::SCHEDULED_HOOK_MIRRORING, $mirror_args );
			}

			// Save the mirror.
			$this->wsal->wsalCommonClass->set_ext_mirror( $mirror );
		}

		if ( isset( $_GET['mirror'] ) ) {
			$redirect_args = array(
				'page' => 'wsal-ext-settings',
				'tab'  => 'mirroring',
			);
			$redirect_url  = add_query_arg( $redirect_args, network_admin_url( 'admin.php' ) );
			wp_safe_redirect( $redirect_url );
			exit();
		}
	}
}
