<?php
/**
 * Sensor: Sessions Handling
 *
 * @since 4.1.0
 * @package Wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WSAL_Sensors_Sessions
 *
 * @package Wsal
 * @subpackage User Sessions
 */
class WSAL_Sensors_UserSessionsTracking extends WSAL_AbstractSensor {

	/**
	 * Current user token
	 *
	 * @var string
	 */
	public static $user_token = '';

	/**
	 * Current user id
	 *
	 * @var int
	 */
	public static $user_id = 0;

	public static $hooked = false;

	/**
	 * Listening using hooks.
	 *
	 * @since 4.1.0
	 */
	public function init() {
		$this->HookEvents();
	}

	public function HookEvents() {
		if ( ! self::$hooked ) {
			// track session on login.
			add_action( 'set_auth_cookie', array( $this, 'login_session_store' ), 10, 6 );
			// keeps track of the session before WP may clear it from it's own records.
			add_action( 'login_form_logout', array( $this, 'store_current_user_session_token_and_id' ) );
			// clear on logout.
			add_action( 'clear_auth_cookie', array( $this, 'user_session_logout_clear' ) );
			// Check if user session is allowed when they are authenticating.
			add_filter( 'authenticate', array( $this, 'check_session_login_rules' ), 100, 2 );
			// remember that these actions are already hooked.
			self::$hooked = true;
		}

	}

	/**
	 * By this point session has already validated... this can only save
	 * but unlikely able to block a login here.
	 *
	 * @method login_session_store
	 * @since
	 * @param  string $auth_cookie an auth cookie string.
	 * @param  string $expire the expiration + 12 hours grace.
	 * @param  string $expiration the expiration time of the session.
	 * @param  int    $user_id the user id logging in.
	 * @param  string $scheme the scheme connection are using.
	 * @param  string $token a session token.
	 */
	public function login_session_store( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token ) {

		// We need non-empty $expiration, $user_id, $token passed.
		if ( empty( $token ) || ( empty( $user_id ) || ! is_int( $user_id ) ) || empty( $expiration ) ) {
			return;
		}

		// get the user roles and sites (in multisite context)
		$roles = array();
		$sites = array();
		$user = get_user_by( 'id', $user_id );

		// Get multiple sessions option.
		// NOTE: on multisite roles are empty when user is not part of the site
		// they are logging in from, need to get roles from a blog id based
		// WP_User.
		$is_multisite = WpSecurityAuditLog::GetInstance()->IsMultisite();
		if ( $is_multisite && function_exists( 'get_blogs_of_user' ) ) {
			$blogs = get_blogs_of_user( $user_id );
			$is_superadmin = is_super_admin( $user_id );

			foreach ( $blogs as $blog ) {
				$user_obj = new WP_User( $user_id, '', $blog->userblog_id );
				if ( $user_obj->roles && ! empty( $user_obj->roles ) ) {
					foreach ( $user_obj->roles as $role ) {
						if ( ! in_array( $role, $roles, true ) ) {
							$roles[] = $role;
						}
					}
					// when user has an admin role anywhere put that at the top
					// of the list.
					if ( count( $roles ) > 1 && in_array( 'administrator', $roles, true ) ) {
						array_unshift( $roles, 'administrator' );
						$roles = array_unique( $roles );
					}
				}
			}

			//  check if user is a superadmin too
			if ( $is_superadmin ) {
				$roles[] = 'superadmin';
				$sites = ['all'];
			} else {
				$sites = array_keys($blogs);
			}
		} else {
			$roles = ( ! empty( $user->roles ) ) ? $user->roles : array();
		}

		// setup the session info to store in the custom table
		$model                = new WSAL_Models_Session();
		$model->user_id       = (int) $user_id;
		$model->session_token = (string) WSAL_UserSessions_Helpers::hash_token( $token );
		$model->creation_time = (int) time();
		$model->expiry_time   = (int) $expiration;
		$model->ip            = (string) $this->plugin->settings()->GetMainClientIP();
		//  Wrap using array_values for case where a role has been removed from array and the keys need to be reset.
		//  The value would be stored differently in the database.
		$model->roles         = array_values( (array) $roles );
		$model->sites         = (string) implode(',', $sites);

		// remember token for later
		self::$user_token = $token;
		//  also remember user ID for later (it is necessary for compatibility with some alternative authentication
		//  method plugins, for example 2FA plugin)
		self::$user_id    = $user->ID;
		// save the session data to database.
		$model->Save();

		// fire event 1000 here.
	}

	/**
	 * Saves the current user session token when it's available so it can be
	 * used later in the login process.
	 *
	 * @method store_current_user_session_token_and_id
	 * @since 4.1.0
	 */
	public function store_current_user_session_token_and_id() {
		// tries to get the current user and their current token before they get
		// cleared.
		$token = wp_get_session_token();
		$user  = wp_get_current_user();
		if ( ( isset( $user ) && is_a( $user, '\WP_User' ) ) && $token ) {
			// hang onto the token for processing at a later point.
			self::$user_token = $token;
			self::$user_id    = $user->ID;
			// add an action where we will clear the token from tables.
			add_action( 'wp_logout', array( $this, 'user_session_logout_clear' ) );
			add_action( 'clear_auth_cookie', array( $this, 'user_session_logout_clear' ) );
		}
	}

	/**
	 * Deletes a session from custom tables if it was removed from usermeta.
	 *
	 * @method user_session_logout_clear
	 * @since  4.1.0
	 */
	public function user_session_logout_clear() {
		$cleared = false;
		if ( ! empty( self::$user_token ) && ! empty( self::$user_id ) ) {
			$user_sessions_wp = WSAL_UserSessions_Plugin::get_user_session_tokens( self::$user_id );
			if ( ! isset( $user_sessions_wp[ self::$user_token ] ) ) {
				// core session already cleared here.
				$adapter = WSAL_UserSessions_Plugin::get_sessions_adapter();
				$adapter->delete_by_session_token( WSAL_UserSessions_Helpers::hash_token( self::$user_token ) );
				$cleared = true;
			}
		}
		return $cleared;
	}

	/**
	 * Handle sessions login allow/block logic.
	 *
	 * @param WP_User $current_user - User object.
	 * @param string  $username - User name.
	 */
	public function check_session_login_rules( $current_user, $username ) {
		// Check $current_user if this is already an error return it.
		if ( ! $current_user || $current_user instanceof WP_Error ) {
			return $current_user;
		}

		// if policies are not enabled with master switch just return user.
		if ( ! \WSAL\Helpers\Options::get_option_value_ignore_prefix( 'wsal_usersessions_policies_enabled', false ) ) {
			return $current_user;
		}

		// Get multiple sessions option.
		// NOTE: on multisite roles are empty when user is not part of the site
		// they are logging in from, need to get roles from a blog id based
		// WP_User.
		if ( is_multisite() ) {
			$blogs = get_blogs_of_user( $current_user->ID );
			$roles = array();
			foreach ( $blogs as $blog ) {
				$user_obj = new WP_User( $current_user->ID, '', $blog->userblog_id );
				if ( $user_obj->roles && ! empty( $user_obj->roles ) ) {
					foreach ( $user_obj->roles as $role ) {
						if ( ! in_array( $role, $roles, true ) ) {
							$roles[] = $role;
						}
					}
					// when user has an admin role anywhere put that at the top
					// of the list.
					if ( count( $roles ) > 1 && in_array( 'administrator', $roles, true ) ) {
						array_unshift( $roles, 'administrator' );
						$roles = array_unique( $roles );
					}
				}
			}
		} else {
			$roles = ( ! empty( $current_user->roles ) ) ? $current_user->roles : array( '' );
		}
		$policy = WSAL_UserSessions_Helpers::get_role_sessions_policy( reset($roles) );
		if ( isset( $policy['policies_disabled'] ) && $policy['policies_disabled'] ) {
			return $current_user;
		}
		$multiple_sessions = ( isset( $policy['multisessions']['type'] ) ) ? $policy['multisessions']['type'] : 'single';

		// Check if we need to block/clear session for some reason.
		if ( 'newest' === $multiple_sessions ) {
			// Only allowing a single user session - any existing sessions are deleted to allow the latest login to happen.
			$user_sessions = WSAL_UserSessions_Plugin::get_user_session_tokens( $current_user->ID );
			if ( 0 === count( $user_sessions ) ) {
				return $current_user;
			}
			$this->clear_existing_sessions( $current_user->ID ); // Override last user session.
			// trigger override session event: 1006
			// is this an event 1006?
			$tmp          = new WSAL_Models_Meta();
			$ip_addresses = $tmp->getAdapter()->GetMatchingIPs( null );

			$roles = ( isset( $current_user->roles ) ) ? $current_user->roles : array();
			if ( is_super_admin( $current_user->ID ) ) {
				$roles[] = 'superadmin';
			}

			$this->plugin->alerts->Trigger(
				1006,
				array(
					'Username'         => $current_user->user_login,
					'CurrentUserRoles' => $roles,
					'IPAddress'        => $ip_addresses,
				),
				true
			);
			return $current_user; // Return the current user.
		} elseif ( 'single' === $multiple_sessions || 'allow-limited' === $multiple_sessions ) { // If limited sessions are allowed then.
			// Get the number of sessions allowed - only 1 when single.
			$allowed_sessions = ( 'allow-limited' === $multiple_sessions && isset( $policy['multisessions']['limit'] ) ) ? $policy['multisessions']['limit'] : 1;

			// Get current user sessions.
			$session_tokens = WSAL_UserSessions_Plugin::get_user_session_tokens( $current_user->ID );

			// Block if the number of sessions is greater or equal to the set limit.
			if ( ! empty( $session_tokens ) ) {
				if ( count( $session_tokens ) >= $allowed_sessions ) {
					// this should fire a 1004
					do_action( 'wp_login_blocked', $username );

					// Get blocked session error message.
					$msg = $policy['sessions_error_message'];
					if ( empty( $msg ) ) {
						$msg = __( 'ERROR: Your session was blocked with the <a href="https://en-gb.wordpress.org/plugins/wp-security-audit-log" target="_blank">WP Activity Log plugin</a> because there is already another user logged in with the same username. Please contact the site administrator for more information.', 'wp-security-audit-log' );
					}
					return new WP_Error( 'login_denied', $msg );
				} elseif ( count( $session_tokens ) > 0 ) {

					// trigger override session event: 1005
					$tmp          = new WSAL_Models_Meta();
					$ip_addresses = $tmp->getAdapter()->GetMatchingIPs( null );

					$roles = ( isset( $current_user->roles ) ) ? $current_user->roles : array();
					if ( is_super_admin( $current_user->ID ) ) {
						$roles[] = 'superadmin';
					}

					$this->plugin->alerts->Trigger(
						1005,
						array(
							'Username'         => $current_user->user_login,
							'CurrentUserRoles' => $roles,
							'IPAddress'        => $ip_addresses,
						),
						true
					);
				}
			}
		}
		return $current_user;
	}

	/**
	 * Clears any and all existing sessions for a given user id.
	 *
	 * @param int $user_id - User id.
	 * @since 4.1.0
	 */
	private function clear_existing_sessions( $user_id ) {
		// Get current user sessions.
		if ( ! $user_id ) {
			return;
		}
		delete_user_meta( $user_id, 'session_tokens' );

		// delete old sessions of this user
		$adapter = WSAL_UserSessions_Plugin::get_sessions_adapter();
		$adapter->delete_by_user_ids( array( $user_id ) );
	}
}
