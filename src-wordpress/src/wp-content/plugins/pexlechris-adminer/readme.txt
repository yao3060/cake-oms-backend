=== Database Management tool - Adminer ===
Contributors: pexlechris
Plugin Name: Database Management tool - Adminer
Author: Pexle Chris
Author URI: https://www.pexlechris.dev
Tags: Adminer, Database, sql, mysql, mariadb, Database Manager
Version: 1.0.0
Stable tag: 1.0.0
Adminer version: 4.8.1
Requires at least: 4.6.0
Tested up to: 5.9.3
Requires PHP: 5.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage the database from your WordPress Dashboard using Adminer.

== Description ==

The best database management tool for the best CMS.

This plugin uses the tool [Adminer 4.8.1](https://www.adminer.org/) in order to give database access to administrators directly from the Dashboard.
As simple as the previous sentence!

== WP Adminer access positions ==
You can access the WP Adminer from the above positions:
1. WP Adminer URL in the Admin Bar
2. WP Adminer Tools Page (Dashboard > Tools > WP Adminer)

== Explore my other plugins ==
* [Library Viewer](https://www.pexlechris.dev/library-viewer/wp-wpadminer): With Library Viewer, you can display the containing files and the containing folders of a “specific folder” of your (FTP) server to your users in the front-end.
* [Gift Wrapping for WooCommerce](https://wordpress.org/plugins/gift-wrapping-for-woocommerce): This plugin allows customers to select a gift wrapper for their orders, via a checkbox in the checkout page.


== Screenshots ==

1. The WP Adminer opened from Tools
2. The WP Adminer opened from Admin Bar

== Frequently Asked Questions ==

 = Is it safe? =
 Yes, because only administrators have access to WP Adminer. If a guest try to access the WP Adminer URL, a 404 page will be shown up.

 = How to allow other capabilities or roles to have access to WP Adminer? =
 Just use the filter `wp_adminer_access_capabilities` and return the array of desired capabilities that you want to have access to WP Adminer.
 For roles, just use the corresponding capabilities, while checking against particular roles in place of a capability is supported in part, this practice is discouraged as it may produce unreliable results.

 = Why is Adminer better than phpMyAdmin? =
 Replace **phpMyAdmin** with **Adminer** and you will get a tidier user interface, better support for MySQL features, higher performance and more security. [See detailed comparison](https://www.adminer.org/en/phpmyadmin/).
 Adminer development priorities are: 1. Security, 2. User experience, 3. Performance, 4. Feature set, 5. Size.

== Installation ==

1. Download the plugin from [Official WP Plugin Repository](https://wordpress.org/plugins/pexlechris-adminer/)
2. Upload Plugin from your WP Dashboard ( Plugins > Add New > Upload Plugin ) the pexlechris-adminer.zip file.
3. Activate the plugin through the 'Plugins' menu in WordPress Dashboard



== Changelog ==

 = 1.0.0 =
*	Initial Release.
