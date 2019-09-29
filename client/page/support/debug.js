/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import Select from 'component/select';
import { fixStatus } from 'state/settings/action';

const getVersions = () => ( [
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
] );

class Debug extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { version: Redirectioni10n.database.next || props.debug.database.current };
	}

	onChange = ev => {
		this.setState( { version: ev.target.value } );
	}

	onSave = ev => {
		ev.preventDefault();
		this.props.onSave( 'database', this.state.version );
	}

	render() {
		const { ip_header, database } = this.props.debug;
		const { version } = this.state;

		return (
			<table className="plugin-status">
				<tbody>
					<tr>
						<th>{ __( 'Database version' ) }</th>
						<td>
							<Select items={ getVersions() } value={ version } name="database_version" onChange={ this.onChange } /> &nbsp;

							{ version !== database.current &&
								<React.Fragment>
									<strong>{ __( 'Do not change unless advised to do so!' ) }</strong> &nbsp;
									<button className="button-secondary button" onClick={ this.onSave }>{ __( 'Save' ) }</button>
								</React.Fragment>
							}
						</td>
					</tr>
					<tr>
						<th>{ __( 'IP Headers' ) }</th>
						<td>
							{ Object
								.keys( ip_header )
								.filter( key => ip_header[ key ] )
								.map( ( key, pos ) =>
									<React.Fragment key={ pos }><code>{ key } = { ip_header[ key ] ? ip_header[ key ] : '-' }</code>&nbsp;</React.Fragment>
								)
							}
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
	mapDispatchToProps,
)( Debug );
