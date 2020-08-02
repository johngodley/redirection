/**
 * External dependencies
 */

import React, { useEffect, useState } from 'react';
import classnames from 'classnames';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import './style.scss';

const SHRINK_TIME = 5000;
let timer = false;

/**
 * A notice
 *
 * @param {object} props - component props
 * @param {string[]} props.notices - Notices
 */
function SnackbarNotice( { notices } ) {
	return <>{ notices[ notices.length - 1 ] + ( notices.length > 1 ? ' (' + notices.length + ')' : '' ) }</>;
}

/**
 * Display a snackbar notice
 *
 * @param {object} props - Component props
 * @param {string[]} props.notices - Notices
 * @param {} props.onClear - Clear notices
 */
function Snackbar( props ) {
	const { notices, onClear } = props;
	const [ shrunk, setShrunk ] = useState( false );

	useEffect(() => {
		if ( notices.length > 0 ) {
			clearTimeout( timer );
			timer = setTimeout( () => setShrunk( true ), SHRINK_TIME );
		}

		return () => {
			clearTimeout( timer );
		};
	}, [ notices ]);

	if ( notices.length === 0 ) {
		return null;
	}

	function onClick() {
		if ( shrunk ) {
			setShrunk( false );
		} else {
			onClear();
		}
	}

	const classes = classnames( 'notice', 'notice-info', 'wpl-notice', shrunk && 'wpl-notice_shrunk' );
	return (
		<div className={ classes } onClick={ onClick }>
			<div className="closer">
				<span className="dashicons dashicons-yes" />
			</div>
			<p>
				{ shrunk ? (
					<span className="dashicons dashicons-warning" title={ __( 'View notice' ) } />
				) : (
					<SnackbarNotice notices={ notices } />
				) }
			</p>
		</div>
	);
}

export default Snackbar;
