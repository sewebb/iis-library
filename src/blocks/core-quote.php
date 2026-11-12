<?php

/**
 * Add the IIS color picker to the core Quote block inspector.
 *
 * The selected palette slug is stored as a regular block class so the quote
 * icon can continue to be styled by the theme's existing color utilities.
 *
 * @return void
 */
function iis_add_quote_icon_color_control() {
	$colors = current( (array) get_theme_support( 'editor-color-palette' ) ) ?: [];

	$script = sprintf(
		<<<'JS'
( function( blockEditor, components, compose, element, hooks, i18n ) {
	const colors = %1$s;
	const colorSlugs = colors.map( ( color ) => color.slug );
	const largeQuoteFontClass = 'iis-quote-large-font';
	const legacyBaseFontSizeClass = 'iis-quote-font-size-base';
	const { InspectorControls, PanelColorSettings } = blockEditor;
	const { PanelBody, ToggleControl } = components;
	const { createHigherOrderComponent } = compose;
	const { createElement: el, Fragment } = element;
	const { __ } = i18n;

	const withQuoteControls = createHigherOrderComponent( ( BlockEdit ) => ( props ) => {
		const classNames = ( props.attributes.className || '' ).trim().split( /\s+/ ).filter( Boolean );
		const selectedColor = colors.find( ( color ) => classNames.includes( color.slug ) );
		const usesLargeQuoteFont = classNames.includes( largeQuoteFontClass );
		if ( 'core/quote' !== props.name ) {
			return el( BlockEdit, props );
		}

		const setColor = ( value ) => {
			const selected = colors.find( ( color ) => color.color.toLowerCase() === ( value || '' ).toLowerCase() );
			const nextClassNames = classNames.filter( ( className ) => ! colorSlugs.includes( className ) );

			if ( selected ) {
				nextClassNames.push( selected.slug );
			}

			props.setAttributes( { className: nextClassNames.join( ' ' ) } );
		};

		const setLargeQuoteFont = ( enabled ) => {
			const nextClassNames = classNames.filter( ( className ) => ! [ largeQuoteFontClass, legacyBaseFontSizeClass ].includes( className ) );

			if ( enabled ) {
				nextClassNames.push( largeQuoteFontClass );
			}

			props.setAttributes( { className: nextClassNames.join( ' ' ) } );
		};

		return el(
			Fragment,
			null,
			el( BlockEdit, props ),
			el(
				InspectorControls,
				null,
				el( PanelColorSettings, {
					title: __( 'Color settings', 'iis-library' ),
					colorSettings: [ {
						colors,
						value: selectedColor ? selectedColor.color : undefined,
						onChange: setColor,
						label: __( 'Quote icon color', 'iis-library' ),
					} ],
				} ),
				el(
					PanelBody,
					{ title: __( 'Typography', 'iis-library' ), initialOpen: true },
					el( ToggleControl, {
						label: __( 'Use large quote font', 'iis-library' ),
						checked: usesLargeQuoteFont,
						onChange: setLargeQuoteFont,
					} )
				)
			)
		);
	}, 'withQuoteControls' );

	hooks.addFilter( 'editor.BlockEdit', 'iis-library/quote-controls', withQuoteControls );
} )( window.wp.blockEditor, window.wp.components, window.wp.compose, window.wp.element, window.wp.hooks, window.wp.i18n );
JS,
		wp_json_encode( $colors )
	);

	wp_add_inline_script( 'wp-block-editor', $script );
}
add_action( 'enqueue_block_editor_assets', 'iis_add_quote_icon_color_control' );

/**
 * Match the Quote icon and font-size utility in the block editor canvas.
 *
 * The editor's theme stylesheet does not include the utility classes used on
 * the frontend. Editor styles are injected into the canvas iframe through
 * block settings, rather than the surrounding admin document.
 *
 * @param array $settings Block editor settings.
 * @return array
 */
function iis_add_quote_editor_styles( $settings ) {
	$css = <<<'CSS'
.editor-styles-wrapper blockquote.wp-block-quote {
	position: relative;
	text-align: left !important;
}

.editor-styles-wrapper blockquote.wp-block-quote::before {
	background-color: #c27fec;
	content: '';
	height: 2rem;
	left: 0;
	position: absolute;
	top: 0;
	width: 1rem;
	-webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 32'%3E%3Cpath d='M16,0v24H8v-8H0V0H16z M0,24h8v8H0C0,32,0,24,0,24z'/%3E%3C/svg%3E") center / contain no-repeat;
	mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 32'%3E%3Cpath d='M16,0v24H8v-8H0V0H16z M0,24h8v8H0C0,32,0,24,0,24z'/%3E%3C/svg%3E") center / contain no-repeat;
}

.editor-styles-wrapper blockquote.wp-block-quote:not(.iis-quote-large-font) p {
	font-size: 1rem !important;
}

.editor-styles-wrapper blockquote.wp-block-quote p {
	margin-left: 0 !important;
	margin-right: 0 !important;
	text-align: left !important;
}

@media (min-width: 769px) {
	.editor-styles-wrapper blockquote.wp-block-quote::before {
		height: 3rem;
		width: 1.5rem;
	}
}
CSS;

	$palette = current( (array) get_theme_support( 'editor-color-palette' ) );

	foreach ( $palette as $color ) {
		if ( empty( $color['slug'] ) || empty( $color['color'] ) ) {
			continue;
		}

		$css .= sprintf(
			'.editor-styles-wrapper blockquote.wp-block-quote.%1$s::before { background-color: %2$s; }',
			sanitize_html_class( $color['slug'] ),
			sanitize_hex_color( $color['color'] )
		);
	}

	$settings['styles'][] = [ 'css' => $css ];

	return $settings;
}
add_filter( 'block_editor_settings_all', 'iis_add_quote_editor_styles' );

add_filter(
	'render_block',
	function ( $content, $block ) {
		if ( 'core/quote' === $block['blockName'] ) {
			$user_class_name      = trim( $block['attrs']['className'] ?? '' );
			$large_quote_font_class = 'iis-quote-large-font';
			$uses_large_quote_font  = in_array( $large_quote_font_class, preg_split( '/\s+/', $user_class_name ), true );
			$user_class_name        = trim( str_replace( [ $large_quote_font_class, 'iis-quote-font-size-base' ], '', $user_class_name ) );

			if ( ! $uses_large_quote_font ) {
				$html = new WP_HTML_Tag_Processor( $content );

				if ( $html->next_tag( [ 'tag_name' => 'p' ] ) ) {
					$html->add_class( 'u-font-size-base' );
					$content = $html->get_updated_html();
				}
			}

			$content = preg_replace_callback( '/<blockquote(?:.*?class=\"(.*?)\")?.*?>/', function ( $matches ) use ( $user_class_name ) {
				$class_name = trim( $matches[1] ?? '' );
				$class_name = trim( str_replace( [ 'iis-quote-large-font', 'iis-quote-font-size-base' ], '', $class_name ) );

				if ( '' === $user_class_name ) {
					$class_name = trim( 'peacock ' . $class_name );
				}

				return '<blockquote class="' . $class_name . '"><svg class="iis-icon"><use xlink:href="#icon-quote"></use></svg>';
			}, $content );
			$content = str_replace( '<cite>', '<cite class="meta">', $content );
		}

		return $content;
	},
	10,
	2
);
