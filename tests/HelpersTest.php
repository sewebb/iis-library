<?php

namespace IIS\Library\Tests;

use Exception;
use WP_Mock;
use WP_Mock\Tools\TestCase;

/**
 * This is the laravel mix test class.
 *
 * @author Tobias Bleckert <tobias.bleckert@internetstiftelsen.se>
 */
class HelpersTest extends TestCase
{
	/**
	 * Setup before tests
	 *
	 * @return void
	 */
	public function setUp(): void {
		WP_Mock::setUp();
	}

	/**
	 * Tear down after tests
	 *
	 * @return void
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	/**
	 * Test that iis_active_class works
	 */
	public function testActiveClass() {
		ob_start();

		iis_active_class( 'foo', 'foo', 'is-active', false );

		$output = ob_get_clean();

		$this->assertSame( 'is-active', $output );
	}
}
