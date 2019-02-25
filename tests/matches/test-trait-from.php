<?php

class FromTraitTest extends WP_UnitTestCase {
	public function testMatches() {
		$matches = [
			'Page_Match' => 'page.php',
		];

		foreach ( $matches as $klass => $file ) {
			require_once dirname( __FILE__ ) . '/../../matches/' . $file;

			// Test URL when matched
			$match = new $klass( serialize( [ 'url' => '/url' ] ) );
			$this->assertEquals( '/url', $match->get_target_url( '', '', new Red_Source_Flags(), true ), $klass );

			// Test false when not matched
			$match = new $klass( serialize( [ 'url' => '/url' ] ) );
			$this->assertEquals( false, $match->get_target_url( '', '', new Red_Source_Flags(), false ), $klass );

			// Test regex URL when matched
			$match = new $klass( serialize( [ 'url' => '/url/$1' ] ) );
			$this->assertEquals( '/url/1', $match->get_target_url( '/category/1', '/category/(.*?)', new Red_Source_Flags( [ 'flag_regex' => true ] ), true ), $klass );
		}
	}
}
