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
		add_filter( 'xmlrpc_methods', [ Theme::class, 'exclude_pingbacks' ] );

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
					font-family: sans-serif;
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

	public static function submenu(): string {
		// TODO: Refactor
		global $post;

		$top_level = $post;

		while ( $top_level && 0 !== $top_level->post_parent ) {
			$top_level = get_post( $top_level->post_parent );
		}

		$all_children = get_pages(
			[
				'child_of'    => $top_level->ID,
				'post_type'   => $top_level->post_type,
				'sort_column' => 'menu_order',
			]
		);

		if ( ! $all_children ) {
			return '';
		}

		$second_level_items = [];
		$third_level_items  = [];

		foreach ( $all_children as $child ) {
			if ( $child->post_parent === $top_level->ID ) {
				$second_level_items[ $child->ID ] = $child;
			} else {
				if ( ! isset( $third_level_items[ $child->post_parent ] ) ) {
					$third_level_items[ $child->post_parent ] = [];
				}

				$third_level_items[ $child->post_parent ][ $child->ID ] = $child;
			}
		}

		ob_start();
		?>
		<div class="alignright">
			<dl class="<?php imns( 'm-submenu' ); ?>">
				<dt class="<?php imns( 'm-submenu__title' ); ?>">
					<?php if ( $post->ID !== $top_level->ID ) : ?>
						<a href="<?php echo get_permalink( $top_level ); ?>" class="<?php imns( 'm-submenu__title__link' ); ?>">
							<span><?php echo apply_filters( 'the_title', $top_level->post_title ); ?></span>
							<svg class="icon">
								<use xlink:href="#icon-arrow-variant"></use>
							</svg>
						</a>
					<?php else : ?>
						<span class="<?php imns( 'm-submenu__title__link !u-pointer-events-none' ); ?>">
					<span><?php echo apply_filters( 'the_title', $top_level->post_title ); ?></span>
				</span>
					<?php endif; ?>
				</dt>
				<?php

				foreach ( $second_level_items as $child ) :
					$link_classes = 'm-submenu__item__link';
					$hidden = true;

					if ( isset( $third_level_items[ $child->ID ] ) ) {
						$link_classes .= ' m-submenu__item__link--has-sublevel';

						foreach ( $third_level_items[ $child->ID ] as $subchild ) {
							if ( $subchild->ID === $post->ID ) {
								$hidden = false;
								break;
							}
						}
					}

					if ( $child->ID === $post->ID ) {
						$link_classes .= ' !is-current';
						$hidden        = false;
					}

					?>
					<dd class="<?php imns( 'm-submenu__item' ); ?>">
						<?php if ( isset( $third_level_items[ $child->ID ] ) ) : ?>
							<div class="<?php imns( 'm-submenu__item__sublevel' ); ?>">
								<a href="<?php echo get_permalink( $child->ID ); ?>" class="<?php imns( $link_classes ); ?>">
									<span><?php echo apply_filters( 'the_title', $child->post_title ); ?></span>
									<svg class="icon">
										<use xlink:href="#icon-arrow-variant"></use>
									</svg>
								</a>
								<button type="button" class="<?php imns( 'm-submenu__item__toggle-button' ); ?>" data-a11y-toggle="sublvl<?php echo $child->ID; ?>" aria-controls="sublvl<?php echo $child->ID; ?>">
									<span class="u-visuallyhidden">Öppna/stäng</span>
								</button>
							</div>
							<ul class="<?php imns( 'm-submenu__sublevel' ); ?>" <?php echo ( $hidden ) ? '' : 'data-a11y-toggle-open'; ?> id="sublvl<?php echo $child->ID; ?>" data-focus-trap="false">
								<?php

								foreach ( $third_level_items[ $child->ID ] as $subchild ) :
									$sub_item_classes = 'm-submenu__item__link m-submenu__sublevel__item__link';

									if ( $subchild->ID === $post->ID ) {
										$sub_item_classes .= ' !is-current';
									}

									?>
									<li class="<?php imns( 'm-submenu__sublevel__item' ); ?>">
										<a href="<?php echo get_permalink( $subchild ); ?>" class="<?php imns( $sub_item_classes ); ?>">
											<span><?php echo apply_filters( 'the_title', $subchild->post_title ); ?></span>
											<svg class="icon">
												<use xlink:href="#icon-arrow-variant"></use>
											</svg>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<a href="<?php echo get_permalink( $child->ID ); ?>" class="<?php imns( $link_classes ); ?>">
								<span><?php echo apply_filters( 'the_title', $child->post_title ); ?></span>
								<svg class="icon">
									<use xlink:href="#icon-arrow-variant"></use>
								</svg>
							</a>
						<?php endif; ?>
					</dd>
				<?php endforeach; ?>
			</dl>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Exclude xmlrpc method pingback.ping
	 * This prevents other sites from inserting pingback comments into our database
	 *
	 * @param  array $methods Original set of allowed methods
	 * @return array          Modified set of methods, excluding pingback.ping
	 */
	public function exclude_pingbacks( $methods ) {
		unset( $methods['pingback.ping'] );
		return $methods;
	}
}
