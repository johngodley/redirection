<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap supporter">
	<h2><?php _e ('Redirection Support', 'redirection'); ?></h2>

	<?php $this->render( 'submenu', array( 'options' => $options ) ); ?>

	<div id="react-ui">
		<noscript>Please enable JavaScript</noscript>
	</div>
</div>

<script>
	addLoadEvent( function() {
		redirection.show( 'react-ui' );		
	} );
</script>
