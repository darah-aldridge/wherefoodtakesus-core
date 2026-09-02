<?php

if (! defined('ABSPATH')) {
    exit; // No direct access.
}

function wftu_acf_json_save_point ($path) {
    return plugin_dir_path(__DIR__) . 'acf-json';
}

add_filter('acf/settings/save_json', 'wftu_acf_json_save_point');

function wftu_acf_json_load_point ($paths) {
    unset($paths[0]);
    $paths[] = plugin_dir_path(__DIR__) . 'acf-json';
    return $paths;
}

add_filter('acf/settings/load_json', 'wftu_acf_json_load_point');


function wftu_acf_google_api_key() {
	if ( defined( 'WFTU_GOOGLE_MAPS_API_KEY' ) ) {
		acf_update_setting( 'google_api_key', WFTU_GOOGLE_MAPS_API_KEY );
	}
}
add_action( 'acf/init', 'wftu_acf_google_api_key' );