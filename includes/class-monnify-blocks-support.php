<?php
if (!defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class WC_Monnify_Blocks_Support extends AbstractPaymentMethodType {
    protected $name = 'monnify';
    protected $settings;

    protected $supports = array(
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
    

    public function initialize() {
        $this->settings = get_option('woocommerce_monnify_settings', []);
    }

    public function is_active() {
        return filter_var($this->settings['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function get_payment_method_script_handles() {
        wp_register_script(
            'wc-monnify-blocks',
            WC_MONNIFY_URL . '/assets/js/blocks.js',
            ['wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities', 'wp-i18n'],
            WC_MONNIFY_VERSION,
            true
        );
        wp_localize_script(
            'wc-monnify-blocks',
            'wc_monnify_params',
            array(
                'supports' => $this->supports,
                'logo_url' => plugins_url('assets/images/monnify.png', WC_MONNIFY_MAIN_FILE) 
                )
        );
        return ['wc-monnify-blocks'];
    }

    public function get_payment_method_data() {
        return [
            'title' => $this->settings['title'] ?? __('Monnify', 'monnify-official'),
            'description' => $this->settings['description'] ?? '',
            'supports' => $this->get_supported_features(),
            'logo_url' => WC_MONNIFY_URL . '/assets/images/monnify.png'
        ];
    }

    public function get_supported_features() { 
        return [
            'products'
        ];
    }
}