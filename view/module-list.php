<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<h2><?php _e( 'Modules', 'redirection' ) ?></h2>

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
