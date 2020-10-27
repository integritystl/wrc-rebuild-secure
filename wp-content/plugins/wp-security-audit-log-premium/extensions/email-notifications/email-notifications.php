<?php
/**
 * Extension: Email Notifications
 *
 * Email notifications extension for wsal.
 *
 * @since 2.7.0
 * @package Wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Holds the option prefix
 */
define( 'WSAL_OPT_PREFIX', 'notification-' );

/**
 * Holds the maximum number of notifications a user is allowed to add
 */
define( 'WSAL_MAX_NOTIFICATIONS', 50 );

/**
 * Holds the name of the cache key if cache available
 */
define( 'WSAL_CACHE_KEY', '__NOTIF_CACHE__' );

/**
 * Debugging true|false
 */
define( 'WSAL_DEBUG_NOTIFICATIONS', false );

/**
 * Class WSAL_NP_Plugin
 *
 * @package wp-security-audit-log
 */
class WSAL_NP_Plugin {

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var object
	 */
	protected $wsal = null;

	/**
	 * Notifications Cache.
	 *
	 * @var array
	 */
	private $notifications = null;

	/**
	 * Cache Expiration Limit.
	 *
	 * Currently set to 12 hrs. 43200 = (12 * 60 * 60).
	 *
	 * @var int
	 */
	private $cache_expire = 43200;

	/**
	 * Method: Constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'wsal_init', array( $this, 'wsal_init' ) );
		add_action( 'wp_login_failed', array( $this, 'counter_login_failure' ) );
		add_filter( 'template_redirect', array( $this, 'counter_event_404' ) );
	}

	/**
	 * Triggered when the main plugin is loaded.
	 *
	 * @param WpSecurityAuditLog $wsal - Instance of WpSecurityAuditLog.
	 * @see WpSecurityAuditLog::load()
	 */
	public function wsal_init( WpSecurityAuditLog $wsal ) {
		// Autoload the files in `classes` folder.
		$wsal->autoloader->Register( 'WSAL_NP_', dirname( __FILE__ ) . '/classes' );
		$wsal_common      = new WSAL_NP_Common( $wsal );
		$wsal->wsalCommon = $wsal_common;

		if ( isset( $wsal->views ) ) {
			// Add notifications view.
			$wsal->views->AddFromClass( 'WSAL_NP_Notifications' );

			// @codingStandardsIgnoreStart
			$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
			// @codingStandardsIgnoreEnd

			if ( $current_page ) {
				$add_notif  = new WSAL_NP_AddNotification( $wsal );
				$edit_notif = new WSAL_NP_EditNotification( $wsal );
				$settings   = $wsal->views->FindByClassName( 'WSAL_Views_Settings' );

				// Get views names.
				$add_notif_page  = $add_notif->GetSafeViewName();
				$edit_notif_page = $edit_notif->GetSafeViewName();
				$settings_page   = $settings->GetSafeViewName();

				switch ( $current_page ) {
					case $add_notif_page:
						$wsal->views->AddFromClass( 'WSAL_NP_AddNotification' );
						break;
					case $edit_notif_page:
						$wsal->views->AddFromClass( 'WSAL_NP_EditNotification' );
						break;
					case $settings_page:
						new WSAL_NP_SMSProviderSettings();
						break;
				}
			}
		}

		if ( isset( $wsal->alerts ) ) {
			$wsal->alerts->AddFromClass( 'WSAL_NP_Notifier' );
		}

		// Set main plugin class object.
		$this->wsal = $wsal;

		// Remove built-in content notifications.
		$this->remove_built_in_content_notif();
	}

	/**
	 * Method: Remove built-in published content & modified
	 * content notifications.
	 *
	 * @since 3.2
	 */
	public function remove_built_in_content_notif() {
		// Check if the built in notifications exists.
		if ( $this->wsal->GetGlobalSetting( 'notification-built-in-6', false ) ) {
			// Remove the built-in notification: Published content is modified.
			$this->wsal->wsalCommon->DeleteGlobalSetting( 'wsal-notification-built-in-6' );
		}
		if ( $this->wsal->GetGlobalSetting( 'notification-built-in-7', false ) ) {
			// Remove the built-in notification: Content is modified.
			$this->wsal->wsalCommon->DeleteGlobalSetting( 'wsal-notification-built-in-7' );
		}
	}

	/**
	 * Triggered by Failed Login Hook.
	 *
	 * Increase the limit changes the max value when you call: $Notifications->CreateSelect().
	 *
	 * @param string $username - Username.
	 */
	public function counter_login_failure( $username ) {
		if ( empty( $this->wsal ) ) {
			$this->wsal_init( WpSecurityAuditLog::GetInstance() );
			remove_action( 'wsal_init', array( $this, 'wsal_init' ) );
		}

		// Get the frontend sensors setting.
		$frontend_sensors = WSAL_Settings::get_frontend_events();

		// Check if allowed to load on front-end for 404s.
		if ( ! is_user_logged_in() && empty( $frontend_sensors['login'] ) ) {
			return;
		}

		$alert_code = 1003;
		$username   = array_key_exists( 'log', $_POST ) ? wp_unslash( $_POST['log'] ) : $username;
		$user       = get_user_by( 'login', $username );
		$alert_code = $user ? 1002 : $alert_code;

		if ( empty( $this->wsal->alerts ) ) {
			$this->wsal->alerts = new WSAL_AlertManager( $this->wsal );
		}

		if ( empty( $this->wsal->settings ) ) {
			$this->wsal->settings = new WSAL_Settings( $this->wsal );
		}

		if ( ! $this->wsal->alerts->IsEnabled( $alert_code ) ) {
			return;
		}

		$site_id             = function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 0;
		$ip                  = $this->wsal->settings()->GetMainClientIP();
		$this->notifications = wp_cache_get( WSAL_CACHE_KEY );

		if ( false === $this->notifications ) {
			$this->notifications = $this->wsal->wsalCommon->GetNotifications();
			wp_cache_set( WSAL_CACHE_KEY, $this->notifications, null, $this->cache_expire );
		}

		if ( ! empty( $this->notifications ) ) {
			foreach ( $this->notifications as $k => $v ) {
				$not_info = maybe_unserialize( $v->option_value );
				$enabled  = intval( $not_info->status );

				if ( 0 == $enabled ) {
					continue;
				}

				if ( ! empty( $not_info->failUser ) && $user ) {
					if ( $this->wsal->wsalCommon->IsLoginFailureLimit( $not_info->failUser, $ip, $site_id, $user, true ) ) {
						break;
					}
					$this->wsal->wsalCommon->CounterLoginFailure( $ip, $site_id, $user );

					if ( $this->wsal->wsalCommon->IsLoginFailureLimit( $not_info->failUser, $ip, $site_id, $user ) ) {
						$this->send_suspicious_activity( $not_info, $ip, $site_id, $alert_code, $username );
					}
				}

				if ( ! empty( $not_info->failNotUser ) && ! $user ) {
					if ( $this->wsal->wsalCommon->IsLoginFailureLimit( $not_info->failNotUser, $ip, $site_id, null, true ) ) {
						break;
					}
					$this->wsal->wsalCommon->CounterLoginFailure( $ip, $site_id, $user );

					if ( $this->wsal->wsalCommon->IsLoginFailureLimit( $not_info->failNotUser, $ip, $site_id, null ) ) {
						$this->send_suspicious_activity( $not_info, $ip, $site_id, $alert_code, $username );
					}
				}
			}
		}
	}

	/**
	 * Triggered by 404 Redirect Hook.
	 *
	 * To increase the limit changes the max value when you call: $Notifications->CreateSelect()
	 */
	public function counter_event_404() {
		global $wp_query;
		if ( ! $wp_query->is_404 ) {
			return;
		}

		if ( empty( $this->wsal ) ) {
			$this->wsal_init( WpSecurityAuditLog::GetInstance() );
			remove_action( 'wsal_init', array( $this, 'wsal_init' ) );
		}

		// Get the frontend sensors setting.
		$frontend_sensors = WSAL_Settings::get_frontend_events();

		// Check if allowed to load on front-end for 404s.
		if ( ! is_user_logged_in() && empty( $frontend_sensors['system'] ) ) {
			return;
		}

		$alert_code = 6007; // 404 alert code for logged in user.

		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			$username   = 'Unregistered user';
			$alert_code = 6023;
		} else {
			$username = wp_get_current_user()->user_login;
		}

		// Check if alert is enabled.
		if ( ! $this->wsal->alerts->IsEnabled( $alert_code ) ) {
			return;
		}

		// Get site ID and IP of the visitor.
		$site_id = function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 0;
		$ip      = $this->wsal->settings()->GetMainClientIP();

		$this->notifications = wp_cache_get( WSAL_CACHE_KEY );

		if ( false === $this->notifications ) {
			$this->notifications = $this->wsal->wsalCommon->GetNotifications();
			wp_cache_set( WSAL_CACHE_KEY, $this->notifications, null, $this->cache_expire );
		}

		if ( ! empty( $this->notifications ) ) {
			foreach ( $this->notifications as $notification ) {
				$not_info = maybe_unserialize( $notification->option_value );
				$enabled  = intval( $not_info->status );

				if ( 0 === $enabled ) {
					continue;
				}

				if ( ! empty( $not_info->error404 ) && 6007 === $alert_code ) {
					if ( $this->wsal->wsalCommon->Is404Limit( $not_info->error404, $site_id, $username, $ip, true, $alert_code ) ) {
						break;
					}

					$this->wsal->wsalCommon->Counter404( $site_id, $username, $ip, $alert_code );

					if ( $this->wsal->wsalCommon->Is404Limit( $not_info->error404, $site_id, $username, $ip, false, $alert_code ) ) {
						$this->send_suspicious_activity( $not_info, $ip, $site_id, $alert_code, $username );
					}
				} elseif ( ! empty( $not_info->error404_visitor ) && 6023 === $alert_code ) {
					if ( $this->wsal->wsalCommon->Is404Limit( $not_info->error404_visitor, $site_id, $username, $ip, true, $alert_code ) ) {
						break;
					}

					$this->wsal->wsalCommon->Counter404( $site_id, $username, $ip, $alert_code );

					if ( $this->wsal->wsalCommon->Is404Limit( $not_info->error404_visitor, $site_id, $username, $ip, false, $alert_code ) ) {
						$this->send_suspicious_activity( $not_info, $ip, $site_id, $alert_code, $username );
					}
				}
			}
		}
	}

	/**
	 * Send Suspicious Activity email.
	 *
	 * Load the template and replace the tags with tha arguments passed.
	 *
	 * @param object $not_info   - Info object.
	 * @param string $ip         - IP Address.
	 * @param int    $site_id    - Site ID.
	 * @param int    $alert_code - Alert code.
	 * @param string $username   - Username.
	 */
	private function send_suspicious_activity( $not_info, $ip, $site_id, $alert_code, $username ) {
		$title         = $not_info->title;
		$email_address = $not_info->email;

		$alert     = $this->wsal->alerts->GetAlert( $alert_code );
		$user      = get_user_by( 'login', $username );
		$user_role = '';

		if ( ! empty( $user ) ) {
			$user_info = get_userdata( $user->ID );
			$user_role = implode( ', ', $user_info->roles );
		}

		$date     = $this->wsal->wsalCommon->GetEmailDatetime();
		$blogname = $this->wsal->wsalCommon->get_blog_domain();
		$search   = array( '%Attempts%', '%Msg%', '%LinkFile%', '%LogFileLink%', '%LogFileText%', '%URL%' );

		if ( ! empty( $not_info->failUser ) ) {
			$replace = array( $not_info->failUser, '', '', '', '', '' );
		} elseif ( ! empty( $not_info->failNotUser ) ) {
			$replace = array( $not_info->failNotUser, '', '', '', '', '' );
		} elseif ( ! empty( $not_info->error404 ) ) {
			$replace = array( $not_info->error404, 'times', '', '', '', 'Check the activity log viewer to see the URLs requested.' );
		} elseif ( ! empty( $not_info->error404_visitor ) ) {
			$replace = array( $not_info->error404_visitor, 'times', '', '', '', 'Check the activity log viewer to see the URLs requested.' );
		}

		$message = str_replace( $search, $replace, $alert->mesg );
		$search  = array_keys( $this->wsal->wsalCommon->get_email_template_tags() );
		$replace = array( $title, $blogname, $username, $user_role, $date, $alert_code, $this->wsal->wsalCommon->get_alert_severity( $alert_code ), $message, $ip );

		$template = $this->wsal->wsalCommon->GetEmailTemplate( 'builder' );
		$subject  = str_replace( $search, $replace, $template['subject'] );
		$content  = str_replace( $search, $replace, stripslashes( $template['body'] ) );

		// Email notification.
		$this->wsal->wsalCommon->SendNotificationEmail( $email_address, $subject, $content, $alert_code );

		if ( ! empty( $not_info->phone ) ) {
			$search_sms_tags  = array_keys( $this->wsal->wsalCommon->get_sms_template_tags() );
			$replace_sms_tags = array( $blogname, $username, $user_role, $date, $alert_code, $this->wsal->wsalCommon->get_alert_severity( $alert_code ), $message, $ip );

			$sms_template = $this->wsal->wsalCommon->get_sms_template( 'builder' );
			$sms_content  = str_replace( $search_sms_tags, $replace_sms_tags, $sms_template['body'] );

			// SMS notification.
			$this->wsal->wsalCommon->send_notification_sms( $not_info->phone, $sms_content );
		}
	}

	/**
	 * Uninstall routine.
	 *
	 * @since 2.7.0
	 */
	public function email_notifications_uninstall_cleanup() {
		$this->wsal->DeleteByPrefix( WSAL_OPT_PREFIX );
	}
}
