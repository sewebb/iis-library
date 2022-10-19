<?php

namespace Internetstiftelsen;

use WP_Post;

/**
 * Helper methods for submenu functionality
 */
class Submenu {
	/**
	 * @param string|bool $align
	 * @param null|WP_Post $submenu_for
	 * @return string
	 */
	public static function render( $align = 'right', $submenu_for = null ): string {
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

			$children_parent[ $child->ID ]            = $child->post_parent;
			if ( $hide === '0' ) {
				continue;
			}

			$posts_by_id[ $child->ID ] = $child;

			if ( ! isset( $parent_children[ $child->post_parent ] ) ) {
				$parent_children[ $child->post_parent ] = [];
			}

			$parent_children[ $child->post_parent ][] = $child->ID;
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
}
