<?php

class Red_Apache_File extends Red_FileIO {
	public function force_download() {
		parent::force_download();

		$filename = 'redirection-'.date_i18n( get_option( 'date_format' ) ).'.htaccess';

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );
	}

	public function get_data( array $items, array $groups ) {
		include_once dirname( dirname( __FILE__ ) ).'/models/htaccess.php';

		$htaccess = new Red_Htaccess();

		foreach ( $items as $item ) {
			$htaccess->add( $item );
		}

		return $htaccess->get().PHP_EOL;
	}

	public function load( $group, $filename, $data ) {
		// Remove any comments
		$data = preg_replace( '@#(.*)@', '', $data );
		$data = str_replace( "\n", "\r", $data );
		$data = str_replace( '\\ ', '%20', $data );

		// Split it into lines
		$lines = array_filter( explode( "\r", $data ) );

		$items = array();
		if ( count( $lines ) > 0 ) {
			foreach ( $lines as $line ) {
				if ( preg_match( '@rewriterule\s+(.*?)\s+(.*?)\s+(\[.*\])*@i', $line, $matches ) > 0 ) {
					$items[] = array(
						'source' => $this->regex_url( $matches[1] ),
						'target' => $this->decode_url( $matches[2] ),
						'code' => $this->get_code( $matches[3] ),
						'regex' => $this->is_regex( $matches[1] ),
					);
				}
				elseif ( preg_match( '@Redirect\s+(.*?)\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
					$items[] = array(
						'source' => $this->decode_url( $matches[2] ),
						'target' => $this->decode_url( $matches[3] ),
						'code' => $this->get_code( $matches[1] ),
					);
				}
				elseif ( preg_match( '@Redirect\s+(.*?)\s+(.*?)@i', $line, $matches ) > 0 ) {
					$items[] = array(
						'source' => $this->decode_url( $matches[1] ),
						'target' => $this->decode_url( $matches[2] ),
						'code' => 302,
					);
				}
				elseif ( preg_match( '@Redirectmatch\s+(.*?)\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
					$items[] = array(
						'source' => $this->decode_url( $matches[2] ),
						'target' => $this->decode_url( $matches[3] ),
						'code' => $this->get_code( $matches[1] ),
						'regex' => true,
					);
				}
				elseif ( preg_match( '@Redirectmatch\s+(.*?)\s+(.*?)@i', $line, $matches ) > 0 ) {
					$items[] = array(
						'source' => $this->decode_url( $matches[1] ),
						'target' => $this->decode_url( $matches[2] ),
						'code' => 302,
						'regex' => true,
					);
				}
			}

			// Add items to group
			if ( count( $items ) > 0 ) {
				foreach ( $items as $item ) {
					$item['group_id'] = $group;
					$item['action_type'] = 'url';
					$item['match_type'] = 'url';

					if ( $item['code'] === 0 ) {
						$item['action_type'] = 'pass';
					}

					Red_Item::create( $item );
				}

				return count( $items );
			}
		}

		return 0;
	}

	function decode_url( $url ) {
		$url = rawurldecode( $url );
		$url = str_replace( '\\.', '.', $url );
		return $url;
	}

	function is_str_regex( $url ) {
		$regex  = '()[]$^?+.';
		$escape = false;

		for ( $x = 0; $x < strlen( $url ); $x++ ) {
			if ( $url{$x} === '\\' )
				$escape = true;
			elseif ( strpos( $regex, $url{$x} ) !== false && ! $escape )
				return true;
			else
				$escape = false;
		}

		return false;
	}

	function is_regex( $url ) {
		if ( $this->is_str_regex( $url ) ) {
			$tmp = ltrim( $url, '^' );
			$tmp = rtrim( $tmp, '$' );

			if ( $this->is_str_regex( $tmp ) )
				return true;
		}

		return false;
	}

	function regex_url ($url) {
		if ( $this->is_str_regex( $url ) ) {
			$tmp = ltrim( $url, '^' );
			$tmp = rtrim( $tmp, '$' );

			if ( $this->is_str_regex( $tmp ) === false )
				return '/'.$this->decode_url( $tmp );

			return '/'.$this->decode_url( $url );
		}

		return $this->decode_url( $url );
	}

	function get_code ($code) {
		if ( strpos( $code, '301' ) !== false || stripos( $code, 'permanent' ) !== false )
			return 301;
		elseif ( strpos( $code, '302' ) !== false )
			return 302;
		elseif ( strpos( $code, '307' ) !== false || stripos( $code, 'seeother' ) !== false )
			return 307;
		elseif ( strpos( $code, '404' ) !== false || stripos( $code, 'forbidden' ) !== false || strpos( $code, 'F' ) !== false )
			return 404;
		elseif ( strpos( $code, '410' ) !== false || stripos( $code, 'gone' ) !== false || strpos( $code, 'G' ) !== false )
			return 410;
		return 0;
	}
}
