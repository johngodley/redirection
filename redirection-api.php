<?php

// Todo: use the JSON API
class Redirection_Api {
	public $endpoints = array(
		'load_settings',
		'save_settings',
		'get_logs',
		'log_action',
		'delete_all',
		'delete_plugin',
		'get_module',
		'set_module',
		'get_redirect',
		'set_redirect',
		'redirect_action',
		'get_group',
		'set_group',
		'group_action',
	);

	public function __construct() {
		foreach ( $this->endpoints as $point ) {
			add_action( 'wp_ajax_red_'.$point, array( $this, 'check_auth' ), 9 );
			add_action( 'wp_ajax_red_'.$point, array( $this, 'ajax_'.$point ), 10 );
		}
	}

	public function check_auth( $params ) {
		if ( check_ajax_referer( 'wp_rest', false, false ) === false ) {
			wp_die( $this->output_ajax_response( array( 'error' => __( 'Unable to perform action' ).' - bad nonce "wp_rest" ('.__LINE__.')' ) ) );
		}

		if ( $this->user_has_access() === false ) {
			wp_die( $this->output_ajax_response( array( 'error' => __( 'No permissions to perform action ('.__LINE__.')' ) ) ) );
		}
	}

	private function get_params( $params ) {
		if ( empty( $params ) ) {
			$params = $_POST;
		}

		return $params;
	}

	public function ajax_group_action( $params ) {
		$params = $this->get_params( $params );

		$action = false;
		$items = array();
		if ( isset( $params['items'] ) ) {
			$items = array_map( 'intval', explode( ',', $params['items'] ) );
		}

		if ( isset( $params['bulk'] ) && in_array( $params['bulk'], array( 'delete', 'enable', 'disable' ) ) ) {
			$action = $params['bulk'];

			foreach ( $items as $item ) {
				$group = Red_Group::get( intval( $item, 10 ) );

				if ( $group ) {
					if ( $action === 'delete' ) {
						$group->delete();
					} else if ( $action === 'disable' ) {
						$group->disable();
					} else if ( $action === 'enable' ) {
						$group->enable();
					}
				}
			}
		}

		return $this->output_ajax_response( Red_Group::get_filtered( $params ) );
	}

	public function ajax_get_group( $params ) {
		$params = $this->get_params( $params );

		return $this->output_ajax_response( Red_Group::get_filtered( $params ) );
	}

	public function ajax_set_group( $params ) {
		$params = $this->get_params( $params );

		$groupId = 0;
		$name = '';
		$moduleId = 0;
		if ( isset( $params['id'] ) ) {
			$groupId = intval( $params['id'], 10 );
		}

		$result = array( 'error' => 'Invalid group or parameters ('.__LINE__.')' );
		if ( $groupId > 0 ) {
			$group = Red_Group::get( $groupId );

			if ( $group && isset( $params['name'] ) && isset( $params['moduleId'] ) ) {
				if ( $group->update( $params ) ) {
					$result = array( 'item' => $group->to_json() );
				}
			}
		} else {
			$group = Red_Group::create( isset( $params['name'] ) ? $params['name'] : '', isset( $params['moduleId'] ) ? $params['moduleId'] : 0 );

			if ( $group ) {
				$result = Red_Group::get_filtered( $params );
			}
		}

		return $this->output_ajax_response( $result );
	}

	public function ajax_get_redirect( $params ) {
		$params = $this->get_params( $params );

		return $this->output_ajax_response( Red_Item::get_filtered( $params ) );
	}

	public function ajax_set_redirect( $params ) {
		$params = $this->get_params( $params );

		$redirectId = 0;
		if ( isset( $params['id'] ) ) {
			$redirectId = intval( $params['id'], 10 );
		}

		$result = array( 'error' => 'Invalid redirect details ('.__LINE__.')' );
		if ( $redirectId === 0 ) {
			$redirect = Red_Item::create( $params );

			if ( ! is_wp_error( $redirect ) ) {
				$result = Red_Item::get_filtered( $params );
			}
		} else {
			$redirect = Red_Item::get_by_id( $redirectId );

			if ( $redirect && ! is_wp_error( $redirect->update( $params ) ) ) {
				$result = array( 'item' => $redirect->to_json() );
			}
		}

		return $this->output_ajax_response( $result );
	}

	public function ajax_redirect_action( $params ) {
		$params = $this->get_params( $params );

		$action = false;
		$items = array();
		if ( isset( $params['items'] ) ) {
			$items = array_map( 'intval', explode( ',', $params['items'] ) );
		}
		if ( isset( $params['bulk'] ) && in_array( $params['bulk'], array( 'delete', 'enable', 'disable', 'reset' ) ) ) {
			$action = $params['bulk'];

			foreach ( $items as $item ) {
				$redirect = Red_Item::get_by_id( intval( $item, 10 ) );

				if ( $redirect ) {
					if ( $action === 'delete' ) {
						$redirect->delete();
					} else if ( $action === 'disable' ) {
						$redirect->disable();
					} else if ( $action === 'enable' ) {
						$redirect->enable();
					} else if ( $action === 'reset' ) {
						$redirect->reset();
					}
				}
			}
		}

		return $this->output_ajax_response( Red_Item::get_filtered( $params ) );
	}

	public function ajax_get_module( $params ) {
		$params = $this->get_params( $params );

		$moduleType = false;

		$modules = array( 1, 2, 3 );
		if ( isset( $params['moduleId'] ) ) {
			$moduleId = intval( $params['moduleId'], 10 );

			if ( $moduleId > 0 && $moduleId <= 3 ) {
				$modules = array( $moduleId );
			}
		}

		if ( isset( $params['moduleType'] ) && in_array( $params['moduleType'], array( 'csv', 'apache', 'nginx' ) ) ) {
			$moduleType = $params['moduleType'];
		}

		foreach ( $modules as $moduleId ) {
			$module = Red_Module::get( $moduleId );
			$moduleData = $this->get_module_data( $module, $moduleType );

			if ( $module->get_id() === Apache_Module::MODULE_ID ) {
				$moduleData['data'] = array(
					'installed' => ABSPATH,
					'location' => $module->get_location(),
					'canonical' => $module->get_canonical(),
				);
			}

			$result[] = $moduleData;
		}

		return $this->output_ajax_response( $result );
	}

	public function ajax_set_module( $params ) {
		$params = $this->get_params( $params );

		$result = array( 'error' => 'Failed to save module' );
		if ( isset( $params['module'] ) ) {
			$module = Red_Module::get( $params[ 'module' ] );

			if ( $module ) {
				$module->update( $params );
				return $this->ajax_get_module( $params );
			}
		}

		return $this->output_ajax_response( $result );
	}

	private function get_module( $module_name ) {
		$module_id_name = array(
			'apache' => Apache_Module::MODULE_ID,
			'wordpress' => WordPress_Module::MODULE_ID,
			'nginx' => Nginx_Module::MODULE_ID,
		);

		if ( isset( $module_id_name[ $module_name ] ) ) {
			return Red_Module::get( $module_id_name[ $module_name ] );
		}

		return false;
	}

	private function get_module_data( $module, $type ) {
		$data = array(
			'redirects' => $module->get_total_redirects(),
			'module_id' => $module->get_id(),
			'displayName' => $module->get_name(),
		);

		if ( $type ) {
			$exporter = Red_FileIO::create( $type );
			$data['data'] = $exporter->get( Red_Item::get_all_for_module( $module->get_id() ) );
		}

		return $data;
	}

	public function ajax_delete_plugin() {
		$plugin = Redirection_Admin::init();
		$plugin->plugin_uninstall();

		$current = get_option( 'active_plugins' );
		array_splice( $current, array_search( basename( dirname( REDIRECTION_FILE ) ).'/'.basename( REDIRECTION_FILE ), $current ), 1 );
		update_option( 'active_plugins', $current );

		return $this->output_ajax_response( array( 'location' => admin_url().'plugins.php' ) );
	}

	public function ajax_load_settings() {
		return $this->output_ajax_response( array( 'settings' => red_get_options(), 'groups' => $this->groups_to_json( Red_Group::get_for_select() ) ) );
	}

	public function ajax_save_settings( $settings = array() ) {
		$settings = $this->get_params( $settings );
		$options = red_get_options();

		if ( isset( $settings['monitor_post'] ) ) {
			$options['monitor_post'] = max( 0, intval( $settings['monitor_post'], 10 ) );
		}

		if ( isset( $settings['auto_target'] ) ) {
			$options['auto_target'] = stripslashes( $settings['auto_target'] );
		}

		if ( isset( $settings['support'] ) ) {
			$options['support'] = $settings['support'] === 'true' ? true : false;
		}

		if ( isset( $settings['token'] ) ) {
			$options['token'] = stripslashes( $settings['token'] );
		}

		if ( !isset( $settings['token'] ) || trim( $options['token'] ) === '' ) {
			$options['token'] = md5( uniqid() );
		}

		if ( isset( $settings['newsletter'] ) ) {
			$options['newsletter'] = $settings['newsletter'] === 'true' ? true : false;
		}

		if ( isset( $settings['expire_redirect'] ) ) {
			$options['expire_redirect'] = max( -1, min( intval( $settings['expire_redirect'], 10 ), 60 ) );
		}

		if ( isset( $settings['expire_404'] ) ) {
			$options['expire_404'] = max( -1, min( intval( $settings['expire_404'], 10 ), 60 ) );
		}

		update_option( 'redirection_options', $options );
		do_action( 'redirection_save_options', $options );

		return $this->output_ajax_response( array( 'settings' => $options, 'groups' => $this->groups_to_json( Red_Group::get_for_select() ) ) );
	}

	public function ajax_get_logs( $params ) {
		$params = $this->get_params( $params );
		$result = $this->get_logs( $params );

		return $this->output_ajax_response( $result );
	}

	public function ajax_log_action( $params ) {
		$params = $this->get_params( $params );

		// Do the action
		if ( isset( $params['bulk'] ) && isset( $params['items'] ) && $params['bulk'] === 'delete' ) {
			$items = explode( ',', $params['items'] );

			if ( $this->get_log_type( $params ) === 'log' ) {
				array_map( array( 'RE_Log', 'delete' ), $items );
			} else {
				array_map( array( 'RE_404', 'delete' ), $items );
			}
		}

		$result = $this->get_logs( $params );

		return $this->output_ajax_response( $result );
	}

	public function ajax_delete_all( $params ) {
		$params = $this->get_params( $params );

		if ( isset( $params['logType'] ) ) {
			if ( $params['logType'] === 'log' ) {
				RE_Log::delete_all();
			} else {
				RE_404::delete_all();
			}
		}

		$result = $this->get_logs( $params );

		return $this->output_ajax_response( $result );
	}

	private function get_log_type( $params ) {
		$type = 'log';

		if ( isset( $params['logType'] ) && in_array( $params['logType'], array( 'log', '404' ), true ) ) {
			$type = $params['logType'];
		}

		return $type;
	}

	private function get_logs( array $params ) {
		$type = $this->get_log_type( $params );

		if ( $type === 'log' ) {
			return RE_Filter_Log::get( 'redirection_logs', 'RE_Log', $params );
		} else if ( $type === '404' ) {
			if ( isset( $params['filterBy'] ) && isset( $params['filter'] ) && $params['filterBy'] === 'ip' ) {
				$params['filter'] = ip2long( $params['filter'] );
			}

			return RE_Filter_Log::get( 'redirection_404', 'RE_404', $params );
		}

		return array( 'items' => array(), 'total' => 0 );
	}

	private function groups_to_json( $groups, $depth = 0 ) {
		$items = array();

		foreach ( $groups as $text => $value ) {
			if ( is_array( $value ) && $depth === 0 ) {
				$items[] = (object)array( 'text' => $text, 'value' => $this->groups_to_json( $value, 1 ) );
			} else {
				$items[] = (object)array( 'text' => $value, 'value' => $text );
			}
		}

		return $items;
	}

	private function output_ajax_response( $data ) {
		$result = wp_json_encode( $data );

		if ( defined( 'DOING_AJAX' ) ) {
			header( 'Content-Type: application/json' );
			echo $result;
			wp_die();
		}

		return $result;
	}

	private function user_has_access() {
		return current_user_can( apply_filters( 'redirection_role', 'administrator' ) );
	}
}
