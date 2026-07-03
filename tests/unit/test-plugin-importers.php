<?php

use Brain\Monkey\Functions;

require_once PLUGIN_PATH . '/tests/unit/stubs/class-test-import-group-item.php';
require_once PLUGIN_PATH . '/tests/unit/stubs/class-red-group.php';
require_once PLUGIN_PATH . '/tests/unit/stubs/class-red-item.php';
require_once PLUGIN_PATH . '/tests/unit/stubs/functions.php';
require_once PLUGIN_PATH . '/includes/import-export/class-group-repository.php';
require_once PLUGIN_PATH . '/includes/import-export/class-redirect-repository.php';
require_once PLUGIN_PATH . '/includes/import-export/class-format-handler.php';
require_once PLUGIN_PATH . '/includes/import-export/class-format-factory.php';
require_once PLUGIN_PATH . '/includes/import-export/class-file-reader.php';
require_once PLUGIN_PATH . '/includes/import-export/class-export-details.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-target-builder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-rule-builder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-encoder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess.php';
require_once PLUGIN_PATH . '/includes/import-export/class-import-group.php';
require_once PLUGIN_PATH . '/includes/import-export/class-redirect-duplicate-matcher.php';
require_once PLUGIN_PATH . '/includes/import-export/class-import-redirect.php';
require_once PLUGIN_PATH . '/includes/import-export/parser/class-csv-parser.php';
require_once PLUGIN_PATH . '/includes/import-export/parser/class-json-parser.php';
require_once PLUGIN_PATH . '/includes/import-export/sanitizer/class-csv-sanitizer.php';
require_once PLUGIN_PATH . '/includes/import-export/sanitizer/class-htaccess-sanitizer.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-apache.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-csv.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-json.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-nginx.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-rss.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-plugin.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-plugin-registry.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-redirect-item-mapper.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-eps301-redirects.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-fake-redirection.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-pretty-links.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-quick-redirects.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-rank-math.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-safe-redirect-manager.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-seo-redirection.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-seopress.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-simple301.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-slim-seo.php';
require_once PLUGIN_PATH . '/includes/import-export/importer/class-wordpress-old-slugs.php';

use Redirection\ImportExport\Format\Apache;
use Redirection\ImportExport\Format\Csv;
use Redirection\ImportExport\Format\Json;
use Redirection\ImportExport\Format\Nginx;
use Redirection\ImportExport\Format\Rss;
use Redirection\ImportExport\FormatFactory;
use Redirection\ImportExport\Importer\Eps301Redirects;
use Redirection\ImportExport\Importer\FakeRedirection;
use Redirection\ImportExport\Importer\Plugin;
use Redirection\ImportExport\Importer\PluginRegistry;
use Redirection\ImportExport\Importer\QuickRedirects;
use Redirection\ImportExport\Importer\RedirectItemMapper;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PluginImporterUnitTest extends TestCase {
	private function get_mapper() {
		return new RedirectItemMapper();
	}

	protected function setUp(): void {
		parent::setUp();

		Red_Group::reset();
		Red_Item::reset();
		Red_Group::$groups[1] = new Test_Import_Group_Item( 1 );
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

	public function testPluginImporterRegistryReturnsKnownImporters() {
		$this->assertInstanceOf( Eps301Redirects::class, PluginRegistry::get_importer( 'eps-301-redirects' ) );
		$this->assertInstanceOf( FakeRedirection::class, PluginRegistry::get_importer( 'fake-redirection' ) );
		$this->assertFalse( PluginRegistry::get_importer( 'not-a-plugin' ) );
	}

	public function testFormatFactoryReturnsExpectedExporters() {
		$factory = new FormatFactory();

		$this->assertFalse( $factory->create( 'monkey' ) );
		$this->assertInstanceOf( Rss::class, $factory->create( 'rss' ) );
		$this->assertInstanceOf( Csv::class, $factory->create( 'csv' ) );
		$this->assertInstanceOf( Apache::class, $factory->create( 'apache' ) );
		$this->assertInstanceOf( Nginx::class, $factory->create( 'nginx' ) );
		$this->assertInstanceOf( Json::class, $factory->create( 'json' ) );
	}

	public function testSimple301ImporterMapsWildcardRedirects() {
		$mapper = $this->get_mapper();

		$result = $mapper->simple301( '/source/*', ' /target/* ' );

		$this->assertSame( '/source/(.*?)', $result['url'] );
		$this->assertSame( '/target/$1', $result['action_data']['url'] );
		$this->assertTrue( $result['regex'] );
		$this->assertSame( 301, $result['action_code'] );
	}

	public function testQuickRedirectsImporterMapsDirectRedirects() {
		$mapper = $this->get_mapper();

		$result = $mapper->quick_redirects( '/source/', 'https://example.com/target/' );

		$this->assertSame( '/source/', $result['url'] );
		$this->assertSame( 'https://example.com/target/', $result['action_data']['url'] );
		$this->assertFalse( $result['regex'] );
		$this->assertSame( 301, $result['action_code'] );
	}

	public function testQuickRedirectsImporterIgnoresMalformedOptionValues() {
		Functions\when( 'get_option' )->justReturn( 'not-an-array' );

		$importer = new class() extends QuickRedirects {
			public function get_items() {
				return $this->get_redirect_items();
			}
		};

		$this->assertSame( [], $importer->get_items() );
		$this->assertFalse( $importer->get_data() );
	}

	public function testSlimSeoImporterRejectsDisabledOrIncompleteRedirects() {
		$mapper = $this->get_mapper();

		$this->assertFalse(
			$mapper->slim_seo(
				[
					'from' => '/source/',
					'to' => '/target/',
					'enable' => 0,
				]
			)
		);
		$this->assertFalse(
			$mapper->slim_seo(
				[
					'from' => '',
					'to' => '/target/',
					'enable' => 1,
				]
			)
		);
	}

	public function testSlimSeoImporterMapsExactRedirects() {
		$mapper = $this->get_mapper();

		$result = $mapper->slim_seo(
			[
				'from' => 'source',
				'to' => 'target',
				'enable' => 1,
			]
		);

		$this->assertSame( '/source', $result['url'] );
		$this->assertSame( '/target', $result['action_data']['url'] );
		$this->assertFalse( $result['regex'] );
		$this->assertSame( 301, $result['action_code'] );
	}

	public function testSlimSeoImporterMapsRegexConditions() {
		$mapper = $this->get_mapper();

		$start_with = $mapper->slim_seo(
			[
				'from' => 'source',
				'to' => '/target/',
				'enable' => 1,
				'condition' => 'start-with',
			]
		);
		$end_with = $mapper->slim_seo(
			[
				'from' => 'source',
				'to' => '/target/',
				'enable' => 1,
				'condition' => 'end-with',
			]
		);
		$contain = $mapper->slim_seo(
			[
				'from' => '/source',
				'to' => '/target/',
				'enable' => 1,
				'condition' => 'contain',
			]
		);
		$regex = $mapper->slim_seo(
			[
				'from' => '^/source/(.*)$',
				'to' => '/target/',
				'enable' => 1,
				'condition' => 'regex',
				'type' => '302',
			]
		);

		$this->assertSame( '^/source', $start_with['url'] );
		$this->assertTrue( $start_with['regex'] );
		$this->assertSame( '/source/?$', $end_with['url'] );
		$this->assertTrue( $end_with['regex'] );
		$this->assertSame( '.*source.*', $contain['url'] );
		$this->assertTrue( $contain['regex'] );
		$this->assertSame( '^/source/(.*)$', $regex['url'] );
		$this->assertTrue( $regex['regex'] );
		$this->assertSame( 302, $regex['action_code'] );
	}

	public function testWordpressOldSlugsImporterMapsRedirectRows() {
		$mapper = $this->get_mapper();

		$result = $mapper->wordpress_old_slug( 'https://example.com/folder/new-target/', 'old-source' );

		$this->assertSame( '/folder/old-source/', $result['url'] );
		$this->assertSame( 'https://example.com/folder/new-target/', $result['action_data']['url'] );
		$this->assertFalse( $result['regex'] );
		$this->assertSame( 301, $result['action_code'] );
	}

	public function testWordpressOldSlugsImporterRejectsMissingPermalink() {
		$mapper = $this->get_mapper();

		$this->assertFalse( $mapper->wordpress_old_slug( 'https://example.com', 'old-source' ) );
	}

	public function testSeopressImporterMapsPostRedirects() {
		$mapper = $this->get_mapper();

		$result = $mapper->seopress_content( 'https://example.com/source/', 'https://example.com/target/', 301 );

		$this->assertSame( '/source/', $result['url'] );
		$this->assertSame( 'https://example.com/target/', $result['action_data']['url'] );
		$this->assertFalse( $result['regex'] );
		$this->assertSame( 301, $result['action_code'] );
	}

	public function testSeopressImporterMapsTermRedirects() {
		$mapper = $this->get_mapper();

		$result = $mapper->seopress_content( 'https://example.com/term-slug/', '/target/', 302 );

		$this->assertSame( '/term-slug/', $result['url'] );
		$this->assertSame( '/target/', $result['action_data']['url'] );
		$this->assertFalse( $result['regex'] );
		$this->assertSame( 302, $result['action_code'] );
	}

	public function testSeopressImporterMaps404Redirects() {
		$mapper = $this->get_mapper();

		$result = $mapper->seopress_404( 'missing-page', '/target/', 307, false );

		$this->assertSame( '/missing-page', $result['url'] );
		$this->assertSame( '/target/', $result['action_data']['url'] );
		$this->assertFalse( $result['regex'] );
		$this->assertSame( 307, $result['action_code'] );
	}

	public function testSeopressImporterKeepsRegex404Source() {
		$mapper = $this->get_mapper();

		$result = $mapper->seopress_404( '^missing-(.*)$', '/target/', 301, true );

		$this->assertSame( '^missing-(.*)$', $result['url'] );
		$this->assertTrue( $result['regex'] );
	}

	public function testSafeRedirectManagerImporterMapsPlainRedirects() {
		$mapper = $this->get_mapper();

		$result = $mapper->safe_redirect_manager(
			[
				'from' => '/source/',
				'to' => '/target/',
				'status_code' => '301',
			]
		);

		$this->assertSame( '/source/', $result['url'] );
		$this->assertSame( '/target/', $result['action_data']['url'] );
		$this->assertFalse( $result['regex'] );
		$this->assertSame( 301, $result['action_code'] );
	}

	public function testSafeRedirectManagerImporterMapsWildcardRedirectsAsRegex() {
		$mapper = $this->get_mapper();

		$result = $mapper->safe_redirect_manager(
			[
				'from' => '/source/*',
				'to' => '/target/',
				'status_code' => '302',
			]
		);

		$this->assertSame( '/source/.*', $result['url'] );
		$this->assertTrue( $result['regex'] );
		$this->assertSame( 302, $result['action_code'] );
	}

	public function testSafeRedirectManagerImporterHonorsExplicitRegexFlag() {
		$mapper = $this->get_mapper();

		$result = $mapper->safe_redirect_manager(
			[
				'from' => '^/source/.+$',
				'to' => '/target/',
				'status_code' => '307',
				'from_regex' => '1',
			]
		);

		$this->assertSame( '^/source/.+$', $result['url'] );
		$this->assertTrue( $result['regex'] );
		$this->assertSame( 307, $result['action_code'] );
	}

	public function testFakeRedirectionImporterMapsRedirectRows() {
		$mapper = $this->get_mapper();

		$result = $mapper->fake_redirection(
			(object) [
				'match' => '/source/',
				'to' => 'https://example.com/target/',
				'redirect_code' => '302',
			]
		);

		$this->assertSame( '/source/', $result['url'] );
		$this->assertSame( 'https://example.com/target/', $result['action_data']['url'] );
		$this->assertFalse( $result['regex'] );
		$this->assertSame( 302, $result['action_code'] );
	}

	public function testFakeRedirectionImporterDefaultsCodeAndRejectsInvalidRows() {
		$mapper = $this->get_mapper();

		$result = $mapper->fake_redirection(
			(object) [
				'match' => '/source/',
				'to' => '/target/',
			]
		);

		$this->assertSame( 301, $result['action_code'] );
		$this->assertFalse(
			$mapper->fake_redirection(
				(object) [
					'match' => '',
					'to' => '/target/',
					'redirect_code' => '301',
				]
			)
		);
	}

	public function testEps301RedirectsImporterMapsDirectRedirectRows() {
		$mapper = $this->get_mapper();

		$result = $mapper->eps301( 'source/', 'https://example.com/target/', 301 );

		$this->assertSame( '/source/', $result['url'] );
		$this->assertSame( 'https://example.com/target/', $result['action_data']['url'] );
		$this->assertFalse( $result['regex'] );
		$this->assertSame( 301, $result['action_code'] );
	}

	public function testEps301RedirectsImporterResolvesPostTargets() {
		$mapper = $this->get_mapper();

		$result = $mapper->eps301( '/source/', 'https://example.com/permalink-target/', 302 );

		$this->assertSame( 'https://example.com/permalink-target/', $result['action_data']['url'] );
		$this->assertSame( 302, $result['action_code'] );
	}

	public function testEps301RedirectsImporterRejectsInvalidRows() {
		$mapper = $this->get_mapper();

		$this->assertFalse( $mapper->eps301( 'source/', false, 301 ) );
		$this->assertFalse( $mapper->eps301( 'source/', '/target/', 0 ) );
	}

	public function testPreviewPluginResultsReturnsEmptyWhenPreviewNotSupported() {
		$importer = new class() extends Plugin {
			public function get_data() {
				return false;
			}
		};

		$result = $importer->preview_plugin_results( 1, [] );

		$this->assertSame(
			[
				'created' => 0,
				'updated' => 0,
				'ignored' => 0,
				'groups_created' => 0,
				'groups_imported' => 0,
				'logs_imported' => 0,
				'errors_imported' => 0,
				'settings_imported' => 0,
				'preview' => [],
			],
			$result
		);
	}

	public function testPreviewPluginResultsProcessesItemsWhenPreviewSupported() {
		global $wpdb;

		$wpdb = $this->get_wpdb();
		$importer = new class() extends Plugin {
			public function supports_preview() {
				return true;
			}

			protected function get_redirect_items() {
				return [
					[
						'url' => '/previewed',
						'regex' => false,
						'action_data' => [ 'url' => '/target' ],
						'match_type' => 'url',
						'action_type' => 'url',
						'action_code' => 301,
					],
				];
			}

			public function get_data() {
				return false;
			}
		};

		$result = $importer->preview_plugin_results( 1, [ 'duplicate_mode' => 'import' ] );

		$this->assertEquals( 1, $result['created'] );
		$this->assertEquals( 0, $wpdb->get_var_calls );
		$this->assertCount( 1, $result['preview'] );
		$this->assertSame( '/previewed', $result['preview'][0]['source'] );
		$this->assertSame( 'created', $result['preview'][0]['result'] );
	}

	public function testImportPluginResultsDelegatesToRedirectImportFlow() {
		global $wpdb;

		$wpdb = $this->get_wpdb();
		$importer = new class() extends Plugin {
			protected function get_redirect_items() {
				return [
					[
						'url' => '/created',
						'regex' => false,
						'action_data' => [ 'url' => '/target' ],
						'match_type' => 'url',
						'action_type' => 'url',
						'action_code' => 301,
					],
				];
			}

			public function get_data() {
				return false;
			}
		};

		$result = $importer->import_plugin_results( 1, [ 'duplicate_mode' => 'import' ] );

		$this->assertEquals( 1, $result['created'] );
		$this->assertEquals( 0, $result['updated'] );
		$this->assertEquals( 0, $result['ignored'] );
		$this->assertCount( 1, Red_Item::$create_calls );
		$this->assertSame( '/created', Red_Item::$create_calls[0]['url'] );
	}
}
