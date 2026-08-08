<?php

require_once PLUGIN_PATH . '/includes/import-export/class-format-handler.php';
require_once PLUGIN_PATH . '/includes/import-export/class-export-details.php';
require_once PLUGIN_PATH . '/includes/import-export/class-file-reader.php';
require_once PLUGIN_PATH . '/includes/import-export/class-group-repository.php';
require_once PLUGIN_PATH . '/includes/import-export/class-import-group.php';
require_once PLUGIN_PATH . '/includes/import-export/class-redirect-repository.php';
require_once PLUGIN_PATH . '/includes/import-export/class-redirect-duplicate-matcher.php';
require_once PLUGIN_PATH . '/includes/import-export/class-import-redirect.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-redirects-file.php';
require_once PLUGIN_PATH . '/models/regex.php';

use Brain\Monkey\Functions;
use Redirection\ImportExport\GroupRepository;
use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;
use Redirection\ImportExport\Format\RedirectsFile;

class RedirectsFileFormatTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		// get_data() calls FormatHandler::get_export_details(), which calls
		// red_get_plugin_data(). That function guards a WP core include behind
		// function_exists( 'get_plugin_data' ), so stubbing it here is enough
		// to keep the export tests out of WP's ABSPATH-dependent bootstrap.
		Functions\when( 'get_plugin_data' )->justReturn( [ 'Version' => '1.0' ] );
	}

	private function get_item( array $args ) {
		return new class( $args ) {
			private $args;

			public function __construct( array $args ) {
				$this->args = $args;
			}

			public function get_url() {
				return $this->args['url'];
			}

			public function get_match_type() {
				return $this->args['match_type'] ?? 'url';
			}

			public function get_action_type() {
				return $this->args['action_type'] ?? 'url';
			}

			public function get_action_code() {
				return $this->args['action_code'] ?? 301;
			}

			public function get_action_data() {
				return $this->args['action_data'] ?? '';
			}

			public function is_regex() {
				return $this->args['regex'] ?? false;
			}

			public function is_enabled() {
				return $this->args['enabled'] ?? true;
			}
		};
	}

	// --- Import: get_as_item() ---

	public function testLiteralRuleDefaultsToStatus301() {
		$format = new RedirectsFile();
		$item = $format->get_as_item( '/old /new' );

		$this->assertEquals( '/old', $item['url'] );
		$this->assertFalse( $item['regex'] );
		$this->assertEquals( [ 'url' => '/new' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
	}

	public function testLiteralRuleWithExplicitStatus() {
		$format = new RedirectsFile();
		$item = $format->get_as_item( '/old /new 302' );

		$this->assertEquals( 302, $item['action_code'] );
	}

	public function testForceFlagIsStrippedWithoutFailingLine() {
		$format = new RedirectsFile();
		$item = $format->get_as_item( '/old /new 301!' );

		$this->assertEquals( '/old', $item['url'] );
		$this->assertEquals( 301, $item['action_code'] );
	}

	public function testSplatRuleBuildsRegexAndSubstitutesTarget() {
		$format = new RedirectsFile();
		$item = $format->get_as_item( '/blog/* /news/:splat 301' );

		$this->assertEquals( '^/blog/(.*)$', $item['url'] );
		$this->assertTrue( $item['regex'] );
		$this->assertEquals( [ 'url' => '/news/$1' ], $item['action_data'] );
		$this->assertEquals( 301, $item['action_code'] );
	}

	public function testSplatRuleWithAtSignInSourceProducesAMatchingRegex() {
		$format = new RedirectsFile();
		$item = $format->get_as_item( '/@old/* /new/:splat 301' );

		$this->assertTrue( $item['regex'] );

		$regex = new \Red_Regex( $item['url'] );
		$this->assertTrue( $regex->is_match( '/@old/thing' ) );
	}

	public function testCommentLinesAreIgnored() {
		$format = new RedirectsFile();

		$this->assertFalse( $format->get_as_item( '# a comment' ) );
	}

	public function testBlankLinesAreIgnored() {
		$format = new RedirectsFile();

		$this->assertFalse( $format->get_as_item( '' ) );
		$this->assertFalse( $format->get_as_item( '   ' ) );
	}

	public function testRewriteStatus200IsOutOfScope() {
		$format = new RedirectsFile();

		$this->assertFalse( $format->get_as_item( '/old /new 200' ) );
	}

	public function testNamedPlaceholderIsOutOfScope() {
		$format = new RedirectsFile();

		$this->assertFalse( $format->get_as_item( '/:lang/* /lang/:lang/:splat 301' ) );
	}

	public function testConditionParamsAreOutOfScope() {
		$format = new RedirectsFile();

		$this->assertFalse( $format->get_as_item( '/old /new 301 Country=us' ) );
	}

	public function testMidStringWildcardIsOutOfScope() {
		$format = new RedirectsFile();

		$this->assertFalse( $format->get_as_item( '/old/*/thing /new 301' ) );
	}

	public function testMultipleWildcardsAreOutOfScope() {
		$format = new RedirectsFile();

		$this->assertFalse( $format->get_as_item( '/old/*/* /new 301' ) );
	}

	public function testSplatTargetWithoutSourceWildcardIsOutOfScope() {
		$format = new RedirectsFile();

		$this->assertFalse( $format->get_as_item( '/old /new/:splat 301' ) );
	}

	public function testLoadFromStringStripsLeadingUtf8Bom() {
		global $wpdb;

		$wpdb = (object) [
			'queries' => [],
		];

		$format = new RedirectsFile();
		$redirect = new ImportRedirect( [ 'dry_run' => true ] );
		$repository = new class() extends GroupRepository {
			public function get( $group_id ) {
				return new class( $group_id ) {
					private $group_id;

					public function __construct( $group_id ) {
						$this->group_id = intval( $group_id, 10 );
					}

					public function get_id() {
						return $this->group_id;
					}

					public function get_name() {
						return 'Group';
					}

					public function is_enabled() {
						return true;
					}
				};
			}
		};
		$group = new ImportGroup( 1, [ 'dry_run' => true ], $repository );

		$format->load_from_string( $group, $redirect, "\xEF\xBB\xBF/old /new 301" );

		$this->assertEquals( 1, $redirect->get_created() );

		$preview = $redirect->get_preview_items();
		$this->assertEquals( '/old', $preview[0]['source'] );
	}

	// --- Export: get_data() / get_skipped_count() ---

	public function testGetDataExportsLiteralRedirect() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '/old',
				'action_data' => '/new',
				'action_code' => 301,
			]
		);

		$data = $format->get_data( [ $item ], [] );

		$this->assertStringContainsString( "/old /new 301\n", $data );
		$this->assertEquals( 0, $format->get_skipped_count() );
		$this->assertEquals( 1, $format->get_exported_count() );
	}

	public function testGetDataExportsSplatRedirect() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '^/blog/(.*)$',
				'action_data' => '/news/$1',
				'action_code' => 301,
				'regex' => true,
			]
		);

		$data = $format->get_data( [ $item ], [] );

		$this->assertStringContainsString( "/blog/* /news/:splat 301\n", $data );
		$this->assertEquals( 0, $format->get_skipped_count() );
	}

	public function testGetDataExportsSplatRedirectWithBracedBackreference() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '^/blog/(.*)$',
				'action_data' => '/news/${1}',
				'action_code' => 301,
				'regex' => true,
			]
		);

		$data = $format->get_data( [ $item ], [] );

		$this->assertStringContainsString( "/blog/* /news/:splat 301\n", $data );
		$this->assertEquals( 0, $format->get_skipped_count() );
	}

	public function testGetDataExportsSplatRedirectWhenBackreferenceIsFollowedByNonDigit() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '^/blog/(.*)$',
				'action_data' => '/news/$1-archive',
				'action_code' => 301,
				'regex' => true,
			]
		);

		$data = $format->get_data( [ $item ], [] );

		$this->assertStringContainsString( "/blog/* /news/:splat-archive 301\n", $data );
		$this->assertEquals( 0, $format->get_skipped_count() );
	}

	public function testGetDataSkipsBracedBackreferenceFollowedByWordCharacter() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '^/blog/(.*)$',
				'action_data' => '/news/${1}0',
				'action_code' => 301,
				'regex' => true,
			]
		);

		$data = $format->get_data( [ $item ], [] );

		// The braces make this unambiguous (group 1 followed by a literal "0"), but the
		// resulting "/news/:splat0" can't be told apart from an unsupported named
		// placeholder on import, so it would never round-trip. Skip it instead.
		$this->assertStringNotContainsString( '/news/', $data );
		$this->assertEquals( 1, $format->get_skipped_count() );
	}

	public function testGetDataExportsSplatRedirectWithNoBackreference() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '^/blog/(.*)$',
				'action_data' => '/news',
				'action_code' => 301,
				'regex' => true,
			]
		);

		$data = $format->get_data( [ $item ], [] );

		// The target doesn't need to reference the captured group at all.
		$this->assertStringContainsString( "/blog/* /news 301\n", $data );
		$this->assertEquals( 0, $format->get_skipped_count() );
	}

	public function testGetDataSkipsAmbiguousNumericBackreference() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '^/blog/(.*)$',
				// There's only one capture group, so "$10" isn't a valid backreference to
				// group 10 - but it also can't be safely assumed to mean "$1" followed by a
				// literal "0" without risking silently wrong output, so this is skipped.
				'action_data' => '/news/$10',
				'action_code' => 301,
				'regex' => true,
			]
		);

		$data = $format->get_data( [ $item ], [] );

		$this->assertStringNotContainsString( '/news/', $data );
		$this->assertEquals( 1, $format->get_skipped_count() );
	}

	public function testGetDataSkipsNonUrlMatchType() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '/old',
				'action_data' => '/new',
				'match_type' => 'agent',
			]
		);

		$data = $format->get_data( [ $item ], [] );

		$this->assertStringNotContainsString( '/old', $data );
		$this->assertEquals( 1, $format->get_skipped_count() );
	}

	public function testGetDataSkipsNonUrlActionType() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '/old',
				'action_data' => '/new',
				'action_type' => 'pass',
			]
		);

		$format->get_data( [ $item ], [] );

		$this->assertEquals( 1, $format->get_skipped_count() );
	}

	public function testGetDataSkipsUnsupportedActionCode() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '/old',
				'action_data' => '/new',
				'action_code' => 404,
			]
		);

		$format->get_data( [ $item ], [] );

		$this->assertEquals( 1, $format->get_skipped_count() );
	}

	public function testGetDataSkipsHandWrittenRegexNotMatchingSplatShape() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '^/(foo|bar)$',
				'action_data' => '/target',
				'action_code' => 301,
				'regex' => true,
			]
		);

		$format->get_data( [ $item ], [] );

		$this->assertEquals( 1, $format->get_skipped_count() );
	}

	public function testGetDataSkipsTargetContainingASpace() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '/old',
				'action_data' => '/new page',
				'action_code' => 301,
			]
		);

		$data = $format->get_data( [ $item ], [] );

		$this->assertStringNotContainsString( '/new page', $data );
		$this->assertEquals( 1, $format->get_skipped_count() );
	}

	public function testGetDataDoesNotCountDisabledItemsAsSkipped() {
		$format = new RedirectsFile();
		$item = $this->get_item(
			[
				'url' => '/old',
				'action_data' => '/new',
				'enabled' => false,
			]
		);

		$data = $format->get_data( [ $item ], [] );

		$this->assertStringNotContainsString( '/old', $data );
		$this->assertEquals( 0, $format->get_skipped_count() );
	}

	public function testGetDataDoesNotCountDisabledItemsAsExported() {
		$format = new RedirectsFile();
		$enabled = $this->get_item(
			[
				'url' => '/old',
				'action_data' => '/new',
				'action_code' => 301,
			]
		);
		$disabled = $this->get_item(
			[
				'url' => '/old2',
				'action_data' => '/new2',
				'action_code' => 301,
				'enabled' => false,
			]
		);

		$format->get_data( [ $enabled, $disabled ], [] );

		// Neither skipped (unsupported) nor exported (written) - `total - exported - skipped`
		// is how many callers should treat as disabled/excluded.
		$this->assertEquals( 0, $format->get_skipped_count() );
		$this->assertEquals( 1, $format->get_exported_count() );
	}

	public function testGetSkippedCountResetsBetweenCalls() {
		$format = new RedirectsFile();
		$skippable = $this->get_item(
			[
				'url' => '/old',
				'action_data' => '/new',
				'action_code' => 404,
			]
		);
		$exportable = $this->get_item(
			[
				'url' => '/old',
				'action_data' => '/new',
				'action_code' => 301,
			]
		);

		$format->get_data( [ $skippable ], [] );
		$this->assertEquals( 1, $format->get_skipped_count() );
		$this->assertEquals( 0, $format->get_exported_count() );

		$format->get_data( [ $exportable ], [] );
		$this->assertEquals( 0, $format->get_skipped_count() );
		$this->assertEquals( 1, $format->get_exported_count() );
	}
}
