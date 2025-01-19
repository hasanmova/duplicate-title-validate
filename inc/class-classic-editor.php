<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Duplicate_Title_Validate_Classic_Editor {

    private $title_checker;

    public function __construct() {
        // Initialize the Title Checker
        $this->title_checker = new Duplicate_Title_Validate_Title_Checker();

        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_check_similar_titles', [$this, 'ajax_check_similar_titles']);
        add_filter('wp_insert_post_data', [$this, 'prevent_duplicate_title'], 10, 2);
        add_action('admin_notices', [$this, 'display_admin_notice']);
    }

    /**
     * Enqueue scripts and styles for Classic Editor
     */
    public function enqueue_scripts() {
        // Only load scripts on the post edit screen
        if (get_current_screen()->base === 'post') {
           
            wp_enqueue_script(
                'duplicate-title-validate-classic-editor',
                plugin_dir_url(dirname(__FILE__)) . 'js/duplicate-title-validate.js',
                ['jquery'],
                '1.0',
                true
            );

           
            wp_localize_script('duplicate-title-validate-classic-editor', 'dtv_ajax_object', [
                'ajaxurl'           => admin_url('admin-ajax.php'),
                'nonce'             => wp_create_nonce('dtv_nonce'),
                'similar_titles_label' => __('Similar Titles:', 'duplicate-title-validate')
            ]);
        }
    }

    /**
     * AJAX handler for checking similar titles
     */
    public function ajax_check_similar_titles() {
        // Verify nonce for security
        check_ajax_referer('dtv_nonce', 'nonce');

       
        $title = sanitize_text_field($_POST['title']);

        // Get similar titles
        $title = trim($title);
        $similar_titles = $this->title_checker->get_matching_titles($title);

        
        if ($similar_titles) {
            wp_send_json_success($similar_titles);
        } else {
            wp_send_json_success([]); // No duplicates found
        }
    }

    /**
     * Prevent publishing posts with duplicate titles unless allowed in settings
     */
    public function prevent_duplicate_title($data, $postarr) {
        // Skip if the post is not being published
        if ($data['post_status'] !== 'publish') {
            return $data;
        }

      
        if (!isset($postarr['post_title'])) {
            return $data;
        }

        // Get the "Allow Duplicate Titles" option from settings
        $options = get_option('duplicate_title_validate_options');
        $allow_duplicates = isset($options['allow_duplicates']) ? $options['allow_duplicates'] : false;

       
        if ($allow_duplicates) {
            return $data;
        }

        // Get the title and post ID
        $title = sanitize_text_field($postarr['post_title']);
        $post_id = $postarr['ID'];

       
        $title = trim($title);
        $duplicate_sources = $this->title_checker->check_duplicates($title, $post_id);

        // If duplicates are found, prevent publishing
        if ($duplicate_sources) {
           
            $error_message = sprintf(
                __('Title used for this post appears to be a duplicate in: %s. Please modify the title.', 'duplicate-title-validate'),
                implode(', ', $duplicate_sources)
            );

            // Set the post status to draft
            $data['post_status'] = 'draft';

            // Store the error message in a transient
            set_transient('dtv_duplicate_title_error_' . $post_id, $error_message, 60);
        }

        return $data;
    }

    /**
     * Display admin notice for duplicate title error
     */
    public function display_admin_notice() {
        global $post;

        // Check if we are on the post edit screen
        if (!isset($post->ID) || get_current_screen()->base !== 'post') {
            return;
        }

       
        $error_message = get_transient('dtv_duplicate_title_error_' . $post->ID);

   
        if ($error_message) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error_message) . '</p></div>';
            delete_transient('dtv_duplicate_title_error_' . $post->ID);
        }
    }
}