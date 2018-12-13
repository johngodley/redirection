<?php

class ErrorTest extends WP_UnitTestCase {
	private function set_404( $is_404 ) {
		global $wp_query;

		wp_reset_query();
		set_query_var( 'is_404', $is_404 );

		$wp_query->is_404 = $is_404;
	}

	public function setUp() {
		$module = Redirection::init()->get_module();
		$module->reset();
	}

	public function testErrorAction() {
		global $wp_query;

		$wp_query->posts = array( 1 );
		$this->set_404( false );

		$action = Red_Action::create( 'error', 1 );
		$result = $action->process_before( 410, 'test' );

		$this->assertTrue( $result );
		$this->assertTrue( get_query_var( 'is_404' ) );
		$this->assertEquals( get_404_template(), $action->template_include() );
		$this->assertFalse( $action->pre_handle_404() );
		$this->assertEquals( [], $wp_query->posts );
	}
}
