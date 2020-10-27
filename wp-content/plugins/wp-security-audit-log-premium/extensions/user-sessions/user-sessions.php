<?php
/**
 * Extension: Users Sessions Management
 *
 * User sessions management extension for wsal.
 *
 * @since 4.1.0
 * @package Wsal
 */

/**
 * Class UserSessions_Plugin
 *
 * @package Wsal
 */
class WSAL_UserSessions_Plugin {

	/**
	 * Instance of the main WpSecurityAuditLog plugin class.
	 *
	 * @since 4.1.0
	 * @var   object
	 */
	protected $wsal = null;

	/**
	 * Method: Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		$this->wsal = WpSecurityAuditLog::GetInstance();
		// Autoload files from /classes.
		$this->wsal->autoloader->Register( 'WSAL_UserSessions_', dirname( __FILE__ ) . '/classes', true );
		// These actions ensure that the adapter is available during the install methods at activation.
		add_action( 'wsal_require_additional_adapters', array( $this, 'require_adapter_classes' ) );
		add_filter( 'wsal_install_apapters_list', array( $this, 'add_adapter_to_install_list' ) );

		add_action( 'wsal_init', array( $this, 'wsal_init' ) ); // Function to hook at `wsal_init`.
		add_action( 'wsal_custom_sensors_classes_dirs', array( $this, 'add_usersessions_sensor' ) );

	}

	public function add_usersessions_sensor( $paths ) {
		$base_dir = trailingslashit( dirname( __FILE__ ) );
		$paths[] = $base_dir . 'classes/Sensors/';
		return $paths;
	}

	/**
	 * Require the user sessions adapters classes. These need hooked in early
	 * and made available during constructor of the main MySQL class.
	 *
	 * @method require_adapter_classes
	 * @since  4.1.0
	 */
	public function require_adapter_classes() {
		$base_dir = trailingslashit( dirname( __FILE__ ) );
		require_once $base_dir . 'classes/Adapters/SessionInterface.php';
		require_once $base_dir . 'classes/Adapters/SessionAdapter.php';
		require_once $base_dir . 'classes/Models/Session.php';
	}


	/**
	 * Hook in and add this extensions database adapter during the initial
	 * plugin activation/install routine.
	 *
	 * @method add_adapter_to_install_list
	 * @since  4.1.0
	 * @param  array $file_list list of adapter class files to load.
	 */
	public function add_adapter_to_install_list( $file_list ) {
		if ( is_array( $file_list ) ) {
			$file_list[] = trailingslashit( dirname( __FILE__ ) ) . 'classes/Adapters/SessionAdapter.php';
		}
		return $file_list;
	}

	/**
	 * Triggered when the main plugin is loaded.
	 *
	 * @method wsal_init
	 * @see    WpSecurityAuditLog::load()
	 * @since  4.1.0
	 * @param  WpSecurityAuditLog $wsal - Instance of WpSecurityAuditLog.
	 */
	public function wsal_init( WpSecurityAuditLog $wsal ) {

		$wsal->usersessions = $this;

		if ( isset( $wsal->views ) ) {
			$wsal->views->AddFromClass( 'WSAL_UserSessions_Views' );
		}
		// hook in any cleanup events.
		$this->add_cleanup_hooks();
	}

	/**
	 * Hooks in the cleanup actions for the sessions handlinging.
	 *
	 * @method maybe_add_cleanup_hooks
	 * @since  4.1.0
	 */
	private function add_cleanup_hooks() {
		// cleans up expired sessions - also optionally cleans them up from core
		// tables as well depending on user option.
		add_action( 'wsal_cleanup', array( $this, 'expired_sessions_cleanup' ) );

		// if idle session cleanup is enabled then hook this in as well.
		if ( WSAL_UserSessions_Helpers::is_idle_session_cleanup_enabled() ) {
			add_action( 'wsal_cleanup', array( $this, 'idle_sessions_cleanup' ) );
		}

		$sessions_test = \WSAL\Helpers\Options::get_option_value_ignore_prefix( 'wsal_inactive_sessions_test', false );
		$next          = wp_next_scheduled( 'run_testing_sessions_cleanup' );
		if ( isset( $sessions_test ) && ! empty( $sessions_test ) ) {
			add_action( 'run_testing_sessions_cleanup', array( $this, 'schedule_testing_sessions_cleanup' ) );
			if ( ! $next ) {
				wp_schedule_single_event( time() + $sessions_test, 'run_testing_sessions_cleanup' );
			}
		}
	}

	public function schedule_testing_sessions_cleanup() {
		$this->idle_sessions_cleanup();
	}

	/**
	 * Hooked as an action on the cleanup cron for cleaning up old WordPress
	 * core session data when session has passed expiry time.
	 *
	 * @method core_sessions_cleanup
	 * @since  4.1.0
	 */
	public function expired_sessions_cleanup() {
		// get the expired sessions
		$adapter    = WSAL_UserSessions_Plugin::get_sessions_adapter();
		$expired_sessions    = $adapter->get_all_expired_sessions();
		$user_tokens_removed = array();
		foreach ( $expired_sessions as $expired_session ) {
			$user_tokens_removed[ $expired_session->session_token ] = $expired_session->user_id;
		}
		$adapter->delete_by_session_tokens( array_keys( $user_tokens_removed ) );

		foreach ( $user_tokens_removed as $token_hash => $user_id ) {
			$user_sessions = WSAL_UserSessions_Plugin::get_user_session_tokens( $user_id );
			if ( array_key_exists( $token_hash, $user_sessions ) ) {
				unset( $user_sessions[ $token_hash ] );
				update_user_meta( $user_id, 'session_tokens', $user_sessions );
			}
		}
		// Users can optionally flush expired sessions from core directly as
		// well. If that is not enabled then we are done, return early.
		if ( ! WSAL_UserSessions_Helpers::is_core_session_cleanup_enabled() ) {
			return;
		}

		// get the full list of users that have sessions, we only need the
		// ID field.
		$users_query = new WP_User_Query(
			array(
				'blog_id'      => 0, // whole network if we are multisite.
				'meta_key'     => 'session_tokens',
				'meta_compare' => 'EXISTS',
				'fields'       => 'ID',
			)
		);

		/*
		 * If we have users to work with then loop through them fetching
		 * all the sessions and checking if they have expired.
		 */
		if ( isset( $users_query->results ) && ! empty( $users_query->results ) ) {
			$users = $users_query->results;
			foreach ( $users as $user_id ) {
				$sessions = WSAL_UserSessions_Plugin::get_user_session_tokens( $user_id );
				// if user has sessions loop through them checking expiry.
				if ( ! empty( $sessions ) ) {
					$to_remove = array();
					foreach ( $sessions as $token => $session_data ) {
						if ( $session_data['expiration'] < time() ) {
							// this session has expired, store it for removal.
							$to_remove[] = $token;
						}
					}
					// if the we have items to remove then remove from user_meta
					// and custom table.
					if ( ! empty( $to_remove ) ) {
						foreach ( $to_remove as $remove ) {
							unset( $sessions[ $remove ] );
						}
						// incase any sessions were left in custom table clear
						// for these sessions clear them now.
						$adapter->delete_by_session_tokens( $to_remove );
						// clear from user meta.
						update_user_meta( $user_id, 'session_tokens', $sessions );
					}
				}
			}
		}

	}

	/**
	 * Cleans up user sessions which have not triggered any alerts in a user
	 * configured idle time.
	 *
	 * Hooked as an action on the plugins cleanup cron task.
	 *
	 * @method idle_sessions_cleanup
	 * @since  4.1.0
	 */
	public function idle_sessions_cleanup() {
		$adapter = WSAL_UserSessions_Plugin::get_sessions_adapter();
		$sessions   = $adapter->load_all_sessions_ordered_by_user_id();
		// bail early if we have no sessions.
		if ( empty( $sessions ) ) {
			return;
		}
		foreach ( $sessions as $session ) {

			// first check if this user role is excluded.
			if ( ! isset( $session->roles ) || ! is_array( $session->roles ) ) {
				continue;
			}

			// For the moment we are only looking at a single user role. We
			// might in future need to do multiple role tests and pick a
			// preference for the main one to use.
			$policy = WSAL_UserSessions_Helpers::get_role_sessions_policy( reset($session->roles) );

			// Check of role has policies disabled.
			if ( isset( $policy['policies_disabled'] ) && ! empty( $policy['policies_disabled'] ) && $policy['policies_disabled'] ) {
				break;
			}

			$user = get_user_by( 'id', $session->user_id );
			if ( ! is_a( $user, '\WP_User' ) ) {
				// we don't have a user object - skip to next iteration.
				continue;
			}
			$last_alert = $this->get_last_user_alert( $user->data->user_login, $session->session_token );
			if ( is_a( $last_alert, '\WSAL_Models_Occurrence' ) ) {
				// Are we testing sessions currently? Lets check for the option.
				$sessions_test = \WSAL\Helpers\Options::get_option_value_ignore_prefix( 'wsal_inactive_sessions_test', false );
				if ( isset( $sessions_test ) && ! empty( $sessions_test ) ) {
					// idle time is a test, so we just need seconds here.
					$idle_time = (int) $sessions_test;
				} else {
					// idle time is saved in DB in hours so multiply by mins and seconds.
					$idle_time = (int) $policy['auto_terminate']['max_hours'] * 60 * 60;
				}

				if ( $last_alert->created_on < ( time() - $idle_time ) ) {
					// user is idle, clear their session from user meta.
					$user_meta_sessions = WSAL_UserSessions_Plugin::get_user_session_tokens( $session->user_id );
					if ( ! empty( $user_meta_sessions ) && array_key_exists( $session->session_token, $user_meta_sessions ) ) {
						unset( $user_meta_sessions[ $session->session_token ] );
						update_user_meta( $session->user_id, 'session_tokens', $user_meta_sessions );
					}
					// clear the session from the custom table.
					$adapter->delete_by_session_token( $session->session_token );
				}
			}
		}

	}

	/**
	 * Get last user event.
	 *
	 * @method get_last_user_alert
	 * @param  string  $value   - User login name.
	 * @param  string  $session - User session.
	 * @param  integer $blog_id - Blog ID.
	 * @return stdClass|bool
	 */
	public function get_last_user_alert( $value, $session, $blog_id = 0 ) {
		// get user by login.
		$user    = get_user_by( 'login', $value );
		$user_id = ( is_a( $user, '\WP_User' ) ) ? $user->ID : -1;

		// try get latest alert via session id.
		$query  = $this->get_user_alerts_by_session_query( $session, $blog_id );
		$result = $this->execute_query( $query );

		// if we got an alert use it, otherwise use false = fail.
		if ( ! empty( $result ) ) {
			$last_alert = $result[0];
		} else {
			$last_alert = false;
		}
		return ( ! empty( $result ) && is_array( $result ) ) ? $result[0] : false;
	}

	/**
	 * Adds params to the query args to order all items by 'created_on' date
	 * and then return only the first item.
	 *
	 * @method execure_query
	 * @since  4.1.0
	 * @param  WSAL_Models_OccurrenceQuery $query an query object filled with args.
	 * @return WSAL_Models_Occurrence object
	 */
	private function execute_query( $query ) {
		$query->addOrderBy( 'created_on', true );
		$query->setLimit( 1 );
		return $query->getAdapter()->Execute( $query );
	}

	/**
	 * Get user alerts query by session id.
	 *
	 * NOTE: when this is used it gets more params attached for ordering and
	 * to only get 1 item.
	 *
	 * @method get_user_alerts_by_session_query
	 * @since  4.0.3
	 * @param  string  $session a string with session id.
	 * @param  integer $blog_id a site id if we have one to check against.
	 * @return WSAL_Models_OccurrenceQuery
	 */
	public function get_user_alerts_by_session_query( $session = '', $blog_id = 0 ) {

		// Get DB connection array.
		$connection = $this->wsal->getConnector()->getAdapter( 'Occurrence' )->get_connection();
		$connection->set_charset( $connection->dbh, 'utf8mb4', 'utf8mb4_general_ci' );

		// Tables.
		$meta       = new WSAL_Adapters_MySQL_Meta( $connection );
		$table_meta = $meta->GetTable(); // Metadata.
		$occurrence = new WSAL_Adapters_MySQL_Occurrence( $connection );
		$table_occ  = $occurrence->GetTable(); // Occurrences.

		// setup the sql for the query.
		$query = new WSAL_Models_OccurrenceQuery();
		$sql   = "$table_occ.id IN ( SELECT occurrence_id FROM $table_meta as meta WHERE ";
		$sql  .= "( meta.name='SessionID' AND find_in_set(meta.value, %s) > 0 ) ) ";
		$query->addORCondition( array( $sql => $session ) );

		// if we have a blog id then add it.
		if ( $blog_id ) {
			$query->addCondition( 'site_id = %s ', $blog_id );
		}

		// return the query object.
		return $query;
	}

	/**
	 * Get a query to fetch user alerts by user_id.
	 *
	 * NOTE: when this is used it gets more params attached for ordering and
	 * to only get 1 item.
	 *
	 * @method get_user_alerts_by_user_query
	 * @since  4.0.3
	 * @param  integer $user_id   a user id.
	 * @param  string  $user_name username of the user.
	 * @param  integer $blog_id   the blog id if we have one to check against.
	 * @return WSAL_Models_OccurrenceQuery
	 */
	public function get_user_alerts_by_user_query( $user_id = -1, $user_name = '', $blog_id = 0 ) {

		// Get DB connection array.
		$connection = $this->wsal->getConnector()->getAdapter( 'Occurrence' )->get_connection();
		$connection->set_charset( $connection->dbh, 'utf8mb4', 'utf8mb4_general_ci' );

		// Tables.
		$meta       = new WSAL_Adapters_MySQL_Meta( $connection );
		$table_meta = $meta->GetTable(); // Metadata.
		$occurrence = new WSAL_Adapters_MySQL_Occurrence( $connection );
		$table_occ  = $occurrence->GetTable(); // Occurrences.

		// set the sql for the query.
		$query = new WSAL_Models_OccurrenceQuery();
		$sql   = "$table_occ.id IN ( SELECT occurrence_id FROM $table_meta as meta WHERE ";
		$sql  .= "( meta.name='CurrentUserID' AND find_in_set(meta.value, '$user_id') > 0 ) ";
		$sql  .= 'OR ';
		$sql  .= "( meta.name='Username' AND find_in_set(meta.value, %s) > 0 ) )";
		$query->addORCondition( array( $sql => $user_name ) );

		// if we have a blog id then add it.
		if ( $blog_id ) {
			$query->addCondition( 'site_id = %s ', $blog_id );
		}

		// return the query object.
		return $query;
	}

	/**
	 * Clears any and all existing sessions for a given user id.
	 *
	 * @param int $user_id - User id.
	 * @since 4.1.0
	 */
	public function clear_existing_sessions( $user_id ) {
		// Get current user sessions.
		if ( ! $user_id ) {
			return;
		}
		delete_user_meta( $user_id, 'session_tokens' );
		// setup the sessions database adapter.
		$adapter = WSAL_UserSessions_Plugin::get_sessions_adapter();
		// delete old sessions of this user.
		$adapter->delete_by_user_ids( array( $user_id ) );
	}

	/**
	 * Retrieves an instance of database adapter for working with sessions data.
	 *
	 * @return WSAL_Adapters_MySQL_Session
	 * @since 4.1.3
	 */
	public static function get_sessions_adapter() {
		return WSAL_Connector_ConnectorFactory::GetDefaultConnector()->getAdapter( 'Session' );
	}

	/**
	 * Helper function to safely load user sessions token from user meta.
	 *
	 * Handles missing data as well as an empty string or serialized data.
	 *
	 * @param int $user_id
	 *
	 * @return string[]
	 * @since 4.1.3
	 */
	public static function get_user_session_tokens( $user_id ) {
		$session_tokens = get_user_meta( $user_id, 'session_tokens', true );
		if ( $session_tokens == false || '' == $session_tokens ) {
			$session_tokens = array();
		}

		if ( ! is_array( $session_tokens ) && is_string( $session_tokens ) ) {
			$session_tokens = maybe_unserialize( $session_tokens );
		}

		return $session_tokens;
	}

}
