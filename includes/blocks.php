<?php 
if (! defined('ABSPATH')) {
    exit; // No direct access.
}

function wftu_register_blocks(){
    register_block_type( plugin_dir_path(__DIR__) . 'blocks/location-map' );
}

add_action('init', 'wftu_register_blocks');