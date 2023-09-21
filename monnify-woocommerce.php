<?php
/**
 * Plugin Name: Monnify WooCommerce Payment Gateway
 * Plugin URI: https://monnify.com
 * Description: Monnify Woocommerce Payment Plugin allows you to integrate Monnify Payment to your WordPress Website. Supports various Monnify payment method options such as Pay with Transfer, Pay with Card, Pay with USSD, Pay with Phone Number.
 * Author: Seye Folajimi
 * Author URI: http://jimiejosh.com
 * Version: 1.0.1
 * Text Domain: monnify-woocommerce
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt

 */

if (!defined('ABSPATH') ) {
    exit;
}

define("WC_MONNIFY_VERSION", "1.0.1");
define('WC_MONNIFY_MAIN_FILE', __FILE__);
define('WC_MONNIFY_URL', untrailingslashit(plugins_url('/', __FILE__)));

// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'monnify_woocommerce_notice');
    return;
}

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter('woocommerce_payment_gateways', 'monnify_add_gateway_class');


function monnify_add_gateway_class($gateways)
{
    $gateways[] = "WC_Monnify_Gateway";
    return $gateways;
}

function monnify_woocommerce_notice()
{
    echo '<div class="error"><p><strong>Monnify WooCommerce Payment Gateway requires WooCommerce to be installed and active.</strong></p></div>';



    $plugin_config = get_option('woocommerce_monnify_settings');
 
    if (  isset($plugin_config['testmode']) ? (($plugin_config['testmode'] === 'yes')? true: false) : false ) {
        echo '<div class="error"><p>' . 
        sprintf(__('Monnify Woocommerce is on Test mode, goto <strong> <a href="%s">Plugin Setting</a></strong> to
            disable Test mode to start accepting live payment on your website.', 'monnify-woocommerce'), esc_url(
            admin_url('admin.php?page=wc-settings&tab=checkout&section=monnify')
        )) . '</p></div>';
    }
}

function monnify_validate_installed_woocommerce()
{
    if (!class_exists('WooCommerce') || !in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_action('admin_notices', 'monnify_no_woocommerce_notice');
        return;
    }
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'monnify_add_links_to_plugin_page');



/*
 * The class itself 
 */
function woo_monnify_init_gateway_class()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    add_action('admin_init', 'monnify_validate_installed_woocommerce');

    require_once dirname( __FILE__ ) . '/includes/class-monnify-woocommerce.php';


}


add_action('plugins_loaded', 'woo_monnify_init_gateway_class');

function monnify_add_links_to_plugin_page($links)
{
    $settings_link = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wc-settings&tab=checkout&section=monnify')) . '">' . __('Settings', 'monnify-woocommerce') . '</a>';
    $documentation_link = '<a href="https://developers.monnify.com" target="_blank">' . __('Documentation', 'monnify-woocommerce') . '</a>';
    array_push($links, $settings_link, $documentation_link);
    return $links;
}









