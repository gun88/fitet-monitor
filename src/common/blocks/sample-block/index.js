(async function (wp) {
	/**
	 * Registers a new block provided a unique name and an object defining its behavior.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/#registering-a-block
	 */
	var registerBlockType = wp.blocks.registerBlockType;
	/**
	 * Returns a new element of given type. Element is an abstraction layer atop React.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/packages/packages-element/
	 */
	var el = wp.element.createElement;
	var raw = wp.element.RawHTML;
	/**
	 * Retrieves the translation of text.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/packages/packages-i18n/
	 */
	var __ = wp.i18n.__;


	var xaxa = await wp.apiRequest({
		path: '../?rest_route=/fitet-monitor/v1/shortcode/subscribe', // todo controlla se funziona su altri siti
		type: 'GET',
		data: {
			content: 'ResTom',
			style: 'color: green',
		}
	})
		.then(r => {
			console.log('_____________', r);
			return r.body;
		}).then(r => {
			console.log('$$$$$$$$$', r);
			return r;
		})

	/**
	 * Every block starts by registering a new block type definition.
	 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/#registering-a-block
	 */
	registerBlockType('fitet-monitor/sample-block', {
		/**
		 * This is the display title for your block, which can be translated with `i18n` functions.
		 * The block inserter will show this name.
		 */
		title: __('Sample Block', 'fitet-monitor'),

		/**
		 * An icon property should be specified to make it easier to identify a block.
		 * These can be any of WordPress’ Dashicons, or a custom svg element.
		 */
		icon: 'menu',

		/**
		 * Blocks are grouped into categories to help users browse and discover them.
		 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
		 */
		category: 'embed',

		/**
		 * Optional block extended support features.
		 */
		supports: {
			// Removes support for an HTML mode.
			html: false,
		},

		/**
		 * The edit function describes the structure of your block in the context of the editor.
		 * This represents what the editor will render when the block is used.
		 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#edit
		 *
		 * @param {Object} [props] Properties passed from the editor.
		 * @return {Element}       Element to render.
		 */
		edit: function (props) {

			async function aaa() {
				//

				/*return fetch("/?rest_route=/fitet-monitor/v1/shortcode/subscribe")*/
				return await wp.apiRequest({
					path: '../?rest_route=/fitet-monitor/v1/shortcode/subscribe', // todo controlla se funziona su altri siti
					type: 'GET',
					data: {
						content: 'ResTom',
						style: 'color: green',
					}
				})
					.then(r => {
						console.log('_____________', r);
						return r.body;
					}).then(r => {
						console.log('$$$$$$$$$', r);
						return r;
					})
			}

			/*let xaxa = aaa();
			console.log(xaxa)*/
			console.log('edit', props)
			//<p style='$style'>" . $str . "</p>

			return el('div', {className: props.className},
				el('p', {}, __('My Block', 'fitet-monitor')),
				el('p', {style: {fontSize: '2rem'}}, 'foobar'),
				wp.element.RawHTML({children: xaxa})
			);
		},

		/**
		 * The save function defines the way in which the different attributes should be combined
		 * into the final markup, which is then serialized by Gutenberg into `post_content`.
		 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#save
		 *
		 * @return {Element}       Element to render.
		 */
		save: function (props) {
			return el('div', {/*className: props.className*/},
				el('p', {}, __('My Block', 'fitet-monitor')),
				el('p', {style: {fontSize: '2rem'}}, 'foobar')
			);
		}
	});
})(
	window.wp
);
