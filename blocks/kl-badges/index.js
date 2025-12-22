// Knowledge Level Badges Block - Minimal editor script
// This is a dynamic block rendered server-side, so no build process needed

(function() {
    if (typeof wp === 'undefined' || !wp.blocks) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { useBlockProps } = wp.blockEditor;

    registerBlockType('knowledge-level/badges', {
        edit: function() {
            const blockProps = useBlockProps();
            return wp.element.createElement(
                'div',
                blockProps,
                wp.element.createElement(
                    'div',
                    { 
                        style: { 
                            padding: '20px', 
                            background: '#f0f0f0', 
                            border: '2px dashed #ccc',
                            borderRadius: '4px',
                            textAlign: 'center'
                        } 
                    },
                    wp.element.createElement('strong', null, '🏆 Knowledge Level Badges'),
                    wp.element.createElement('p', { style: { margin: '10px 0 0', fontSize: '13px', color: '#666' } }, 
                        'Badges will be displayed here on the frontend based on the values set in the post sidebar.'
                    )
                )
            );
        },
        save: function() {
            return null; // Dynamic block, rendered server-side
        }
    });
})();
