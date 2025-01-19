<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Duplicate_Title_Validate_Settings {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add settings page to the main menu
     */
    public function add_settings_page() {
        add_menu_page(
            __('Duplicate Title Validate Settings', 'duplicate-title-validate'),
            __('Duplicate Title', 'duplicate-title-validate'),
            'manage_options',
            'duplicate-title-validate-settings',
            [$this, 'render_settings_page'],
            'dashicons-warning',
            100
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Register a setting for the options group
        register_setting(
            'duplicate_title_validate_options_group', // Option group
            'duplicate_title_validate_options', // Option name
            [$this, 'sanitize_options'] // Sanitize callback
        );

        // Add a main settings section
        add_settings_section(
            'duplicate_title_validate_main_section', // ID
            __('Main Settings', 'duplicate-title-validate'), // Title
            [$this, 'render_section_text'], // Callback
            'duplicate-title-validate-settings' // Page
        );

        // Add fields to the main section
        add_settings_field(
            'duplicate_title_validate_allow_duplicates', // ID
            __('Allow Duplicate Titles', 'duplicate-title-validate'), // Title
            [$this, 'render_allow_duplicates_field'], // Callback
            'duplicate-title-validate-settings', // Page
            'duplicate_title_validate_main_section' // Section
        );

        // Add a new section for Dashboard Widget settings
        add_settings_section(
            'duplicate_title_validate_dashboard_widget_section', // ID
            '', // Leave title empty to avoid duplication
            [$this, 'render_dashboard_widget_section_text'], // Callback
            'duplicate-title-validate-settings' // Page
        );

        // Add fields to the Dashboard Widget section
        add_settings_field(
            'duplicate_title_validate_limit', // ID
            __('Limit for Items', 'duplicate-title-validate'), // Title
            [$this, 'render_limit_field'], // Callback
            'duplicate-title-validate-settings', // Page
            'duplicate_title_validate_dashboard_widget_section' // Section
        );

        add_settings_field(
            'duplicate_title_validate_similarity_threshold', // ID
            __('Similarity Threshold', 'duplicate-title-validate'), // Title
            [$this, 'render_similarity_threshold_field'], // Callback
            'duplicate-title-validate-settings', // Page
            'duplicate_title_validate_dashboard_widget_section' // Section
        );

        add_settings_field(
            'duplicate_title_validate_max_similar_items', // ID
            __('Max Similar Items', 'duplicate-title-validate'), // Title
            [$this, 'render_max_similar_items_field'], // Callback
            'duplicate-title-validate-settings', // Page
            'duplicate_title_validate_dashboard_widget_section' // Section
        );
    }

    /**
     * Sanitize options
     */
    public function sanitize_options($input) {
        $sanitized_input = [];

        // Sanitize each field
        $sanitized_input['allow_duplicates'] = isset($input['allow_duplicates']) ? (bool)$input['allow_duplicates'] : false;
        $sanitized_input['limit'] = isset($input['limit']) ? absint($input['limit']) : 1000; // Ensure positive integer
        $sanitized_input['similarity_threshold'] = isset($input['similarity_threshold']) ? floatval($input['similarity_threshold']) : 0.5; // Ensure float
        $sanitized_input['max_similar_items'] = isset($input['max_similar_items']) ? absint($input['max_similar_items']) : 6; // Ensure positive integer

        return $sanitized_input;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check if the settings were saved successfully
    if (isset($_GET['settings-updated'])) {
        add_settings_error('duplicate_title_validate_messages', 'duplicate_title_validate_message', __('Settings Saved', 'duplicate-title-validate'), 'updated');
    }

    // Show error/update messages
    settings_errors('duplicate_title_validate_messages');
    ?>
        
        <div class="wrap">
            <h1><?php _e('Duplicate Title Validate Settings', 'duplicate-title-validate'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('duplicate_title_validate_options_group');
                do_settings_sections('duplicate-title-validate-settings');
                submit_button();
                ?>
            </form>
        </div>

        <div style="background-color:rgb(255, 255, 255); padding: 20px; border-radius: 10px; text-align: center; font-family: Arial, sans-serif; max-width: 60%; margin: 0 0;">
    <h2 style="color: #333; font-size: 20px; margin-bottom: 10px;">🚀 Get Your Custom WordPress Plugin at an Unbelievable Price!</h2>
    <p style="color: #555; font-size: 14px; line-height: 1.5;">
        With over <strong>1,600 active installations</strong>, we have extensive experience in designing WordPress plugins.<br>
        Now, to celebrate this success, we’re offering an exclusive deal:<br>
        ✅ <strong>Custom WordPress Plugin for Only $10!</strong><br>
        ✅ This offer is valid only for the <strong>first 10 customers</strong>.
    </p>
    <p style="color: #777; font-size: 14px; margin-top: 15px;">
        📧 For orders or any questions, email me at:<br>
        <a href="mailto:hasan.mova@gmail.com" style="color: #0073e6; text-decoration: none;">hasan.mova@gmail.com</a>
    </p>
    <a href="mailto:hasan.mova@gmail.com?subject=Get Special Offer&body=Hi, I\'m interested in your $10 WordPress plugin offer!" style="display: inline-block; background-color: #0073e6; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-size: 14px; margin-top: 10px;">
        💡 Get This Special Offer Now
    </a>
</div>

        <?php
    }

    /**
     * Render main section text
     */
    public function render_section_text() {
        echo '<p>' . __('Configure the main settings for the Duplicate Title Validate plugin.', 'duplicate-title-validate') . '</p>';
    }

    /**
     * Render Dashboard Widget section text
     */
    public function render_dashboard_widget_section_text() {
        echo '<hr style="margin-top: 20px; margin-bottom: 20px;">'; // Add a separator line with margin
        echo '<h2>' . __('Dashboard Widget Settings', 'duplicate-title-validate') . '</h2>';
        echo '<p>' . __('Configure the settings for the Dashboard Widget.', 'duplicate-title-validate') . '</p>';
    }

    /**
     * Render the "Allow Duplicate Titles" checkbox
     */
    public function render_allow_duplicates_field() {
        $options = get_option('duplicate_title_validate_options');
        $allow_duplicates = isset($options['allow_duplicates']) ? $options['allow_duplicates'] : false;
        ?>
        <label>
            <input type="checkbox" name="duplicate_title_validate_options[allow_duplicates]" value="1" <?php checked($allow_duplicates, 1); ?> />
            <?php _e('Allow publishing posts with duplicate titles', 'duplicate-title-validate'); ?>
        </label>
        <?php
    }

    /**
     * Render the "Limit for Items" input field
     */
    public function render_limit_field() {
        $options = get_option('duplicate_title_validate_options');
        $limit = isset($options['limit']) ? $options['limit'] : 1000;
        ?>
        <input type="number" name="duplicate_title_validate_options[limit]" value="<?php echo esc_attr($limit); ?>" min="1" />
        <?php
    }

    /**
     * Render the "Similarity Threshold" input field
     */
    public function render_similarity_threshold_field() {
        $options = get_option('duplicate_title_validate_options');
        $similarity_threshold = isset($options['similarity_threshold']) ? $options['similarity_threshold'] : 0.5;
        ?>
        <input type="number" step="0.1" min="0" max="1" name="duplicate_title_validate_options[similarity_threshold]" value="<?php echo esc_attr($similarity_threshold); ?>" />
        <?php
    }

    /**
     * Render the "Max Similar Items" input field
     */
    public function render_max_similar_items_field() {
        $options = get_option('duplicate_title_validate_options');
        $max_similar_items = isset($options['max_similar_items']) ? $options['max_similar_items'] : 6;
        ?>
        <input type="number" name="duplicate_title_validate_options[max_similar_items]" value="<?php echo esc_attr($max_similar_items); ?>" min="1" />
        <?php
    }
}