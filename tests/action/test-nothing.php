<?php

class NothingTest extends WP_UnitTestCase {
	public function setUp(): void {
		$module = Redirection::init()->get_module();
		$module->reset();
	}

	public function test404Logs() {
		global $wp_query, $wpdb;

		$before = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404" );

		$wp_query->is_404 = 1;
		$module = Redirection::init()->get_module();
		$module->template_redirect();

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404" );
		$this->assertEquals( intval( $before, 10 ) + 1, intval( $after, 10 ) );
	}

	public function test404NothingHasNoLog() {
		global $wp_query, $wpdb;

		$before = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404" );

		$wp_query->is_404 = 1;
		$module = Redirection::init()->get_module();
		$action = Action\Action::create( 'nothing', 1 );

		$action->run();
		$module->template_redirect();

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404" );
		$this->assertEquals( intval( $before, 10 ), intval( $after, 10 ) );
	}
}
