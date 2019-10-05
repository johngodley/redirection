<?php

class Apache_Module extends Red_Module {
	const MODULE_ID = 2;

	private $location  = '';

	public function get_id() {
		return self::MODULE_ID;
	}

	public function get_name() {
		return 'Apache';
	}

	public function get_location() {
		return $this->location;
	}

	protected function load( $data ) {
		$mine = array( 'location' );

		foreach ( $mine as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$this->$key = $data[ $key ];
			}
		}
	}

	protected function flush_module() {
		include_once dirname( dirname( __FILE__ ) ) . '/models/htaccess.php';

		if ( empty( $this->location ) ) {
			return;
		}

		$items = Red_Item::get_all_for_module( $this->get_id() );

		// Produce the .htaccess file
		$htaccess = new Red_Htaccess();
		if ( is_array( $items ) && count( $items ) > 0 ) {
			foreach ( $items as $item ) {
				if ( $item->is_enabled() ) {
					$htaccess->add( $item );
				}
			}
		}

		return $htaccess->save( $this->location );
	}

	public function can_save( $location ) {
		$location = $this->sanitize_location( $location );

		if ( @fopen( $location, 'a' ) === false ) {
			$error = error_get_last();
			return new WP_Error( 'redirect', isset( $error['message'] ) ? $error['message'] : 'Unknown error' );
		}

		return true;
	}

	private function sanitize_location( $location ) {
		$location = str_replace( '.htaccess', '', $location );
		$location = rtrim( $location, '/' ) . '/.htaccess';
		return rtrim( dirname( $location ), '/' ) . '/.htaccess';
	}

	public function update( array $data ) {
		include_once dirname( dirname( __FILE__ ) ) . '/models/htaccess.php';

		$save = [
			'location' => isset( $data['location'] ) ? $this->sanitize_location( trim( $data['location'] ) ) : '',
		];

		if ( ! empty( $this->location ) && $save['location'] !== $this->location ) {
			// Location has moved. Remove from old location
			$htaccess = new Red_Htaccess();
			$htaccess->save( $this->location, '' );
		}

		$this->load( $save );

		if ( $save['location'] !== '' && $this->flush_module() === false ) {
			$save['location'] = '';
		}

		return $save;
	}
}
