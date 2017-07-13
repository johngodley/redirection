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
import Donation from './donation';
import { loadSettings } from 'state/settings/action';

class Support extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoadSettings();
	}

	render() {
		const { support = false, newsletter = false } = this.props.values ? this.props.values : {};

		return (
			<div style={ { paddingTop: '5px' } }>
				<Donation support={ support } />
				<Newsletter newsletter={ newsletter } />
				<Faq />
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
