<?php

class Apache_Module extends Red_Module {
	const MODULE_ID = 2;

	private $location  = '';
	private $canonical = '';

	public function get_id() {
		return self::MODULE_ID;
	}

	public function can_edit_config() {
		return true;
	}

	protected function load( $data ) {
		$mine = array( 'location', 'canonical' );

		foreach ( $mine AS $key ) {
			if ( isset( $data[$key] ) )
				$this->$key = $data[$key];
		}
	}

	protected function flush_module() {
		include_once dirname( dirname( __FILE__ ) ).'/models/htaccess.php';

		if ( empty( $this->location ) )
			return;

		$items = Red_Item::get_by_module( $this->get_id() );

		// Produce the .htaccess file
		$htaccess = new Red_Htaccess();
		if ( is_array( $items ) && count( $items ) > 0 ) {
			foreach ( $items AS $item ) {
				if ( $item->is_enabled() )
					$htaccess->add( $item );
			}
		}

		return $htaccess->save( $this->location );
	}

	public function update( $data ) {
		include_once dirname( dirname( __FILE__ ) ).'/models/htaccess.php';

		$save = array(
			'location'  => isset( $data['location'] ) ? $data['location'] : false,
			'canonical' => isset( $data['canonical'] ) ? $data['canonical'] : false,
		);

		if ( !empty( $this->location ) && $save['location'] !== $this->location ) {
			// Location has moved. Remove from old location
			$htaccess = new Red_Htaccess();
			$htaccess->save( $this->location, '' );
		}

		$this->load( $save );

		if ( $save['location'] && $this->flush_module() === false )
			return __( 'Cannot write to chosen location - check path and permissions.', 'redirection' );

		$options = red_get_options();
		$options['modules'][self::MODULE_ID] = $save;

		update_option( 'redirection_options', $options );
		return true;
	}

	public function render_config() {
		$without = preg_replace( '@https?://(www)?@', '', get_bloginfo( 'url' ) );
		?>
		<tr>
			<th valign="top" width="170"><?php _e( '.htaccess Location', 'redirection' ); ?>:</th>
			<td>
				<input type="text" name="location" value="<?php echo esc_attr( $this->location ) ?>" style="width: 95%"/>

				<p class="sub"><?php _e( 'If you want Redirection to automatically update your <code>.htaccess</code> file then enter the full path and filename here. You can also download the file and update it manually.', 'redirection' ); ?></p>
				<p class="sub"><?php printf( __( 'WordPress is installed in: <code>%s</code>', 'redirection' ), ABSPATH ); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php _e( 'Canonical URL', 'redirection' ); ?>:</th>
			<td>
				<select name="canonical">
					<option <?php selected( $this->canonical, '' ); ?> value=""><?php _e( 'Default server', 'redirection' ); ?></option>
					<option <?php selected( $this->canonical, 'nowww' ); ?> value="nowww"><?php printf( __( 'Remove WWW (%s)', 'redirection' ), $without ); ?></option>
					<option <?php selected( $this->canonical, 'www' ); ?> value="www"><?php printf( __( 'Add WWW (www.%s)', 'redirection' ), $without ); ?></option>
				</select>

				<p class="sub"><?php _e( 'Automatically remove or add www to your site.', 'redirection' ); ?></p>
			</td>
		</tr>

		<?php
	}

	public function get_config() {
		if ( !empty( $this->location ) )
			return array( sprintf( __( '<code>.htaccess</code> saved to %s', 'redirection' ), esc_html( $this->location ) ) );

		return array();
	}

	public function get_name() {
		return __( 'Apache', 'redirection' );
	}

	public function get_description() {
		return __( 'Uses Apache <code>.htaccess</code> files. Requires further configuration. The redirect happens without loading WordPress. No tracking of hits.', 'redirection' );
	}
}
