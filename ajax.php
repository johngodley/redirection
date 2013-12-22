<?php
/**
 * Redirection
 *
 * @package Redirection
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

class RedirectionAjax extends Redirection_Plugin {
	var $post;

	function RedirectionAjax() {
		$this->register_plugin( 'redirection', __FILE__ );

		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		if ( current_user_can( 'administrator' ) ) {
			$this->post = stripslashes_deep( $_POST );

			$this->register_ajax( 'red_log_show' );
			$this->register_ajax( 'red_log_hide' );
			$this->register_ajax( 'red_log_delete' );

			$this->register_ajax( 'red_module_edit' );
			$this->register_ajax( 'red_module_load' );
			$this->register_ajax( 'red_module_save' );
			$this->register_ajax( 'red_module_reset' );
			$this->register_ajax( 'red_module_delete' );

			$this->register_ajax( 'red_group_edit' );
			$this->register_ajax( 'red_group_load' );
			$this->register_ajax( 'red_group_save' );
			$this->register_ajax( 'red_group_toggle' );
			$this->register_ajax( 'red_group_delete' );
			$this->register_ajax( 'red_group_reset' );
			$this->register_ajax( 'red_group_move' );
			$this->register_ajax( 'red_group_saveorder' );

			$this->register_ajax( 'red_redirect_edit' );
			$this->register_ajax( 'red_redirect_load' );
			$this->register_ajax( 'red_redirect_save' );
			$this->register_ajax( 'red_redirect_toggle' );
			$this->register_ajax( 'red_redirect_delete' );
			$this->register_ajax( 'red_redirect_reset' );
			$this->register_ajax( 'red_redirect_move' );
			$this->register_ajax( 'red_redirect_saveorder' );
			$this->register_ajax( 'red_redirect_add' );
		}
	}

	function red_log_show() {
		$id = intval( $_GET['id'] );

		if ( check_ajax_referer( 'redirection-log_'.$id ) ) {
			$log      = RE_Log::get_by_id( $id );
			$redirect = Red_Item::get_by_id( $log->redirection_id );

			$this->render_admin( 'log_item_details', array( 'log' => $log, 'redirect' => $redirect ) );
			die();
		}
	}

	function red_log_hide() {
		$id = intval( $_GET['id'] );

		if ( check_ajax_referer( 'redirection-log_'.$id ) ) {
			$log = RE_Log::get_by_id( $id );

			echo '<a class="details" href="'.$log->url.'">'.$log->show_url ($log->url).'</a>';
			die();
		}
	}

	function red_log_delete()	{
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $this->post['checked'], $items ) > 0) {
				foreach ( $items[1] AS $item ) {
					RE_Log::delete( intval( $item ) );
				}
			}
		}
	}

	function red_module_edit() {
		$id = intval( $_GET['id'] );

		if ( check_ajax_referer( 'redirection-module_'.$id ) ) {
			$module = Red_Module::get( $id );

			if ( $module )
				$this->render_admin( 'module_edit', array( 'module' => $module ) );

			die();
		}
	}

	function red_module_load() {
		$id = intval( $_GET['id'] );

		if ( check_ajax_referer( 'redirection-module_'.$id ) ) {
			$module = Red_Module::get ($id);
			if ($module) {
				global $redirection;
				$options = $redirection->get_options();

				$this->render_admin( 'module_item', array( 'module' => $module, 'token' => $options['token'] ) );
			}

			die();
		}
	}

	function red_module_save() {
		$id = intval( $this->post['id'] );

		if ( check_ajax_referer( 'redirection-module_save_'.$id ) ) {
			$module = Red_Module::get( $id );
			if ( $module ) {
				global $redirection;
				$options = $redirection->get_options();
				$module->update( $this->post );

				$this->render_admin( 'module_item', array( 'module' => $module, 'token' => $options['token'] ) );
				die();
			}
		}
	}

	function red_module_reset() {
		$id = intval( $_GET['id'] );

		if ( check_ajax_referer( 'redirection-module_'.$id ) ) {
			$module = Red_Module::get( $id );

			if ( $module ) {
				global $redirection;
				$options = $redirection->get_options();

				$module->reset ();
				$this->render_admin( 'module_item', array( 'module' => $module, 'token' => $options['token'] ) );
			}

			die();
		}
	}

	function red_module_delete() {
		$id = intval( $_GET['id'] );
		if ( check_ajax_referer( 'redirection-module_'.$id ) ) {
			$module = Red_Module::get( $id );
			$module->delete();
		}
	}

	function red_group_edit() {
		$id = intval( $_GET['id'] );

		if ( check_ajax_referer( 'redirection-group_'.$id ) ) {
			$group = Red_Group::get( $id );
			if ( $group )
				$this->render_admin( 'group_edit', array( 'group' => $group, 'modules' => Red_Module::get_for_select() ) );

			die();
		}
	}

	function red_group_load() {
		$id = intval( $_GET['id'] );

		if ( check_ajax_referer( 'redirection-group_'.$id ) ) {
			$group = Red_Group::get( $id );
			if ( $group )
				$this->render_admin( 'group_item', array( 'group' => $group ) );
			die();
		}
	}

	function red_group_save() {
		$id = intval( $this->post['id'] );

		if ( check_ajax_referer( 'redirection-group_save_'.$id ) ) {
			$group = Red_Group::get( $id );
			if ( $group ) {
				$original_module = $group->module_id;
				$group->update( $this->post );

				$this->render_admin( 'group_item', array( 'group' => $group ) );
			}

			die();
		}
	}

	function red_group_toggle() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $this->post['checked'], $items ) > 0) {
				foreach ( $items[1] AS $group ) {
					$group = Red_Group::get( $group );
					$group->toggle_status();
				}

				Red_Module::flush( $group->module_id );
			}
		}
	}

	function red_group_delete() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $this->post['checked'], $items ) > 0) {
				foreach ( $items[1] AS $group ) {
					Red_Group::delete( intval( $group ) );
				}
			}
		}
	}

	function red_group_reset() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $this->post['checked'], $items ) > 0) {
				foreach ( $items[1] AS $group ) {
					$redirect = Red_Group::get( intval( $group ) );
					$redirect->reset();
				}
			}
		}
	}

	function red_group_move() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			$target = intval( $this->post['target'] );

			if ( preg_match_all( '/=(\d*)/', $this->post['checked'], $items ) > 0) {
				foreach ( $items[1] AS $group ) {
					$redirect = Red_Group::get( $group );
					$redirect->move_to( $target );
				}
			}
		}
	}

	function red_group_saveorder() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $this->post['items'], $items ) > 0) {
				Red_Group::save_order( $items[1], intval( $this->post['page'] ) );
			}
		}
	}

	function red_redirect_edit() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			$redirect = Red_Item::get_by_id( intval( $_GET['id'] ) );
			if ( $redirect )
				$this->render_admin( 'item_edit', array( 'redirect' => $redirect, 'groups' => Red_Group::get_for_select() ) );

			die();
		}
	}

	function red_redirect_load() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			$redirect = Red_Item::get_by_id( intval( $_GET['id'] ) );
			if ( $redirect )
				$this->render_admin( 'item', array( 'redirect' => $redirect, 'date_format' => get_option( 'date_format' ) ) );

			die();
		}
	}

	function red_redirect_save() {
		$id = intval( $this->post['id'] );

		if ( check_ajax_referer( 'redirection-redirect_save_'.$id ) ) {
			$redirect = Red_Item::get_by_id( $id );
			$redirect->update( $this->post );

			$this->render_admin( 'item', array( 'redirect' => $redirect, 'date_format' => get_option( 'date_format' ) ) );
			die();
		}
	}

	function red_redirect_toggle() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $this->post['checked'], $items ) > 0) {
				foreach ( $items[1] AS $item ) {
					$redirect = Red_Item::get_by_id( $item );
					$redirect->toggle_status();
				}
			}

			$group = Red_Group::get( $redirect->group_id );
			Red_Module::flush( $group->module_id );
		}
	}

	function red_redirect_delete() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $this->post['checked'], $items ) > 0) {
				$redirect = Red_Item::get_by_id( $items[0]);

				foreach ( $items[1] AS $item ) {
					Red_Item::delete( intval( $item ) );
				}

				$group = Red_Group::get( $redirect->group_id );
				Red_Module::flush( $group->module_id );
			}
		}
	}

	function red_redirect_reset() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $this->post['checked'], $items ) > 0) {
				foreach ( $items[1] AS $item ) {
					$redirect = Red_Item::get_by_id( intval( $item ) );
					$redirect->reset();
				}
			}
		}
	}

	function red_redirect_move() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			$target = intval( $this->post['target'] );

			if ( preg_match_all( '/=(\d*)/', $this->post['checked'], $items ) > 0) {
				foreach ( $items[1] AS $item ) {
					$redirect = Red_Item::get_by_id( $item );
					$redirect->move_to( $target );
				}
			}
		}
	}

	function red_redirect_saveorder() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $this->post['items'], $items ) > 0) {
				Red_Item::save_order( $items[1], intval( $this->post['page'] ) );
			}
		}
	}

	function red_redirect_add()	{
		if ( check_ajax_referer( 'redirection-redirect_add' ) ) {
			$item = Red_Item::create( $this->post );
			if ( is_wp_error( $item ) )
				$this->render_error( $item->get_error_message() );
			elseif ( $item !== false ) {
				echo '<li class="type_'.$item->action_type.'" id="item_'.$item->id.'">';
				$this->render_admin( 'item', array( 'redirect' => $item, 'date_format' => get_option( 'date_format' ) ) );
				echo '</li>';
			}
			else
				$this->render_error( __( 'Sorry, but your redirection was not created', 'redirection' ) );

			die();
		}
	}
}
