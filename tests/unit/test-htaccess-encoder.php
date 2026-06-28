<?php

require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-encoder.php';

use Redirection\ImportExport\HtaccessEncoder;

class HtaccessEncoderTest extends TestCase {
	public function testEncodeFromSupportsTrailingSlashIgnore() {
		$encoder = new HtaccessEncoder();

		$this->assertEquals( '^path/?$', $encoder->encode_from( '/path', true ) );
	}

	public function testEncodeTargetPreservesEscapedTargetCharacters() {
		$encoder = new HtaccessEncoder();

		$this->assertEquals( '/target?test=1&thing=2%20', $encoder->encode_target( '/target?test=1&thing=2 ' ) );
	}

	public function testEncodeRegexPreservesOptionalRegexSuffix() {
		$encoder = new HtaccessEncoder();

		$this->assertEquals( '^contact-us(/.*)?$', $encoder->encode_regex( '^/contact-us(/.*)?$' ) );
	}

	public function testSanitizeRedirectRemovesUnsafeSequences() {
		$encoder = new HtaccessEncoder();

		$this->assertEquals( 'RewriteRuletestalert(1)', $encoder->sanitize_redirect( "RewriteRule\ttest\t<?alert(1)>" ) );
	}
}
