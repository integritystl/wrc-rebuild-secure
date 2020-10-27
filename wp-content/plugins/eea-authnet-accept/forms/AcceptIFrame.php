<?php

namespace EventEspresso\Authnet\Accept\forms;

use EE_Form_Section_Proper;
use EE_Payment;
use EE_Payment_Method;
use EE_Transaction;
use EEG_AuthorizeNet_Accept;
use EEM_Payment;
use EventEspresso\core\exceptions\EntityNotFoundException;
use EventEspresso\core\exceptions\UnexpectedEntityException;

defined('EVENT_ESPRESSO_VERSION') || exit('No direct script access allowed');



/**
 * Class AcceptIFrameFormSection
 * Description
 *
 * @package        Event Espresso
 * @author         Mike Nelson
 * @since          1.0.0.p
 */
class AcceptIFrame extends EE_Form_Section_Proper
{

    /**
     * @var EE_Payment_Method
     */
    protected $payment_method;


    /**
     * @var EE_Transaction
     */
    protected $transaction;

    /**
     * @var float
     */
    protected $amount_owing;

    public function __construct(EE_Payment_Method $payment_method, EE_Transaction $transaction, array $options_array = array())
    {
        $this->payment_method = $payment_method;
        $this->transaction = $transaction;
        if (isset($options_array['amount_owing'])) {
            $this->amount_owing = (float)$options_array['amount_owing'];
        } else {
            $this->amount_owing = $transaction->remaining();
        }

        parent::__construct($options_array);
    }



    /**
     * Overrides parent because the URL for the iFrame depends on the transaction.
     * We only do this while fetching the HTML because this isn't necessary when using the form only
     * for form validation.
     * @param bool $display_previously_submitted_data unused
     * @return string
     * @throws UnexpectedEntityException
     */
    public function get_html($display_previously_submitted_data = true)
    {
        $gateway_obj = $this->paymentMethod()->type_obj()->get_gateway();
        if (! $gateway_obj instanceof EEG_AuthorizeNet_Accept) {
            throw new UnexpectedEntityException(
                $gateway_obj,
                'EEG_AuthorizeNet_Accept'
            );
        }
        $payment = EE_Payment::new_instance(
            array(
                'STS_ID'               => EEM_Payment::status_id_failed,
                'TXN_ID'               => $this->transaction()->ID(),
                'PMD_ID'               => $this->paymentMethod()->ID(),
                'PAY_source'           => 'CART',
                'PAY_amount'           => $this->amountOwing(),
                'PAY_gateway_response' => null,
                'PAY_timestamp'        => time(),
            )
        );
        $payment = $gateway_obj->getIFrameUrl(
            $payment,
            EEA_AUTHORIZENET_ACCEPT_URL . 'iframe-communicator.php',
            EEA_AUTHORIZENET_ACCEPT_URL . 'payment-cancelled.html'
        );

        $html = '<div class="accept-iframe-container"><iframe id="authnet_accept_iframe" class="embed-responsive-item accept-iframe" name="authnet_accept_iframe" allowpaymentrequest frameborder="0" scrolling="auto"></iframe></div>';

        if (! $payment->get('PAY_redirect_url')) {
            return $html;
        }
        $iframe_init_form_html = '<form id="authnet_accept_iframe_init_form" action="' . $payment->redirect_url()
            . '" method="post" target="authnet_accept_iframe" >';
        foreach($payment->redirect_args() as $name => $value) {
            $iframe_init_form_html .= '<input type="hidden"  name="' . $name . '" value="' . $value . '" />';
        }
        $html .= '<input type="hidden" id="iframe_init_form_content" value="' .  esc_attr($iframe_init_form_html). '"/>';
        $this->transaction()->set_payment_method_ID($this->paymentMethod()->ID());
        $this->transaction()->save();
        return $html;
    }



    /**
     * Gets the payment method for this form
     * @throws UnexpectedEntityException
     * @return EE_Payment_Method
     */
    private function paymentMethod()
    {
        if (! $this->payment_method instanceof EE_Payment_Method) {
            throw new UnexpectedEntityException(
                $this->payment_method,
                'EE_Payment_Method'
            );
        }
        return $this->payment_method;
    }

    /**
     * Gets the transaction for this form
     * @throws UnexpectedEntityException
     * @return EE_Transaction
     */
    private function transaction()
    {
        if (! $this->transaction instanceof EE_Transaction) {
            throw new UnexpectedEntityException(
                $this->transaction,
                'EE_Transaction'
            );
        }
        return $this->transaction;
    }



    /**
     * Gets the amount owing for this transaction
     * @return float
     */
    private function amountOwing()
    {
        return $this->amount_owing;
    }

}
// End of file AcceptIFrameFormSection.php
// Location: ${NAMESPACE}/AcceptIFrameFormSection.php
