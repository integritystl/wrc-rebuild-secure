<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }
/**
 * Class  EED_AuthorizeNet_Accept
 *
 * @package         Event Espresso
 * @subpackage      ee4-mailchimp
 */
class EED_AuthorizeNet_Accept extends EED_Module {


	/**
	 * For hooking into EE Core, other modules, etc.
	 *
	 * @access public
	 * @return void
	 */
	public static function set_hooks() {
	}

	/**
	 * For hooking into EE Admin Core and other modules, etc.
	 *
	 * @access public
	 * @return void
	 */
	public static function set_hooks_admin() {
        add_action('wp_ajax_eea_authorizenet_accept_log_error', array('EED_AuthorizeNet_Accept', 'log_accept_error'));
        add_action('wp_ajax_nopriv_eea_authorizenet_accept_stripe_log_error', array('EED_AuthorizeNet_Accept', 'log_accept_error'));
    }



    /**
     * If the JS gets an error, it sends it via ajax to here so we can log it
     */
    public static function log_accept_error() {
	    $message = strip_tags(EE_Registry::instance()->REQ->get('message'));
	    $txn_id = (int)EE_Registry::instance()->REQ->get('txn_id');
	    EEM_Change_Log::instance()->gateway_log(
            array('authnet_error_in_js' => $message),
            $txn_id,
            'Transaction'
        );
    }

    public function run($WP){}
}