<?php

use Redirection\ImportExport\Importer\PluginImporterRegistry;
use Redirection\ImportExport\Importer\SafeRedirectManagerImporter;
use Redirection\ImportExport\Importer\WordpressOldSlugsImporter;

class ImportImportCsvTest extends Redirection_Api_Test {
	private function get_endpoints() {
		return [
			[ 'import/file/1', 'POST', [] ],
			[ 'import/plugin', 'GET', [] ],
			[ 'import/plugin', 'POST', [ 'plugin' => [ 'thing' ] ] ],
		];
	}

	public function testNoPermission() {
		$this->setUnauthorised();

		// None of these should work
		$this->check_endpoints( $this->get_endpoints() );
	}

	public function testEditorPermission() {
		// Everything else is 403
		$working = [
			Redirection_Capabilities::CAP_IO_MANAGE => [
				[ 'import/plugin', 'GET' ],
				[ 'import/plugin', 'POST' ],
				[ 'import/file/1', 'POST' ],
			],
		];

		$this->setEditor();

		foreach ( $working as $cap => $working_caps ) {
			$this->add_capability( $cap );
			$this->check_endpoints( $this->get_endpoints(), $working_caps );
			$this->clear_capability();
		}
	}

	public function testAdminPermission() {
		// All of these should work
		$this->check_endpoints( $this->get_endpoints(), $this->get_endpoints() );
	}

	public function testPluginList() {
		global $wpdb;

		try {
			update_option( '301_redirects', [ '/simple' => '/target' ] );
			update_option(
				'ss_redirects',
				[
					'fixture' => [
						'type' => 301,
						'condition' => 'exact-match',
						'from' => 'slim-source',
						'to' => '/slim-target/',
						'note' => '',
						'enable' => 1,
						'ignoreParameters' => 0,
					],
				]
			);

			$post_id = self::factory()->post->create(
				[
					'post_status' => 'publish',
					'post_type' => 'post',
				]
			);

			update_post_meta( $post_id, '_seopress_redirections_enabled', 'yes' );
			update_post_meta( $post_id, '_seopress_redirections_type', '301' );
			update_post_meta( $post_id, '_seopress_redirections_value', 'https://example.com/seopress-target/' );

			$table = $wpdb->prefix . 'redirects';
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS {$table} (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					url_from VARCHAR(1024) DEFAULT '' NOT NULL,
					url_to VARCHAR(1024) DEFAULT '' NOT NULL,
					status VARCHAR(12) DEFAULT '301' NOT NULL,
					type VARCHAR(12) DEFAULT 'url' NOT NULL,
					count mediumint(9) DEFAULT 0 NOT NULL,
					UNIQUE KEY id (id)
				)"
			);
			// phpcs:enable
			$wpdb->insert(
				$table,
				[
					'url_from' => 'eps-source',
					'url_to' => 'https://example.com/eps-target/',
					'status' => '301',
					'type' => 'url',
					'count' => 0,
				]
			);

			$this->setNonce();
			$result = $this->callApi( 'import/plugin' );

			$ids = array_column( $result->data['importers'], 'id' );
			$importers = array_column( $result->data['importers'], null, 'id' );

			$this->assertContains( 'wp-simple-redirect', $ids );
			$this->assertContains( 'slim-seo', $ids );
			$this->assertContains( 'seopress', $ids );
			$this->assertTrue( $importers['slim-seo']['preview_supported'] );
		} finally {
			delete_option( '301_redirects' );
			delete_option( 'ss_redirects' );

			if ( isset( $post_id ) ) {
				wp_delete_post( $post_id, true );
			}

			if ( isset( $table ) ) {
				$wpdb->delete( $table, [ 'url_from' => 'eps-source' ] );
			}
		}
	}

	public function testPluginImportWithNoGroups() {
		global $wpdb;

		$latest = Red_Database::get_latest_database();

		try {
			$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_groups" );

			$this->setNonce();
			$result = $this->callApi( 'import/plugin', array( 'plugin' => [ 'thing' ] ), 'POST' );

			$this->assertEquals( 'redirect_import_invalid_group', $result->data['code'] );
			$this->assertEquals( 400, $result->status );
		} finally {
			$latest->create_groups( $wpdb, true );
		}
	}

	public function testImportFileOptionValidation() {
		$this->setNonce();

		$result = $this->callApi( 'import/file/1', [ 'dry_run' => 'maybe' ], 'POST' );
		$this->assertEquals( 400, $result->status );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );

		$result = $this->callApi( 'import/file/1', [ 'duplicate_mode' => 'maybe' ], 'POST' );
		$this->assertEquals( 400, $result->status );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testPluginImportOptionValidation() {
		$this->setNonce();

		$result = $this->callApi( 'import/plugin', [ 'plugin' => [ 'thing' ], 'delete_source' => 'maybe' ], 'POST' );
		$this->assertEquals( 400, $result->status );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testWordPressOldSlugDeleteSourceOnlyRunsOnImport() {
		global $wpdb;

		$permalink_structure = get_option( 'permalink_structure' );
		$group = Red_Group::create( 'import-test-group', 1 );
		$post_id = self::factory()->post->create(
			[
				'post_status' => 'publish',
				'post_type' => 'post',
				'post_name' => 'new-import-target',
			]
		);

		update_option( 'permalink_structure', '/%postname%/' );
		update_post_meta( $post_id, '_wp_old_slug', 'old-import-source' );

		$importer = PluginImporterRegistry::get_importer( 'wordpress-old-slugs' );
		$this->assertInstanceOf( WordpressOldSlugsImporter::class, $importer );

		try {
			$preview = $importer->preview_plugin_results(
				$group->get_id(),
				[
					'delete_source' => true,
					'dry_run' => true,
				]
			);

			$this->assertEquals( 1, $preview['created'] );
			$this->assertCount( 1, get_post_meta( $post_id, '_wp_old_slug', false ) );

			$result = $importer->import_plugin(
				$group->get_id(),
				[
					'delete_source' => true,
				]
			);

			$this->assertEquals( 1, $result['created'] );
			$this->assertCount( 0, get_post_meta( $post_id, '_wp_old_slug', false ) );
			$this->assertEquals(
				1,
				intval(
					$wpdb->get_var(
						$wpdb->prepare(
							"SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE url=%s",
							$preview['preview'][0]['source']
						)
					),
					10
				)
			);
		} finally {
			update_option( 'permalink_structure', $permalink_structure );
			delete_post_meta( $post_id, '_wp_old_slug', 'old-import-source' );
			wp_delete_post( $post_id, true );

			if ( $group instanceof Red_Group ) {
				$group->delete();
			}

			if ( isset( $preview['preview'][0]['source'] ) ) {
				$wpdb->delete( $wpdb->prefix . 'redirection_items', [ 'url' => $preview['preview'][0]['source'] ] );
			}
		}
	}

	public function testSafeRedirectManagerDeleteSourceOnlyRunsOnImport() {
		global $wpdb;

		$group = Red_Group::create( 'import-test-group', 1 );
		$post_id = self::factory()->post->create(
			[
				'post_status' => 'publish',
				'post_type' => 'post',
			]
		);

		update_post_meta( $post_id, '_redirect_rule_from', '/safe-source/' );
		update_post_meta( $post_id, '_redirect_rule_to', 'https://example.com/safe-target/' );
		update_post_meta( $post_id, '_redirect_rule_status_code', '301' );

		$importer = PluginImporterRegistry::get_importer( 'safe-redirect-manager' );
		$this->assertInstanceOf( SafeRedirectManagerImporter::class, $importer );

		try {
			$preview = $importer->preview_plugin_results(
				$group->get_id(),
				[
					'delete_source' => true,
					'dry_run' => true,
				]
			);

			$this->assertEquals( 1, $preview['created'] );
			$this->assertSame( '/safe-source/', get_post_meta( $post_id, '_redirect_rule_from', true ) );

			$result = $importer->import_plugin(
				$group->get_id(),
				[
					'delete_source' => true,
				]
			);

			$this->assertEquals( 1, $result['created'] );
			$this->assertSame( '', get_post_meta( $post_id, '_redirect_rule_from', true ) );
			$this->assertSame( '', get_post_meta( $post_id, '_redirect_rule_to', true ) );
			$this->assertSame( '', get_post_meta( $post_id, '_redirect_rule_status_code', true ) );
			$this->assertEquals(
				1,
				intval(
					$wpdb->get_var(
						$wpdb->prepare(
							"SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE url=%s",
							'/safe-source/'
						)
					),
					10
				)
			);
		} finally {
			delete_post_meta( $post_id, '_redirect_rule_from', '/safe-source/' );
			delete_post_meta( $post_id, '_redirect_rule_to', 'https://example.com/safe-target/' );
			delete_post_meta( $post_id, '_redirect_rule_status_code', '301' );
			wp_delete_post( $post_id, true );

			if ( $group instanceof Red_Group ) {
				$group->delete();
			}

			$wpdb->delete( $wpdb->prefix . 'redirection_items', [ 'url' => '/safe-source/' ] );
		}
	}
}
