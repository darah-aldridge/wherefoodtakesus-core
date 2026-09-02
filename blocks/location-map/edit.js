( function( blocks, element, blockEditor) {
    var el = element.createElement;
    var useBlockProps = blockEditor.useBlockProps;

    blocks.registerBlockType('wherefoodtakesus/location-map', {
        edit: function() {
            var blockProps = useBlockProps();
            return el('div', blockProps, el('p', {}, 'Location Map — renders on the front end, showing pins for any Locations linked to this post.'));
            },
        save: function() {
            return null;
        }
    });
}) (window.wp.blocks, window.wp.element, window.wp.blockEditor);