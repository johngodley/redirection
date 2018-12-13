<?php

class Red_Plugin_Importer {
	public static function get_plugins() {
		$results = array();

		$importers = array(
			'wp-simple-redirect',
			'seo-redirection',
			'safe-redirect-manager',
			'wordpress-old-slugs',
		);

		foreach ( $importers as $importer ) {
			$importer = Red_Plugin_Importer::get_importer( $importer );
			$results[] = $importer->get_data();
		}

		return array_values( array_filter( $results ) );
	}

	public static function get_importer( $id ) {
		if ( $id === 'wp-simple-redirect' ) {
			return new Red_Simple301_Importer();
		}

		if ( $id === 'seo-redirection' ) {
			return new Red_SeoRedirection_Importer();
		}

		if ( $id === 'safe-redirect-manager' ) {
			return new Red_SafeRedirectManager_Importer();
		}

		if ( $id === 'wordpress-old-slugs' ) {
			return new Red_WordPressOldSlug_Importer();
		}

		return false;
	}

	public static function import( $plugin, $group_id ) {
		$importer = Red_Plugin_Importer::get_importer( $plugin );
		if ( $importer ) {
			return $importer->import_plugin( $group_id );
		}

		return 0;
	}
}

class Red_Simple301_Importer extends Red_Plugin_Importer {
	public function import_plugin( $group_id ) {
		$redirects = get_option( '301_redirects' );
		$count = 0;

		foreach ( $redirects as $source => $target ) {
			$item = $this->create_for_item( $group_id, $source, $target );

			if ( $item ) {
				$count++;
			}
		}

		return $count;
	}

	private function create_for_item( $group_id, $source, $target ) {
		$item = array(
			'url'         => str_replace( '*', '(.*?)', $source ),
			'action_data' => array( 'url' => str_replace( '*', '$1', trim( $target ) ) ),
			'regex'       => strpos( $source, '*' ) === false ? false : true,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);

		return Red_Item::create( $item );
	}

	public function get_data() {
		$data = get_option( '301_redirects' );

		if ( $data ) {
			return array(
				'id' => 'wp-simple-redirect',
				'name' => 'Simple 301 Redirects',
				'total' => count( $data ),
			);
		}

		return false;
	}
}

class Red_WordPressOldSlug_Importer extends Red_Plugin_Importer {
	public function import_plugin( $group_id ) {
		global $wpdb;

		$count = 0;
		$redirects = $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id " .
			"WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);

		foreach ( $redirects as $redirect ) {
			$item = $this->create_for_item( $group_id, $redirect );

			if ( $item ) {
				$count++;
			}
		}

		return $count;
	}

	private function create_for_item( $group_id, $redirect ) {
		$new = get_permalink( $redirect->post_id );
		if ( is_wp_error( $new ) ) {
			return false;
		}

		$new = wp_parse_url( $new, PHP_URL_PATH );
		$old = rtrim( dirname( $new ), '/' ) . '/' . rtrim( $redirect->meta_value, '/' ) . '/';
		$old = str_replace( '\\', '', $old );

		$data = array(
			'url'         => $old,
			'action_data' => array( 'url' => $new ),
			'regex'       => false,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);

		return Red_Item::create( $data );
	}

	public function get_data() {
		global $wpdb;

		$total = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);

		if ( $total ) {
			return array(
				'id' => 'wordpress-old-slugs',
				'name' => __( 'Default WordPress "old slugs"', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}

class Red_SeoRedirection_Importer extends Red_Plugin_Importer {
	public function import_plugin( $group_id ) {
		global $wpdb;

		$count = 0;
		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}WP_SEO_Redirection" );

		foreach ( $redirects as $redirect ) {
			$item = $this->create_for_item( $group_id, $redirect );

			if ( $item ) {
				$count++;
			}
		}

		return $count;
	}

	private function create_for_item( $group_id, $seo ) {
		if ( intval( $seo->enabled, 10 ) === 0 ) {
			return false;
		}

		$data = array(
			'url'         => $seo->regex ? $seo->regex : $seo->redirect_from,
			'action_data' => array( 'url' => $seo->redirect_to ),
			'regex'       => $seo->regex ? true : false,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => intval( $seo->redirect_type, 10 ),
		);

		return Red_Item::create( $data );
	}

	public function get_data() {
		global $wpdb;

		$plugins = get_option( 'active_plugins', array() );
		$found = false;

		foreach ( $plugins as $plugin ) {
			if ( strpos( $plugin, 'seo-redirection.php' ) !== false ) {
				$found = true;
				break;
			}
		}

		if ( $found ) {
			$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}WP_SEO_Redirection" );

			return array(
				'id' => 'seo-redirection',
				'name' => 'SEO Redirection',
				'total' => $total,
			);
		}

		return false;
	}
}

class Red_SafeRedirectManager_Importer extends Red_Plugin_Importer {
	public function import_plugin( $group_id ) {
		global $wpdb;

		$count = 0;
		$redirects = $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key LIKE '_redirect_rule_%' AND {$wpdb->prefix}posts.post_status='publish'"
		);

		// Group them by post ID
		$by_post = array();
		foreach ( $redirects as $redirect ) {
			if ( ! isset( $by_post[ $redirect->post_id ] ) ) {
				$by_post[ $redirect->post_id ] = array();
			}

			$by_post[ $redirect->post_id ][ str_replace( '_redirect_rule_', '', $redirect->meta_key ) ] = $redirect->meta_value;
		}

		// Now go through the redirects
		foreach ( $by_post as $post ) {
			$item = $this->create_for_item( $group_id, $post );

			if ( $item ) {
				$count++;
			}
		}

		return $count;
	}

	private function create_for_item( $group_id, $post ) {
		$regex = false;
		$source = $post['from'];

		if ( strpos( $post['from'], '*' ) !== false ) {
			$regex = true;
			$source = str_replace( '*', '.*', $source );
		} elseif ( isset( $post['from_regex'] ) && $post['from_regex'] === '1' ) {
			$regex = true;
		}

		$data = array(
			'url'         => $source,
			'action_data' => array( 'url' => $post['to'] ),
			'regex'       => $regex,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => intval( $post['status_code'], 10 ),
		);

		return Red_Item::create( $data );
	}

	public function get_data() {
		global $wpdb;

		$total = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key = '_redirect_rule_from' AND {$wpdb->prefix}posts.post_status='publish'"
		);

		if ( $total ) {
			return array(
				'id' => 'safe-redirect-manager',
				'name' => 'Safe Redirect Manager',
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
