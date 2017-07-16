/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import { clearNotices } from 'state/message/action';

const SHRINK_TIME = 5000;

class Notice extends React.Component {
	constructor( props ) {
		super( props );

		this.handleClick = this.onClick.bind( this );
		this.handleShrink = this.onShrink.bind( this );
		this.state = { shrunk: false, width: 'auto' };
	}

	onClick() {
		if ( this.state.shrunk ) {
			this.setState( { shrunk: false } );
		} else {
			this.props.onClear();
		}
	}

	componentWillUpdate( nextProps ) {
		if ( this.props.notices !== nextProps.notices ) {
			this.stopTimer();
			this.setState( { shrunk: false } );
			this.startTimer();
		}
	}

	componentWillUnmount() {
		this.stopTimer();
	}

	stopTimer() {
		clearTimeout( this.timer );
	}

	startTimer() {
		this.timer = setTimeout( this.handleShrink, SHRINK_TIME );
	}

	onShrink() {
		this.setState( { shrunk: true } );
	}

	renderNotice( notices ) {
		const klasses = 'notice notice-info redirection-notice' + ( this.state.shrunk ? ' notice-shrunk' : '' );

		return (
			<div className={ klasses } onClick={ this.handleClick }>
				<div className="closer">&#10004;</div>
				<p>{ this.state.shrunk ? <span title={ __( 'View notice' ) }>🔔</span> : notices[ notices.length - 1 ] }</p>
			</div>
		);
	}

	render() {
		const { notices } = this.props;

		if ( notices.length === 0 ) {
			return null;
		}

		return this.renderNotice( notices );
	}
}

function mapStateToProps( state ) {
	const { notices } = state.message;

	return {
		notices,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onClear: () => {
			dispatch( clearNotices() );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( Notice );
