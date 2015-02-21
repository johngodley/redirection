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
		$lines   = array();
		$version = get_plugin_data( dirname( dirname( __FILE__ ) ).'/redirection.php' );

		$lines[] = '# Created by Redirection';
		$lines[] = '# '.date ('r');
		$lines[] = '# Redirection '.trim( $version['Version'] ).' - http://urbangiraffe.com/plugins/redirection/';
		$lines[] = '';
		$lines[] = 'server {';

		foreach ( $items AS $item ) {
			$lines[] = '    rewrite ^'.$item->get_url().'$ '.$item->get_action_data().' '.$this->get_redirect_code( $item ).';';
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
}
