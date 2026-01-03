import { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import Placeholder from '@wp-plugin-components/placeholder';
import { ExternalLink, createInterpolateElement } from '@wp-plugin-components';
import SiteAliases from './aliases';
import RelocateSite from './relocate';
import CanonicalSettings from './canonical';
import HttpHeaders from './headers';
import { getDomainOnly, getDomainAndPathOnly } from 'lib/url';
import { useSettings, useSettingsUpdate } from 'lib/api/hooks';
import { useSettingsStore } from 'stores';
import './style.scss';
import PermalinkSettings from './permalink';

interface SiteSettings {
	headers?: any[];
	relocate?: string;
	preferred_domain?: string;
	https?: boolean;
	aliases?: string[];
	permalinks?: string[];
}

export default function Site() {
	const siteDomain = getDomainOnly( Redirectioni10n.pluginRoot );
	// Direct property access instead of destructuring
	const loadStatus = useSettingsStore( ( state ) => state.loadStatus );
	const values = useSettingsStore( ( state ) => state.values );
	const saveStatus = useSettingsStore( ( state ) => state.saveStatus );

	useSettings();
	const { mutate: updateSettings } = useSettingsUpdate();
	const [ https, setHttps ] = useState< boolean >( false );
	const [ preferredDomain, setPreferredDomain ] = useState< string >( '' );
	const [ headers, setHeaders ] = useState< any[] >( [] );
	const [ relocate, setRelocate ] = useState< string >( '' );
	const [ aliases, setAliases ] = useState< string[] >( [] );
	const [ permalinks, setPermalinks ] = useState< string[] >( [] );

	useEffect( () => {
		if ( values ) {
			setHttps( values.https || false );
			setPreferredDomain( ( values.preferred_domain || '' ) as string );
			setHeaders( values.headers || [] );
			setRelocate( ( values.relocate || '' ) as string );
			setAliases( ( values.aliases || [] ) as string[] );
			setPermalinks( ( values.permalinks || [] ) as string[] );
		}
	}, [ values ] );

	const onSubmit = ( ev: React.FormEvent ) => {
		ev.preventDefault();
		updateSettings( {
			https,
			headers,
			preferred_domain: preferredDomain,
			aliases: aliases.filter( ( item ) => item ).map( getDomainOnly ),
			relocate: getDomainAndPathOnly( relocate ),
			permalinks,
		} );
	};

	const onChange = ( settings: Partial< SiteSettings > ) => {
		if ( settings.https !== undefined ) {
			setHttps( settings.https );
		}
		if ( settings.preferred_domain !== undefined ) {
			setPreferredDomain( settings.preferred_domain );
		}
		if ( settings.headers !== undefined ) {
			setHeaders( settings.headers );
		}
		if ( settings.relocate !== undefined ) {
			setRelocate( settings.relocate );
		}
		if ( settings.aliases !== undefined ) {
			setAliases( settings.aliases );
		}
		if ( settings.permalinks !== undefined ) {
			setPermalinks( settings.permalinks );
		}
	};

	if ( loadStatus === 'loading' || ! values ) {
		return <Placeholder />;
	}

	return (
		<form onSubmit={ onSubmit }>
			<div className="inline-notice inline-warning">
				<p>
					{ createInterpolateElement(
						__(
							'Options on this page can cause problems if used incorrectly. You can {{link}}temporarily disable them{{/link}} to make changes.',
							'redirection'
						),
						{
							link: <ExternalLink url="https://redirection.me/support/disable-redirection/" />,
						}
					) }
				</p>
			</div>

			<RelocateSite relocate={ relocate } siteDomain={ siteDomain as string } onChange={ onChange } />
			{ relocate.length === 0 && (
				<SiteAliases aliases={ aliases } siteDomain={ siteDomain as string } onChange={ onChange } />
			) }
			{ relocate.length === 0 && (
				<CanonicalSettings
					https={ https }
					siteDomain={ siteDomain as string }
					preferredDomain={ preferredDomain }
					onChange={ onChange }
				/>
			) }
			{ relocate.length === 0 && <PermalinkSettings permalinks={ permalinks } onChange={ onChange } /> }

			<HttpHeaders headers={ headers } onChange={ onChange } />

			<input
				className="button-primary"
				type="submit"
				name="update"
				value={ __( 'Update', 'redirection' ) }
				disabled={ saveStatus }
			/>
		</form>
	);
}
