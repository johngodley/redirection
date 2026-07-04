<?php

require_once PLUGIN_PATH . '/includes/import-export/sanitizer/class-htaccess-sanitizer.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-encoder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-target-builder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-rule-builder.php';
require_once PLUGIN_PATH . '/tests/unit/stubs/class-url-match.php';

use Redirection\ImportExport\HtaccessEncoder;
use Redirection\ImportExport\HtaccessRuleBuilder;
use Redirection\ImportExport\HtaccessTargetBuilder;

class HtaccessRuleBuilderTest extends TestCase {
	private function get_item( array $args, $match ) {
		return new class( $args, $match ) {
			public $match;
			public $source_flags;
			private $args;

			public function __construct( array $args, $match ) {
				$this->args = $args;
				$this->match = $match;
				$this->source_flags = $args['source_flags'] ?? null;
			}

			public function get_match_type() {
				return $this->args['match_type'];
			}

			public function is_enabled() {
				return $this->args['enabled'] ?? true;
			}

			public function get_url() {
				return $this->args['url'];
			}

			public function is_regex() {
				return $this->args['regex'] ?? false;
			}

			public function get_match_data() {
				return $this->args['match_data'] ?? [ 'source' => [] ];
			}

			public function get_action_type() {
				return $this->args['action_type'] ?? 'url';
			}

			public function get_action_code() {
				return $this->args['action_code'] ?? 301;
			}
		};
	}

	public function testBuildForUrlItemReturnsRuleAndQueryCondition() {
		$builder = new HtaccessRuleBuilder( new HtaccessEncoder(), new HtaccessTargetBuilder() );
		$item = $this->get_item(
			[
				'match_type' => 'url',
				'url' => '/my-test?query=1',
				'action_code' => 301,
				'match_data' => [ 'source' => [] ],
			],
			new URL_Match( '/target' )
		);

		$lines = $builder->build_for_item( $item );

		$this->assertEquals( 'RewriteCond %{QUERY_STRING} ^query=1$', $lines[0] );
		$this->assertEquals( 'RewriteRule ^my-test$ /target [R=301,L]', $lines[1] );
	}

	public function testBuildForReferrerItemReturnsConditionAndRule() {
		$builder = new HtaccessRuleBuilder( new HtaccessEncoder(), new HtaccessTargetBuilder() );
		$match = (object) [
			'url_from' => '/target',
			'url_notfrom' => '',
			'referrer' => '/from',
			'regex' => false,
		];
		$item = $this->get_item(
			[
				'match_type' => 'referrer',
				'url' => '/test',
				'action_code' => 301,
				'match_data' => [ 'source' => [] ],
			],
			$match
		);

		$lines = $builder->build_for_item( $item );

		$this->assertEquals( 'RewriteCond %{HTTP_REFERER} ^from$ [NC]', $lines[0] );
		$this->assertEquals( 'RewriteRule ^test$ /target [R=301,L]', $lines[1] );
	}

	public function testBuildForServerItemReturnsHostConditionAndRule() {
		$builder = new HtaccessRuleBuilder( new HtaccessEncoder(), new HtaccessTargetBuilder() );
		$match = (object) [
			'server' => 'https://otherdomain.com',
			'url_from' => '/target',
		];
		$item = $this->get_item(
			[
				'match_type' => 'server',
				'url' => '/test',
				'action_code' => 301,
				'match_data' => [ 'source' => [] ],
			],
			$match
		);

		$lines = $builder->build_for_item( $item );

		$this->assertEquals( 'RewriteCond %{HTTP_HOST} ^otherdomain\.com$ [NC]', $lines[0] );
		$this->assertEquals( 'RewriteRule ^test$ /target [R=301,L]', $lines[1] );
	}

	public function testBuildForDisabledItemReturnsNoLines() {
		$builder = new HtaccessRuleBuilder( new HtaccessEncoder(), new HtaccessTargetBuilder() );
		$item = $this->get_item(
			[
				'match_type' => 'url',
				'url' => '/test',
				'enabled' => false,
			],
			new URL_Match( '/target' )
		);

		$this->assertSame( [], $builder->build_for_item( $item ) );
	}
}
