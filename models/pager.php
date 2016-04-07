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

class Redirection_Group_Table extends WP_List_Table {
	private $modules;

	function __construct( $modules ) {
		$this->modules = $modules;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'item',	 //singular name of the listed records
			'plural'   => 'items',	//plural name of the listed records
			'ajax'	 => false,	  //does this table support ajax?
		) );
	}

	function get_columns() {
		$columns = array(
			'cb'		=> '<input type="checkbox" />', //Render a checkbox instead of text
			'name'	  => __( 'Name', 'redirection' ),
			'redirects' => __( 'Redirects', 'redirection' ),
			'module'	=> __( 'Module', 'redirection' ),
		);

		return $columns;
	}

	function column_name( $item ) {
		$actions = array(
			'edit'   => sprintf( '<a class="red-ajax" data-action="%s" data-nonce="%s" data-id="%s" href="#">'.__( 'Edit', 'redirection' ).'</a>', 'red_group_edit', wp_create_nonce( 'red-edit_'.$item->get_id() ), $item->get_id() ),
			'delete' => sprintf( '<a class="red-auto" data-action="%s" href="#">'.__( 'Delete', 'redirection' ).'</a>', 'delete', $item->get_id() ),
			'view' => '<a href="tools.php?page=redirection.php&amp;id='.$item->get_id().'">'.__( 'View Redirects', 'redirection' ).'</a>',
		);

		$after = $before = '';
		if ( $item->is_enabled() )
			$actions['disable']  = sprintf( '<a class="red-auto" data-action="%s" href="#">'.__( 'Disable', 'redirection' ).'</a>', 'disable', $item->get_id() );
		else {
			$actions['enable']   = sprintf( '<a class="red-auto" data-action="%s" href="#">'.__( 'Enable', 'redirection' ).'</a>', 'enable',  $item->get_id() );
			$before = '<span class="red-disabled">';
			$after = '</span>';
		}

		return sprintf( '%1$s %2$s', $before.esc_html( $item->get_name() ).$after, $this->row_actions( $actions ) );
	}

	function column_redirects( $item ) {
		return esc_html( $item->get_total_redirects() );
	}

	function column_module( $item ) {
		$module = Red_Module::get( $item->get_module_id() );

		if ( $module )
			return esc_html( $module->get_name() );
		return esc_html( __( 'Unknown', 'redirection' ) );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->id				//The value of the checkbox should be the record's id
		);
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', false ),
		);

		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete'  => __( 'Delete', 'redirection' ),
			'enable'  => __( 'Enable', 'redirection' ),
			'disable' => __( 'Disable', 'redirection' ),
		);

		return $actions;
	}

	function process_bulk_action() {
		if ( ! isset( $_POST['item'] ) )
			return;

		if ( in_array( $this->current_action(), array( 'delete', 'enable', 'disable' ) ) ) {
			$groups = array();

			foreach( (array) $_POST['item'] as $id ) {
				$group = Red_Group::get( intval( $id ) );

				if ( $group ) {
					if ( $this->current_action() === 'delete' )
						$group->delete();
					else if ( $this->current_action() === 'enable' ) {
						$group->enable();
						Red_Module::flush( $group->get_id() );
					}
					else if ( $this->current_action() === 'disable' ) {
						$group->disable();
						Red_Module::flush( $group->get_id() );
					}
				}
			}
		}
	}

	private function delete( $item ) {
		$item->delete();
	}

	private function enable( $item ) {
		$item->enable();
	}

	private function disable( $item ) {
		$item->disable();
	}

	function extra_tablenav( $which ) {
		if ( $which === 'bottom' )
			return;

		$selected = 0;
		if ( isset( $_POST['id'] ) )
			$selected = intval( $_POST['id'] );
?>
		<div class="alignleft actions">
			<select name="id">
				<option value="0"<?php selected( 0, $selected ); ?>><?php _e( 'All modules', 'redirection' ); ?></option>
				<?php foreach ( $this->modules as $module_id => $module ) : ?>
					<option value="<?php echo esc_attr( $module_id ); ?>"<?php selected( $module_id, $selected ); ?>>
						<?php echo esc_html( $module->get_name() ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<?php submit_button( __( 'Filter' ), 'button', false, false, array( 'id' => 'post-query-submit' ) ); ?>
		</div>
<?php
	}

	function prepare_items( $type = '', $id = 0 ) {
		global $wpdb, $current_user;

		$screen = get_current_screen();
		$per_page = get_user_meta( $current_user->ID, $screen->get_option( 'per_page', 'option' ), true );

		$per_page = $per_page ? $per_page : 25;
		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, array(), $sortable );

		// Process any stuff
		$this->process_bulk_action();

		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
		$order   = ( ! empty( $_GET['order'] ) ) ? strtolower( $_GET['order'] ) : 'desc';

		if ( ! in_array( $orderby, array_keys( $sortable ) ) )
			$orderby = $wpdb->prefix.'redirection_groups.name';

		if ( ! in_array( $order, array( 'asc', 'desc' ) ) )
			$order = 'desc';

		$where = array();
		if ( isset( $_GET['s'] ) && strlen( $_GET['s'] ) > 0 )
			$where[] = $wpdb->prepare( 'name LIKE %s', '%'.$wpdb->esc_like( $_GET['s'] ).'%' );

		if ( isset( $_REQUEST['id'] ) && intval( $_REQUEST['id'] ) > 0 )
			$where[] = $wpdb->prepare( 'module_id=%d', intval( $_REQUEST['id'] ) );

		$where_cond = '';
		if ( count( $where ) > 0 )
			$where_cond = ' WHERE '.implode( ' AND ', $where );

		$table = $wpdb->prefix.'redirection_groups';
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ".$where_cond.$wpdb->prepare( " ORDER BY $orderby $order LIMIT %d,%d", ( $this->get_pagenum() - 1 ) * $per_page, $per_page ) );
		$this->total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}".$where_cond );

		$this->items = array();
		foreach ( (array) $rows as $row ) {
			$this->items[] = new Red_Group( $row );
		}

		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil( $this->total_items / $per_page ),
		) );
	}
}

class Redirection_Log_Table extends WP_List_Table {
	const REFERRER_MAX = 120;
	const TARGET_MAX = 80;
	private $lookup;

	function __construct( $options ) {
		$this->lookup = $options['lookup'];

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'item',	 //singular name of the listed records
			'plural'	=> 'items',	//plural name of the listed records
			'ajax'	  => false,		//does this table support ajax?
		) );
	}

	function column_created( $item ) {
		$actions = array();

		if ( $item->sent_to === '' ) {
			$actions['add'] = '<a href="'.esc_url( $item->url ).'" class="add-log">'.__( 'Add redirect', 'redirection' ).'</a>';
		}

		return sprintf( '%1$s %2$s', date_i18n( get_option( 'date_format' ), $item->created ).' '.gmdate( get_option( 'time_format' ), $item->created ), $this->row_actions( $actions ) );
	}

	function column_ip( $item ) {
		return '<a href="'.esc_attr( $this->lookup ).esc_attr( $item->ip ).'">'.esc_html( $item->ip ).'</a>';
	}

	function column_url( $item ) {
		$actions = array(
			'target' => esc_html( substr( $item->sent_to, 0, self::TARGET_MAX ) ),
		);

		return sprintf( '%1$s %2$s', '<a href="'.esc_url( $item->url ).'">'.esc_html( $item->show_url( $item->url ) ).'</a>', $this->row_actions( $actions ) );
	}

	function column_referrer( $item ) {
		$actions = array(
			'agent' => esc_html( substr( $item->agent, 0, self::REFERRER_MAX ) ),
		);

		return sprintf( '%1$s %2$s', '<a href="'.esc_url( $item->referrer ).'">'.esc_html( parse_url( $item->referrer, PHP_URL_HOST ) ).'</a>', $this->row_actions( $actions ) );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->id				//The value of the checkbox should be the record's id
		);
	}

	function get_columns() {
		$columns = array(
			'cb'	   => '<input type="checkbox" />', //Render a checkbox instead of text
			'created'	   => __( 'Date', 'redirection' ),
			'url'	  => __( 'Source URL', 'redirection' ),
			'referrer' => __( 'Referrer', 'redirection' ),
			'ip'	   => __( 'IP', 'redirection' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'created'	   => array( 'id', true ),
			'url'	  => array( 'url', false ),
			'referrer' => array( 'referrer', false ),
			'ip'	   => array( 'item_id', false ),
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'redirection' ),
		);
		return $actions;
	}

	function process_bulk_action() {
		if ( 'delete' === $this->current_action() ) {
			foreach( $_POST['item'] as $id ) {
				RE_Log::delete( intval( $id ) );
			}
		}
	}

	function prepare_items( $type = '', $id = 0 ) {
		global $wpdb, $current_user;

		$screen = get_current_screen();
		$per_page = get_user_meta( $current_user->ID, $screen->get_option( 'per_page', 'option' ), true );

		$per_page = $per_page ? $per_page : 25;
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

		$where_cond = '';
		if ( count( $where ) > 0 )
			$where_cond = ' WHERE '.implode( ' AND ', $where );

		$table = $wpdb->prefix.'redirection_logs';
		$rows		= $wpdb->get_results( "SELECT * FROM {$table} ".$where_cond.$wpdb->prepare( " ORDER BY $orderby $order LIMIT %d,%d", ( $this->get_pagenum() - 1 ) * $per_page, $per_page ) );
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}".$where_cond );

		$this->items = array();
		foreach ( (array) $rows as $row ) {
			$this->items[] = new RE_Log( $row );
		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}
}

class Redirection_404_Table extends WP_List_Table {
	private $lookup;

	function __construct( $options ) {
		$this->lookup = $options['lookup'];

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'item',	 //singular name of the listed records
			'plural'	=> 'items',	//plural name of the listed records
			'ajax'	  => false,		//does this table support ajax?
		) );
	}

	function column_created( $item ) {
		$actions['add'] = '<a href="'.esc_url( $item->url ).'" class="add-log">'.__( 'Add redirect', 'redirection' ).'</a>';

		return sprintf( '%1$s%2$s', date_i18n( get_option( 'date_format' ), $item->created ).'<br/>'.gmdate( get_option( 'time_format' ), $item->created ), $this->row_actions( $actions ) );
	}

	function column_ip( $item ) {
		$actions['add'] = '<a href="'.admin_url( 'tools.php?page=redirection.php&sub=404s&ip='.esc_attr( long2ip( $item->ip ) ) ).'">'.__( 'Show only this IP', 'redirection' ).'</a>';

		return sprintf( '%1$s %2$s', '<a href="'.esc_attr( $this->lookup ).esc_attr( long2ip( $item->ip ) ).'">'.long2ip( $item->ip ).'</a>', $this->row_actions( $actions ) );
	}

	function column_url( $item ) {
		return '<a href="'.esc_url( $item->url ).'">'.esc_html( $item->show_url( $item->url ) ).'</a>';
	}

	function column_referrer( $item ) {
		$actions = array(
			'agent' => esc_html( $item->agent ),
		);

		return sprintf( '%1$s %2$s', '<a href="'.esc_url( $item->referrer ).'">'.esc_html( parse_url( $item->referrer, PHP_URL_HOST ) ).'</a>', $this->row_actions( $actions ) );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->id				//The value of the checkbox should be the record's id
		);
	}

	function get_columns() {
		$columns = array(
			'cb'	   => '<input type="checkbox" />', //Render a checkbox instead of text
			'created'  => __( 'Date', 'redirection' ),
			'url'	  => __( 'Source URL', 'redirection' ),
			'referrer' => __( 'Referrer', 'redirection' ),
			'ip'	   => __( 'IP', 'redirection' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'created'  => array( 'id', true ),
			'url'	  => array( 'url', false ),
			'referrer' => array( 'referrer', false ),
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'redirection' ),
		);
		return $actions;
	}

	function process_bulk_action() {
		if ( 'delete' === $this->current_action() ) {
			foreach( $_POST['item'] as $id ) {
				RE_404::delete( intval( $id ) );
			}
		}
	}

	function prepare_items( $restrict_by_ip = false ) {
		global $wpdb, $current_user;

		$screen = get_current_screen();
		$per_page = get_user_meta( $current_user->ID, $screen->get_option( 'per_page', 'option' ), true );

		$per_page = $per_page ? $per_page : 25;
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

		if ( $restrict_by_ip !== false )
			$where[] = $wpdb->prepare( 'ip=INET_ATON(%s)', $restrict_by_ip );

		$where_cond = '';
		if ( count( $where ) > 0 )
			$where_cond = ' WHERE '.implode( ' AND ', $where );

		$table = $wpdb->prefix.'redirection_404';
		$rows		= $wpdb->get_results( "SELECT * FROM {$table} ".$where_cond.$wpdb->prepare( " ORDER BY $orderby $order LIMIT %d,%d",  ( $this->get_pagenum() - 1 ) * $per_page, $per_page ) );
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}".$where_cond );

		$this->items = array();
		foreach ( (array) $rows as $row ) {
			$this->items[] = new RE_Log( $row );
		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}
}

class Redirection_Module_Table extends WP_List_Table {
	private $token = false;

	function __construct( $token ) {
		$this->token = $token;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'item',	 //singular name of the listed records
			'plural'	=> 'items',	//plural name of the listed records
			'ajax'	  => false,		//does this table support ajax?
		) );
	}

	function get_columns() {
		$columns = array(
			'name'   => __( 'Module', 'redirection' ),
			'total' => __( 'Redirects', 'redirection' ),
		);

		return $columns;
	}

	function column_name( $item ) {
		$config = $item->get_config();

		if ( $item->can_edit_config() )
			$actions['edit'] = sprintf( '<a href="#" class="red-ajax" data-action="%s" data-nonce="%s" data-id="%s">'.__( 'Configure', 'redirection' ).'</a>', 'red_module_edit', wp_create_nonce( 'red_edit-'.$item->get_id() ), $item->get_id() );

		if ( $item->get_id() === WordPress_Module::MODULE_ID && $this->token )
			$actions['rss'] = sprintf( '<a href="%s">RSS</a>', '?page=redirection.php&amp;token='.$this->token.'&amp;sub=rss&amp;module='.intval( $item->get_id() ) );

		$actions['csv'] = sprintf( '<a href="%s">CSV</a>', '?page=redirection.php&amp;token='.$this->token.'&amp;sub=csv&amp;module='.intval( $item->get_id() ) );
		$actions['view-htaccess'] = sprintf( '<a href="#" class="red-ajax" data-id="%d" data-action="red_get_htaccess" data-nonce="%s">.htaccess</a>', $item->get_id(), wp_create_nonce( 'red_get_htaccess' ) );
		$actions['view-nginx']	= sprintf( '<a href="#" class="red-ajax" data-id="%d" data-action="red_get_nginx" data-nonce="%s">Nginx</a>', $item->get_id(), wp_create_nonce( 'red_get_nginx' ) );

		if ( count( $config ) > 0 )
			$config = '<div class="module-config">'.join( '<br/>', $config ).'</div>';
		else
			$config = '';

		return '<p><strong>'.esc_html( $item->get_name() ).'</strong></p>'.$item->get_description().$config.$this->row_actions( $actions );
	}

	function column_total( $item ) {
		return esc_html( $item->get_total_redirects() );
	}

	function prepare_items( $type = '', $id = 0 ) {
		global $wpdb;

		$options  = red_get_options();
		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, array(), $sortable );
		$this->items = Red_Module::get_for_select();
		$this->total_items = count( $this->items );
		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'	=> 100,
			'total_pages' => ceil( $this->total_items / 100 ),
		) );
	}
}
