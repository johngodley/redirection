/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import { clearNotices } from 'state/message/action';
import './style.scss';

const SHRINK_TIME = 5000;

class Notice extends React.Component {
	constructor( props ) {
		super( props );

		this.state = {
			shrunk: false,
			width: 'auto',
		};
	}

	onClick = () => {
		if ( this.state.shrunk ) {
			this.setState( { shrunk: false } );
		} else {
			this.props.onClear();
		}
	}

	getSnapshotBeforeUpdate( prevProps ) {
		if ( this.props.notices !== prevProps.notices ) {
			this.stopTimer();
			this.setState( { shrunk: false } );
			this.startTimer();
		}

		return null;
	}

	componentWillUnmount() {
		this.stopTimer();
	}

	stopTimer() {
		clearTimeout( this.timer );
	}

	startTimer() {
		this.timer = setTimeout( this.onShrink, SHRINK_TIME );
	}

	onShrink = () => {
		this.setState( { shrunk: true } );
	}

	getNotice( notices ) {
		if ( notices.length > 1 ) {
			return notices[ notices.length - 1 ] + ' (' + notices.length + ')';
		}

		return notices[ 0 ];
	}

	renderNotice( notices ) {
		const klasses = 'notice notice-info redirection-notice' + ( this.state.shrunk ? ' redirection-notice_shrunk' : '' );

		return (
			<div className={ klasses } onClick={ this.onClick }>
				<div className="closer">&#10004;</div>
				<p>{ this.state.shrunk ? <span title={ __( 'View notice' ) }>🔔</span> : this.getNotice( notices ) }</p>
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
