<?php

require_once PLUGIN_PATH . '/includes/import-export/class-format-handler.php';
require_once PLUGIN_PATH . '/includes/import-export/class-export-details.php';
require_once PLUGIN_PATH . '/includes/import-export/class-file-reader.php';
require_once PLUGIN_PATH . '/includes/import-export/sanitizer/class-htaccess-sanitizer.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-encoder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-target-builder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-rule-builder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-apache.php';

use Redirection\ImportExport\Format\Apache;

class ApacheFormatTest extends TestCase {
	public function testRewriteRuleParsesStandardRule() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule /thing /newthing [301,L]' );

		$this->assertEquals( '/thing', $item['url'] );
		$this->assertEquals( [ 'url' => '/newthing' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
	}

	public function testRedirectPermanentParsesQuotedSource() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'Redirect permanent "/products/space thing/again thing.html" /products/again-thing/' );

		$this->assertEquals( '/products/space thing/again thing.html', $item['url'] );
		$this->assertEquals( [ 'url' => '/products/again-thing/' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
	}

	public function testRedirectWithoutCodeDefaultsTo302() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'Redirect /thing /newthing' );

		$this->assertEquals( '/thing', $item['url'] );
		$this->assertEquals( [ 'url' => '/newthing' ], $item['action_data'] );
		$this->assertEquals( 302, $item['action_code'] );
	}

	public function testRedirectMatchParsesAsRegex() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RedirectMatch permanent /thing /newthing' );

		$this->assertEquals( '/thing', $item['url'] );
		$this->assertEquals( [ 'url' => '/newthing' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
		$this->assertTrue( $item['regex'] );
	}

	public function testRewriteRuleWithoutExplicitCodeDefaultsTo302() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule ^products/reporting$ /products/ [R,L]' );

		$this->assertEquals( '/products/reporting', $item['url'] );
		$this->assertEquals( [ 'url' => '/products/' ], $item['action_data'] );
		$this->assertEquals( 302, $item['action_code'] );
	}

	public function testRewriteRulePreservesRegexSuffix() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule ^/contact-us(/.*)?$ /contact/ [R=301,L]' );

		$this->assertEquals( '^/contact-us(/.*)?$', $item['url'] );
		$this->assertTrue( $item['regex'] );
		$this->assertEquals( [ 'url' => '/contact/' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
	}

	public function testRewriteRulePreservesEscapedDollar() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule ^prices\\$$ /prices/ [R=301,L]' );

		$this->assertEquals( '^/prices\\$$', $item['url'] );
		$this->assertTrue( $item['regex'] );
		$this->assertEquals( [ 'url' => '/prices/' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
	}

	public function testRewriteRuleUnescapesLiteralDots() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule ^wp-admin/tools\.php/?$ /xfds [R=301,L,NC,QSA]' );

		$this->assertEquals( '/wp-admin/tools.php', $item['url'] );
		$this->assertFalse( $item['regex'] );
		$this->assertEquals( [ 'url' => '/xfds' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
		$this->assertEquals(
			[
				'source' => [
					'flag_case' => true,
					'flag_query' => 'pass',
					'flag_trailing' => true,
				],
			],
			$item['match_data']
		);
	}

	public function testRewriteRuleWithOptionalTrailingSlashImportsAsStandardRedirect() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule ^uncategorized/hello-world/?$ http://latest.local/uncategorized/hello-world2/ [R=301,L,NC,QSA]' );

		$this->assertEquals( '/uncategorized/hello-world', $item['url'] );
		$this->assertFalse( $item['regex'] );
		$this->assertEquals( [ 'url' => 'http://latest.local/uncategorized/hello-world2/' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
		$this->assertEquals(
			[
				'source' => [
					'flag_case' => true,
					'flag_query' => 'pass',
					'flag_trailing' => true,
				],
			],
			$item['match_data']
		);
	}

	public function testRewriteRulePreservesEscapedLiteralDotsForRegexPatterns() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule ^foo\.bar$ /target [R=301,L]' );

		$this->assertEquals( '^/foo\.bar$', $item['url'] );
		$this->assertTrue( $item['regex'] );
		$this->assertEquals( [ 'url' => '/target' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
	}
}
