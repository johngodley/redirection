<?php

use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;
use Redirection\ImportExport\RedirectDuplicateMatcher;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ImportRedirectTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		$this->load_import_redirect_dependencies();

		Red_Group::reset();
		Red_Item::reset();
		Red_Group::$groups[1] = new Test_Import_Group_Item( 1 );
	}

	private function load_import_redirect_dependencies() {
		require_once PLUGIN_PATH . '/tests/unit/stubs/class-test-import-group-item.php';
		require_once PLUGIN_PATH . '/tests/unit/stubs/class-red-group.php';
		require_once PLUGIN_PATH . '/tests/unit/stubs/class-red-item.php';
		require_once PLUGIN_PATH . '/tests/unit/stubs/functions.php';
		require_once PLUGIN_PATH . '/includes/import-export/class-group-repository.php';
		require_once PLUGIN_PATH . '/includes/import-export/class-redirect-repository.php';
		require_once PLUGIN_PATH . '/includes/import-export/class-import-group.php';
		require_once PLUGIN_PATH . '/includes/import-export/class-import-redirect.php';
		require_once PLUGIN_PATH . '/includes/import-export/class-redirect-duplicate-matcher.php';
	}

	private function get_wpdb( $return_value = null ) {
		return new class( $return_value ) {
			public $prefix = 'wp_';
			public $get_var_calls = 0;
			public $prepared = [];
			private $return_value;

			public function __construct( $return_value ) {
				$this->return_value = $return_value;
			}

			public function prepare( $query, ...$args ) {
				$this->prepared[] = [
					'query' => $query,
					'args' => $args,
				];

				return vsprintf( str_replace( [ '%s', '%d' ], [ "'%s'", '%d' ], $query ), $args );
			}

			public function get_var( $query ) {
				$this->get_var_calls++;

				if ( is_callable( $this->return_value ) ) {
					return call_user_func( $this->return_value, $query );
				}

				return $this->return_value;
			}
		};
	}

	public function testGetExistingRedirectReturnsExistingItemById() {
		$matcher = new RedirectDuplicateMatcher();
		$item = Red_Item::add_existing(
			55,
			[
				'url' => '/existing',
				'regex' => false,
			]
		);

		$result = $matcher->get_existing_redirect(
			[
				'url' => '/existing',
				'regex' => false,
			],
			55
		);

		$this->assertSame( $item, $result );
		$this->assertEquals( 1, Red_Item::$get_by_id_calls );
	}

	public function testGetExistingRedirectIgnoresIdMatchWhenUrlOrRegexDiffer() {
		global $wpdb;

		$wpdb = $this->get_wpdb( 77 );

		$matcher = new RedirectDuplicateMatcher();
		$item = Red_Item::add_existing(
			55,
			[
				'url' => '/existing',
				'regex' => false,
			]
		);
		$fallback = Red_Item::add_existing(
			77,
			[
				'url' => '/incoming',
				'regex' => true,
			]
		);

		$result = $matcher->get_existing_redirect(
			[
				'url' => '/incoming',
				'regex' => true,
			],
			55
		);

		$this->assertNotSame( $item, $result );
		$this->assertSame( $fallback, $result );
		$this->assertEquals( 2, Red_Item::$get_by_id_calls );
		$this->assertEquals( 1, $wpdb->get_var_calls );
	}

	public function testGetExistingRedirectFallsBackToUrlAndRegex() {
		global $wpdb;

		$wpdb = $this->get_wpdb( 77 );

		$matcher = new RedirectDuplicateMatcher();
		$item = Red_Item::add_existing(
			77,
			[
				'url' => '/same-url',
				'regex' => true,
			]
		);

		$result = $matcher->get_existing_redirect(
			[
				'url' => '/same-url',
				'regex' => true,
			],
			0
		);

		$this->assertSame( $item, $result );
		$this->assertEquals( 1, $wpdb->get_var_calls );
		$this->assertEquals( '/same-url', $wpdb->prepared[0]['args'][0] );
		$this->assertEquals( 1, $wpdb->prepared[0]['args'][1] );
	}

	public function testGetExistingRedirectUsesRegexFlagWhenLookingUpByUrl() {
		global $wpdb;

		$wpdb = $this->get_wpdb(
			function ( $query ) {
				if ( strpos( $query, 'regex=0' ) !== false ) {
					return 91;
				}

				return null;
			}
		);

		$matcher = new RedirectDuplicateMatcher();
		$item = Red_Item::add_existing(
			91,
			[
				'url' => '/same-url',
				'regex' => false,
			]
		);

		$non_regex_result = $matcher->get_existing_redirect(
			[
				'url' => '/same-url',
				'regex' => false,
			],
			0
		);

		$regex_result = $matcher->get_existing_redirect(
			[
				'url' => '/same-url',
				'regex' => true,
			],
			0
		);

		$this->assertSame( $item, $non_regex_result );
		$this->assertFalse( $regex_result );
		$this->assertEquals( 2, $wpdb->get_var_calls );
	}

	public function testSaveSkipsDuplicateLookupWhenImportingEverything() {
		global $wpdb;

		$wpdb = $this->get_wpdb();
		$import = new ImportRedirect( [ 'dry_run' => true, 'duplicate_mode' => 'import' ] );

		$result = $import->save(
			[
				'url' => '/created',
				'regex' => false,
			],
			new ImportGroup( 1, [ 'dry_run' => true ] )
		);

		$this->assertTrue( $result );
		$this->assertEquals( 0, $wpdb->get_var_calls );
		$this->assertEquals( 0, Red_Item::$get_by_id_calls );
		$this->assertEquals( 1, $import->get_created() );
	}

	public function testSaveDefaultsStatusFromDisabledGroup() {
		$import = new ImportRedirect( [ 'duplicate_mode' => 'import' ] );
		$group = new ImportGroup( 2 );

		Red_Group::$groups[2] = new Test_Import_Group_Item( 2, false, 'Disabled' );

		$result = $import->save(
			[
				'url' => '/created',
				'regex' => false,
			],
			$group
		);

		$this->assertTrue( $result );
		$this->assertSame( 'disabled', Red_Item::$create_calls[0]['status'] );
	}
}
