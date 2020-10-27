<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}



/**
 *
 * EEG_AuthorizeNet_Accept
 *
 * @package			Event Espresso
 * @subpackage		eea-authorizenet-accept
 * @author			Event Espresso
 *
 */
class EEG_AuthorizeNet_Accept extends EE_Onsite_Gateway
{

    /**
     *
     * @var $_AuthorizeNet_Accept_transaction_key string.
     */
    protected $_transaction_key = null;

    /**
     *
     * @var $_AuthorizeNet_Accept_api_login_id string.
     */
    protected $_api_login_id = null;

    /**
     *
     * @var $_gateway_url string
     */
    protected $_gateway_url = null;

    /**
     * @var string values 'hide', 'show' or 'require'
     */
    protected $_shipping_address;

    /**
     * @var string values 'hide', 'show' or 'require'
     */
    protected $_billing_address;


    /**
     * Sets the gateway url variable based on whether debug mode is enabled or not.
     *
     * @param type $settings_array
     */
    public function set_settings($settings_array)
    {
        parent::set_settings($settings_array);
        if ($this->_debug_mode) {
            $this->_gateway_url = 'https://test.authorize.net/payment/payment';
            $this->_token_request_uri = 'https://apitest.authorize.net/xml/v1/request.api';
        } else {
            $this->_gateway_url = 'https://accept.authorize.net/payment/payment';
            $this->_token_request_uri = 'https://api.authorize.net/xml/v1/request.api';
        }
    }


    /**
     * Override parent so we can declare that we receive an IPN-request (the relay resposne)
     * and so shouldn't process payment-stuff when returning to SPCO.
     * Also declare we don't support receiving notifications via refund yet
     */
    public function __construct()
    {
        $this->_currencies_supported = array(
            'USD',
            'CAD',
            'AUD',
            'EUR',
            'GBP',
            'NZD'
        );

        parent::__construct();
        //only use the method set_uses_separate_IPN_request if the method exists,
        //which was in 4.6.18 or so
        if (method_exists($this, 'set_uses_separate_IPN_request')) {
            $this->set_uses_separate_IPN_request(false);
        }
        $this->_supports_receiving_refunds = false;
    }



    /**
     * Tells authnet about the payment so we can get the URL for the iFrame
     * @param EEI_Payment $payment
     * @param string $iframe_communicator_url
     * @param string $payment_canclled_url
     * @return EEI_Payment
     */
    public function getIFrameUrl(EEI_Payment $payment, $iframe_communicator_url, $payment_canclled_url)
    {
        // Payment OK ?
        if (! $payment instanceof EEI_Payment) {
            $payment->set_gateway_response(
                esc_html__(
                    'Error. No associated payment was found.',
                    'event_espresso'
                )
            );
            $payment->set_status($this->_pay_model->failed_status());
            return $payment;
        }
        // Transaction OK ?
        $transaction = $payment->transaction();
        if (! $transaction instanceof EEI_Transaction) {
            $payment->set_gateway_response(
                esc_html__(
                    'Could not process this payment because it has no associated transaction.',
                    'event_espresso'
                )
            );
            $payment->set_status($this->_pay_model->failed_status());
            return $payment;
        }
        // Primary attendee OK ?
        $primary_registrant = $transaction->primary_registration();
        $primary_attendee = ($primary_registrant instanceof EEI_Registration) ? $primary_registrant->attendee() : false;
        if (! $primary_attendee instanceof EE_Attendee) {
            $payment->set_gateway_response(
                esc_html__(
                    'Could not process this payment because it has no associated Attendee.',
                    'event_espresso'
                )
            );
            $payment->set_status($this->_pay_model->failed_status());
            return $payment;
        }

        $gateway_formatter = $this->_get_gateway_formatter();
        $order_description = substr($gateway_formatter->formatOrderDescription($payment), 0, 255);
        $amount = $gateway_formatter->formatCurrency($payment->amount());
        // The invoice number is generated using the date and time.
        $invoice = date('YmdHis');
        // Randomly generate the sequence number.
        $sequence = rand(1, 1000);
        // Generate the timestamp.
        $timeStamp = time();

        // Get the list items.
        $list_items = $this->itemize_list($payment, $transaction);
        $total_line_items = $transaction->total_line_item();

        // Authorize.net requires (only) the name-value pairs embedded in the URL to be URL-encoded.
        if (strpos($iframe_communicator_url, '?') !== false) {
            $return_url_parts = explode('?', $iframe_communicator_url);
            $iframe_communicator_url = $return_url_parts[0] . '?' . urlencode($return_url_parts[1]);
        } else {
            $iframe_communicator_url = $iframe_communicator_url;
        }
        // only send the address info if the user has a chance to modify it (otherwise Authnet uses it, and it might
        // not be correct. eg if they're using a company credit card)
        $bill_to = array(
            'firstName' => $this->prepareAPIField($primary_attendee->fname(),50),
            'lastName'  => $this->prepareAPIField($primary_attendee->lname(), 50),
        );
        if( $this->showAddressFields($this->_billing_address)){
            $bill_to = array_merge(
                $bill_to,
                array(
                    'address'   => $this->prepareAPIField($primary_attendee->address(), 60),
                    'city'      => $this->prepareAPIField($primary_attendee->city(), 40),
                    'state'     => $primary_attendee->country_ID() === 'US'
                        ? $this->prepareAPIField($primary_attendee->state_abbrev(), 2)
                        : $this->prepareAPIField($primary_attendee->state_name(), 40),
                    'zip'       => $this->prepareAPIField($primary_attendee->zip(), 20),
                    'country'   => $this->prepareAPIField($primary_attendee->country_name(), 60),
                )
            );
        }
        // Prep the request parameters.
        // Note: the order of request parameters matters !
        $request_params = apply_filters(
            'FHEE__EEG_AuthorizeNet_Accept__getIFrameUrl__request_params',
            array(
                'getHostedPaymentPageRequest' => array(
                    'merchantAuthentication' => array(
                        'name'           => $this->_api_login_id,
                        'transactionKey' => $this->_transaction_key
                    ),
                    'transactionRequest'     => array(
                        'transactionType' => 'authCaptureTransaction',
                        'amount'          => $amount,
                        'solution'        => array(
                            'id' => $this->_debug_mode ? 'AAA100302' : 'AAA105363'
                        ),
                        'order'           => array(
                            'invoiceNumber' => $invoice,
                            'description'   => $this->prepareAPIField($order_description, 300),
                        ),
                        // List of line-items here.
                        'lineItems'       => $list_items,
                        'tax'             => array('amount' => $total_line_items->get_total_tax()),
                        'customer'        => array(
                            'email' => substr($primary_attendee->email(), 0, 255)
                        ),
                        'billTo'          => $bill_to
                    ),
                    'hostedPaymentSettings' => array(
                        'setting' => array(
                            array(
                                'settingName'  => 'hostedPaymentReturnOptions',
                                'settingValue' => json_encode(
                                    array(
                                        'url'           => $iframe_communicator_url,
                                        'urlText'       => esc_html__('Continue', 'event_espresso'),
                                        'showReceipt'   => false,
                                        'cancelUrl'     => $payment_canclled_url,
                                        'cancelUrlText' => esc_html__('Reset Form And Try Again', 'event_espresso')
                                    )
                                )
                            ),
                            array(
                                'settingName'  => 'hostedPaymentOrderOptions',
                                'settingValue' => json_encode(
                                    array(
                                        'show'         => true,
                                        'merchantName' => $this->prepareAPIField(urlencode(EE_Config::instance()->organization->name),255),
                                    )
                                )
                            ),
                            array(
                                'settingName'  => 'hostedPaymentPaymentOptions',
                                'settingValue' => json_encode(
                                    array(
                                        'cardCodeRequired' => true,
                                        'showCreditCard'   => true,
                                        'showBankAccount'  => true
                                    )
                                )
                            ),
                            array(
                                'settingName'  => 'hostedPaymentBillingAddressOptions',
                                'settingValue' => json_encode(
                                    array(
                                        'show'     => $this->showAddressFields($this->_billing_address),
                                        'required' => $this->requireAddressFields($this->_billing_address),
                                    )
                                )
                            ),
                            array(
                                'settingName'  => 'hostedPaymentShippingAddressOptions',
                                'settingValue' => json_encode(
                                    array(
                                        'show'     => $this->showAddressFields($this->_shipping_address),
                                        'required' => $this->requireAddressFields($this->_shipping_address),
                                    )
                                )
                            ),
                            array(
                                'settingName'  => 'hostedPaymentCustomerOptions',
                                'settingValue' => json_encode(
                                    array(
                                        'showEmail'     => false,
                                        'requiredEmail' => false
                                    )
                                )
                            ),
                            array(
                                'settingName'  => 'hostedPaymentIFrameCommunicatorUrl',
                                'settingValue' => json_encode(
                                    array(
                                        'url' => $iframe_communicator_url,
                                    )
                                )
                            )
                        )
                    )
                ),
            ),
            $payment,
            $iframe_communicator_url
        );

        // Parameters.
        $post_params = array(
            'method'      => 'POST',
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'headers'     => array(
                'Content-Type' => 'application/json',
            ),
            'body'        => json_encode($request_params),
        );
        // Send the request.
        $token_request = wp_remote_post($this->_token_request_uri, $post_params);
        // Now process the response.
        $response = false;
        $error_info_txt = esc_html__('Payment page token request', 'event_espresso');
        if (! is_wp_error($token_request) && isset($token_request['body']) && $token_request['body']) {
            // Clean the encoded string from any whitespace or invisible separator like zero-width-space...
            $trimmed_response = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $token_request['body']);
            // and try to decode.
            $response = json_decode($trimmed_response, true);
        }
        if (
            ! $response
            || ! isset($response['token'])
            || ! $response['token']
            || ! isset($response['messages'])
            || $response['messages']['resultCode'] !== 'Ok'
        ) {
            // Got an error requesting a token for the payment page.
            if (
                isset($response['messages'], $response['messages']['message'])
                && is_array($response['messages']['message'])
            ) {
                $msg_details = reset($response['messages']['message']);
                $err_msg = $msg_details['text'];
            } else {
                $err_msg = esc_html__('Unrecognized "getHostedPaymentPageRequest" Error', 'event_espresso');
            }
            $this->log(
                array(
                    $error_info_txt => $token_request,
                    'request' => $request_params
                ),
                // The payment object has not been saved to DB yet and has no ID so we need to pass the transaction object to the log.
                $transaction);
            $payment->set_status($this->_pay_model->failed_status());
            $payment->set_gateway_response($err_msg);
            return $payment;
        }

        $payment->set_txn_id_chq_nmbr($response['token']);
        $redirect_args = array(
            'token' => $response['token']
        );

        $payment->set_redirect_url($this->_gateway_url);
        $payment->set_redirect_args($redirect_args);

        $this->log(
            array(
                'authnet accept request' => $request_params,
                'authnet accept response' => $response
            ),
            // The payment object has not been saved to DB yet and has no ID so we need to pass the transaction object to the log.
            $transaction
        );

        return $payment;
    }



    /**
     * Whether to show these address fields or not.
     * @param string $address_setting
     */
    protected function showAddressFields($address_setting)
    {
        return in_array(
            $address_setting,
            array(
                'show',
                'require'
            ),
            true
        ) ? true
            : false;
    }


    /**
     * Whether to require these address fields or not.
     * @param string $address_setting
     */
    protected function requireAddressFields($address_setting)
    {
        return $address_setting === 'require'
            ? true
            : false;
    }


    /**
     * Handles the payment update.
     *
     * Note: Authorize.net SIM uses a 'Relay Response' for communicating the
     * transaction results to the customer which does not redirect the customer back to the merchant website,
     * but it relays the content from the specified Relay URL (from the merchant's website) to the customer through Authorize.net receipt page.
     * So we use a script to redirect the customer back to the purchase website as a response to that Relay call.
     *
     * @param EEI_Payment $payment
     * @param array $billing_info
     * @return EEI_Payment updated
     * @throws EE_Error
     */
    public function do_direct_payment($payment, $billing_info = null)
    {
	    $request_params = array(
			'getTransactionDetailsRequest' => array(
			'merchantAuthentication' => array(
				'name'           => $this->_api_login_id,
				'transactionKey' => $this->_transaction_key
			),
			'transId' => $billing_info['trans_id'],
		));
	    // Parameters.
	    $post_params = array(
		    'method'      => 'POST',
		    'timeout'     => 60,
		    'redirection' => 5,
		    'blocking'    => true,
		    'headers'     => array(
			    'Content-Type' => 'application/json',
		    ),
		    'body'        => json_encode($request_params),
	    );
	    // Send the request.
	    $txn_status_response = wp_remote_post($this->_token_request_uri, $post_params);
	    // Now process the response.
	    $response = false;
	    if (is_wp_error($txn_status_response) || !isset($txn_status_response['body']) || !$txn_status_response['body']) {
		    $this->log(
		        array(
		            'error_communicating_with_authnet' => $response
                ),
                $payment
            );
	    }
        // Clean the encoded string from any whitespace or invisible separator like zero-width-space...
        $trimmed_response = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $txn_status_response['body']);
        // and try to decode.
        $response = json_decode($trimmed_response, true);

        if ($payment instanceof EEI_Payment && is_array($response)) {
            if (isset($response['transaction'])) {
                $accept_transaction = $response['transaction'];
                switch ((int)$accept_transaction['responseCode']) {
                    case 1:
                        $payment->set_status($this->_pay_model->approved_status());
                        $payment->set_amount(floatval($accept_transaction['settleAmount']));
                        break;
                    case 4:
                        $payment->set_status($this->_pay_model->pending_status());
                        break;
                    case 2:
                        $payment->set_status($this->_pay_model->declined_status());
                        break;
                    case 3:
                        $payment->set_status($this->_pay_model->failed_status());
                        break;
                    default:
                        $payment->set_status($this->_pay_model->declined_status());
                }
                $payment->set_gateway_response($accept_transaction['responseReasonDescription']);
                $payment->set_txn_id_chq_nmbr($accept_transaction['transId']);
                $this->log(array('AuthNet Accept Response' => $response), $payment);
            } elseif (isset($response['messages'], $response['messages']['message'])) {
                   $first_message = $response['messages']['message'][0];
                   if ($first_message['code'] === 'E00011' && $billing_info['response_code'] === '1') {
                       //the user doesn't appear to have granted the needed permissions to use the transaction API
                       //let's fallback to using the response received over JS, which we assume was from authnet
                       $payment->set_status($this->_pay_model->approved_status());
                       $payment->set_amount($billing_info['payment_amount']);
                       $payment->set_gateway_response(
                           sprintf(
                                esc_html__(
                                    'Successful payment, but it could not be verified. Please check the Transaction Details API is enabled for your account. See %1$s',
                                    'event_espresso'
                                ),
                                'https://developer.authorize.net/api/reference/features/transaction_reporting.html'
                           )
                       );
                       $payment->set_txn_id_chq_nmbr($billing_info['trans_id']);
                   } else {
                       $payment->set_status($this->_pay_model->failed_status());
                       $payment->set_gateway_response(
                           sprintf(
                               '%1$s (%2$s)',
                               $first_message['code'],
                               $first_message['text']
                           )
                       );
                   }
            } else {
                $payment->set_gateway_response(__('The response from Authorize.net could to be defined.', 'event_espresso'));
                $payment->set_status($this->_pay_model->failed_status());
                $this->log(array('Accept response could to be defined' => $response), $payment);
            }
            return $payment;
        }
        $this->log(array('No payment for the transaction' => $billing_info), $payment);
        throw new EE_Error(
            sprintf(
                __("Could not find Authorize.net Accept payment for EE payment %s", 'event_espresso'),
                $payment->ID()
            )
        );
    }


    /**
     * Make a list of items that are in the giver transaction.
     *
     * @param EEI_Payment     $payment
     * @param EEI_Transaction $transaction
     * @return array
     */
    public function itemize_list(EEI_Payment $payment, EEI_Transaction $transaction)
    {
        $items_list = array();
        $gateway_formatter = $this->_get_gateway_formatter();

        // Generate a new list for this transaction.
        if (EEH_Money::compare_floats($payment->amount(), $transaction->total(), '==')) {
            $item_num = 0;
            $itemized_sum = 0;
            $all_line_items = $transaction->total_line_item();
            // Go through all items in the list.
            foreach ($all_line_items->get_items() as $line_item) {
                if ($line_item instanceof EE_Line_Item) {
                    $item_info = array();
                    $item_price = $line_item->unit_price();
                    $line_item_quantity = $line_item->quantity();
                    // A discount ?
                    if ($line_item->is_percent()) {
                        $item_price = $line_item->total();
                        $line_item_quantity = 1;
                    }

                    $item_info['itemId'] = $line_item->ID();
                    $item_info['name'] = substr($gateway_formatter->formatLineItemName($line_item, $payment), 0, 31);
                    $item_info['description'] = substr($gateway_formatter->formatLineItemDesc($line_item, $payment), 0, 255);
                    $item_info['quantity'] = $line_item_quantity;
                    // API is ok with 0.00 rpice items but not with negative numbers.
                    $item_info['unitPrice'] = $gateway_formatter->formatCurrency(abs($item_price));
                    $item_info['taxable'] = $line_item->is_taxable();
                    // Add item to the list.
                    $items_list[] = $item_info;
                    $itemized_sum += $line_item->total();
                    ++$item_num;
                }
            }
            $itemized_sum_diff_from_txn_total = round(
                $transaction->total() - $itemized_sum - $all_line_items->get_total_tax(),
                2
            );
            // If we were not able to recognize some item - add the difference as an extra line item.
            if (EEH_Money::compare_floats($itemized_sum_diff_from_txn_total, 0, '!=')) {
                $item_info = array();
                $item_info['itemId'] = $item_num . '_other';
                $item_info['name'] = substr(esc_html__('Other (promotion/surcharge)', 'event_espresso'), 0, 31);
                $item_info['description'] = '';
                $item_info['quantity'] = 1;
                $item_info['unitPrice'] = $gateway_formatter->formatCurrency(abs($itemized_sum_diff_from_txn_total));
                $item_info['taxable'] = false;
                // Add item to the list.
                $items_list[] = $item_info;
                ++$item_num;
            }
        } else {
            // Just one Item.
            $item_info = array();
            $item_info['itemId'] = 1;
            $item_info['name'] = substr($gateway_formatter->formatPartialPaymentLineItemName($payment), 0, 31);
            $item_info['description'] = substr($gateway_formatter->formatPartialPaymentLineItemDesc($payment), 0, 127);
            $item_info['quantity'] = 1;
            $item_info['unitPrice'] = $gateway_formatter->formatCurrency($payment->amount());
            $item_info['taxable'] = false;
            // Add item to the list.
            $items_list[] = $item_info;
        }
        return array('lineItem' => $items_list);
    }



    /**
     * Removes characters authnet can't handle.
     * @param string $original_string
     * @param int $max_length
     * @return string
     */
    protected function prepareAPIField($original_string, $max_length)
    {
        $prepared_string =  substr(
            preg_replace(
                '~[^a-zA-Z0-9 ]~',
                '',
                $original_string
            ),
            0,
            $max_length
        );
        if( ! $prepared_string) {
        	$prepared_string = '';
        }
        return $prepared_string;
    }
}

// End of file EEG_AuthorizeNet_Accept.gateway.php
