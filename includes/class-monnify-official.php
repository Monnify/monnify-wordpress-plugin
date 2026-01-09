<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Monnify_Gateway extends WC_Payment_Gateway {
    /**
     * Monnify testmode.
     *
     * @var string
     */
    public $monnify_test_mode; // Flag to indicate whether the gateway is in test mode or not.

    /**
     * Monnify public key for production.
     *
     * @var string
     */
    public $monnify_public_key; // Public key for the Monnify production environment.

    /**
     * Monnify public key for testing.
     *
     * @var string
     */
    public $monnify_public_key_test; // Public key for the Monnify test environment.

    /**
     * Monnify secret key for production.
     *
     * @var string
     */
    public $monnify_secret_key; // Secret key for the Monnify production environment.

    /**
     * Monnify secret key for testing.
     *
     * @var string
     */
    public $monnify_secret_key_test; // Secret key for the Monnify test environment.

    /**
     * Monnify contract code for production.
     *
     * @var string
     */
    public $monnify_contract_code; // Contract code for the Monnify production environment.

    /**
     * Monnify contract code for testing.
     *
     * @var string
     */
    public $monnify_contract_code_test; // Contract code for the Monnify test environment.

    


    /**
     * Constructor for the payment gateway.
     */
    public function __construct()
    {
        $this->id = 'monnify'; 
        $this->has_fields = true; // If you need a custom creditcard form, set it to true
        $this->method_title =  __('Monnify Woocommerce Payment', 'monnify-official');
        // translators: %1$s is a link to the Monnify website, %2$s is a link to the login page for the Monnify dashboard.
        $this->method_description = sprintf(__('Monnify Woocommerce Payment Plugin allows you to integrate <a href="%1$s" target="_blank">Monnify Payments</a> to your WordPress Website. Supports various Monnify payment method options such as Pay with Transfer, Card, USSD, or Phone Number. <a href="%2$s" target="_blank">Click here to get your API keys</a>.','monnify-official'),esc_url('https://monnify.com'),esc_url('https://app.monnify.com/login'));
        
        $this->supports = array(
            'products',
            'tokenization',
            'subscriptions',
            'multiple_subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',

        );
        
        // Load the settings.
        $this->init_form_fields();

        $this->init_settings(); 

        // Define your settings
        $this->title            = $this->get_option('title');
        $this->description      = $this->get_option('description');
        $this->enabled          = $this->get_option('enabled');
        $this->monnify_test_mode         = $this->get_option('monnify_test_mode') === 'yes' ? true : false;

        // Apikeys
        $this->monnify_public_key              = $this->get_option('monnify_public_key');
        $this->monnify_public_key_test         = $this->get_option('monnify_public_key_test');
        $this->monnify_secret_key              = $this->get_option('monnify_secret_key');
        $this->monnify_secret_key_test         = $this->get_option('monnify_secret_key_test');
        $this->monnify_contract_code           = $this->get_option('monnify_contract_code');
        $this->monnify_contract_code_test      = $this->get_option('monnify_contract_code_test');

        // monnify endpoints
            $this->monnify_endpoint          = $this->monnify_test_mode ? "https://sandbox.monnify.com" : "https://api.monnify.com";


        // main config
        $this->monnify_api_key     = $this->monnify_test_mode ? $this->monnify_public_key_test : $this->monnify_public_key;
        $this->monnify_secret      = $this->monnify_test_mode ? $this->monnify_secret_key_test : $this->monnify_secret_key ;
        $this->monnify_contract    = $this->monnify_test_mode ? $this->monnify_contract_code_test : $this->monnify_contract_code ;


        // Hooks
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        add_action('woocommerce_available_payment_gateways', array($this, 'add_gateway_to_checkout'));
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action('woocommerce_api_wc_monnify_gateway', array($this, 'handle_webhook'));
    }

    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => 'Enable/Disable',
                'label'       => 'Enable Monnify Woocommerce',
                'type'        => 'checkbox',
                'description' => 'Enable or disable the Monnify Woocommerce payment gateway.',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'Pay securely using Monnify (cards, USSD, Bank Transfer etc. )',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default'     => 'Pay securely using Monnify Payment.',
            ),
            'webhook_url' => array(
                'title'       => 'Webhook URL',
                'type'        => 'text',
                'description' => sprintf(
                    // translators: %s is the link to the Monnify dashboard
                    __('Copy this URL and set it as your webhook endpoint for Transaction Completion on your <a href="%s" target="_blank">Monnify dashboard</a>.', 'monnify-official'),
                    'https://app.monnify.com'
                ),
                'default'     => $this->get_webhook_url(),
                'custom_attributes' => array(
                    'readonly' => 'readonly',
                    'id'       => 'monnify-webhook-url'
                ),
            ),
            'monnify_test_mode' => array(
                'title'       => 'Test Mode',
                'label'       => 'Enable Test Mode',
                'type'        => 'checkbox',
                'description' => 'Enable test mode for testing purposes.',
                'default'     => 'no',
            ),

            // test details
            'monnify_public_key_test' => array(
                'title'       => 'Test API Key on Sandbox Enviroment',
                'type'        => 'text',
                'description' => 'Enter your Monnify Test API key.',
                'default'     => '',

            ),
            'monnify_secret_key_test' => array(
                'title'       => 'Test Secret key on Sandbox Enviroment',
                'type'        => 'text',
                'description' => 'Enter your Monnify Secret Key.',
                'default'     => '',

            ),
            'monnify_contract_code_test' => array(
                'title'       => 'Test Contract Code on Sandbox Enviroment',
                'type'        => 'text',
                'description' => 'Enter your Monnify Test Contract code.',
                'default'     => '',

            ),

            // test details
            'monnify_public_key' => array(
                'title'       => 'Live API Key',
                'type'        => 'text',
                'description' => 'Enter your Live Monnify API key.',
                'default'     => '',

            ),
            'monnify_secret_key' => array(
                'title'       => 'Live Secret Key',
                'type'        => 'text',
                'description' => 'Enter your Live Monnify Secret Key.',
                'default'     => '',

            ),
            'monnify_contract_code' => array(
                'title'       => 'Live Contract Code',
                'type'        => 'text',
                'description' => 'Enter your Live Monnify contract code.',
                'default'     => '',

            ),
            'payment_methods' => array(
                'title'       => 'Supported Payment Methods',
                'type'        => 'multiselect',
                'description' => 'Select the payment methods you want to support. Hold "SHIFT" to select multiple. Works only in Live Mode.',
                'default'     => array('CARD', 'ACCOUNT_TRANSFER', 'USSD', 'PHONE_NUMBER'),
                'options'     => array(
                    'CARD'            => 'Card',
                    'ACCOUNT_TRANSFER' => 'Account Transfer',
                    'USSD'            => 'USSD',
                    'PHONE_NUMBER'    => 'Phone Number',
                ),
            ),
        );
    }

    /**
     * Output the payment gateway to the checkout page.
     *
     * @param array $gateways The available payment gateways.
     * @return array The updated list of payment gateways.
     */
    public function add_gateway_to_checkout($gateways) {
        if ('yes' === $this->enabled) {
            $gateways[$this->id] = $this;
        }
        return $gateways;
    }
   
    /*
    * Process the payment and return the result
    */
    public function process_payment($order_id)
    {
        // ignore nonce check
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( is_user_logged_in() && isset( $_POST[ 'wc-' . $this->id . '-new-payment-method' ] ) && true === (bool) $_POST[ 'wc-' . $this->id . '-new-payment-method' ] && $this->saved_cards ) {
            update_post_meta( $order_id, '_wc_monnify_save_card', true );
        }

        $order = wc_get_order( $order_id );

        return array(
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url( true ),
        );
    }
 
    /**
     * Payment scripts and styles
     */ 
    public function payment_scripts() {
        
        // Add payment scripts and styles

        if (!is_checkout_pay_page() || $this->enabled === 'no') {
            return;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $order_key = isset($_GET['key']) ? sanitize_text_field(wp_unslash( $_GET['key'] )) : "";

        $order_id = absint(get_query_var('order-pay'));
        $order    = wc_get_order($order_id);
        // Generate a nonce for verification
        $nonce = wp_create_nonce('monnify_verify_payment_' . $order_id);


        $payment_method = method_exists($order, 'get_payment_method') ? $order->get_payment_method() : $order->payment_method;

        if ($this->id !== $payment_method) {
            return;
        }

        // Add custom payment scripts and styles here

        wp_enqueue_script('jquery');
        wp_enqueue_script('monnify', 'https://sdk.monnify.com/plugin/monnify.js', array('jquery'), WC_MONNIFY_VERSION, false);
        wp_enqueue_script('wc_monnify', plugins_url('assets/js/monnify.js', WC_MONNIFY_MAIN_FILE), array('jquery', 'monnify'), WC_MONNIFY_VERSION, false);

        $selected_payment_methods = $this->get_option('payment_methods', array(
            'CARD',
            'ACCOUNT_TRANSFER',
            'USSD',
            'PHONE_NUMBER',
        ));
    
        $monnify_params = array(
            'selectedPaymentMethods' => $selected_payment_methods,
            'key'              => $this->monnify_api_key,
            'contractCode'     => $this->monnify_contract,
            'monnify_test_mode'=> $this->monnify_test_mode,
            'mon_redirect_url'   => esc_url_raw( add_query_arg( array('monnify_order_id' => $order_id, 'monnify_nonce' => $nonce,), WC()->api_request_url( 'WC_Monnify_Gateway' ) ) ),
            'email'            => '',
            'amount'           => '',
            'txnref'           => '',
            'currency'         => '', 
            'bank_channel'      => in_array('ACCOUNT_TRANSFER', $selected_payment_methods),
            'card_channel'      => in_array('CARD', $selected_payment_methods),
            'ussd_channel'      => in_array('USSD', $selected_payment_methods),
            'phone_number_channel' => in_array('PHONE_NUMBER', $selected_payment_methods), 
            'first_name'       => '',
            'last_name'        => '',
            'phone'            => '', 
            'monnify_nonce'    => $nonce,
            'cart_url'         => wc_get_cart_url(),
        );

        if (is_checkout_pay_page() && get_query_var('order-pay') && $order->get_id() === $order_id && $order->get_order_key() === $order_key) {
            $monnify_params['email']        = method_exists($order, 'get_billing_email') ? $order->get_billing_email() : $order->billing_email;
            $monnify_params['amount']       = $order->get_total();
            $monnify_params['txnref']       = "MNFY_WP_{$order_id}_" . time() . '_' . wp_rand(1000, 9999);
            $monnify_params['currency']     = method_exists($order, 'get_currency') ? $order->get_currency() : $order->order_currency;
            $monnify_params['first_name']   = $order->get_billing_first_name();
            $monnify_params['last_name']    = $order->get_billing_last_name();
            $monnify_params['phone']        = $order->get_billing_phone();
        }

        // update_post_meta($order_id, '_monnify_txn_ref', $monnify_params['txnref']);
        $existing_refs = get_post_meta($order_id, '_monnify_txn_refs', true);
        if (!is_array($existing_refs)) {
            $existing_refs = [];
        }
        $existing_refs[] = $monnify_params['txnref'];
        update_post_meta($order_id, '_monnify_txn_refs', $existing_refs);

        wp_localize_script('wc_monnify', 'woo_monnify_params', $monnify_params);
    }

    /**
     * Display Monnify payment icon.
     */
    public function get_icon()
    {
        $icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url('assets/images/monnify.png', WC_MONNIFY_MAIN_FILE) ) . '" alt="Monnify Payment Gateway"  />';

        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

    /**
     * Displays the payment page.
     *
     * @param $order_id
     */
    public function receipt_page($order_id)
    {
        $order = wc_get_order($order_id);

        echo '<div id="seye">' . esc_html__('Thank you for your order, please click the button below to pay with Monnify.', 'monnify-official') . '</div>';

        echo '<div id="monnify_form"><form id="order_review" method="post" action="' . esc_url(WC()->api_request_url('WC_Monnify_Gateway')) . '"></form><button class="button alt" id="wc-monnify-official-button">' . esc_html__('Pay Now', 'monnify-official') . '</button>';
    }

    /**
     * Webhook URL getter
     */
    public function get_webhook_url() {
        return add_query_arg('wc-api', 'wc_monnify_gateway', home_url('/'));
    }

    /**
     * Handle Monnify webhook notifications
     */
    public function handle_webhook() {
        $logger = wc_get_logger();
        $context = ['source' => 'monnify-webhook'];
        
        // Verify this is a webhook call
        if (!isset($_SERVER['HTTP_MONNIFY_SIGNATURE'])) {
            $logger->info('Non-webhook request received', $context);
            $this->monnify_trans_verify_payment(); // Fallback to original verification
            return;
        }

        try {
            // IP Whitelist - Verify IP address against monnify IP  35.242.133.146
            $ip = '';
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
                $logger->info('REMOTE_ADDR - ' . $ip , $context);
            } elseif (isset($_SERVER['REMOTE_HOST'])) {
                $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_HOST']));
                $logger->info('REMOTE_HOST - ' . $ip , $context);
            }
            
            if( $ip != "35.242.133.146") throw new Exception('Webhook validation failed, invalid source IP address');
            
            // Get and validate payload
            $payload = file_get_contents('php://input');
            $signature = sanitize_text_field(wp_unslash($_SERVER['HTTP_MONNIFY_SIGNATURE']));
            
            if (!$this->validate_webhook($payload, $signature)) {
                throw new Exception('Webhook validation failed, invalid signature');
            }
            
            $data = json_decode($payload, true);
            $event_type = $data['eventType'] ?? '';
            $event_data = $data['eventData'] ?? [];
            
            // Process based on event type
            switch ($event_type) {
                case 'SUCCESSFUL_TRANSACTION':
                    $this->process_successful_webhook($event_data);
                    break;
                    
                default:
                    $logger->warning("Unhandled webhook event: $event_type", $context);
                    break;
            }
            
            // Return success response
            http_response_code(200);
            exit;
            
        } catch (Exception $e) {
            $logger->error('Webhook processing failed: ' . $e->getMessage(), $context);
            http_response_code(400);
            exit;
        }
    }

    /**
     * Validate webhook signature
     */
    private function validate_webhook($payload, $signature) {
        $computed_signature = hash_hmac('sha512', $payload, $this->monnify_secret);
        return hash_equals($computed_signature, $signature);
    }

    /**
     * Process successful payment webhook
     */
    private function process_successful_webhook($data) {
        $logger = wc_get_logger();
        $context = ['source' => 'monnify-webhook'];
        
        $transaction_reference = $data['transactionReference'] ?? '';
        $payment_reference = $data['paymentReference'] ?? '';
        $amount_paid = $data['amountPaid'] ?? 0;
        
        // Find order by reference
        $order_id = $this->get_order_id_by_reference($payment_reference);
        $logger->info("Processing webhook notification for Order with reference: $payment_reference and transaction reference: $transaction_reference", $context);

        if (!$order_id) {
            throw new Exception('Order not found for reference: ' . esc_html($payment_reference));
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception('Invalid order ID: ' . esc_html($order_id));
        }
        
        // Check if already processed
        if (strtolower($order->get_status()) === 'completed') {
            $logger->info("Order #$order_id already completed", $context);
            return;
        }
        
        // Verify amount
        $order_amount = $order->get_total();
        
        if ($amount_paid < $order_amount) {
            $logger->info('Partial payment received. Paid: ' . $amount_paid . ', Expected: ' . $order_amount . '. Reference: ' . $transaction_reference, $context);
            $message = sprintf(
                // translators: %1$s is the paid amount, %2$s is order amount
                __('Partial payment received. Paid: %1$s, Expected: %2$s', 'monnify-official'),
                wc_price($amount_paid),
                wc_price($order_amount)
            );
            $order->update_status('on-hold', $message);
            $order->add_order_note($message);
            return;
        }
        elseif ($amount_paid > $order_amount) {
            $logger->info('Over-payment received. Paid: ' . $amount_paid . ', Expected: ' . $order_amount . '. Reference: ' . $transaction_reference, $context);
            $message = sprintf(
                // translators: %1$s is the paid amount, %2$s is order amount
                __('Over-payment received. Paid: %1$s, Expected: %2$s', 'monnify-official'),
                wc_price($amount_paid),
                wc_price($order_amount)
            );
            $order->add_order_note($message);
        }

        // Complete payment
        $this->complete_order($order, $transaction_reference);
        
        $logger->info("Successfully processed payment for order #$order_id", $context);
    }

    /**
     * Find WooCommerce order by Monnify reference
     */
    private function get_order_id_by_reference($reference) {
        // Extract order ID from reference, expected format MNFY_WP_{order_id}_{timestamp}
        $parts = explode('_', $reference);
        return absint($parts[2]);
    }

    /**
     * Verify Monnify payment and update order status
     */
    public function monnify_trans_verify_payment() {
        $logger = wc_get_logger();
        $context = ['source' => 'monnify-payment'];
        
        try {
            // 1. Validate and sanitize input parameters
            if (!isset($_GET['mnfy_reference']) || empty($_GET['mnfy_reference'])) {
                throw new Exception('Missing Monnify transaction reference');
            }

            if (!isset($_GET['monnify_order_id']) || empty($_GET['monnify_order_id'])) {
                throw new Exception('Missing order ID');
            }

            if(!isset($_GET['monnify_nonce']) || empty($_GET['monnify_nonce'])) {
                throw new Exception('Missing nonce');
            }

            $mnfy_reference = sanitize_text_field(wp_unslash($_GET['mnfy_reference']));
            $order_id = absint(sanitize_text_field(wp_unslash($_GET['monnify_order_id'])));
            $order = wc_get_order($order_id);
            $nonce = sanitize_text_field(wp_unslash($_GET['monnify_nonce']));

            if (!$order) {
                throw new Exception('Invalid order ID: ' . $order_id);
            }

            if (!wp_verify_nonce($nonce, 'monnify_verify_payment_' . $order_id)) {
                throw new Exception('Security check failed - invalid nonce');
            }
                  
            if ($mnfy_reference === 'undefined') {
                throw new Exception('Missing Monnify transaction reference');
            }

            $logger->info("Starting verification for order #$order_id, reference: $mnfy_reference", $context);

            // Check if already processed
            if (strtolower($order->get_status()) === 'completed') {
                $logger->info("Order #$order_id already completed", $context);
                wp_redirect($this->get_return_url($order));
                exit;
            }

            // 2. Get authentication token from Monnify
            $auth_token = $this->get_monnify_auth_token();
            if (!$auth_token) {
                throw new Exception('Failed to get Monnify authentication token');
            }

            // 3. Verify transaction status with Monnify
            $transaction_response = $this->verify_monnify_transaction($mnfy_reference, $auth_token);
            if (!$transaction_response) {
                throw new Exception('Failed to verify transaction status');
            }

            // 3.5 Does this payment reference exist for this order??
            $payment_references = get_post_meta($order_id, '_monnify_txn_refs', true);
            $payment_reference = $transaction_response->responseBody->paymentReference;
            if (!is_array($payment_references) || !in_array($payment_reference, $payment_references, true) || $this->get_order_id_by_reference($payment_reference) !== $order_id) {
                throw new Exception('This payment reference is not for this order');
            }

            // 4. Handle payment status
            $this->handle_payment_status($order, $mnfy_reference, $transaction_response);

            // 5. Redirect to thank you page
            wp_redirect($this->get_return_url($order));
            exit;

        } catch (Exception $e) {
            $logger->error('Verification failed: ' . $e->getMessage(), $context);
            
            wc_add_notice(__('Payment verification failed. Please contact support if your account was debited and your order status is not updated in a few minutes.', 'monnify-official'), 'error');
            wp_redirect(wc_get_checkout_url());
            exit;
        }
    }

    /**
     * Get authentication token from Monnify
     */
    private function get_monnify_auth_token() {
        $logger = wc_get_logger();
        $context = ['source' => 'monnify-payment'];
        
        $monnify_login_url = $this->monnify_endpoint . '/api/v1/auth/login';
        $auth_string = base64_encode($this->monnify_api_key . ":" . $this->monnify_secret);

        $args = [
            'headers' => [
                'Authorization' => 'Basic ' . $auth_string,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 60
        ];

        $response = wp_remote_post($monnify_login_url, $args);

        if (is_wp_error($response)) {
            $logger->error('Auth token request failed: ' . $response->get_error_message(), $context);
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $logger->error("Auth token request failed with code $response_code", $context);
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));
        return $body->responseBody->accessToken ?? false;
    }

    /**
     * Verify transaction with Monnify API
     */
    private function verify_monnify_transaction($transaction_reference, $auth_token) {
        $logger = wc_get_logger();
        $context = ['source' => 'monnify-payment'];

        $url = $this->monnify_endpoint . '/api/v2/transactions/' . urlencode($transaction_reference);
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $auth_token,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 60
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $logger->error('Transaction verification failed: ' . $response->get_error_message(), $context);
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));
        return $body ?? false;
    }

    /**
     * Handle payment status based on Monnify response
     */
    private function handle_payment_status($order, $transaction_reference, $transaction_response) {
        $logger = wc_get_logger();
        $context = ['source' => 'monnify-payment'];
        $transaction = $transaction_response->responseBody;
        $payment_status = $transaction->paymentStatus ?? 'UNKNOWN';
        $order_id = $order->get_id();

        $logger->info("Processing payment for order #$order_id", $context);

        switch (strtoupper($payment_status)) {
            case 'PAID':
            case 'OVERPAID':
            case 'PARTIALLY_PAID':
                // Verify amount matches order total
                $order_amount = $order->get_total();
                $paid_amount = $transaction->amountPaid ?? 0;
                
                if ($paid_amount < $order_amount) {
                    $logger->info('Partial payment received. Paid: ' . $paid_amount . ', Expected: ' . $order_amount . '. Reference: ' . $transaction_reference, $context);
                    $message = sprintf(
                        // translators: %1$s is the paid amount, %2$s is order amount,  %3$s is the monnify transaction reference
                        __('Partial payment received. Paid: %1$s, Expected: %2$s. Reference: %3$s', 'monnify-official'),
                        wc_price($paid_amount),
                        wc_price($order_amount),
                        $transaction_reference
                    );
                    $order->update_status('on-hold', $message);
                    $order->add_order_note($message);
                    break;
                }
                elseif ($paid_amount > $order_amount) {
                    $logger->info('Over-payment received. Paid: ' . $paid_amount . ', Expected: ' . $order_amount . '. Reference: ' . $transaction_reference, $context);
                    $message = sprintf(
                        // translators: %1$s is the paid amount, %2$s is order amount,  %3$s is the monnify transaction reference
                        __('Over-payment received. Paid: %1$s, Expected: %2$s. Reference: %3$s', 'monnify-official'),
                        wc_price($paid_amount),
                        wc_price($order_amount),
                        $transaction_reference
                    );
                    $order->add_order_note($message);
                }

                // Complete payment
                $this->complete_order($order, $transaction_reference);
                break;

            case 'PENDING':
                $logger->info('Transaction status still pending skipping', $context);
                break;

            default:
                $logger->warning("Payment not completed, current payment status from Monnify (Order ID: $order_id, Reference: $transaction_reference, Status: $payment_status)", $context);
                break;
        }
    }

    private function complete_order( $order, $transaction_reference) {
        // Complete payment
        $order->payment_complete($transaction_reference);
        $order->update_status('completed');
        
        // Add order notes
        $order->add_order_note(sprintf(
            // translators: %s is the Monnify transaction reference
            __('Payment via Monnify successful (Reference: %s)', 'monnify-official'),
            $transaction_reference
        ));
        
        $customer_note = __('Thank you for your order. Your payment was successful and we are processing your order.', 'monnify-official');
        $order->add_order_note($customer_note, 1);
        
        // Clear cart
        if (is_user_logged_in()) {
            WC()->cart->empty_cart();
        }        
    }
}
 
