<?php

class Red_Apache_File extends Red_FileIO {
	var $htaccess;

	function export( array $items ) {
		$filename = 'redirection-'.date_i18n( get_option( 'date_format' ) ).'.htaccess';

		header( 'Content-Type: application/octet-stream' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );

		echo $htaccess->generate( $items );
	}

	public function get( array $items ) {
		include_once dirname( dirname( __FILE__ ) ).'/models/htaccess.php';

		$htaccess = new Red_Htaccess();

		foreach ( $items AS $item ) {
			$htaccess->add( $item );
		}

		return $htaccess->get();
	}

	function load( $group, $data, $filename = '' ) {
		// Remove any comments
		$data = preg_replace ('@#(.*)@', '', $data);
		$data = str_replace ("\n", "\r", $data);
		$data = str_replace ('\\ ', '%20', $data);

		// Split it into lines
		$lines = array_filter (explode ("\r", $data));
		if ( count( $lines ) > 0 ) {
			foreach ( $lines AS $line ) {
				if ( preg_match ('@rewriterule\s+(.*?)\s+(.*?)\s+(\[.*\])*@i', $line, $matches) > 0 )
					$items[] = array('source' => $this->regex_url ($matches[1]), 'target' => $this->decode_url ($matches[2]), 'code' => $this->get_code ($matches[3]), 'regex' => $this->is_regex ($matches[1]) );
				elseif ( preg_match( '@Redirect\s+(.*?)\s+(.*?)\s+(.*)@i', $line, $matches) > 0 )
					$items[] = array( 'source' => $this->decode_url ($matches[2]), 'target' => $this->decode_url ($matches[3]), 'code' => $this->get_code ($matches[1]) );
				elseif ( preg_match( '@Redirect\s+(.*?)\s+(.*?)@i', $line, $matches) > 0 )
					$items[] = array( 'source' => $this->decode_url ($matches[1]), 'target' => $this->decode_url ($matches[2]), 'code' => 302);
				elseif ( preg_match( '@Redirectmatch\s+(.*?)\s+(.*?)\s+(.*)@i', $line, $matches) > 0 )
					$items[] = array( 'source' => $this->decode_url ($matches[2]), 'target' => $this->decode_url ($matches[3]), 'code' => $this->get_code ($matches[1]), 'regex' => true );
				elseif ( preg_match( '@Redirectmatch\s+(.*?)\s+(.*?)@i', $line, $matches) > 0 )
					$items[] = array( 'source' => $this->decode_url ($matches[1]), 'target' => $this->decode_url ($matches[2]), 'code' => 302, 'regex' => true );
			}

			// Add items to group
			if ( count( $items ) > 0 ) {
				foreach ( $items AS $item ) {
					$item['group_id']  = $group;
					$item['red_action'] = 'url';
					$item['match']  = 'url';

					if ( $item['code'] == 0 )
						$item['red_action'] = 'pass';

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
			if ( $url{$x} == '\\' )
				$escape = true;
			elseif ( strpos( $regex, $url{$x} ) !== false && !$escape )
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

			if ( $this->is_str_regex( $tmp ) == false )
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
