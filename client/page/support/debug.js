/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import { Select } from 'wp-plugin-components';
import { fixStatus } from 'state/settings/action';

const getVersions = () => [
	{
		value: '2.3.1',
		label: '2.3.1',
	},
	{
		value: '2.3.2',
		label: '2.3.2',
	},
	{
		value: '2.4',
		label: '2.4',
	},
	{
		value: '4.0',
		label: '4.0',
	},
	{
		value: '4.1',
		label: '4.1',
	},
	{
		value: '4.2',
		label: '4.2',
	},
];

class Debug extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { version: props.debug.database.current };
	}

	onChange = ( ev ) => {
		this.setState( { version: ev.target.value } );
	};

	onSave = ( ev ) => {
		ev.preventDefault();
		this.props.onSave( 'database', this.state.version );
	};

	render() {
		const { ip_header, database } = this.props.debug;
		const { version } = this.state;

		return (
			<table className="plugin-status">
				<tbody>
					<tr>
						<th>{ __( 'Database version', 'redirection' ) }</th>
						<td>
							<Select
								items={ getVersions() }
								value={ version }
								name="database_version"
								onChange={ this.onChange }
							/>{' '}
							&nbsp;
							{ version !== database.current && (
								<>
									<strong>{ __( 'Do not change unless advised to do so!', 'redirection' ) }</strong> &nbsp;
									<button className="button-secondary button" onClick={ this.onSave }>
										{ __( 'Save', 'redirection' ) }
									</button>
								</>
							) }
						</td>
					</tr>
					<tr>
						<th>{ __( 'IP Headers', 'redirection' ) }</th>
						<td>
							{ Object.keys( ip_header )
								.filter( ( key ) => ip_header[ key ] )
								.map( ( key, pos ) => (
									<code key={ pos }>
										{ key } = { ip_header[ key ] ? ip_header[ key ] : '-' }&nbsp;
									</code>
								) ) }
						</td>
					</tr>
				</tbody>
			</table>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onSave: ( name, version ) => {
			dispatch( fixStatus( name, version ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( Debug );
