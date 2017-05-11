<?php if ( ! defined( 'ABSPATH' ) ) {
	die ( 'No direct access allowed' );
} ?>
<div id="<?php echo $id; ?>" class="activity-block">
	<h3><?php echo $title; ?></h3>
	<?php
	if ( empty( $rows ) ) {
		echo '<div class="no-activity">';
		echo '<p class="smiley" aria-hidden="true"></p>';
		echo '<p>' . __( 'No recent activity!', 'redirection' ) . '</p>';
		echo '</div>';
	} else {
		echo '<ul>';
		printf(
			'<li><span class="last-count header">%1$s</span> <span class="url header">%2$s</span></li>',
			__( 'Count', 'redirection' ),
			__( 'URL', 'redirection' )
		);
		foreach ( $rows as $row ) {
			printf(
				'<li><span class="last-count">%1$s</span> <span class="url">%2$s</span></li>',
				number_format( $row->last_count, 0, '', _x( ',', 'Thousands separator in widget counts', 'redirection' ) ),
				$row->url
			);
		}
		echo '</ul>';
	}
	if ( ! empty( $link ) ) {
		echo '<a href="' . $link . '">' . __( 'View All', 'redirection' ) . '</a>';
	}
	?>
</div>
