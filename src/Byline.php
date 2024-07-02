<?php

namespace Internetstiftelsen;

use WP_Post;

/**
 * Byline and review functionality
 */
class Byline {
	/**
	 * Enable review functionality.
	 *
	 * @param null|array $location
	 * @return void
	 */
	public static function enableReview( $location = null ) {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			trigger_error( 'You need to enable the plugin Advanced Custom Fields.', E_USER_WARNING );

			return;
		}

		add_action(
			'acf/init',
			function () use ( $location ) {
				if ( $location ) {
					$location = array_map(
						function ( $post_type ) {
							return [
								'param'    => 'post_type',
								'operator' => '==',
								'value'    => $post_type,
							];
						},
						$location
					);
				} else {
					$location = [
						[
							'param'    => 'post_type',
							'operator' => '!=',
							'value'    => 'allow_all',
						],
					];
				}

				$location = [ $location ];

				acf_add_local_field_group(
					[
						'key'                   => 'group_60742c1d479af',
						'title'                 => __( 'Updated and reviewed', 'iis-library' ),
						'fields'                => [
							[
								'key'               => 'field_60742c276dd20',
								'label'             => __( 'Reviewed by', 'iis-library' ),
								'name'              => 'reviewed_by',
								'type'              => 'user',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => [
									'width' => '',
									'class' => '',
									'id'    => '',
								],
								'role'              => '',
								'allow_null'        => 0,
								'multiple'          => 1,
								'return_format'     => 'array',
							],
							[
								'key'               => 'field_60742d1357762',
								'label'             => __( 'Updated at', 'iis-library' ),
								'name'              => 'updated_at',
								'type'              => 'date_time_picker',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => [
									'width' => '',
									'class' => '',
									'id'    => '',
								],
								'display_format'    => 'Y-m-d H:i:s',
								'return_format'     => 'Y-m-d H:i:s',
								'first_day'         => 1,
							],
						],
						'location'              => $location,
						'menu_order'            => 0,
						'position'              => 'side',
						'style'                 => 'default',
						'label_placement'       => 'top',
						'instruction_placement' => 'label',
						'hide_on_screen'        => '',
						'active'                => true,
						'description'           => '',
					]
				);
			}
		);
	}

	/**
	 * Render byline
	 *
	 * @param string[] $options
	 * @return string
	 */
	public static function render( $options = [ 'date', 'reviewed_by', 'updated_at', 'avatar', 'author_name', 'author_link' ], int $post_id = null ): string {
		if ( ! $post_id ) {
			global $post;

			$post_id  = get_the_ID();
			$the_post = $post;
		} else {
			$the_post = get_post( $post_id );
		}

		$author_id        = $the_post->post_author;
		$author_name      = get_the_author_meta( 'display_name', $author_id );
		$avatar           = get_avatar( $author_id, 64, 'https://static.internetstiftelsen.se/images/transparent-avatar.png', '', [ 'class' => imns( 'm-byline__portrait', false ) ] );
		$updated_at       = ( in_array( 'updated_at', $options, true ) ) ? get_post_meta( $post_id, 'updated_at', true ) : null;
		$reviewed_by      = ( in_array( 'reviewed_by', $options, true ) ) ? get_field( 'reviewed_by', $post_id ) : null;
		$show_avatar      = in_array( 'avatar', $options );
		$show_author_name = in_array( 'author_name', $options );
		$show_author_link = in_array( 'author_link', $options );

		ob_start();
		?>
		<div class="<?php imns( 'm-byline' ); ?> u-m-b-4">
			<?php
			if ( $show_avatar ) {
				echo $avatar;
			}
			?>

			<?php if ( $show_author_name ) : ?>
				<span class="<?php imns( 'a-meta' ); ?>"><span class="small"><?php _e( 'Author', 'iis-library' ); ?></span></span>
				<?php if ( $show_author_link ) : ?>
					<a href="<?php echo get_author_posts_url( $author_id ); ?>" class="<?php imns( 'm-byline__link' ); ?>" title="<?php esc_attr_e( 'View all articles by author', 'iis-library' ); ?>"><?php echo esc_html( $author_name ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $author_name ); ?>
				<?php endif; ?>
			<?php endif; ?>
			<ul class="<?php imns( 'm-byline__list' ); ?>">
				<?php if ( in_array( 'date', $options, true ) ) : ?>
				<li class="<?php imns( 'm-byline__list__item' ); ?>">
					<strong><?php _e( 'Published:', 'iis-library' ); ?></strong> <?php echo get_the_date( '', $the_post ); ?>
				</li>
				<?php endif; ?>
				<?php if ( $updated_at ) : ?>
				<li class="<?php imns( 'm-byline__list__item' ); ?>">
					<strong><?php _e( 'Updated at', 'iis-library' ); ?>:</strong> <?php echo wp_date( get_option( 'date_format' ), strtotime( $updated_at ) ); ?>
				</li>
				<?php endif; ?>
				<?php

				if ( $reviewed_by ) :
					foreach ( $reviewed_by as $reviewer ) :
						$name = $reviewer['user_firstname'] . ' ' . $reviewer['user_lastname'];

						if ( ! empty( $reviewer['user_description'] ) ) {
							$name .= ', ' . $reviewer['user_description'];
						}

				?>
				<li class="<?php imns( 'm-byline__list__item' ); ?>">
					<strong><?php _e( 'Reviewed by', 'iis-library' ); ?>:</strong> <?php echo esc_html( $name ); ?>
				</li>
				<?php
				endforeach;
endif;
?>
			</ul>
		</div>
		<?php

		return str_replace( [ "\t", "\n", "\r" ], '', ob_get_clean() );
	}
}
