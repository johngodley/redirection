/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { loadSettings, deletePlugin } from 'state/settings/action';
import { STATUS_IN_PROGRESS, STATUS_COMPLETE } from 'state/settings/type';
import OptionsForm from './options-form';
import DeletePlugin from 'component/options/delete-plugin';
import Importer from 'component/options/importer';
import Placeholder from 'component/wordpress/placeholder';

class Options extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoadSettings();
	}

	render() {
		const { loadStatus } = this.props;

		if ( loadStatus === STATUS_IN_PROGRESS ) {
			return <Placeholder />;
		}

		return (
			<div>
				{ loadStatus === STATUS_COMPLETE && <OptionsForm /> }
				{ loadStatus === STATUS_COMPLETE && <Importer /> }
				<DeletePlugin onDelete={ this.props.onDeletePlugin } />
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadSettings: () => {
			dispatch( loadSettings() );
		},
		onDeletePlugin: () => {
			dispatch( deletePlugin() );
		}
	};
}

function mapStateToProps( state ) {
	const { loadStatus } = state.settings;

	return {
		loadStatus,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Options );
