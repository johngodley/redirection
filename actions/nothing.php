<?php

class Nothing_Action extends Red_Action {
	public function process_before( $code, $target ) {
		return apply_filters( 'redirection_do_nothing', false, $target );
	}
}
