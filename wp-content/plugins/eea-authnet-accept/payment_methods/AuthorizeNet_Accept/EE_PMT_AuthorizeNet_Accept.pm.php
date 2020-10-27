<?php

use EventEspresso\Authnet\Accept\forms\AcceptBilling;
use EventEspresso\Authnet\Accept\forms\AcceptIFrame;

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('NO direct script access allowed');
}



/**
 *
 * EE_PMT_AuthorizeNet_Accept
 *
 *
 * @package			Event Espresso
 * @subpackage		eea-authorizenet-accept
 * @author			Event Espresso
 *
 */
class EE_PMT_AuthorizeNet_Accept extends EE_PMT_Base
{

    /**
     * path to the templates folder for the Authorize.net PM
     * @var string
     */
    protected $_template_path = null;


    /**
     * @return \EE_PMT_AuthorizeNet_Accept
     */
    public function __construct($pm_instance = null)
    {
        require_once( $this->file_folder() . 'EEG_AuthorizeNet_Accept.gateway.php' );
        $this->_gateway = new EEG_AuthorizeNet_Accept();
        $this->_pretty_name = __("Authorize.net Accept", 'event_espresso');
        $this->_default_description = __( 'Please provide the following billing information.', 'event_espresso' );
        $this->_template_path = dirname(__FILE__) . DS . 'templates' . DS;
        $this->_requires_https = true;

        parent::__construct($pm_instance);
        $this->_default_button_url = $this->file_url() . 'lib' . DS . 'authorize-net-logo.png';
    }


    /**
     * Gets the form for all the settings related to this payment method type
     *
     * @return EE_Payment_Method_Form
     */
    public function generate_new_settings_form()
    {
        $form = new EE_Payment_Method_Form(array(
            'extra_meta_inputs' => array(
                'api_login_id'         => new EE_Text_Input(
                    array(
                        'html_label_text' => sprintf(__("Authorize.net API login ID %s", "event_espresso"),
                            $this->get_help_tab_link()),
                        'required'        => true
                    )
                ),
                'transaction_key'      => new EE_Text_Input(
                    array(
                        'html_label_text' => sprintf(__("Authorize.net Transaction Key %s", "event_espresso"),
                            $this->get_help_tab_link()),
                        'required'        => true
                    )
                ),
                'billing_address' => new EE_Select_Input(
                    array(
                        'hide' => esc_html__('Hide', 'event_espresso'),
                        'show' => esc_html__('Show, but don\'t require', 'event_espresso'),
                        'require' => esc_html__('Require', 'event_espresso'),
                    ),
                    array(
                        'html_label_text' => esc_html__('Billing Address', 'event_espresso'),
                        'default' => 'hide'
                    )
                ),
                'shipping_address' => new EE_Select_Input(
                    array(
                        'hide' => esc_html__('Hide', 'event_espresso'),
                        'show' => esc_html__('Show, but don\'t require', 'event_espresso'),
                        'require' => esc_html__('Require', 'event_espresso'),
                    ),
                    array(
                        'html_label_text' => esc_html__('Shipping Address', 'event_espresso'),
                        'default' => 'hide'
                    )
                ),
            )
        ));

        return $form;
    }


    /**
     * Just to post some debug info on the checkour page.
     *
     * @param \EE_Transaction $transaction
     * @param array $extra_args may contain key 'amount_owing'
     * @return null
     */
    public function generate_new_billing_form(EE_Transaction $transaction = null, $extra_args = array())
    {
        $form = new AcceptBilling(
            $this->_pm_instance,
            $transaction,
            $extra_args
        );

        return $form;
    }



    public function help_tabs_config()
    {
        return array(
            $this->get_help_tab_name() => array(
                'title'    => __('Authorize.net Accept Settings', 'event_espresso'),
                'filename' => 'payment_methods_overview_authorizenet_accept'
            ),
        );
    }
}

// End of file EE_PMT_AuthorizeNet_Accept.pm.php
