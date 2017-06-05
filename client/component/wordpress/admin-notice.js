/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

class AdminNotice extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { visible: true };
		this.onClick = this.dismiss.bind( this );
	}

	dismiss() {
		this.setState( { visible: false } );
	}

	render() {
		const { message, isError } = this.props;
		const classes = classnames( {
			notice: true,
			'notice-error': isError,
			'notice-success': ! isError,
			'is-dismiss': true,
		} );

		if ( ! this.state.visible ) {
			return false;
		}

		return (
			<div className={ classes } onClick={ this.onClick }>
				<p>{ message }</p>
			</div>
		);
	}
}

export default AdminNotice;
