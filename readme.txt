=== Duplicate Title Validator ===
Tags: duplicate, title, duplicate checker, taxonomy, localization
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.0
Stable Tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin detects duplicate post titles across all post types and taxonomies. It prevents duplicate titles by saving the post as a draft and displaying a warning message. It also reminds users to review the URL slug for uniqueness when modifying the post title. Compatible with both Gutenberg and Classic Editor, with full localization in English and Persian.

== Description ==

**Duplicate Title Validator** is a robust WordPress plugin designed to ensure the uniqueness of post titles across all post types and taxonomies. By preventing duplicate titles, this plugin enhances both SEO and user experience. Whether you use Gutenberg or the Classic Editor, it seamlessly integrates to maintain title uniqueness.

### Key Features
- **Comprehensive Duplicate Detection:** Scans all post types (including custom ones) and taxonomies to identify duplicate titles.
- **Clear Warning Messages:** Provides detailed error notifications specifying the source of duplication.
- **Draft Mode for Duplicates:** Automatically saves posts with duplicate titles as drafts to prevent accidental publishing.
- **URL Slug Reminder:** Prompts users to verify and update the URL slug for consistency.
- **Localization Support:** Fully translated into English and Persian, with options to add other languages.
- **Editor Compatibility:** Functions smoothly with both Gutenberg and Classic Editor.

== Installation ==

### Steps to Install the Plugin
1. **Upload the Plugin:**
   - Upload the `duplicate-title-validator` folder to the `/wp-content/plugins/` directory.

2. **Activate the Plugin:**
   - Go to **Plugins > Installed Plugins** in your WordPress dashboard.
   - Locate **Duplicate Title Validator** and click **Activate**.

3. **Configure Settings (Optional):**
   - The plugin works out of the box. Customization can be achieved by editing plugin files or extending its functionality.

== Frequently Asked Questions ==

### Does this plugin support custom post types and taxonomies?
Yes, it checks for duplicate titles across all registered post types and taxonomies, including custom ones.

### Can I translate this plugin into other languages?
Absolutely! The plugin is translation-ready. Add translations by creating `.po` and `.mo` files in the `languages` directory.

### What happens when a duplicate title is detected?
The plugin saves the post as a draft and displays an error message detailing the duplication source.

### Does the plugin affect site performance?
The plugin is optimized for performance and should have minimal impact. However, on sites with extensive content, performance should be monitored.

### How do I update the plugin?
Updates can be done directly via the WordPress dashboard. Always back up your site before updating.

== Screenshots ==

1. **Warning for Duplicate Title in Gutenberg Editor**

== Upgrade Notice ==

### 1.4
- Added advanced taxonomy duplication checks.
- Improved localization for Persian.
- Enhanced clarity of error messages.
- Performance optimizations for large datasets.

### 1.3
- Refactored to object-oriented code.
- Expanded support for all taxonomies.
- Updated compatibility with latest WordPress versions.

### 1.2
- Added localization support.
- Improved error messages with duplication sources.

### 1.1
- Enhanced handling of REST API responses.
- Improved Classic Editor compatibility.

### 1.0
- Initial release with title duplication detection and prevention.

== Changelog ==

### 1.4
- Enhanced taxonomy duplication checks.
- Improved localization support for Persian.
- Refined error message clarity.
- Optimized performance for larger datasets.

### 1.3
- Refactored plugin structure to object-oriented.
- Added comprehensive taxonomy support.
- Improved compatibility with WordPress updates.

### 1.2
- Introduced localization.
- Enhanced duplication source identification.

### 1.1
- Improved handling of complex REST API responses.
- Enhanced Classic Editor support.

### 1.0
- Launched with core functionality for duplicate title detection.

== Translators ==

* **Persian (fa_IR):** [Hasan Movahed](http://www.tazechin.com/)
* **English (en_US):** [Noumaan Yaqoob](http://www.wpbeginner.com/)

== License ==

This plugin is licensed under the GPLv2 or later license.

== Contributing ==

Contributions are welcome! Fork the repository and submit a pull request. Ensure your code adheres to WordPress coding standards and includes thorough documentation.

== Support ==

For assistance, visit the [WordPress.org support forum](https://wordpress.org/support/plugin/duplicate-title-validator).

