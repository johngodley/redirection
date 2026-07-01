<?php

require_once PLUGIN_PATH . '/includes/import-export/parser/class-json-parser.php';

use Redirection\ImportExport\Parser\JsonParser;

class JsonParserTest extends TestCase {
	public function testParseReturnsNormalizedGroupsAndRedirects() {
		$parser = new JsonParser();
		$parsed = $parser->parse(
			json_encode(
				[
					'groups' => [
						[
							'id' => 55,
							'name' => 'group',
							'module_id' => 1,
							'enabled' => true,
						],
					],
					'redirects' => [
						[
							'id' => 99,
							'url' => '/source',
							'group_id' => 55,
							'match_type' => 'url',
							'action_type' => 'url',
							'action_data' => '/target',
						],
					],
				]
			)
		);

		$this->assertEquals( 'group', $parsed['groups'][55]['name'] );
		$this->assertEquals( [ 'url' => '/target' ], $parsed['redirects'][0]['action_data'] );
	}

	public function testParseReturnsAdditionalBundleSections() {
		$parser = new JsonParser();
		$parsed = $parser->parse(
			json_encode(
				[
					'settings' => [
						'https' => true,
					],
					'logs' => [
						[
							'url' => '/logged',
							'ip' => '127.0.0.1',
						],
					],
					'errors_404' => [
						[
							'url' => '/missing',
							'ip' => '127.0.0.2',
						],
					],
				]
			)
		);

		$this->assertTrue( $parsed['settings']['https'] );
		$this->assertEquals( '/logged', $parsed['logs'][0]['url'] );
		$this->assertEquals( '/missing', $parsed['errors_404'][0]['url'] );
	}

	public function testParseReturnsFalseForInvalidJson() {
		$parser = new JsonParser();

		$this->assertFalse( $parser->parse( 'x' ) );
	}
}
