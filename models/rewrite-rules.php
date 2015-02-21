<?php

class Red_Rewrite_Rules {
	private $items = array();
	const INSERT_REGEX = '@\n?# Created by Redirection(.*?)# End of Redirection\n?@sm';

	public function add( $item ) {
		if ( $item->get_match_type() === 'url' )
			$this->add_url( $item, $item->match );
	}

	public function get( $existing = false ) {
		$text = $this->generate();

		if ( $existing ) {
			if ( preg_match( self::INSERT_REGEX, $existing ) > 0 )
				$text = preg_replace( self::INSERT_REGEX, $text, $existing );
			else
				$text = trim( $existing )."\n".$text;
		}

		return trim( $text );
	}

	public function save( $filename, $content_to_save = false ) {
		$existing = false;

		if ( file_exists( $filename ) )
			$existing = @file_get_contents( $filename );

		return @file_put_contents( $filename, $this->get( $existing ) );
	}
}
