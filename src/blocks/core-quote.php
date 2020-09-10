<?php

add_filter( 'render_block', function ( $content, $block ) {
	if ( 'core/quote' === $block['blockName'] ) {
		$content = str_replace( 'blockquote class="', 'blockquote class="peacock ', $content );
		$content = str_replace( '><p>', '><svg class="iis-icon"><use xlink:href="#icon-quote"></use></svg><p>', $content );
		$content = str_replace( '<cite>', '<cite class="meta">', $content );
	}

	return $content;
}, 10, 2 );
