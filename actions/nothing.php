<?php

class Nothing_Action extends Red_Action {
	public function process_before( $code, $target ) {
		return false;
	}
}
