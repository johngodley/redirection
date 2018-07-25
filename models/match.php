<?php

abstract class Red_Match {
	public function __construct( $values = '' ) {
		if ( $values ) {
			$this->load( $values );
		}
	}

	abstract public function save( array $details, $no_target_url = false );
	abstract public function name();
	abstract public function get_target( $url, $matched_url, $regex );
	abstract public function get_data();
	abstract public function load( $values );

	public function sanitize_url( $url ) {
		// No new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		return $url;
	}

	protected function get_target_regex_url( $matched_url, $target, $url ) {
		return preg_replace( '@' . str_replace( '@', '\\@', $matched_url ) . '@', $target, $url );
	}

	static function create( $name, $data = '' ) {
		$avail = self::available();
		if ( isset( $avail[ strtolower( $name ) ] ) ) {
			$classname = $name . '_match';

			if ( ! class_exists( strtolower( $classname ) ) ) {
				include( dirname( __FILE__ ) . '/../matches/' . $avail[ strtolower( $name ) ] );
			}

			return new $classname( $data );
		}

		return false;
	}

	static function all() {
		$data = array();

		$avail = self::available();
		foreach ( $avail as $name => $file ) {
			$obj = self::create( $name );
			$data[ $name ] = $obj->name();
		}

		return $data;
	}

	static function available() {
		return array(
			'url'      => 'url.php',
			'referrer' => 'referrer.php',
			'agent'    => 'user-agent.php',
			'login'    => 'login.php',
			'header'   => 'http-header.php',
			'custom'   => 'custom-filter.php',
			'cookie'   => 'cookie.php',
			'role'     => 'user-role.php',
			'server'   => 'server.php',
		);
	}
}

trait FromNotFrom_Match {
	public $url_from;
	public $url_notfrom;

	private function save_data( array $details, $no_target_url, array $data ) {
		if ( $no_target_url === false ) {
			return array_merge( array(
				'url_from' => isset( $details['url_from'] ) ? $this->sanitize_url( $details['url_from'] ) : '',
				'url_notfrom' => isset( $details['url_notfrom'] ) ? $this->sanitize_url( $details['url_notfrom'] ) : '',
			), $data );
		}

		return $data;
	}

	private function get_matched_target( $matched ) {
		if ( $this->url_from !== '' && $matched ) {
			return $this->url_from;
		}

		if ( $this->url_notfrom !== '' && ! $matched ) {
			return $this->url_notfrom;
		}

		return false;
	}

	private function load_data( $values ) {
		$values = unserialize( $values );

		if ( isset( $values['url_from'] ) ) {
			$this->url_from = $values['url_from'];
		}

		if ( isset( $values['url_notfrom'] ) ) {
			$this->url_notfrom = $values['url_notfrom'];
		}

		return $values;
	}

	private function get_from_data() {
		return array(
			'url_from' => $this->url_from,
			'url_notfrom' => $this->url_notfrom,
		);
	}
}
