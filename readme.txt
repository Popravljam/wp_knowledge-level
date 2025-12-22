=== Knowledge Level ===
Contributors: Lazar Čovs
Tags: badges, knowledge, level, difficulty, posts
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add configurable badges to posts with customizable display modes (bars or tokens).

== Description ==

Knowledge Level allows you to create reusable badge types and assign them to posts. Each badge can be displayed as a progress bar or as tokens (dashes, circles, or icons).

= Features =

* Create unlimited badge types with custom colors
* Two display modes: Bar or Tokens
* Multiple token styles: dashes, circles, filled circles, or Dashicons
* Assign badge values per post via meta box
* Shortcodes: [kl_badges] and [kl_badge id="X"]
* Gutenberg block support
* Auto-insert badges before or after content
* Fully accessible with ARIA labels
* No external dependencies

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/knowledge-level/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Badge Types > Add New to create your first badge type
4. Edit any post and assign badge values in the sidebar

== Frequently Asked Questions ==

= How do I display badges? =

You can use the [kl_badges] shortcode, the Gutenberg block, or enable auto-insert in Settings.

= Can I customize the colors? =

Yes! Each badge type has its own color picker.

= Is it Gutenberg compatible? =

Yes! The plugin includes a dynamic Gutenberg block and post meta is REST API compatible.

== Changelog ==

= 1.0.0 =
* Initial release
* Badge Types custom post type
* Per-post badge values
* Bar and token display modes
* Shortcodes and Gutenberg block
* Auto-insert functionality
