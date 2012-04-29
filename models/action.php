<?php

class Red_Action
{
	function Red_Action ($values)
	{
		if (is_array ($values))
		{
			foreach ($values AS $key => $value)
			 	$this->$key = $value;
		}
	}

	function can_change_code () { return false;}

	function config () { }

	function create ($name, $code)
	{
		$avail = Red_Action::available ();
		if (isset ($avail[$name]))
		{
			if (!class_exists (strtolower ($avail[$name][1])))
				include (dirname (__FILE__).'/../actions/'.$avail[$name][0]);

			$obj = new $avail[$name][1] (array ('action_code' => $code));
			$obj->type = $name;
			return $obj;
		}

		return false;
	}

	function available ()
	{
	 	return array
		(
			'url'     => array ('url.php',     'Url_Action'),
			'error'   => array ('error.php',   'Error_Action'),
			'nothing' => array ('nothing.php', 'Nothing_Action'),
			'random'  => array ('random.php',  'Random_Action'),
			'pass'    => array ('pass.php',    'Pass_Action'),
		);
	}

	function type ()
	{
		return $this->type;
	}

	function process_before ($code, $target) { return true; }
	function process_after ($code, $target) { return true; }
	function can_perform_action () { return true; }
	function action_codes () { return array ();}

	function display_actions ()
	{
		foreach ($this->action_codes () AS $key => $code)
			echo '<option value="'.$key.'"'.(($key == $this->action_code) ? ' selected="selected"' : '').'>'.sprintf ('%s - %s', $key, $code).'</option>';
	}

}
?>
