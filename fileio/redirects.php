<?php
const ESCAPED_REGEX = '/\\./';

class Red_Redirects_File extends Red_FileIO {
	public function force_download() {
		parent::force_download();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="_redirects"' );
	}

	public function get_data( array $items, array $groups ) {
		$lines = array();
		$columns_size = array();

		foreach ( $items as $item ) {
			$group_id = $item->get_group_id();

			if ( ! isset( $lines[ $group_id ] ) ) {
				$lines[ $group_id ] = array();
				$columns_size[ $group_id ] = array_fill( 0, 10, 0 );
			}

			foreach ( $this->item_as_lines( $item ) as $data ) {
				$columns_size[ $group_id ] = array_map( function ( $size, $value ) {
					return max( $size, strlen( $value ) );
				}, $columns_size[ $group_id ], array_slice( $data, 0, -1 ) );

				array_push( $lines[ $group_id ], $data );
			}
		}

		$groups = array_combine( array_map( function ( $group ) {
			return $group['id'];
		}, $groups ), $groups );

		$contents = array();

		foreach ( $lines as $group_id => $group_lines ) {
			$group = $groups[ $group_id ];

			$current_column_size = $columns_size[ $group_id ];

			$content = "# Group {$group_id}";
			if ( $group['name'] ) {
				$content .= ": {$group['name']}";
			}

			foreach ( $group_lines as $line ) {
				$content .= PHP_EOL . trim( implode( array_map( function ( int $column, string|null $value ) use ( $current_column_size ) {
					$column_size = $current_column_size[ $column ];

					if ( ! $column_size ) {
						return '';
					}

					return str_pad( $value, $column_size + 1);
				}, array_keys( $line ), $line ) ) );
			}

			$contents[] = $content . PHP_EOL;
		}

		return trim( implode( PHP_EOL, $contents ) );
	}

	public function item_as_lines( Red_Item $item ) {
		if ( ! $item->is_enabled() ) {
			return array();
		}

		$id = $item->get_id();
		$source = $item->get_url();
		$data = $item->match->get_data();
		$code = $item->get_action_code();
		$is_regex = $item->is_regex();
		$title = $item->get_title();
		$match_type = $item->get_match_type();
		$match_data = $item->get_match_data();

		switch ( $match_type ) {
			case 'cookie':
				trigger_error( "Degraded Cookie conversion, _redirects only supports cookie presence." , E_USER_NOTICE );

				return array(
					$this->item_as_line( $source, $data['url_from'], $code, $match_data['source'], $title, array( 'cookie' => array( $data['name'] ) ) ),
					$this->item_as_line( $source, $data['url_notfrom'], $code, $match_data['source'], $title, array() ),
				);
			case 'url':
				return array(
					$this->item_as_line( $source, $data['url'], $code, $match_data['source'], $title, array() ),
				);
			case 'role':
				return array(
					$this->item_as_line( $source, $data['url_from'], $code, $match_data['source'], $title, array( 'role' => explode( ',', $data['role'] ) ) ),
					$this->item_as_line( $source, $data['url_notfrom'], $code, $match_data['source'], $title, array() ),
				);
			case 'language':
				return array(
					$this->item_as_line( $source, $data['url_from'], $code, $match_data['source'], $title, array( 'language' => explode( ',', $data['language'] ) ) ),
					$this->item_as_line( $source, $data['url_notfrom'], $code, $match_data['source'], $title, array() ),
				);
			default:
				break;
		}

		trigger_error( "Invalid item {$id}: unsupported type {$match_type}" , E_USER_NOTICE );

		if ( $title ) {
			return array(
				array( "# ERROR: Not supported item {$id} ($title)" ),
			);
		}

		return array(
			array( "# ERROR: Not supported item {$id}" ),
		);
	}

	public function item_as_line( string $source, string $target, int $code, array $flags, string $title, array $extra ) {
		$params_str = '';

		if ( isset( $flags[ Red_Source_Flags::FLAG_REGEX ] ) && $flags[ Red_Source_Flags::FLAG_REGEX ] ) {
			$replacer = new FromRegexReplacer();

			$source = $replacer->replace_source( $source );
			$target = $replacer->replace_target( $target );
		} elseif ( isset( $flags[ Red_Source_Flags::FLAG_QUERY ] ) ) {
			switch ( $flags[ Red_Source_Flags::FLAG_QUERY ] ) {
				case 'exactorder':
					list( $_, $query_params ) = $this->extract_query_params( $source );
					list( $target, $target_query_params ) = $this->extract_query_params( $target );

					$query_params = array_merge( $target_query_params, $query_params );
					if ( count( $query_params ) > 0 ) {
						$target .= '?' . http_build_query( $query_params );
					}

					break;
				case 'exact':
					list( $source, $query_params ) = $this->extract_query_params( $source );
					list( $target, $target_query_params ) = $this->extract_query_params( $target );

					$params_str = http_build_query( $query_params, '', ' ' );
					$query_params = array_merge( $target_query_params, $query_params );
					if ( count( $query_params ) > 0 ) {
						$target .= '?' . http_build_query( $query_params );
					}

					break;
				case 'pass':
					list( $source, $query_params ) = $this->extract_query_params( $source );
					list( $target, $target_query_params ) = $this->extract_query_params( $target );

					$counter = 0;
					$params = array_map( function ( string $key, string $value ) use ( &$counter, &$target ) {
						return $key . '=:param' . ++$counter;
					}, array_keys( $query_params ), $query_params );

					$params_str = join( ' ', $params );
					array_push( $params, http_build_query( $target_query_params ) );
					if ( count( $params ) > 0 ) {
						$target .= '?' . join( '&', $params );
					}

					break;
				case 'ignore':
					list( $source, $_ ) = $this->extract_query_params( $source );

					break;
				default:
					trigger_error( "Unsupported query flag {$flags[ Red_Source_Flags::FLAG_QUERY ]}" , E_USER_ERROR );
			}

		}

		$title = trim( $title );

		$extra = join( ' ', array_map( function ( string $key, array $value ) {
			return ucfirst( $key ) . '=' . join( ',', array_map( 'rawurlencode', $value ) );
		}, array_keys( $extra ), $extra ) );

		return array( $this->encode( $source ), $params_str, $this->encode( $target ), $code . '!', $extra, $title ? "# $title" : '');
	}

	private function encode( string $url ) {
		return preg_replace_callback( "/[\ \"<>`\\x{0080}-\\x{FFFF}]+/u", function ( array $match ) {
			return rawurlencode( $match[0] );
		}, $url );
	}

	private function extract_query_params( string &$url ) {
		$component = parse_url( $url );
		if ( ! $component ) {
			trigger_error( "Invalid source URL $source in item $id", E_USER_ERROR );

			return array( $url, array() );
		}

		if ( ! isset( $component['query'] ) ) {
			return array( $url, array() );
		}

		parse_str( $component['query'], $query_params );

		return array( $this->http_build_url( $component ), $query_params );
	}

	private function http_build_url( array $component ) {
		if ( function_exists( 'http_build_url' ) ) {
			unset( $component['query'] );
			unset( $component['fragment'] );
			unset( $component['user'] );
			unset( $component['password'] );

			return http_build_url( $component );
		}

		$url = '';

		if ( ! empty( $component['scheme'] ) ) {
			$url .= $component['scheme'] . '://';
		}

		if ( ! empty( $component['host'] ) ) {
			$url .= $component['host'];
		}

		if ( ! empty( $component['port'] ) ) {
			$url .= ':' . $component['port'];
		}

		$url .= $component['path'];

		return $url;
	}

	public function load( $group, $filename, $data ) {
		ini_set( 'auto_detect_line_endings', true );

		try {
			$file = fopen( $filename, 'r' );
		} finally {
			ini_set( 'auto_detect_line_endings', false );
		}

		if ( ! $file ) {
			return 0;
		}

		return $this->load_from_file( $group, $file );
	}

	public function load_from_file( $group_id, $file ) {
		global $wpdb;

		$count = 0;

		while ( ( $line = fgets( $file ) ) ) {
			$item = $this->line_as_item( $line, $group_id );

			if ( $item && $this->item_is_valid( $item ) ) {
				$created = Red_Item::create( $item );

				// The query log can use up all the memory
				$wpdb->queries = [];

				if ( ! is_wp_error( $created ) ) {
					$count++;
				}
			}
		}

		return $count;
	}

	private function item_is_valid( array $csv ) {
		if ( strlen( $csv['url'] ) === 0 ) {
			return false;
		}

		if ( $csv['action_data']['url'] === $csv['url'] ) {
			return false;
		}

		return true;
	}

	private function get_valid_code( string $code ) {
		if ( get_status_header_desc( $code ) !== '' ) {
			return intval( $code, 10 );
		}

		return 301;
	}

	private function get_action_type( $code ) {
		if ( $code > 400 && $code < 500 ) {
			return 'error';
		}

		return 'url';
	}

	public function line_as_item( $line, $group ) {
		$title = '';

		$line = trim(preg_replace_callback_array( array(
			// escaped character
			ESCAPED_REGEX => function ( array $matches ) { return $matches[0]; },

			// placeholder
			'/#(?P<title>.*)$/' => function ( array $matches ) use ( &$title ) {
				$title = trim( $matches['title'] );

				return "";
			},
		), $line ));

		// Ignore Country, Language, Role or Cookie matching
		$data = preg_split( '/\s+/', $line );
		if ( ! $data || count( $data ) < 2 ) {
			return false;
		}

		$source = array_shift( $data );
		$query_params = array();

		while ( false !== ( $query_param = array_shift( $data ) ) ) {
			if ( strpos( $query_param, '=' ) === false ) {
				array_unshift( $data, $query_param );

				break;
			}

			list( $key, $value ) = explode( '=', $query_param, 2 );

			$query_params[ $key ] = $value;
		}

		$target = array_shift( $data );
		$code = array_shift( $data );
		if ( '!' == substr( $code, -1 ) ) {
			$code = substr( $code, 0, -1 );
			$force = true;
		} else {
			$force = false;
		}
		if ( ! is_numeric( $code ) ) {
			$extra = $force ? $code . '!' : $code; // not supported yet
			$code = 301;
		} else {
			$code = $this->get_valid_code( $code );
		}

		if ( '*' === substr( $source, -1 ) ) {
			$regex = true;
		} else {
			$regex = ! preg_match( '/^(\\.|[^\\\*\:])*$/', $source ); // Ensure that no unescaped wildcard characters are present
		}

		if ( $regex ) {
			$replacer = new ToRegexReplacer();

			$source = $replacer->replace_source( $source );
			$target = $replacer->replace_target( $target );
		}

		return array(
			'url'          => $source,
			'title'        => $title,
			'regex'        => $regex,
			'group_id'     => $group,
			'match_type'   => 'url',
			'action_data'  => array( 'url' => trim( $target ) ),
			'action_type'  => $this->get_action_type( $code ),
			'action_code'  => $code,
			'match_data' => array(
				'source' => array(
					Red_Source_Flags::FLAG_CASE     => false,
					Red_Source_Flags::FLAG_REGEX    => $regex,
					Red_Source_Flags::FLAG_TRAILING => false,
				),
			),
		);
	}

	private function is_regex( $url ) {
		return preg_match( '/^(\\.|[^\(\)\[\]\$\^\*])*$/', $url ) === false; // Ensure that no unescaped special characters are present
	}
}


class FromRegexReplacer {
	private const SOURCE_PLACEHOLDER_REGEX = '/\((?P<placeholder>[^\)]+)\)/';
	private const SOURCE_SPLAT_REGEX = '/\((?P<placeholder>[^\)]*)\)$/';
	private const TARGET_PLACEHOLDER_REGEX = '/\$\{?(?P<placeholder>\d+)\}?/';

	private $counter = 0;
	private $plat = false;

	public function replace_source( string $url ) {
		return preg_replace_callback_array( array(
			// escaped character
			ESCAPED_REGEX => function ( array $matches ) { return $matches[0]; },

			// specific last placeholder
			self::SOURCE_SPLAT_REGEX => function ( array $matches ) {
				if ( $this->splat ) {
					throw new Exception( "Expected only one splat" );
				}

				$this->splat = true;

				return ':splat';
			},

			// placeholder
			self::SOURCE_PLACEHOLDER_REGEX => function ( array $matches ) {
				$counter = ++$this->counter;

				return ":path{$counter}";
			},
		), rtrim( ltrim( $url, '^ ' ), '$ ' ) );
	}

	public function replace_target( string $target ) {
		return preg_replace_callback_array( array(
			// escaped character
			ESCAPED_REGEX => function ( array $matches ) { return $matches[0]; },

			// placeholder
			self::TARGET_PLACEHOLDER_REGEX => function ( array $matches ) {
				if ( $this->splat && $matches['placeholder'] > $this->counter ) {
					return ':splat';
				}

				return ":path{$matches['placeholder']}";
			},
		), $target );
	}
}

class ToRegexReplacer {
	private const SOURCE_SPLAT_REGEX = '/:splat$/i';
	private const PLACEHOLDER_REGEX = '/:(?P<placeholder>[\w\d_]+)/i';

	private $counter = 0;
	private $targets = array();

	public function replace_source( string $url ) {
		return '^' . preg_replace_callback_array( array(
			// escaped character
			ESCAPED_REGEX => function ( array $matches ) { return preg_quote( substr( $matches[0], 1 ) ); },

			// placeholder
			self::PLACEHOLDER_REGEX => function ( array $matches ) {
				$this->targets[ $matches['placeholder'] ] = ++$this->counter;

				return 'splat' === $matches['placeholder'] ? '(.*)$' : '([^/]*)';
			},
		), $url );
	}

	public function replace_target( string $target ) {
		return preg_replace_callback_array( array(
			// escaped character
			ESCAPED_REGEX => function ( array $matches ) { return $matches[0]; },

			// placeholder
			self::PLACEHOLDER_REGEX => function ( array $matches ) {
				if ( ! isset( $this->targets[ $matches['placeholder'] ] ) ) {
					throw new Exception( "Placeholder {$matches['placeholder']} not found" );
				}

				$index = $this->targets[ $matches['placeholder'] ];

				return '${' . $index . '}';
			},
		), $target );
	}
}
