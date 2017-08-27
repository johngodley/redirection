/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import Faq from './faq';
import Newsletter from './newsletter';
import Help from './help';
import Status from './status';
import { loadSettings } from 'state/settings/action';

class Support extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoadSettings();
	}

	render() {
		const { newsletter = false } = this.props.values ? this.props.values : {};

		return (
			<div>
				<Status />
				<Help />
				<Faq />
				<Newsletter newsletter={ newsletter } />
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadSettings: () => {
			dispatch( loadSettings() );
		},
	};
}

function mapStateToProps( state ) {
	const { values } = state.settings;

	return {
		values,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Support );
