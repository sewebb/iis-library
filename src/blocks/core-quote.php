<?php

add_filter(
	'render_block',
	function ( $content, $block ) {
		if ( 'core/quote' === $block['blockName'] ) {
			$content = preg_replace_callback( '/<blockquote(?:.*?class=\"(.*?)\")?.*?>/', function ( $matches ) {
				$class_name = trim( $matches[1] ?? '' ) ? $matches[1] : 'peacock';

				return '<blockquote class="' . $class_name . '"><svg class="iis-icon"><use xlink:href="#icon-quote"></use></svg>';
			}, $content );
			$content = str_replace( '<cite>', '<cite class="meta">', $content );
		}

		return $content;
	},
	10,
	2
);
