<?php

class Red_Nginx_File extends Red_FileIO {
	function export( array $items ) {
		$filename = 'redirection-'.date_i18n( get_option( 'date_format' ) ).'.nginx';

		header( 'Content-Type: application/octet-stream' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );

		echo $this->get( $items );
	}

	public function get( array $items ) {
		if ( count( $items ) === 0 )
			return '';

		$lines   = array();
		$version = get_plugin_data( dirname( dirname( __FILE__ ) ).'/redirection.php' );

		$lines[] = '# Created by Redirection';
		$lines[] = '# '.date ('r');
		$lines[] = '# Redirection '.trim( $version['Version'] ).' - http://urbangiraffe.com/plugins/redirection/';
		$lines[] = '';
		$lines[] = 'server {';

		foreach ( $items AS $item ) {
			$lines[] = $this->get_nginx_item( $item );
		}

		$lines[] = '}';
		$lines[] = '';
		$lines[] = '# End of Redirection';

		return implode( "\n", $lines );
	}

	private function get_redirect_code( Red_Item $item ) {
		if ( $item->get_action_code() === 301 )
			return 'permanent';
		return 'redirect';
	}

	function load( $group, $data, $filename = '' ) {
		return 0;
	}

	private function get_nginx_item( Red_Item $item ) {
		$target = 'add_'.$item->get_match_type();

		if ( method_exists( $this, $target ) )
			return '    '.$this->$target( $item, $item->match );
		return false;
	}

	private function add_url( Red_Item $item ) {
		return $this->add_redirect( $item->get_url(), $item->get_action_data(), $this->get_redirect_code( $item ) );
	}

	private function add_agent( Red_Item $item ) {
		if ( $item->match->url_from ) {
			$lines[] = 'if ( $http_user_agent ~* ^'.$item->match->user_agent.'$ ) {';
			$lines[] = '        '.$this->add_redirect( $item->get_url(), $item->match->url_from, $this->get_redirect_code( $item ) );
			$lines[] = '    }';
		}

		if ( $item->match->url_notfrom ) {
			$lines[] = 'if ( $http_user_agent !~* ^'.$item->match->user_agent.'$ ) {';
			$lines[] = '        '.$this->add_redirect( $item->get_url(), $item->match->url_notfrom, $this->get_redirect_code( $item ) );
			$lines[] = '    }';
		}

		return implode( "\n", $lines );
	}

	private function add_referrer( Red_Item $item ) {
		if ( $item->match->url_from ) {
			$lines[] = 'if ( $http_referer ~* ^'.$item->match->referrer.'$ ) {';
			$lines[] = '        '.$this->add_redirect( $item->get_url(), $item->match->url_from, $this->get_redirect_code( $item ) );
			$lines[] = '    }';
		}

		if ( $item->match->url_notfrom ) {
			$lines[] = 'if ( $http_referer !~* ^'.$item->match->referrer.'$ ) {';
			$lines[] = '        '.$this->add_redirect( $item->get_url(), $item->match->url_notfrom, $this->get_redirect_code( $item ) );
			$lines[] = '    }';
		}

		return implode( "\n", $lines );
	}

	private function add_redirect( $source, $target, $code ) {
		return 'rewrite ^'.$source.'$ '.$target.' '.$code.';';
	}
}


