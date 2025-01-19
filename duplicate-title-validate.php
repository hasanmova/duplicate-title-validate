<?php
/*
* Plugin Name: Duplicate Title Validate
* Description: Prevents publishing posts with duplicate titles and identifies conflicting post types or taxonomies. Compatible with Classic Editor and Gutenberg.
* Version: 1.6
* Author: Hasan Movahed
* Text Domain: duplicate-title-validate
* Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


require_once plugin_dir_path(__FILE__) . 'inc/class-duplicate-title-validate.php';
require_once plugin_dir_path(__FILE__) . 'inc/class-Similar-Titles-Widget.php';
require_once plugin_dir_path(__FILE__) . 'inc/class-title_checker.php';
require_once plugin_dir_path(__FILE__) . 'inc/class-settings.php';
require_once plugin_dir_path(__FILE__) . 'inc/class-rest-api.php';
require_once plugin_dir_path(__FILE__) . 'inc/class-classic-editor.php';
require_once plugin_dir_path(__FILE__) . 'inc/class-gutenberg.php';



function dtv_init() {
    new Duplicate_Title_Validate();
}
add_action('init', 'dtv_init');