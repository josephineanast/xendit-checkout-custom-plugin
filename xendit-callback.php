<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_action('rest_api_init', function () {
    register_rest_route('xendit-checkout/v1', '/callback', [
        'methods' => 'POST',
        'callback' => 'xendit_handle_callback',
        'permission_callback' => '__return_true'
    ]);
});

function xendit_handle_callback(WP_REST_Request $request) {
    $body = $request->get_body();
    $data = json_decode($body, true);

    if ($data['status'] === 'PAID') {
        error_log("Invoice successfully paid with status {$data['status']} and id {$data['id']}");

        global $wpdb;
        $table_name = $wpdb->prefix . 'order';
        $wpdb->update(
            $table_name,
            array('status' => 'Lunas'),
            array('invoice' => $data['external_id'])
        );
    }

    return new WP_REST_Response(null, 200);
}