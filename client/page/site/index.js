/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import { loadSettings, saveSettings } from 'state/settings/action';
import { STATUS_IN_PROGRESS } from 'state/settings/type';
import Placeholder from 'component/placeholder';
import ExternalLink from 'wp-plugin-components/external-link';
import SiteAliases from './aliases';
import RelocateSite from './relocate';
import CanonicalSettings from './canonical';
import HttpHeaders from './headers';
import { getDomainOnly, getDomainAndPathOnly } from 'lib/url';
import './style.scss';

class Site extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoadSettings();

		const { headers = [], relocate = '', preferred_domain = '', https = false, aliases = [] } = props.values ? props.values : {};

		this.state = {
			https,
			preferred_domain,
			headers,
			relocate,
			aliases,
		};
	}

	onSubmit = ev => {
		const { https, headers, preferred_domain, aliases, relocate } = this.state;

		ev.preventDefault();
		this.props.onSaveSettings( {
			https,
			headers,
			preferred_domain,
			aliases: aliases.filter( item => item ).map( getDomainOnly ),
			relocate: getDomainAndPathOnly( relocate )
		} );
	}

	onChange = settings => {
		this.setState( { ...this.state, ...settings } );
	}

	render() {
		const { loadStatus, values, saveStatus, siteDomain } = this.props;
		const { headers, relocate, aliases, https, preferred_domain } = this.state;

		if ( loadStatus === STATUS_IN_PROGRESS || ! values ) {
			return <Placeholder />;
		}

		return (
			<form onSubmit={ this.onSubmit }>
				<div className="inline-notice inline-warning">
					<p>{ __( 'Options on this page can cause problems if used incorrectly. You can {{link}}temporarily disable them{{/link}} to make changes.', {
						components: {
							link: <ExternalLink url="https://redirection.me/support/disable-redirection/" />,
						},
					} ) }</p>
				</div>

				<RelocateSite relocate={ relocate } siteDomain={ siteDomain } onChange={ this.onChange } />
				{ relocate.length === 0 && <SiteAliases aliases={ aliases } siteDomain={ siteDomain } onChange={ this.onChange } /> }
				{ relocate.length === 0 && <CanonicalSettings https={ https } siteDomain={ siteDomain } preferredDomain={ preferred_domain } onChange={ this.onChange } /> }

				<HttpHeaders headers={ headers } onChange={ this.onChange } />

				<input className="button-primary" type="submit" name="update" value={ __( 'Update' ) } disabled={ saveStatus === STATUS_IN_PROGRESS } />
			</form>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadSettings: () => {
			dispatch( loadSettings() );
		},
		onSaveSettings: settings => {
			dispatch( saveSettings( settings ) );
		},
	};
}

function mapStateToProps( state ) {
	const { loadStatus, saveStatus, values } = state.settings;
	const siteDomain = getDomainOnly( Redirectioni10n.pluginRoot );

	return {
		loadStatus,
		saveStatus,
		values,
		siteDomain,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Site );
