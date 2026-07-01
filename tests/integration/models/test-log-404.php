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
		$expected = [ "\t=created", "\t@url", "\t-ip", "\t-referrer", "\t+agent" ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowEscapesFormulaValuesWithLeadingWhitespace() {
		$row = [ 'created' => ' =created', 'url' => "\t@url", 'ip' => ' -ip', 'referrer' => "\r-referrer", 'agent' => "\n+agent" ];
		$expected = [ "\t =created", "\t\t@url", "\t -ip", "\t\r-referrer", "\t\n+agent" ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowEscapesFormulaValuesWithFullWidthPrefix() {
		$row = [ 'created' => '＝created', 'url' => '＋url', 'ip' => '－ip', 'referrer' => '＠referrer', 'agent' => '＝agent' ];
		$expected = [ "\t＝created", "\t＋url", "\t－ip", "\t＠referrer", "\t＝agent" ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testCsvRowEscapesFormulaValuesWithLeadingSpaceAndFullWidthPrefix() {
		$row = [ 'created' => ' ＝created', 'url' => ' ＋url', 'ip' => ' －ip', 'referrer' => ' ＠referrer', 'agent' => ' ＝agent' ];
		$expected = [ "\t ＝created", "\t ＋url", "\t －ip", "\t ＠referrer", "\t ＝agent" ];
		$csv = Red_404_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}
}
