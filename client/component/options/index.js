/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { loadSettings, deletePlugin } from 'state/settings/action';
import { STATUS_IN_PROGRESS, STATUS_COMPLETE } from 'state/settings/type';
import Spinner from 'component/wordpress/spinner';
import OptionsForm from './options-form';
import DeletePlugin from 'component/options/delete-plugin';
import Importer from 'component/options/importer';

class Options extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoadSettings();
	}

	render() {
		const { loadStatus } = this.props;

		if ( loadStatus === STATUS_IN_PROGRESS ) {
			return <Spinner />;
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
