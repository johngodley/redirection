<?php

/**
 * @phpstan-import-type RedModuleOptions from Red_Module
 */
class Apache_Module extends Red_Module {
	const MODULE_ID = 2;

	/**
	 * Location path for .htaccess file
	 *
	 * @var string
	 */
	private $location = '';

	/**
	 * Get module ID
	 *
	 * @return int
	 */
	public function get_id() {
		return self::MODULE_ID;
	}

	/**
	 * Get module name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Apache';
	}

	/**
	 * Get .htaccess file location
	 *
	 * @return string
	 */
	public function get_location() {
		return $this->location;
	}

	/**
	 * Load module data
	 *
	 * @param RedModuleOptions $options Options.
	 * @return void
	 */
	protected function load( array $options ) {
		if ( isset( $options['location'] ) ) {
			$this->location = $options['location'];
		}
	}

	/**
	 * Flush module by regenerating .htaccess file
	 *
	 * @return bool
	 */
	protected function flush_module() {
		include_once dirname( __DIR__ ) . '/models/htaccess.php';

		if ( empty( $this->location ) ) {
			return false;
		}

		$items = Red_Item::get_all_for_module( $this->get_id() );

		// Produce the .htaccess file
		$htaccess = new Red_Htaccess();
		if ( count( $items ) > 0 ) {
			foreach ( $items as $item ) {
				if ( $item->is_enabled() ) {
					$htaccess->add( $item );
				}
			}
		}

		return $htaccess->save( $this->location );
	}

	/**
	 * Check if .htaccess file can be saved to the given location
	 *
	 * @param string $location File path.
	 * @return WP_Error|true
	 */
	public function can_save( $location ) {
		$location = $this->sanitize_location( $location );

		$file = fopen( $location, 'a' );
		if ( $file === false ) {
			$error = error_get_last();
			return new WP_Error( 'redirect', isset( $error['message'] ) ? $error['message'] : 'Unknown error' );
		}

		fclose( $file );
		return true;
	}

	/**
	 * Sanitize location path to ensure it ends with .htaccess
	 *
	 * @param string $location File path.
	 * @return string
	 */
	private function sanitize_location( $location ) {
		$location = str_replace( '.htaccess', '', $location );
		$location = rtrim( $location, '/' ) . '/.htaccess';
		return rtrim( dirname( $location ), '/' ) . '/.htaccess';
	}

	/**
	 * Update module configuration
	 *
	 * @param RedModuleOptions $data Data.
	 * @return array<string, string>|false
	 */
	public function update( array $data ) {
		include_once dirname( __DIR__ ) . '/models/htaccess.php';

		$new_location = isset( $data['location'] ) ? $data['location'] : '';
		if ( strlen( $new_location ) > 0 ) {
			$new_location = $this->sanitize_location( trim( $new_location ) );
		}

		$save = [
			'location' => $new_location,
		];

		if ( ! empty( $this->location ) && $save['location'] !== $this->location && $save['location'] !== '' ) {
			// Location has moved. Remove from old location
			$htaccess = new Red_Htaccess();
			$htaccess->save( $this->location, false );
		}

		$this->load( $save );

		if ( $save['location'] !== '' && $this->flush_module() === false ) {
			$save['location'] = '';
		}

		return $save;
	}
}
