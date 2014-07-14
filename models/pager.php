<?php

if ( !class_exists( 'WP_List_Table' ) )
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

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

class Redirection_Group_Table extends WP_List_Table {
	private $lookup;

	function __construct( $options ) {
		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'item',     //singular name of the listed records
			'plural'    => 'items',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_name( $item ) {
		$actions['add'] = '<a href="'.esc_url( $item->url ).'" class="add-log">'.__( 'Name', 'redirection' ).'</a>';

		return 'Name';
	}

	function column_hits( $item ) {
		$actions['add'] = '<a href="'.admin_url( 'tools.php?page=redirection.php&sub=404s&ip='.esc_attr( long2ip( $item->ip ) ) ).'">'.__( 'Show only this IP', 'redirection' ).'</a>';

		return 'Hits';
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
			'name'  => __( 'Name', 'redirection' ),
			'hits'      => __( 'Hits', 'redirection' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', false ),
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
// XXX				RE_404::delete( intval( $id ) );
			}
		}
	}

	function prepare_items( $restrict_by_ip = false ) {
		global $wpdb, $current_user;

		$screen   = get_current_screen();
		$per_page = get_user_meta( $current_user->ID, $screen->get_option( 'per_page', 'option' ), true );

		$per_page     = $per_page ? $per_page : 25;
		$columns      = $this->get_columns();
		$sortable     = $this->get_sortable_columns();
		$current_page = $this->get_pagenum();

		$this->_column_headers = array( $columns, array(), $sortable );

		// Process any stuff
		$this->process_bulk_action();

		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
		$order   = ( ! empty( $_GET['order'] ) ) ? strtolower( $_GET['order'] ) : 'desc';

		if ( !in_array( $orderby, array_keys( $sortable ) ) )
			$orderby = 'id';

		if ( !in_array( $order, array( 'asc', 'desc' ) ) )
			$order = 'desc';

		$table = $wpdb->prefix.'redirection_groups';

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

class RE_Pager
{
	var $url             = null;
	var $current_page    = 1;
	var $per_page        = 25;
	var $total           = 0;
	var $order_by        = null;
	var $order_original  = null;
	var $order_direction = null;
	var $order_tags      = array ();
	var $steps           = array ();
	var $search          = null;
	var $filters         = array ();
	var $id;


	/**
	 * Construct a pager object using the $_GET data, the current URL, and default preferences
	 *
	 * @param array $data Array of values, typically from $_GET
	 * @param string $url The current URL
	 * @param string $orderby Default database column to order data by
	 * @param string $direction Default direction of ordering (DESC or ASC)
	 * @param string $id An ID for the pager to separate it from other pagers (typically the plugin name)
	 * @return void
	 **/
	function RE_Pager ($data, $url, $orderby = '', $direction = 'DESC', $id = 'default', $tags = '')
	{
		// Remove all pager params from the url
		$this->id  = $id;
		$this->url = $url;

		if (isset ($data['curpage']) && $data['curpage'] > 0)
			$this->current_page = intval ($data['curpage']);

		global $user_ID;

		if (isset ($data['perpage']))
		{
			$this->per_page = intval ($data['perpage']);
			$per_page[get_class ($this)][$this->id] = $this->per_page;
		}
		else if (isset ($per_page[get_class ($this)]) && isset ($per_page[get_class ($this)][$this->id]))
			$this->per_page = $per_page[get_class ($this)][$this->id];

		if (!empty ($tags))
		{
			$this->order_tags = $tags;
			if (isset ($this->order_tags[$this->order_by]))
				$this->order_by = $this->order_tags[$this->order_by];
		}

		$this->order_direction = $direction;
		$this->order_original  = $orderby;
		if (isset ($data['order']))
			$this->order_direction = $data['order'];

		$this->search = isset($data['search']) ? $data['search'] : '';
		$this->steps = array (10, 25, 50, 100, 250);
		$this->url = str_replace ('&', '&amp;', $this->url);
		$this->url = str_replace ('&&amp;', '&amp;', $this->url);
	}


	/**
	 * Set the total number of entries that match the conditions
	 *
	 * @param int $total Count
	 * @return void
	 **/

	function set_total ($total)
	{
		$this->total = $total;

		if ($this->current_page <= 0 || $this->current_page > $this->total_pages ())
			$this->current_page = 1;
	}


	/**
	 * Return the current page offset
	 *
	 * @return int Current page offset
	 **/

	function offset ()
	{
		return ($this->current_page - 1) * $this->per_page;
	}


	/**
	 * @todo explain
	 * @return void
	 **/
	function is_secondary_sort ()
	{
		return substr ($this->order_by, 0, 1) == '_' ? true : false;
	}


	/**
	 * Returns a set of conditions without any limits.  This is suitable for a COUNT SQL
	 *
	 * @param string $conditions WHERE conditions
	 * @param array $searches Array of columns to search on
	 * @param array $filters Array of columns to filter on
	 * @return string SQL
	 **/

	function to_conditions ($conditions, $searches = '', $filters = '')	{
		global $wpdb;

		$sql = '';
		if ($conditions != '')
			$sql .= ' WHERE '.$conditions;

		// Add on search conditions
		if (is_array ($searches) && $this->search != '')
		{
			if ($sql == '')
				$sql .= ' WHERE (';
			else
				$sql .= ' AND (';

			$searchbits = array ();
			foreach ($searches AS $search)
				$searchbits[] = $wpdb->prepare( $search.' LIKE %s', '%'.like_escape( $this->search ).'%' );

			$sql .= implode (' OR ', $searchbits);
			$sql .= ')';
		}

		// Add filters
		if (is_array ($filters) && !empty ($this->filters))
		{
			$searchbits = array ();
			foreach ($filters AS $filter)
			{
				if (isset ($this->filters[$filter]))
				{
					if ($this->filters[$filter] != '')
						$searchbits[] = $wpdb->prepare( $filter." = %s", $this->filters[$filter] );
				}
			}

			if (count ($searchbits) > 0)
			{
				if ($sql == '')
					$sql .= ' WHERE (';
				else
					$sql .= ' AND (';

				$sql .= implode (' AND ', $searchbits);
				$sql .= ')';
			}
		}

		return $sql;
	}


	/**
	 * Returns a set of conditions with limits.
	 *
	 * @param string $conditions WHERE conditions
	 * @param array $searches Array of columns to search on
	 * @param array $filters Array of columns to filter on
	 * @return string SQL
	 **/

	function to_limits ($conditions = '', $searches = '', $filters = '', $group_by = '') {
		global $wpdb;

		$sql = $this->to_conditions ($conditions, $searches, $filters);

		if ($group_by)
			$sql .= ' '.$group_by.' ';

		if ($this->per_page > 0)
			$sql .= $wpdb->prepare( ' LIMIT %d,%d', $this->offset(), $this->per_page );
		return $sql;
	}


	/**
	 * Return the url with all the params added back
	 *
	 * @param int Page offset
	 * @param string $orderby Optional order
	 * @return string URL
	 **/

	function url ($offset, $orderby = '')
	{
		// Position
		if (strpos ($this->url, 'curpage=') !== false)
			$url = preg_replace ('/curpage=\d*/', 'curpage='.$offset, $this->url);
		else
			$url = $this->url.'&amp;curpage='.$offset;

		// Order
		if ($orderby != '')
		{
			if (strpos ($url, 'orderby=') !== false)
				$url = preg_replace ('/orderby=\w*/', 'orderby='.$orderby, $url);
			else
				$url = $url.'&amp;orderby='.$orderby;

			if (!empty ($this->order_tags) && isset ($this->order_tags[$orderby]))
				$dir = $this->order_direction == 'ASC' ? 'DESC' : 'ASC';
			else if ($this->order_by == $orderby)
				$dir = $this->order_direction == 'ASC' ? 'DESC' : 'ASC';
			else
				$dir = $this->order_direction;

			if (strpos ($url, 'order=') !== false)
				$url = preg_replace ('/order=\w*/', 'order='.$dir, $url);
			else
				$url = $url.'&amp;order='.$dir;
		}

		if ( isset( $_GET['id'] ) )
			$url .= '&amp;id='.intval( $_GET['id'] );

		if ( isset( $_GET['perpage'] ) )
			$url .= '&amp;perpage='.intval( $_GET['perpage'] );

		if ( isset( $_GET['search'] ) )
			$url .= '&amp;search='.urlencode( $_GET['search'] );

		return str_replace ('&go=go', '', $url);
	}


	/**
	 * Return current page
	 *
	 * @return int
	 **/

	function current_page () { return $this->current_page; }


	/**
	 * Return total number of pages
	 *
	 * @return int
	 **/

	function total_pages ()
	{
		if ($this->per_page == 0)
			return 1;
		return ceil ($this->total / $this->per_page);
	}


	/**
	 * Determine if we have a next page
	 *
	 * @return boolean
	 **/

	function have_next_page ()
	{
		if ($this->current_page < $this->total_pages ())
			return true;
		return false;
	}


	/**
	 * Determine if we have a previous page
	 *
	 * @return boolean
	 **/

	function have_previous_page ()
	{
		if ($this->current_page > 1)
			return true;
		return false;
	}


	function sortable_class ($column, $class = true)
	{
		if ($column == $this->order_by)
		{
			if ($class)
				printf (' class="sortedd"');
			else
				echo ' sortedd';
		}
	}

	/**
	 * Return a string suitable for a sortable column heading
	 *
	 * @param string $column Column to search upon
	 * @param string $text Text to display for the column
	 * @param boolean $image Whether to show a direction image
	 * @return string URL
	 **/

	function sortable ($column, $text, $image = true)
	{
		return $text;
		$url = admin_url( add_query_arg( array( 'orderby' => $column ), 'redirection.php' ) );

		$img = '';

		if (isset ($this->order_tags[$column]))
			$column = $this->order_tags[$column];

		if ($column == $this->order_by)
		{
			$dir = WP_PLUGIN_URL.'/'.basename (dirname (dirname (__FILE__)));

			if (strpos ($url, 'ASC') !== false)
				$img = '<img align="bottom" src="'.$dir.'/images/up.gif" alt="dir" width="16" height="7"/>';
			else
				$img = '<img align="bottom" src="'.$dir.'/images/down.gif" alt="dir" width="16" height="7"/>';

			if ($image == false)
				$img = '';
		}

		return '<a href="'.$url.'">'.$text.'</a>'.$img;
	}


	/**
	 * Returns an array of page numbers => link, given the current page (next and previous etc)
	 *
	 * @return array Array of page links
	 **/

	function area_pages ()
	{
		// First page
		$allow_dot = true;
		$pages = array ();

		if ($this->total_pages () > 1)
		{
			$previous = __ ('Previous', 'redirection');
			$next     = __ ('Next', 'redirection');

			if ($this->have_previous_page ())
				$pages[] = '<a href="'.$this->url ($this->current_page - 1).'">'.$previous.'</a> |';
			else
				$pages[] = $previous.' |';

			for ($pos = 1; $pos <= $this->total_pages (); $pos++)
			{
				if ($pos == $this->current_page)
				{
					$pages[] = '<span class="active">'.$pos.'</span>';
					$allow_dot = true;
				}
				else if ($pos == 1 || abs ($this->current_page - $pos) <= 2 || $pos == $this->total_pages ())
					$pages[] = '<a href="'.$this->url ($pos).'">'.$pos."</a>";
				else if ($allow_dot)
				{
					$allow_dot = false;
					$pages[] = '&hellip;';
				}
			}

			if ($this->have_next_page ())
				$pages[] = '| <a href="'.$this->url ($this->current_page + 1).'">'.$next.'</a>';
			else
				$pages[] = '| '.$next;
		}

		return $pages;
	}


	/**
	 * @todo
	 * @return boolean
	 **/

	function filtered ($field, $value)
	{
		if (isset ($this->filters[$field]) && $this->filters[$field] == $value)
			return true;
		return false;
	}


	/**
	 * Display a SELECT box suitable for a per-page
	 *
	 * @return void
	 **/

	function per_page ($plugin = '')
	{
		?>
		<select name="perpage">
			<?php foreach ($this->steps AS $step) : ?>
		  	<option value="<?php echo $step ?>"<?php if ($this->per_page == $step) echo ' selected="selected"' ?>>
					<?php printf (__ ('%d per-page', $plugin), $step) ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	function page_links ()
	{
		$text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>',
											number_format_i18n (($this->current_page () - 1) * $this->per_page + 1),
											number_format_i18n ($this->current_page () * $this->per_page > $this->total ? $this->total : $this->current_page () * $this->per_page),
											number_format_i18n ($this->total));

		$links = paginate_links (array ('base' => str_replace ('99', '%#%', $this->url (99)), 'format' => '%#%', 'current' => $this->current_page (), 'total' => $this->total_pages (), 'end_size' => 3, 'mid_size' => 2, 'prev_next' => true));
		return $text.$links;
	}
}
