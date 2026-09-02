<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wftu_register_rest_routes() {
	register_rest_route( 'wherefoodtakesus/v1', '/locations-for-post/(?P<id>\d+)', array(
		'methods'             => 'GET',
		'callback'            => 'wftu_rest_locations_for_post',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
}
add_action( 'rest_api_init', 'wftu_register_rest_routes' );

function wftu_rest_locations_for_post( $request ) {
	$post_id   = (int) $request['id'];
	$locations = wftu_get_locations_for_post( $post_id );

	$response = array();
	foreach ( $locations as $location ) {
		$response[] = array(
			'id'       => $location->ID,
			'title'    => get_the_title( $location->ID ),
			'editLink' => get_edit_post_link( $location->ID, 'raw' ),
		);
	}

	return rest_ensure_response( $response );
}

function wftu_rest_link_location_to_post( $request ) {
	$params      = $request->get_json_params();
	$location_id = isset( $params['locationId'] ) ? (int) $params['locationId'] : 0;
	$post_id     = isset( $params['postId'] ) ? (int) $params['postId'] : 0;

	if ( ! $location_id || ! $post_id || get_post_type( $location_id ) !== 'wftu_location' ) {
		return new WP_Error( 'wftu_invalid_params', 'Invalid location or post ID.', array( 'status' => 400 ) );
	}

	$current = get_field( 'parent_post', $location_id );
	$current = is_array( $current ) ? $current : array();

	if ( ! in_array( $post_id, $current ) ) {
		$current[] = $post_id;
		update_field( 'parent_post', $current, $location_id );
	}

	return rest_ensure_response( array( 'success' => true ) );
}
add_action( 'rest_api_init', function () {
	register_rest_route( 'wherefoodtakesus/v1', '/link-location', array(
		'methods'             => 'POST',
		'callback'            => 'wftu_rest_link_location_to_post',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
} );

function wftu_rest_unlink_location_from_post( $request ) {
	$params      = $request->get_json_params();
	$location_id = isset( $params['locationId'] ) ? (int) $params['locationId'] : 0;
	$post_id     = isset( $params['postId'] ) ? (int) $params['postId'] : 0;

	if ( ! $location_id || ! $post_id || get_post_type( $location_id ) !== 'wftu_location' ) {
		return new WP_Error( 'wftu_invalid_params', 'Invalid location or post ID.', array( 'status' => 400 ) );
	}

	$current = get_field( 'parent_post', $location_id );
	$current = is_array( $current ) ? $current : array();

	$updated = array_values( array_filter( $current, function ( $id ) use ( $post_id ) {
		return (int) $id !== $post_id;
	} ) );

	update_field( 'parent_post', $updated, $location_id );

	return rest_ensure_response( array( 'success' => true ) );
}
add_action( 'rest_api_init', function () {
	register_rest_route( 'wherefoodtakesus/v1', '/unlink-location', array(
		'methods'             => 'POST',
		'callback'            => 'wftu_rest_unlink_location_from_post',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
} );