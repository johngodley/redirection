<?php

if ( !class_exists( 'WP_List_Table' ) )
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class Redirection_Table extends WP_List_Table {
	private $groups;
	private $total_items;

	function __construct( array $groups, Red_Group $current_group = null ) {
		$this->groups = $groups;
		$this->current_group = $current_group;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'item',     //singular name of the listed records
			'plural'    => 'items',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function get_columns(){
		$columns = array(
			'cb'       => '<input type="checkbox" />', //Render a checkbox instead of text
			'type'       => __( 'Type', 'redirection' ),
			'url'       => __( 'URL', 'redirection' ),
			'hits'      => __( 'Hits', 'redirection' ),
			'last_access' => __( 'Last Access', 'redirection' ),
		);

		return $columns;
	}

	function column_type( $item ) {
		return esc_html( $item->type() );
	}

	function column_last_access( $item ) {
		if ( $item->last_access == 0 )
			return '&mdash;';
		return date_i18n( get_option( 'date_format' ), $item->last_access );
	}

	function column_hits( $item ) {
		return esc_html( $item->last_count );
	}

	function column_url( $item ) {
		$actions = array(
			'edit'     => sprintf( '<a class="red-ajax" data-action="%s" data-nonce="%s" data-id="%s" href="#">'.__( 'Edit', 'redirection' ).'</a>', 'red_redirect_edit', wp_create_nonce( 'red-edit_'.$item->get_id() ), $item->get_id() ),
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

		return sprintf( '%1$s %2$s', $before.'<a href="'.esc_url( $item->url ).'">'.esc_html( $item->url ).'</a>'.$after, $this->row_actions( $actions ) );
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->id                //The value of the checkbox should be the record's id
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
			'url'      => array( 'url', false),
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
		if ( !isset( $_POST['item'] ) )
			return;

		if ( in_array( $this->current_action(), array( 'reset', 'enable', 'disable', 'delete' ) ) ) {
			$redirections = array();

			foreach( (array)$_POST['item'] AS $id ) {
				$redirect = Red_Item::get_by_id( intval( $id ) );
				if ( $redirect )
					$redirections[] = $redirect;
			}

			array_map( array( &$this, 'process_action_items' ), $redirections );

			Red_Module::flush( $this->current_group->module_id );
		}
	}

	function process_action_items( $item ) {
		if ( $this->current_action() == 'reset' )
			$item->reset();
		elseif ( $this->current_action() == 'enable' )
			$item->enable();
		elseif ( $this->current_action() == 'disable' )
			$item->disable();
		elseif ( $this->current_action() == 'delete' )
			$item->delete();
	}

	function extra_tablenav( $which ) {
		if ( $which == 'bottom' )
			return;

?>
		<div class="alignleft actions">
			<select name="id">
				<?php foreach ( $this->groups AS $module_name => $groups ) : ?>
					<optgroup label="<?php echo esc_attr( $module_name ); ?>">
						<?php foreach ( $groups AS $group_name => $group ) : ?>
							<option value="<?php echo esc_attr( $group_name ); ?>"<?php selected( $group_name, $this->current_group->get_id() ); ?>>
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
		$per_page = get_user_meta( $current_user->ID, $screen->get_option( 'per_page', 'option' ), true );

		$per_page = $per_page ? $per_page : 25;
		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, array(), $sortable );

		// Process any stuff
		$this->process_bulk_action();

		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
		$order   = ( ! empty( $_GET['order'] ) ) ? strtolower( $_GET['order'] ) : 'desc';

		if ( !in_array( $orderby, array_keys( $sortable ) ) )
			$orderby = 'id';

		if ( !in_array( $order, array( 'asc', 'desc' ) ) )
			$order = 'desc';

		$where = array();
		if ( isset( $_GET['s'] ) )
			$where[] = $wpdb->prepare( 'url LIKE %s', '%'.like_escape( $_GET['s'] ).'%' );

		if ( isset( $_REQUEST['id'] ) )
			$where[] = $wpdb->prepare( "group_id=%d", intval( $_REQUEST['id'] ) );

		$where_cond = "";
		if ( count( $where ) > 0 )
			$where_cond = " WHERE ".implode( ' AND ', $where );

		$table = $wpdb->prefix.'redirection_items';
		$rows        = $wpdb->get_results( "SELECT * FROM {$table} ".$where_cond.$wpdb->prepare( " ORDER BY $orderby $order LIMIT %d,%d", ( $this->get_pagenum() - 1 ) * $per_page, $per_page ) );
		$this->total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}".$where_cond );

		$this->items = array();
		foreach ( (array)$rows AS $row ) {
			$this->items[] = new Red_Item( $row );
		}

		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->total_items / $per_page )
		) );
	}
}

class Redirection_Group_Table extends WP_List_Table {
	private $modules;
	private $current_module;

	function __construct( $modules, $current_module ) {
		$this->modules = $modules;
		$this->current_module = $current_module;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'item',     //singular name of the listed records
			'plural'   => 'items',    //plural name of the listed records
			'ajax'     => false        //does this table support ajax?
		) );
	}

	function get_columns(){
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'name'      => __( 'Name', 'redirection' ),
			'redirects' => __( 'Redirects', 'redirection' ),
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
		if ( $item->is_disabled() ) {
			$before = '<span class="red-disabled">';
			$after = '</span>';
		}

		return sprintf( '%1$s %2$s', $before.esc_html( $item->get_name() ).$after, $this->row_actions( $actions ) );
	}

	function column_redirects( $item ) {
		return esc_html( $item->get_item_count() );
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->id                //The value of the checkbox should be the record's id
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
			'delete' => __( 'Delete', 'redirection' ),
		);

		return $actions;
	}

	function process_bulk_action() {
		if ( !isset( $_POST['item'] ) )
			return;

		if ( in_array( $this->current_action(), array( 'delete' ) ) ) {
			$groups = array();

			foreach( (array)$_POST['item'] AS $id ) {
				$redirect = Red_Group::get( intval( $id ) );
				if ( $redirect )
					$groups[] = $redirect;
			}

			array_map( array( &$this, 'delete_item' ), $groups );
		}
	}

	function delete_item( $item ) {
		$item->delete();
	}

	protected function extra_tablenav( $which ) {
		if ( $which == 'bottom' )
			return;

?>
		<div class="alignleft actions">
			<select name="id">
				<?php foreach ( $this->modules AS $module_name => $module ) : ?>
					<option value="<?php echo esc_attr( $module_name ); ?>"<?php selected( $module_name, $this->current_module ); ?>>
						<?php echo esc_html( $module ); ?>
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

		if ( !in_array( $orderby, array_keys( $sortable ) ) )
			$orderby = $wpdb->prefix.'redirection_groups.name';

		if ( !in_array( $order, array( 'asc', 'desc' ) ) )
			$order = 'desc';

		$where = array();
		if ( isset( $_GET['s'] ) )
			$where[] = $wpdb->prepare( 'name LIKE %s', '%'.like_escape( $_GET['s'] ).'%' );

		if ( isset( $_REQUEST['id'] ) )
			$where[] = $wpdb->prepare( "module_id=%d", intval( $_REQUEST['id'] ) );

		$where_cond = "";
		if ( count( $where ) > 0 )
			$where_cond = " WHERE ".implode( ' AND ', $where );

		$table = $wpdb->prefix.'redirection_groups';
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ".$where_cond.$wpdb->prepare( " ORDER BY $orderby $order LIMIT %d,%d", ( $this->get_pagenum() - 1 ) * $per_page, $per_page ) );
		$this->total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}".$where_cond );

		$this->items = array();
		foreach ( (array)$rows AS $row ) {
			$this->items[] = new Red_Group( $row );
		}

		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->total_items / $per_page )
		) );
	}
}

class Redirection_Log_Table extends WP_List_Table {
	private $lookup;

	function __construct( $options ) {
		$this->lookup = $options['lookup'];

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'item',     //singular name of the listed records
			'plural'    => 'items',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_created( $item ) {
		$actions = array();

		if ( $item->sent_to == '' ) {
			$actions['add'] = '<a href="'.esc_url( $item->url ).'" class="add-log">'.__( 'Add redirect', 'redirection' ).'</a>';
		}

		return sprintf( '%1$s %2$s', date_i18n( get_option( 'date_format' ), $item->created ).' '.gmdate( get_option( 'time_format' ), $item->created ), $this->row_actions( $actions ) );
	}

	function column_ip( $item ) {
		return '<a href="'.esc_attr( $this->lookup ).esc_attr( $item->ip ).'">'.esc_html( $item->ip ).'</a>';
	}

	function column_url( $item ) {
		$actions = array(
			'target' => esc_html( $item->sent_to ),
		);

		return sprintf( '%1$s %2$s', '<a href="'.esc_url( $item->url ).'">'.esc_html( $item->show_url( $item->url ) ).'</a>', $this->row_actions( $actions ) );
	}

	function column_referrer( $item ) {
		$actions = array(
			'agent' => esc_html( $item->agent ),
		);

		return sprintf( '%1$s %2$s', '<a href="'.esc_url( $item->referrer ).'">'.esc_html( parse_url( $item->referrer, PHP_URL_HOST ) ).'</a>', $this->row_actions( $actions ) );
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->id                //The value of the checkbox should be the record's id
		);
	}

	function get_columns(){
		$columns = array(
			'cb'       => '<input type="checkbox" />', //Render a checkbox instead of text
			'created'       => __( 'Date', 'redirection' ),
			'url'      => __( 'Source URL', 'redirection' ),
			'referrer' => __( 'Referrer', 'redirection' ),
			'ip'       => __( 'IP', 'redirection' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'created'       => array( 'id', true ),
			'url'      => array( 'url', false),
			'referrer' => array( 'referrer', false ),
			'ip'       => array( 'item_id', false ),
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'redirection' )
		);
		return $actions;
	}

	function process_bulk_action() {
		if ( 'delete' === $this->current_action() ) {
			foreach( $_POST['item'] AS $id ) {
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

		if ( !in_array( $orderby, array_keys( $sortable ) ) )
			$orderby = 'id';

		if ( !in_array( $order, array( 'asc', 'desc' ) ) )
			$order = 'desc';

		$where = array();
		if ( isset( $_GET['s'] ) )
			$where[] = $wpdb->prepare( 'url LIKE %s', '%'.like_escape( $_GET['s'] ).'%' );

		$where_cond = "";
		if ( count( $where ) > 0 )
			$where_cond = " WHERE ".implode( ' AND ', $where );

		$table = $wpdb->prefix.'redirection_logs';
		$rows        = $wpdb->get_results( "SELECT * FROM {$table} ".$where_cond.$wpdb->prepare( " ORDER BY $orderby $order LIMIT %d,%d", ( $this->get_pagenum() - 1 ) * $per_page, $per_page ) );
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}".$where_cond );

		$this->items = array();
		foreach ( (array)$rows AS $row ) {
			$this->items[] = new RE_Log( $row );
		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
	}
}

class Redirection_404_Table extends WP_List_Table {
	private $lookup;

	function __construct( $options ) {
		$this->lookup = $options['lookup'];

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'item',     //singular name of the listed records
			'plural'    => 'items',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
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

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->id                //The value of the checkbox should be the record's id
		);
	}

	function get_columns(){
		$columns = array(
			'cb'       => '<input type="checkbox" />', //Render a checkbox instead of text
			'created'  => __( 'Date', 'redirection' ),
			'url'      => __( 'Source URL', 'redirection' ),
			'referrer' => __( 'Referrer', 'redirection' ),
			'ip'       => __( 'IP', 'redirection' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'created'  => array( 'id', true ),
			'url'      => array( 'url', false),
			'referrer' => array( 'referrer', false ),
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'redirection' )
		);
		return $actions;
	}

	function process_bulk_action() {
		if ( 'delete' === $this->current_action() ) {
			foreach( $_POST['item'] AS $id ) {
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

		if ( !in_array( $orderby, array_keys( $sortable ) ) )
			$orderby = 'id';

		if ( !in_array( $order, array( 'asc', 'desc' ) ) )
			$order = 'desc';

		$where = array();
		if ( isset( $_GET['s'] ) )
			$where[] = $wpdb->prepare( 'url LIKE %s', '%'.like_escape( $_GET['s'] ).'%' );

		if ( $restrict_by_ip !== false )
			$where[] = $wpdb->prepare( 'ip=INET_ATON(%s)', $restrict_by_ip );

		$where_cond = "";
		if ( count( $where ) > 0 )
			$where_cond = " WHERE ".implode( ' AND ', $where );

		$table = $wpdb->prefix.'redirection_404';
		$rows        = $wpdb->get_results( "SELECT * FROM {$table} ".$where_cond.$wpdb->prepare( " ORDER BY $orderby $order LIMIT %d,%d", ( $this->get_pagenum() - 1 ) * $per_page, $per_page ) );
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}".$where_cond );

		$this->items = array();
		foreach ( (array)$rows AS $row ) {
			$this->items[] = new RE_Log( $row );
		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
	}
}

class Redirection_Module_Table extends WP_List_Table {
	function __construct() {
		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'item',     //singular name of the listed records
			'plural'    => 'items',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function get_columns() {
		$columns = array(
			'moduletype'   => __( 'Type', 'redirection' ),
			'name' => __( 'Name', 'redirection' ),
			'groups' => __( 'Groups', 'redirection' ),
			'hits'   => __( 'Hits', 'redirection' ),
		);

		return $columns;
	}

	function column_groups( $item ) {
		return esc_html( $item->groups() );
	}

	function column_hits( $item ) {
		return esc_html( $item->hits() );
	}

	function column_moduletype( $item ) {
		return esc_html( $item->get_type_string() );
	}

	function column_name( $item ) {
		$actions['edit']   = sprintf( '<a href="#" class="red-ajax" data-action="%s" data-nonce="%s" data-id="%s">'.__( 'Edit', 'redirection' ).'</a>', 'red_module_edit', wp_create_nonce( 'red_edit-'.$item->get_id() ), $item->get_id() );

		return '<a href="#" data-action="%s" data-nonce="%s" data-id="%s">'.esc_html( $item->get_name() ).'</a>'.$this->row_actions( $actions );
	}

	function prepare_items( $type = '', $id = 0 ) {
		global $wpdb;

		$table = $wpdb->prefix.'redirection_modules';
		$rows = $wpdb->get_results( "SELECT * FROM {$table}" );
		$this->total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, array(), $sortable );

		$this->items = array();
		foreach ( (array)$rows AS $row ) {
			$this->items[] = Red_Module::new_item( $row );
		}

		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'    => 100,
			'total_pages' => ceil( $this->total_items / 100 )
		) );
	}
}
