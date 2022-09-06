<?php

namespace Redirection\FileIO;

use Redirection\Redirect;
use Redirection\Url;
use Redirection\Plugin;
use Redirection\Match;

class Nginx extends FileIO {
	public function force_download() {
		parent::force_download();

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'nginx' ) . '"' );
	}

	public function get_data( array $items, array $groups ) {
		$lines   = array();
		$version = Plugin\Settings\red_get_plugin_data( REDIRECTION_FILE );

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

	private function get_redirect_code( Redirect\Redirect $item ) {
		if ( $item->get_action_code() === 301 ) {
			return 'permanent';
		}
		return 'redirect';
	}

	public function load( $group, $data, $filename = '' ) {
		return 0;
	}

	private function get_nginx_item( Redirect\Redirect $item ) {
		$target = 'add_' . $item->get_match_type();

		if ( method_exists( $this, $target ) ) {
			return '    ' . $this->$target( $item, $item->get_match_data() );
		}

		return false;
	}

	/**
	 * Add plain redirect
	 *
	 * @param Redirect\Redirect $item Item.
	 * @param array             $match_data Match data.
	 * @return string
	 **/
	private function add_url( Redirect\Redirect $item, array $match_data ) {
		return $this->get_redirect( $item->get_url(), $item->get_action_data(), $this->get_redirect_code( $item ), $match_data['source'] );
	}

	/**
	 * Add user agent match
	 *
	 * @param Redirect\Redirect $item Item.
	 * @param array             $match_data Match data.
	 * @return string
	 * @suppress PhanUndeclaredProperty
	 **/
	private function add_agent( Redirect\Redirect $item, array $match_data ) {
		if ( ! ( $item->match instanceof Match\User_Agent ) ) {
			return '';
		}

		/** @var Match\User_Agent */
		$match = $item->match;
		$lines = [];

		if ( $match->url_from ) {
			$lines[] = 'if ( $http_user_agent ~* ^' . $match->user_agent . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $match->url_from, $this->get_redirect_code( $item ), $match_data['source'] );
			$lines[] = '    }';
		}

		if ( $match->url_notfrom ) {
			$lines[] = 'if ( $http_user_agent !~* ^' . $match->user_agent . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $match->url_notfrom, $this->get_redirect_code( $item ), $match_data['source'] );
			$lines[] = '    }';
		}

		return implode( "\n", $lines );
	}

	/**
	 * Add referrer match
	 *
	 * @param Redirect\Redirect $item Item.
	 * @param array             $match_data Match data.
	 * @return string
	 * @suppress PhanUndeclaredProperty
	 **/
	private function add_referrer( Redirect\Redirect $item, array $match_data ) {
		if ( ! ( $item->match instanceof Match\Referrer ) ) {
			return '';
		}

		/** @var Match\Referrer */
		$match = $item->match;
		$lines = [];

		if ( $match->url_from ) {
			$lines[] = 'if ( $http_referer ~* ^' . $match->referrer . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $match->url_from, $this->get_redirect_code( $item ), $match_data['source'] );
			$lines[] = '    }';
		}

		if ( $match->url_notfrom ) {
			$lines[] = 'if ( $http_referer !~* ^' . $match->referrer . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $match->url_notfrom, $this->get_redirect_code( $item ), $match_data['source'] );
			$lines[] = '    }';
		}

		return implode( "\n", $lines );
	}

	/**
	 * Get a redirect
	 *
	 * @param string $line Existing line.
	 * @param string $target Target URL.
	 * @param string $code HTTP code.
	 * @param string $source Source URL.
	 * @return string
	 */
	private function get_redirect( $line, $target, $code, $source ) {
		$line = ltrim( $line, '^' );
		$line = rtrim( $line, '$' );

		$source_url = new Url\Encode( $line );
		$target_url = new Url\Encode( $target );

		// Remove any existing start/end from a regex
		$from = $source_url->get_as_source();

		if ( isset( $source['flag_case'] ) && $source['flag_case'] ) {
			$from = '(?i)^' . $from;
		} else {
			$from = '^' . $from;
		}

		return 'rewrite ' . $from . '$ ' . $target_url->get_as_target() . ' ' . $code . ';';
	}
}
