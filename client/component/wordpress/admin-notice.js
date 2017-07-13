/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

class AdminNotice extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { visible: true };
		this.onClick = this.dismiss.bind( this );
		window.scrollTo( 0, 0 );
	}

	dismiss() {
		this.setState( { visible: false } );
	}

	render() {
		const { message, isError = false } = this.props;
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

AdminNotice.propTypes = {
	message: PropTypes.string.isRequired,
	isError: PropTypes.bool,
};

export default AdminNotice;
