<?php

require_once PLUGIN_PATH . '/includes/import-export/sanitizer/class-htaccess-sanitizer.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-encoder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-target-builder.php';

use Redirection\ImportExport\HtaccessEncoder;
use Redirection\ImportExport\HtaccessTargetBuilder;

class HtaccessTargetBuilderTest extends TestCase {
	private function get_match_data( array $source = [] ) {
		return [
			'source' => $source,
		];
	}

	public function testBuildUrlTargetIncludesRedirectCode() {
		$builder = new HtaccessTargetBuilder( new HtaccessEncoder() );

		$this->assertEquals(
			'/target [R=301,L]',
			$builder->build( 'url', '/target', 301, $this->get_match_data() )
		);
	}

	public function testBuildUrlTargetAddsFlags() {
		$builder = new HtaccessTargetBuilder( new HtaccessEncoder() );

		$this->assertEquals(
			'/target#hash [R=301,L,NC,QSA,NE]',
			$builder->build(
				'url',
				'/target#hash',
				301,
				$this->get_match_data(
					[
						'flag_case' => true,
						'flag_query' => 'pass',
					]
				)
			)
		);
	}

	public function testBuildErrorTargetUsesGoneFlagFor410() {
		$builder = new HtaccessTargetBuilder( new HtaccessEncoder() );

		$this->assertEquals(
			'/ [G]',
			$builder->build( 'error', '/ignored', 410, $this->get_match_data() )
		);
	}

	public function testBuildErrorTargetUsesForbiddenFlagFor403() {
		$builder = new HtaccessTargetBuilder( new HtaccessEncoder() );

		$this->assertEquals(
			'/ [F]',
			$builder->build( 'error', '/ignored', 403, $this->get_match_data() )
		);
	}

	public function testBuildErrorTargetUsesStatusCodeFor404() {
		$builder = new HtaccessTargetBuilder( new HtaccessEncoder() );

		$this->assertEquals(
			'- [R=404,L]',
			$builder->build( 'error', '/ignored', 404, $this->get_match_data() )
		);
	}

	public function testBuildErrorTargetUsesStatusCodeForOtherCodes() {
		$builder = new HtaccessTargetBuilder( new HtaccessEncoder() );

		$this->assertEquals(
			'- [R=500,L]',
			$builder->build( 'error', '/ignored', 500, $this->get_match_data() )
		);
	}

	public function testBuildPassTargetUsesLastFlag() {
		$builder = new HtaccessTargetBuilder( new HtaccessEncoder() );

		$this->assertEquals(
			'/target?value=1 [L]',
			$builder->build( 'pass', '/target?value=1', 200, $this->get_match_data() )
		);
	}

	public function testBuildRandomTargetUsesResolver() {
		$builder = new HtaccessTargetBuilder(
			new HtaccessEncoder(),
			static function () {
				return '/random-post';
			}
		);

		$this->assertEquals(
			'/random-post [R=302,L]',
			$builder->build( 'random', '', 302, $this->get_match_data() )
		);
	}
}
