<?php

require_once PLUGIN_PATH . '/models/csv-sanitizer.php';

class CsvSanitizerTest extends TestCase {
	public function testLeavesSafeValueAlone() {
		$this->assertEquals( 'hello world', Red_Csv_Sanitizer::escape( 'hello world' ) );
	}

	public function testEscapeHandlesNull() {
		$this->assertEquals( '', Red_Csv_Sanitizer::escape( null ) );
	}

	public function testEscapesAsciiFormulaPrefix() {
		$this->assertEquals( '[FORMULA] =SUM(A1:A2)', Red_Csv_Sanitizer::escape( '=SUM(A1:A2)' ) );
		$this->assertEquals( '[FORMULA] @SUM(A1:A2)', Red_Csv_Sanitizer::escape( '@SUM(A1:A2)' ) );
	}

	public function testEscapesLeadingWhitespaceBeforeAsciiFormulaPrefix() {
		$this->assertEquals( '[FORMULA]  =SUM(A1:A2)', Red_Csv_Sanitizer::escape( ' =SUM(A1:A2)' ) );
		$this->assertEquals( "[FORMULA] \t@SUM(A1:A2)", Red_Csv_Sanitizer::escape( "\t@SUM(A1:A2)" ) );
	}

	public function testLeavesLeadingWhitespaceWithoutFormulaPrefixAlone() {
		$this->assertEquals( "\thello", Red_Csv_Sanitizer::escape( "\thello" ) );
		$this->assertEquals( "\nhello", Red_Csv_Sanitizer::escape( "\nhello" ) );
		$this->assertEquals( "\rhello", Red_Csv_Sanitizer::escape( "\rhello" ) );
	}

	public function testEscapesFullWidthFormulaPrefix() {
		$this->assertEquals( '[FORMULA] ＝SUM(A1:A2)', Red_Csv_Sanitizer::escape( '＝SUM(A1:A2)' ) );
		$this->assertEquals( '[FORMULA]  ＝SUM(A1:A2)', Red_Csv_Sanitizer::escape( ' ＝SUM(A1:A2)' ) );
	}

	public function testEscapesMalformedUtf8FormulaPrefix() {
		$this->assertEquals( "[FORMULA] =\xffSUM(A1:A2)", Red_Csv_Sanitizer::escape( "=\xffSUM(A1:A2)" ) );
	}

	public function testUnescapeRemovesProtectionPrefix() {
		$this->assertEquals( '=SUM(A1:A2)', Red_Csv_Sanitizer::unescape( '[FORMULA] =SUM(A1:A2)' ) );
		$this->assertEquals( "\t@SUM(A1:A2)", Red_Csv_Sanitizer::unescape( "[FORMULA] \t@SUM(A1:A2)" ) );
	}

	public function testUnescapeLeavesSafePrefixedValueAlone() {
		$this->assertEquals( '[FORMULA] hello', Red_Csv_Sanitizer::unescape( '[FORMULA] hello' ) );
	}

	public function testUnescapeHandlesNull() {
		$this->assertEquals( '', Red_Csv_Sanitizer::unescape( null ) );
	}
}
