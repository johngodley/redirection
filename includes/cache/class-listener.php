<?php

namespace Redirection\Cache;

use Redirection\Redirect\Redirect;
use Redirection\Settings\Settings;

/**
 * Listen for cache-affecting changes.
 */
class Listener {
	/**
	 * Settings that affect redirect matching.
	 *
	 * @var string[]
	 */
	const MATCH_SETTINGS = [ 'flag_case', 'flag_trailing', 'flag_query' ];

	public static function init(): void {
		$listener = new Listener();

		add_action( 'redirection_redirect_updated', [ $listener, 'redirect_updated' ], 10, 3 );
		add_action( 'redirection_redirect_deleted', [ $listener, 'redirect_deleted' ] );
		add_action( 'redirection_redirect_enabled', [ $listener, 'redirect_by_id' ] );
		add_action( 'redirection_redirect_disabled', [ $listener, 'redirect_by_id' ] );

		add_action( 'redirection_group_updated', [ $listener, 'group_changed' ] );
		add_action( 'redirection_group_deleted', [ $listener, 'group_changed' ] );

		add_action( 'update_option_' . Settings::OPTION_KEY, [ $listener, 'settings_saved' ], 10, 2 );

		add_action( 'shutdown', [ $listener, 'flush' ], 1 );
	}

	/**
	 * @param integer       $id Redirect ID.
	 * @param Redirect      $redirect The redirect.
	 * @param Redirect|null $previous Previous redirect state, when updating.
	 */
	public function redirect_updated( $id, $redirect, $previous = null ): void {
		if ( $previous instanceof Redirect ) {
			if ( $previous->is_dynamic() || $previous->is_regex() ) {
				$this->full_purge();

				return;
			}

			if ( $redirect instanceof Redirect && $previous->get_url() !== $redirect->get_url() ) {
				Invalidator::init()->queue_url( $previous->get_url() );
			}
		}

		$this->changed( $redirect );
	}

	/**
	 * @param Redirect $redirect The redirect.
	 */
	public function redirect_deleted( $redirect ): void {
		$this->changed( $redirect );
	}

	/**
	 * Queue a redirect change by ID.
	 *
	 * @param integer $id Redirect ID.
	 */
	public function redirect_by_id( $id ): void {
		$redirect = Redirect::get_by_id( intval( $id ) );

		if ( $redirect instanceof Redirect ) {
			$this->changed( $redirect );

			return;
		}

		$this->full_purge();
	}

	/**
	 * @param integer $id Group ID.
	 */
	public function group_changed( $id ): void {
		$this->full_purge();
	}

	/**
	 * @param array<string, mixed> $old Previous settings.
	 * @param array<string, mixed> $new Saved settings.
	 */
	public function settings_saved( $old, $new ): void {
		if ( ! is_array( $old ) || ! is_array( $new ) ) {
			return;
		}

		foreach ( self::MATCH_SETTINGS as $flag ) {
			$before = isset( $old[ $flag ] ) ? $old[ $flag ] : null;
			$after = isset( $new[ $flag ] ) ? $new[ $flag ] : null;

			if ( $before !== $after ) {
				$this->full_purge();

				return;
			}
		}
	}

	public function flush(): void {
		Invalidator::init()->flush();
	}

	/**
	 * @param Redirect|mixed $redirect The changed redirect.
	 */
	private function changed( $redirect ): void {
		if ( ! $redirect instanceof Redirect ) {
			$this->full_purge();

			return;
		}

		if ( $redirect->is_dynamic() || $redirect->is_regex() ) {
			$this->full_purge();

			return;
		}

		$this->bump_cache_key();

		Invalidator::init()->queue_url( $redirect->get_url() );
	}

	private function full_purge(): void {
		$this->bump_cache_key();

		Invalidator::init()->queue_full();
	}

	private function bump_cache_key(): void {
		$settings = Settings::get();

		if ( $settings['cache_key'] > 0 ) {
			Settings::save( [ 'cache_key' => time() ] );
		}
	}
}
