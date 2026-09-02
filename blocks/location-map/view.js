window.wftuInitLocationMaps = function () {
	var mapContainers = document.querySelectorAll( '.wftu-location-map' );

	mapContainers.forEach( function ( mapEl ) {
		var wrapper = mapEl.closest( '[data-locations]' );
		if ( ! wrapper ) {
			return;
		}

		var locations;
		try {
			locations = JSON.parse( wrapper.getAttribute( 'data-locations' ) );
		} catch ( e ) {
			return;
		}

		if ( ! locations || ! locations.length ) {
			return;
		}

		var markerIcons = {
            hike: '/wp-content/uploads/2026/09/map-pin-food-and-drink.svg',
            sight: '/wp-content/uploads/2026/09/map-pin-sights.svg',
            'food & drink': '/wp-content/uploads/2026/09/map-pin-food-and-drink.svg',
            'default': '/wp-content/uploads/2026/09/map-pin-default.svg',
            museum: '/wp-content/uploads/2026/09/map-pin-museums.svg',
            shopping: '/wp-content/uploads/2026/09/map-pin-shopping.svg',
            park: '/wp-content/uploads/2026/09/map-pin-park.svg',
		};

		var map = new google.maps.Map( mapEl, {
			zoom: locations.length > 1 ? 8 : 14,
			center: { lat: locations[0].lat, lng: locations[0].lng }
		} );

		var bounds = new google.maps.LatLngBounds();
		var infoWindow = new google.maps.InfoWindow();

		locations.forEach( function ( location ) {
			var position = { lat: location.lat, lng: location.lng };
			bounds.extend( position );

			var marker = new google.maps.Marker( {
				position: position,
				map: map,
				title: location.title,
				icon: {
				url: markerIcons[ location.type ] || markerIcons['default'],
				scaledSize: new google.maps.Size( 35, 46 )
				}
			} );

			var subtitle = location.displayCoordinates
				? location.lat.toFixed( 5 ) + ', ' + location.lng.toFixed( 5 )
				: location.address;

			var content = '<div class="wftu-map-info">';
			if ( location.thumbnail ) {
				content += '<img src="' + location.thumbnail + '" alt="" style="width:100%;height:auto;margin-bottom:6px;">';
			}
			content += '<strong>' + location.title + '</strong><br>' + subtitle;
			if ( location.description ) {
				content += '<p>' + location.description + '</p>';
			}
			if ( location.website ) {
				content += '<a href="' + location.website + '" target="_blank" rel="noopener">Website</a>';
			}
			var directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + location.lat + ',' + location.lng;
content += ' <a href="' + directionsUrl + '" target="_blank" rel="noopener">Get Directions</a>';
			content += '</div>';

			marker.addListener( 'click', function () {
				infoWindow.setContent( content );
				infoWindow.open( map, marker );
			} );
		} );

		if ( locations.length > 1 ) {
			map.fitBounds( bounds );
		}
	} );
};