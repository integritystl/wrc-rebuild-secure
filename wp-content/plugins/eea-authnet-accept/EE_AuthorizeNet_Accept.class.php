<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('NO direct script access allowed');
}
/**
 * ------------------------------------------------------------------------
 *
 * Class  EE_AuthorizeNet_Accept
 *
 * @package			Event Espresso
 * @subpackage		eea-authorizenet-accept
 * @author			Event Espresso
 * @ version		 	1.0.0.p
 *
 * ------------------------------------------------------------------------
 */
// Define the plugin directory path and URL.
define('EEA_AUTHORIZENET_ACCEPT_BASENAME', plugin_basename(EEA_AUTHORIZENET_ACCEPT_PLUGIN_FILE));
define('EEA_AUTHORIZENET_ACCEPT_PATH', plugin_dir_path(__FILE__));
define('EEA_AUTHORIZENET_ACCEPT_URL', plugin_dir_url(__FILE__));



class EE_AuthorizeNet_Accept extends EE_Addon
{


    /**
     * class constructor
     */
    public function __construct()
    {

    }


    public static function register_addon()
    {
        // Register addon via Plugin API.
        EE_Register_Addon::register(
            'AuthorizeNet_Accept',
            array(
            'version'              => EEA_AUTHORIZENET_ACCEPT_VERSION,
            'min_core_version'     => '4.9.59',
            'main_file_path'       => EEA_AUTHORIZENET_ACCEPT_PLUGIN_FILE,
            'admin_callback'       => 'additional_authorizenet_accept_admin_hooks',
            // if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
            'pue_options'          => array(
                'pue_plugin_slug' => 'eea-authorizenet-accept',
                'plugin_basename' => EEA_AUTHORIZENET_ACCEPT_BASENAME,
                'checkPeriod'     => '24',
                'use_wp_update'   => false,
            ),
            'payment_method_paths' => array(
                EEA_AUTHORIZENET_ACCEPT_PATH . 'payment_methods' . DS . 'AuthorizeNet_Accept'
            ),
            'module_paths' 		    => array( EEA_AUTHORIZENET_ACCEPT_PATH . 'EED_AuthorizeNet_Accept.module.php' ),
            'namespace'        => array(
                'FQNS' => 'EventEspresso\Authnet\Accept',
                'DIR'  => __DIR__,
            ),
        ));
    }


    /**
     * 	Additional admin hooks.
     *
     *  @access 	public
     *  @return 	void
     */
    public static function additional_authorizenet_accept_admin_hooks()
    {
        // is admin and not in M-Mode ?
        if (is_admin() && !EE_Maintenance_Mode::instance()->level()) {
            add_filter('plugin_action_links', array('EE_AuthorizeNet_Accept', 'plugin_actions'), 10, 2);
        }
    }


    /**
     * Add a settings link to the Plugins page.
     *
     * Add a settings link to the Plugins page, so people can go straight from the plugin page to the settings page.
     * @param 	$links
     * @param 	$file
     * @return 	array
     */
    public static function plugin_actions($links, $file)
    {
        if ($file == EEA_AUTHORIZENET_ACCEPT_BASENAME) {
            // Before other links
            array_unshift($links, '<a href="admin.php?page=espresso_payment_settings">' . __('Settings') . '</a>');
        }
        return $links;
    }
}

// End of file EE_AuthorizeNet_Accept.class.php
// Location: wp-content/plugins/eea-authorize-net-sim-pm/EE_AuthorizeNet_Accept.class.php
