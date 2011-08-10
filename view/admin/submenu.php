<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>

<ul class="subsubsub">
  <li>
		<a <?php if ( !isset( $_GET['sub'] ) ) echo 'class="current"'; ?>href="?page=redirection.php<?php if ( isset( $_GET['id'] ) ) echo '&amp;id='.intval( $_GET['id'] ) ?>">
			<?php _e( 'Redirects', 'redirection' ); ?>
		</a> |
	</li>
  <li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'groups' ) echo 'class="current"'; ?>href="?page=redirection.php&amp;sub=groups<?php if ( isset( $_GET['id'] ) ) echo '&amp;id='.intval( $_GET['id'] ) ?>">
			<?php _e( 'Groups', 'redirection' ); ?>
		</a> |
	</li>
  <li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'modules' ) echo 'class="current"'; ?>href="?page=redirection.php&amp;sub=modules">
			<?php _e( 'Modules', 'redirection' ); ?>
		</a> |
	</li>
  <li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'log' ) echo 'class="current"'; ?>href="?page=redirection.php&amp;sub=log">
			<?php _e( 'Log', 'redirection' ); ?>
		</a> |
	</li>
  <li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'options' ) echo 'class="current"'; ?>href="?page=redirection.php&amp;sub=options">
			<?php _e( 'Options', 'redirection' ); ?>
		</a> |
	</li>
  <li>
		<a <?php if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'support' ) echo 'class="current"'; ?>href="?page=redirection.php&amp;sub=support">
			<?php _e( 'Support', 'redirection' ); ?>
		</a>
	</li>
</ul>

