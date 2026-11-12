<?php

/**
 * Add IIS list-icon controls to the core List and List Item blocks.
 *
 * Icons and colors are stored as the CSS classes supplied by the styleguide.
 *
 * @return void
 */
function iis_add_list_icon_controls() {
	$colors = current( (array) get_theme_support( 'editor-color-palette' ) ) ?: [];

	$script = sprintf(
		<<<'JS'
( function( blockEditor, components, compose, element, hooks, i18n ) {
	const colors = %1$s;
	const { __ } = i18n;
	const iconClasses = [
		{ label: __( 'Default bullet', 'iis-library' ), value: '' },
		{ label: __( 'Quote', 'iis-library' ), value: 'bullet-list--quote' },
		{ label: __( 'Arrow', 'iis-library' ), value: 'bullet-list--arrow-right-variant' },
		{ label: __( 'Heart', 'iis-library' ), value: 'bullet-list--heart-variant' },
		{ label: __( 'Check', 'iis-library' ), value: 'bullet-list--check-variant' },
		{ label: __( 'Close', 'iis-library' ), value: 'bullet-list--close-variant' },
	];
	const iconClassNames = iconClasses.map( ( icon ) => icon.value ).filter( Boolean );
	const colorSlugs = colors.map( ( color ) => color.slug );
	const { InspectorControls, PanelColorSettings } = blockEditor;
	const { PanelBody, SelectControl } = components;
	const { createHigherOrderComponent } = compose;
	const { createElement: el, Fragment, useEffect } = element;

	const withListIconControls = createHigherOrderComponent( ( BlockEdit ) => ( props ) => {
		const isList = 'core/list' === props.name;
		const isListItem = 'core/list-item' === props.name;
		const classNames = ( props.attributes.className || '' ).trim().split( /\s+/ ).filter( Boolean );

		// Core/List puts its custom class on an editor wrapper rather than the
		// ul. Mirror it to the ul in the canvas so the styleguide's list-level
		// icon rules preview exactly as they do on the site.
		useEffect( () => {
			if ( ! isList ) {
				return undefined;
			}

			const canvas = document.querySelector( 'iframe[name="editor-canvas"]' );
			const canvasDocument = canvas ? canvas.contentDocument : document;
			const list = canvasDocument ? canvasDocument.querySelector( '[data-block="' + props.clientId + '"] ul' ) : null;
			const previewClassNames = iconClassNames.concat( colorSlugs );

			if ( ! list ) {
				return undefined;
			}

			list.classList.remove( ...previewClassNames );
			list.classList.add( ...classNames.filter( ( className ) => previewClassNames.includes( className ) ) );

			return () => list.classList.remove( ...previewClassNames );
		}, [ isList, props.clientId, classNames.join( ' ' ) ] );

		if ( ! isList && ! isListItem ) {
			return el( BlockEdit, props );
		}

		const selectedIcon = iconClassNames.find( ( iconClassName ) => classNames.includes( iconClassName ) ) || '';
		const selectedColor = colors.find( ( color ) => classNames.includes( color.slug ) );
		const targetLabel = isList ? __( 'List', 'iis-library' ) : __( 'List item', 'iis-library' );

		const setIcon = ( iconClassName ) => {
			const nextClassNames = classNames.filter( ( className ) => ! iconClassNames.includes( className ) );

			if ( iconClassName ) {
				nextClassNames.push( iconClassName );
			}

			props.setAttributes( { className: nextClassNames.join( ' ' ) } );
		};

		const setColor = ( value ) => {
			const selected = colors.find( ( color ) => color.color.toLowerCase() === ( value || '' ).toLowerCase() );
			const nextClassNames = classNames.filter( ( className ) => ! colorSlugs.includes( className ) );

			if ( selected ) {
				nextClassNames.push( selected.slug );
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
				el(
					PanelBody,
					{ title: __( 'List icon', 'iis-library' ), initialOpen: true },
					el( SelectControl, {
						label: targetLabel + ' ' + __( 'icon', 'iis-library' ),
						value: selectedIcon,
						options: iconClasses,
						onChange: setIcon,
					} )
				),
				el( PanelColorSettings, {
					title: __( 'Color settings', 'iis-library' ),
					colorSettings: [ {
						colors,
						value: selectedColor ? selectedColor.color : undefined,
						onChange: setColor,
						label: targetLabel + ' ' + __( 'icon color', 'iis-library' ),
					} ],
				} )
			)
		);
	}, 'withListIconControls' );

	hooks.addFilter( 'editor.BlockEdit', 'iis-library/list-icon-controls', withListIconControls );
} )( window.wp.blockEditor, window.wp.components, window.wp.compose, window.wp.element, window.wp.hooks, window.wp.i18n );
JS,
		wp_json_encode( $colors )
	);

	wp_add_inline_script( 'wp-block-editor', $script );
}
add_action( 'enqueue_block_editor_assets', 'iis_add_list_icon_controls' );

/**
 * Keep list icon classes on the elements targeted by the styleguide.
 *
 * Gutenberg may place a List block's custom class on an editor wrapper.  The
 * frontend styleguide targets the ul itself, so copy the selected class names
 * onto the rendered ul.  List items additionally receive a small marker class
 * used to hide the browser's native bullet when they use a custom icon.
 *
 * @param string $content The block HTML.
 * @param array  $block   The parsed block.
 * @return string
 */
function iis_render_list_icon_classes( $content, $block ) {
	$icon_pattern = '/^bullet-list--(?:quote|arrow-right-variant|heart-variant|check-variant|close-variant)$/';
	$class_names  = preg_split( '/\s+/', trim( $block['attrs']['className'] ?? '' ) );
	$class_names  = array_filter( $class_names, static fn( $class_name ) => preg_match( $icon_pattern, $class_name ) || in_array( $class_name, array_column( current( (array) get_theme_support( 'editor-color-palette' ) ) ?: [], 'slug' ), true ) );

	if ( empty( $class_names ) ) {
		return $content;
	}

	$html = new WP_HTML_Tag_Processor( $content );

	if ( 'core/list' === $block['blockName'] && $html->next_tag( [ 'tag_name' => 'ul' ] ) ) {
		foreach ( $class_names as $class_name ) {
			$html->add_class( $class_name );
		}
	}

	if ( 'core/list-item' === $block['blockName'] && $html->next_tag( [ 'tag_name' => 'li' ] ) ) {
		$html->add_class( 'iis-list-icon' );
	}

	return $html->get_updated_html();
}
add_filter( 'render_block', 'iis_render_list_icon_classes', 10, 2 );

/**
 * Hide a native list marker when the styleguide draws a custom icon instead.
 *
 * @return void
 */
function iis_add_list_icon_marker_style() {
	wp_add_inline_style(
		'wp-block-library',
		'.wp-block-list[class*="bullet-list--"], .wp-block-list li[class*="bullet-list--"], .wp-block-list li.iis-list-icon { list-style: none; }'
	);
}
add_action( 'wp_enqueue_scripts', 'iis_add_list_icon_marker_style', 20 );
add_action( 'enqueue_block_editor_assets', 'iis_add_list_icon_marker_style', 20 );

/**
 * Apply the marker reset inside Gutenberg's canvas iframe as well.
 *
 * @param array $settings Block editor settings.
 * @return array
 */
function iis_add_list_icon_editor_styles( $settings ) {
	$settings['styles'][] = [
		'css' => '.editor-styles-wrapper .wp-block-list[class*="bullet-list--"], .editor-styles-wrapper .wp-block-list li[class*="bullet-list--"], .editor-styles-wrapper .wp-block-list li.iis-list-icon { list-style: none !important; }',
	];

	return $settings;
}
add_filter( 'block_editor_settings_all', 'iis_add_list_icon_editor_styles' );
