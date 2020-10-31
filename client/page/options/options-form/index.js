/**
 * External dependencies
 */

import React, { useEffect, useState } from 'react';
import { translate as __ } from 'i18n-calypso';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { saveSettings } from 'state/settings/action';
import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { FormTable, TableRow } from 'component/form-table';
import { Button } from 'wp-plugin-components';
import LogOptions from './log-options';
import OtherOptions from './other-options';
import UrlOptions from './url-options';

function supportLink( rel, anchor ) {
	return 'https://redirection.me/support/' + rel + ( anchor ? '#' + anchor : '' );
}

function OptionsForm( props ) {
	const { onSaveSettings, installed, warning, saveStatus, values, groups, postTypes } = props;
	const [ settings, setSettings ] = useState( values );
	const { support } = settings;

	function onSubmit( ev ) {
		ev.preventDefault();
		onSaveSettings( settings );
	}

	function onChange( ev ) {
		if ( ev.target ) {
			const { target } = ev;
			const value = target.type === 'checkbox' ? target.checked : target.value;

			setSettings( { ...settings, [ target.name ]: value } );
		} else {
			setSettings( { ...settings, ...ev } );
		}
	}

	// Update local settings if values change
	useEffect(() => {
		setSettings( values );
	}, [ values ]);

	return (
		<form onSubmit={ onSubmit }>
			<FormTable>
				<TableRow title="">
					<label>
						<input type="checkbox" checked={ support } name="support" onChange={ onChange } />
						<span className="sub">
							{ __( "I'm a nice person and I have helped support the author of this plugin" ) }
						</span>
					</label>
				</TableRow>

				<LogOptions settings={ settings } onChange={ onChange } getLink={ supportLink } />
				<UrlOptions settings={ settings } onChange={ onChange } getLink={ supportLink } groups={ groups } postTypes={ postTypes } />

				<OtherOptions
					settings={ settings }
					onChange={ onChange }
					getLink={ supportLink }
					installed={ installed }
					warning={ warning }
				/>
			</FormTable>

			<Button isPrimary isSubmit disabled={ saveStatus === STATUS_IN_PROGRESS }>
				{ __( 'Update' ) }
			</Button>
		</form>
	);
}

function mapDispatchToProps( dispatch ) {
	return {
		onSaveSettings: ( settings ) => {
			dispatch( saveSettings( settings ) );
		},
	};
}

function mapStateToProps( state ) {
	const { groups, values, saveStatus, installed, postTypes, warning } = state.settings;

	return {
		groups,
		values,
		saveStatus,
		installed,
		postTypes,
		warning,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( OptionsForm );
