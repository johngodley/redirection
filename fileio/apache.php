<?php

class Red_Apache_File extends Red_FileIO {
	public function force_download() {
		parent::force_download();

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'htaccess' ) . '"' );
	}

	public function get_data( array $items, array $groups ) {
		include_once dirname( dirname( __FILE__ ) ) . '/models/htaccess.php';

		$htaccess = new Red_Htaccess();

		foreach ( $items as $item ) {
			$htaccess->add( $item );
		}

		return $htaccess->get() . PHP_EOL;
	}

	public function load( $group, $filename, $data ) {
		// Remove any comments
		$data = str_replace( "\n", "\r", $data );

		// Split it into lines
		$lines = array_filter( explode( "\r", $data ) );
		$count = 0;

		foreach ( (array) $lines as $line ) {
			$item = $this->get_as_item( $line );

			if ( $item ) {
				$item['group_id'] = $group;
				$redirect = Red_Item::create( $item );

				if ( ! is_wp_error( $redirect ) ) {
					$count++;
				}
			}
		}

		return $count;
	}

	public function get_as_item( $line ) {
		$item = false;

		if ( preg_match( '@rewriterule\s+(.*?)\s+(.*?)\s+(\[.*\])*@i', $line, $matches ) > 0 ) {
			$item = array(
				'url' => $this->regex_url( $matches[1] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => array( 'url' => $this->decode_url( $matches[2] ) ),
				'action_code' => $this->get_code( $matches[3] ),
				'regex' => $this->is_regex( $matches[1] ),
			);
		} elseif ( preg_match( '@Redirect\s+(.*?)\s+"(.*?)"\s+(.*)@i', $line, $matches ) > 0 || preg_match( '@Redirect\s+(.*?)\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
			$item = array(
				'url' => $this->decode_url( $matches[2] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => array( 'url' => $this->decode_url( $matches[3] ) ),
				'action_code' => $this->get_code( $matches[1] ),
			);
		} elseif ( preg_match( '@Redirect\s+"(.*?)"\s+(.*)@i', $line, $matches ) > 0 || preg_match( '@Redirect\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
			$item = array(
				'url' => $this->decode_url( $matches[1] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => array( 'url' => $this->decode_url( $matches[2] ) ),
				'action_code' => 302,
			);
		} elseif ( preg_match( '@Redirectmatch\s+(.*?)\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
			$item = array(
				'url' => $this->decode_url( $matches[2] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => array( 'url' => $this->decode_url( $matches[3] ) ),
				'action_code' => $this->get_code( $matches[1] ),
				'regex' => true,
			);
		} elseif ( preg_match( '@Redirectmatch\s+(.*?)\s+(.*)@i', $line, $matches ) > 0 ) {
			$item = array(
				'url' => $this->decode_url( $matches[1] ),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => array( 'url' => $this->decode_url( $matches[2] ) ),
				'action_code' => 302,
				'regex' => true,
			);
		}

		if ( $item ) {
			$item['action_type'] = 'url';
			$item['match_type'] = 'url';

			if ( $item['action_code'] === 0 ) {
				$item['action_type'] = 'pass';
			}

			return $item;
		}

		return false;
	}

	private function decode_url( $url ) {
		$url = rawurldecode( $url );

		// Replace quoted slashes
		$url = preg_replace( '@\\\/@', '/', $url );

		// Ensure escaped '.' is still escaped
		$url = preg_replace( '@\\\\.@', '\\\\.', $url );
		return $url;
	}

	private function is_str_regex( $url ) {
		$regex  = '()[]$^?+.';
		$escape = false;
		$len = strlen( $url );

		for ( $x = 0; $x < $len; $x++ ) {
			$escape = false;
			$char = substr( $url, $x, 1 );

			if ( $char === '\\' ) {
				$escape = true;
			} elseif ( strpos( $regex, $char ) !== false && ! $escape ) {
				return true;
			}
		}

		return false;
	}

	private function is_regex( $url ) {
		if ( $this->is_str_regex( $url ) ) {
			$tmp = ltrim( $url, '^' );
			$tmp = rtrim( $tmp, '$' );

			if ( $this->is_str_regex( $tmp ) ) {
				return true;
			}
		}

		return false;
	}

	private function regex_url( $url ) {
		$url = $this->decode_url( $url );

		if ( $this->is_str_regex( $url ) ) {
			$tmp = ltrim( $url, '^' );
			$tmp = rtrim( $tmp, '$' );

			if ( $this->is_str_regex( $tmp ) ) {
				return '^/' . ltrim( $tmp, '/' );
			}

			return '/' . ltrim( $tmp, '/' );
		}

		return $this->decode_url( $url );
	}

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
