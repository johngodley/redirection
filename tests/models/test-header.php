<?php

class HeaderTest extends WP_UnitTestCase {
	public function testNoHeaders() {
		$http = new Red_Http_Headers();
		$this->assertEquals( [], $http->get_json() );
	}

	public function testBadHeaderNoName() {
		$headers = [ [ 'name' => 'bad' ] ];
		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( [], $http->get_json() );
	}

	public function testHeaderSanitizeName() {
		$headers = [ [ 'headerName' => 'this is  nonsense }' ] ];
		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( [ [ 'location' => 'site', 'headerName' => 'This-Is-Nonsense', 'headerValue' => '', 'headerSettings' => [] ] ], $http->get_json() );
	}

	public function testHeaderBadLocation() {
		$headers = [ [ 'headerName' => 'Good', 'location' => 'bad' ] ];
		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( [ [ 'location' => 'site', 'headerName' => 'Good', 'headerValue' => '', 'headerSettings' => [] ] ], $http->get_json( $headers ) );
	}

	public function testHeaderLocation() {
		$headers = [ [ 'headerName' => 'Good', 'location' => 'redirect' ] ];
		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( [ [ 'location' => 'redirect', 'headerName' => 'Good', 'headerValue' => '', 'headerSettings' => [] ] ], $http->get_json( $headers ) );
	}

	public function testHeaderSanitizeValue() {
		$headers = [ [ 'headerName' => 'Good', 'location' => 'redirect', 'headerValue' => "this\nthing" ] ];
		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( [ [ 'location' => 'redirect', 'headerName' => 'Good', 'headerValue' => 'this', 'headerSettings' => [] ] ], $http->get_json( $headers ) );
	}

	public function testGoodHeader() {
		$headers = [ [ 'headerName' => 'Good', 'location' => 'redirect', 'headerValue' => 'value' ] ];
		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( [ [ 'location' => 'redirect', 'headerName' => 'Good', 'headerValue' => 'value', 'headerSettings' => [] ] ], $http->get_json( $headers ) );
	}

	public function testSiteHeaders() {
		$headers = [
			[ 'headerName' => 'redirect', 'location' => 'redirect', 'headerValue' => 'value' ],
			[ 'headerName' => 'site', 'location' => 'site', 'headerValue' => 'value' ],
		];
		$expected = [ [ 'location' => 'site', 'headerName' => 'Site', 'headerValue' => 'value', 'headerSettings' => [] ] ];

		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_site_headers() );
	}

	public function testRedirectHeaders() {
		$headers = [
			[ 'headerName' => 'Site', 'location' => 'site', 'headerValue' => 'value' ],
			[ 'headerName' => 'Redirect', 'location' => 'redirect', 'headerValue' => 'value' ],
		];

		$expected = [
			[ 'headerName' => 'Site', 'location' => 'site', 'headerValue' => 'value', 'headerSettings' => [] ],
			[ 'headerName' => 'Redirect', 'location' => 'redirect', 'headerValue' => 'value', 'headerSettings' => [] ],
		];

		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_redirect_headers() );
	}

	public function testRedirectHeadersRemoveRedirectDupes() {
		$headers = [
			[ 'headerName' => 'name', 'location' => 'site', 'headerValue' => 'valuesite' ],
			[ 'headerName' => 'name', 'location' => 'redirect', 'headerValue' => 'valueredirect' ],
		];

		$expected = [
			[ 'headerName' => 'Name', 'location' => 'redirect', 'headerValue' => 'valueredirect', 'headerSettings' => [] ],
		];

		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_redirect_headers() );
	}

	public function testRedirectHeadersRemoveSiteDupes() {
		$headers = [
			[ 'headerName' => 'name', 'location' => 'site', 'headerValue' => 'value1' ],
			[ 'headerName' => 'name', 'location' => 'site', 'headerValue' => 'value2' ],
		];

		$expected = [
			[ 'headerName' => 'Name', 'location' => 'site', 'headerValue' => 'value2', 'headerSettings' => [] ],
		];

		$http = new Red_Http_Headers( $headers );
		$this->assertEquals( $expected, $http->get_site_headers() );
	}
}
