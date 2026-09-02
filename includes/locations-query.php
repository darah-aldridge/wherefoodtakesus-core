<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find every Location whose `parent_post` field includes the given post ID.
 *
 * Shared between the front-end map block (render.php) and the editor's
 * REST endpoint below, so both use one proven query instead of two
 * copies that could quietly drift apart.
 */
function wftu_get_locations_for_post( $post_id ) {
	$query = new WP_Query( array(
		'post_type'      => 'wftu_location',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'     => 'parent_post',
				'value'   => '"' . (int) $post_id . '"',
				'compare' => 'LIKE',
			),
		),
	) );

	return $query->posts;
}