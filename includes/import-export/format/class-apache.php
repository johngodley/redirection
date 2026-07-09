<?php

namespace Redirection\ImportExport\Format;

use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\FileReader;
use Redirection\ImportExport\Htaccess;
use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;

/**
 * @phpstan-import-type GroupExport from \Red_Group
 * @phpstan-import-type ImportResult from \Redirection\ImportExport\FormatHandler
 */
class Apache extends FormatHandler {
	/**
	 * @var FileReader
	 */
	private $files;

	/**
	 * @var Htaccess
	 */
	private $htaccess;

	/**
	 * @param FileReader|null $files File reader.
	 * @param Htaccess|null $htaccess .htaccess exporter.
	 */
	public function __construct( ?FileReader $files = null, ?Htaccess $htaccess = null ) {
		$this->files = $files ? $files : new FileReader();
		$this->htaccess = $htaccess ? $htaccess : new Htaccess();
	}

	public function force_download() {
		parent::force_download();

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'htaccess' ) . '"' );
	}

	/**
	 * @param array<\Red_Item>  $items
	 * @param array<GroupExport> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		$htaccess = clone $this->htaccess;

		foreach ( $items as $item ) {
			$htaccess->add( $item );
		}

		return $htaccess->get() . PHP_EOL;
	}

	/**
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $redirect Redirect saver.
	 * @param string $filename Path to the file to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @return ImportResult
	 */
	public function load( $group, $redirect, $filename, $is_dry_run, array $options = [] ) {
		unset( $options );
		if ( $filename === '' ) {
			return $this->get_import_result( $group, $redirect );
		}

		$data = $this->files->read( $filename );
		if ( $data === false ) {
			return $this->get_import_result( $group, $redirect );
		}

		return $this->load_from_string( $group, $redirect, $data, $is_dry_run );
	}

	/**
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $redirect Redirect saver.
	 * @param string $data Apache config data to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @return ImportResult
	 */
	public function load_from_string( $group, $redirect, $data, $is_dry_run ) {
		$data = str_replace( "\n", "\r", $data );
		$lines = array_filter(
			explode( "\r", $data ),
			static function ( $line ) {
				return strlen( $line ) > 0;
			}
		);
		foreach ( $lines as $line ) {
			$item = $this->get_as_item( $line );

			if ( $item !== false && $redirect->save( $item, $group ) ) {
				continue;
			}
		}

		return $this->get_import_result( $group, $redirect );
	}

	/**
	 * @param string $line
	 * @return array<string, mixed>|false
	 */
	public function get_as_item( $line ) {
		$item = false;

		if ( preg_match( '@rewriterule\s+(.*?)\s+(.*?)\s+(\[.*\])*@i', $line, $matches ) > 0 ) {
			$item = [
				'url' => $this->regex_url( $matches[1] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => $this->decode_url( $matches[2] ) ],
				'action_code' => $this->get_code( $matches[3] ?? '' ),
				'regex' => $this->is_regex( $matches[1] ),
			];

			$match_data = $this->get_match_data( $matches[1], $matches[3] ?? '' );
			if ( $match_data !== null ) {
				$item['match_data'] = $match_data;
			}
		} elseif ( preg_match( '@Redirect\s+(.*?)\s+"(.*?)"\s+(.*)@i', $line, $matches ) > 0 || preg_match( '@Redirect\s+(.*?)\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
			$item = [
				'url' => $this->decode_url( $matches[2] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => $this->decode_url( $matches[3] ) ],
				'action_code' => $this->get_code( $matches[1] ),
			];
		} elseif ( preg_match( '@Redirect\s+"(.*?)"\s+(.*)@i', $line, $matches ) > 0 || preg_match( '@Redirect\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
			$item = [
				'url' => $this->decode_url( $matches[1] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => $this->decode_url( $matches[2] ) ],
				'action_code' => 302,
			];
		} elseif ( preg_match( '@Redirectmatch\s+(.*?)\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
			$item = [
				'url' => $this->decode_url( $matches[2] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => $this->decode_url( $matches[3] ) ],
				'action_code' => $this->get_code( $matches[1] ),
				'regex' => true,
			];
		} elseif ( preg_match( '@Redirectmatch\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
			$item = [
				'url' => $this->decode_url( $matches[1] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => $this->decode_url( $matches[2] ) ],
				'action_code' => 302,
				'regex' => true,
			];
		}

		if ( $item !== false ) {
			$item['action_type'] = 'url';
			$item['match_type'] = 'url';

			if ( $item['action_code'] === 0 ) {
				$item['action_type'] = 'pass';
			}

			return $item;
		}

		return false;
	}

	/**
	 * @param string $url
	 * @param bool $preserve_escaped_dots Preserve escaped literal dots.
	 * @return string
	 */
	private function decode_url( $url, $preserve_escaped_dots = false ) {
		$url = rawurldecode( $url );
		$url = (string) preg_replace( '@\\\/@', '/', $url );

		if ( ! $preserve_escaped_dots ) {
			$url = (string) preg_replace( '@\\\\\\.@', '.', $url );
		}

		return $url;
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	private function is_str_regex( $url ) {
		$regex = '()[]$^?+.';
		$len = strlen( $url );

		for ( $x = 0; $x < $len; $x++ ) {
			$char = substr( $url, $x, 1 );

			if ( $char !== '\\' && strpos( $regex, $char ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	private function is_regex( $url ) {
		if ( $this->is_optional_trailing_standard_rule( $url ) ) {
			return false;
		}

		if ( $this->is_str_regex( $url ) ) {
			$tmp = ltrim( $url, '^' );
			if ( $this->has_end_anchor( $tmp ) ) {
				$tmp = substr( $tmp, 0, -1 );
			}

			if ( $this->is_str_regex( $tmp ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	private function regex_url( $url ) {
		$standard_url = $this->get_optional_trailing_standard_url( $url );

		if ( $standard_url !== false ) {
			return $standard_url;
		}

		$url = $this->decode_url( $url, true );

		if ( $this->is_str_regex( $url ) ) {
			$has_start = strpos( $url, '^' ) === 0;
			$has_end = $this->has_end_anchor( $url );
			$tmp = ltrim( $url, '^' );
			if ( $has_end ) {
				$tmp = substr( $tmp, 0, -1 );
			}

			if ( $this->is_str_regex( $tmp ) ) {
				return ( $has_start ? '^' : '' ) . '/' . ltrim( $tmp, '/' ) . ( $has_end ? '$' : '' );
			}

			return '/' . ltrim( $tmp, '/' );
		}

		return $this->decode_url( $url );
	}

	/**
	 * @param string $url
	 * @param string $flags
	 * @return array{source: array<string, bool|string>}|null
	 */
	private function get_match_data( $url, $flags ) {
		$source = [];

		if ( stripos( $flags, 'NC' ) !== false ) {
			$source['flag_case'] = true;
		}

		if ( stripos( $flags, 'QSA' ) !== false ) {
			$source['flag_query'] = 'pass';
		}

		if ( $this->is_optional_trailing_standard_rule( $url ) ) {
			$source['flag_trailing'] = true;
		}

		if ( count( $source ) === 0 ) {
			return null;
		}

		return [ 'source' => $source ];
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	private function is_optional_trailing_standard_rule( $url ) {
		return $this->get_optional_trailing_standard_url( $url ) !== false;
	}

	/**
	 * @param string $url
	 * @return string|false
	 */
	private function get_optional_trailing_standard_url( $url ) {
		$tmp = ltrim( $url, '^' );

		if ( ! $this->has_end_anchor( $tmp ) ) {
			return false;
		}

		$tmp = substr( $tmp, 0, -1 );
		if ( substr( $tmp, -2 ) !== '/?' ) {
			return false;
		}

		$tmp = substr( $tmp, 0, -2 );
		if ( $tmp === '' || $tmp === false ) {
			return '/';
		}

		if ( $this->is_standard_rewrite_path( $tmp ) === false ) {
			return false;
		}

		return '/' . ltrim( $this->decode_url( $tmp ), '/' );
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	private function is_standard_rewrite_path( $url ) {
		$url = (string) preg_replace( '@\\\\\\.@', '', $url );

		return ! $this->is_str_regex( $url );
	}

	/**
	 * Determine if a regex ends with an unescaped $ anchor.
	 *
	 * @param string $url
	 * @return bool
	 */
	private function has_end_anchor( $url ) {
		$length = strlen( $url );

		if ( $length === 0 || substr( $url, -1 ) !== '$' ) {
			return false;
		}

		$slashes = 0;

		for ( $pos = $length - 2; $pos >= 0; $pos-- ) {
			if ( substr( $url, $pos, 1 ) !== '\\' ) {
				break;
			}

			$slashes++;
		}

		return $slashes % 2 === 0;
	}

	/**
	 * @param string $code
	 * @return int
	 */
	private function get_code( $code ) {
		if ( strpos( $code, '301' ) !== false || stripos( $code, 'permanent' ) !== false ) {
			return 301;
		}

		if ( strpos( $code, '302' ) !== false ) {
			return 302;
		}

		if ( strpos( $code, '307' ) !== false || stripos( $code, 'seeother' ) !== false ) {
			return 307;
		}

		if ( strpos( $code, '404' ) !== false || stripos( $code, 'forbidden' ) !== false || strpos( $code, 'F' ) !== false ) {
			return 404;
		}

		if ( strpos( $code, '410' ) !== false || stripos( $code, 'gone' ) !== false || strpos( $code, 'G' ) !== false ) {
			return 410;
		}

		return 302;
	}
}
