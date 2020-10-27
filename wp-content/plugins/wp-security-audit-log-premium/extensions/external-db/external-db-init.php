<?php
/**
 * Extension: External DB
 *
 * External DB extension for WSAL.
 *
 * @since 1.0.0
 * @package Wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Connections Prefix.
 */
define( 'WSAL_CONN_PREFIX', 'connection-' );
define( 'WSAL_MIRROR_PREFIX', 'mirror-' );

/**
 * Class WSAL_Ext_Plugin
 *
 * @package Wsal
 */
class WSAL_Ext_Plugin {

	const SCHEDULED_HOOK_ARCHIVING = 'wsal_run_archiving';
	const SCHEDULED_HOOK_MIRRORING = 'wsal_run_mirroring';

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var object
	 */
	protected $wsal = null;

	/**
	 * Method: Constructor
	 */
	public function __construct() {
		// Function to hook at `wsal_init`.
		add_action( 'wsal_init', array( $this, 'wsal_init' ) );

		// if ( class_exists( 'WpSecurityAuditLog' ) ) {
			// register_deactivation_hook( __FILE__, array( $this, 'remove_config' ) );
			// Register freemius uninstall event.
			// wsal_freemius()->add_action( 'after_uninstall', array( $this, 'remove_config' ) );
		// }
	}

	/**
	 * Triggered when the main plugin is loaded.
	 *
	 * @param object $wsal - Instance of WpSecurityAuditLog.
	 * @see WpSecurityAuditLog::load()
	 */
	public function wsal_init( WpSecurityAuditLog $wsal ) {
		$wsal->autoloader->Register( 'WSAL_Ext_', dirname( __FILE__ ) . '/classes' );
		$wsal_common_class     = new WSAL_Ext_Common( $wsal );
		$wsal->wsalCommonClass = $wsal_common_class;

		if ( isset( $wsal->views ) ) {
			$wsal->views->AddFromClass( 'WSAL_Ext_Settings' );
		}
		$this->wsal = $wsal;

		// Cron job archiving.
		if ( $this->wsal->wsalCommonClass->IsArchivingEnabled() ) {
			if ( ! $this->wsal->wsalCommonClass->IsArchivingStop() ) {
				add_action( self::SCHEDULED_HOOK_ARCHIVING, array( $this, 'archiving_alerts' ) );
				$every = strtolower( $this->wsal->wsalCommonClass->GetArchivingRunEvery() );
				if ( ! wp_next_scheduled( self::SCHEDULED_HOOK_ARCHIVING ) ) {
					wp_schedule_event( time(), $every, self::SCHEDULED_HOOK_ARCHIVING );
				}
			}
		}

		// Get mirrors.
		$mirrors = $this->wsal->GetNotificationsSetting( WSAL_MIRROR_PREFIX );
		add_action( self::SCHEDULED_HOOK_MIRRORING, array( $this, 'mirroring_alerts' ) );

		if ( ! empty( $mirrors ) && is_array( $mirrors ) ) {
			foreach ( $mirrors as $mirror ) {
				// Get mirror details.
				$mirror_details = maybe_unserialize( $mirror->option_value );

				// Check if mirror details are valid.
				if ( ! empty( $mirror_details ) && $mirror_details instanceof stdClass ) {
					$mirror_args = array( $mirror_details->name );

					if ( isset( $mirror_details->state ) && true === $mirror_details->state ) {
						if ( ! wp_next_scheduled( self::SCHEDULED_HOOK_MIRRORING, $mirror_args ) ) {
							wp_schedule_event( time(), $mirror_details->frequency, self::SCHEDULED_HOOK_MIRRORING, $mirror_args );
						}
					} else {
						wp_clear_scheduled_hook( self::SCHEDULED_HOOK_MIRRORING, $mirror_args );
					}
				}
			}
		} elseif ( wp_next_scheduled( self::SCHEDULED_HOOK_MIRRORING ) ) {
			wp_clear_scheduled_hook( self::SCHEDULED_HOOK_MIRRORING );
		}
	}

	/**
	 * Remove External DB config and recreate DB tables on WP.
	 */
	public function remove_config() {
		$wsalCommonClass = $this->wsal->wsalCommonClass;
		$wsalCommonClass->RemoveConfig();
		$wsalCommonClass->RecreateTables();
	}

	/**
	 * Archiving alerts
	 */
	public function archiving_alerts() {
		$this->wsal->wsalCommonClass->archiving_alerts();
	}

	/**
	 * Mirroring alerts
	 *
	 * @param stdClass $mirror_name – Mirror name.
	 */
	public function mirroring_alerts( $mirror_name ) {
		$this->wsal->wsalCommonClass->mirroring_alerts( $mirror_name );
	}
}
