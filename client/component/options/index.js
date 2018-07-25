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
import Placeholder from 'component/wordpress/placeholder';
import Donation from './donation';

class Options extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoadSettings();
	}

	render() {
		const { loadStatus, values, canDelete = false } = this.props;

		if ( loadStatus === STATUS_IN_PROGRESS || ! values ) {
			return <Placeholder />;
		}

		return (
			<div>
				{ loadStatus === STATUS_COMPLETE && <Donation support={ values.support } /> }
				{ loadStatus === STATUS_COMPLETE && <OptionsForm /> }

				<br /><br /><hr />
				{ canDelete && <DeletePlugin onDelete={ this.props.onDeletePlugin } /> }
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
		},
	};
}

function mapStateToProps( state ) {
	const { loadStatus, values, canDelete } = state.settings;

	return {
		loadStatus,
		values,
		canDelete,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Options );
