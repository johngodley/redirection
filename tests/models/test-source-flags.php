<?php

class SourceFlagsTest extends WP_UnitTestCase {
	private function checkDefaults( $flags ) {
		$this->assertFalse( $flags->is_ignore_case() );
		$this->assertFalse( $flags->is_ignore_trailing() );
		$this->assertFalse( $flags->is_regex() );
		$this->assertFalse( $flags->is_query_ignore() );
		$this->assertFalse( $flags->is_query_pass() );
		$this->assertTrue( $flags->is_query_exact() );
	}

	public function testDefaultFlags() {
		$flags = new Red_Source_Flags();
		$this->checkDefaults( $flags );
	}

	public function testSetInvalidFlags() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [
			'cat' => 'cat',
			'case' => 'cat',
			'queryMatch' => 'cat',
			'trailing' => 'cat',
			'queryPass' => 'cat',
			'regex' => 'cat',
		] );
		$this->checkDefaults( $flags );
	}

	public function testSetQueryMatch() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'queryMatch' => 'ignore' ] );
		$this->assertTrue( $flags->is_query_ignore() );
		$this->assertFalse( $flags->is_query_exact() );

		$flags->set_flags( [ 'queryMatch' => 'exact' ] );
		$this->assertFalse( $flags->is_query_ignore() );
		$this->assertTrue( $flags->is_query_exact() );
	}

	public function testSetCase() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'case' => true ] );
		$this->assertTrue( $flags->is_ignore_case() );
	}

	public function testSetTrailing() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'trailing' => true ] );
		$this->assertTrue( $flags->is_ignore_trailing() );
	}

	public function testSetPass() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'queryPass' => true ] );
		$this->assertTrue( $flags->is_query_pass() );
	}

	public function testSetRegex() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'regex' => true ] );
		$this->assertTrue( $flags->is_regex() );
	}
}
