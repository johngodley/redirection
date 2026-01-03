import { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { FormTable } from 'component/form-table';
import { Button } from '@wp-plugin-components';
import LogOptions from './log-options';
import OtherOptions from './other-options';
import UrlOptions from './url-options';
import './style.scss';
import { useSettingsUpdate } from 'lib/api/hooks/use-settings';
import { useSettingsStore, useGroupStore } from 'stores';

interface GroupOption {
	value: number | Array< { value: number } >;
	label: string;
}

interface PostTypes {
	[ key: string ]: string;
}

interface SettingsValues {
	[ key: string ]: any;
	support?: any;
}

function supportLink( rel: string, anchor?: string ): string {
	return 'https://redirection.me/support/' + rel + ( anchor ? '/#' + anchor : '' );
}

// Empty object constant to avoid creating new object on every render
const EMPTY_POST_TYPES: PostTypes = {};
const EMPTY_SETTINGS: SettingsValues = {};

function OptionsForm() {
	// Direct property access instead of destructuring
	const values = useSettingsStore( ( state ) => state.values );
	const saveStatus = useSettingsStore( ( state ) => state.saveStatus );
	const installed = useSettingsStore( ( state ) => state.values?.installed ?? '' );
	const warning = useSettingsStore( ( state ) => state.values?.warning ?? '' );
	const settingsPostTypes = useSettingsStore( ( state ) => state.values?.postTypes );
	const groups = useGroupStore( ( state ) => state.rows ) as unknown as GroupOption[];
	const { mutate: updateSettings } = useSettingsUpdate();

	const [ settings, setSettings ] = useState( values ?? EMPTY_SETTINGS );
	const postTypes = settingsPostTypes ?? EMPTY_POST_TYPES;

	function onSubmit( ev: React.FormEvent ) {
		ev.preventDefault();
		updateSettings( settings );
	}

	function onChange( ev: React.ChangeEvent< HTMLInputElement | HTMLTextAreaElement > | { [ key: string ]: any } ) {
		if ( 'target' in ev ) {
			const { target } = ev;
			const value = target.type === 'checkbox' ? ( target as HTMLInputElement ).checked : target.value;

			setSettings( { ...settings, [ target.name ]: value } );
		} else {
			setSettings( { ...settings, ...ev } );
		}
	}

	useEffect( () => {
		if ( values ) {
			setSettings( values );
		}
	}, [ values ] );

	return (
		<form onSubmit={ onSubmit }>
			<FormTable>
				<LogOptions settings={ settings as any } onChange={ onChange } getLink={ supportLink } />
				<UrlOptions
					settings={ settings as any }
					onChange={ onChange }
					getLink={ supportLink }
					groups={ groups }
					postTypes={ postTypes }
				/>

				<OtherOptions
					settings={ settings as any }
					onChange={ onChange }
					getLink={ supportLink }
					installed={ installed }
					warning={ warning }
				/>
			</FormTable>

			<Button isPrimary isSubmit disabled={ saveStatus }>
				{ __( 'Update', 'redirection' ) }
			</Button>
		</form>
	);
}

export default OptionsForm;
