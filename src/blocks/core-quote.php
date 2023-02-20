<?php

add_filter(
	'render_block',
	function ( $content, $block ) {
		if ( 'core/quote' === $block['blockName'] ) {
			$content = preg_replace( '/<blockquote.*?class=\"(.*?)\".*?>/', '<blockquote class="$1 peacock"><svg class="iis-icon"><use xlink:href="#icon-quote"></use></svg>', $content );
			$content = str_replace( '<cite>', '<cite class="meta">', $content );
		}

		return $content;
	},
	10,
	2
);
