<?php

namespace Redirection\ImportExport\Format;

use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\FileReader;
use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;

/**
 * Import/export redirects as a Netlify/Cloudflare Pages `_redirects` file.
 *
 * Only a core subset is supported: literal or single-trailing-wildcard
 * (`*`/`:splat`) rules with a status of 301, 302, 303, 307, or 308.
 * Rewrites (status 200), named placeholders other than `:splat`, and
 * condition parameters (Country=, Language=, Role=, Query=) are out of
 * scope: they're skipped on import and never produced on export.
 *
 * @phpstan-import-type GroupExport from \Red_Group
 * @phpstan-import-type ImportResult from \Redirection\ImportExport\FormatHandler
 */
class RedirectsFile extends FormatHandler {
	const STATUS_CODES = [ '301', '302', '303', '307', '308' ];

	/**
	 * Matches `$1`/`${1}` as a standalone backreference. A bare `$1` must not be
	 * followed by another digit, otherwise it's part of a different (unsupported)
	 * backreference such as `$10` or `$12` rather than group 1 plus literal digits.
	 */
	const BACKREFERENCE_PATTERN = '/\$\{1\}|\$1(?!\d)/';

	/**
	 * @var FileReader
	 */
	private $files;

	/**
	 * @var int
	 */
	private $skipped = 0;

	/**
	 * @var int
	 */
	private $exported = 0;

	/**
	 * @param FileReader|null $files File reader.
	 */
	public function __construct( ?FileReader $files = null ) {
		$this->files = $files ? $files : new FileReader();
	}

	public function force_download() {
		parent::force_download();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="_redirects"' );
	}

	/**
	 * @param array<\Red_Item> $items
	 * @param array<GroupExport> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		unset( $groups );
		$this->skipped = 0;
		$this->exported = 0;

		$details = $this->get_export_details();
		$lines = [
			'# Created by Redirection',
			'# ' . $details['date'],
			'# Redirection ' . $details['version'] . ' - https://redirection.me',
			'',
		];

		foreach ( $items as $item ) {
			if ( ! $item->is_enabled() ) {
				continue;
			}

			$line = $this->get_as_line( $item );

			if ( $line === false ) {
				$this->skipped++;
				continue;
			}

			$lines[] = $line;
			$this->exported++;
		}

		return implode( PHP_EOL, $lines ) . PHP_EOL;
	}

	/**
	 * @return int
	 */
	public function get_skipped_count() {
		return $this->skipped;
	}

	/**
	 * @return int
	 */
	public function get_exported_count() {
		return $this->exported;
	}

	/**
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $redirect Redirect saver.
	 * @param string $filename Path to the file to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @return ImportResult
	 */
	public function load( $group, $redirect, $filename, $is_dry_run, array $options = [] ) {
		unset( $is_dry_run, $options );

		if ( $filename === '' ) {
			return $this->get_import_result( $group, $redirect );
		}

		$data = $this->files->read( $filename );
		if ( $data === false ) {
			return $this->get_import_result( $group, $redirect );
		}

		return $this->load_from_string( $group, $redirect, $data );
	}

	/**
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $redirect Redirect saver.
	 * @param string $data _redirects file contents.
	 * @return ImportResult
	 */
	public function load_from_string( $group, $redirect, $data ) {
		$data = (string) preg_replace( '/^\xEF\xBB\xBF/', '', $data );
		$data = str_replace( "\r\n", "\n", $data );
		$data = str_replace( "\r", "\n", $data );

		foreach ( explode( "\n", $data ) as $line ) {
			$item = $this->get_as_item( $line );

			if ( $item !== false ) {
				$redirect->save( $item, $group );
			}
		}

		return $this->get_import_result( $group, $redirect );
	}

	/**
	 * @param string $line
	 * @return array<string, mixed>|false
	 */
	public function get_as_item( $line ) {
		$line = trim( $line );

		if ( $line === '' || strpos( $line, '#' ) === 0 ) {
			return false;
		}

		$parts = preg_split( '/\s+/', $line );
		if ( $parts === false || count( $parts ) < 2 || count( $parts ) > 3 ) {
			return false;
		}

		$from = $parts[0];
		$to = $parts[1];
		$status = isset( $parts[2] ) ? (string) preg_replace( '/!$/', '', $parts[2] ) : '301';

		if ( ! in_array( $status, self::STATUS_CODES, true ) ) {
			return false;
		}

		if ( preg_match( '/:(?!splat\b)[a-zA-Z_]/', $from . ' ' . $to ) === 1 ) {
			return false;
		}

		return $this->build_item( $from, $to, intval( $status, 10 ) );
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @param int $code
	 * @return array<string, mixed>|false
	 */
	private function build_item( $from, $to, $code ) {
		$splat_count = substr_count( $from, '*' );

		if ( $splat_count > 1 || ( $splat_count === 1 && substr( $from, -1 ) !== '*' ) ) {
			return false;
		}

		if ( $splat_count === 0 ) {
			if ( strpos( $to, ':splat' ) !== false ) {
				return false;
			}

			return [
				'url' => $from,
				'regex' => false,
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => $to ],
				'action_code' => $code,
			];
		}

		$prefix = substr( $from, 0, -1 );

		return [
			'url' => '^' . preg_quote( $prefix, '#' ) . '(.*)$',
			'regex' => true,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_data' => [ 'url' => str_replace( ':splat', '$1', $to ) ],
			'action_code' => $code,
		];
	}

	/**
	 * @param \Red_Item $item
	 * @return string|false
	 */
	private function get_as_line( $item ) {
		if ( $item->get_match_type() !== 'url' || $item->get_action_type() !== 'url' ) {
			return false;
		}

		if ( ! in_array( strval( $item->get_action_code() ), self::STATUS_CODES, true ) ) {
			return false;
		}

		$from = $this->get_export_source( $item );
		$to = $this->get_export_target( $item );

		if ( $from === false || $to === false ) {
			return false;
		}

		if ( $from === '' || $to === '' || preg_match( '/\s/', $from ) === 1 || preg_match( '/\s/', $to ) === 1 ) {
			return false;
		}

		return $from . ' ' . $to . ' ' . $item->get_action_code();
	}

	/**
	 * @param \Red_Item $item
	 * @return string|false
	 */
	private function get_export_source( $item ) {
		if ( ! $item->is_regex() ) {
			return $item->get_url();
		}

		$pattern = $item->get_url();
		if ( preg_match( '@^\^(.*)\(\.\*\)\$$@', $pattern, $matches ) !== 1 ) {
			return false;
		}

		$prefix = stripslashes( $matches[1] );

		if ( preg_quote( $prefix, '#' ) !== $matches[1] ) {
			return false;
		}

		return $prefix . '*';
	}

	/**
	 * @param \Red_Item $item
	 * @return string|false
	 */
	private function get_export_target( $item ) {
		$target = $item->get_action_data();

		if ( ! $item->is_regex() ) {
			return $target;
		}

		if ( preg_match( self::BACKREFERENCE_PATTERN, $target ) !== 1 ) {
			if ( strpos( $target, '$' ) === false ) {
				// No `$` at all: the target doesn't reference the captured group, so it can
				// be exported as a literal string and still round-trip correctly on import.
				return $target;
			}

			// A `$` is present but isn't an unambiguous `$1`/`${1}` backreference (eg `$10`,
			// `$2`) - this can't be safely resolved, so skip it rather than risk silently
			// wrong output.
			return false;
		}

		$replaced = preg_replace( self::BACKREFERENCE_PATTERN, ':splat', $target );

		if ( $replaced === null || preg_match( '/:splat\w/', $replaced ) === 1 ) {
			// `:splat` immediately followed by a word character (eg "$1" + "0" becoming
			// "splat0") is indistinguishable from an unsupported named placeholder to the
			// importer/sniffer, so it would be silently dropped on re-import. Skip instead
			// of producing a file that can't round-trip.
			return false;
		}

		return $replaced;
	}
}
