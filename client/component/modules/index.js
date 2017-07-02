/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import Table from 'component/table';
import { getModule } from 'state/module/action';
import ModuleRow from './row';

const headers = [
	{
		name: 'modules',
		title: __( 'Module' ),
		sortable: false,
	},
	{
		name: 'redirects',
		title: __( 'Redirects' ),
		sortable: false,
	},
];

class Modules extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onLoadModules();
	}

	renderRow( row, key, status ) {
		return <ModuleRow item={ row } key={ key } selected={ status.isSelected } isLoading={ status.isLoading } />;
	}

	render() {
		const { status, total, table, rows } = this.props.module;

		return (
			<div>
				<Table headers={ headers } rows={ rows } total={ total } row={ this.renderRow } table={ table } status={ status } />
			</div>
		);
	}
}

function mapStateToProps( state ) {
	const { module } = state;

	return {
		module,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadModules: () => {
			dispatch( getModule() );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( Modules );
