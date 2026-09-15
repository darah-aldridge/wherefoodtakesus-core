<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wftu_register_destination_taxonomy() {
	$labels = array(
		'name'              => __( 'Destinations', 'wherefoodtakesus-core' ),
		'singular_name'     => __( 'Destination', 'wherefoodtakesus-core' ),
		'search_items'      => __( 'Search Destinations', 'wherefoodtakesus-core' ),
		'all_items'         => __( 'All Destinations', 'wherefoodtakesus-core' ),
		'parent_item'       => __( 'Parent Destination', 'wherefoodtakesus-core' ),
		'parent_item_colon' => __( 'Parent Destination:', 'wherefoodtakesus-core' ),
		'edit_item'         => __( 'Edit Destination', 'wherefoodtakesus-core' ),
		'update_item'       => __( 'Update Destination', 'wherefoodtakesus-core' ),
		'add_new_item'      => __( 'Add New Destination', 'wherefoodtakesus-core' ),
		'new_item_name'     => __( 'New Destination Name', 'wherefoodtakesus-core' ),
		'menu_name'         => __( 'Destinations', 'wherefoodtakesus-core' ),
	);

	register_taxonomy( 'destination', array( 'post' ), array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'query_var'         => true,
		'rewrite'           => array(
			'slug'         => 'destination',
			'hierarchical' => true,
			'with_front'   => false,
		),
	) );
}
add_action( 'init', 'wftu_register_destination_taxonomy' );

function wftu_reorder_taxonomies() {
	global $wp_taxonomies;

	if ( ! isset( $wp_taxonomies['destination'] ) ) {
		return;
	}

	$desired_order = array( 'category', 'destination', 'post_tag' );
	$reordered     = array();

	foreach ( $desired_order as $tax ) {
		if ( isset( $wp_taxonomies[ $tax ] ) ) {
			$reordered[ $tax ] = $wp_taxonomies[ $tax ];
			unset( $wp_taxonomies[ $tax ] );
		}
	}

	// Reordered ones first, then anything else exactly as it was.
	$wp_taxonomies = $reordered + $wp_taxonomies;
}
add_action( 'init', 'wftu_reorder_taxonomies', 999 );