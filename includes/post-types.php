<?php

if (! defined('ABSPATH')) {
    exit; // No direct access.
}

function wftu_register_location_post_types() {
    $labels = array(
        'name' => __('Locations', 'wherefoodtakesus-core'),
        'singular_name' => __('Location', 'wherefoodtakesus-core'),
        'add_new_item' => __('Add New Location', 'wherefoodtakesus-core'),
        'edit_item' => __('Edit Location', 'wherefoodtakesus-core'),
        'all_items' => __('All Locations', 'wherefoodtakesus-core'),
        'menu_name' => __('Locations', 'wherefoodtakesus-core'),
    );
    register_post_type( 'wftu_location', array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => false,
        'rewrite' => false,
        'supports' => array('title', 'thumbnail'),
        'menu_icon' => 'dashicons-location-alt',
    ));
}

add_action('init', 'wftu_register_location_post_types');