=== Advanced Custom Fields - Widget Relationship Field add-on ===
Contributors: djbokka
Donate link:
Tags: advanced custom fields, widget, widget management, widget filter, widget relationship
Requires at least: 3.3
Tested up to: 3.4
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin is an add-on for Advanced Custom Fields. It allows you to use an ACF "relationship" field to choose widgets at a page level.

== Description ==

This plugin is an add-on for Advanced Custom Fields. It allows you to use an ACF "relationship" field to choose widgets at a page level.

Inherit widgets from parent post or menu items. Drag and drop to change widget display order.

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In `sidebar.php`, replace `dynamic_sidebar()` function with the new `dynamic_widgets()` method.
`if ( ! acf_Widget::dynamic_widgets( 'Side Bar' ) ) {

   //fallback to default function if you like
   dynamic_sidebar( 'Side Bar' );

}`

== Frequently asked questions ==

https://bitbucket.org/djbokka/widget-relationship-field-add-on-for-advanced-custom-fields


== Screenshots ==

1. Configuration on ACF settings

2. Usage at the page level


== Changelog ==

= 1.0 =
* Initial Commit.

