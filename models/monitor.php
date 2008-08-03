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
		}
		
		if ($options['monitor_category'] > 0)
		{
			$this->monitor_category = $options['monitor_category'];
			
			add_action ('edit_category_form', array (&$this, 'insert_old_category'));
			add_action ('edit_category', array (&$this, 'category_changed'));
		}
	}
	
	function insert_old_category ($category)
	{
		$url = parse_url (get_category_link ($category->cat_ID));
	?>
	<input type="hidden" name="redirection_slug" value="<?php echo attribute_escape ($url['path']) ?>"/>
	<?php
	}
	
	function category_changed ($categoryid)
	{
		$new_url = parse_url (get_category_link ($categoryid));
		$new_url['path'] = dirname ($new_url['path']).'/'.$_POST['category_nicename'];

		if ($new_url['path'] != $_POST['redirection_slug'])
		{
			$redirect = array
			(
				'source' => '^'.$_POST['redirection_slug'].'/(.*)$',
				'target' => $new_url['path'].'/$1',
				'match'  => 'url_match',
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

		if ($newslug != $oldslug && strlen ($oldslug) > 0 && ($post->post_status == 'publish' || $post->post_status == 'static') && $_POST['redirection_status'] != 'draft')
		{
			$old_url = parse_url ($oldslug);
			$new_url = parse_url ($newslug);

			Red_Item::create (array ('source' => $old_url['path'], 'target' => $new_url['path'], 'match' => 'url_match', 'action' => 'url', 'group' => $this->monitor_post));
		}
	}
}
			

?>