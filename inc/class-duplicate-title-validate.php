<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Duplicate_Title_Validate {

    public function __construct() {
       
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        // Initialize other classes
        new Duplicate_Title_Validate_Title_Checker();
        new Duplicate_Title_Validate_Settings();
        new Duplicate_Title_Validate_REST_API();
        new Duplicate_Title_Validate_Classic_Editor();
        new Duplicate_Title_Validate_Gutenberg();
        new Similar_Titles_Checker_Widget();
    }

    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain('duplicate-title-validate', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}