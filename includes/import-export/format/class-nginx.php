<?php

namespace Redirection\ImportExport\Format;

use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\ImportRedirect;

/**
 * @phpstan-import-type GroupJson from \Red_Group
 * @phpstan-import-type ImportResult from \Redirection\ImportExport\FormatHandler
 * @phpstan-import-type RedirectMatchData from \Red_Item
 *
 * @phpstan-type SourceMatchOptions array{
 *   flag_query?: 'ignore'|'exact'|'pass'|'exactorder',
 *   flag_case?: bool,
 *   flag_trailing?: bool,
 *   flag_regex?: bool
 * }
 */
class Nginx extends FormatHandler {
	public function force_download() {
		parent::force_download();

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'nginx' ) . '"' );
	}

	/**
	 * @param array<\Red_Item> $items
	 * @param array<GroupJson> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		$lines = [];
		$details = $this->get_export_details();

		$lines[] = '# Created by Redirection';
		$lines[] = '# ' . $details['date'];
		$lines[] = '# Redirection ' . $details['version'] . ' - https://redirection.me';
		$lines[] = '';
		$lines[] = 'server {';

		$parts = [];
		foreach ( $items as $item ) {
			if ( $item->is_enabled() ) {
				$parts[] = $this->get_nginx_item( $item );
			}
		}

		$lines = array_merge(
			$lines,
			array_filter(
				$parts,
				static function ( $part ) {
					return is_string( $part ) && $part !== '';
				}
			)
		);

		$lines[] = '}';
		$lines[] = '';
		$lines[] = '# End of Redirection';

		return implode( PHP_EOL, $lines ) . PHP_EOL;
	}

	/**
	 * @param \Red_Item $item
	 * @return 'permanent'|'redirect'
	 */
	private function get_redirect_code( $item ) {
		if ( $item->get_action_code() === 301 ) {
			return 'permanent';
		}

		return 'redirect';
	}

	/**
	 * @param mixed $group Group resolver.
	 * @param ImportRedirect $redirect Redirect saver.
	 * @param string $filename Path to the file to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @return ImportResult
	 */
	public function load( $group, $redirect, $filename, $is_dry_run ) {
		return $this->get_import_result( $group, $redirect );
	}

	/**
	 * @param \Red_Item $item
	 * @return string|false
	 */
	private function get_nginx_item( $item ) {
		$match_data = $item->get_match_data();
		$match_data = is_array( $match_data ) ? $match_data : [];
		$line = false;

		switch ( $item->get_match_type() ) {
			case 'url':
				$line = $this->add_url( $item, $match_data );
				break;

			case 'agent':
				$line = $this->add_agent( $item, $match_data );
				break;

			case 'referrer':
				$line = $this->add_referrer( $item, $match_data );
				break;
		}

		if ( is_string( $line ) && $line !== '' ) {
			return '    ' . $line;
		}

		return false;
	}

	/**
	 * @param \Red_Item $item
	 * @param RedirectMatchData $match_data
	 * @return string
	 */
	private function add_url( $item, array $match_data ) {
		/** @var SourceMatchOptions|null $source */
		$source = isset( $match_data['source'] ) && is_array( $match_data['source'] ) ? $match_data['source'] : null;
		$regex = $item->source_flags !== null && $item->source_flags->is_regex();

		return $this->get_redirect( $item->get_url(), $item->get_action_data(), $this->get_redirect_code( $item ), $source, $regex );
	}

	/**
	 * @param \Red_Item $item
	 * @param RedirectMatchData $match_data
	 * @return string
	 */
	private function add_agent( $item, array $match_data ) {
		$lines = [];
		/** @var SourceMatchOptions|null $source */
		$source = isset( $match_data['source'] ) && is_array( $match_data['source'] ) ? $match_data['source'] : null;

		$match = $item->match;
		if ( ! ( $match instanceof \Agent_Match ) ) {
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
	 * @param \Red_Item $item
	 * @param RedirectMatchData $match_data
	 * @return string
	 */
	private function add_referrer( $item, array $match_data ) {
		$lines = [];
		/** @var SourceMatchOptions|null $source */
		$source = isset( $match_data['source'] ) && is_array( $match_data['source'] ) ? $match_data['source'] : null;

		$match = $item->match;
		if ( ! ( $match instanceof \Referrer_Match ) ) {
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
	 * @param string $line
	 * @param string $target
	 * @param 'permanent'|'redirect' $code
	 * @param SourceMatchOptions|null $source
	 * @param bool $regex
	 * @return string
	 */
	private function get_redirect( $line, $target, $code, $source, $regex = false ) {
		$line = ltrim( $line, '^' );
		$line = rtrim( $line, '$' );

		$source_url = new \Red_Url_Encode( $line, $regex );
		$target_url = new \Red_Url_Encode( $target );

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
