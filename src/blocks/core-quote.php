<?php

function iis_render_block_blockquote( $attributes, $content ) {
	$content = str_replace( 'blockquote class="', 'blockquote class="peacock ', $content );
	$content = str_replace( '><p>', '><svg class="iis-icon"><use xlink:href="#icon-quote"></use></svg><p>', $content );
	$content = str_replace( '<cite>', '<cite class="meta">', $content );

	return $content;
}

register_block_type(
	'core/quote',
	[
		'render_callback' => 'iis_render_block_blockquote',
	]
);
