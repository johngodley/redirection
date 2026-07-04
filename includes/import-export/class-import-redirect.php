<?php

namespace Redirection\ImportExport;

/**
 * Save imported redirects, optionally deduplicating against existing entries.
 */
class ImportRedirect {
	const MAX_PREVIEW_ITEMS = 20;

	/**
	 * @var RedirectRepository
	 */
	private $redirects;

	/**
	 * @var RedirectDuplicateMatcher
	 */
	private $duplicate_matcher;

	/**
	 * @var 'import'|'ignore'|'update'
	 */
	private $duplicate_mode = 'import';

	/**
	 * @var bool
	 */
	private $is_dry_run = false;

	/**
	 * @var int
	 */
	private $created = 0;

	/**
	 * @var int
	 */
	private $updated = 0;

	/**
	 * @var int
	 */
	private $ignored = 0;

	/**
	 * @var array<int, array{source: string, target: string, code: int, regex: bool, group: string, result: 'created'|'updated'|'ignored', redirect_id?: int}>
	 */
	private $preview_items = [];

	/**
	 * @param array<string, bool|string|array<int, string>> $options Import options.
	 */
	public function __construct( array $options = [], ?RedirectDuplicateMatcher $duplicate_matcher = null, ?RedirectRepository $redirects = null ) {
		$this->is_dry_run = isset( $options['dry_run'] ) ? $options['dry_run'] === true : false;
		$this->redirects = $redirects ? $redirects : new RedirectRepository();
		$this->duplicate_matcher = $duplicate_matcher ? $duplicate_matcher : new RedirectDuplicateMatcher( $this->redirects );

		if ( isset( $options['duplicate_mode'] ) && in_array( $options['duplicate_mode'], [ 'import', 'ignore', 'update' ], true ) ) {
			$this->duplicate_mode = $options['duplicate_mode'];
		} elseif ( isset( $options['deduplicate'] ) && $options['deduplicate'] === true ) {
			$this->duplicate_mode = 'update';
		}
	}

	/**
	 * @param array<string, mixed> $redirect Redirect data being imported.
	 * @param ImportGroup $group Group resolver.
	 * @param int|string $file_group_id Group ID referenced by the file.
	 * @param array<string, mixed>|null $group_data Group data from the file.
	 * @param int $file_redirect_id Redirect ID referenced by the file.
	 * @return bool
	 */
	public function save( array $redirect, ImportGroup $group, $file_group_id = 0, $group_data = null, $file_redirect_id = 0 ) {
		if ( $this->duplicate_mode !== 'import' ) {
			$existing = $this->duplicate_matcher->get_existing_redirect( $redirect, $file_redirect_id );
			if ( $existing !== false ) {
				return $this->update_redirect( $existing, $redirect );
			}
		}

		$resolved_group = $group->get_group( $file_group_id, $group_data );
		if ( $resolved_group === false ) {
			return false;
		}

		if ( $this->is_dry_run ) {
			$redirect['group_id'] = $resolved_group->get_id();
			$redirect = $this->apply_default_group_status( $redirect, $resolved_group );
			$this->add_preview_item( $redirect, $resolved_group->get_name(), 'created' );
			$this->created++;
			return true;
		}

		$redirect['group_id'] = $resolved_group->get_id();
		$redirect = $this->apply_default_group_status( $redirect, $resolved_group );
		$created = $this->redirects->create( $redirect );
		if ( $created instanceof \Red_Item ) {
			$this->add_preview_item( $redirect, $resolved_group->get_name(), 'created' );
			$this->created++;
			return true;
		}

		return false;
	}

	/**
	 * @return int
	 */
	public function get_created() {
		return $this->created;
	}

	/**
	 * @return int
	 */
	public function get_updated() {
		return $this->updated;
	}

	/**
	 * @return int
	 */
	public function get_ignored() {
		return $this->ignored;
	}

	/**
	 * @return int
	 */
	public function get_total_handled() {
		return $this->created + $this->updated + $this->ignored;
	}

	/**
	 * @return array<int, array{source: string, target: string, code: int, regex: bool, group: string, result: 'created'|'updated'|'ignored', redirect_id?: int}>
	 */
	public function get_preview_items() {
		return $this->preview_items;
	}

	/**
	 * @param \Red_Item $existing Existing redirect.
	 * @param array<string, mixed> $redirect Redirect data being imported.
	 * @return bool
	 */
	private function update_redirect( \Red_Item $existing, array $redirect ) {
		if ( $this->duplicate_mode === 'ignore' ) {
			$this->add_preview_item( $redirect, $this->get_group_name( $existing->get_group_id() ), 'ignored', $existing->get_id() );
			$this->ignored++;
			return true;
		}

		$redirect = array_merge( $existing->to_json(), $redirect );
		$redirect['group_id'] = $existing->get_group_id();

		if ( $this->is_dry_run ) {
			$this->add_preview_item( $redirect, $this->get_group_name( $existing->get_group_id() ), 'updated', $existing->get_id() );
			$this->updated++;
			return true;
		}

		if ( ! $this->redirects->update( $existing, $redirect ) ) {
			return false;
		}

		$this->update_redirect_status( $existing, $redirect );
		$this->add_preview_item( $redirect, $this->get_group_name( $existing->get_group_id() ), 'updated', $existing->get_id() );
		$this->updated++;
		return true;
	}

	/**
	 * @param \Red_Item $existing Existing redirect.
	 * @param array<string, mixed> $redirect Redirect data being imported.
	 * @return void
	 */
	private function update_redirect_status( \Red_Item $existing, array $redirect ) {
		if ( isset( $redirect['enabled'] ) ) {
			$this->redirects->set_enabled( $existing, $redirect['enabled'] !== false );
			return;
		}

		if ( isset( $redirect['status'] ) && is_string( $redirect['status'] ) ) {
			$this->redirects->set_enabled( $existing, $redirect['status'] !== 'disabled' );
		}
	}

	/**
	 * @param array<string, mixed> $redirect Redirect data being imported.
	 * @param \Red_Group|\Redirection\ImportExport\ImportPreviewGroup $group Resolved group.
	 * @return array<string, mixed>
	 */
	private function apply_default_group_status( array $redirect, $group ) {
		if ( isset( $redirect['enabled'] ) || isset( $redirect['status'] ) || ! method_exists( $group, 'is_enabled' ) ) {
			return $redirect;
		}

		$redirect['status'] = $group->is_enabled() ? 'enabled' : 'disabled';

		return $redirect;
	}

	/**
	 * @param array<string, mixed> $redirect Redirect data being imported.
	 * @param string $group_name Resolved group name.
	 * @param 'created'|'updated'|'ignored' $result Preview result.
	 * @param int|null $redirect_id Existing redirect ID.
	 * @return void
	 */
	private function add_preview_item( array $redirect, $group_name, $result, $redirect_id = null ) {
		if ( count( $this->preview_items ) >= self::MAX_PREVIEW_ITEMS ) {
			return;
		}

		$target = '';
		if ( isset( $redirect['action_data'] ) && is_array( $redirect['action_data'] ) && isset( $redirect['action_data']['url'] ) && is_string( $redirect['action_data']['url'] ) ) {
			$target = $redirect['action_data']['url'];
		}

		$this->preview_items[] = [
			'source' => isset( $redirect['url'] ) && is_string( $redirect['url'] ) ? $redirect['url'] : '',
			'target' => $target,
			'code' => isset( $redirect['action_code'] ) ? intval( $redirect['action_code'], 10 ) : 0,
			'regex' => isset( $redirect['regex'] ) ? $redirect['regex'] === true : false,
			'group' => $group_name,
			'result' => $result,
			'redirect_id' => $redirect_id !== null ? intval( $redirect_id, 10 ) : 0,
		];
	}

	/**
	 * @param int $group_id Group ID.
	 * @return string
	 */
	private function get_group_name( $group_id ) {
		$group = \Red_Group::get( $group_id );

		if ( $group instanceof \Red_Group ) {
			return $group->get_name();
		}

		return '';
	}
}
