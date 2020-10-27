<?php
/**
 * Class: Utility Class
 *
 * Utility class for common function.
 *
 * @since 1.0.0
 * @package Wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_Ext_Common
 *
 * Utility class, used for all the common functions used in the plugin.
 *
 * @package Wsal/external-db
 */
class WSAL_Ext_Common {

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var WpSecurityAuditLog
	 */
	public $wsal = null;

	/**
	 * Archive DB Connection Object.
	 *
	 * @var object
	 */
	protected static $_archive_db = null;

	/**
	 * Mirror DB Connection Object.
	 *
	 * @var object
	 */
	protected static $_mirror_db = null;

	/**
	 * Method: Constructor
	 *
	 * @param WpSecurityAuditLog $wsal - Instance of WpSecurityAuditLog.
	 */
	public function __construct( WpSecurityAuditLog $wsal ) {
		$this->wsal = $wsal;
	}

	/**
	 * Set the option by name with the given value.
	 *
	 * Deprecated function. It is only kept for the upgrade routine. It should be removed in future releases.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $value - value.
	 *
	 * @deprecated 4.1.3 Use WSAL_Ext_Common::AddGlobalSetting() instead
	 * @see WSAL_Ext_Common::AddGlobalSetting()
	 */
	public function AddGlobalOption( $option, $value ) {
		$this->wsal->SetGlobalOption( $option, $value );
	}

	/**
	 * Set the setting by name with the given value.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $value - value.
	 */
	public function AddGlobalSetting( $option, $value ) {
		$this->wsal->SetGlobalSetting( $option, $value );
	}

	/**
	 * Update the option by name with the given value.
	 *
	 * Deprecated function. It is only kept for the upgrade routine. It should be removed in future releases.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $value - Value.
	 * @return boolean
	 *
	 * @deprecated 4.1.3 Use WSAL_Ext_Common::UpdateGlobalSetting() instead
	 * @see WSAL_Ext_Common::UpdateGlobalSetting()
	 */
	public function UpdateGlobalOption( $option, $value ) {
		return $this->wsal->UpdateGlobalOption( $option, $value );
	}

	/**
	 * Update the setting by name with the given value.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $value - Value.
	 * @return boolean
	 */
	public function UpdateGlobalSetting( $option, $value ) {
		return $this->wsal->UpdateGlobalSetting( $option, $value );
	}

	/**
	 * Delete the option by name.
	 *
	 * @param string $option - Option name.
	 * @return boolean result
	 *
	 * @deprecated 4.1.3 Use WSAL_Ext_Common::DeleteGlobalSetting instead
	 * @see WSAL_Ext_Common::DeleteGlobalSetting()
	 */
	public function DeleteGlobalOption( $option ) {
		return $this->wsal->DeleteByName( $option );
	}

	/**
	 * Delete setting by name.
	 *
	 * @param string $option - Option name.
	 * @return boolean result
	 */
	public function DeleteGlobalSetting( $option ) {
		return $this->wsal->DeleteSettingByName( $option );
	}

	/**
	 * Get the option by name.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $default - Default value.
	 * @return mixed value
	 *
	 * @deprecated 4.1.3 Use WSAL_Ext_Common::GetSettingByName instead.
	 * @see WSAL_Ext_Common::GetSettingByName()
	 */
	public function GetOptionByName( $option, $default = false ) {
		return $this->wsal->GetGlobalOption( $option, $default );
	}

	/**
	 * Get setting by name.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $default - Default value.
	 * @return mixed value
	 */
	public function GetSettingByName( $option, $default = false ) {
		return $this->wsal->GetGlobalSetting( $option, $default );
	}

	/**
	 * Encrypt password, before saves it to the DB.
	 *
	 * @param string $data - Original text.
	 * @return string - Encrypted text
	 */
	public function EncryptPassword( $data ) {
		return $this->wsal->getConnector()->encryptString( $data );
	}

	/**
	 * Decrypt password, after reads it from the DB.
	 *
	 * @param string $ciphertext_base64 - Encrypted text.
	 * @return string - Original text.
	 */
	public function DecryptPassword( $ciphertext_base64 ) {
		return $this->wsal->getConnector()->decryptString( $ciphertext_base64 );
	}

	/**
	 * Method: Return URL based prefix for DB.
	 *
	 * @return string - URL based prefix.
	 *
	 * @param string $name - Name of the DB type.
	 */
	public function get_url_base_prefix( $name = '' ) {
		// Get home URL.
		$home_url  = get_home_url();
		$protocols = array( 'http://', 'https://' ); // URL protocols.
		$home_url  = str_replace( $protocols, '', $home_url ); // Replace URL protocols.
		$home_url  = str_replace( array( '.', '-' ), '_', $home_url ); // Replace `.` with `_` in the URL.

		// Concat name of the DB type at the end.
		if ( ! empty( $name ) ) {
			$home_url .= '_';
			$home_url .= $name;
			$home_url .= '_';
		} else {
			$home_url .= '_';
		}

		// Return the prefix.
		return $home_url;
	}

	/**
	 * Get timezone from the settings.
	 *
	 * @return int $gmt_offset_sec
	 */
	public function GetTimezone() {
		$gmt_offset_sec = 0;
		$timezone       = $this->wsal->settings()->GetTimezone();

		/**
		 * Transform timezone values.
		 *
		 * @since 3.2.3
		 */
		if ( '0' === $timezone ) {
			$timezone = 'utc';
		} elseif ( '1' === $timezone ) {
			$timezone = 'wp';
		}

		if ( 'utc' === $timezone ) {
			$gmt_offset_sec = date( 'Z' );
		} else {
			$gmt_offset_sec = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		}
		return $gmt_offset_sec;
	}

	/**
	 * Time Format from WordPress General Settings.
	 *
	 * @return boolean true if time is 24 hours false otherwise
	 */
	public function IsTime24Hours() {
		$wp_time_format = get_option( 'time_format' );
		return ! ( stripos( $wp_time_format, 'g' ) !== false );
	}

	/**
	 * Creates a connection and returns it
	 *
	 * @param array $connection_config - Array of connection configurations.
	 * @return wpdb Instance of WPDB
	 */
	private function CreateConnection( $connection_config ) {
		$password = $this->DecryptPassword( $connection_config['password'] );
		$new_wpdb = new wpdbCustom( $connection_config['user'], $password, $connection_config['name'], $connection_config['hostname'], $connection_config['is_ssl'], $connection_config['is_cc'], $connection_config['ssl_ca'], $connection_config['ssl_cert'], $connection_config['ssl_key'] );
		$new_wpdb->set_prefix( $connection_config['base_prefix'] );
		return $new_wpdb;
	}

	/*============================== External Database functions ==============================*/

	/**
	 * Migrate to external database (Metadata table)
	 *
	 * @param int $index - Index.
	 * @param int $limit - Limit.
	 */
	public function MigrateMeta( $index, $limit ) {
		return $this->wsal->getConnector()->MigrateMeta( $index, $limit );
	}

	/**
	 * Migrate to external database (Occurrences table)
	 *
	 * @param int $index - Index.
	 * @param int $limit - Limit.
	 */
	public function MigrateOccurrence( $index, $limit ) {
		return $this->wsal->getConnector()->MigrateOccurrence( $index, $limit );
	}

	/**
	 * Migrate back to WP database (Metadata table)
	 *
	 * @param int $index - Index.
	 * @param int $limit - Limit.
	 */
	public function MigrateBackMeta( $index, $limit ) {
		if ( $index == 0 ) {
			$this->RecreateTables();
		}
		return $this->wsal->getConnector()->MigrateBackMeta( $index, $limit );
	}

	/**
	 * Migrate back to WP database (Occurrences table)
	 *
	 * @param int $index - Index.
	 * @param int $limit - Limit.
	 *
	 * @return array
	 */
	public function MigrateBackOccurrence( $index, $limit ) {
		$response = $this->wsal->getConnector()->MigrateBackOccurrence( $index, $limit );

		if ( ( ! empty( $response['complete'] ) && $response['complete'] ) || ( ! empty( $response['empty'] ) && $response['empty'] ) ) {
			$this->RemoveConfig();
		}

		return $response;
	}

	/**
	 * Checks if the necessary tables are available.
	 *
	 * @return bool true|false
	 */
	public function IsInstalled() {
		return $this->wsal->getConnector()->isInstalled();
	}

	/**
	 * Remove External DB config.
	 */
	public function RemoveConfig() {
		// Get archive connection.
		$adapter_conn_name = $this->GetSettingByName( 'adapter-connection' );

		if ( $adapter_conn_name ) {
			$adapter_connection           = $this->get_ext_connection( $adapter_conn_name );
			$adapter_connection->used_for = '';
			$this->set_ext_connection( $adapter_connection );
		}

		$this->DeleteGlobalSetting( 'wsal-adapter-connection' );
		$this->DeleteGlobalSetting( 'wsal-adapter-type' );
		$this->DeleteGlobalSetting( 'wsal-adapter-user' );
		$this->DeleteGlobalSetting( 'wsal-adapter-password' );
		$this->DeleteGlobalSetting( 'wsal-adapter-name' );
		$this->DeleteGlobalSetting( 'wsal-adapter-hostname' );
		$this->DeleteGlobalSetting( 'wsal-adapter-base-prefix' );
		$this->DeleteGlobalSetting( 'wsal-adapter-url-base-prefix' );
		$this->DeleteGlobalSetting( 'wsal-adapter-use-buffer' );
		$this->DeleteGlobalSetting( 'wsal-adapter-ssl' );
		$this->DeleteGlobalSetting( 'wsal-adapter-client-certificate' );
		$this->DeleteGlobalSetting( 'wsal-adapter-ssl-ca' );
		$this->DeleteGlobalSetting( 'wsal-adapter-ssl-cert' );
		$this->DeleteGlobalSetting( 'wsal-adapter-ssl-key' );
	}

	/**
	 * Recreate DB tables on WP.
	 */
	public function RecreateTables() {
		$occurrence = new WSAL_Models_Occurrence();
		$occurrence->getAdapter()->InstallOriginal();
		$meta = new WSAL_Models_Meta();
		$meta->getAdapter()->InstallOriginal();
	}

	/*============================== Mirroring functions ==============================*/

	/**
	 * Checks if mirroring is enabled.
	 *
	 * @deprecated 3.3
	 *
	 * @return bool true|false
	 */
	public function IsMirroringEnabled() {
		return $this->GetSettingByName( 'mirroring-e' );
	}

	/**
	 * Enable/Disable mirroring if is marked as disabled
	 * remove the related config.
	 *
	 * @deprecated 3.3
	 *
	 * @param bool $enabled - True|False.
	 */
	public function SetMirroringEnabled( $enabled ) {
		$this->AddGlobalSetting( 'mirroring-e', $enabled );
		if ( empty( $enabled ) ) {
			$this->RemoveMirroringDBConfig();
			$this->RemovePapertrailConfig();
			$this->DeleteGlobalSetting( 'wsal-mirroring-run-every' );
			$this->DeleteGlobalSetting( 'wsal-mirroring-last-created' );
		}
	}

	/**
	 * Get mirroring type (database, papertrail, syslog).
	 *
	 * @deprecated 3.3
	 *
	 * @return string value
	 */
	public function GetMirroringType() {
		return $this->GetSettingByName( 'mirroring-type' );
	}

	/**
	 * Set mirroring type (database, papertrail, syslog).
	 *
	 * @deprecated 3.3
	 *
	 * @param string $newvalue - New value.
	 */
	public function SetMirroringType( $newvalue ) {
		$this->AddGlobalSetting( 'mirroring-type', $newvalue );
		if ( 'database' == $newvalue ) {
			$this->RemovePapertrailConfig();
		} elseif ( 'papertrail' == $newvalue ) {
			$this->RemoveMirroringDBConfig();
		} elseif ( 'syslog' == $newvalue ) {
			$this->RemoveMirroringDBConfig();
			$this->RemovePapertrailConfig();
		}
	}

	/**
	 * Get mirroring frequency.
	 *
	 * @deprecated 3.3
	 *
	 * @return string frequency
	 */
	public function GetMirroringRunEvery() {
		return $this->GetSettingByName( 'mirroring-run-every', 'hourly' );
	}

	/**
	 * Set mirroring frequency.
	 *
	 * @deprecated 3.3
	 *
	 * @param string $newvalue - New value.
	 */
	public function SetMirroringRunEvery( $newvalue ) {
		$this->AddGlobalSetting( 'mirroring-run-every', $newvalue );
	}

	/**
	 * Check if mirroring stops.
	 *
	 * @return bool value
	 */
	public function IsMirroringStop() {
		return $this->GetSettingByName( 'mirroring-stop' );
	}

	/**
	 * Enable/Disable mirroring stop.
	 *
	 * @param bool $enabled - Value.
	 */
	public function SetMirroringStop( $enabled ) {
		$this->AddGlobalSetting( 'mirroring-stop', $enabled );
	}

	/**
	 * Get Papertrail destination.
	 *
	 * @deprecated 3.3
	 *
	 * @return string value
	 */
	public function GetPapertrailDestination() {
		return trim( $this->GetSettingByName( 'papertrail-destination' ) );
	}

	/**
	 * Set Papertrail destination.
	 *
	 * @deprecated 3.3
	 *
	 * @param string $newvalue - New value.
	 */
	public function SetPapertrailDestination( $newvalue ) {
		$this->AddGlobalSetting( 'papertrail-destination', $newvalue );
	}

	/**
	 * Check if Papertrail colorization is enabled.
	 *
	 * @deprecated 3.3
	 *
	 * @return bool value
	 */
	public function IsPapertrailColorizationEnabled() {
		return $this->GetSettingByName( 'papertrail-colorization-e' );
	}

	/**
	 * Enable/Disable Papertrail colorization.
	 *
	 * @deprecated 3.3
	 *
	 * @param bool $enabled - Value.
	 */
	public function SetPapertrailColorization( $enabled ) {
		if ( ! empty( $enabled ) ) {
			$this->AddGlobalSetting( 'papertrail-colorization-e', $enabled );
		}
	}

	/**
	 * Remove the mirroring DB config.
	 *
	 * @deprecated 3.3
	 */
	public function RemoveMirroringDBConfig() {
		$this->DeleteGlobalSetting( 'wsal-mirror-type' );
		$this->DeleteGlobalSetting( 'wsal-mirror-user' );
		$this->DeleteGlobalSetting( 'wsal-mirror-password' );
		$this->DeleteGlobalSetting( 'wsal-mirror-name' );
		$this->DeleteGlobalSetting( 'wsal-mirror-hostname' );
		$this->DeleteGlobalSetting( 'wsal-mirror-base-prefix' );
		$this->DeleteGlobalSetting( 'wsal-mirror-ssl' );
		$this->DeleteGlobalSetting( 'wsal-mirror-client-certificate' );
		$this->DeleteGlobalSetting( 'wsal-mirror-ssl-ca' );
		$this->DeleteGlobalSetting( 'wsal-mirror-ssl-cert' );
		$this->DeleteGlobalSetting( 'wsal-mirror-ssl-key' );
	}

	/**
	 * Remove the Papertrail config.
	 *
	 * @deprecated 3.3
	 */
	public function RemovePapertrailConfig() {
		$this->DeleteGlobalSetting( 'wsal-papertrail-destination' );
		$this->DeleteGlobalSetting( 'wsal-papertrail-colorization-e' );
	}

	/**
	 * Copy to the mirror Database today alerts
	 * and update last_created date.
	 *
	 * @param stdClass $mirror_args – Mirror arguments.
	 * @param stdClass $connection  – Mirror DB connection.
	 */
	public function mirroring_alerts_to_db( $mirror_args, $connection ) {
		$args['mirroring_db'] = $this->mirror_database_connection( $connection ); // Set mirror db connection.
		$last_created_on      = isset( $mirror_args->last_created ) ? $mirror_args->last_created : false; // Set last created timestamp.
		$args['filter']       = isset( $mirror_args->filter ) ? $mirror_args->filter : 'all'; // Set events filter.
		$args['last_occ_id']  = isset( $mirror_args->last_occ_id ) ? $mirror_args->last_occ_id : 0;

		// Check last created timestamp.
		if ( $last_created_on ) {
			$args['last_created_on'] = $last_created_on;
		} else {
			// If timestamp is empty then start with today's date at 00:00:00.
			$args['last_created_on'] = strtotime( date( 'Y-m-d' ) . ' 00:00:00' );
		}

		// Check for event codes using filter value.
		$mirror_events = array();
		if ( 'event-codes' === $args['filter'] ) {
			$mirror_events = isset( $mirror_args->event_codes ) ? $mirror_args->event_codes : array();
		} elseif ( 'except-codes' === $args['filter'] ) {
			$mirror_events = isset( $mirror_args->exception_codes ) ? $mirror_args->exception_codes : array();
		}

		if ( ! empty( $mirror_events ) && is_array( $mirror_events ) ) {
			$events_str = '(';
			foreach ( $mirror_events as $event ) {
				$events_str .= '\'' . $event . '\',';
			}
			$events_str  = rtrim( $events_str, ',' );
			$events_str .= ')';

			// Set event codes for mirroring.
			$args['events'] = $events_str;
		}

		// Start mirroring.
		$last_event = $this->wsal->getConnector()->MirroringAlertsToDB( $args );

		if ( ! empty( $last_event ) ) {
			// Update last created.
			$mirror_args->last_created = isset( $last_event['created_on'] ) ? $last_event['created_on'] : null;
			$mirror_args->last_occ_id  = isset( $last_event['id'] ) ? $last_event['id'] : null;
			$this->wsal->SetGlobalSetting( WSAL_MIRROR_PREFIX . $mirror_args->name, $mirror_args );
		}
	}

	/**
	 * Get Log Alerts.
	 *
	 * Get last_created alerts used in send_remote_syslog
	 * and send_local_syslog.
	 *
	 * NOTE: Used by papertrail, syslog and slack mirroring.
	 *
	 * @param stdClass $mirror_args – Mirror arguments.
	 * @return WSAL_Models_Occurrence
	 */
	public function get_today_alerts( $mirror_args ) {

		// Query events.
		$query = $this->get_todays_alerts_for_mirroring_query( $mirror_args );
		/** @var \WSAL\MainWPExtension\Models\Occurrence $items */
		$items = $query->getAdapter()->Execute( $query );

		if ( ! empty( $items ) ) {
			// Get the last item.
			$last = end( $items );

			// Update last timestamp and occurrence id.
			$mirror_args->last_created = $last->created_on;
			$mirror_args->last_occ_id  = $last->id;
			$this->wsal->SetGlobalSetting( WSAL_MIRROR_PREFIX . $mirror_args->name, $mirror_args );
		}
		return $items;
	}

	/**
	 * Gets the query to perform that fetches todays alerts for mirroring.
	 *
	 * NOTE: Used by papertrail, syslog and slack mirroring.
	 *
	 * @method get_todays_alerts_for_mirroring_query
	 * @since  4.0.3
	 * @param  stdClass $mirror_args object containing mirroring args.
	 * @return WSAL_Models_OccurrenceQuery
	 */
	public function get_todays_alerts_for_mirroring_query( $mirror_args ) {
		$query           = new WSAL_Models_OccurrenceQuery();
		$last_created_on = isset( $mirror_args->last_created ) ? $mirror_args->last_created : false; // Set last created timestamp.
		$events_filter   = isset( $mirror_args->filter ) ? $mirror_args->filter : 'all'; // Set events filter.
		$last_occ_id     = isset( $mirror_args->last_occ_id ) ? $mirror_args->last_occ_id : false;

		// Check last created timestamp.
		if ( ! empty( $last_created_on ) ) {
			$start_from = $last_created_on;
		} else {
			// If timestamp is empty then start with today's date at 00:00:00.
			$start_from = strtotime( date( 'Y-m-d' ) . ' 00:00:00' );
		}

		// Check for event codes using filter value.
		$mirror_events = array();
		if ( 'event-codes' === $events_filter ) {
			$mirror_events = isset( $mirror_args->event_codes ) ? $mirror_args->event_codes : array();
		} elseif ( 'except-codes' === $events_filter ) {
			$mirror_events = isset( $mirror_args->exception_codes ) ? $mirror_args->exception_codes : array();
		}

		// Formulate events string for mirroring.
		if ( ! empty( $mirror_events ) && is_array( $mirror_events ) ) {
			$mirror_events = implode( ',', $mirror_events );
		}

		// Timestamp condition.
		$query->addCondition( 'created_on > %s ', $start_from );

		if ( $last_occ_id ) {
			$query->addCondition( 'id > %d ', $last_occ_id );
		}

		// Selected events condition if any.
		if ( 'event-codes' === $events_filter ) {
			$query->addCondition( ' find_in_set( alert_id, %s ) ', $mirror_events );
		} elseif ( 'except-codes' === $events_filter ) {
			$query->addCondition( ' NOT find_in_set( alert_id, %s ) ', $mirror_events );
		}

		return $query;
	}

	/**
	 * Use syslog to a remote destination.
	 *
	 * @param int     $site_id                 - Site ID.
	 * @param int     $alert_code              - Alert Code.
	 * @param string  $created_on              - Alert creation timestamp.
	 * @param string  $username                - Username.
	 * @param mixed   $user_roles              - User roles.
	 * @param string  $source_ip               - Source IP.
	 * @param string  $alert_message           - Alert message.
	 * @param string  $papertrail_destination  - Papertrail destination.
	 * @param boolean $papertrail_colorization - Papertrail colorization.
	 */
	public function send_remote_syslog( $site_id, $alert_code, $created_on, $username, $user_roles, $source_ip, $alert_message, $papertrail_destination, $papertrail_colorization = false ) {
		$destination = array_combine( array( 'hostname', 'port' ), explode( ':', $papertrail_destination ) );

		if ( $this->wsal->IsMultisite() ) {
			$info    = get_blog_details( $site_id, true );
			$website = ( ! $info ) ? 'Unknown_site_' . $site_id : str_replace( ' ', '_', $info->blogname );
		} else {
			$website = str_replace( ' ', '_', get_bloginfo( 'name' ) );
		}

		$component = 'Security_Audit_Log';
		$date      = date( 'M d H:i:s', (int) $created_on + $this->GetTimezone() );
		if ( is_string( $source_ip ) ) {
			$source_ip = str_replace( array( '"', '[', ']' ), '', $source_ip );
		}
		$message = ' ,"' . $source_ip . '", ';

		if ( ! empty( $username ) ) {
			if ( is_array( $user_roles ) && count( $user_roles ) ) {
				$user_roles = ucwords( implode( ', ', $user_roles ) );
			} elseif ( is_string( $user_roles ) && $user_roles != '' ) {
				$user_roles = ucwords( str_replace( array( '"', '[', ']' ), ' ', $user_roles ) );
			} else {
				$user_roles = 'Unknown';
			}
			$message .= $username . '(' . $user_roles . ') ';
		}

		$message .= $alert_message;

		if ( $papertrail_colorization ) {
			$message = $this->colorise_json( $message );
		}

		$code = $this->wsal->alerts->GetAlert( $alert_code );
		$code = isset( $code->code ) ? $code->code : 0;

		$const = (object) array(
			'name'        => 'E_UNKNOWN',
			'value'       => 0,
			'description' => __( 'Unknown error code.', 'wp-security-audit-log' ),
		);
		$const = $this->wsal->constants->GetConstantBy( 'value', $code, $const );

		$severity = 7; // Default severity.
		switch ( $const->name ) {
			case 'E_NOTICE':
				$severity = 6;
				break;
			case 'E_WARNING':
				$severity = 4;
				break;
			case 'E_CRITICAL':
				$severity = 1;
				break;
			default:
				break;
		}

		$sock = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
		foreach ( explode( "\n", $message ) as $line ) {
			$syslog_message = "<$severity>" . $date . ' ' . $website . ' ' . $component . ':' . $line;
			socket_sendto( $sock, $syslog_message, strlen( $syslog_message ), 0, $destination['hostname'], $destination['port'] );
		}
		socket_close( $sock );
	}

	/**
	 * Use syslog to the local machine.
	 *
	 * @param int    $site_id       - Site ID.
	 * @param int    $alert_code    - Alert Code.
	 * @param string $username      - Username.
	 * @param string $alert_message - Alert message.
	 * @param int    $code          - Code.
	 */
	public function send_local_syslog( $site_id, $alert_code, $username, $alert_message, $code ) {
		$code       = $code ? $code->code : E_NOTICE;
		$a_priority = array(
			E_CRITICAL => LOG_CRIT,
			E_ERROR    => LOG_ERR,
			E_WARNING  => LOG_WARNING,
			E_NOTICE   => LOG_ALERT,
		);
		$website    = ' on website ';
		if ( $this->wsal->IsMultisite() ) {
			$info     = get_blog_details( $site_id, true );
			$website .= ( ! $info ) ? 'Unknown Site ' . $site_id : $info->blogname;
		} else {
			$website .= str_replace( ' ', '_', get_bloginfo( 'name' ) );
		}
		$alert_message = 'Alert ' . $alert_code . $website . ': ' . $username . ' has ' . $alert_message;

		openlog( 'Security_Audit_Log', LOG_NDELAY, LOG_USER );
		syslog( $a_priority[ $code ], $alert_message );
		closelog();
	}

	/**
	 * Mirroring alerts.
	 *
	 * @param stdClass $mirror_name - Mirror name.
	 * @param boolean  $mirror_now  - Set to true if manually mirroring.
	 * @return boolean
	 */
	public function mirroring_alerts( $mirror_name, $mirror_now = false ) {
		// Get mirror details.
		$mirror_args = $this->GetSettingByName( WSAL_MIRROR_PREFIX . $mirror_name );
		$connection  = $this->GetSettingByName( WSAL_CONN_PREFIX . $mirror_args->connection );

		if ( 'mysql' === $connection->type ) {
			$this->mirroring_alerts_to_db( $mirror_args, $connection );
		} elseif ( 'papertrail' === $connection->type ) {
			// Test connection.
			if ( $mirror_now && ! $this->test_remote_syslog( $connection->destination ) ) {
				return false;
			}

			$alerts = $this->get_today_alerts( $mirror_args );
			if ( ! empty( $alerts ) ) {
				foreach ( $alerts as $item ) {
					$this->send_remote_syslog(
						$item->site_id,
						$item->alert_id,
						$item->created_on,
						$item->GetUsername(),
						$item->GetUserRoles(),
						$item->GetSourceIP(),
						$item->GetMessage(),
						$connection->destination,
						$connection->colorization
					);
				}
			}
		} elseif ( 'syslog' === $connection->type ) {
			$alerts = $this->get_today_alerts( $mirror_args );
			if ( ! empty( $alerts ) ) {
				if ( 'remote' === $connection->location ) {
					$destination = $connection->remote_ip . ':' . $connection->remote_port;
					// Test connection.
					if ( $mirror_now && ! $this->test_remote_syslog( $destination ) ) {
						return false;
					}

					foreach ( $alerts as $item ) {
						$this->send_remote_syslog(
							$item->site_id,
							$item->alert_id,
							$item->created_on,
							$item->GetUsername(),
							$item->GetUserRoles(),
							$item->GetSourceIP(),
							$item->GetMessage(),
							$destination,
							false
						);
					}
				} else {
					foreach ( $alerts as $item ) {
						$this->send_local_syslog( $item->site_id, $item->alert_id, $item->GetUsername(), $item->GetMessage(), $this->wsal->alerts->GetAlert( $item->alert_id ) );
					}
				}
			}
		} elseif ( 'slack' === $connection->type ) {
			$alerts     = $this->get_today_alerts( $mirror_args ); // Get alerts.
			$gmt_offset = $this->GetTimezone(); // Get user selected timezone.
			$datetime_f = $this->wsal->settings()->GetDatetimeFormat(); // Get date time format.

			$slack_attachments = array();
			if ( ! empty( $alerts ) ) {
				foreach ( $alerts as $alert ) {
					$slack_attachments[] = $this->get_slack_event_attachment( $alert, $gmt_offset, $datetime_f );
				}
			}

			if ( ! empty( $slack_attachments ) ) {
				// Message body.
				$message_body = wp_json_encode(
					array(
						'username'    => $connection->bot_name,
						'attachments' => $slack_attachments,
					)
				);

				// Slack POST message arguments.
				$message_args = array(
					'headers' => array( 'Content-Type' => 'application/json' ),
					'body'    => str_replace( '\n', 'n', $message_body ),
				);

				// POST the message.
				wp_remote_post( $connection->webhook_url, $message_args );
			}
		}
		return true;
	}

	/**
	 * Test Papertrail Connection.
	 *
	 * @param stdClass $dest - Connection destination.
	 */
	public function test_remote_syslog( $dest ) {
		// Get papertrail configs.
		$destination = array_combine( array( 'hostname', 'port' ), explode( ':', $dest ) );
		$socket      = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );

		// Connection response.
		$response = false;

		if ( socket_connect( $socket, $destination['hostname'], $destination['port'] ) ) {
			// Get website info.
			if ( $this->wsal->IsMultisite() ) {
				$site_id = get_current_blog_id();
				$info    = get_blog_details( $site_id, true );
				$website = ( ! $info ) ? 'Unknown_site_' . $site_id : str_replace( ' ', '_', $info->blogname );
			} else {
				$website = str_replace( ' ', '_', get_bloginfo( 'name' ) );
			}

			$current_date   = date( 'M d H:i:s', time() + $this->GetTimezone() );
			$syslog_message = '<6>' . $current_date . ' ' . $website . ' Security_Audit_Log:Test message by WP Activity Log plugin';
			if ( socket_sendto( $socket, $syslog_message, strlen( $syslog_message ), 0, $destination['hostname'], $destination['port'] ) ) {
				$response = true;
			}
		}
		socket_close( $socket );
		return $response;
	}

	/**
	 * Send Event to Slack.
	 *
	 * Slack Message Format:
	 *     Site: — Optional for multisite.
	 *     ID:
	 *     Date & Time:
	 *     Severity:
	 *     User:
	 *     Role:
	 *     Message:
	 *
	 * @since 3.3
	 *
	 * @param object  $alert      – WSAL alert object.
	 * @param integer $timezone   – Timezone.
	 * @param string  $datetime_f – Date time format.
	 * @return array
	 */
	public function get_slack_event_attachment( $alert, $timezone, $datetime_f ) {
		// Verify that the alert is not empty.
		if ( empty( $alert ) ) {
			return;
		}

		// Confirm the alert object.
		if ( ! $alert instanceof WSAL_Models_Occurrence ) {
			return;
		}

		// Get alert details.
		if ( $this->wsal->IsMultisite() ) {
			// Get blog details.
			$info = get_blog_details( $alert->site_id, true );

			/* Translators: Site ID. */
			$website = ( ! $info ) ? 'Unknown Site' : $info->blogname;
		} else {
			$website = get_bloginfo( 'name' );
		}

		if ( empty( $this->wsal->alerts ) ) {
			$this->wsal->alerts = new WSAL_AlertManager( $this->wsal );
		}

		// Severity.
		$code  = $this->wsal->alerts->GetAlert( $alert->alert_id );
		$code  = isset( $code->code ) ? $code->code : 0;
		$const = (object) array(
			'name'        => 'E_UNKNOWN',
			'value'       => 0,
			'description' => __( 'Unknown error code.', 'wp-security-audit-log' ),
		);

		if ( empty( $this->wsal->constants ) ) {
			$this->wsal->constants = new WSAL_ConstantManager( $this->wsal );
		}

		$const = $this->wsal->constants->GetConstantBy( 'value', $code, $const );
		if ( 'E_WARNING' === $const->name ) {
			$color    = 'warning';
			$severity = 'Warning';
		} elseif ( 'E_CRITICAL' === $const->name ) {
			$color    = 'danger';
			$severity = 'High';
		} else {
			$color    = 'good';
			$severity = 'Notice';
		}

		// User roles.
		$roles = $alert->GetUserRoles();
		if ( is_array( $roles ) && count( $roles ) ) {
			$roles = esc_html( ucwords( implode( ', ', $roles ) ) );
		} elseif ( is_string( $roles ) && '' !== $roles ) {
			$roles = esc_html( ucwords( str_replace( array( '"', '[', ']' ), ' ', $roles ) ) );
		} else {
			$roles = 'Unknown';
		}

		// Username.
		$username = $alert->GetUsername();

		// Event IP address.
		$ips = $alert->GetSourceIP();

		if ( is_string( $ips ) ) {
			$ips = str_replace( array( '"', '[', ']' ), '', $ips );
		}

		if ( is_null( $ips ) || '' === $ips ) {
			$ips = 'Unknown';
		} else {
			// If there's only one IP.
			$link = 'https://whatismyipaddress.com/ip/' . $ips . '?utm_source=plugin&utm_medium=referral&utm_campaign=WPSAL';
			$ips  = '<' . $link . '|' . $ips . '>';
		}

		return array(
			'color'     => $color,
			'title'     => 'Event Message:',
			'text'      => $alert->GetMessage( array( $this->wsal->settings, 'slack_meta_formatter' ) ),
			'ts'        => $alert->created_on,
			'mrkdwn_in' => array( 'text' ),
			'fields'    => array(
				array(
					'title' => 'Site',
					'value' => $website,
					'short' => true,
				),
				array(
					'title' => 'ID',
					'value' => $alert->alert_id,
					'short' => true,
				),
				array(
					'title' => 'Severity',
					'value' => $severity,
					'short' => true,
				),
				array(
					'title' => 'User',
					'value' => ( ! $username ) ? 'System' : $username,
					'short' => true,
				),
				array(
					'title' => 'Role',
					'value' => $roles,
					'short' => true,
				),
				array(
					'title' => 'IP',
					'value' => $ips,
					'short' => true,
				),
			),
		);
	}

	/**
	 * Colorise Papertrail App Message.
	 *
	 * @param string $json – Message.
	 * @return string
	 */
	private function colorise_json( $json ) {
		$seq    = array(
			'reset' => "\033[0m",
			'color' => "\033[1;%dm",
			'bold'  => "\033[1m",
		);
		$fcolor = array(
			'black'   => "\033[30m",
			'red'     => "\033[31m",
			'green'   => "\033[32m",
			'yellow'  => "\033[33m",
			'blue'    => "\033[34m",
			'magenta' => "\033[35m",
			'cyan'    => "\033[36m",
			'white'   => "\033[37m",
		);
		$bcolor = array(
			'black'   => "\033[40m",
			'red'     => "\033[41m",
			'green'   => "\033[42m",
			'yellow'  => "\033[43m",
			'blue'    => "\033[44m",
			'magenta' => "\033[45m",
			'cyan'    => "\033[46m",
			'white'   => "\033[47m",
		);
		$output = $json;
		$output = preg_replace( '/(":)([0-9]+)/', '$1' . $fcolor['magenta'] . '$2' . $seq['reset'], $output );
		$output = preg_replace( '/(":)(true|false)/', '$1' . $fcolor['magenta'] . '$2' . $seq['reset'], $output );
		$output = str_replace( '{"', '{' . $fcolor['green'] . '"', $output );
		$output = str_replace( ',"', ',' . $fcolor['green'] . '"', $output );
		$output = str_replace( '":', '"' . $seq['reset'] . ':', $output );
		$output = str_replace( ':"', ':' . $fcolor['green'] . '"', $output );
		$output = str_replace( '",', '"' . $seq['reset'] . ',', $output );
		$output = str_replace( '",', '"' . $seq['reset'] . ',', $output );
		$output = $seq['reset'] . $output . $seq['reset'];
		return $output;
	}

	/**
	 * Get the Mirror connection.
	 *
	 * @param stdClass $connection – Mirror DB Connection.
	 * @return wpdb Instance of WPDB
	 */
	private function mirror_database_connection( $connection ) {
		if ( ! empty( self::$_mirror_db ) ) {
			return self::$_mirror_db;
		} else {
			$connection_config = array(
				'type'        => isset( $connection->type ) ? $connection->type : false,
				'user'        => isset( $connection->user ) ? $connection->user : false,
				'password'    => isset( $connection->password ) ? $connection->password : false,
				'name'        => isset( $connection->db_name ) ? $connection->db_name : false,
				'hostname'    => isset( $connection->hostname ) ? $connection->hostname : false,
				'base_prefix' => isset( $connection->baseprefix ) ? $connection->baseprefix : false,
				'is_ssl'      => isset( $connection->is_ssl ) ? $connection->is_ssl : false,
				'is_cc'       => isset( $connection->is_cc ) ? $connection->is_cc : false,
				'ssl_ca'      => isset( $connection->ssl_ca ) ? $connection->ssl_ca : false,
				'ssl_cert'    => isset( $connection->ssl_cert ) ? $connection->ssl_cert : false,
				'ssl_key'     => isset( $connection->ssl_key ) ? $connection->ssl_key : false,
			);
			if ( empty( $connection_config ) ) {
				return null;
			} else {
				// Get mirror DB connection.
				self::$_mirror_db = $this->CreateConnection( $connection_config );

				// Check the connection for errors.
				$connected = true;
				if ( isset( self::$_mirror_db->dbh->errno ) ) {
					$connected = 0 !== (int) self::$_mirror_db->dbh->errno ? false : true; // Database connection error check.
				} elseif ( is_wp_error( self::$_mirror_db->error ) ) {
					$connected = false;
				}

				if ( $connected ) {
					return self::$_mirror_db;
				} else {
					return null;
				}
			}
		}
	}

	/**
	 * Get the Mirror current config.
	 *
	 * @deprecated 3.3
	 *
	 * @return array|null config
	 */
	private function GetMirrorConfig() {
		$type = $this->GetSettingByName( 'mirror-type' );
		if ( empty( $type ) ) {
			return null;
		} else {
			return array(
				'type'        => $this->GetSettingByName( 'mirror-type' ),
				'user'        => $this->GetSettingByName( 'mirror-user' ),
				'password'    => $this->GetSettingByName( 'mirror-password' ),
				'name'        => $this->GetSettingByName( 'mirror-name' ),
				'hostname'    => $this->GetSettingByName( 'mirror-hostname' ),
				'base_prefix' => $this->GetSettingByName( 'mirror-base-prefix' ),
				'is_ssl'      => $this->GetSettingByName( 'mirror-ssl' ),
				'is_cc'       => $this->GetSettingByName( 'mirror-client-certificate' ),
				'ssl_ca'      => $this->GetSettingByName( 'mirror-ssl-ca' ),
				'ssl_cert'    => $this->GetSettingByName( 'mirror-ssl-cert' ),
				'ssl_key'     => $this->GetSettingByName( 'mirror-ssl-key' ),
			);
		}
	}

	/*============================== Archiving functions ==============================*/

	/**
	 * Check if archiving is enabled.
	 *
	 * @return bool value
	 */
	public function IsArchivingEnabled() {
		return $this->GetSettingByName( 'archiving-e' );
	}

	/**
	 * Enable/Disable archiving.
	 *
	 * @param bool $enabled - Value.
	 */
	public function SetArchivingEnabled( $enabled ) {
		$this->AddGlobalSetting( 'archiving-e', $enabled );
		if ( empty( $enabled ) ) {
			$this->RemoveArchivingConfig();
			$this->DeleteGlobalSetting( 'wsal-archiving-last-created' );
		}
	}

	/**
	 * Check if archiving by date is enabled.
	 *
	 * @deprecated 3.4
	 *
	 * @return bool value
	 */
	public function IsArchivingDateEnabled() {
		return true;
	}

	/**
	 * Enable/Disable archiving by date.
	 *
	 * @deprecated 3.4
	 *
	 * @param bool $enabled - Value.
	 */
	public function SetArchivingDateEnabled( $enabled ) {}

	/**
	 * Check if archiving by limit is enabled.
	 *
	 * @deprecated 3.4
	 *
	 * @return bool value
	 */
	public function IsArchivingLimitEnabled() {
		return false;
	}

	/**
	 * Enable/Disable archiving by limit.
	 *
	 * @deprecated 3.4
	 *
	 * @param bool $enabled - Value.
	 */
	public function SetArchivingLimitEnabled( $enabled ) {}

	/**
	 * Get archiving date.
	 *
	 * @return int value
	 */
	public function GetArchivingDate() {
		return (int) $this->GetSettingByName( 'archiving-date', 1 );
	}

	/**
	 * Set archiving date.
	 *
	 * @param string $newvalue - New value.
	 */
	public function SetArchivingDate( $newvalue ) {
		$this->AddGlobalSetting( 'archiving-date', (int) $newvalue );
	}

	/**
	 * Get archiving date type.
	 *
	 * @return string value
	 */
	public function GetArchivingDateType() {
		return $this->GetSettingByName( 'archiving-date-type', 'months' );
	}

	/**
	 * Set archiving date type.
	 *
	 * @param string $newvalue - New value.
	 */
	public function SetArchivingDateType( $newvalue ) {
		$this->AddGlobalSetting( 'archiving-date-type', $newvalue );
	}

	/**
	 * Get archiving limit.
	 *
	 * @deprecated 3.4
	 *
	 * @return int value
	 */
	public function GetArchivingLimit() {
		return false;
	}

	/**
	 * Set archiving date limit.
	 *
	 * @deprecated 3.4
	 *
	 * @param string $newvalue - New value.
	 */
	public function SetArchivingLimit( $newvalue ) {}

	/**
	 * Get archiving frequency.
	 *
	 * @return string frequency
	 */
	public function GetArchivingRunEvery() {
		return $this->GetSettingByName( 'archiving-run-every', 'hourly' );
	}

	/**
	 * Set archiving frequency.
	 *
	 * @param string $newvalue - New value.
	 */
	public function SetArchivingRunEvery( $newvalue ) {
		$this->AddGlobalSetting( 'archiving-run-every', $newvalue );
	}

	/**
	 * Check if archiving stop.
	 *
	 * @return bool value
	 */
	public function IsArchivingStop() {
		return $this->GetSettingByName( 'archiving-stop' );
	}

	/**
	 * Enable/Disable archiving stop.
	 *
	 * @param bool $enabled - Value.
	 */
	public function SetArchivingStop( $enabled ) {
		$this->AddGlobalSetting( 'archiving-stop', $enabled );
	}

	/**
	 * Remove the archiving config.
	 */
	public function RemoveArchivingConfig() {
		// Get archive connection.
		$archive_conn_name = $this->GetSettingByName( 'archive-connection' );

		if ( $archive_conn_name ) {
			$archive_connection           = $this->get_ext_connection( $archive_conn_name );
			$archive_connection->used_for = '';
			$this->set_ext_connection( $archive_connection );
		}

		$this->DeleteGlobalSetting( 'wsal-archiving-date' );
		$this->DeleteGlobalSetting( 'wsal-archiving-date-type' );

		$this->DeleteGlobalSetting( 'wsal-archive-connection' );
		$this->DeleteGlobalSetting( 'wsal-archive-type' );
		$this->DeleteGlobalSetting( 'wsal-archive-user' );
		$this->DeleteGlobalSetting( 'wsal-archive-password' );
		$this->DeleteGlobalSetting( 'wsal-archive-name' );
		$this->DeleteGlobalSetting( 'wsal-archive-hostname' );
		$this->DeleteGlobalSetting( 'wsal-archive-base-prefix' );
		$this->DeleteGlobalSetting( 'wsal-archive-url-base-prefix' );
		$this->DeleteGlobalSetting( 'wsal-archive-ssl' );
		$this->DeleteGlobalSetting( 'wsal-archive-client-certificate' );
		$this->DeleteGlobalSetting( 'wsal-archive-ssl-ca' );
		$this->DeleteGlobalSetting( 'wsal-archive-ssl-cert' );
		$this->DeleteGlobalSetting( 'wsal-archive-ssl-key' );

		$this->DeleteGlobalSetting( 'wsal-archiving-run-every' );
		$this->DeleteGlobalSetting( 'wsal-archiving-daily-e' );
		$this->DeleteGlobalSetting( 'wsal-archiving-weekly-e' );
		$this->DeleteGlobalSetting( 'wsal-archiving-week-day' );
		$this->DeleteGlobalSetting( 'wsal-archiving-time' );
	}

	/**
	 * Disable the pruning config.
	 */
	public function DisablePruning() {
		$this->SetGlobalBooleanSetting( 'pruning-date-e', false );
		$this->SetGlobalBooleanSetting( 'pruning-limit-e', false );
	}

	/**
	 * Archive alerts (Occurrences table)
	 *
	 * @param array $args - Arguments array.
	 */
	public function ArchiveOccurrence( $args ) {
		$args['archive_db'] = $this->ArchiveDatabaseConnection();
		if ( empty( $args['archive_db'] ) ) {
			return false;
		}
		$last_created_on = $this->GetSettingByName( 'archiving-last-created' );
		if ( ! empty( $last_created_on ) ) {
			$args['last_created_on'] = $last_created_on;
		}
		return $this->wsal->getConnector()->ArchiveOccurrence( $args );
	}

	/**
	 * Archive alerts (Metadata table)
	 *
	 * @param array $args - Arguments array.
	 */
	public function ArchiveMeta( $args ) {
		$args['archive_db'] = $this->ArchiveDatabaseConnection();
		return $this->wsal->getConnector()->ArchiveMeta( $args );
	}

	/**
	 * Delete alerts from the source tables
	 * after archiving them.
	 *
	 * @param array $args - Arguments array.
	 */
	public function DeleteAfterArchive( $args ) {
		$args['archive_db'] = $this->ArchiveDatabaseConnection();
		$this->wsal->getConnector()->DeleteAfterArchive( $args );
		if ( ! empty( $args['last_created_on'] ) ) {
			// update last_created
			$this->AddGlobalSetting( 'archiving-last-created', $args['last_created_on'] );
		}
	}

	/**
	 * Check if archiving cron job started.
	 *
	 * @return bool
	 */
	public function IsArchivingCronStarted() {
		return $this->GetSettingByName( 'archiving-cron-started' );
	}

	/**
	 * Enable/Disable archiving cron job started option.
	 *
	 * @param bool $value - Value.
	 */
	public function SetArchivingCronStarted( $value ) {
		if ( ! empty( $value ) ) {
			$this->AddGlobalSetting( 'archiving-cron-started', 1 );
		} else {
			$this->DeleteGlobalSetting( 'wsal-archiving-cron-started' );
		}
	}

	/**
	 * Archiving alerts.
	 */
	public function archiving_alerts() {
		if ( ! $this->IsArchivingCronStarted() ) {
			set_time_limit( 0 );
			// Start archiving.
			$this->SetArchivingCronStarted( true );

			$args          = array();
			$args['limit'] = 100;
			$args_result   = false;

			do {
				$num             = $this->GetArchivingDate();
				$type            = $this->GetArchivingDateType();
				$now             = current_time( 'timestamp' );
				$args['by_date'] = $now - ( strtotime( $num . ' ' . $type ) - $now );
				$args_result     = $this->ArchiveOccurrence( $args );
				if ( ! empty( $args_result ) ) {
					$args_result = $this->ArchiveMeta( $args_result );
				}
				if ( ! empty( $args_result ) ) {
					$this->DeleteAfterArchive( $args_result );
				}
			} while ( $args_result != false );
			// End archiving.
			$this->SetArchivingCronStarted( false );
		}
	}

	/**
	 * Get the Archive connection
	 *
	 * @return wpdb Instance of WPDB
	 */
	private function ArchiveDatabaseConnection() {
		if ( ! empty( self::$_archive_db ) ) {
			return self::$_archive_db;
		} else {
			$connection_config = $this->GetArchiveConfig();
			if ( empty( $connection_config ) ) {
				return null;
			} else {
				// Get archive DB connection.
				self::$_archive_db = $this->CreateConnection( $connection_config );

				// Check object for disconnection or other errors.
				$connected = true;
				if ( isset( self::$_archive_db->dbh->errno ) ) {
					$connected = 0 !== (int) self::$_archive_db->dbh->errno ? false : true; // Database connection error check.
				} elseif ( is_wp_error( self::$_archive_db->error ) ) {
					$connected = false;
				}

				if ( $connected ) {
					return self::$_archive_db;
				} else {
					return null;
				}
			}
		}
	}

	/**
	 * Get the Archive config
	 *
	 * @return array|null config
	 */
	private function GetArchiveConfig() {
		$type = $this->GetSettingByName( 'archive-type' );
		if ( empty( $type ) ) {
			return null;
		} else {
			return array(
				'type'        => $this->GetSettingByName( 'archive-type' ),
				'user'        => $this->GetSettingByName( 'archive-user' ),
				'password'    => $this->GetSettingByName( 'archive-password' ),
				'name'        => $this->GetSettingByName( 'archive-name' ),
				'hostname'    => $this->GetSettingByName( 'archive-hostname' ),
				'base_prefix' => $this->GetSettingByName( 'archive-base-prefix' ),
				'is_ssl'      => $this->GetSettingByName( 'archive-ssl' ),
				'is_cc'       => $this->GetSettingByName( 'archive-client-certificate' ),
				'ssl_ca'      => $this->GetSettingByName( 'archive-ssl-ca' ),
				'ssl_cert'    => $this->GetSettingByName( 'archive-ssl-cert' ),
				'ssl_key'     => $this->GetSettingByName( 'archive-ssl-key' ),
			);
		}
	}

	/**
	 * Return Connection Object.
	 *
	 * @since 3.3
	 *
	 * @param string $connection_name - Connection name.
	 * @return mixed
	 */
	public function get_ext_connection( $connection_name ) {
		if ( empty( $connection_name ) ) {
			return false;
		}
		return $this->GetSettingByName( WSAL_CONN_PREFIX . $connection_name );
	}

	/**
	 * Set Connection Object.
	 *
	 * @since 3.3
	 *
	 * @param stdClass $connection - Connection object.
	 */
	public function set_ext_connection( $connection ) {
		if ( empty( $connection ) ) {
			return;
		}
		$this->AddGlobalSetting( WSAL_CONN_PREFIX . $connection->name, $connection );
	}

	/**
	 * Return Mirror Object.
	 *
	 * @since 3.3
	 *
	 * @param string $mirror_name - Mirror name.
	 * @return mixed
	 */
	public function get_ext_mirror( $mirror_name ) {
		if ( empty( $mirror_name ) ) {
			return false;
		}
		return $this->GetSettingByName( WSAL_MIRROR_PREFIX . $mirror_name );
	}

	/**
	 * Set Mirror Object.
	 *
	 * @since 3.3
	 *
	 * @param stdClass $mirror - Mirror object.
	 */
	public function set_ext_mirror( $mirror ) {
		if ( empty( $mirror ) ) {
			return;
		}
		$this->AddGlobalSetting( WSAL_MIRROR_PREFIX . $mirror->name, $mirror );
	}
}
