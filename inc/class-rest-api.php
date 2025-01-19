<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Duplicate_Title_Validate_REST_API {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_rest_api']);
    }

    /**
     * Register REST API endpoint for checking duplicate titles
     */
    public function register_rest_api() {
        register_rest_route('duplicate-title-validate/v1', '/check-titles', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_rest_api_request'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }

    /**
     * Handle REST API request
     */
    public function handle_rest_api_request(WP_REST_Request $request) {
        global $wpdb;
        $title = sanitize_text_field($request->get_param('title'));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_title FROM $wpdb->posts 
             WHERE post_status = 'publish' AND post_title LIKE %s",
            '%' . $wpdb->esc_like($title) . '%'
        ));

        $titles = array_map(function ($result) {
            return $result->post_title;
        }, $results);

        return $titles;
    }
}