<?php
/**
 * One-time migration: JetEngine's `jet_cct_maps` + `jet_rel_default` tables
 * into the new `wftu_location` post type + ACF fields.
 *
 * Run via WP-CLI, NOT as a normal plugin file:
 *   wp eval-file migrate-locations.php
 *
 * Safe to re-run: each row is tagged with `_wftu_migrated_from` after
 * creation, and already-migrated rows are skipped on subsequent runs.
 * Reads only from JetEngine's tables — never writes to them.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$locations = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}jet_cct_maps", ARRAY_A );
$relations = $wpdb->get_results( "SELECT parent_object_id, child_object_id FROM {$wpdb->prefix}jet_rel_default", ARRAY_A );

// Build old-location-ID => [ new-post parent IDs ] from the relations table.
$parent_map = array();
foreach ( $relations as $rel ) {
	$child_id  = (int) $rel['child_object_id'];
	$parent_id = (int) $rel['parent_object_id'];

	if ( ! isset( $parent_map[ $child_id ] ) ) {
		$parent_map[ $child_id ] = array();
	}
	$parent_map[ $child_id ][] = $parent_id;
}

// Old `type` column stores a full icon URL — map filename fragment to the
// new radio field's value.
$type_map = array(
	'map-pin-hiking'         => 'hike',
	'map-pin-sights'         => 'sight',
	'map-pin-food-and-drink' => 'food & drink',
	'map-pin-park'           => 'park',
	'map-pin-shopping'       => 'shopping',
	'map-pin-museums'        => 'museum',
	'map-pin-final'          => 'default',
);

$flagged_coordinates = array();
$migrated_count       = 0;
$skipped_count        = 0;

foreach ( $locations as $row ) {
	$old_id = (int) $row['_ID'];

	// Skip anything already migrated in a previous run.
	$existing = get_posts( array(
		'post_type'   => 'wftu_location',
		'meta_key'    => '_wftu_migrated_from',
		'meta_value'  => $old_id,
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		$skipped_count++;
		continue;
	}

	$post_id = wp_insert_post( array(
		'post_type'   => 'wftu_location',
		'post_title'  => $row['location'],
		'post_status' => 'publish',
	) );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		echo "FAILED to create post for old ID {$old_id}: {$row['location']}\n";
		continue;
	}

	// Tag it so re-runs skip this row.
	update_post_meta( $post_id, '_wftu_migrated_from', $old_id );

	// Featured image — old `image` column is a serialized [id, url] array.
	$image_data = maybe_unserialize( $row['image'] );
	if ( is_array( $image_data ) && ! empty( $image_data['id'] ) ) {
		set_post_thumbnail( $post_id, (int) $image_data['id'] );
	}

	// Address / map field (ACF Google Map field expects this array shape).
	update_field( 'address', array(
		'address' => $row['address'],
		'lat'     => (float) $row['coordinates_lat'],
		'lng'     => (float) $row['coordinates_lng'],
	), $post_id );

	update_field( 'description', $row['description'], $post_id );
	update_field( 'website', $row['website'], $post_id );

	// Type — extracted from the old icon URL's filename.
	$type_value = 'default';
	foreach ( $type_map as $needle => $value ) {
		if ( strpos( $row['type'], $needle ) !== false ) {
			$type_value = $value;
			break;
		}
	}
	update_field( 'type', $type_value, $post_id );

	// Display-coordinates heuristic: does `address` actually look like a
	// real address, or is it raw lat/lng or degrees-minutes-seconds text?
	$looks_like_coordinates = (
		preg_match( '/^-?\d+(\.\d+)?,\s*-?\d+(\.\d+)?$/', trim( $row['address'] ) )
		|| preg_match( '/\d+°\d+\'[\d.]+"[NSEW]/', $row['address'] )
	);
	update_field( 'display_coordinates', (bool) $looks_like_coordinates, $post_id );

	if ( $looks_like_coordinates ) {
		$flagged_coordinates[] = $row['location'] . ' (new post ID ' . $post_id . ')';
	}

	// Parent post(s) — Select Multiple + Post ID return format, so this
	// needs to be an array even when there's only one.
	if ( isset( $parent_map[ $old_id ] ) ) {
		update_field( 'parent_post', $parent_map[ $old_id ], $post_id );
	}

	echo "Migrated: {$row['location']} -> new post ID {$post_id}\n";
	$migrated_count++;
}

echo "\nDone. Migrated: {$migrated_count}. Skipped (already migrated): {$skipped_count}.\n";

if ( ! empty( $flagged_coordinates ) ) {
	echo "\nFlagged as coordinates-only — please double check these:\n";
	foreach ( $flagged_coordinates as $entry ) {
		echo "- {$entry}\n";
	}
}
