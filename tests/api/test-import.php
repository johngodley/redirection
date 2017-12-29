<?php

class ImportImportCsvTest extends Redirection_Api_Test {
	public function testNoPermission() {
		$this->setUnauthorised();

		$result = $this->callApi( 'import/file', array(), 'POST' );
		$this->assertEquals( 403, $result->status );

		$result = $this->callApi( 'import/plugin' );
		$this->assertEquals( 403, $result->status );

		$result = $this->callApi( 'import/plugin/XXX', array(), 'POST' );
		$this->assertEquals( 403, $result->status );
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
