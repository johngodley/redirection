<?php

class FromNotFromTraitTest extends WP_UnitTestCase {
	public function testMatches() {
		$matches = [
			'Cookie_Match' => 'cookie.php',
			'Custom_Match' => 'custom-filter.php',
			'Header_Match' => 'http-header.php',
			'IP_Match' => 'ip.php',
			'Referrer_Match' => 'referrer.php',
			'Server_Match' => 'server.php',
			'Agent_Match' => 'user-agent.php',
			'Role_Match' => 'user-role.php',
		];

		foreach ( $matches as $klass => $file ) {
			require_once dirname( __FILE__ ) . '/../../matches/' . $file;

			// Test a match to from
			$match = new $klass( serialize( [ 'url_from' => '/from', 'url_notfrom' => '/notfrom' ] ) );
			$this->assertEquals( '/from', $match->get_target_url( '', '', new Red_Source_Flags(), true ), $klass );

			// Test no match to notfrom
			$match = new $klass( serialize( [ 'url_from' => '/from', 'url_notfrom' => '/notfrom' ] ) );
			$this->assertEquals( '/notfrom', $match->get_target_url( '', '', new Red_Source_Flags(), false ), $klass );

			// Test a match with no from
			$match = new $klass( serialize( [] ) );
			$this->assertEquals( false, $match->get_target_url( '', '', new Red_Source_Flags(), true ), $klass );

			// Test no match with no notfrom
			$match = new $klass( serialize( [] ) );
			$this->assertEquals( false, $match->get_target_url( '', '', new Red_Source_Flags(), false ), $klass );

			// Test a match with regex from
			$match = new $klass( serialize( [ 'url_from' => '/from/$1', 'url_notfrom' => '/notfrom/$1' ] ) );
			$this->assertEquals( '/from/1', $match->get_target_url( '/category/1', '/category/(.*?)', new Red_Source_Flags( [ 'flag_regex' => true ] ), true ), $klass );

			// Test no match with regex notfrom
			$match = new $klass( serialize( [ 'url_from' => '/from/$1', 'url_notfrom' => '/notfrom/$1' ] ) );
			$this->assertEquals( '/notfrom/1', $match->get_target_url( '/category/1', '/category/(.*?)', new Red_Source_Flags( [ 'flag_regex' => true ] ), false ), $klass );
		}
	}
}
