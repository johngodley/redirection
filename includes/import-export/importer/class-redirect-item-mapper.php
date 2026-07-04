<?php

namespace Redirection\ImportExport\Importer;

class RedirectItemMapper {
	/**
	 * @param string $source Source pattern.
	 * @param string $target Target URL.
	 * @return array<string, mixed>
	 */
	public function simple301( $source, $target ) {
		return array(
			'url' => str_replace( '*', '(.*?)', $source ),
			'action_data' => array( 'url' => str_replace( '*', '$1', trim( $target ) ) ),
			'regex' => strpos( $source, '*' ) !== false,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);
	}

	/**
	 * @param string $source Source URL.
	 * @param string $target Target URL.
	 * @return array<string, mixed>
	 */
	public function quick_redirects( $source, $target ) {
		return array(
			'url' => $source,
			'action_data' => array( 'url' => $target ),
			'regex' => false,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);
	}

	/**
	 * @param array<string, mixed> $redirect Redirect data.
	 * @return array<string, mixed>|false
	 */
	public function slim_seo( array $redirect ) {
		if ( empty( $redirect['from'] ) || empty( $redirect['to'] ) || empty( $redirect['enable'] ) ) {
			return false;
		}

		$condition = isset( $redirect['condition'] ) ? $redirect['condition'] : 'exact-match';
		$source = $this->normalize_source( (string) $redirect['from'] );
		$regex = false;

		if ( $condition === 'regex' ) {
			$regex = true;
		} elseif ( $condition === 'start-with' ) {
			$regex = true;
			$source = '^' . preg_quote( $source, '#' );
		} elseif ( $condition === 'end-with' ) {
			$regex = true;
			$source = preg_quote( $source, '#' ) . '/?$';
		} elseif ( $condition === 'contain' ) {
			$regex = true;
			$source = '.*' . preg_quote( ltrim( $source, '/' ), '#' ) . '.*';
		}

		return array(
			'url' => $source,
			'action_data' => array( 'url' => $this->normalize_target( (string) $redirect['to'] ) ),
			'regex' => $regex,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => isset( $redirect['type'] ) ? intval( $redirect['type'], 10 ) : 301,
		);
	}

	/**
	 * @param string $source Source URL.
	 * @return string
	 */
	public function normalize_source( $source ) {
		if ( substr( $source, 0, 1 ) !== '/' && substr( $source, 0, 1 ) !== '^' ) {
			return '/' . $source;
		}

		return $source;
	}

	/**
	 * @param string $target Target URL.
	 * @return string
	 */
	public function normalize_target( $target ) {
		if ( preg_match( '@^https?://@i', $target ) === 1 || substr( $target, 0, 1 ) === '/' ) {
			return $target;
		}

		return '/' . ltrim( $target, '/' );
	}

	/**
	 * @param string $permalink Resolved permalink.
	 * @param string $old_slug Old slug value.
	 * @return array<string, mixed>|false
	 */
	public function wordpress_old_slug( $permalink, $old_slug ) {
		$new_path = wp_parse_url( $permalink, PHP_URL_PATH );
		if ( $new_path === false || $new_path === null ) {
			return false;
		}

		$old = rtrim( dirname( $new_path ), '/' ) . '/' . rtrim( $old_slug, '/' ) . '/';
		$old = str_replace( '\\', '', $old );
		$old = str_replace( '//', '/', $old );

		return array(
			'url' => $old,
			'action_data' => array( 'url' => $permalink ),
			'regex' => false,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);
	}

	/**
	 * @param string $source Source URL.
	 * @param string $target Target URL.
	 * @param int $code Redirect code.
	 * @return array<string, mixed>|false
	 */
	public function seopress_content( $source, $target, $code ) {
		if ( $source === '' || $target === '' || $code === 0 ) {
			return false;
		}

		return array(
			'url' => $this->get_path( $source ),
			'action_data' => array( 'url' => $target ),
			'regex' => false,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => $code,
		);
	}

	/**
	 * @param string $source Source value.
	 * @param string $target Target URL.
	 * @param int $code Redirect code.
	 * @param bool $regex Is regex redirect.
	 * @return array<string, mixed>|false
	 */
	public function seopress_404( $source, $target, $code, $regex ) {
		if ( $target === '' || $source === '' || $code === 0 ) {
			return false;
		}

		if ( ! $regex && substr( $source, 0, 1 ) !== '/' ) {
			$source = '/' . ltrim( $source, '/' );
		}

		return array(
			'url' => $source,
			'action_data' => array( 'url' => $target ),
			'regex' => $regex,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => $code,
		);
	}

	/**
	 * @param string $url URL.
	 * @return string
	 */
	public function get_path( $url ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( ! is_string( $path ) || $path === '' ) {
			return '/';
		}

		return $path;
	}

	/**
	 * @param array<string, int|string> $post Redirect data.
	 * @return array<string, mixed>|false
	 */
	public function safe_redirect_manager( array $post ) {
		$regex = false;
		$source = isset( $post['from'] ) ? (string) $post['from'] : '';
		$target = isset( $post['to'] ) ? (string) $post['to'] : '';
		$code = isset( $post['status_code'] ) ? intval( $post['status_code'], 10 ) : 0;

		if ( $source === '' || $target === '' || $code === 0 ) {
			return false;
		}

		if ( strpos( $source, '*' ) !== false ) {
			$regex = true;
			$source = str_replace( '*', '.*', $source );
		} elseif ( isset( $post['from_regex'] ) && (string) $post['from_regex'] === '1' ) {
			$regex = true;
		}

		return array(
			'url' => $source,
			'action_data' => array( 'url' => $target ),
			'regex' => $regex,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => $code,
		);
	}

	/**
	 * @param object{match: string, to: string, redirect_code?: string} $redirect Redirect row.
	 * @return array<string, mixed>|false
	 */
	public function fake_redirection( $redirect ) {
		$source = (string) $redirect->match;
		$target = (string) $redirect->to;
		$code = isset( $redirect->redirect_code ) ? intval( $redirect->redirect_code, 10 ) : 301;

		if ( $source === '' || $target === '' || $code === 0 ) {
			return false;
		}

		return array(
			'url' => $source,
			'action_data' => array( 'url' => $target ),
			'regex' => false,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => $code,
		);
	}

	/**
	 * @param string $source Source URL.
	 * @param string|false $target Target URL.
	 * @param int $code Redirect code.
	 * @return array<string, mixed>|false
	 */
	public function eps301( $source, $target, $code ) {
		if ( $target === false || $source === '' || $code === 0 ) {
			return false;
		}

		return array(
			'url' => '/' . ltrim( $source, '/' ),
			'action_data' => array( 'url' => $target ),
			'regex' => false,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => $code,
		);
	}
}
