jQuery(document).ready(function($) {
    /**
     * @namespace EE_AUTHNET_ACCEPT
     * @type {{
		 *     handler: object,
		 *     offset_from_top_modifier: number,
		 *     notification: string,
		 *     initialized: boolean,
		 *     selected: boolean,
	 * }}
     * @namespace Authnet_AcceptCheckout
     * @type {{
		 *     configure: function,
		 *     handler: object
	 * }}
     * @namespace token
     * @type {{
		 *     error: object
		 *     id: string
	 * }}
     * @namespace localization_strings
     * @type {{
	 *     iframe_init_form_submit_error: string
	 * }}
     */
    function EeAuthnetAccept (slug, spco, localization_strings, data){
        this.slug = slug;
        this.spco = spco;
        this.localization_strings = localization_strings;
        this.data = data;

        this.iframe_id = 'authnet_accept_iframe';
        this.iframe_init_form_id = 'authnet_accept_iframe_init_form';
        this.first_name_id = data.first_name_input_id;
        this.last_name_id = data.last_name_input_id;
        this.address_id = data.address_input_id;
        this.city_id = data.city_input_id;
        this.state_id = data.state_input_id;
        this.country_id = data.country_input_id;
        this.zip_id = data.zip_input_id;
        this.phone_id= data.phone_input_id;
        this.authnet_response_code_id = data.authnet_response_code_id;
        this.authnet_trans_id_id = data.authnet_trans_id_id;
        this.authnet_amount_id = data.authnet_amount_id;

        this.iframe = {};
        this.iframe_init_form = {};
        this.first_name = {};
        this.last_name = {};
        this.address = {};
        this.city = {};
        this.state = {};
        this.country = {};
        this.zip = {};
        this.phone = {};
        this.authnet_response_code = {};
        this.authnet_trans_id = {};
        this.authnet_amount = {};

        this.payment_amount = spco.payment_amount;

        this.handler = {};
        this.notification = '';
        this.initialized = false;
        this.selected = false;
        this.received_communication = false;
        this.payment_complete = false;
        //variable that holds the timeout function's ID
        this.timeout = null;




        /**
         * @function initialize
         */
        this.initialize  = function() {
           this.setupListeners();
           this.initForm();
        };

        /**
         * Setups listeners that listen for actions taken in SPCO that indicate to initialize this form
         */
        this.setupListeners = function() {
            this.setListenerForPaymentAmountChange();
            this.setListenerForPaymentOptionsStepDisplay();
            this.setListenerForPaymentMethodChange();
        };

        /**
         * @function set_listener_for_payment_amount_change
         */
        this.setListenerForPaymentAmountChange = function () {
            //console.log( JSON.stringify( '**EE_AUTHNET_ACCEPT.set_listener_for_payment_amount_change**', null, 4 ) );
            var accept = this;
            this.spco.main_container.on('spco_payment_amount', function (event, payment_amount) {
                //if the "new" amount is the same as the old one, no need to refresh the billing form
                //or if this message appeared after we've already received payment through accept,
                //also no need for this
                clearTimeout(accept.timeout);
                if( accept.payment_amount != payment_amount && ! accept.payment_complete ) {
                    //even if we're not the current payment method, listen for what the current payment amount is
                    accept.payment_amount = payment_amount;
                    if( accept.selected ) {
                        //if we're already selected and the amount just changed, we gotta re-fetch the iframe!
                        //the previous amount in it is incorrect
                        accept.spco.display_payment_method(accept.slug);
                    }
                }
            });
        };

        this.setListenerForPaymentOptionsStepDisplay = function () {
            // initialize Accept if the SPCO reg step changes to "payment_options"
            var accept = this;
            clearTimeout(accept.timeout);
            this.spco.main_container.on( 'spco_display_step',  function (event, step_to_show) {
                if ( typeof step_to_show !== 'undefined' && step_to_show === 'payment_options' ) {
                    accept.initForm();
                }
            });
        };


        /**
         * When a payment method change event happens, initialize this object we're the one selected
         */
        this.setListenerForPaymentMethodChange = function () {
            // also initialize Accept if the selected method of payment changes
            var accept = this;
            clearTimeout(accept.timeout);
            this.spco.main_container.on( 'spco_switch_payment_methods', function( event, payment_method ) {
                //SPCO.console_log( 'payment_method', payment_method, false );
                if (typeof payment_method !== 'undefined' && payment_method === accept.slug) {
                    accept.initialized = false;
                    accept.initForm();
                } else {
                    accept.selected = false;
                    accept.initialized = false;
                }
            });
        };
        /**
         * Sets up this iframe form. This can be called when the Accept Billing form isn't yet rendered, it just won't do anything.
         */
        this.initForm = function() {
            // ensure that the SPCO js class is loaded
            if ( typeof this.spco === 'undefined' ) {
                this.handle_error(this.localization_strings.no_SPCO_error);
                return;
            }
            this.initialize_objects();
            if ( ! this.iframe.length ) {
                //if the iframe div doesn't exist on the page, it's because this payment method isn't selected
                //stop initializing the form
                this.selected = false;
                this.initialized = false;
                return;
            }
            this.selected = true;
            this.disable_SPCO_submit_buttons_if_Authnet_Accept_selected();
            // has the Authnet_Accept gateway been selected ? or already initialized?
            if ( this.initialized ) {
                //SPCO.console_log( 'initialize', 'already initialized!', true );
                return;
            }
            this.submitIFrameForm();
            this.initialized = true;
        };


        /**
         * Initializes the jquery objects taht get used
         * @function initialize_objects
         */
        this.initialize_objects = function() {
            this.iframe = $('#' + this.iframe_id);
            this.iframe_init_form = $('#' + this.iframe_init_form_id);
            this.first_name = $(this.first_name_id);
            this.last_name = $(this.last_name_id);
            this.address = $(this.address_id);
            this.city = $(this.city_id);
            this.state = $(this.state_id);
            this.country = $(this.country_id);
            this.zip = $(this.zip_id);
            this.phone = $(this.phone_id);
            this.authnet_response_code = $(this.authnet_response_code_id);
            this.authnet_trans_id = $(this.authnet_trans_id_id);
            this.authnet_amount = $(this.authnet_amount_id);
        };

        this.submitIFrameForm = function() {
            var iframe_init_html_in_input = $('#iframe_init_form_content');
            if( iframe_init_html_in_input.length > 0) {
                //set a listener to verify the iframe loads ok
                this.received_communication = false;
                var accept = this;
                accept.timeout = setTimeout(
                    function(){
                        accept.verifyIframeLoaded();
                    },
                    this.localization_strings.authnet_iframe_timeout * 1000
                );
                this.spco.do_before_sending_ajax();

                var content = iframe_init_html_in_input.val();
                $('form:first').parent().append(content);

                this.iframe_init_form = $('#authnet_accept_iframe_init_form');
                this.iframe_init_form.submit();
                this.iframe_init_form.remove();
                this.iframe.show();

            } else {
                this.handle_error(this.localization_strings.iframe_init_form_submit_error);
            }
        };

        /**
         * Verifies that the iframe was successfully loaded. When it gets loaded, the
         * iframe's iframe communicator page calls this class' onReceiveCommunication() method,
         * where we record communication was received. If we don't get that message by the time
         * this method gets called, it's pretty safe to assume there was a problem loading the iframe.
         */
        this.verifyIframeLoaded = function() {
            if ( ! this.received_communication ) {
                this.handle_error(this.localization_strings.no_response_from_authnet);
            }
        };





        /**
         * @function checkout_success
         * @param int accept_response_code
         * @param  int trans_id
         * @param string payment_amount
         */
        this.checkout_success = function( accept_response_code, trans_id, payment_amount ) {
            this.spco.enable_submit_buttons();
            if ( typeof trans_id !== 'undefined' && trans_id ) {
                this.payment_complete = true;
                this.authnet_response_code.val(accept_response_code);
                this.authnet_trans_id.val(trans_id);
                this.authnet_amount.val(payment_amount);

                // trigger click event on SPCO "Proceed to Next Step" button
                this.iframe.parents( 'form:first' ).find( '.spco-next-step-btn' ).trigger( 'click' );
            }
        };

        /**
         * @function disable_SPCO_submit_buttons_if_Authnet_Accept_selected
         * Deactivate SPCO submit buttons to prevent submitting with no Authnet_Accept token.
         */
        this.disable_SPCO_submit_buttons_if_Authnet_Accept_selected = function() {
            //console.log( JSON.stringify( '**EE_AUTHNET_ACCEPT.disable_SPCO_submit_buttons_if_Authnet_Accept_selected**', null, 4 ) );
            if ( this.selected && this.iframe.length > 0 ) {
                this.spco.allow_enable_submit_buttons = false;
                //console.log( JSON.stringify( 'EE_AUTHNET_ACCEPT >> disable_submit_buttons', null, 4 ) );
                this.spco.disable_submit_buttons();
            }
        };

        /**
         * If we get an error, show it to the user and log it
         * @param error_message
         */
        this.handle_error = function(error_message) {
            this.log_error(error_message);
            this.spco.scroll_to_top_and_display_messages(
                this.iframe,
                this.spco.generate_message_object( '', error_message, '' ),
                true
            );
        };

        /**
         * @function log_error
         * @param  {string} msg
         */
        this.log_error = function( msg ) {
            var accept = this;
            $.ajax({
                type : 'POST',
                url : eei18n.ajax_url,
                data : {
                    action : 'eea_authorizenet_accept_log_error',
                    txn_id : accept.data.txn_id,
                    message : msg
                },
                success: function (data, textstatus){
                    //that's great. remain silent
                },
                error: function( jwcht, textstatus, errorThrown){
                    accept.spco.scroll_to_top_and_display_messages(
                        accept.iframe,
                        accept.spco.generate_message_object( '', accept.localization_strings.error_logging_error + msg + '(' + textstatus + ' ' + errorThrown + ')', '' ),
                        true
                    );
                }
            });
        };

        /**
         * Utility function for getting info from the URL's querystring
         * @param str
         * @returns {Array}
         */
        this.parseQueryString = function (str) {
            var vars = [];
            var arr = str.split('&');
            var pair;
            for (var i = 0; i < arr.length; i++) {
                pair = arr[i].split('=');
                vars[pair[0]] = unescape(pair[1]);
            }
            return vars;
        };

        /**
         * This is data passed from the iframe communicator page (see eea-authnet-accept/iframe-communicator.php) which
         * is in an iframe inside authnet's iframe. It sends messages to this window's "accept" object by calling this
         * method.
         * @param argument
         */
        this.onReceiveCommunication = function (argument) {
            var params = this.parseQueryString(argument.qstr);
            this.received_communication=true;
            switch(params.action){
                case 'resizeWindow':
                    this.spco.end_ajax();
                    //alert('Received iframe size: height: ' + params.height + ', width: ' + params.width);
                    //if(params.height > this.iframe.height()) {
                        this.iframe.css('height', (parseInt(params.height) + 100).toString() + 'px');
                    //}
                    //if(params.width > this.iframe.width()) {
                        this.iframe.css('width', (parseInt(params.width) + 50).toString() + 'px');
                    //}
                    break;
                case "cancel" 			:
                    location.reload();
                    break;
                case "transactResponse"	:
                    var data = JSON.parse(params.response);
                    var responseCode = data.responseCode;
                    var transId = data.transId;
                    var totalAmount = data.totalAmount;
                    if( responseCode === '1' && typeof(transId) !== 'undefined') {
                        this.updateBillingInfo(data);
                        this.checkout_success(responseCode, transId, totalAmount);
                    }
                    break;
                default:
                    //alert(params.action);
                    break;

            }
        };

        /**
         * Inserts the billing data into the hidden inputs, so we can store it
         * @param authnet_data
         */
        this.updateBillingInfo = function(authnet_data) {
            var billing_data = authnet_data.billTo;
            if(typeof(billing_data.firstName)!== 'undefined') {
                this.first_name.val(billing_data.firstName);
            }
            if(typeof(billing_data.lastName)!== 'undefined') {
                this.last_name.val(billing_data.lastName);
            }
            if(typeof(billing_data.address)!== 'undefined') {
                this.address.val(billing_data.address);
            }
            if(typeof(billing_data.city)!== 'undefined') {
                this.city.val(billing_data.city);
            }
            if(typeof(billing_data.state)!== 'undefined') {
                this.state.val(billing_data.state);
            }
            if(typeof(billing_data.country)!== 'undefined') {
                this.country.val(billing_data.country);
            }
            if(typeof(billing_data.zip)!== 'undefined') {
                this.zip.val(billing_data.zip);
            }
            if(typeof(billing_data.phoneNumber)!== 'undefined') {
                this.phone.val(billing_data.phoneNumber);
            }
        };



    }
    // end of EE_AUTHNET_ACCEPT object
    // also initialize Authnet_Accept Checkout if the page just happens to load on the "payment_options" step with Authnet_Accept already selected!
    for (var slug in ee_form_section_vars.authnet_accept) {
        var vars = ee_form_section_vars.authnet_accept[slug];
        var accept = new EeAuthnetAccept(slug, SPCO, vars.translations, vars.data );
        window.accept = accept;
        accept.initialize();
    }
});
