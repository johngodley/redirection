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
			'flag_query' => 'cat',
			'flag_trailing' => 'cat',
			'flag_regex' => 'cat',
		] );
		$this->checkDefaults( $flags );
	}

	public function testSetQueryMatch() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'flag_query' => 'ignore' ] );
		$this->assertTrue( $flags->is_query_ignore() );
		$this->assertFalse( $flags->is_query_exact() );

		$flags->set_flags( [ 'flag_query' => 'exact' ] );
		$this->assertFalse( $flags->is_query_ignore() );
		$this->assertTrue( $flags->is_query_exact() );
	}

	public function testSetCase() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'flag_case' => true ] );
		$this->assertTrue( $flags->is_ignore_case() );
	}

	public function testSetTrailing() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'flag_trailing' => true ] );
		$this->assertTrue( $flags->is_ignore_trailing() );
	}

	public function testSetPass() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'flag_query' => 'pass' ] );
		$this->assertTrue( $flags->is_query_pass() );
	}

	public function testSetRegexTrue() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'flag_regex' => true, 'flag_case' => true, 'flag_trailing' => true, 'flag_query' => 'pass' ] );
		$this->assertTrue( $flags->is_regex() );
		$this->assertTrue( $flags->is_ignore_case() );
		$this->assertFalse( $flags->is_ignore_trailing() );
		$this->assertFalse( $flags->is_query_pass() );
	}

	public function testSetRegexFalse() {
		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'flag_regex' => false, 'flag_case' => true, 'flag_trailing' => true, 'flag_query' => 'pass' ] );
		$this->assertFalse( $flags->is_regex() );
		$this->assertTrue( $flags->is_ignore_case() );
		$this->assertTrue( $flags->is_ignore_trailing() );
		$this->assertTrue( $flags->is_query_pass() );
	}

	public function testIgnoreSameDefaults() {
		$defaults = [
			'cat' => 'thing',
			'flag_trailing' => true,
			'flag_query' => 'pass',
			'flag_trailing' => true,
			'flag_regex' => false,
			'flag_case' => true,
		];
		$expected = [];

		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'flag_regex' => false, 'flag_case' => true, 'flag_trailing' => true, 'flag_query' => 'pass' ] );
		$this->assertEquals( $expected, $flags->get_json( $defaults ) );
	}

	public function testReturnDifferentDefaults() {
		$defaults = [
			'cat' => 'thing',
			'flag_trailing' => false,
			'flag_query' => 'ignore',
			'flag_trailing' => false,
			'flag_regex' => true,
			'flag_case' => false,
		];
		$expected = [
			'flag_trailing' => true,
			'flag_query' => 'pass',
			'flag_trailing' => true,
			'flag_regex' => false,
			'flag_case' => true,
		];

		$flags = new Red_Source_Flags();
		$flags->set_flags( [ 'flag_regex' => false, 'flag_case' => true, 'flag_trailing' => true, 'flag_query' => 'pass' ] );
		$this->assertEquals( $expected, $flags->get_json( $defaults ) );
	}
}
