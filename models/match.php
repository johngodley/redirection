<?php
/**
 * Redirection
 *
 * @package Redirection
 * @author John Godley
 * @copyright Copyright( C ) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages( including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption ) however caused and on any theory of liability, whether in
contract, strict liability, or tort( including negligence or otherwise ) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

class Red_Match {
	var $url;

	function Red_Match( $values = '' ) {
		if ( $values ) {
			$this->url = $values;

			$obj = maybe_unserialize( $values );

			if ( is_array( $obj ) ) {
				foreach ( $obj AS $key => $value ) {
					$this->$key = $value;
				}
			}
		}
	}

	function data( $details ) {
		$data = $this->save( $details );
		if ( count( $data ) == 1 && !is_array( current( $data ) ) )
			$data = current( $data );
		else
			$data = serialize( $data );
		return $data;
	}

	function save( $details ) {
		return array();
	}

	function name() {
		return '';
	}

	function show() {
	}

	function wants_it() {
		return true;
	}

	function get_target( $url, $matched_url, $regex ) {
		return false;
	}

	static function create( $name, $data = '' ) {
		$avail = self::available();
		if ( isset( $avail[strtolower( $name )] ) ) {
			$classname = $name.'_match';

			if ( !class_exists( strtolower( $classname ) ) )
				include( dirname( __FILE__ ).'/../matches/'.$avail[strtolower( $name )] );
			return new $classname( $data );
		}

		return false;
	}

	static function all() {
		$data = array();

		$avail = self::available();
		foreach ( $avail AS $name => $file ) {
			$obj = self::create( $name );
			$data[$name] = $obj->name();
		}

		return $data;
	}

	static function available() {
	 	return array (
			'url'      => 'url.php',
			'referrer' => 'referrer.php',
			'agent'    => 'user_agent.php',
			'login'    => 'login.php',
		 );
	}

	function match_name() {
		return '';
	}
}
