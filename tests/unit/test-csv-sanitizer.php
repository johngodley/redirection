<?php

require_once PLUGIN_PATH . '/models/csv-sanitizer.php';

class CsvSanitizerTest extends TestCase {
	public function testLeavesSafeValueAlone() {
		$this->assertEquals( 'hello world', Red_Csv_Sanitizer::escape( 'hello world' ) );
	}

	public function testEscapesAsciiFormulaPrefix() {
		$this->assertEquals( "\t=SUM(A1:A2)", Red_Csv_Sanitizer::escape( '=SUM(A1:A2)' ) );
		$this->assertEquals( "\t@SUM(A1:A2)", Red_Csv_Sanitizer::escape( '@SUM(A1:A2)' ) );
	}

	public function testEscapesLeadingWhitespaceBeforeAsciiFormulaPrefix() {
		$this->assertEquals( "\t =SUM(A1:A2)", Red_Csv_Sanitizer::escape( ' =SUM(A1:A2)' ) );
		$this->assertEquals( "\t\t@SUM(A1:A2)", Red_Csv_Sanitizer::escape( "\t@SUM(A1:A2)" ) );
	}

	public function testEscapesFullWidthFormulaPrefix() {
		$this->assertEquals( "\t＝SUM(A1:A2)", Red_Csv_Sanitizer::escape( '＝SUM(A1:A2)' ) );
		$this->assertEquals( "\t ＝SUM(A1:A2)", Red_Csv_Sanitizer::escape( ' ＝SUM(A1:A2)' ) );
	}

	public function testEscapesMalformedUtf8FormulaPrefix() {
		$this->assertEquals( "\t=\xffSUM(A1:A2)", Red_Csv_Sanitizer::escape( "=\xffSUM(A1:A2)" ) );
	}

	public function testUnescapeRemovesSingleProtectionTab() {
		$this->assertEquals( '=SUM(A1:A2)', Red_Csv_Sanitizer::unescape( "\t=SUM(A1:A2)" ) );
		$this->assertEquals( "\t@SUM(A1:A2)", Red_Csv_Sanitizer::unescape( "\t\t@SUM(A1:A2)" ) );
	}

	public function testUnescapeLeavesSafeTabValueAlone() {
		$this->assertEquals( "\thello", Red_Csv_Sanitizer::unescape( "\thello" ) );
	}
}
