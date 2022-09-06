<?php

use Redirection\Match;
use Redirection\Url;

class FromNotFromTraitTest extends WP_UnitTestCase {
	public function testMatches() {
		$matches = [
			'\Redirection\Match\Cookie' => 'cookie.php',
			'\Redirection\Match\Custom' => 'custom-filter.php',
			'\Redirection\Match\Header' => 'http-header.php',
			'\Redirection\Match\IP' => 'ip.php',
			'\Redirection\Match\Referrer' => 'referrer.php',
			'\Redirection\Match\Server' => 'server.php',
			'\Redirection\Match\User_Agent' => 'user-agent.php',
			'\Redirection\Match\Role' => 'user-role.php',
		];

		foreach ( $matches as $klass => $file ) {
			require_once dirname( __FILE__ ) . '/../../includes/match/match-' . $file;

			// Test a match to from
			$match = new $klass( serialize( [ 'url_from' => '/from', 'url_notfrom' => '/notfrom' ] ) );
			$this->assertEquals( '/from', $match->get_target_url( '', '', new Url\Source_Flags(), true ), $klass );

			// Test no match to notfrom
			$match = new $klass( serialize( [ 'url_from' => '/from', 'url_notfrom' => '/notfrom' ] ) );
			$this->assertEquals( '/notfrom', $match->get_target_url( '', '', new Url\Source_Flags(), false ), $klass );

			// Test a match with no from
			$match = new $klass( serialize( [] ) );
			$this->assertEquals( false, $match->get_target_url( '', '', new Url\Source_Flags(), true ), $klass );

			// Test no match with no notfrom
			$match = new $klass( serialize( [] ) );
			$this->assertEquals( false, $match->get_target_url( '', '', new Url\Source_Flags(), false ), $klass );

			// Test a match with regex from
			$match = new $klass( serialize( [ 'url_from' => '/from/$1', 'url_notfrom' => '/notfrom/$1' ] ) );
			$this->assertEquals( '/from/1', $match->get_target_url( '/category/1', '/category/(.*?)', new Url\Source_Flags( [ 'flag_regex' => true ] ), true ), $klass );

			// Test no match with regex notfrom
			$match = new $klass( serialize( [ 'url_from' => '/from/$1', 'url_notfrom' => '/notfrom/$1' ] ) );
			$this->assertEquals( '/notfrom/1', $match->get_target_url( '/category/1', '/category/(.*?)', new Url\Source_Flags( [ 'flag_regex' => true ] ), false ), $klass );
		}
	}
}
