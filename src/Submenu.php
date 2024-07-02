<?php

namespace Internetstiftelsen;

use WP_Post;

/**
 * Helper methods for submenu functionality
 */
class Submenu {
	private static function mapChildren( $id, $parent_children, $posts_by_id ): array {
		$children = $parent_children[ $id ] ?? [];

		if ( ! $children || ! count( $children ) ) {
			return [];
		}

		return array_map(
			function ( $child_id ) use ( $parent_children, $posts_by_id ) {
				$child = $posts_by_id[ $child_id ];

				return (object) [
					'id'         => $child->ID,
					'title'      => $child->post_title,
					'url'        => get_permalink( $child ),
					'is_current' => $child->ID === get_the_ID(),
					'items'      => self::mapChildren( $child->ID, $parent_children, $posts_by_id ),
				];
			},
			$children
		);
	}

	public static function data( WP_Post|null $submenu_for = null ): object {
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

		$parent_children = [];
		$children_parent = [];
		$posts_by_id     = [];

		if ( is_array( $all_children ) ) {
			foreach ( $all_children as $child ) {
				$hide                          = get_post_meta( $child->ID, 'display_in_submenus', true );
				$children_parent[ $child->ID ] = $child->post_parent;

				if ( $hide === '0' ) {
					continue;
				}

				$posts_by_id[ $child->ID ] = $child;

				if ( ! isset( $parent_children[ $child->post_parent ] ) ) {
					$parent_children[ $child->post_parent ] = [];
				}

				$parent_children[ $child->post_parent ][] = $child->ID;
			}
		}

		if ( $top_level->ID === $submenu_for->ID ) {
			// Current post is the top level. Display two levels down
			$top_level_id = $submenu_for->ID;
		} elseif ( ! isset( $parent_children[ $submenu_for->ID ] ) ) {
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

		return (object) [
			'title' => $top_level->post_title,
			'url'   => ( $submenu_for->ID !== $top_level->ID ) ? get_permalink( $top_level ) : null,
			'items' => self::mapChildren( $top_level->ID, $parent_children, $posts_by_id ),
		];
	}

	public static function render( string $align = 'right', WP_Post|null $submenu_for = null, array $prepend_items = [], array $append_items = [] ): string {
		$data          = self::data( $submenu_for );
		$wrapper_class = '';

		if ( $align ) {
			$wrapper_class .= 'u-m-t-2 align' . $align;
		}

		$data->items = array_merge(
			json_decode( json_encode( $prepend_items ) ),
			$data->items,
			json_decode( json_encode( $append_items ) ),
		);

		if ( ! count( $data->items ) ) {
			return '';
		}

		ob_start();
		?>
		<nav class="rs_skip <?php echo esc_attr( $wrapper_class ); ?>" id="pageSubmenu" aria-label="<?php esc_html_e( 'Submenu', 'iis-library' ); ?>">
			<dl class="<?php imns( 'm-submenu' ); ?>" data-responsive="xs:article,lg:pageSubmenu">
				<dt class="<?php imns( 'm-submenu__title' ); ?>">
					<?php if ( $data->url ) : ?>
						<a href="<?php echo esc_url( $data->url ); ?>" class="<?php imns( 'm-submenu__title__link' ); ?>">
							<span><?php echo apply_filters( 'the_title', $data->title ); ?></span>
							<svg class="icon">
								<use xlink:href="#icon-arrow-variant"></use>
							</svg>
						</a>
					<?php else : ?>
						<span class="<?php imns( 'm-submenu__title__link !u-pointer-events-none' ); ?>">
							<span><?php echo apply_filters( 'the_title', $data->title ); ?></span>
						</span>
					<?php endif; ?>
				</dt>
				<?php

				foreach ( $data->items as $child ) :
					$link_classes = 'm-submenu__item__link';
					$hidden       = true;

					if ( count( $child->items ?? [] ) ) {
						$link_classes .= ' m-submenu__item__link--has-sublevel';

						foreach ( $child->items as $subchild ) {
							if ( $subchild->is_current ?? false ) {
								$hidden = false;
								break;
							}
						}
					}

					if ( $child->is_current ?? false ) {
						$hidden = false;
					}

					if ( $child->is_current ?? false ) {
						$link_classes .= ' !is-current';
					}

					?>
					<dd class="<?php imns( 'm-submenu__item' ); ?>">
						<?php if ( count( $child->items ?? [] ) ) : ?>
							<div class="<?php imns( 'm-submenu__item__sublevel' ); ?>">
								<a href="<?php echo esc_url( $child->url ); ?>" class="<?php imns( $link_classes ); ?>">
									<span><?php echo apply_filters( 'the_title', $child->title ); ?></span>
								</a>
								<button type="button" class="<?php imns( 'm-submenu__item__toggle-button' ); ?>" data-a11y-toggle="sublvl<?php echo $child->id; ?>" aria-controls="sublvl<?php echo $child->id; ?>">
									<span class="u-visuallyhidden"><?php esc_html_e( 'Open/Close', 'iis-library' ); ?></span>
								</button>
							</div>
							<ul class="<?php imns( 'm-submenu__sublevel' ); ?>" <?php echo ( $hidden ) ? '' : 'data-a11y-toggle-open'; ?> id="sublvl<?php echo $child->id; ?>" data-focus-trap="false">
								<?php

								foreach ( $child->items as $subchild ) :
									$sub_item_classes = 'm-submenu__item__link m-submenu__sublevel__item__link';

									if ( $subchild->is_current ?? false ) {
										$sub_item_classes .= ' !is-current';
									}

									?>
									<li class="<?php imns( 'm-submenu__sublevel__item' ); ?>">
										<a href="<?php echo esc_url( $subchild->url ); ?>" class="<?php imns( $sub_item_classes ); ?>">
											<span><?php echo apply_filters( 'the_title', $subchild->title ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<a href="<?php echo esc_url( $child->url ); ?>" class="<?php imns( $link_classes ); ?>">
								<span><?php echo apply_filters( 'the_title', $child->title ); ?></span>
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
