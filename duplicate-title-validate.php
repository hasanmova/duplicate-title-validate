<?php
/*
* Plugin Name: Duplicate Title Validate
* Description: Prevents publishing posts with duplicate titles and shows from which post types or taxonomies the duplicates occur. Works in both Classic Editor and Gutenberg.
* Version: 1.4
* Author: Hasan Movahed
* Text Domain: duplicate-title-validate
* Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Duplicate_Title_Validate {

    public function __construct() {
        // Load plugin text domain for translations
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        // Hooks
        add_filter('rest_pre_insert_post', [$this, 'check_duplicate_title_rest'], 10, 2);
        add_action('publish_post', [$this, 'check_duplicate_title_classic']);
        add_action('admin_notices', [$this, 'not_published_error_notice']);
        add_action('wp_print_scripts', [$this, 'disable_autosave']);
    }

    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain('duplicate-title-validate', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Check for duplicate title in Gutenberg (REST API)
     *
     * @param mixed $prepared_post The prepared post object or array.
     * @param WP_REST_Request $request The current request.
     * @return mixed WP_Error on duplicate, otherwise the prepared post.
     */
    public function check_duplicate_title_rest($prepared_post, $request) {
        // Verify user permissions
        if (!is_user_logged_in() || !current_user_can('edit_posts')) {
            return new WP_Error(
                'rest_forbidden',
                __('You are not allowed to do this.', 'duplicate-title-validate'),
                ['status' => 403]
            );
        }

        global $wpdb;
        $title = '';
        $post_id = 0;

        // Determine if $prepared_post is an object or array
        if (is_object($prepared_post)) {
            $title = !empty($prepared_post->post_title) ? sanitize_text_field($prepared_post->post_title) : '';
            $post_id = !empty($prepared_post->ID) ? (int)$prepared_post->ID : 0;
        } elseif (is_array($prepared_post)) {
            $title = isset($prepared_post['post_title']) ? sanitize_text_field($prepared_post['post_title']) : '';
            $post_id = !empty($prepared_post['ID']) ? (int)$prepared_post['ID'] : 0;
        }

        if (empty($title)) {
            return $prepared_post;
        }

        // Check for duplicate posts
        $post_results = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_type FROM $wpdb->posts 
             WHERE post_status = 'publish' AND post_title = %s AND ID != %d",
            $title, $post_id
        ));

        // Check for duplicate terms across all taxonomies
        $taxonomy_results = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT tt.taxonomy 
             FROM $wpdb->terms t
             INNER JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id
             WHERE t.name = %s",
            $title
        ));

        // If no duplicates in posts or taxonomies, return the post
        if (empty($post_results) && empty($taxonomy_results)) {
            return $prepared_post;
        }

        // Collect sources of duplicates
        $sources = [];

        // If there are duplicate posts
        if (!empty($post_results)) {
            $post_types_list = $this->get_post_type_labels($post_results);
            $sources[] = $post_types_list;
        }

        // If there are duplicate taxonomies
        if (!empty($taxonomy_results)) {
            $taxonomy_names = [];
            foreach ($taxonomy_results as $taxonomy) {
                $tax_obj = get_taxonomy($taxonomy->taxonomy);
                $tax_label = ($tax_obj && !empty($tax_obj->labels->singular_name)) ? $tax_obj->labels->singular_name : $taxonomy->taxonomy;
                $taxonomy_names[] = $tax_label;
            }
            $taxonomies_list = implode(', ', array_unique($taxonomy_names));
            $sources[] = $taxonomies_list;
        }

        $combined_sources = implode(', ', $sources);

        // Return WP_Error with detailed sources
        return new WP_Error(
            'duplicate_title_error',
            '-- ' . sprintf(__('Duplicate title detected in: %s. Please change the title.', 'duplicate-title-validate'), $combined_sources),
            ['status' => 400]
        );
    }

    /**
     * Check for duplicate title in Classic Editor
     *
     * @param int $post_id The ID of the post being published.
     */
    public function check_duplicate_title_classic($post_id) {
        if (!isset($_POST['post_title'])) {
            return;
        }

        global $wpdb;
        $title = sanitize_text_field($_POST['post_title']);

        // Check for duplicate posts
        $post_results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_title, post_type FROM $wpdb->posts 
             WHERE post_status = 'publish' AND post_title = %s AND ID != %d",
            $title, $post_id
        ));

        // Check for duplicate terms across all taxonomies
        $taxonomy_results = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT tt.taxonomy 
             FROM $wpdb->terms t
             INNER JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id
             WHERE t.name = %s",
            $title
        ));

        // If no duplicates found, do nothing
        if (empty($post_results) && empty($taxonomy_results)) {
            return;
        }

        // Collect sources of duplicates
        $sources = [];

        // If there are duplicate posts
        if (!empty($post_results)) {
            $post_types_list = $this->get_post_type_labels($post_results);
            $sources[] = $post_types_list;
        }

        // If there are duplicate taxonomies
        if (!empty($taxonomy_results)) {
            $taxonomy_names = [];
            foreach ($taxonomy_results as $taxonomy) {
                $tax_obj = get_taxonomy($taxonomy->taxonomy);
                $tax_label = ($tax_obj && !empty($tax_obj->labels->singular_name)) ? $tax_obj->labels->singular_name : $taxonomy->taxonomy;
                $taxonomy_names[] = $tax_label;
            }
            $taxonomies_list = implode(', ', array_unique($taxonomy_names));
            $sources[] = $taxonomies_list;
        }

        $combined_sources = implode(', ', $sources);

        // Prepare the error message
        $error_message = sprintf(
            __('Title used for this post appears to be a duplicate in: %s. Please modify the title.', 'duplicate-title-validate'),
            $combined_sources
        );

        // Update the post status to draft
        $wpdb->update($wpdb->posts, ['post_status' => 'draft'], ['ID' => $post_id]);

        // Redirect back to the edit page with error message
        $arr_params = ['message' => '10', 'wallfaerror' => '1', 'wallfaerror_msg' => urlencode($error_message)];
        $location = add_query_arg($arr_params, get_edit_post_link($post_id, 'url'));
        wp_redirect($location);
        exit;
    }

    /**
     * Display error message in Classic Editor
     */
    public function not_published_error_notice() {
        if (isset($_GET['wallfaerror']) && $_GET['wallfaerror'] == '1') {
            $msg = isset($_GET['wallfaerror_msg']) ? urldecode($_GET['wallfaerror_msg']) : __('Title used for this post appears to be a duplicate. Please modify the title.', 'duplicate-title-validate');
            echo '<div class="error"><p style="color:red">' . esc_html($msg) . '</p></div>';
        }
    }

    /**
     * Disable autosave
     */
    public function disable_autosave() {
        wp_deregister_script('autosave');
    }

    /**
     * Helper: Get post type labels for displaying in error messages
     *
     * @param array $results Array of post objects.
     * @return string Comma-separated list of post type labels.
     */
    protected function get_post_type_labels($results) {
        $types = [];
        foreach ($results as $r) {
            $pt_obj = get_post_type_object($r->post_type);
            $pt_label = ($pt_obj && !empty($pt_obj->labels->singular_name)) ? $pt_obj->labels->singular_name : $r->post_type;
            $types[] = $pt_label;
        }
        return implode(', ', array_unique($types));
    }
}

/**
 * Initialize the plugin
 */
function dtv_init() {
    new Duplicate_Title_Validate();
}
add_action('init', 'dtv_init');
