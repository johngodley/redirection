<?php

class HeaderTest extends WP_UnitTestCase {
	public function testNoHeaders() {
		$http = new Site\Http_Headers();
		$this->assertEquals( [], $http->get_json() );
	}

	public function testBadHeaderNoName() {
		$headers = [ [ 'name' => 'bad', 'type' => 'Custom' ] ];
		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( [], $http->get_json() );
	}

	public function testBadHeaderNoType() {
		$headers = [ [ 'headerName' => 'Good' ] ];
		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( [], $http->get_json() );
	}

	public function testHeaderSanitizeName() {
		$headers = [ [ 'headerName' => 'this is  nonsense }', 'type' => 'Custom' ] ];
		$expected = [ [ 'type' => 'Custom', 'location' => 'site', 'headerName' => 'This-Is-Nonsense', 'headerValue' => '', 'headerSettings' => [] ] ];

		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_json() );
	}

	public function testHeaderBadLocation() {
		$headers = [ [ 'headerName' => 'Good', 'location' => 'bad', 'type' => 'Custom' ] ];
		$expected = [ [ 'type' => 'Custom', 'location' => 'site', 'headerName' => 'Good', 'headerValue' => '', 'headerSettings' => [] ] ];
		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_json() );
	}

	public function testHeaderLocation() {
		$headers = [ [ 'headerName' => 'Good', 'location' => 'redirect', 'type' => 'Custom' ] ];
		$expected = [ [ 'type' => 'Custom', 'location' => 'redirect', 'headerName' => 'Good', 'headerValue' => '', 'headerSettings' => [] ] ];
		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_json() );
	}

	public function testHeaderSanitizeValue() {
		$headers = [ [ 'headerName' => 'Good', 'location' => 'redirect', 'headerValue' => "this\nthing", 'type' => 'Custom' ] ];
		$expected = [ [ 'type' => 'Custom', 'location' => 'redirect', 'headerName' => 'Good', 'headerValue' => 'this', 'headerSettings' => [] ] ];
		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_json() );
	}

	public function testGoodHeader() {
		$headers = [ [ 'headerName' => 'Good', 'location' => 'redirect', 'headerValue' => 'value', 'type' => 'Custom', 'headerSettings' => [ 'thing' => 'test' ] ] ];
		$expected = [ [ 'location' => 'redirect', 'headerName' => 'Good', 'headerValue' => 'value', 'headerSettings' => [ 'thing' => 'test' ], 'type' => 'Custom' ] ];
		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_json() );
	}

	public function testSiteHeaders() {
		$headers = [
			[ 'headerName' => 'redirect', 'location' => 'redirect', 'headerValue' => 'value', 'type' => 'Custom' ],
			[ 'headerName' => 'site', 'location' => 'site', 'headerValue' => 'value', 'type' => 'Custom' ],
		];
		$expected = [ [ 'location' => 'site', 'headerName' => 'Site', 'headerValue' => 'value', 'headerSettings' => [], 'type' => 'Custom' ] ];

		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_site_headers() );
	}

	public function testRedirectHeaders() {
		$headers = [
			[ 'headerName' => 'Site', 'location' => 'site', 'headerValue' => 'value', 'type' => 'Custom' ],
			[ 'headerName' => 'Redirect', 'location' => 'redirect', 'headerValue' => 'value', 'type' => 'Custom' ],
		];

		$expected = [
			[ 'headerName' => 'Site', 'location' => 'site', 'headerValue' => 'value', 'headerSettings' => [], 'type' => 'Custom' ],
			[ 'headerName' => 'Redirect', 'location' => 'redirect', 'headerValue' => 'value', 'headerSettings' => [], 'type' => 'Custom' ],
		];

		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_redirect_headers() );
	}

	public function testRedirectHeadersRemoveRedirectDupes() {
		$headers = [
			[ 'headerName' => 'name', 'location' => 'site', 'headerValue' => 'valuesite', 'type' => 'Custom' ],
			[ 'headerName' => 'name', 'location' => 'redirect', 'headerValue' => 'valueredirect', 'type' => 'Custom' ],
		];

		$expected = [
			[ 'headerName' => 'Name', 'location' => 'redirect', 'headerValue' => 'valueredirect', 'headerSettings' => [], 'type' => 'Custom' ],
		];

		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_redirect_headers() );
	}

	public function testRedirectHeadersRemoveSiteDupes() {
		$headers = [
			[ 'headerName' => 'name', 'location' => 'site', 'headerValue' => 'value1', 'type' => 'Custom' ],
			[ 'headerName' => 'name', 'location' => 'site', 'headerValue' => 'value2', 'type' => 'Custom' ],
		];

		$expected = [
			[ 'headerName' => 'Name', 'location' => 'site', 'headerValue' => 'value2', 'headerSettings' => [], 'type' => 'Custom' ],
		];

		$http = new Site\Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_site_headers() );
	}
}
