<?php

class Log_404_Test extends WP_UnitTestCase {
	public function testCsvRow() {
		$row = [ 'created' => 'created', 'url' => 'url', 'ip' => 'ip', 'referrer' => 'referrer', 'agent' => 'agent' ];
		$expected = [ 'created', 'url', 'ip', 'referrer', 'agent' ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowReturnsRawFormulaValues() {
		$row = [ 'created' => '=created', 'url' => '@url', 'ip' => '-ip', 'referrer' => '-referrer', 'agent' => '+agent' ];
		$expected = [ '=created', '@url', '-ip', '-referrer', '+agent' ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowLeavesLeadingWhitespaceWithoutFormulaPrefixAlone() {
		$row = [ 'created' => "\thello", 'url' => "\nurl", 'ip' => "\rip", 'referrer' => "\treferrer", 'agent' => "\nagent" ];
		$expected = [ "\thello", "\nurl", "\rip", "\treferrer", "\nagent" ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}
}
