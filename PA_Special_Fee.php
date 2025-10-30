<?php
/*
Plugin Name: Shipping States - Pennsylvania Fee
Plugin URI: http://imran1.com
Description: Requires Pennsylvania residents to specify a liquor store for shipping and applies a surcharge.
Version: 1.1.0
Author: Imran Khan Amy
Author URI: http://imran1.com
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Ensure WooCommerce is active
add_action('plugins_loaded', function () {
    if (class_exists('WooCommerce')) {
        new PA_Special_Fee();
    }
});

class PA_Special_Fee {

    public function __construct() {
        // Add checkout field
        add_action('woocommerce_before_order_notes', [$this, 'display_pa_checkout_field']);

        // Validate field
        add_action('woocommerce_checkout_process', [$this, 'validate_pa_checkout_field']);

        // Save field to order meta
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_pa_checkout_field']);

        // Display in admin order
        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'display_admin_pa_field'], 10, 1);

        // Add surcharge
        add_action('woocommerce_cart_calculate_fees', [$this, 'add_pa_surcharge']);

        // Enqueue JS
        add_action('wp_enqueue_scripts', [$this, 'enqueue_checkout_script']);
    }

    public function display_pa_checkout_field($checkout) {
        echo '<div id="pa_shipping_notice" style="display:none;">';
        echo '<p><strong style="color:#990000; font-size:16px;">Attention Pennsylvania Residents:</strong></p>';
        echo '<p>The state of Pennsylvania requires us to ship your order to your nearest liquor store. Please enter the store ID below. <a href="http://www.lcbapps.lcb.state.pa.us/app/Retail/storeloc.asp" target="_blank" rel="noopener">Find your local store number</a>.</p>';

        woocommerce_form_field('pennsylvania_shipping', [
            'type'        => 'text',
            'class'       => ['form-row-wide'],
            'label'       => __('PA Liquor Store Number'),
            'placeholder' => __('Enter Liquor Store Number'),
            'required'    => true,
        ], $checkout->get_value('pennsylvania_shipping'));

        echo '<p><strong>By placing your order, you agree to the <a href="https://www.thekeywestwinery.com/privacy-policy/" target="_blank">Special Terms & Fees</a> for PA residents.</strong></p>';
        echo '</div>';
    }

    public function validate_pa_checkout_field() {
        if ( isset($_POST['billing_state']) && strtoupper($_POST['billing_state']) === 'PA' &&  
                empty($_POST['pennsylvania_shipping'])        ) {
            wc_add_notice(__('<strong>Liquor Store Number</strong> is required for Pennsylvania residents.'), 'error');
        }
    }

    public function save_pa_checkout_field($order_id) {
        if (!empty($_POST['pennsylvania_shipping'])) {
            update_post_meta($order_id, '_pa_liquor_store_number', sanitize_text_field($_POST['pennsylvania_shipping']));
        }
    }

    public function display_admin_pa_field($order) {
        $store_number = get_post_meta($order->get_id(), '_pa_liquor_store_number', true);
        if ($store_number) {
            echo '<p><strong>' . esc_html__('PA Liquor Store Number') . ':</strong> ' . esc_html($store_number) . '</p>';
        }
    }

    public function add_pa_surcharge() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $customer = WC()->customer;

        if ($customer && $customer->get_shipping_state() === 'PA') {
            WC()->cart->add_fee(__('PA Shipping Surcharge'), 4.50, true);
        }
    }

    public function enqueue_checkout_script() {
        if (is_checkout()) {
            wp_enqueue_script('pa-shipping-checkout', plugin_dir_url(__FILE__) . 'assets/js/pa-shipping.js', ['jquery'], '1.0.0', true);
        }
    }
}
