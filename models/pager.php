<?php

// ======================================================================================
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
// ======================================================================================
// @author     John Godley (http://urbangiraffe.com)
// @version    0.2.8
// @copyright  Copyright &copy; 2007 John Godley, All Rights Reserved
// ======================================================================================
// 0.2.3 - Remember pager details in user data
// 0.2.4 - Add phpdoc comments
// 0.2.5 - Allow orderby to use tags to hide database columns
// 0.2.6 - Fix sortable columns with only 1 page
// 0.2.7 - Add a GROUP BY feature, make search work when position not 0
// 0.2.8 - WP 2.7 functions
// ======================================================================================


/**
 * Provides pagination, column-based ordering, searching, and filtering
 *
 * The class does no database queries itself but instead relies on the user modifying their queries with data
 * from the pager.  For correct pagination you must set the total number of results
 *
 * For example,
 *
 * $pager->set_total ($wpdb->get_var ("SELECT COUNT(*) FROM wp_posts").$pager->to_conditions ());
 * $rows = $wpdb->get_results ("SELECT * FROM wp_posts".$pager->to_limits ("post_type=page"));
 *
 * Searching is achieved by specifying the columns that can be searched:
 *
 * $rows = $wpdb->get_results ("SELECT * FROM wp_posts".$pager->to_limits ("post_type=page", array ('post_content', 'post_excerpt')));
 *
 * Additionally you can output column headings with correct URLs:
 *   <th><?php echo $pager->sortable ('post_username', 'Username') ?></th>
 *
 * @package default
 **/

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

		if (isset ($data['curpage']))
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
	// XXX

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
				$searchbits[] = $wpdb->prepare( $search.' LIKE "%s"', '%'.$this->search.'%' );

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

?>
