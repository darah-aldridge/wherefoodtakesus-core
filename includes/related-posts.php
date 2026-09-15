<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wftu_get_primary_category_id( $post_id ) {
	$primary_id = get_post_meta( $post_id, '_yoast_wpseo_primary_category', true );
	if ( $primary_id ) {
		return (int) $primary_id;
	}
	$categories = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
	return ! empty( $categories ) ? $categories[0] : null;
}

/**
 * Single term -> that's trivially the "primary", no lookup needed.
 * Multiple terms -> ask Yoast which one was marked primary; if none
 * was ever picked, fall back to null (caller decides what to do).
 */
function wftu_get_primary_destination_id( $post_id ) {
	$term_ids = wp_get_post_terms( $post_id, 'destination', array( 'fields' => 'ids' ) );

	if ( empty( $term_ids ) ) {
		return null;
	}
	if ( count( $term_ids ) === 1 ) {
		return (int) $term_ids[0];
	}

	$primary_id = get_post_meta( $post_id, '_yoast_wpseo_primary_destination', true );
	return $primary_id ? (int) $primary_id : null;
}

function wftu_related_posts_query_vars( $query, $block, $page ) {
	$is_related_block = $block->context['query']['wftuRelated'] ?? false;
	if ( ! $is_related_block ) {
		return $query;
	}

	$current_post_id = get_the_ID();
	if ( ! $current_post_id ) {
		return $query;
	}

	$max_posts   = 3;
	$related_ids = array();

	$primary_destination_id = wftu_get_primary_destination_id( $current_post_id );
	if ( $primary_destination_id ) {
		$related_ids = get_posts( array(
	'post_type'      => 'post',
	'post__not_in'   => array( $current_post_id ),
	'tax_query'      => array(
		array( 'taxonomy' => 'destination', 'field' => 'term_id', 'terms' => array( $primary_destination_id ) ),
	),
	'posts_per_page' => $max_posts,
	'orderby'        => 'rand',
	'fields'         => 'ids',
) );
	}

	$remaining = $max_posts - count( $related_ids );
	if ( $remaining > 0 ) {
		$primary_category_id = wftu_get_primary_category_id( $current_post_id );
		if ( $primary_category_id ) {
			$category_ids = get_posts( array(
				'post_type'      => 'post',
				'post__not_in'   => array_merge( array( $current_post_id ), $related_ids ),
				'tax_query'      => array(
					array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => array( $primary_category_id ) ),
				),
				'posts_per_page' => $remaining,
				'orderby'        => 'rand',
				'fields'         => 'ids',
			) );
			$related_ids = array_merge( $related_ids, $category_ids );
		}
	}

	if ( empty( $related_ids ) ) {
		$query['post__in'] = array( 0 );
		return $query;
	}

	$query['post_type']      = 'post';
	$query['post__in']       = $related_ids;
	$query['orderby']        = 'post__in';
	$query['posts_per_page'] = $max_posts;
	unset( $query['post__not_in'] );
	unset( $query['tax_query'] );

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'wftu_related_posts_query_vars', 10, 3 );