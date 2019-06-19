<?php

if ( ! function_exists( 'iis_config' ) ) {
	/**
	 * IIS Start config helper
	 *
	 * @param  string $keys the key to get the value for. Use dot notation for going deeper.
	 * @return mixed     The value (if found) for the given key.
	 */
	function iis_config( $keys ) {
		$keys  = explode( '.', $keys );
		$value = include get_template_directory() . '/config.php';

		foreach ( $keys as $key ) {
			if ( isset( $value[ $key ] ) ) {
				$value = $value[ $key ];
			} else {
				$value = null;
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
	 * @param string   $cache_key  The name of the cached content
	 * @param int      $cache_time How long the content should be cached
	 * @param callable $callback   The callback that returns the content that should be cached
	 * @return mixed|null
	 */
	function iis_remember( $cache_key, $cache_time, $callback ) {
		$content = ( ! defined( 'WP_ENV' ) || 'production' != WP_ENV ) ? false : get_transient( $cache_key );

		if ( false === $content ) {
			$content = $callback();
			set_transient( $cache_key, $content, $cache_time );
		}

		return $content;
	}
}

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

	return wp_strip_all_tags( wp_unslash( $input ) );
}

/**
 * Return a safe GET value with an optional default value.
 *
 * @param string      $key The key for the $_GET array.
 * @param null|string $default Default value if GET variable doesn't exist.
 * @return null|string
 */
function iis_safe_get( $key, $default = null ) {
	if ( ! isset( $_GET[ $key ] ) ) {
		return $default;
	}

	return iis_safe_get_input( $_GET[ $key ] );
}

/**
 * Echo an "active"-class if the comparison is true, otherwise
 * an empty string.
 *
 * @param string $value The value to compare against.
 * @param string $compare_with The value to compare with.
 * @param string $class The class that should be echoed if true.
 * @param bool   $include_attr True if class attribute should be included.
 */
function iis_active_class( $value, $compare_with = null, $class = 'is-active', $include_attr = true ) {
	$attr = $include_attr ? 'class="' . esc_attr( $class ) . '"' : esc_attr( $class );

	echo iis_active( $value, $compare_with, $attr, true );
}

/**
 * Echo a string if the comparison is true, otherwise empty string
 *
 * @param string       $value The value to compare against.
 * @param string|array $compare_with The value to compare with.
 * @param string       $attr The attribute that should be echoed if $echo is true.
 * @param bool         $echo True if class attribute should be included.
 * @return bool|string|void
 */
function iis_active( $value, $compare_with = null, $attr = ' checked', $echo = true ) {
	$value = (string) $value;

	if ( is_null( $compare_with ) ) {
		$active = ! ! $value;
	} else {
		$active = ( is_array( $compare_with ) ) ? in_array( $value, array_map( 'strval', $compare_with ), true ) : $value === (string) $compare_with;
	}

	if ( ! $echo ) {
		return $active;
	}

	echo esc_html( ( $active ) ? $attr : '' );
}

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

/**
 * Get the path to a versioned Mix file
 *
 * @param $path
 * @param string $base
 * @return string|null
 */
function iis_mix( $path, $base = '/assets/' ) {
	$manifest = iis_mix_manifest();

	if ( ! $manifest ) {
		return null;
	}

	$path = $base . $path;

	if ( ! isset( $manifest[$path] ) ) {
		return null;
	}

	return $manifest[$path];
}
