<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_post_id = get_the_ID();
$locations        = wftu_get_locations_for_post( $current_post_id );

if ( empty( $locations ) ) {
	return;
}

$locations_data = array();

foreach ( $locations as $location ) {
	$location_id = $location->ID;
	$map_field   = get_field( 'address', $location_id );

	if ( ! $map_field ) {
		continue;
	}

	$locations_data[] = array(
		'title'              => get_the_title( $location_id ),
		'address'            => $map_field['address'],
		'lat'                => $map_field['lat'],
		'lng'                => $map_field['lng'],
		'displayCoordinates' => (bool) get_field( 'display_coordinates', $location_id ),
		'website'            => get_field( 'website', $location_id ),
		'description'        => get_field( 'description', $location_id ),
		'type'               => get_field( 'type', $location_id ),
		'thumbnail'          => get_the_post_thumbnail_url( $location_id, 'medium' ),
	);
}

if ( empty( $locations_data ) ) {
	return;
}

wp_enqueue_script(
	'wftu-google-maps-api',
	'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode( defined( 'WFTU_GOOGLE_MAPS_API_KEY' ) ? WFTU_GOOGLE_MAPS_API_KEY : '' ) . '&callback=wftuInitLocationMaps&loading=async',
	array(),
	null,
	array( 'strategy' => 'async', 'in_footer' => true )
);
?>
<div <?php echo get_block_wrapper_attributes(); ?> data-locations="<?php echo esc_attr( wp_json_encode( $locations_data ) ); ?>">
	<div class="wftu-location-map" style="height: 400px;"></div>
</div>