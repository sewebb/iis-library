<?php

namespace Internetstiftelsen;

use WP_Post;

/**
 * Setup common theme functionality
 */
class Theme {
	public static function init() {
		add_action( 'after_setup_theme', [ Theme::class, 'theme_setup' ] );
		add_action( 'wp_footer', [ Theme::class, 'env_banner' ] );
		add_action( 'admin_footer', [ Theme::class, 'env_banner' ] );
		add_action(' admin_head', [ Theme::class, 'inject_admin_styles' ] );
		add_filter( 'xmlrpc_methods', [ Theme::class, 'exclude_pingbacks' ] );
		add_filter( 'render_block', [ Theme::class, 'append_submenu_hero' ], 10, 2 );
		add_filter( 'the_content', [ Theme::class, 'append_submenu' ] );
		add_filter(
			'site_status_tests',
			function( $tests ) {
				unset( $tests['async']['background_updates'] );
				return $tests;
			}
		);

		require_once __DIR__ . '/blocks/index.php';
		require_once __DIR__ . '/acf.php';
	}

	/**
	 * Setup theme
	 *
	 * @return void
	 */
	public static function theme_setup() {
		load_theme_textdomain( 'iis-library', __DIR__ . '/languages' );

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
					'name'  => 'Ocean',
					'slug'  => 'ocean',
					'color' => '#50b2fc',
				],
				[
					'name'  => 'Ocean light',
					'slug'  => 'ocean-light',
					'color' => '#a7d8fd',
				],
				[
					'name'  => 'Ocean dark',
					'slug'  => 'ocean-dark',
					'color' => '#0477ce',
				],
				[
					'name'  => 'Ruby',
					'slug'  => 'ruby',
					'color' => '#ff4069',
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
					'name'  => 'Jade',
					'slug'  => 'jade',
					'color' => '#55c7b4',
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
				[
					'name'  => 'Peacock',
					'slug'  => 'peacock',
					'color' => '#c27fec',
				],
				[
					'name'  => 'Peacock light',
					'slug'  => 'peacock-light',
					'color' => '#e0bff5',
				],
				[
					'name'  => 'Sandstone',
					'slug'  => 'sandstone',
					'color' => '#f99963',
				],
				[
					'name'  => 'Sandstone light',
					'slug'  => 'sandstone-light',
					'color' => '#fcccb1',
				],
				[
					'name'  => 'Granit',
					'slug'  => 'granit',
					'color' => '#8E9297',
				],
				[
					'name'  => 'Snow',
					'slug'  => 'snow',
					'color' => '#ffffff',
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
		$env = wp_get_environment_type();

		if ( in_array( $env, [ 'local', 'development', 'staging' ], true ) ) {
			$banner_text = ( 'staging' === $env ) ? 'STAGE' : 'DEV'; ?>

			<div class="ribbon js-ribbon">
				<?php echo $banner_text; ?>
			</div>

			<script type="text/javascript">
				var ribbonElement = document.querySelector('.js-ribbon');
				ribbonElement.addEventListener('mouseover', () => {
					ribbonElement.classList.add('is-hidden');
					setTimeout(function() {
						ribbonElement.classList.remove('is-hidden');
					}, 4000);
				});
			</script>
			<style media="screen">
				.ribbon {
					position: fixed;
					z-index: 100000;
					/* Must be higher than WP's admin toolbar */
					bottom: 0;
					left: 0;
					padding: 0.5rem 0;
					transform: translateX(-33.33%) rotate(45deg);
					transform-origin: bottom right;
					background-color: #e0bff5;
					font-family: "HK Grotesk Semibold", sans-serif;
					font-size: 12px;
					text-align: center;
					text-transform: uppercase;
				}

				.ribbon.is-hidden {
					display: none;
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

	/**
	 * Exclude xmlrpc method pingback.ping
	 * This prevents other sites from inserting pingback comments into our database
	 *
	 * @param array $methods Original set of allowed methods
	 * @return array          Modified set of methods, excluding pingback.ping
	 */
	public static function exclude_pingbacks( array $methods ): array {
		unset( $methods['pingback.ping'] );
		return $methods;
	}

	public static function append_submenu_hero( $content, $block ): string {
		if ( ! is_page() || ! apply_filters( 'iis_render_submenu', false ) ) {
			return $content;
		}

		if ( 'iis/hero' === $block['blockName'] ) {
			return $content . Submenu::render();
		}

		return $content;
	}

	public static function append_submenu( $content ): string {
		if ( ! is_page() || ! apply_filters( 'iis_render_submenu', false ) ) {
			return $content;
		}

		if ( ! iis_has_hero() ) {
			return Submenu::render() . $content;
		}

		return $content;
	}

	// Clearfix for right aligned Gutenberg blocks
	public static function inject_admin_styles() {
		echo '<style>
					.block-editor-block-list__block:has([data-align="right"]) + .block-editor-block-list__block,
					[data-align="right"] + .block-editor-block-list__block {
					clear: both;
					position: relative;
					top: 1rem;
				}
		  		</style>';
	}
}
