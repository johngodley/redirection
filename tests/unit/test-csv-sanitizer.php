<?php

require_once PLUGIN_PATH . '/includes/import-export/sanitizer/class-csv-sanitizer.php';

use Redirection\ImportExport\Sanitizer\CsvSanitizer;

class CsvSanitizerTest extends TestCase {
	private function get_sanitizer() {
		return new CsvSanitizer();
	}

	public function testLeavesSafeValueAlone() {
		$this->assertEquals( 'hello world', $this->get_sanitizer()->escape( 'hello world' ) );
	}

	public function testEscapeHandlesNull() {
		$this->assertEquals( '', $this->get_sanitizer()->escape( null ) );
	}

	public function testEscapesAsciiFormulaPrefix() {
		$this->assertEquals( '[FORMULA] =SUM(A1:A2)', $this->get_sanitizer()->escape( '=SUM(A1:A2)' ) );
		$this->assertEquals( '[FORMULA] @SUM(A1:A2)', $this->get_sanitizer()->escape( '@SUM(A1:A2)' ) );
	}

	public function testEscapesLeadingWhitespaceBeforeAsciiFormulaPrefix() {
		$this->assertEquals( '[FORMULA]  =SUM(A1:A2)', $this->get_sanitizer()->escape( ' =SUM(A1:A2)' ) );
		$this->assertEquals( "[FORMULA] \t@SUM(A1:A2)", $this->get_sanitizer()->escape( "\t@SUM(A1:A2)" ) );
	}

	public function testLeavesLeadingWhitespaceWithoutFormulaPrefixAlone() {
		$this->assertEquals( "\thello", $this->get_sanitizer()->escape( "\thello" ) );
		$this->assertEquals( "\nhello", $this->get_sanitizer()->escape( "\nhello" ) );
		$this->assertEquals( "\rhello", $this->get_sanitizer()->escape( "\rhello" ) );
	}

	public function testEscapesFullWidthFormulaPrefix() {
		$this->assertEquals( '[FORMULA] ＝SUM(A1:A2)', $this->get_sanitizer()->escape( '＝SUM(A1:A2)' ) );
		$this->assertEquals( '[FORMULA]  ＝SUM(A1:A2)', $this->get_sanitizer()->escape( ' ＝SUM(A1:A2)' ) );
	}

	public function testEscapesMalformedUtf8FormulaPrefix() {
		$this->assertEquals( "[FORMULA] =\xffSUM(A1:A2)", $this->get_sanitizer()->escape( "=\xffSUM(A1:A2)" ) );
	}

	public function testEscapesExistingProtectionPrefix() {
		$this->assertEquals( '[FORMULA] [FORMULA] =SUM(A1:A2)', $this->get_sanitizer()->escape( '[FORMULA] =SUM(A1:A2)' ) );
	}

	public function testUnescapeRemovesProtectionPrefix() {
		$this->assertEquals( '=SUM(A1:A2)', $this->get_sanitizer()->unescape( '[FORMULA] =SUM(A1:A2)' ) );
		$this->assertEquals( "\t@SUM(A1:A2)", $this->get_sanitizer()->unescape( "[FORMULA] \t@SUM(A1:A2)" ) );
	}

	public function testUnescapeRestoresEscapedProtectionPrefix() {
		$this->assertEquals( '[FORMULA] =SUM(A1:A2)', $this->get_sanitizer()->unescape( '[FORMULA] [FORMULA] =SUM(A1:A2)' ) );
	}

	public function testUnescapeLeavesSafePrefixedValueAlone() {
		$this->assertEquals( '[FORMULA] hello', $this->get_sanitizer()->unescape( '[FORMULA] hello' ) );
	}

	public function testUnescapeHandlesNull() {
		$this->assertEquals( '', $this->get_sanitizer()->unescape( null ) );
	}
}
