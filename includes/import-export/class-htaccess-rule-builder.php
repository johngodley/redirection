<?php

namespace Redirection\ImportExport;

/**
 * Build .htaccess rule lines for redirects.
 */
class HtaccessRuleBuilder {
	/**
	 * @var HtaccessEncoder
	 */
	private $encoder;

	/**
	 * @var HtaccessTargetBuilder
	 */
	private $target_builder;

	/**
	 * @param HtaccessEncoder|null $encoder
	 * @param HtaccessTargetBuilder|null $target_builder
	 */
	public function __construct( ?HtaccessEncoder $encoder = null, ?HtaccessTargetBuilder $target_builder = null ) {
		$this->encoder = $encoder ? $encoder : new HtaccessEncoder();
		$this->target_builder = $target_builder ? $target_builder : new HtaccessTargetBuilder( $this->encoder );
	}

	/**
	 * @param mixed $item Redirect item.
	 * @return array<string>
	 */
	public function build_for_item( $item ) {
		if ( ! $item->is_enabled() ) {
			return [];
		}

		switch ( $item->get_match_type() ) {
			case 'url':
				if ( $item->match instanceof \URL_Match ) {
					return $this->add_url( $item, $item->match->url );
				}

				return [];

			case 'referrer':
				return $this->add_referrer( $item, $item->match );

			case 'agent':
				return $this->add_agent( $item, $item->match );

			case 'server':
				return $this->add_server( $item, $item->match );
		}

		return [];
	}

	/**
	 * @param mixed $item Redirect item.
	 * @param mixed $match_object Redirect match.
	 * @return array<string>
	 */
	private function add_referrer( $item, $match_object ) {
		$from = $this->get_from( $item, true );

		if ( ( $match_object->url_from === '' && $match_object->url_notfrom === '' ) || $match_object->referrer === '' ) {
			return [];
		}

		$referrer = $match_object->regex ? $this->encoder->encode_regex( $match_object->referrer ) : $this->encoder->encode_from( $match_object->referrer, false );
		$to = $this->get_target_from_match( $item, $match_object->url_from, $match_object->url_notfrom );
		if ( $to === '' ) {
			return [];
		}

		return [
			sprintf( 'RewriteCond %%{HTTP_REFERER} %s [NC]', $referrer ),
			sprintf( 'RewriteRule %s %s', $from, $to ),
		];
	}

	/**
	 * @param mixed $item Redirect item.
	 * @param mixed $match_object Redirect match.
	 * @return array<string>
	 */
	private function add_agent( $item, $match_object ) {
		$from = $this->get_from( $item, false );

		if ( ( $match_object->url_from === '' && $match_object->url_notfrom === '' ) || $match_object->agent === '' ) {
			return [];
		}

		$agent = $match_object->regex ? $this->encoder->encode_regex( $match_object->agent ) : $this->encoder->encode_target( $match_object->agent );
		$to = $this->get_target_from_match( $item, $match_object->url_from, $match_object->url_notfrom );
		if ( $to === '' ) {
			return [];
		}

		return [
			sprintf( 'RewriteCond %%{HTTP_USER_AGENT} %s [NC]', $agent ),
			sprintf( 'RewriteRule %s %s', $from, $to ),
		];
	}

	/**
	 * @param mixed $item Redirect item.
	 * @param mixed $match_object Redirect match.
	 * @return array<string>
	 */
	private function add_server( $item, $match_object ) {
		$lines = [];
		$host = wp_parse_url( $match_object->server, PHP_URL_HOST );
		if ( is_string( $host ) ) {
			$lines[] = sprintf( 'RewriteCond %%{HTTP_HOST} ^%s$ [NC]', preg_quote( $host, '/' ) );
		}

		return array_merge( $lines, $this->add_url( $item, $match_object->url_from ) );
	}

	/**
	 * @param mixed $item Redirect item.
	 * @param string $target_url Target URL.
	 * @return array<string>
	 */
	private function add_url( $item, $target_url ) {
		$lines = [];
		$url = $item->get_url();

		if ( $item->is_regex() === false && strpos( $url, '?' ) !== false ) {
			$url_parts = wp_parse_url( $url );

			if ( isset( $url_parts['path'] ) ) {
				$url = $url_parts['path'];
				$query = isset( $url_parts['query'] ) ? $url_parts['query'] : '';
				$lines[] = sprintf( 'RewriteCond %%{QUERY_STRING} ^%s$', $query );
			}
		}

		$to = '';
		$match_data = $item->get_match_data();
		if ( $match_data !== null && $target_url !== '' ) {
			$to = $this->target_builder->build( $item->get_action_type(), $target_url, $item->get_action_code(), $match_data );
		}

		$from = $this->encoder->encode_from( $url, $item->source_flags !== null && $item->source_flags->is_ignore_trailing() );
		if ( $item->is_regex() ) {
			$from = $this->encoder->encode_regex( $item->get_url() );
		}

		if ( $to !== '' ) {
			$lines[] = sprintf( 'RewriteRule %s %s', trim( $from ), trim( $to ) );
		}

		return $lines;
	}

	/**
	 * @param mixed $item Redirect item.
	 * @param bool $use_from_encoding
	 * @return string
	 */
	private function get_from( $item, $use_from_encoding ) {
		if ( $item->is_regex() ) {
			return $this->encoder->encode_regex( ltrim( $item->get_url(), '/' ) );
		}

		if ( $use_from_encoding ) {
			return $this->encoder->encode_from( ltrim( $item->get_url(), '/' ), $item->source_flags !== null && $item->source_flags->is_ignore_trailing() );
		}

		return $this->encoder->encode_path( ltrim( $item->get_url(), '/' ) );
	}

	/**
	 * @param mixed $item Redirect item.
	 * @param string $url_from
	 * @param string $url_notfrom
	 * @return string
	 */
	private function get_target_from_match( $item, $url_from, $url_notfrom ) {
		$match_data = $item->get_match_data();
		if ( $match_data === null ) {
			return '';
		}

		if ( $url_from !== '' ) {
			return $this->target_builder->build( $item->get_action_type(), $url_from, $item->get_action_code(), $match_data );
		}

		if ( $url_notfrom !== '' ) {
			return $this->target_builder->build( $item->get_action_type(), $url_notfrom, $item->get_action_code(), $match_data );
		}

		return '';
	}
}
