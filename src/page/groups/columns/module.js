/**
 * External dependencies
 */

import { sprintf, __ } from '@wordpress/i18n';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import { Badge } from '@wp-plugin-components';
import { getModuleName } from '../../../state/io/selector';
import { setFilter } from '../../../state/group/action';

function ModuleColumn( { row, onEnableModule } ) {
	const { module_id } = row;
	const name = getModuleName( module_id );

	return (
		<Badge
			onClick={ () => onEnableModule( module_id ) }
			title={ sprintf( __( 'Filter on: %(type)s', 'redirection' ), { type: name } ) }
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
