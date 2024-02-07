// https://www.wordpressintegration.com/blog/creating-custom-wordpress-gutenberg-block/

var el = wp.element.createElement;

wp.blocks.registerBlockType('gutenberg-notice-block/notices', {
	title: 'Notices',		// Block name visible to the user within the editor
	icon: 'warning',	// Toolbar icon displayed beneath the name of the block
	category: 'common',	// The category under which the block will appear in the Add block menu
	attributes: {			// The data this block will be storing
		type: { type: 'string', default: 'default' },			// Notice box type for loading the appropriate CSS class. Default class is 'default'.
		title: { type: 'string' },			// Title of Notice box in h4 tag
		content: { type: 'array', source: 'children', selector: 'p' }		/// Notice box content in p tag
	},
	edit: function(props) {
		// Defines how the block will render in the editor
		
      function updateTitle( event ) {
	      props.setAttributes( { title: event.target.value } );
	   }

	   function updateContent( newdata ) {
	      props.setAttributes( { content: newdata } );
	   }

	   function updateType( newdata ) {
	      props.setAttributes( { type: event.target.value } );
	   }

		return el( 'div', 
			{ 
				className: 'notice-box notice-' + props.attributes.type
			}, 
			el(
				'select', 
				{
					onChange: updateType,
					value: props.attributes.type,
				},
				el("option", {value: "information" }, "Information"),
				el("option", {value: "advice" }, "Advice"),
				el("option", {value: "warning" }, "Warning"),
				el("option", {value: "danger" }, "Danger")
			),
			el(
				'input', 
				{
					type: 'text', 
					placeholder: 'Write your title here...',
					value: props.attributes.title,
					onChange: updateTitle,
					style: { width: '100%' }
				}
			),
			el(
				wp.editor.RichText,
            {
               tagName: 'p',
               onChange: updateContent,
               value: props.attributes.content,
               placeholder: 'Write your description here...'
            }
         )

		);	// End return

	},	// End edit()
	save: function(props) {
		// Defines how the block will render on the frontend
		
		return el( 'div', 
			{ 
				className: 'notice-box notice-' + props.attributes.type
			}, 
			el(
				'h4', 
				null,
				props.attributes.title
			),
			el( wp.editor.RichText.Content, {
            tagName: 'p',
            value: props.attributes.content
         })
			
		);	// End return
		
	}	// End save()
});