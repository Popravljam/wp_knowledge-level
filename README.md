# Knowledge Level

A WordPress plugin that adds configurable badges to posts with customizable display modes (bars or tokens).

## Features

* Create unlimited badge types with custom colors
* Two display modes: Bar or Tokens
* Multiple token styles: dashes, circles, filled circles, or Dashicons
* Assign badge values per post via meta box
* Shortcodes: `[kl_badges]` and `[kl_badge id="X"]`
* Gutenberg block support
* Auto-insert badges before or after content
* Fully accessible with ARIA labels
* No external dependencies

## Requirements

* WordPress 6.0 or higher
* PHP 7.4 or higher

## Installation

1. Upload the plugin files to `/wp-content/plugins/knowledge-level/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Badge Types > Add New to create your first badge type
4. Edit any post and assign badge values in the sidebar

## Usage

### Display Badges

You can display badges in three ways:

1. **Shortcode**: Use `[kl_badges]` to display all badges for a post
2. **Gutenberg Block**: Add the "Knowledge Level Badges" block in the block editor
3. **Auto-insert**: Enable auto-insert in Settings to display badges automatically

### Customize Badge Types

Each badge type can be configured with:
- Custom color
- Display mode (Bar or Tokens)
- Token style (dashes, circles, filled circles, or Dashicons)

## FAQ

**How do I display badges?**  
You can use the `[kl_badges]` shortcode, the Gutenberg block, or enable auto-insert in Settings.

**Can I customize the colors?**  
Yes! Each badge type has its own color picker.

**Is it Gutenberg compatible?**  
Yes! The plugin includes a dynamic Gutenberg block and post meta is REST API compatible.

## Changelog

### 1.0.5
* Current stable release

### 1.0.0
* Initial release
* Badge Types custom post type
* Per-post badge values
* Bar and token display modes
* Shortcodes and Gutenberg block
* Auto-insert functionality

## License

GPLv2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## Author

Lazar Čovs
