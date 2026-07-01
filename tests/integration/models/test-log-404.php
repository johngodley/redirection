<?php

class Log_404_Test extends WP_UnitTestCase {
	public function testCsvRow() {
		$row = [ 'created' => 'created', 'url' => 'url', 'ip' => 'ip', 'referrer' => 'referrer', 'agent' => 'agent' ];
		$expected = [ 'created', 'url', 'ip', 'referrer', 'agent' ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowEscapesFormulaValues() {
		$row = [ 'created' => '=created', 'url' => '@url', 'ip' => '-ip', 'referrer' => '-referrer', 'agent' => '+agent' ];
		$expected = [ '[FORMULA] =created', '[FORMULA] @url', '[FORMULA] -ip', '[FORMULA] -referrer', '[FORMULA] +agent' ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowEscapesFormulaValuesWithLeadingWhitespace() {
		$row = [ 'created' => ' =created', 'url' => "\t@url", 'ip' => ' -ip', 'referrer' => "\r-referrer", 'agent' => "\n+agent" ];
		$expected = [ '[FORMULA]  =created', "[FORMULA] \t@url", '[FORMULA]  -ip', "[FORMULA] \r-referrer", "[FORMULA] \n+agent" ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowLeavesLeadingWhitespaceWithoutFormulaPrefixAlone() {
		$row = [ 'created' => "\thello", 'url' => "\nurl", 'ip' => "\rip", 'referrer' => "\treferrer", 'agent' => "\nagent" ];
		$expected = [ "\thello", "\nurl", "\rip", "\treferrer", "\nagent" ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowEscapesFormulaValuesWithFullWidthPrefix() {
		$row = [ 'created' => '＝created', 'url' => '＋url', 'ip' => '－ip', 'referrer' => '＠referrer', 'agent' => '＝agent' ];
		$expected = [ '[FORMULA] ＝created', '[FORMULA] ＋url', '[FORMULA] －ip', '[FORMULA] ＠referrer', '[FORMULA] ＝agent' ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowEscapesFormulaValuesWithLeadingSpaceAndFullWidthPrefix() {
		$row = [ 'created' => ' ＝created', 'url' => ' ＋url', 'ip' => ' －ip', 'referrer' => ' ＠referrer', 'agent' => ' ＝agent' ];
		$expected = [ '[FORMULA]  ＝created', '[FORMULA]  ＋url', '[FORMULA]  －ip', '[FORMULA]  ＠referrer', '[FORMULA]  ＝agent' ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}
}
