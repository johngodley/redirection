<?php
/**
 * Redirection
 *
 * @package Redirection
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */
class Redirector
{
	var $url;
	
	function initialize ($url)
	{
		$this->url = $url;
	}
	
	function redirect_to ($type, $url)
	{
		if ($type == 'pass')
		{
			// Determine what we are passing to:  local URL, remote URL, file
			if (substr ($url, 0, 7) == 'http://' || substr ($url, 0, 8) == 'https://')
				echo @wp_remote_fopen ($url);
			else if (substr ($url, 0, 7) == 'file://')
			{
				$parts = explode ('?', substr ($url, 7));
				if (count ($parts) > 1)
				{
					// Put parameters into the environment
					$args = explode ('&', $parts[1]);
					if (count ($args) > 0)
					{
						foreach ($args AS $arg)
						{
							$tmp = explode ('=', $arg);
							if (count ($tmp) == 1)
								$_GET[$arg] = '';
							else
								$_GET[$tmp[0]] = $tmp[1];
						}
					}
				}

				include ($parts[0]);
			}
			else
			{
				$_SERVER['REQUEST_URI'] = $url;
				return false;
			}
		}
		else if ($type == '404' || $type == '410')
			return false;
		else if ($url != $_SERVER['REQUEST_URI'])     // Final check that we don't loop
			wp_redirect ($url, $type);
		return true;
	}
	
	function save ($details) {}
	function name () { return ''; }
	function wants_it () { return true; }
}

class Redirector_Factory
{
	var $available;
	
	function Redirector_Factory ()
	{
		$files = glob (dirname (__FILE__).'/../redirectors/*.php');
		if (count ($files) > 0)
		{
			foreach ($files AS $file)
			{
				if (is_dir ($file))
					@include_once (dirname ($file).'/'.basename ($file).'.php');
				else
					@include_once ($file);
			}
		}
		
		if (count ($this->available) > 0)
			ksort ($this->available);
	}
	
	function register ($class)
	{
		$obj = new $class;
		$this->available[$class] = $obj->name ();
	}
}

?>