<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<h2><?php echo esc_html( $title ); ?></h2>

	<?php $this->render( 'submenu', array( 'options' => $options ) ); ?>

	<div id="react-ui">
		<noscript>Please enable JavaScript</noscript>
	</div>
</div>

<script>
	var Redirection_Adder, Redirection_Logger;

	addLoadEvent( function() {
		Redirection_Logger = new Redirection_Logs( jQuery, '#add', '#added', '#old' );
		Redirection_Adder = new Redirection_Add( jQuery, '#target', false );

		Redirection_Adder.setup( 'select[name=red_action]', '#add' );
		redirection.show( 'react-ui' );
	} );
</script>

<?php $this->render( 'add', array( 'hidden' => true ) ); ?>
