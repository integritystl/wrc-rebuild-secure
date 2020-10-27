<?php

namespace EventEspresso\Authnet\Accept\forms;

use EE_Billing_Info_Form;
use EE_Form_Section_Proper;
use EE_Hidden_Input;
use EE_Payment_Method;
use EE_Template_Layout;
use EE_Transaction;

defined('EVENT_ESPRESSO_VERSION') || exit('No direct script access allowed');



/**
 * Class AcceptBillingForm
 * Billing form for Authorize.net Accept iFrame. Mostly the base form but also enqueues javascript
 *
 * @package        Event Espresso
 * @author         Mike Nelson
 * @since          1.0.0.p
 */
class AcceptBilling extends \EE_Billing_Attendee_Info_Form
{

    /**
     * @var EE_Transaction
     */
    protected $transaction;

    public function __construct(EE_Payment_Method $payment_method, EE_Transaction $transaction = null, array $options_array = array())
    {
        $this->transaction = $transaction;
        $settings = $payment_method->settings_array();
        $iframe_input = $transaction instanceof EE_Transaction
            ? new AcceptIFrame($payment_method, $transaction, $options_array)
            : new \EE_Form_Section_HTML('');
        $options_array = array_merge(
            array(
                'name'        => 'AuthorizeNet_Accept_Info_Form',
                'html_id'     => 'authorizenet-accept-info-form',
                'subsections' => array(
                    'debug' => new EE_Form_Section_Proper(
                        array(
                            'layout_strategy' => new EE_Template_Layout(
                                array(
                                    'layout_template_file' => $payment_method->type_obj()->file_folder()
                                                                . 'templates/authorizenet_accept_debug_info.template.php',
                                    'template_args'        => array(
                                        'debug_mode' => $payment_method->debug_mode(),
                                    )
                                )
                            )
                        )
                    ),
                    'iframe'         => $iframe_input,
                    'trans_id'       => new EE_Hidden_Input(),
                    'response_code'  => new EE_Hidden_Input(),
                    'payment_amount' => new EE_Hidden_Input(),

                )
            ),
            $options_array
        );
        add_filter('FHEE__EE_Billing_Attendee_Info_Form__state_field', array($this,'useTextStateInput'));
        add_filter('FHEE__EE_Billing_Attendee_Info_Form__country_field', array($this,'useTextCountryInput'));
        parent::__construct($payment_method, $options_array);
        remove_filter('FHEE__EE_Billing_Attendee_Info_Form__state_field', array($this,'useTextStateInput'));
        remove_filter('FHEE__EE_Billing_Attendee_Info_Form__country_field', array($this,'useTextCountryInput'));
        $this->exclude(
            array(
                'address2',
                'email',//authnet doesn't tell us what email the user inputted when this question is included in the iframe page
            )
        );
        $dynamically_included_fields = array(
            'first_name',
            'last_name',
            'address',
            'city',
            'state',
            'country',
            'zip',
            'phone'
        );
        if ($settings['billing_address'] === 'hide') {
            $this->exclude($dynamically_included_fields);
        } elseif ($transaction instanceof EE_Transaction) {
            //we're showing this form for a payment. Hide the billing inputs
            $this->hide($dynamically_included_fields);
            foreach($dynamically_included_fields as $field_name) {
                $this->get_input($field_name)->set_required(false);
            }
        }
    }



    /**
     * Callback for making the billing form use a text input, because the value we're going to populate it
     * with, from Authorize.net, won't necessarily fit into our list of states.
     * @param $original_input
     * @return \EE_Text_Input
     */
    public function useTextStateInput($original_input)
    {
        return new \EE_Text_Input(
            array(
                'html_label_text' => esc_html__('State', 'event_espresso')
            )
        );
    }

    /**
     * Callback for making the billing form use a text input, because the value we're going to populate it
     * with, from Authorize.net, won't necessarily fit into our list of countries.
     * @param $original_input
     * @return \EE_Text_Input
     */
    public function useTextCountryInput($original_input)
    {
        return new \EE_Text_Input(
            array(
                'html_label_text' => esc_html__('Country', 'event_espresso')
            )
        );
    }


    /**
     * Also enqueue javascript specific to this billing form
     */
    public function enqueue_js()
    {
        $txn_id = $this->transaction() instanceof EE_Transaction ? $this->transaction()->ID() : null;
        if (current_user_can('ee_read_ee')) {
            // provide a more useful message to event managers
            $no_response_message = sprintf(
                esc_html__(
                    'The payment processor Authorize.net appears to not be responding. Their server may be having issues, or %1$s may have content being served over HTTP and your browser is preventing the page from loading completely. Please contact the site owner and try again using a different browser.',
                    'event_espresso'
                ),
                get_bloginfo('name')
            );
        } else {
            // provide a more friendly message to site visitors
            $no_response_message = esc_html__('The billing form could not be displayed. Your web browser may not be supported. Please restart your registration using different browser and notify the site owner.', 'event_espresso');
        }
        $accept_js_data = array(
            'translations' => array(
                'iframe_init_form_submit_error' => esc_html__(
                    'There was an error initializing the payment form from Authorize.net. Please contact the site administrator.',
                    'event_espresso'
                ),
                'no_SPCO_error' => esc_html__(
                    'It appears the Single Page Checkout javascript was not loaded properly! Please refresh the page and try again or contact support.',
                    'event_espresso'
                ),
                'no_response_from_authnet' => $no_response_message,
                'error_logging_error' => esc_html__(
                    'We were unable to send an error report to the server. Please notify the site owner. The error was: ',
                    'event_espresso'
                ),
                'authnet_iframe_timeout' => apply_filters(
                    'FHEE__EventEspresso__Authnet__Accept__forms__AcceptBilling__enqueue_js__authnet_iframe_timeout',
                    30,
                    $this->payment_method()
                ),

            ),
            'data' => array(
                'txn_id' => $txn_id,
                'authnet_trans_id_id'      => $this->get_input('trans_id')->html_id(true),
                'authnet_response_code_id' => $this->get_input('response_code')->html_id(true),
                'authnet_amount_id'        => $this->get_input('payment_amount')->html_id(true),
            )
        );
        $settings = $this->payment_method()->settings_array();
        if ($settings['billing_address'] !== 'hide') {
            $accept_js_data['data'] +=  array(
                'first_name_input_id' => $this->get_input('first_name')->html_id(true),
                'last_name_input_id'  => $this->get_input('last_name')->html_id(true),
                'address_input_id'    => $this->get_input('address')->html_id(true),
                'city_input_id'       => $this->get_input('city')->html_id(true),
                'state_input_id'      => $this->get_input('state')->html_id(true),
                'country_input_id'    => $this->get_input('country')->html_id(true),
                'zip_input_id'        => $this->get_input('zip')->html_id(true),
                'phone_input_id'      => $this->get_input('phone')->html_id(true)
            );
        }
        EE_Form_Section_Proper::$_js_localization['authnet_accept'][$this->payment_method()->slug()] = $accept_js_data;
        wp_enqueue_script(
            'authnet_accept_iframe',
            EEA_AUTHORIZENET_ACCEPT_URL . 'scripts/authnet_accept_iframe.js',
            array('jquery', 'single_page_checkout'),
            EEA_AUTHORIZENET_ACCEPT_VERSION,
            true
        );
        wp_enqueue_style(
            'authnet_accept_iframe',
            EEA_AUTHORIZENET_ACCEPT_URL . 'styles/authnet_accept_iframe.css',
            array(),
            EEA_AUTHORIZENET_ACCEPT_VERSION
        );
        parent::enqueue_js(); // TODO: Change the autogenerated stub
    }



    /**
     * Gets the transaction this billing form is for.
     * @return EE_Transaction
     */
    protected function transaction()
    {
        return $this->transaction;
    }


}
// End of file AcceptBillingForm.php
// Location: EventEspresso\Authnet\Accept\forms/AcceptBillingForm.php