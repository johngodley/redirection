<?php

class Red_Monitor
{
	var $monitor_post;
	var $monitor_category;
	
	function Red_Monitor ($options)
	{
		if ($options['monitor_post'] > 0)
		{
			$this->monitor_post = $options['monitor_post'];
			
			add_action ('edit_form_advanced', array (&$this, 'insert_old_post'));
			add_action ('edit_page_form',     array (&$this, 'insert_old_post'));
			add_action ('edit_post',          array (&$this, 'post_changed'));
			add_action ('delete_post',        array (&$this, 'post_deleted'));
			
//			if ($options['monitor_new_posts'])
//				add_action ('transition_post_status', array (&$this, 'transition_post_status'), 10, 3);
		}
		
		if ($options['monitor_category'] > 0)
		{
/*			$this->monitor_category = $options['monitor_category'];
			
			add_action ('edit_category_form', array (&$this, 'insert_old_category'));
			add_action ('edit_category', array (&$this, 'category_changed'));
			*/
		}
	}
	
	function transition_post_status ($new_status, $old_status, $post)
	{
		if ($new_status == 'publish')
		{
			$redirect = array
			(
				'source' => '',
				'target' => substr (get_permalink ($post->ID), strlen (get_bloginfo ('home'))),
				'match'  => 'url',
				'action' => 'url',
				'regex'  => false,
				'group'  => $this->monitor_post
			);
				
			Red_Item::create ($redirect);
		}
	}
	
	function insert_old_category ($category)
	{
		if (isset ($category->cat_ID))
		{
			$link = get_category_link ($category->cat_ID);
			$url = parse_url ($link);
	?>
	<input type="hidden" name="redirection_slug" value="<?php echo attribute_escape ($url['path']) ?>"/>
	<?php
		}
	}
	
	function category_changed ($categoryid)
	{
		$new_url = parse_url (get_category_link ($categoryid));
		$new_url['path'] = dirname ($new_url['path']).'/'.$_POST['category_nicename'];

		if ($new_url['path'] != $_POST['redirection_slug'] && $_POST['redirection_slug'] != '')
		{
			$redirect = array
			(
				'source' => '^'.$_POST['redirection_slug'].'/(.*)$',
				'target' => $new_url['path'].'/$1',
				'match'  => 'url',
				'action' => 'url',
				'regex'  => true,
				'group'  => $this->monitor_post
			);
			
			Red_Item::create ($redirect);
		}
	}
	
	function insert_old_post ()
	{
		global $post;
	?>
	<input type="hidden" name="redirection_slug" value="<?php the_permalink () ?>"/>
	<input type="hidden" name="redirection_status" value="<?php echo $post->post_status ?>"/>
	<?php
	}

	function post_changed ($id)
	{
		$post    = get_post ($id);
		$newslug = get_permalink ($id);
		$oldslug = $_POST['redirection_slug'];
		$base    = get_option ('home');

		if ($newslug != $oldslug && strlen ($oldslug) > 0 && ($post->post_status == 'publish' || $post->post_status == 'static') && $_POST['redirection_status'] != 'draft' && $_POST['redirection_status'] != 'pending' && $newslug != '/')
		{
			$old_url = parse_url ($oldslug);
			$new_url = parse_url ($newslug);

			$conflicting_items = Red_Item::get_for_url ($new_url['path'], 'wp');
			foreach ($conflicting_items as $item) {
				Red_Item::delete ($item->id);
			}

			Red_Item::create (array ('source' => $old_url['path'], 'target' => $new_url['path'], 'match' => 'url', 'action' => 'url', 'group' => $this->monitor_post));
		}
	}
	
	function post_deleted ($id)
	{
		$post = get_post ($id);
		if ($post->post_status == 'publish' || $post->post_status == 'static')
		{
			$url  = get_permalink ($id);
			$slug = parse_url ($url);

			Red_Item::create (array ('source' => $slug['path'], 'target' => '', 'match' => 'url', 'action' => 'error', 'group' => $this->monitor_post, 'action_code' => 410));
		}
	}
}
			

?>
