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

	function module_flush ($items) {
		// Produce the .htaccess file
		include_once (dirname (__FILE__).'/../models/htaccess.php');

		$htaccess = new Red_Htaccess ($this);
		if (is_array ($items) && count ($items) > 0)
		{
			foreach ($items AS $item)
				$htaccess->add ($item);
		}

		$htaccess->save ($this->location, $this->name);
	}

	public function update( $data ) {
		$save = array(
			'location'  => isset( $data['location'] ) ? $data['location'] : false,
			'canonical' => isset( $data['canonical'] ) ? $data['canonical'] : false,
		);

		$this->load( $save );

		$options = red_get_options();
		$options['modules'][self::MODULE_ID] = $save;

		update_option( 'redirection_options', $options );
	}

	public function render_config() {
		$without = preg_replace( '@https?://(www)?@', '', get_bloginfo( 'url' ) );
		?>
		<tr>
			<th valign="top" width="170"><?php _e( '.htaccess Location', 'redirection' ); ?>:</th>
			<td>
				<input type="text" name="location" value="<?php echo esc_attr( $this->location ) ?>" style="width: 95%"/>

				<p class="sub"><?php _e( 'If you want Redirection to automatically update your .htaccess file then enter the path here.', 'redirection' ); ?></p>
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

	public function get_name() {
		return __( 'Apache', 'redirection' );
	}

	public function get_description() {
		return __( 'Uses Apache <code>.htaccess</code> files. Requires further configuration, but the redirect happens without loading WordPress. No tracking of hits.', 'redirection' );
	}
}
