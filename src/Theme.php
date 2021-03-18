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
		add_filter( 'render_block', [ Theme::class, 'append_submenu_hero' ], 10, 2 );
		add_filter( 'the_content', [ Theme::class, 'append_submenu' ] );

		require_once __DIR__ . '/blocks/index.php';
		require_once __DIR__ . '/acf.php';
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

	/**
	 * @param string|bool $align
	 * @param null|WP_Post $submenu_for
	 * @return string
	 */
	public static function submenu( $align = 'right', $submenu_for = null ): string {
		// TODO: Refactor
		global $post;

		$submenu_for = ( is_null( $submenu_for ) ) ? $post : $submenu_for;
		$top_level   = $submenu_for;

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

		if ( ! $all_children || ! count( $all_children ) ) {
			return '';
		}

		$parent_children = [];
		$children_parent = [];
		$posts_by_id     = [];

		foreach ( $all_children as $child ) {
			$hide = get_post_meta( $child->ID, 'display_in_submenus', true );

			if ( $hide === '0' ) {
				continue;
			}

			$posts_by_id[ $child->ID ] = $child;

			if ( ! isset( $parent_children[ $child->post_parent ] ) ) {
				$parent_children[ $child->post_parent ] = [];
			}

			$parent_children[ $child->post_parent ][] = $child->ID;
			$children_parent[ $child->ID ]            = $child->post_parent;
		}

		if ( $top_level->ID === $post->ID ) {
			// Current post is the top level. Display two levels down
			$top_level_id = $submenu_for->ID;
		} elseif ( ! isset( $parent_children[$submenu_for->ID] ) ) {
			// Current post is on the last level
			$top_level_id = $children_parent[ $submenu_for->ID ];

			if ( isset( $children_parent[ $top_level_id ] ) ) {
				$top_level_id = $children_parent[ $top_level_id ];
			}
		} else {
			$top_level_id = $submenu_for->post_parent;
		}

		if ( $top_level_id !== $top_level->ID && isset( $posts_by_id[ $top_level_id ] ) ) {
			$top_level = $posts_by_id[ $top_level_id ];
		}

		$wrapper_class = '';

		if ( $align ) {
			$wrapper_class .= 'u-m-t-2 align' . $align;
		}

		ob_start();
		?>
		<nav class="rs_skip <?php echo esc_attr( $wrapper_class ); ?>" id="pageSubmenu">
			<dl class="<?php imns( 'm-submenu' ); ?>" data-responsive="xs:article,lg:pageSubmenu">
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

				$children = $parent_children[ $top_level->ID ];

				foreach ( $children as $child ) :
					$child        = $posts_by_id[ $child ];
					$link_classes = 'm-submenu__item__link';
					$hidden = true;

					if ( isset( $parent_children[ $child->ID ] ) ) {
						$link_classes .= ' m-submenu__item__link--has-sublevel';

						foreach ( $parent_children[ $child->ID ] as $subchild ) {
							if ( $subchild === $submenu_for->ID ) {
								$hidden = false;
								break;
							}
						}
					}

					if ( $child->ID === $post->ID ) {
						$link_classes .= ' !is-current';
					}

					if ( $child->ID === $submenu_for->ID ) {
						$hidden = false;
					}

					?>
					<dd class="<?php imns( 'm-submenu__item' ); ?>">
						<?php if ( isset( $parent_children[ $child->ID ] ) ) : ?>
							<div class="<?php imns( 'm-submenu__item__sublevel' ); ?>">
								<a href="<?php echo get_permalink( $child->ID ); ?>" class="<?php imns( $link_classes ); ?>">
									<span><?php echo apply_filters( 'the_title', $child->post_title ); ?></span>
								</a>
								<button type="button" class="<?php imns( 'm-submenu__item__toggle-button' ); ?>" data-a11y-toggle="sublvl<?php echo $child->ID; ?>" aria-controls="sublvl<?php echo $child->ID; ?>">
									<span class="u-visuallyhidden">Öppna/stäng</span>
								</button>
							</div>
							<ul class="<?php imns( 'm-submenu__sublevel' ); ?>" <?php echo ( $hidden ) ? '' : 'data-a11y-toggle-open'; ?> id="sublvl<?php echo $child->ID; ?>" data-focus-trap="false">
								<?php

								foreach ( $parent_children[ $child->ID ] as $subchild ) :
									$subchild         = $posts_by_id[ $subchild ];
									$sub_item_classes = 'm-submenu__item__link m-submenu__sublevel__item__link';

									if ( $subchild->ID === $post->ID ) {
										$sub_item_classes .= ' !is-current';
									}

									?>
									<li class="<?php imns( 'm-submenu__sublevel__item' ); ?>">
										<a href="<?php echo get_permalink( $subchild ); ?>" class="<?php imns( $sub_item_classes ); ?>">
											<span><?php echo apply_filters( 'the_title', $subchild->post_title ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<a href="<?php echo get_permalink( $child->ID ); ?>" class="<?php imns( $link_classes ); ?>">
								<span><?php echo apply_filters( 'the_title', $child->post_title ); ?></span>
							</a>
						<?php endif; ?>
					</dd>
				<?php endforeach; ?>
			</dl>
		</nav>
		<?php

		return str_replace( [ "\t", "\n", "\r" ], '', ob_get_clean() );
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

	public static function append_submenu_hero( $content, $block ): string {
		if ( ! is_page() || ! apply_filters( 'iis_render_submenu', false ) ) {
			return $content;
		}

		if ( 'iis/hero' === $block['blockName'] ) {
			return $content . self::submenu();
		}

		return $content;
	}

	public static function append_submenu( $content ): string {
		if ( ! is_page() || ! apply_filters( 'iis_render_submenu', false ) ) {
			return $content;
		}

		if ( ! iis_has_hero() ) {
			return self::submenu() . $content;
		}

		return $content;
	}
}
