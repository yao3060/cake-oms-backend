<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define( 'WP_USE_THEMES', true );

/** Loads the WordPress Environment and Template */
require __DIR__ . '/wp-blog-header.php';


// migration
if($_GET['migration'] === 'yes') {
	echo '<pre style="border: 1px solid #ccc;padding: 10px;background: #EFEFEF;">';
	// add roles
	require_once ABSPATH . 'app/Migration/roles.php';

	//	maybe_create_table
	require_once ABSPATH . 'app/Migration/orders.php';
	require_once ABSPATH . 'app/Migration/order_logs.php';

	echo '</pre>';
}
