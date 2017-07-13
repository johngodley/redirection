<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Redirection_Table extends WP_List_Table {
	private $groups;
	private $current_group;
	private $total_items;
	private $current_group_id;

	function __construct( array $groups, $current_group_id ) {
		$this->groups = $groups;
		$this->current_group_id = $current_group_id;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'item',	 //singular name of the listed records
			'plural'	=> 'items',	//plural name of the listed records
			'ajax'	  => false,	  //does this table support ajax?
		) );
	}

	function get_columns() {
		$columns = array(
			'cb'	   => '<input type="checkbox" />', //Render a checkbox instead of text
			'type'	   => __( 'Type', 'redirection' ),
			'url'	   => __( 'URL', 'redirection' ),
			'hits'	  => __( 'Hits', 'redirection' ),
			'last_access' => __( 'Last Access', 'redirection' ),
		);

		return $columns;
	}

	function column_type( $item ) {
		return esc_html( $item->type() );
	}

	function column_last_access( $item ) {
		if ( $item->get_last_hit() === 0 )
			return '&mdash;';
		return date_i18n( get_option( 'date_format' ), $item->get_last_hit() );
	}

	function column_hits( $item ) {
		return esc_html( number_format_i18n( $item->get_hits(), 0 ) );
	}

	function column_url( $item ) {
		$actions = array(
			'edit'	 => sprintf( '<a class="red-ajax" data-action="%s" data-nonce="%s" data-id="%s" href="#">'.__( 'Edit', 'redirection' ).'</a>', 'red_redirect_edit', wp_create_nonce( 'red-edit_'.$item->get_id() ), $item->get_id() ),
			'delete'   => sprintf( '<a class="red-auto" data-action="%s" href="#">'.__( 'Delete', 'redirection' ).'</a>', 'delete', $item->get_id() ),
		);

		$before = $after = '';
		if ( $item->is_enabled() )
			$actions['disable']  = sprintf( '<a class="red-auto" data-action="%s" href="#">'.__( 'Disable', 'redirection' ).'</a>', 'disable', $item->get_id() );
		else {
			$actions['enable']   = sprintf( '<a class="red-auto" data-action="%s" href="#">'.__( 'Enable', 'redirection' ).'</a>', 'enable',  $item->get_id() );
			$before = '<span class="red-disabled">';
			$after = '</span>';
		}

		$title = $item->get_url();
		if ( $item->get_title() )
			$title = $item->get_title();

		return sprintf( '%1$s %2$s', $before.'<a href="'.esc_url( $item->get_url() ).'">'.esc_html( $title ).'</a>'.$after, $this->row_actions( $actions ) );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->get_id()				//The value of the checkbox should be the record's id
		);
	}

	function get_row( $item ) {
		ob_start();

		$this->single_row( $item );
		$output = ob_get_contents();

		ob_end_clean();

		return $output;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'url' => array( 'url', false ),
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete'  => __( 'Delete', 'redirection' ),
			'enable'  => __( 'Enable', 'redirection' ),
			'disable' => __( 'Disable', 'redirection' ),
			'reset'   => __( 'Reset Hits', 'redirection' ),
		);

		return $actions;
	}

	function process_bulk_action() {
		if ( ! isset( $_POST['item'] ) )
			return;

		if ( in_array( $this->current_action(), array( 'reset', 'enable', 'disable', 'delete' ) ) ) {
			$redirections = array();
			$flush = array();

			foreach( (array) $_POST['item'] as $id ) {
				$redirect = Red_Item::get_by_id( intval( $id ) );

				if ( $redirect ) {
					if ( $this->current_action() === 'reset' )
						$redirect->reset();
					elseif ( $this->current_action() === 'enable' ) {
						$redirect->enable();
						$flush[] = $redirect->get_group_id();
					}
					elseif ( $this->current_action() === 'disable' ) {
						$redirect->disable();
						$flush[] = $redirect->get_group_id();
					}
					elseif ( $this->current_action() === 'delete' )
						$redirect->delete();
				}
			}

			$flush = array_unique( $flush );
			foreach ( $flush as $group_id ) {
				Red_Module::flush( $group_id );
			}
		}
	}

	function extra_tablenav( $which ) {
		if ( $which === 'bottom' )
			return;

?>
		<div class="alignleft actions">
			<select name="id">
				<option value="0"<?php selected( 0, $this->current_group_id ); ?>><?php _e( 'No group filter', 'redirection' ); ?></option>

				<?php foreach ( $this->groups as $module_name => $groups ) : ?>
					<optgroup label="<?php echo esc_attr( $module_name ); ?>">
						<?php foreach ( $groups as $group_id => $group ) : ?>
							<option value="<?php echo esc_attr( $group_id ); ?>"<?php selected( $group_id, $this->current_group_id ); ?>>
								<?php echo esc_html( $group ); ?>
							</option>
						<?php endforeach; ?>
					</optgroup>
				<?php endforeach; ?>
			</select>

			<?php submit_button( __( 'Filter' ), 'button', false, false, array( 'id' => 'post-query-submit' ) ); ?>
		</div>
<?php
	}

	function prepare_items( $type = '', $id = 0 ) {
		global $wpdb, $current_user;

		$screen = get_current_screen();

		$per_page = 25;
		if ( $screen && $screen->get_option( 'per_page', 'option' ) ) {
			$per_page = intval( get_user_meta( $current_user->ID, $screen->get_option( 'per_page', 'option' ), true ) );
			if ( $per_page === 0 )
				$per_page = 25;
		}

		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, array(), $sortable );

		// Process any stuff
		$this->process_bulk_action();

		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
		$order   = ( ! empty( $_GET['order'] ) ) ? strtolower( $_GET['order'] ) : 'desc';

		if ( ! in_array( $orderby, array_keys( $sortable ) ) )
			$orderby = 'id';

		if ( ! in_array( $order, array( 'asc', 'desc' ) ) )
			$order = 'desc';

		$where = array();
		if ( isset( $_GET['s'] ) && strlen( $_GET['s'] ) > 0 )
			$where[] = $wpdb->prepare( 'url LIKE %s', '%'.$wpdb->esc_like( $_GET['s'] ).'%' );

		if ( isset( $_REQUEST['id'] ) && intval( $_REQUEST['id'] ) > 0 )
			$where[] = $wpdb->prepare( 'group_id=%d', intval( $_REQUEST['id'] ) );

		$where_cond = '';
		if ( count( $where ) > 0 )
			$where_cond = ' WHERE '.implode( ' AND ', $where );

		$table = $wpdb->prefix.'redirection_items';
		$rows		= $wpdb->get_results( "SELECT * FROM {$table} ".$where_cond.$wpdb->prepare( " ORDER BY $orderby $order LIMIT %d,%d", ( $this->get_pagenum() - 1 ) * $per_page, $per_page ) );
		$this->total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}".$where_cond );

		$this->items = array();
		foreach ( (array) $rows as $row ) {
			$this->items[] = new Red_Item( $row );
		}

		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil( $this->total_items / $per_page ),
		) );
	}
}
