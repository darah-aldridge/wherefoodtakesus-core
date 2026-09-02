<?php
/**
 * Plugin Name: Where Food Takes Us Core
 * Description: Custom post types and ACF field groups for wherefoodtakesus.com
 * Version 0.1.0
 * Author: Darah 
 * Text Domain: wherefoodtakesus-core 
 */

if (! defined('ABSPATH')) {
    exit; // No direct access.
}


require_once plugin_dir_path(__FILE__) . 'includes/acf-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/blocks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/locations-query.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/rest-api.php';