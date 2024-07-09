<?php
/*
Plugin Name: Xendit Checkout Integration
Description: A custom plugin to integrate Xendit checkout in WordPress.
Version: 1.0
Author: Josephine Anastasya Christabel
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('plugins_loaded', 'xendit_checkout_init');

function xendit_checkout_init() {
    add_shortcode('xendit_checkout', 'xendit_checkout_form');
}

function xendit_checkout_form() {
    ob_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = xendit_create_invoice_request($_POST);
        if ($response) {
            $invoice_url = $response->invoice_url;
            echo "<a href='$invoice_url' target='_blank'>Pay Now</a>";
        } else {
            echo "Failed to create invoice.";
        }
    }

    ?>
    <form method="POST">
        <input type="text" name="customer_name" placeholder="Customer Name" required />
        <input type="email" name="customer_email" placeholder="Customer Email" required />
        <input type="text" name="customer_phone" placeholder="Customer Phone" required />
        <input type="number" name="amount" placeholder="Amount" required />
        <button type="submit">Create Invoice</button>
    </form>
    <?php

    return ob_get_clean();
}

function xendit_create_invoice_request($data) {
    $api_key = 'xnd_development_j7cGCDIbhPrnFYLJmHBlj6tUFThkjKDMrbeWYDYODScrx1LvSl7Blm69VXtTQlq';
    $url = 'https://api.xendit.co/v2/invoices';

    $payload = json_encode([
        'external_id' => 'xendit_test_id_1_' . time(),
        'amount' => $data['amount'],
        'currency' => 'IDR',
        'customer' => [
            'given_names' => $data['customer_name'],
            'email' => $data['customer_email'],
            'mobile_number' => $data['customer_phone']
        ],
        'customer_notification_preference' => [
            'invoice_paid' => ['email', 'whatsapp']
        ],
        'success_redirect_url' => 'example.com/success',
        'failure_redirect_url' => 'example.com/failure',
    ]);

    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode("$api_key:"),
            'Content-Type' => 'application/json'
        ],
        'body' => $payload
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body);
}

// Include callback handler
require_once plugin_dir_path(__FILE__) . 'xendit-callback.php';
?>
