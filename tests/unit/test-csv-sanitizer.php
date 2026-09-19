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

	private function write_row( array $row ) {
		$handle = fopen( 'php://memory', 'r+' );

		CsvSanitizer::put_row( $handle, $row );
		rewind( $handle );
		$line = stream_get_contents( $handle );
		fclose( $handle );

		return rtrim( $line, "\r\n" );
	}

	private function parse_as_spreadsheet( $line ) {
		return str_getcsv( $line, ',', '"', '' );
	}

	public function testPutRowKeepsBackslashQuotePayloadInOneColumn() {
		$payload = 'safe\\",=1+1,"';
		$columns = $this->parse_as_spreadsheet( $this->write_row( [ 'GET', $payload, 'referrer' ] ) );

		$this->assertCount( 3, $columns );
		$this->assertEquals( [ 'GET', $payload, 'referrer' ], $columns );
	}

	public function testPutRowDoesNotLeakAFormulaIntoItsOwnColumn() {
		$columns = $this->parse_as_spreadsheet( $this->write_row( [ 'GET', 'safe\\",=1+1,"', 'referrer' ] ) );

		$this->assertNotContains( '=1+1', $columns );
	}

	public function testPutRowContainsFormulaPayloadsHiddenBehindABackslashQuote() {
		$payloads = [
			'agent\\",=cmd|\' /c calc\'!A1,"',
			'http://example.com/\\",@SUM(1+1),"',
			'\\",-2+3+cmd|\' /c calc\'!A1,"',
			'trailing backslash\\',
			'"\\",=1+1,"',
		];

		foreach ( $payloads as $payload ) {
			$columns = $this->parse_as_spreadsheet( $this->write_row( [ 'a', $payload, 'b' ] ) );

			$this->assertCount( 3, $columns, 'Payload broke out of its column: ' . $payload );
			$this->assertEquals( $payload, $columns[1], 'Payload was altered: ' . $payload );
		}
	}

	public function testPutRowStillQuotesOrdinaryValues() {
		$this->assertEquals( 'a,"b,c",d', $this->write_row( [ 'a', 'b,c', 'd' ] ) );
		$this->assertEquals( 'a,"b""c",d', $this->write_row( [ 'a', 'b"c', 'd' ] ) );
	}

	public function testGetRowReadsBackWhatPutRowWrote() {
		$row = [ 'a', 'safe\\",=1+1,"', 'b\\', 'c"d' ];
		$handle = fopen( 'php://memory', 'r+' );

		CsvSanitizer::put_row( $handle, $row );
		rewind( $handle );
		$read = CsvSanitizer::get_row( $handle, ',' );
		fclose( $handle );

		$this->assertEquals( $row, $read );
	}
}
