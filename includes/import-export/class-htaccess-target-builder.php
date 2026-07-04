<?php

namespace Redirection\ImportExport;

/**
 * Build .htaccess redirect targets and flags.
 */
class HtaccessTargetBuilder {
	/**
	 * @var HtaccessEncoder
	 */
	private $encoder;

	/**
	 * @var callable|null
	 */
	private $random_target_resolver;

	/**
	 * @param HtaccessEncoder|null $encoder
	 * @param callable|null $random_target_resolver
	 */
	public function __construct( ?HtaccessEncoder $encoder = null, $random_target_resolver = null ) {
		$this->encoder = $encoder ? $encoder : new HtaccessEncoder();
		$this->random_target_resolver = $random_target_resolver;
	}

	/**
	 * @param string $action Action type.
	 * @param string $data Target URL data.
	 * @param int $code HTTP status code.
	 * @param array<string, mixed> $match_data Match data including source flags.
	 * @return string
	 */
	public function build( $action, $data, $code, array $match_data ) {
		switch ( $action ) {
			case 'random':
				return $this->action_random( $data, $code, $match_data );

			case 'pass':
				return $this->action_pass( $data, $code, $match_data );

			case 'error':
				return $this->action_error( $data, $code, $match_data );

			case 'url':
				return $this->action_url( $data, $code, $match_data );
		}

		return '';
	}

	/**
	 * @param string $current Current redirect rule.
	 * @param array<string> $flags Flags to add.
	 * @return string
	 */
	private function add_flags( string $current, array $flags ) {
		return $current . ' [' . implode( ',', $flags ) . ']';
	}

	/**
	 * @param array<string> $existing Existing flags.
	 * @param array<string, mixed> $source Source flags.
	 * @param string $url URL.
	 * @return array<string>
	 */
	private function get_source_flags( array $existing, array $source, string $url ) {
		$flags = [];

		if ( isset( $source['flag_case'] ) && $source['flag_case'] !== false ) {
			$flags[] = 'NC';
		}

		if ( isset( $source['flag_query'] ) && $source['flag_query'] === 'pass' ) {
			$flags[] = 'QSA';
		}

		if ( strpos( $url, '#' ) !== false || strpos( $url, '%' ) !== false ) {
			$flags[] = 'NE';
		}

		return array_merge( $existing, $flags );
	}

	/**
	 * @param string $data Target URL data.
	 * @param int $code HTTP status code.
	 * @param array<string, mixed> $match_data Match data including source flags.
	 * @return string
	 */
	private function action_random( string $data, int $code, array $match_data ) {
		$path = $this->resolve_random_target_path();
		if ( $path === '' ) {
			return '';
		}

		$flags = [ sprintf( 'R=%d', $code ), 'L' ];
		$flags = $this->get_source_flags( $flags, $match_data['source'], $data );

		return $this->add_flags( $this->encoder->encode_path( $path ), $flags );
	}

	/**
	 * @param string $data Target URL data.
	 * @param int $code HTTP status code.
	 * @param array<string, mixed> $match_data Match data including source flags.
	 * @return string
	 */
	private function action_pass( string $data, int $code, array $match_data ) {
		$flags = $this->get_source_flags( [ 'L' ], $match_data['source'], $data );

		return $this->add_flags( $this->encoder->encode_target( $data ), $flags );
	}

	/**
	 * @param string $data Target URL data.
	 * @param int $code HTTP status code.
	 * @param array<string, mixed> $match_data Match data including source flags.
	 * @return string
	 */
	private function action_error( string $data, int $code, array $match_data ) {
		$flags = $this->get_source_flags( [ 'F' ], $match_data['source'], $data );

		if ( $code === 410 ) {
			$flags = $this->get_source_flags( [ 'G' ], $match_data['source'], $data );
		}

		return $this->add_flags( '/', $flags );
	}

	/**
	 * @param string $data Target URL data.
	 * @param int $code HTTP status code.
	 * @param array<string, mixed> $match_data Match data including source flags.
	 * @return string
	 */
	private function action_url( string $data, int $code, array $match_data ) {
		$flags = [ sprintf( 'R=%d', $code ), 'L' ];
		$flags = $this->get_source_flags( $flags, $match_data['source'], $data );

		return $this->add_flags( $this->encoder->encode_target( $data ), $flags );
	}

	/**
	 * @return string
	 */
	private function resolve_random_target_path() {
		if ( is_callable( $this->random_target_resolver ) ) {
			return (string) call_user_func( $this->random_target_resolver );
		}

		global $wpdb;

		$post = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} ORDER BY RAND() LIMIT 0,1" );
		$permalink = get_permalink( $post );
		if ( $permalink === false ) {
			return '';
		}

		$url = wp_parse_url( $permalink );
		if ( $url === false || ! isset( $url['path'] ) ) {
			return '';
		}

		return is_string( $url['path'] ) ? $url['path'] : '';
	}
}
