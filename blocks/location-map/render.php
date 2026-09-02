<?php
if (! defined('ABSPATH')) {
    exit; // No direct access.
}

$current_post_id = get_the_ID();

$locations_query = new WP_Query( array(
	'post_type'      => 'wftu_location',
	'posts_per_page' => -1,
	'meta_query'     => array(
		array(
			'key'     => 'parent_post',
			'value'   => '"' . $current_post_id . '"',
			'compare' => 'LIKE',
		),
	),
) );

if (! $locations_query->have_posts()) {
    return;
}

$locations_data = array();

foreach ($locations_query->posts as $location) {
    $location_id = $location->ID;
    $map_field = get_field('address', $location_id);

    if (! $map_field) {
        continue; // Skip if no map field found.
    }

    $locations_data[] = array(
        'title'              => get_the_title($location_id),
        'address'            => $map_field['address'],
        'lat'                => $map_field['lat'],
        'lng'                => $map_field['lng'],
        'displayCoordinates' => (bool) get_field('display_coordinates', $location_id),
        'website'            => get_field('website', $location_id),
        'description'        => get_field('description', $location_id),
        'type'               => get_field('type', $location_id),
        'thumbnail'          => get_the_post_thumbnail_url($location_id, 'medium'),
    );
}

if ( empty($locations_data) ) {
    return;
}
?>
<div <?php echo get_block_wrapper_attributes(); ?> data-locations="<?php echo esc_attr(wp_json_encode($locations_data)); 
wp_enqueue_script(
	'wftu-google-maps-api',
	'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode( defined( 'WFTU_GOOGLE_MAPS_API_KEY' ) ? WFTU_GOOGLE_MAPS_API_KEY : '' ) . '&callback=wftuInitLocationMaps&loading=async',
	array(),
	null,
	array( 'strategy' => 'async', 'in_footer' => true )
);
?>">
    <div class="wftu-location-map" style="height: 400px;"></div>
</div>