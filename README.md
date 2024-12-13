# Duplicate Title Validator

[![License: GPLv2 or later](https://img.shields.io/badge/License-GPLv2-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)

Duplicate Title Validator is a WordPress plugin designed to detect duplicate post titles across all post types and taxonomies. It prevents duplicate titles by saving the post as a draft and displaying a warning message. Compatible with both Gutenberg and Classic Editor, it supports full localization in English and Persian.

---

## Features

- **Comprehensive Duplicate Detection:** Scans all post types (including custom ones) and taxonomies to identify duplicate titles.
- **Clear Warning Messages:** Provides detailed error notifications specifying the source of duplication.
- **Draft Mode for Duplicates:** Automatically saves posts with duplicate titles as drafts to prevent accidental publishing.
- **URL Slug Reminder:** Prompts users to verify and update the URL slug for consistency.
- **Localization Support:** Fully translated into English and Persian, with options to add other languages.
- **Editor Compatibility:** Functions smoothly with both Gutenberg and Classic Editor.

---

## Installation

### Manual Installation

1. Clone this repository or download the ZIP file.
2. Upload the `duplicate-title-validator` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin via the **Plugins** menu in WordPress.

### Configuration

The plugin works out of the box. Customizations can be achieved by modifying the plugin files or extending its functionality.

---

## Screenshots

1. **Warning for Duplicate Title in Gutenberg Editor**

---

## FAQ

### Does this plugin support custom post types and taxonomies?
Yes, it checks for duplicate titles across all registered post types and taxonomies, including custom ones.

### Can I translate this plugin into other languages?
Absolutely! The plugin is translation-ready. Add translations by creating `.po` and `.mo` files in the `languages` directory.

### What happens when a duplicate title is detected?
The plugin saves the post as a draft and displays an error message detailing the duplication source.

### Does the plugin affect site performance?
The plugin is optimized for performance and should have minimal impact. However, on sites with extensive content, performance should be monitored.

### How do I update the plugin?
Simply pull the latest changes from this repository or update the plugin via the WordPress dashboard. Always back up your site before updating.

---

## Changelog

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

---

## Translators

- **Persian (fa_IR):** [Hasan Movahed](http://www.tazechin.com/)
- **English (en_US):** [Noumaan Yaqoob](http://www.wpbeginner.com/)

---

## Contributing

We welcome contributions! To contribute:

1. Fork this repository.
2. Create a feature branch.
3. Submit a pull request with a clear description of your changes.

Ensure your code adheres to [WordPress coding standards](https://developer.wordpress.org/coding-standards/).

---

## License

This project is licensed under the [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html) license.

---

## Support

If you need assistance, please visit the [WordPress.org support forum](https://wordpress.org/support/plugin/duplicate-title-validator).
