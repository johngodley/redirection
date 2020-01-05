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
import DropdownButton from 'component/dropdown-button';
import Header from './header';
import ExternalLink from 'component/external-link';
import './style.scss';

const getPresets = () => [
	{
		title: 'Add Header',
		name: 'header',
	},
	{
		title: 'Add Security Presets',
		name: 'security',
	},
	{
		title: 'Add CORS Presets',
		name: 'cors',
	},
];

const createHeader = ( headerName = 'X-Robots-Tag', headerValue = 'nofollow', headerSettings = {} ) => ( {
	location: 'site',
	type: headerName,
	headerName,
	headerValue,
	headerSettings,
} );

const createCorsHeaders = () => ( [
	createHeader( 'Access-Control-Allow-Origin', '*' ),
	createHeader( 'Access-Control-Allow-Credentials', 'true' ),
	createHeader( 'Access-Control-Allow-Methods', 'POST,GET,OPTIONS' ),
	createHeader( 'Access-Control-Allow-Headers', 'origin' ),
	createHeader( 'Referrer-Policy', 'no-referrer-when-downgrade' ),
	createHeader( 'P3P', 'CP="CAO PSA OUR"' ),
] );

const createSecurityHeaders = () => ( [
	createHeader( 'X-Frame-Options', 'deny' ),
	createHeader( 'X-XSS-Protection', '1; mode=block' ),
	createHeader( 'X-Content-Type-Options', 'nosniff' ),
	createHeader( 'Content-Security-Policy', "default-src 'self'; script-src 'unsafe-inline' 'unsafe-eval' http:; style-src 'unsafe-inline' http:; img-src http: data:; font-src http: data:; sandbox allow-forms allow-scripts" ),
	document.location.protocol === 'https' ? createHeader( 'Strict-Transport-Security', 'max-age: 31536000; includeSubDomains' ) : null,
	createHeader( 'Referrer-Policy', 'no-referrer-when-downgrade' ),
].filter( item => item ) );

class Site extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoadSettings();

		this.state = {
			https: props.values.https,
			headers: props.values.headers ? props.values.headers : [],
			preset: 'header',
		};
	}

	onHttps = ev => {
		this.setState( { https: ev.target.checked } );
	}

	onSubmit = ev => {
		const { https, headers } = this.state;
		ev.preventDefault();
		this.props.onSaveSettings( { https, headers } );
	}

	onChangeHeader = ( pos, attrs ) => {
		const headers = this.state.headers.slice();

		headers[ pos ] = attrs;
		this.setState( { headers } );
	}

	onChangePreset = preset => {
		this.setState( { preset } );
	}

	onPreset = () => {
		const { preset, headers } = this.state;

		if ( preset === 'header' ) {
			this.setState( { headers: headers.concat( [ createHeader() ] ) } );
		} else if ( preset === 'security' ) {
			this.setState( { headers: headers.concat( createSecurityHeaders() ) } );
		} else if ( preset === 'cors' ) {
			this.setState( { headers: headers.concat( createCorsHeaders() ) } );
		}
	}

	onDelete = ( pos ) => {
		const headers = [ ...this.state.headers.slice( 0, pos ), ...this.state.headers.slice( pos + 1 ) ];

		this.setState( { headers } );
	}

	render() {
		const { loadStatus, values, saveStatus } = this.props;
		const { headers } = this.state;

		if ( loadStatus === STATUS_IN_PROGRESS || ! values ) {
			return <Placeholder />;
		}

		return (
			<form onSubmit={ this.onSubmit }>
				<h3>{ __( 'Settings' ) }</h3>
				<p><label><input type="checkbox" name="https" onChange={ this.onHttps } checked={ this.state.https } /> { __( 'Force a redirect from HTTP to HTTPS' ) }</label></p>

				<div className="inline-notice inline-warning">
					<p>{ __( '{{strong}}Warning{{/strong}}: ensure your HTTPS is working otherwise you can break your site.', {
						components: {
							strong: <strong />,
						},
					} ) } { __( 'Ensure that you update your site URL settings.' ) }</p>
					<p>{ __( 'If your site stops working you will need to {{link}}disable the plugin{{/link}} and make changes.', {
						components: {
							link: <ExternalLink url="https://redirection.me/support/disable-redirection/" />,
						},
					} ) }</p>
				</div>

				<h3>{ __( 'HTTP Headers' ) }</h3>
				<p>{ __( 'Site headers are added across your site, including redirects. Redirect headers are only added to redirects.' ) }</p>

				<table className="wp-list-table widefat fixed striped items redirect-headers table-auto">
					<thead>
						<tr>
							<th>{ __( 'Location' ) }</th>
							<th>{ __( 'Header' ) }</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						{ headers.map( ( header, pos ) => (
							<Header
								key={ pos }
								header={ header }
								onChange={ attrs => this.onChangeHeader( pos, attrs ) }
								onDelete={ () => this.onDelete( pos ) }
							/>
						) ) }

						{ headers.length === 0 && <tr><td colSpan="3">{ __( 'No headers' ) }</td></tr> }
					</tbody>
				</table>

				<DropdownButton
					options={ getPresets() }
					selected={ this.state.preset }
					onChange={ this.onChangePreset }
					onSelect={ this.onPreset }
				/>

				<p>{ __( 'Note that some HTTP headers are set by your server and cannot be changed.' ) }</p>

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

	return {
		loadStatus,
		saveStatus,
		values,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Site );
