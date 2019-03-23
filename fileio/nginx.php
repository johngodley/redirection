<?php

class Red_Nginx_File extends Red_FileIO {
	public function force_download() {
		parent::force_download();

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'nginx' ) . '"' );
	}

	public function get_data( array $items, array $groups ) {
		$lines   = array();
		$version = red_get_plugin_data( dirname( dirname( __FILE__ ) ) . '/redirection.php' );

		$lines[] = '# Created by Redirection';
		$lines[] = '# ' . date( 'r' );
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

	private function get_redirect_code( Red_Item $item ) {
		if ( $item->get_action_code() === 301 ) {
			return 'permanent';
		}
		return 'redirect';
	}

	function load( $group, $data, $filename = '' ) {
		return 0;
	}

	private function get_nginx_item( Red_Item $item ) {
		$target = 'add_' . $item->get_match_type();

		if ( method_exists( $this, $target ) ) {
			return '    ' . $this->$target( $item, $item->get_match_data() );
		}

		return false;
	}

	private function add_url( Red_Item $item, array $match_data ) {
		return $this->get_redirect( $item->get_url(), $item->get_action_data(), $this->get_redirect_code( $item ), $match_data['source'] );
	}

	private function add_agent( Red_Item $item, array $match_data ) {
		if ( $item->match->url_from ) {
			$lines[] = 'if ( $http_user_agent ~* ^' . $item->match->user_agent . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $item->match->url_from, $this->get_redirect_code( $item ), $match_data['source'] );
			$lines[] = '    }';
		}

		if ( $item->match->url_notfrom ) {
			$lines[] = 'if ( $http_user_agent !~* ^' . $item->match->user_agent . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $item->match->url_notfrom, $this->get_redirect_code( $item ), $match_data['source'] );
			$lines[] = '    }';
		}

		return implode( "\n", $lines );
	}

	private function add_referrer( Red_Item $item, array $match_data ) {
		if ( $item->match->url_from ) {
			$lines[] = 'if ( $http_referer ~* ^' . $item->match->referrer . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $item->match->url_from, $this->get_redirect_code( $item ), $match_data['source'] );
			$lines[] = '    }';
		}

		if ( $item->match->url_notfrom ) {
			$lines[] = 'if ( $http_referer !~* ^' . $item->match->referrer . '$ ) {';
			$lines[] = '        ' . $this->get_redirect( $item->get_url(), $item->match->url_notfrom, $this->get_redirect_code( $item ), $match_data['source'] );
			$lines[] = '    }';
		}

		return implode( "\n", $lines );
	}

	private function get_redirect( $line, $target, $code, $source ) {
		// Remove any existing start/end from a regex
		$line = ltrim( $line, '^' );
		$line = rtrim( $line, '$' );

		if ( isset( $source['flag_case'] ) && $source['flag_case'] ) {
			$line = '(?i)^' . $line;
		} else {
			$line = '^' . $line;
		}

		$line = preg_replace( "/[\r\n\t].*?$/s", '', $line );
		$line = preg_replace( '/[^\PC\s]/u', '', $line );
		$target = preg_replace( "/[\r\n\t].*?$/s", '', $target );
		$target = preg_replace( '/[^\PC\s]/u', '', $target );

		return 'rewrite ' . $line . '$ ' . $target . ' ' . $code . ';';
	}
}
