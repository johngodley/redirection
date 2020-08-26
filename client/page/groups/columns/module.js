/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import Badge from 'wp-plugin-components/badge';
import { getModuleName } from 'state/io/selector';
import { setFilter } from 'state/group/action';

function ModuleColumn( { row, onEnableModule } ) {
	const { module_id } = row;
	const name = getModuleName( module_id );

	return (
		<Badge
			onClick={ () => onEnableModule( module_id ) }
			title={ __( 'Filter on: %(type)s', { args: { type: name } } ) }
		>
			{ name }
		</Badge>
	);
}

function mapDispatchToProps( dispatch ) {
	return {
		onEnableModule: ( moduleId ) => {
			dispatch( setFilter( { module: moduleId } ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( ModuleColumn );
