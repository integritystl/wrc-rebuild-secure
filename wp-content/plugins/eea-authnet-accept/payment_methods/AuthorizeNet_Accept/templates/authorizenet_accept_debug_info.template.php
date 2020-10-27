<?php if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
} ?>

<div id="authorizenet-accept-sandbox-panel" class="sandbox-panel">

    <?php if ($debug_mode) { ?>
        <h6 class="important-notice" style="text-align: center;"><?php _e('Debug Mode is turned ON. After clicking \'Finalize Registration\' You will be redirected to the Authorize.net Sandbox environment.',
        'event_espresso'); ?></h6>
    <?php } ?>

<?php if ( $debug_mode) { ?>
        <p class="test-credit-cards-info-pg">
            <strong><?php _e('Information to use for Testing:', 'event_espresso'); ?></strong>
        </p>
        <div class="tbl-wrap">
            <table id="accept-test-credit-cards" class="test-credit-card-data-tbl">
                <thead>
                    <tr>
                        <td colspan="2"><b><?php _e(' Test Cards:', 'event_espresso'); ?></b></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>4007000000027</td>
                        <td><?php _e('Visa Test Card', 'event_espresso'); ?></td>
                    </tr>
                    <tr>
                        <td>370000000000002</td>
                        <td><?php _e('American Express Test Card', 'event_espresso'); ?></td>
                    </tr>
                    <tr>
                        <td>6011000000000012</td>
                        <td><?php _e('Discover Test', 'event_espresso'); ?></td>
                    </tr>
                    <tr>
                        <td>3088000000000017</td>
                        <td><?php _e('JCB', 'event_espresso'); ?></td>
                    </tr>
                    <tr>
                        <td>38000000000006</td>
                        <td><?php _e('Diners Club/ Carte Blanche', 'event_espresso'); ?></td>
                    </tr>
                    <tr>
                        <td>5424000000000015</td>
                        <td><?php _e('MasterCard', 'event_espresso'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p>
            <strong><?php _e('Use the following ZIP code values to generate a specific error:'); ?></strong>
        </p>
        <div class="tbl-wrap">
            <table id="accept-test-zip-codes" class="test-zip-codes-data-tbl">
                <thead>
                    <tr>
                        <td><b><?php _e('ZIP CODE', 'event_espresso'); ?></b></td>
                        <td><b><?php _e('RESPONSE TEXT', 'event_espresso'); ?></b></td>
                        <td><b><?php _e('NOTES', 'event_espresso'); ?></b></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>46282</td>
                        <td><?php _e('This transaction has been declined.', 'event_espresso'); ?></td>
                        <td><?php _e('General bank decline.', 'event_espresso'); ?></td>
                    </tr>
                    <tr>
                        <td>46206</td>
                        <td><?php _e('AVS Not Applicable.', 'event_espresso'); ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>46208</td>
                        <td><?php _e('The U.S. card issuing bank does not support AVS.', 'event_espresso'); ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>46205</td>
                        <td><?php _e('Address: No Match; ZIP Code: No Match.', 'event_espresso'); ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>46204</td>
                        <td><?php _e('The card issuing bank is of non-U.S. origin and does not support AVS.',
        'event_espresso'); ?></td>
                        <td><?php _e('Not applicable to American Express.', 'event_espresso'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p><strong><?php esc_html_e('Test Bank Accounts', 'event_espresso');?></strong></p>
        <p>
            <?php printf(
                esc_html__('When testing checking accounts, use %1$s for the routing number and %2$s for the account number.', 'event_espresso'),
            '121038265',
            '123456789012345'
            );?>
        </p>
        <p>
            <?php esc_html_e('Transactions under $100 will be accepted, those over $100 will be declined. A monthly limit of $5000 is also configured in the sandbox.  If you exceed this amount, your eCheck transactions will also be declined.', 'event_espresso');?>
        </p>
<?php } ?>

</div>
