<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>

<ul class="subsubsub">
	<li>
		<a <?php if ( !isset( $_GET['sub'] ) || $_GET['sub'] == '' ) echo 'class="current"'; ?> href="<?php echo admin_url( 'tools.php?page=redirection.php' ); ?><?php if ( isset( $_GET['id'] ) ) echo '&amp;id='.intval( $_GET['id'] ) ?>">
			<?php _e( 'Redirects', 'redirection' ); ?>
		</a> |
	</li>
	<li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'groups' ) echo 'class="current"'; ?> href="<?php echo admin_url( 'tools.php?page=redirection.php' ); ?>&amp;sub=groups<?php if ( isset( $_GET['id'] ) ) echo '&amp;id='.intval( $_GET['id'] ) ?>">
			<?php _e( 'Groups', 'redirection' ); ?>
		</a> |
	</li>
	<li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'modules' ) echo 'class="current"'; ?> href="<?php echo admin_url( 'tools.php?page=redirection.php' ); ?>&amp;sub=modules">
			<?php _e( 'Modules', 'redirection' ); ?>
		</a> |
	</li>

	<?php if ( isset( $options['expire_redirect'] ) && $options['expire_redirect'] >= 0 ) : ?>
	<li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'log' ) echo 'class="current"'; ?> href="<?php echo admin_url( 'tools.php?page=redirection.php' ); ?>&amp;sub=log">
			<?php _e( 'Log', 'redirection' ); ?>
		</a> |
	</li>
	<?php endif; ?>

	<?php if ( isset( $options['expire_404'] ) && $options['expire_404'] >= 0 ) : ?>
	<li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == '404s' ) echo 'class="current"'; ?> href="<?php echo admin_url( 'tools.php?page=redirection.php' ); ?>&amp;sub=404s">
			<?php if ( isset( $_GET['ip'] ) ) : ?>
				<?php printf( __( '404s from %s', 'redirection' ), long2ip( ip2long( $_GET['ip'] ) ) ); ?>
			<?php else : ?>
				<?php _e( '404s', 'redirection' ); ?>
			<?php endif; ?>
		</a> |
	</li>
	<?php endif; ?>

	<li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'options' ) echo 'class="current"'; ?> href="<?php echo admin_url( 'tools.php?page=redirection.php' ); ?>&amp;sub=options">
			<?php _e( 'Options', 'redirection' ); ?>
		</a> |
	</li>
	<li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'support' ) echo 'class="current"'; ?> href="<?php echo admin_url( 'tools.php?page=redirection.php' ); ?>&amp;sub=support">
			<?php _e( 'Support', 'redirection' ); ?>
		</a>
	</li>
</ul>

