<?php

add_filter(
	'render_block',
	function ( $content, $block ) {
		if ( 'core/quote' === $block['blockName'] ) {
			$user_class_name = trim( $block['attrs']['className'] ?? '' );

			$content = preg_replace_callback( '/<blockquote(?:.*?class=\"(.*?)\")?.*?>/', function ( $matches ) use ( $user_class_name ) {
				$class_name = trim( $matches[1] ?? '' );

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
