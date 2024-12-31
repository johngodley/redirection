<?php

class Log_Redirect_Test extends WP_UnitTestCase {
	public function testCsvRow() {
		$row = [ 'created' => 'created', 'url' => 'url', 'sent_to' => 'sent_to', 'ip' => 'ip', 'referrer' => 'referrer', 'agent' => 'agent' ];
		$expected = [ 'created', 'url', 'sent_to', 'ip', 'referrer', 'agent' ];
		$csv = Red_Redirect_Log::get_csv_row( (object) $row );

		$this->assertEquals( $expected, $csv );
	}

	public function testValidLog() {
		$log = Red_Redirect_Log::create( 'domain', 'url', '192.168.1.1', [
			'agent' => 'agent',
			'referrer' => 'referrer',
			'http_code' => 301,
			'request_method' => 'GET',
			'redirect_id' => 5,
			'target' => 'target',
			'redirect_by' => 'wordpress',
			'request_data' => [
				'cats' => 4,
			],
		] );

		$json = Red_Redirect_Log::get_by_id( $log )->to_json();
		$expected = [
			'url' => 'url',
			'agent' => 'agent',
			'referrer' => 'referrer',
			'domain' => 'domain',
			'ip' => '192.168.1.1',
			'http_code' => 301,
			'request_method' => 'GET',
			'request_data' => [
				'cats' => 4,
			],
			'sent_to' => 'target',
			'redirection_id' => 5,
			'redirect_by_slug' => 'wordpress',
			'redirect_by' => 'WordPress',
		];

		unset( $json['created'] );
		unset( $json['created_time'] );
		unset( $json['id'] );

		$this->assertEquals( $expected, $json );
	}

	public function testEmptyRedirectLog() {
		$log = Red_Redirect_Log::create( 'domain', 'url', '192.168.1.1', [
			'agent' => 'agent',
			'referrer' => 'referrer',
			'http_code' => 301,
			'request_method' => 'GET',
			'request_data' => [
				'cats' => 4,
			],
		] );

		$json = Red_Redirect_Log::get_by_id( $log )->to_json();
		$expected = [
			'url' => 'url',
			'agent' => 'agent',
			'referrer' => 'referrer',
			'domain' => 'domain',
			'ip' => '192.168.1.1',
			'http_code' => 301,
			'request_method' => 'GET',
			'request_data' => [
				'cats' => 4,
			],
			'sent_to' => '',
			'redirection_id' => 0,
			'redirect_by_slug' => '',
			'redirect_by' => '',
		];

		unset( $json['created'] );
		unset( $json['created_time'] );
		unset( $json['id'] );

		$this->assertEquals( $expected, $json );
	}

	public function testGoodQuery() {
		$query = [
			'page' => 4,
			'per_page' => 20,
			'direction' => 'asc',
			'orderby' => 'ip',
			'filterBy' => [
				'target' => 'target',
				'redirect_by' => 'redirect_by',
			],
		];
		$expected = [
			'orderby' => 'ip',
			'direction' => 'ASC',
			'limit' => 20,
			'offset' => 20 * 4,
			'where' => "WHERE sent_to LIKE '%target%' AND redirect_by = 'redirect_by'",
		];
		$query = Red_Redirect_Log::get_query( $query );
		$query['where'] = preg_replace( '/\{.*?\}/', '%', $query['where'] );

		$this->assertEquals( $expected, $query );
	}
}
