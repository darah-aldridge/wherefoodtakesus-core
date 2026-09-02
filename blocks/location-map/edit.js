( function ( blocks, element, blockEditor, data, apiFetch, components ) {
	var el = element.createElement;
	var useState = element.useState;
	var useEffect = element.useEffect;
	var useSelect = data.useSelect;
	var useBlockProps = blockEditor.useBlockProps;
	var ComboboxControl = components.ComboboxControl;
	var Button = components.Button;
	var Spinner = components.Spinner;

	blocks.registerBlockType( 'wherefoodtakesus/location-map', {
		edit: function () {
			var blockProps = useBlockProps();

			var postId = useSelect( function ( select ) {
				return select( 'core/editor' ).getCurrentPostId();
			}, [] );

			var linkedState = useState( null );
			var linkedLocations = linkedState[ 0 ];
			var setLinkedLocations = linkedState[ 1 ];

			var allState = useState( [] );
			var allLocations = allState[ 0 ];
			var setAllLocations = allState[ 1 ];

			var selectedState = useState( '' );
			var selectedId = selectedState[ 0 ];
			var setSelectedId = selectedState[ 1 ];

			var linkingState = useState( false );
			var isLinking = linkingState[ 0 ];
			var setIsLinking = linkingState[ 1 ];

			var removingIdState = useState( null );
			var removingId = removingIdState[ 0 ];
			var setRemovingId = removingIdState[ 1 ];

			var errorState = useState( null );
			var error = errorState[ 0 ];
			var setError = errorState[ 1 ];

			function fetchLinkedLocations() {
				if ( ! postId ) {
					return;
				}
				setError( null );
				apiFetch( { path: '/wherefoodtakesus/v1/locations-for-post/' + postId } )
					.then( setLinkedLocations )
					.catch( function () {
						setError( 'Could not load linked locations.' );
					} );
			}

			useEffect( function () {
				fetchLinkedLocations();
			}, [ postId ] );

			useEffect( function () {
				apiFetch( { path: '/wp/v2/wftu_location?per_page=100&_fields=id,title' } )
					.then( function ( results ) {
						setAllLocations( results.map( function ( loc ) {
							return { id: loc.id, title: loc.title.rendered };
						} ) );
					} )
					.catch( function () {} );
			}, [] );

			function handleLinkLocation() {
				if ( ! selectedId || ! postId ) {
					return;
				}
				setIsLinking( true );
				apiFetch( {
					path: '/wherefoodtakesus/v1/link-location',
					method: 'POST',
					data: { locationId: parseInt( selectedId, 10 ), postId: postId }
				} )
					.then( function () {
						setSelectedId( '' );
						fetchLinkedLocations();
					} )
					.catch( function () {
						setError( 'Could not link that location.' );
					} )
					.finally( function () {
						setIsLinking( false );
					} );
			}

			function handleRemoveLocation( locationId ) {
				if ( ! postId ) {
					return;
				}
				setRemovingId( locationId );
				apiFetch( {
					path: '/wherefoodtakesus/v1/unlink-location',
					method: 'POST',
					data: { locationId: locationId, postId: postId }
				} )
					.then( function () {
						fetchLinkedLocations();
					} )
					.catch( function () {
						setError( 'Could not remove that location.' );
					} )
					.finally( function () {
						setRemovingId( null );
					} );
			}

			var linkedIds = ( linkedLocations || [] ).map( function ( loc ) {
				return loc.id;
			} );

			var availableOptions = allLocations
				.filter( function ( loc ) {
					return linkedIds.indexOf( loc.id ) === -1;
				} )
				.map( function ( loc ) {
					return { value: String( loc.id ), label: loc.title };
				} );

			var linkedListBody;
			if ( error ) {
				linkedListBody = el( 'p', { style: { color: '#a00' } }, error );
			} else if ( linkedLocations === null ) {
				linkedListBody = el( 'p', {}, 'Loading linked locations…' );
			} else if ( linkedLocations.length === 0 ) {
				linkedListBody = el( 'p', {}, 'No locations linked yet.' );
			} else {
				linkedListBody = el(
					'ul',
					{},
					linkedLocations.map( function ( loc ) {
						return el(
							'li',
							{ key: loc.id, style: { display: 'flex', alignItems: 'center', gap: '6px' } },
							el( 'a', { href: loc.editLink, target: '_blank', rel: 'noopener' }, loc.title ),
							el( Button, {
								variant: 'tertiary',
								isDestructive: true,
								isSmall: true,
								disabled: removingId === loc.id,
								onClick: function () { handleRemoveLocation( loc.id ); }
							}, removingId === loc.id ? el( Spinner ) : 'Remove' )
						);
					} )
				);
			}

			return el(
				'div',
				blockProps,
				el( 'p', { style: { fontWeight: 'bold', marginBottom: '4px' } }, 'Location Map' ),
				el( 'p', { style: { fontSize: '13px', opacity: 0.7 } }, 'Renders as an interactive map on the front end. Linked locations:' ),
				linkedListBody,
				el( 'div', { style: { marginTop: '12px', borderTop: '1px solid #ddd', paddingTop: '12px' } },
					el( ComboboxControl, {
						label: 'Link an existing location',
						value: selectedId,
						onChange: setSelectedId,
						options: availableOptions,
						allowReset: true
					} ),
					el( Button, {
						variant: 'secondary',
						onClick: handleLinkLocation,
						disabled: ! selectedId || isLinking,
						style: { marginTop: '8px' }
					}, isLinking ? el( Spinner ) : 'Link Location' )
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.data, window.wp.apiFetch, window.wp.components );