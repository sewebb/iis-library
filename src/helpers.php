<?php

if ( ! function_exists( 'iis_config' ) ) {
	/**
	 * IIS Start config helper
	 *
	 * @param string $keys         the key to get the value for. Use dot notation for going deeper.
	 * @param mixed|null $fallback fallback if value is missing
	 * @return mixed     The value (if found) for the given key.
	 */
	function iis_config( string $keys, $fallback = null ) {
		$keys  = explode( '.', $keys );
		$value = include get_template_directory() . '/config.php';

		foreach ( $keys as $key ) {
			if ( isset( $value[ $key ] ) ) {
				$value = $value[ $key ];
			} else {
				$value = $fallback;
				break;
			}
		}

		return $value;
	}
}

if ( ! function_exists( 'iis_remember' ) ) {
	/**
	 * Cache return value of callback if not already cached and return the contents
	 *
	 * @param string   $cache_key  The name of the cached content.
	 * @param int      $cache_time How long the content should be cached.
	 * @param callable $callback   The callback that returns the content that should be cached.
	 * @return mixed|null
	 */
	function iis_remember( $cache_key, $cache_time, $callback ) {
		$content = ( ! defined( 'WP_ENV' ) || 'production' !== WP_ENV ) ? false : get_transient( $cache_key );

		if ( false === $content ) {
			$content = $callback();
			set_transient( $cache_key, $content, $cache_time );
		}

		return $content;
	}
}

if ( ! function_exists( 'iis_safe_get_input' ) ) {
	/**
	 * Escape input recursively
	 *
	 * @param mixed $input Input parameter.
	 * @return array|string
	 */
	function iis_safe_get_input( $input ) {
		if ( is_array( $input ) ) {
			return array_map(
				function ( $value ) {
					return iis_safe_get_input( $value );
				},
				$input
			);
		}

		return sanitize_text_field( wp_unslash( $input ) );
	}
}

if ( ! function_exists( 'iis_safe_get' ) ) {
	/**
	 * Return a safe GET value with an optional default value.
	 *
	 * @param string      $key The key for the $_GET array.
	 * @param null|string $default Default value if GET variable doesn't exist.
	 * @return null|string
	 */
	function iis_safe_get( $key, $default = null ) {
		// Ignore nonce check for $_GET variables.
		// phpcs:disable
		if ( ! isset( $_GET[ $key ] ) ) {
			return $default;
		}

		return iis_safe_get_input( $_GET[ $key ] );
		// phpcs:enable
	}
}

if ( ! function_exists( 'iis_active_class' ) ) {
	/**
	 * Echo an "active"-class if the comparison is true, otherwise
	 * an empty string.
	 *
	 * @param string $value The value to compare against.
	 * @param string $compare_with The value to compare with.
	 * @param string $class The class that should be echoed if true.
	 * @param bool   $include_attr True if class attribute should be included.
	 * @return void
	 */
	function iis_active_class( $value, $compare_with = null, $class = 'is-active', $include_attr = true ) {
		$attr = $include_attr ? 'class="' . esc_attr( $class ) . '"' : esc_attr( $class );

		iis_active( $value, $compare_with, $attr, true );
	}
}

if ( ! function_exists( 'iis_active' ) ) {
	/**
	 * Echo a string if the comparison is true, otherwise empty string
	 *
	 * @param string       $value The value to compare against.
	 * @param string|array $compare_with The value to compare with.
	 * @param string       $attr The attribute that should be echoed if $echo is true.
	 * @param bool         $echo True if class attribute should be included.
	 * @return bool|string|void
	 */
	function iis_active( $value, $compare_with = null, $attr = 'checked', $echo = true ) {
		$value = (string) $value;

		if ( is_null( $compare_with ) ) {
			$active = ! ! $value;
		} else {
			$active = ( is_array( $compare_with ) ) ? in_array( $value, array_map( 'strval', $compare_with ), true ) : $value === (string) $compare_with;
		}

		if ( ! $echo ) {
			return $active;
		}

		echo esc_html( ( $active ) ? ' ' . $attr : '' );
	}
}

if ( ! function_exists( 'iis_mix_manifest' ) ) {
	/**
	 * Get the laravel mix manifest
	 *
	 * @return array|null
	 */
	function iis_mix_manifest() {
		$mix_manifest_content = iis_remember(
			'mix_manifest_transient',
			1 * DAY_IN_SECONDS,
			function () {
				return file_get_contents( get_template_directory() . '/mix-manifest.json' );
			}
		);

		try {
			$mix_manifest = json_decode( $mix_manifest_content, true );
		} catch ( Exception $e ) {
			$mix_manifest = null;
		}

		return $mix_manifest;
	}
}

if ( ! function_exists( 'iis_mix' ) ) {
	/**
	 * Get the path to a versioned Mix file
	 *
	 * @param string $path Path tp mix manifest.
	 * @param string $base Base path to scripts.
	 * @return string|null
	 */
	function iis_mix( $path, $base = '/assets/' ) {
		$manifest = iis_mix_manifest();

		if ( ! $manifest ) {
			return null;
		}

		$path = $base . $path;

		if ( ! isset( $manifest[ $path ] ) ) {
			return null;
		}

		return $manifest[ $path ];
	}
}

if ( ! function_exists( 'iis_get_hero' ) ) {
	/**
	 * Get the content hero
	 *
	 * @return mixed
	 */
	function iis_get_hero() {
		$content = get_the_content();

		if ( has_blocks( $content ) ) {
			$blocks = parse_blocks( $content );

			if ( 'iis/hero' === $blocks[0]['blockName'] ) {
				return $blocks[0];
			}
		}

		return null;
	}
}

if ( ! function_exists( 'iis_has_hero' ) ) {
	/**
	 * Checks if content starts with a hero
	 *
	 * @return bool
	 */
	function iis_has_hero() {
		$hero = iis_get_hero();

		return null !== $hero;
	}
}

if ( ! function_exists( 'iis_has_full_hero' ) ) {
	/**
	 * Checks if content starts with a full-width hero
	 *
	 * @return bool
	 */
	function iis_has_full_hero() {
		$hero = iis_get_hero();

		return $hero && 'full' === ( $hero['attrs']['align'] ?? 'wide' );
	}
}

if ( ! function_exists( 'imns' ) ) {
	/**
	 * Get and echo the styleguide namespace, set in .env-file of the theme
	 *
	 * @param string $class Class names, separated by space
	 * @param bool   $echo  true for echo and false to return the string
	 * @return void|string
	 */
	function imns( $class, $echo = true ) {
		$namespace = apply_filters( 'iis_blocks_namespace', env( 'IIS_NAMESPACE', 'iis-' ) );
		$classes   = array_map(
			function ( $single_class ) use ( $namespace ) {
				if ( strpos( $single_class, '!' ) === 0 ) {
					return substr( $single_class, 1 );
				}

				return $namespace . $single_class;
			},
			explode( ' ', $class )
		);

		$classes = implode( ' ', $classes );

		if ( $echo ) {
			echo esc_attr( $classes );

			return;
		}

		return $classes;
	}
}

if ( ! function_exists( 'iis_uses_styleguide' ) ) {
	/**
	 * Check if theme is using the styleguide
	 *
	 * @return bool
	 */
	function iis_uses_styleguide() {
		if ( ! defined( 'PROJECT_ROOT' ) ) {
			return false;
		}

		return file_exists( PROJECT_ROOT . '/node_modules/@internetstiftelsen/styleguide' );
	}
}

if ( ! function_exists( 'iis_styleguide_sprite' ) ) {
	/**
	 * Print IIS styleguide icon sprite
	 *
	 * @return void
	 */
	function iis_styleguide_sprite() {
		echo iis_remember(
			'iis_styleguide_sprite',
			0,
			function () {
				if ( iis_uses_styleguide() ) {
					return file_get_contents( PROJECT_ROOT . '/node_modules/@internetstiftelsen/styleguide/src/atoms/icon/sprite.svg' );
				}

				return null;
			}
		);
	}
}


if ( ! function_exists( 'iis_word_count' ) ) {
	/**
	 * Count number of words in string.
	 * Multibyte version because str_word_count does not work with Swedish chars.
	 * https://stackoverflow.com/questions/8290537/is-php-str-word-count-multibyte-safe
	 *
	 * @param $str
	 * @return false|int|null
	 */
	function iis_word_count( $str ) {
		$str = wp_strip_all_tags( $str );

		return preg_match_all( '~[\p{L}\'\-\xC2\xAD]+~u', $str );
	}
}

if ( ! function_exists( 'iis_get_post_reading_time' ) ) {
	/**
	 * Get reading time for a post, in minutes.
	 * Uses the calculation from https://blog.medium.com/read-time-and-you-bc2048ab620c.
	 *
	 * @param WP_Post|object|int $post_id
	 * @return float|false
	 */
	function iis_get_post_reading_time( $post_id ) {
		// $reading_time_post = get_post($post_id);
		// Get the content and apply content filter so Gutenberg blocks are parsed.
		$content_html = get_the_content( null, false, $post_id );
		$content_html = apply_filters( 'the_content', $content_html );

		// Get number of images in text.
		if ( $content_html ) {
			$document = new DOMDocument();
			libxml_use_internal_errors( true );
			$document->loadHTML( $content_html );
			$images = $document->getElementsByTagName( 'img' );
			libxml_clear_errors();
			$images_count = count( $images );
		} else {
			$images_count = 0;
		}

		$words_count  = 0;
		$words_count += iis_word_count( get_the_title() );
		$words_count += iis_word_count( $content_html );

		$words_per_minute = 275;

		// Reading time for the text.
		$reading_time = $words_count / $words_per_minute;

		// Add reading time for images.
		$images_reading_time = iis_get_images_reading_time( $images_count, $words_per_minute );
		$reading_time       += $images_reading_time;

		// Check for grahps.
		if ( $content_html ) {
			$document = new DOMDocument();
			libxml_use_internal_errors( true );
			$document->loadHTML( $content_html );

			$xpath  = new \DOMXpath( $document );
			$graphs = $xpath->query( '//div[@class="wp-block-iis-graph"]' );

			libxml_clear_errors();
			$graphs_count = count( $graphs );

			// Each graph adds 10 seconds to reading time.
			$reading_time = $reading_time + ( $graphs_count * ( 10 / 60 ) );
		}

		return ceil( $reading_time );
	}
}

if ( ! function_exists( 'iis_get_images_reading_time' ) ) {
	/**
	 * Calculate reading time for images in minutes.
	 *
	 * Based on function from
	 * https://github.com/yingles/reading-time-wp/blob/master/rt-reading-time.php
	 *
	 * @param int $total_images number of images in post.
	 * @param int $wpm words per minute.
	 * @return int  Additional time in minutes added to the reading time by images.
	 */
	function iis_get_images_reading_time( int $total_images, int $wpm ) {
		$additional_time = 0;
		// For the first image add 12 seconds, second image add 11, ..., for image 10+ add 3 seconds.
		for ( $i = 1; $i <= $total_images; $i++ ) {
			if ( $i >= 10 ) {
				$additional_time += 3 * $wpm / 60;
			} else {
				$additional_time += ( 12 - ( $i - 1 ) ) * $wpm / 60;
			}
		}

		return $additional_time / $wpm;
	}
}
