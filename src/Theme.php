<?php

namespace Internetstiftelsen;

class Theme {
	public static function init() {
		add_action( 'after_setup_theme', [Theme::class, 'theme_setup'] );

		require_once __DIR__ . '/blocks/index.php';
	}

	public static function theme_setup() {
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'disable-custom-colors' );
		add_theme_support(
			'editor-color-palette',
			[
				[
					'name'  => 'Ocean dark',
					'slug'  => 'ocean-dark',
					'color' => '#0477ce',
				],
				[
					'name'  => 'Ruby light',
					'slug'  => 'ruby-light',
					'color' => '#ff9fb4',
				],
				[
					'name'  => 'Ruby dark',
					'slug'  => 'ruby-dark',
					'color' => '#d9002f',
				],
				[
					'name'  => 'Jade light',
					'slug'  => 'jade-light',
					'color' => '#aae3d9',
				],
				[
					'name'  => 'Jade dark',
					'slug'  => 'jade-dark',
					'color' => '#2d897a',
				],
				[
					'name'  => 'Lemon',
					'slug'  => 'lemon',
					'color' => '#ffce2e',
				],
				[
					'name'  => 'Lemon light',
					'slug'  => 'lemon-light',
					'color' => '#ffe696',
				],
			]
		);

		// Don't use the texturize function, show posts as is
		add_filter( 'run_wptexturize', '__return_false' );
	}
}
