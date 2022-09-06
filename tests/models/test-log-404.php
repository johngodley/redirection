<?php

class Log_404_Test extends WP_UnitTestCase {
	public function testCsvRow() {
		$row = [ 'created' => 'created', 'url' => 'url', 'ip' => 'ip', 'referrer' => 'referrer', 'agent' => 'agent' ];
		$expected = [ 'created', 'url', 'ip', 'referrer', 'agent' ];
		$csv = Log\Error::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}
}
