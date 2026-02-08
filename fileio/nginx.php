<?php

/**
 * @phpstan-import-type GroupJson from Red_Group
 * @phpstan-import-type RedirectMatchData from Red_Item
 */

class Red_Nginx_File extends Red_FileIO {
	public function force_download() {
		parent::force_download();

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'nginx' ) . '"' );
	}

	/**
	 * @param array<Red_Item> $items
	 * @param array<GroupJson> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		$lines = array();
		$version = red_get_plugin_data( dirname( __DIR__ ) . '/redirection.php' );

		$lines[] = '# Created by Redirection';
		$lines[] = '# ' . gmdate( 'r' );
		$lines[] = '# Redirection ' . trim( $version['Version'] ) . ' - https://redirection.me';
		$lines[] = '';
		$lines[] = 'server {';

		$parts = array();
		foreach ( $items as $item ) {
			if ( $item->is_enabled() ) {
				$parts[] = $this->get_nginx_item( $item );
			}
		}

		$lines = array_merge( $lines, array_filter( $parts ) );

		$lines[] = '}';
		$lines[] = '';
		$lines[] = '# End of Redirection';

		return implode( PHP_EOL, $lines ) . PHP_EOL;
	}

	/**
	 * @return 'permanent'|'redirect'
	 */
	private function get_redirect_code( Red_Item $item ) {
		if ( $item->get_action_code() === 301 ) {
			return 'permanent';
		}
		return 'redirect';
	}

	/**
	 * @param int $group Group ID to import into.
	 * @param string $filename Path to the file to import.
	 * @param string|false $data File contents (or false if not pre-loaded).
	 * @return int
	 */
	public function load( $group, $filename, $data ) {
		return 0;
	}

	/**
	 * @return string|false
	 */
	private function get_nginx_item( Red_Item $item ) {
		$target = 'add_' . $item->get_match_type();

		if ( method_exists( $this, $target ) ) {
			$match_data = $item->get_match_data();
			$match_data = is_array( $match_data ) ? $match_data : array();
			// @phpstan-ignore method.dynamicName
			return '    ' . $this->$target( $item, $match_data );
		}

		return false;
	}

	/**
	 * @param RedirectMatchData $match_data
	 * @return string
	 */
	private function add_url( Red_Item $item, array $match_data ) {
		// @phpstan-ignore booleanAnd.rightAlwaysTrue
		$source = isset( $match_data['source'] ) && is_array( $match_data['source'] ) ? $match_data['source'] : null;
		$regex = $item->source_flags !== null && $item->source_flags->is_regex();

		return $this->get_redirect( $item->get_url(), $item->get_action_data(), $this->get_redirect_code( $item ), $source, $regex );
	}

	/**
	 * @param RedirectMatchData $match_data
	 * @return string
	 */
	private function add_agent( Red_Item $item, array $match_data ) {
		$lines = array();

		// @phpstan-ignore booleanAnd.rightAlwaysTrue
		$source = isset( $match_data['source'] ) && is_array( $match_data['source'] ) ? $match_data['source'] : null;

		// Help PHPStan: ensure we operate on an Agent_Match
		$match = $item->match;
		if ( ! ( $match instanceof Agent_Match ) ) {
			return '';
		}

		if ( $match->url_from !== '' ) {
			$lines[] = 'if ( $http_user_agent ~* ^' . $match->agent . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $match->url_from, $this->get_redirect_code( $item ), $source );
			$lines[] = '    }';
		}

		if ( $match->url_notfrom !== '' ) {
			$lines[] = 'if ( $http_user_agent !~* ^' . $match->agent . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $match->url_notfrom, $this->get_redirect_code( $item ), $source );
			$lines[] = '    }';
		}

		return implode( "\n", $lines );
	}

	/**
	 * @param RedirectMatchData $match_data
	 * @return string
	 */
	private function add_referrer( Red_Item $item, array $match_data ) {
		$lines = array();
		// @phpstan-ignore booleanAnd.rightAlwaysTrue
		$source = isset( $match_data['source'] ) && is_array( $match_data['source'] ) ? $match_data['source'] : null;

		// Help PHPStan: ensure we operate on a Referrer_Match
		$match = $item->match;
		if ( ! ( $match instanceof Referrer_Match ) ) {
			return '';
		}

		if ( $match->url_from !== '' ) {
			$lines[] = 'if ( $http_referer ~* ^' . $match->referrer . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $match->url_from, $this->get_redirect_code( $item ), $source );
			$lines[] = '    }';
		}

		if ( $match->url_notfrom !== '' ) {
			$lines[] = 'if ( $http_referer !~* ^' . $match->referrer . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $match->url_notfrom, $this->get_redirect_code( $item ), $source );
			$lines[] = '    }';
		}

		return implode( "\n", $lines );
	}

	/**
	 * @param string                         $line
	 * @param string                         $target
	 * @param 'permanent'|'redirect'         $code
	 * @param array{
	 *   flag_query?: 'ignore'|'exact'|'pass'|'exactorder',
	 *   flag_case?: bool,
	 *   flag_trailing?: bool,
	 *   flag_regex?: bool
	 * }|null                                 $source
	 * @param bool                           $regex
	 * @return string
	 */
	private function get_redirect( $line, $target, $code, $source, $regex = false ) {
		$line = ltrim( $line, '^' );
		$line = rtrim( $line, '$' );

		$source_url = new Red_Url_Encode( $line, $regex );
		$target_url = new Red_Url_Encode( $target );

		// Remove any existing start/end from a regex
		$from = $source_url->get_as_source();
		$from = ltrim( $from, '^' );
		$from = rtrim( $from, '$' );

		if ( isset( $source['flag_case'] ) && $source['flag_case'] ) {
			$from = '(?i)^' . $from;
		} else {
			$from = '^' . $from;
		}

		return 'rewrite ' . $from . '$ ' . $target_url->get_as_target() . ' ' . $code . ';';
	}
}
