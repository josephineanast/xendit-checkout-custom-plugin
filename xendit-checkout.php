<?php
/*
Plugin Name: Xendit Checkout Integration
Description: A custom plugin to integrate Xendit checkout in WordPress.
Version: 1.0
Author: Josephine Anastasya Christabel
*/

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'xendit_checkout_init');

function xendit_checkout_init() {
    // Register REST API route
    add_action('rest_api_init', function () {
        register_rest_route('xendit-checkout/v1', '/create-invoice', array(
            'methods' => 'POST',
            'callback' => 'xendit_create_invoice_request',
            'permission_callback' => '__return_true'
        ));
    });

    // Enqueue custom API script
    add_action('wp_enqueue_scripts', function () {
        wp_enqueue_script('xendit-api', plugins_url('xendit-api.js', __FILE__), array('jquery'), '1.0', true);
    });
}

function xendit_create_invoice_request(WP_REST_Request $request) {
    $data = $request->get_params();

    $api_key = 'xnd_development_j7cGCDIbhPrnFYLJmHBlj6tUFThkjKDMrbeWYDYODScrx1LvSl7Blm69VXtTQlq';
    $url = 'https://api.xendit.co/v2/invoices';
    $payload = json_encode([
        'external_id' => 'xendit_test_id_1_' . time(),
        'amount' => $data['produk[hargatotal]'],
        'currency' => 'IDR',
        'customer' => [
            'given_names' => $data['nama'],
            'email' => $data['email'],
            'mobile_number' => $data['hp']
        ],
        'customer_notification_preference' => [
            'invoice_paid' => ['email', 'whatsapp']
        ],
        'success_redirect_url' => 'https://ptanugerahmegahkencana.com/keranjang/?action=finish',
        'failure_redirect_url' => 'https://ptanugerahmegahkencana.com/',
    ]);

    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode("$api_key:"),
            'Content-Type' => 'application/json'
        ],
        'body' => $payload
    ]);

    if (is_wp_error($response)) {
        return new WP_REST_Response(['error' => 'Failed to create invoice'], 500);
    }

    $body = wp_remote_retrieve_body($response);
    $invoice_data = json_decode($body);

    return new WP_REST_Response($invoice_data, 200);
}

// Include callback handler
require_once plugin_dir_path(__FILE__) . 'xendit-callback.php';