<?php

use Redirection\Database\Status;
use Redirection\Database\Upgrader;

// A stage that throws a \TypeError - a \Throwable that is NOT an \Exception.
// This simulates a fatal error from a broken stage implementation, e.g. a
// third-party plugin hooking `red_default_options`/`redirection_save_options`
// and passing a bad value into a type-hinted call further down the stack.
class ThrowableStageUpgrader extends Upgrader {
	public function get_stages() {
		return [
			'throwable_stage' => 'Stage that throws a non-Exception Throwable',
		];
	}

	protected function throwable_stage( $wpdb ) {
		throw new TypeError( 'Simulated fatal error inside a stage' );
	}
}

class UpgraderPerformStageTest extends WP_UnitTestCase {
	public function setUp(): void {
		$status = new Status();
		$status->stop_update();
	}

	public function tearDown(): void {
		$status = new Status();
		$status->stop_update();
	}

	// A \Throwable that isn't an \Exception (e.g. \TypeError) must still be caught
	// and turned into a status error, not escape as an uncaught fatal. An uncaught
	// fatal here means the REST request never returns valid JSON, so the client-side
	// poller (src/component/database/index.tsx) never sees an error state and keeps
	// retrying forever.
	public function testThrowableFromStageIsCaughtAsError() {
		$status = new Status();
		$status->set_stage( 'throwable_stage' );

		$upgrader = new ThrowableStageUpgrader();
		$upgrader->perform_stage( $status );

		$this->assertTrue( $status->is_error() );
		$this->assertStringContainsString( 'Simulated fatal error inside a stage', $status->get_json()['reason'] );
	}
}
