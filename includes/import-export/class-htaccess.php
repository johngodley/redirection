<?php

namespace Redirection\ImportExport;

/**
 * Convert redirects to .htaccess format
 *
 * Ignores:
 * - Trailing slash flag
 * - Query flags
 */
class Htaccess {
	/**
	 * @var HtaccessEncoder
	 */
	private $encoder;

	/**
	 * @var HtaccessRuleBuilder
	 */
	private $rule_builder;

	/**
	 * Array of redirect lines
	 *
	 * @var array<string>
	 */
	private $items = [];

	const INSERT_REGEX = '@\n?# Created by Redirection(?:.*?)# End of Redirection\n?@sm';

	/**
	 * @param HtaccessEncoder|null $encoder
	 * @param HtaccessTargetBuilder|null $target_builder
	 * @param HtaccessRuleBuilder|null $rule_builder
	 */
	public function __construct( ?HtaccessEncoder $encoder = null, ?HtaccessTargetBuilder $target_builder = null, ?HtaccessRuleBuilder $rule_builder = null ) {
		$this->encoder = $encoder ? $encoder : new HtaccessEncoder();
		$target_builder = $target_builder ? $target_builder : new HtaccessTargetBuilder( $this->encoder );
		$this->rule_builder = $rule_builder ? $rule_builder : new HtaccessRuleBuilder( $this->encoder, $target_builder );
	}

	/**
	 * Generate the .htaccess file in memory
	 *
	 * @return string
	 */
	private function generate() {
		$details = ExportDetails::get();

		if ( count( $this->items ) === 0 ) {
			return '';
		}

		$text = [
			'# Created by Redirection',
			'# ' . $details['date'],
			'# Redirection ' . $details['version'] . ' - https://redirection.me',
			'',
			'<IfModule mod_rewrite.c>',
		];

		$options = \Red_Options::get();
		if ( $options['https'] !== false ) {
			$text[] = 'RewriteCond %{HTTPS} off';
			$text[] = 'RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]';
		}

		$text = array_merge( $text, array_filter( array_map( [ $this->encoder, 'sanitize_redirect' ], $this->items ) ) );
		$text[] = '</IfModule>';
		$text[] = '';
		$text[] = '# End of Redirection';

		return "\n" . implode( "\n", $text ) . "\n";
	}
	/**
	 * Add a redirect to the file
	 *
	 * @param \Red_Item $item Redirect.
	 * @return void
	 */
	public function add( $item ) {
		foreach ( $this->rule_builder->build_for_item( $item ) as $line ) {
			$this->items[] = $line;
		}
	}

	/**
	 * Get the .htaccess file
	 *
	 * @param string|false $existing Existing .htaccess data.
	 * @return string
	 */
	public function get( $existing = false ) {
		$text = $this->generate();

		if ( $existing !== false ) {
			if ( preg_match( self::INSERT_REGEX, $existing ) > 0 ) {
				$text = (string) preg_replace( self::INSERT_REGEX, str_replace( '$', '\\$', $text ), $existing );
			} else {
				$text = $text . "\n" . trim( $existing );
			}
		}

		return trim( $text );
	}

	/**
	 * Sanitize the redirect
	 *
	 * @param string $text Text.
	 * @return string
	 */
	public function sanitize_redirect( $text ) {
		return $this->encoder->sanitize_redirect( $text );
	}

	/**
	 * Sanitize the filename
	 *
	 * @param string $filename Filename.
	 * @return string
	 */
	public function sanitize_filename( $filename ) {
		return str_replace( '.php', '', sanitize_text_field( $filename ) );
	}

	/**
	 * Save the .htaccess to a file
	 *
	 * @param string $filename Filename to save.
	 * @param bool $content_to_save Content to save.
	 * @return bool
	 */
	public function save( $filename, $content_to_save = false ) {
		$existing = false;
		$filename = $this->sanitize_filename( $filename );

		global $wp_filesystem;
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$filesystem_ready = WP_Filesystem();
		if ( $filesystem_ready !== true || ! isset( $wp_filesystem ) ) {
			return false;
		}

		if ( $wp_filesystem->exists( $filename ) ) {
			$file_contents = $wp_filesystem->get_contents( $filename );
			if ( $file_contents !== false ) {
				$existing = $file_contents;
			}
		}

		return $wp_filesystem->put_contents( $filename, $this->get( $existing ), FS_CHMOD_FILE );
	}
}
