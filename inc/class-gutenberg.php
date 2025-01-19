<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Duplicate_Title_Validate_Gutenberg {

    private $title_checker;

    // Constructor
    public function __construct() {
        $this->title_checker = new Duplicate_Title_Validate_Title_Checker();
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_gutenberg_assets']);
        add_action('rest_api_init', [$this, 'register_rest_endpoint']);
        add_action('add_meta_boxes', [$this, 'add_duplicate_title_meta_box']);
    }

    /**
     * Enqueue Gutenberg assets
     */
    public function enqueue_gutenberg_assets() {
        wp_enqueue_script(
            'duplicate-title-validate-gutenberg',
            plugin_dir_url(dirname(__FILE__)) . 'js/gutenberg-duplicate-titles.js',
            ['wp-element', 'wp-data', 'wp-editor', 'wp-plugins', 'wp-i18n', 'wp-api-fetch', 'wp-plugins'],
            '1.0',
            true
        );
    }

    /**
     * Register REST API endpoints
     */
    public function register_rest_endpoint() {
        register_rest_route('duplicate-title-validate/v1', '/check-duplicate', [
            'methods'  => 'POST',
            'callback' => [$this, 'check_duplicate_title'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);

        register_rest_route('duplicate-title-validate/v1', '/get-matching-titles', [
            'methods'  => 'POST',
            'callback' => [$this, 'get_matching_titles'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }

    /**
     * Check for duplicate titles
     */
    public function check_duplicate_title(WP_REST_Request $request) {
        $title = $request->get_param('title');
        $post_id = $request->get_param('post_id');

        if (empty($title)) {
            return new WP_Error('empty_title', __('Title is empty.', 'duplicate-title-validate'), ['status' => 400]);
        }

        $title = trim($title);
        $duplicate_sources = $this->title_checker->check_duplicates($title, $post_id);

        if ($duplicate_sources === false) {
            return ['is_duplicate' => false];
        }

        $combined_sources = implode(', ', $duplicate_sources);
        $message = sprintf(__('Duplicate title detected in: %s. You can still publish the post.', 'duplicate-title-validate'), $combined_sources);

        return [
            'is_duplicate' => true,
            'message' => $message,
        ];
    }

    /**
     * Get matching titles
     */
    public function get_matching_titles(WP_REST_Request $request) {
        $title = $request->get_param('title');

        if (empty($title)) {
            return new WP_Error('empty_title', __('Title is empty.', 'duplicate-title-validate'), ['status' => 400]);
        }
         
        $titles = $this->title_checker->get_matching_titles($title);

        return [
            'is_duplicate' => !empty($titles),
            'titles' => $titles,
            'message' => __('Duplicate titles found:', 'duplicate-title-validate'), 
            'no_duplicate_message' => __('No duplicate titles found.', 'duplicate-title-validate'), 
        ];
    }

    /**
     * Add meta box to check duplicate titles
     */
    public function add_duplicate_title_meta_box() {
        add_meta_box(
            'duplicate_title_meta_box',
            __('Duplicate Title Checker', 'duplicate-title-validate'),
            [$this, 'render_duplicate_title_meta_box'],
            ['post', 'page', 'product'],
            'normal',
            'high'
        );
    }

    /**
     * Render the meta box content
     */
    public function render_duplicate_title_meta_box($post) {
        echo '<div id="duplicate-title-checker-result"></div>';
    }
}