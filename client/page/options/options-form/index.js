/**
 * External dependencies
 */

import { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { saveSettings } from 'state/settings/action';
import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { FormTable } from 'component/form-table';
import { Button } from 'wp-plugin-components';
import LogOptions from './log-options';
import OtherOptions from './other-options';
import UrlOptions from './url-options';
import './style.scss';

function supportLink( rel, anchor ) {
	return 'https://redirection.me/support/' + rel + ( anchor ? '/#' + anchor : '' );
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
	useEffect( () => {
		setSettings( values );
	}, [ values ] );

	return (
		<form onSubmit={ onSubmit }>
			<FormTable>
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
				{ __( 'Update', 'redirection' ) }
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
