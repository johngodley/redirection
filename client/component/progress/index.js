/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import Spinner from 'component/wordpress/spinner';

class Progress extends React.Component {
	constructor( props ) {
		super( props );
	}

	getMessage( inProgress ) {
		if ( inProgress > 1 ) {
			return __( 'Saving...' ) + ' (' + inProgress + ')';
		}

		return __( 'Saving...' );
	}

	renderProgress( inProgress ) {
		const klasses = 'notice notice-progress redirection-notice';

		return (
			<div className={ klasses }>
				<Spinner />
				<p>{ this.getMessage( inProgress ) }</p>
			</div>
		);
	}

	render() {
		const { inProgress } = this.props;

		if ( inProgress === 0 ) {
			return null;
		}

		return this.renderProgress( inProgress );
	}
}

function mapStateToProps( state ) {
	const { inProgress } = state.message;

	return {
		inProgress,
	};
}

export default connect(
	mapStateToProps,
	null,
)( Progress );
