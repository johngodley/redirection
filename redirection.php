<?php
/*
Plugin Name: Redirection
Plugin URI: http://urbangiraffe.com/plugins/redirection/
Description: A redirection manager
Version: 1.7.21
Author: John Godley
Author URI: http://urbangiraffe.com
============================================================================================================
1.1    - Added .htaccess processing, counter reset, and Search Regex
         Better item deletion, and infinite redirection protection
1.2    - AJAX support (delete/edit), split admin interface, bug fixes, better regex, redirections to external sites
1.3    - Highlight internal redirects.  Fix bug in Google code
1.4    - Change to wpurl
1.5    - Make non-regex pattern not match subpatterns of itself
1.6    - Allow 'custom' scripts
1.7    - Interface redesign and feature upgrade
1.7.9  - Fix bug with PHP4. Add search box and IP lookup service.  Add log delete function & update notification.  Add 410.
         Add optional global 404 redirect
1.7.10 - Fix bug where other plugins that hook redirections wouldn't work when no redirections are defined
1.7.11 - Fix bug where index.html was not being redirected correctly
1.7.12 - Better database performance, clean up log display, make usable in WP 2.0+
1.7.13 - Workaround for FastCGI bug
1.7.14 - Add delete option, stop AJAX from looping on a bad redirection
1.7.15 - Minor bug fix, show redirect names in drop-down list, add auto-generated target URL
1.7.16 - Prevent errors caused by magic _vti_cnf.php files
1.7.17 - Add option to disable 404 logs
1.7.18 - Add auto-generation for source URL
1.7.19 - Better database installation, better auto-generation
1.7.20 - Workaround for the FastCGI workaround.  Hide canonical options for WP2.3
1.7.21 - Fix activation bug
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================
*/

include (dirname (__FILE__).'/plugin.php');
include (dirname (__FILE__).'/models/redirection_item.php');
include (dirname (__FILE__).'/models/redirector.php');
include (dirname (__FILE__).'/models/log.php');


class Redirection extends Redirection_Plugin
{
	var $redirectors;
	
	function Redirection ()
	{
		$this->register_plugin ('redirection', __FILE__);
		
		$this->redirectors = new Redirector_Factory;
		
		if (is_admin ())
		{
			include (dirname (__FILE__).'/models/pager.php');
			
			$this->add_action ('admin_menu');
			
			if (strpos ($_SERVER['REQUEST_URI'], 'redirection.php'))
				$this->add_action ('admin_head');
			
			$this->register_activation (__FILE__);
			if (get_option ('redirection_post') == 'true')
			{
				$this->add_action ('edit_form_advanced', 'insert_old_slug');
				$this->add_action ('edit_page_form',     'insert_old_slug');
				$this->add_action ('edit_post',          'post_changed');
			}
		}
		else
		{
			$this->add_action ('plugins_loaded');
			$this->add_action ('template_redirect', 'check_404');
			$this->add_action ('send_headers');
			$this->add_filter ('permalink_redirect_skip');          // For YLSY permalink plugin
			
			global $wp_db_version;
			if ($wp_db_version < 6000)
				$this->add_filter ('status_header');
		}
		
		// Remove WordPress redirection
		remove_action ('template_redirect', 'wp_old_slug_redirect');
		remove_action ('edit_form_advanced', 'wp_remember_old_slug');
	}
	
	function activate ()
	{
		include (dirname (__FILE__).'/models/database.php');
		
		$db = new RE_Database;
		$db->install ();
	}
	
	function status_header ($status)
	{
		// Fix for incorrect headers sent when using FastCGI
		if (substr (php_sapi_name (), 0, 3) == 'cgi')
			return str_replace ('HTTP/1.1', 'Status:', $status);
		return $status;
	}
	
	function admin_head ()
	{
		$this->render_admin ('head', array ('type' => $_GET['sub'] == '' ? '301' : $_GET['sub']));
	}
	
	function admin_menu ()
	{
  	add_management_page (__ ("Redirection", 'redirection'), __ ("Redirection", 'redirection'), "edit_plugins", basename (__FILE__), array (&$this, "admin_screen"));
	}

	function admin_screen_options ()
	{
		if (isset ($_POST['update']))
		{
			update_option ('redirection_root', $_POST['root']);
			if (isset ($_POST['index']))
				update_option ('redirection_index', 'true');
			else
				update_option ('redirection_index', 'false');
				
			if (isset ($_POST['post_change']))
				update_option ('redirection_post', 'true');
			else
				update_option ('redirection_post', 'false');
				
			if (isset ($_POST['redirection_updates']))
				update_option ('redirection_updates', 'true');
			else
				update_option ('redirection_updates', 'false');
				
			if (isset ($_POST['redirection_404_log']))
				update_option ('redirection_404_log', 'true');
			else
				update_option ('redirection_404_log', 'false');
			
			update_option ('redirection_global_404', trim ($_POST['404_redirect']));
			update_option ('redirection_auto_target', trim ($_POST['redirection_auto_target']));
			update_option ('redirection_lookup', $_POST['lookup']);
			$this->render_message ('The options were updated');
		}
		else if (isset ($_POST['delete']))
		{
			include (dirname (__FILE__).'/models/database.php');

			$db = new RE_Database;
			$db->remove (__FILE__);
			
			$this->render_message ('Redirection data has been deleted and the plugin disabled');
		}

		$this->render_admin ('options');
	}
	
	function update ()
	{
		$version = get_option ('redirection_version');
		if ($version != '1.9')
		{
			include (dirname (__FILE__).'/models/database.php');

			$db = new RE_Database;
			$db->upgrade ($version, DRAINHOLE_VERSION);
		}
	}

	function admin_screen ()
	{
	  $this->update ();
	  
	  $url = get_bloginfo ('wpurl').'/wp-admin/edit.php?page=redirection.php';
	
		$this->render_admin ('submenu', array ('url' => $url));
		
		// Display version update message
		$update = get_option ('redirection_update');
		if ($update == 'true' || $update === false)
		{
			$version = $this->version_update ('http://urbangiraffe.com/category/software/wordpress/redirection/feed/');
			if ($version && count ($version->items) > 0)
				$this->render_admin ('version', array ('rss' => $version));
		}
		
		if ($_GET['sub'] == 'log')
			return $this->admin_screen_log ();
		else if ($_GET['sub'] == '404')
			return $this->admin_screen_404 ();
	  else if ($_GET['sub'] == 'options')
	    return $this->admin_screen_options ();
	  else if ($_GET['sub'] == 'process')
	    return $this->admin_screen_process ();
		else if ($_GET['sub'] == 'import')
			return $this->admin_screen_import ();
	  else
			return $this->admin_redirection ();
	}
	
	function admin_screen_import ()
	{
		$this->render_admin ('import');
	}
	
	function admin_screen_log ()
	{
		if (isset ($_POST['deleteall']))
		{
			RE_Log::delete_all ();
			$this->render_message (__ ('Your logs have been deleted', 'redirection'));
		}
			
		$pager = new RE_Pager ($_GET, $_SERVER['REQUEST_URI'], 'created', 'DESC', 'log');
		$logs  = RE_Log::get ($pager);
		
		$this->render_admin ('log', array ('logs' => $logs, 'pager' => $pager));
	}

	function admin_screen_404 ()
	{
		if (isset ($_POST['delete']))
		{
			RE_Log::delete_404 ();
			$this->render_message ("404 errors have been deleted");
		}
		
		$pager = new RE_Pager ($_GET, $_SERVER['REQUEST_URI'], 'created', 'DESC', '404');
		$logs  = RE_Log::get_404 ($pager);
		
		$this->render_admin ('404', array ('logs' => $logs, 'pager' => $pager, 'redirectors' => $this->redirectors));
	}
	
	function admin_redirection ()
	{
		$pager = new RE_Pager ($_GET, $_SERVER['REQUEST_URI'], 'position', 'ASC');
		$items = Redirection_Item::get ($pager);

  	$this->render_admin ('redirections', array ('items' => $items, 'redirectors' => $this->redirectors, 'date_format' => get_option ('date_format'), 'pager' => $pager));
	}
	
	
	function send_headers ($obj)
	{
		if ($this->matched->type == '410')
			status_header (410);
	}
	
	function check_404 ()
	{
		// // First check for www/no-www
		if (get_option ('redirection_root') == 'www' && strpos ($_SERVER['HTTP_HOST'], 'www.') === false && strpos ($_SERVER['HTTP_HOST'], 'local.') === false)
		{
			// Make sure we have www at the start
			wp_redirect ('http://www.'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		}
		else if (get_option ('redirection_root') == 'nowww' && strpos ($_SERVER['HTTP_HOST'], 'www.') !== false && strpos ($_SERVER['HTTP_HOST'], 'local.') === false)
		{
			// Remove any www from the URL
			wp_redirect ('http://'.str_replace ('www.', '', $_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI']);
		}
		
		// And now index.php|htm|html
		if (get_option ('redirection_index') == 'true' && preg_match ('/index\.(htm?|php)/', $_SERVER['REQUEST_URI'], $matches) > 0)
		{
			$url = preg_replace ('/index\.(html|htm|php)/', '', $_SERVER['REQUEST_URI']);
			if ($url != $_SERVER['REQUEST_URI'])
				wp_redirect ($url, 301);
		}

		// Is it a redirection 404?
		if ($this->matched && ($this->matched->type == '404' || $this->matched->type == '410'))
		{
			global $wp_query;
			$wp_query->is_404 = true;
		}
		else if (is_404 ())
		{
			if (isset ($_SERVER['REMOTE_ADDR']))
			  $ip = $_SERVER['REMOTE_ADDR'];
			else if (isset ($_SERVER['HTTP_X_FORWARDED_FOR']))
			  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			
			$makelog = get_option ('redirection_404_log');
			if ($makelog === false || $makelog == 'true')
				$log = RE_Log::create ($_SERVER['REQUEST_URI'], '', $_SERVER['HTTP_USER_AGENT'], $ip, $_SERVER['HTTP_REFERER']);
				
			if (strlen (get_option ('redirection_global_404')) > 0)
				wp_redirect (get_option ('redirection_global_404'), 301);
		}
	}
	
	function permalink_redirect_skip ($skip)
	{
		// only want this if we:ve matched using redirection
		if ($this->matched)
			$skip[] = $_SERVER['REQUEST_URI'];
		return $skip;
	}
	
	function plugins_loaded ()
	{
		$this->update ();
		
		$url  = $_SERVER['REQUEST_URI'];
		$part = explode ('?', $url);
		
		// Make sure someone doesnt accidentaly redirect the plugin!
		if ($part[0] == str_replace (get_bloginfo ('home'), '', $this->url ()).'/ajax.php')
			return;

		$item = apply_filters ('redirection_first', $url);
		if (is_a ($item, 'Redirection_Item'))
		{
			$this->matched = $item;
			$item->redirect ($url);
		}
		
		$redirects = Redirection_Item::get_by_position ($url);
		if (!empty ($redirects))
		{
			foreach ($redirects AS $key => $item)
			{
				if ($item->matches ($url))
				{
					$this->matched = $item;
					break;
				}
			}
		}

		$item = apply_filters ('redirection_last', $url);
		if (is_a ($item, 'Redirection_Item'))
		{
			$this->matched = $item;
			$item->redirect ($url);
		}
	}
	
	function insert_old_slug ()
	{
		global $post;
	?>
	<input type="hidden" name="redirection_slug" value="<?php the_permalink () ?>"/>
	<input type="hidden" name="redirection_status" value="<?php echo $post->post_status ?>"/>
	<?php
	}
	
	function post_changed ($id)
	{
		$text = __ngettext ('%d window', '%d windows', 3, 'domain');
		
		$post    = get_post ($id);
		$newslug = get_permalink ($id);
		$oldslug = $_POST['redirection_slug'];
		$base    = get_option ('home');

		if ($newslug != $oldslug && strlen ($oldslug) > 0 && ($post->post_status == 'publish' || $post->post_status == 'static') && $_POST['redirection_status'] != 'draft')
		{	
			// Remove everything before the home URL
			$pos = strpos ($newslug, $base);
			if ($pos !== false)
				$newslug = substr ($newslug, $pos + strlen ($base));
			
			$pos = strpos ($_POST['redirection_slug'], $base);
			if ($pos !== false)
				$oldslug = substr ($oldslug, $pos + strlen ($base));

			Redirection_Item::create (array ('old' => $oldslug, 'type' => 301, 'new' => $newslug, 'redirector' => 'A_Redirector_URL'));
		}
	}
}

// Instantiate the plugin
$redirection = new Redirection;
?>