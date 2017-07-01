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

class Modules extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onLoadModules();
	}

	renderRow( row, key, status ) {
		return <ModuleRow item={ row } key={ key } selected={ status.isSelected } isLoading={ status.isLoading } />;
	}

	render() {
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

		return (
			<div>
				<Table headers={ headers } store={ this.props.module } row={ this.renderRow } />
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
