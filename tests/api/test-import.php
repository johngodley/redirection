<?php

class ImportImportCsvTest extends Redirection_Api_Test {
	private function get_endpoints() {
		return [
			[ 'import/file/1', 'POST', [] ],
			[ 'import/plugin', 'GET', [] ],
			[ 'import/plugin', 'POST', [ 'plugin' => [ 'thing' ] ] ],
		];
	}

	public function testNoPermission() {
		$this->setUnauthorised();

		// None of these should work
		$this->check_endpoints( $this->get_endpoints() );
	}

	public function testEditorPermission() {
		// Everything else is 403
		$working = [
			Redirection_Capabilities::CAP_IO_MANAGE => [
				[ 'import/plugin', 'GET' ],
				[ 'import/plugin', 'POST' ],
				[ 'import/file/1', 'POST' ],
			],
		];

		$this->setEditor();

		foreach ( $working as $cap => $working_caps ) {
			$this->add_capability( $cap );
			$this->check_endpoints( $this->get_endpoints(), $working_caps );
			$this->clear_capability();
		}
	}

	public function testAdminPermission() {
		// All of these should work
		$this->check_endpoints( $this->get_endpoints(), $this->get_endpoints() );
	}

	public function testPluginList() {
		update_option( '301_redirects', array( 'redirect' ) );

		$this->setNonce();
		$result = $this->callApi( 'import/plugin' );

		$this->assertEquals( 1, count( $result->data['importers'] ) );
	}

	public function testBadCreate() {
		$exporter = Red_FileIO::create( 'monkey' );
		$this->assertFalse( $exporter );
	}

	public function testGoodCreate() {
		$types = array( 'rss', 'csv', 'apache', 'nginx', 'json' );

		foreach ( $types as $type ) {
			$exporter = Red_FileIO::create( $type );
			$this->assertTrue( $exporter !== false );
		}
	}
}
