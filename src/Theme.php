<?php

namespace Internetstiftelsen;

/**
 * Setup common theme functionality
 */
class Theme {
	public static function init() {
		add_action( 'after_setup_theme', [ Theme::class, 'theme_setup' ] );
		add_action( 'wp_footer', [ Theme::class, 'env_banner' ] );
		add_action( 'admin_footer', [ Theme::class, 'env_banner' ] );

		require_once __DIR__ . '/blocks/index.php';
	}

	/**
	 * Setup theme
	 *
	 * @return void
	 */
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
					'name'  => 'Cyberspace',
					'slug'  => 'cyberspace',
					'color' => '#1f2a36',
				],
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

	/**
	 * Add environment banner on dev and stage
	 *
	 * @return void
	 */
	public static function env_banner() {
		if ( defined( 'WP_ENV' ) && in_array( WP_ENV, [ 'stage', 'dev' ], true ) ) {
			$banner_text = ( 'stage' === WP_ENV ) ? 'STAGE' : 'DEV'; ?>

			<div class="ribbon">
				<?php echo $banner_text; ?>
			</div>

			<style media="screen">
				.ribbon {
					position: fixed;
					z-index: 100000;
					/* Must be higher than WP's admin toolbar */
					bottom: 0;
					left: 0;
					padding: 0.5rem 0;
					-webkit-transform: translateX(-33.33%) rotate(45deg);
					transform: translateX(-33.33%) rotate(45deg);
					-webkit-transform-origin: bottom right;
					transform-origin: bottom right;
					border: 0;
					background-color: #e0bff5;
					font-family: "HK Grotesk Semibold", sans-serif;
					font-family: sans-serif;
					font-size: 12px;
					text-align: center;
					text-transform: uppercase;
				}

				.ribbon::before,
				.ribbon::after {
					content: '';
					position: absolute;
					top: 0;
					width: 200%;
					height: 100%;
					margin: 0 -1px;
					background-color: inherit;
				}

				.ribbon::before {
					right: 100%;
				}

				.ribbon::after {
					left: 100%;
				}

				@media (min-width: 576px) {
					.ribbon {
						font-size: 14px;
					}
				}

				@media (min-width: 1400px) {
					.ribbon {
						font-size: 18px;
					}
				}
			</style>

			<?php
		}
	}
}
