<?php
/**
 * Extensions Manager Class
 *
 * Class file for extensions management.
 *
 * @since 3.0.0
 * @package Wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WSAL_Extension_Manager' ) ) :

	/**
	 * WSAL_Extension_Manager.
	 *
	 * Extension manager class.
	 */
	class WSAL_Extension_Manager {

		/**
		 * Extensions.
		 *
		 * @var array
		 */
		public $extensions;

		/**
		 * WSAL Instance.
		 *
		 * @var WpSecurityAuditLog
		 */
		public $wsal;

		/**
		 * Method: Constructor.
		 */
		public function __construct() {
			// Include extension files.
			$this->includes();

			// Initialize the extensions.
			$this->init();
		}

		/**
		 * Include extension manually.
		 *
		 * @param string $extension - Extension.
		 */
		public static function include_extension( $extension ) {
			switch ( $extension ) {
				case 'search':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/search/search-init.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/search/search-init.php';
						new WSAL_SearchExtension();
					}
					break;
				case 'notifications':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/email-notifications/email-notifications.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/email-notifications/email-notifications.php';
						new WSAL_NP_Plugin();
					}
					break;
				case 'reports':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/reports/reports-init.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/reports/reports-init.php';
						new WSAL_Rep_Plugin();
					}
					break;
				case 'sessions':
				case 'usersessions':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/users-sessions/user-sessions.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/users-sessions/user-sessions.php';
						new WSAL_UserSessions_Plugin();
					}
					break;
				case 'external-db':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/external-db/external-db-init.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/external-db/external-db-init.php';
						new WSAL_Ext_Plugin();
					}
					break;
				default:
					break;
			}
		}

		/**
		 * Method: Include extensions.
		 */
		protected function includes() {
			// Extensions for BASIC and above plans.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'starter' ) ) {
				/**
				 * Search.
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/search/search-init.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/search/search-init.php';
				}

				/**
				 * Email Notifications.
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/email-notifications/email-notifications.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/email-notifications/email-notifications.php';
				}
			}

			// Extensions for PROFESSIONAL and above plans.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'professional' ) ) {
				/**
				 * Reports
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/reports/reports-init.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/reports/reports-init.php';
				}

				/**
				 * Users Sessions Management.
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/user-sessions/user-sessions.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/user-sessions/user-sessions.php';
				}

				/**
				 * External DB
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/external-db/external-db-init.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/external-db/external-db-init.php';
				}
			}
		}

		/**
		 * Method: Initialize the extensions.
		 */
		protected function init() {
			// Basic package extensions.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'starter' ) ) {
				// Search filters.
				if ( class_exists( 'WSAL_SearchExtension' ) ) {
					$this->extensions[] = new WSAL_SearchExtension();
				}

				// Email Notifications.
				if ( class_exists( 'WSAL_NP_Plugin' ) ) {
					$this->extensions[] = new WSAL_NP_Plugin();
				}
			}

			// Professional package extensions.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'professional' ) ) {
				// Reports.
				if ( class_exists( 'WSAL_Rep_Plugin' ) ) {
					$this->extensions[] = new WSAL_Rep_Plugin();
				}

				// Users Sessions Management.
				if ( class_exists( 'WSAL_UserSessions_Plugin' ) ) {
					$this->extensions[] = new WSAL_UserSessions_Plugin();
				}

				// External DB.
				if ( class_exists( 'WSAL_Ext_Plugin' ) ) {
					$this->extensions[] = new WSAL_Ext_Plugin();
				}
			}
		}

		/**
		 * Update External DB Options.
		 *
		 * Update routine to create external/archiving/mirror
		 * and connection options.
		 *
		 * @since 3.3
		 *
		 * @param WpSecurityAuditLog $wsal – Instance of WpSecurityAuditLog.
		 */
		public function update_external_db_options( WpSecurityAuditLog $wsal ) {
			// Get adapter options.
			$adapter_type            = $wsal->GetGlobalSetting( 'adapter-type' );
			$adapter_user            = $wsal->GetGlobalSetting( 'adapter-user' );
			$adapter_password        = $wsal->GetGlobalSetting( 'adapter-password' );
			$adapter_name            = $wsal->GetGlobalSetting( 'adapter-name' );
			$adapter_hostname        = $wsal->GetGlobalSetting( 'adapter-hostname' );
			$adapter_base_prefix     = $wsal->GetGlobalSetting( 'adapter-base-prefix' );
			$adapter_url_base_prefix = $wsal->GetGlobalSetting( 'adapter-url-base-prefix' );
			$adapter_ssl             = $wsal->GetGlobalSetting( 'adapter-ssl' );
			$adapter_cc              = $wsal->GetGlobalSetting( 'adapter-client-certificate' );
			$adapter_ssl_ca          = $wsal->GetGlobalSetting( 'adapter-ssl-ca' );
			$adapter_ssl_cert        = $wsal->GetGlobalSetting( 'adapter-ssl-cert' );
			$adapter_ssl_key         = $wsal->GetGlobalSetting( 'adapter-ssl-key' );

			// Create a new DB connection for external DB.
			$connection             = new stdClass();
			$connection->name       = 'wsalExtConn';
			$connection->type       = 'mysql';
			$connection->user       = $adapter_user;
			$connection->password   = $adapter_password;
			$connection->db_name    = $adapter_name;
			$connection->hostname   = $adapter_hostname;
			$connection->baseprefix = $adapter_base_prefix;
			$connection->url_prefix = ( 'on' === $adapter_url_base_prefix ) ? true : false;
			$connection->is_ssl     = $adapter_ssl;
			$connection->is_cc      = $adapter_cc;
			$connection->ssl_ca     = $adapter_ssl_ca;
			$connection->ssl_cert   = $adapter_ssl_cert;
			$connection->ssl_key    = $adapter_ssl_key;
			$connection->used_for   = __( 'External Storage', 'wp-security-audit-log' );

			// Save the connection.
			$wsal->SetGlobalSetting( 'connection-wsalExtConn', $connection );
			$wsal->SetGlobalSetting( 'adapter-connection', 'wsalExtConn' );

			// Get mirroring options.
			$mirroring_e         = $wsal->GetGlobalSetting( 'mirroring-e' );
			$mirroring_type      = $wsal->GetGlobalSetting( 'mirroring-type' );
			$mirroring_frequency = $wsal->GetGlobalSetting( 'mirroring-run-every' );
			$mirroring_last_crtd = $wsal->GetGlobalSetting( 'mirroring-last-created' );

			if ( 'database' === $mirroring_type ) {
				$mirror_type     = $wsal->GetGlobalSetting( 'mirror-type' );
				$mirror_user     = $wsal->GetGlobalSetting( 'mirror-user' );
				$mirror_password = $wsal->GetGlobalSetting( 'mirror-password' );
				$mirror_name     = $wsal->GetGlobalSetting( 'mirror-name' );
				$mirror_hostname = $wsal->GetGlobalSetting( 'mirror-hostname' );
				$mirror_prefix   = $wsal->GetGlobalSetting( 'mirror-base-prefix' );
				$mirror_url_base = $wsal->GetGlobalSetting( 'mirror-url-base-prefix' );
				$mirror_ssl      = $wsal->GetGlobalSetting( 'mirror-ssl' );
				$mirror_cc       = $wsal->GetGlobalSetting( 'mirror-client-certificate' );
				$mirror_ssl_ca   = $wsal->GetGlobalSetting( 'mirror-ssl-ca' );
				$mirror_ssl_cert = $wsal->GetGlobalSetting( 'mirror-ssl-cert' );
				$mirror_ssl_key  = $wsal->GetGlobalSetting( 'mirror-ssl-key' );

				// Create a new DB connection for mirror DB.
				$mirror_connection             = new stdClass();
				$mirror_connection->name       = 'wsalMirrorCC';
				$mirror_connection->type       = 'mysql';
				$mirror_connection->user       = $mirror_user;
				$mirror_connection->password   = $mirror_password;
				$mirror_connection->db_name    = $mirror_name;
				$mirror_connection->hostname   = $mirror_hostname;
				$mirror_connection->baseprefix = $mirror_prefix;
				$mirror_connection->url_prefix = ( 'on' === $mirror_url_base ) ? true : false;
				$mirror_connection->is_ssl     = $mirror_ssl;
				$mirror_connection->is_cc      = $mirror_cc;
				$mirror_connection->ssl_ca     = $mirror_ssl_ca;
				$mirror_connection->ssl_cert   = $mirror_ssl_cert;
				$mirror_connection->ssl_key    = $mirror_ssl_key;
				$mirror_connection->used_for   = __( 'Mirroring', 'wp-security-audit-log' );

				// Save the connection.
				$wsal->SetGlobalSetting( 'connection-wsalMirrorCC', $mirror_connection );

				// Create the mirror object.
				$mirror               = new stdClass();
				$mirror->name         = 'wsalMirrorDB';
				$mirror->connection   = 'wsalMirrorCC';
				$mirror->frequency    = 'hourly';
				$mirror->filter       = 'all';
				$mirror->state        = ( $mirroring_e ) ? true : false;
				$mirror->last_created = $mirroring_last_crtd;

				// Save the mirror.
				$wsal->SetGlobalSetting( 'mirror-wsalMirrorDB', $mirror );

				$wsal->DeleteSettingByName( 'wsal-mirror-type' );
				$wsal->DeleteSettingByName( 'wsal-mirror-user' );
				$wsal->DeleteSettingByName( 'wsal-mirror-password' );
				$wsal->DeleteSettingByName( 'wsal-mirror-name' );
				$wsal->DeleteSettingByName( 'wsal-mirror-hostname' );
				$wsal->DeleteSettingByName( 'wsal-mirror-base-prefix' );
				$wsal->DeleteSettingByName( 'wsal-mirror-url-base-prefix' );
				$wsal->DeleteSettingByName( 'wsal-mirror-ssl' );
				$wsal->DeleteSettingByName( 'wsal-mirror-client-certificate' );
				$wsal->DeleteSettingByName( 'wsal-mirror-ssl-ca' );
				$wsal->DeleteSettingByName( 'wsal-mirror-ssl-cert' );
				$wsal->DeleteSettingByName( 'wsal-mirror-ssl-key' );
			} elseif ( 'papertrail' === $mirroring_type ) {
				// Get papertrail options.
				$papertrail_destination  = $wsal->GetGlobalSetting( 'papertrail-destination' );
				$papertrail_colorization = $wsal->GetGlobalSetting( 'papertrail-colorization-e' );

				// Create the connection object.
				$mirror_connection               = new stdClass();
				$mirror_connection->name         = 'wsalMirrorCC';
				$mirror_connection->type         = 'papertrail';
				$mirror_connection->destination  = $papertrail_destination;
				$mirror_connection->colorization = $papertrail_colorization;
				$mirror_connection->used_for     = __( 'Mirroring', 'wp-security-audit-log' );

				// Save the connection.
				$wsal->SetGlobalSetting( 'connection-wsalMirrorCC', $mirror_connection );

				// Create the mirror object.
				$mirror               = new stdClass();
				$mirror->name         = 'wsalPTMirror';
				$mirror->connection   = 'wsalMirrorCC';
				$mirror->frequency    = 'hourly';
				$mirror->filter       = 'all';
				$mirror->state        = ( $mirroring_e ) ? true : false;
				$mirror->last_created = $mirroring_last_crtd;

				// Save the mirror.
				$wsal->SetGlobalSetting( 'mirror-wsalPTMirror', $mirror );

				$wsal->DeleteSettingByName( 'wsal-papertrail-destination' );
				$wsal->DeleteSettingByName( 'wsal-papertrail-colorization-e' );
			} elseif ( 'syslog' === $mirroring_type ) {
				// Create the connection object.
				$mirror_connection           = new stdClass();
				$mirror_connection->name     = 'wsalMirrorCC';
				$mirror_connection->type     = 'syslog';
				$mirror_connection->used_for = __( 'Mirroring', 'wp-security-audit-log' );

				// Save the connection.
				$wsal->SetGlobalSetting( 'connection-wsalMirrorCC', $mirror_connection );

				// Create the mirror object.
				$mirror               = new stdClass();
				$mirror->name         = 'wsalSysMiror';
				$mirror->connection   = 'wsalMirrorCC';
				$mirror->frequency    = 'hourly';
				$mirror->filter       = 'all';
				$mirror->state        = ( $mirroring_e ) ? true : false;
				$mirror->last_created = $mirroring_last_crtd;

				// Save the mirror.
				$wsal->SetGlobalSetting( 'mirror-wsalSysMiror', $mirror );
			}

			// Delete mirroring options.
			$wsal->DeleteSettingByName( 'wsal-mirroring-e' );
			$wsal->DeleteSettingByName( 'wsal-mirroring-type' );
			$wsal->DeleteSettingByName( 'wsal-mirroring-run-every' );
			$wsal->DeleteSettingByName( 'wsal-mirroring-last-created' );

			// Get archiving options.
			$archive_type            = $wsal->GetGlobalSetting( 'archive-type' );
			$archive_user            = $wsal->GetGlobalSetting( 'archive-user' );
			$archive_password        = $wsal->GetGlobalSetting( 'archive-password' );
			$archive_name            = $wsal->GetGlobalSetting( 'archive-name' );
			$archive_hostname        = $wsal->GetGlobalSetting( 'archive-hostname' );
			$archive_base_prefix     = $wsal->GetGlobalSetting( 'archive-base-prefix' );
			$archive_url_base_prefix = $wsal->GetGlobalSetting( 'archive-url-base-prefix' );
			$archive_ssl             = $wsal->GetGlobalSetting( 'archive-ssl' );
			$archive_cc              = $wsal->GetGlobalSetting( 'archive-client-certificate' );
			$archive_ssl_ca          = $wsal->GetGlobalSetting( 'archive-ssl-ca' );
			$archive_ssl_cert        = $wsal->GetGlobalSetting( 'archive-ssl-cert' );
			$archive_ssl_key         = $wsal->GetGlobalSetting( 'archive-ssl-key' );

			// Create a new DB connection for external DB.
			$archive_connection             = new stdClass();
			$archive_connection->name       = 'wsalArchConn';
			$archive_connection->type       = 'mysql';
			$archive_connection->user       = $archive_user;
			$archive_connection->password   = $archive_password;
			$archive_connection->db_name    = $archive_name;
			$archive_connection->hostname   = $archive_hostname;
			$archive_connection->baseprefix = $archive_base_prefix;
			$archive_connection->url_prefix = ( 'on' === $archive_url_base_prefix ) ? true : false;
			$archive_connection->is_ssl     = $archive_ssl;
			$archive_connection->is_cc      = $archive_cc;
			$archive_connection->ssl_ca     = $archive_ssl_ca;
			$archive_connection->ssl_cert   = $archive_ssl_cert;
			$archive_connection->ssl_key    = $archive_ssl_key;
			$archive_connection->used_for   = __( 'Archiving', 'wp-security-audit-log' );

			// Save the connection.
			$wsal->SetGlobalSetting( 'connection-wsalArchConn', $archive_connection );
			$wsal->SetGlobalSetting( 'archive-connection', 'wsalArchConn' );
		}
	}

endif;
